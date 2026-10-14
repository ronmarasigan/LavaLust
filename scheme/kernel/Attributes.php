<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - Attribute-based routing
 * ------------------------------------------------------------------
 *
 * These are plain PHP 8 Attribute classes. They carry no behavior of
 * their own — AttributeRouteLoader reads them via Reflection and
 * replays them onto the existing Router public API (get/post/put/
 * patch/delete/match/middleware/where/name), so Router.php itself
 * is never modified.
 *
 * Requires PHP >= 8.0. On PHP 7.4 this file is simply never loaded
 * (see LavaLust.php), so existing string-callback routing in
 * app/config/routes.php is completely unaffected either way.
 */

/**
 * Route
 *
 * On a CLASS: defines the URL prefix for every attributed method
 * in that controller.
 *   #[Route('/users')]
 *
 * On a METHOD: defines a full route, optionally spanning multiple
 * HTTP methods. Use this when Get/Post/Put/Patch/Delete shorthand
 * isn't enough (e.g. a handler that answers both PUT and PATCH).
 *   #[Route('/{id}', methods: ['PUT', 'PATCH'])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $path,
        public array $methods = ['GET']
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Get
{
    public function __construct(public string $path = '/') {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Post
{
    public function __construct(public string $path = '/') {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Put
{
    public function __construct(public string $path = '/') {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Patch
{
    public function __construct(public string $path = '/') {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Delete
{
    public function __construct(public string $path = '/') {}
}

/**
 * UseMiddleware
 *
 * Accepts one or more middleware names as separate arguments:
 *   #[UseMiddleware('auth')]
 *   #[UseMiddleware('throttle', 'csrf')]
 *
 * Legal on both class (applies to every attributed method in the
 * controller) and method (appended after class-level middleware,
 * i.e. it chains: class middleware runs first, then method
 * middleware, in declared order).
 *
 * Named UseMiddleware (not Middleware) because scheme/kernel/Middleware.php
 * already declares a global-scope `Middleware` class — the framework's
 * middleware pipeline runner, lazily loaded via load_class('Middleware',
 * 'kernel') whenever a route actually has middleware to run. Reusing
 * that name here caused a fatal "Cannot redeclare class Middleware".
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class UseMiddleware
{
    /** @var string[] */
    public array $names;

    public function __construct(string ...$names)
    {
        $this->names = $names;
    }
}

/**
 * Where — route parameter constraint, repeatable per method.
 *   #[Where('id', '[0-9]+')]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Where
{
    public function __construct(
        public string $param,
        public string $pattern
    ) {}
}

/**
 * Name — named route, equivalent to ->name() chaining.
 *   #[Name('users.show')]
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Name
{
    public function __construct(public string $name) {}
}
?>