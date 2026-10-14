<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - Attribute-based routing
 * ------------------------------------------------------------------
 *
 * Auto-discovers controllers under APP_DIR.'controllers/' (recursively),
 * reads Route/Get/Post/Put/Patch/Delete/Middleware/Where/Name attributes
 * via Reflection, and replays them onto the existing Router instance
 * using its public API only. Router.php is never touched.
 *
 * Controllers with no attributes at all (e.g. app/controllers/Welcome.php)
 * cost one getAttributes() check and are otherwise ignored — the old
 * string-callback style in app/config/routes.php keeps working exactly
 * as before, unchanged.
 *
 * Nothing needs to be registered anywhere: this scans the same
 * directory Router::call_controller_from_callback() already resolves
 * controllers from, so a new attributed controller file is picked up
 * automatically on the next request.
 */

class AttributeRouteLoader
{
    /** @var Router */
    private $router;

    /** @var string */
    private $dir;

    public function __construct($router, $dir = null)
    {
        $this->router = $router;
        $this->dir    = $dir ?: (APP_DIR . 'controllers/');
    }

    /**
     * Scan, reflect, and register.
     *
     * @return void
     */
    public function load()
    {
        foreach ($this->find_controller_files($this->dir) as $file) {
            $before = get_declared_classes();
            require_once $file;
            $discovered = array_diff(get_declared_classes(), $before);

            foreach ($discovered as $class) {
                $ref = new ReflectionClass($class);

                if (!$ref->isSubclassOf('Controller') || $ref->isAbstract()) {
                    continue;
                }

                if (!$this->has_any_route_attribute($ref)) {
                    continue; // untouched controller, nothing to do
                }

                $this->register_class($ref);
            }
        }
    }

    /**
     * Recursively find *.php files under a directory.
     *
     * @param string $dir
     * @return array
     */
    private function find_controller_files($dir)
    {
        $files = [];

        if (!is_dir($dir)) {
            return $files;
        }

        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        ) as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'php') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    /**
     * Does this class (or any public method on it) carry a route-defining
     * or middleware attribute? Cheap pre-check so plain controllers are
     * skipped without doing any registration work.
     *
     * @param ReflectionClass $ref
     * @return bool
     */
    private function has_any_route_attribute(ReflectionClass $ref)
    {
        if ($ref->getAttributes(Route::class) || $ref->getAttributes(UseMiddleware::class)) {
            return true;
        }

        foreach ($this->own_public_methods($ref) as $method) {
            foreach ($this->route_defining_attribute_names() as $attr) {
                if ($method->getAttributes($attr)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function route_defining_attribute_names()
    {
        return [Route::class, Get::class, Post::class, Put::class, Patch::class, Delete::class];
    }

    /**
     * Public methods declared directly on the controller (skip inherited
     * Controller::before_action() etc, and __construct).
     *
     * @param ReflectionClass $ref
     * @return ReflectionMethod[]
     */
    private function own_public_methods(ReflectionClass $ref)
    {
        $methods = [];

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $ref->getName()) {
                continue;
            }
            if ($method->isConstructor() || $method->isStatic()) {
                continue;
            }
            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Register every attributed method on a controller class.
     *
     * @param ReflectionClass $ref
     * @return void
     */
    private function register_class(ReflectionClass $ref)
    {
        $prefix           = $this->class_prefix($ref);
        $class_middleware = $this->middleware_names($ref->getAttributes(UseMiddleware::class));
        $class_name       = $ref->getName();

        foreach ($this->own_public_methods($ref) as $method) {
            $definitions = $this->route_definitions($method);

            if (empty($definitions)) {
                continue;
            }

            $method_middleware = $this->middleware_names($method->getAttributes(UseMiddleware::class));
            $all_middleware    = array_merge($class_middleware, $method_middleware);

            // Explicit #[Where] attributes are still supported (kept for
            // overrides / raw regex not expressible inline), but are no
            // longer required — {param:constraint} in the path itself
            // covers the common case with no separate attribute at all.
            $explicit_wheres = array_map(
                fn($a) => $a->newInstance(),
                $method->getAttributes(Where::class)
            );

            $name_attr = $method->getAttributes(Name::class);
            $name      = $name_attr ? $name_attr[0]->newInstance()->name : null;

            foreach ($definitions as [$path, $http_methods]) {
                $full_path = $this->join_path($prefix, $path);

                // Pull {id:int}, {id:[0-9]+}, etc out of the path into a
                // where-constraint list, leaving a clean {id} for Router
                // (which only understands bare {name}/{name?} segments).
                [$full_path, $wheres] = $this->parse_inline_constraints($full_path);

                // Explicit #[Where(...)] wins over inline shorthand for
                // the same param, so it's still available as an override.
                foreach ($explicit_wheres as $w) {
                    $wheres[$w->param] = $w->pattern;
                }

                foreach ($http_methods as $http_method) {
                    $this->register_single(
                        $full_path,
                        strtoupper($http_method),
                        [$class_name, $method->getName()],
                        $all_middleware,
                        $wheres,
                        $name
                    );
                }
            }
        }
    }

    /**
     * Register exactly one (path, http_method) pair, then chain
     * middleware/where/name onto it. Deliberately registers one HTTP
     * method at a time (rather than handing Router::match() an array)
     * so ->middleware()/->where()/->name() — which apply to "the last
     * pushed route" in Router.php — land on the right route entry even
     * when a method answers multiple verbs.
     *
     * @return void
     */
    private function register_single($path, $http_method, $callback, $middleware, $wheres, $name)
    {
        $verb = strtolower($http_method);

        if (in_array($verb, ['get', 'post', 'put', 'patch', 'delete', 'options'], true)) {
            $route = $this->router->$verb($path, $callback);
        } else {
            $route = $this->router->match($path, $callback, [$http_method]);
        }

        if (!empty($middleware)) {
            $route->middleware($middleware);
        }

        foreach ($wheres as $param => $pattern) {
            $route->where($param, $pattern);
        }

        if ($name !== null) {
            $route->name($name);
        }
    }

    /**
     * Parse {param:constraint} placeholders out of a path.
     *
     * Supports shorthand tokens (int, alpha, alnum, slug, uuid, ulid) or
     * a raw inline regex, e.g.:
     *   {id}              -> no constraint (Router default: [^/]+)
     *   {id:int}          -> where('id', '[0-9]+')
     *   {id:[0-9]{4}}     -> where('id', '[0-9]{4}')   (raw regex, verbatim)
     *   {id:int?}         -> optional segment, same constraint
     *
     * Returns [clean_path, ['id' => '[0-9]+', ...]] — clean_path has
     * plain {id}/{id?} segments only, which is all Router itself
     * understands (see Router::convert_to_regex_pattern()).
     *
     * @param string $path
     * @return array [string, array<string,string>]
     */
    private function parse_inline_constraints($path)
    {
        $wheres = [];
        $out    = '';
        $len    = strlen($path);
        $i      = 0;

        while ($i < $len) {
            if ($path[$i] !== '{') {
                $out .= $path[$i];
                $i++;
                continue;
            }

            // Find this placeholder's matching close brace, tracking depth
            // so a quantifier like {4} inside a raw regex constraint
            // (e.g. {code:[0-9]{4}}) doesn't get mistaken for the end
            // of the placeholder itself.
            $depth = 1;
            $j     = $i + 1;
            while ($j < $len && $depth > 0) {
                if ($path[$j] === '{') {
                    $depth++;
                } elseif ($path[$j] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                }
                $j++;
            }

            $inner = substr($path, $i + 1, $j - $i - 1);

            $optional = '';
            if (substr($inner, -1) === '?') {
                $optional = '?';
                $inner    = substr($inner, 0, -1);
            }

            $colon = strpos($inner, ':');
            if ($colon !== false) {
                $name       = substr($inner, 0, $colon);
                $constraint = substr($inner, $colon + 1);
                $wheres[$name] = $this->shorthand_pattern($constraint);
            } else {
                $name     = $inner;
                $implicit = $this->implicit_pattern($name);
                if ($implicit !== null) {
                    $wheres[$name] = $implicit;
                }
            }

            $out .= '{' . $name . $optional . '}';
            $i = $j + 1;
        }

        return [$out, $wheres];
    }

    /**
     * Resolve a constraint token to a regex. Known shorthands mirror
     * Router's own where_number()/where_alpha()/where_uuid()/where_ulid()
     * patterns; anything unrecognized is treated as a raw regex written
     * directly in the path (e.g. {id:[0-9]{4}}).
     *
     * @param string $token
     * @return string
     */
    private function shorthand_pattern($token)
    {
        static $map = [
            'int'          => '[0-9]+',
            'alpha'        => '[a-zA-Z]+',
            'alnum'        => '[a-zA-Z0-9]+',
            'alphanumeric' => '[a-zA-Z0-9]+',
            'slug'         => '[a-zA-Z0-9-]+',
            'uuid'         => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
            'ulid'         => '[0-9A-HJ-NP-Za-km-z]{26}',
        ];

        return $map[$token] ?? $token;
    }

    /**
     * Convention-based constraint when a param carries NO explicit
     * `:constraint` at all — {id} and {user_id} imply numeric without
     * anyone having to type {id:int}. Anything else (a colon-annotated
     * placeholder, or an explicit #[Where] override) always wins over
     * this. Returns null when no convention applies (e.g. {slug}),
     * leaving Router's own default ([^/]+) in place.
     *
     * @param string $name
     * @return string|null
     */
    private function implicit_pattern($name)
    {
        if ($name === 'id' || substr($name, -3) === '_id') {
            return $this->shorthand_pattern('int');
        }

        return null;
    }

    /**
     * @param ReflectionMethod $method
     * @return array List of [path, http_methods[]]
     */
    private function route_definitions(ReflectionMethod $method)
    {
        $defs = [];

        foreach ($method->getAttributes(Get::class) as $a) {
            $defs[] = [$a->newInstance()->path, ['GET']];
        }
        foreach ($method->getAttributes(Post::class) as $a) {
            $defs[] = [$a->newInstance()->path, ['POST']];
        }
        foreach ($method->getAttributes(Put::class) as $a) {
            $defs[] = [$a->newInstance()->path, ['PUT']];
        }
        foreach ($method->getAttributes(Patch::class) as $a) {
            $defs[] = [$a->newInstance()->path, ['PATCH']];
        }
        foreach ($method->getAttributes(Delete::class) as $a) {
            $defs[] = [$a->newInstance()->path, ['DELETE']];
        }
        foreach ($method->getAttributes(Route::class) as $a) {
            $inst = $a->newInstance();
            $defs[] = [$inst->path, $inst->methods];
        }

        return $defs;
    }

    /**
     * @param ReflectionClass $ref
     * @return string
     */
    private function class_prefix(ReflectionClass $ref)
    {
        $attrs = $ref->getAttributes(Route::class);
        return $attrs ? $attrs[0]->newInstance()->path : '';
    }

    /**
     * Flatten Middleware attribute instances (each possibly carrying
     * multiple names) into one ordered, deduped list.
     *
     * @param array $attributes
     * @return string[]
     */
    private function middleware_names(array $attributes)
    {
        $names = [];
        foreach ($attributes as $a) {
            foreach ($a->newInstance()->names as $n) {
                $names[] = $n;
            }
        }
        return $names;
    }

    /**
     * Join a class-level prefix and a method-level path into one
     * clean, single-slash-separated URL.
     *
     * @param string $prefix
     * @param string $path
     * @return string
     */
    private function join_path($prefix, $path)
    {
        $prefix = trim($prefix, '/');
        $path   = trim($path, '/');

        $joined = trim($prefix . '/' . $path, '/');

        return '/' . $joined;
    }
}
?>