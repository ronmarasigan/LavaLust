<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
* ------------------------------------------------------
*  Class Router
* ------------------------------------------------------
 */
class Router
{
    /**
     * List of routes
     *
     * @var array
     */
    private $routes = [];
    
    /**
     * Group middleware
     *
     * @var array
     */
    private $group_middleware = [];

    /**
     * Global middleware
     *
     * @var array
     */
    private $global_middleware = [];

    /**
     * Group routes
     *
     * @var string
     */
    private $group_prefix = '';


    /**
     * Sanitize URL
     *
     * @param string $url
     * @return string
     */
    public function sanitize_url($url)
    {
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return $url;
    }

    /**
     * GET Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function get($url, $callback)
    {
        $this->add_route($url, $callback, 'GET');
        return $this;
    }

    /**
     * POST Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function post($url, $callback)
    {
        $this->add_route($url, $callback, 'POST');
        return $this;
    }

    /**
     * PUT Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function put($url, $callback)
    {
        $this->add_route($url, $callback, 'PUT');
        return $this;
    }

    /**
     * PATCH Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function patch($url, $callback)
    {
        $this->add_route($url, $callback, 'PATCH');
        return $this;
    }

    /**
     * DELETE Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function delete($url, $callback)
    {
        $this->add_route($url, $callback, 'DELETE');
        return $this;
    }

    /**
     * Options Method
     *
     * @param string $url
     * @param mixed $callback
     * @return void
     */
    public function options($url, $callback)
    {
        $this->add_route($url, $callback, 'OPTIONS');
        return $this;
    }

    /**
     * Match any method
     *
     * @param string $url
     * @param mixed $callback
     * @param string $methods
     * @return void
     */
    public function match($url, $callback, $methods)
    {
        $this->add_route($url, $callback, $methods);
        return $this;
    }

    /**
     * Match any HTTP method (GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS)
     *
     * @param string $url
     * @param mixed $callback
     * @return $this
     */
    public function any($url, $callback)
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        
        $this->add_route($url, $callback, $methods);
        
        return $this;
    }

    /**
     * Adding Routes
     *
     * @param string $url
     * @param mixed $callback
     * @param string $method
     * @return void
     */
    private function add_route($url, $callback, $method = 'GET', $name = NULL)
    {
		if (strpos($url, '/') !== 0) {
			$url = '/' . $url;
		}

        $methods = is_string($method)
            ? explode('|', strtoupper($method))
            : array_map('strtoupper', (array) $method);

        foreach ($methods as $http_method) {
            $route = [
                'url' => $this->group_prefix . $this->sanitize_url($url),
                'callback' => $callback,
                'method' => $http_method,
                'name' => $name,
                'constraints' => [],
                'middleware' => array_merge($this->global_middleware, $this->group_middleware, []),
            ];
            $this->routes[] = $route;
        }

    }

    /**
     * Add global middleware
     *
     * @param mixed $middleware
     * @return void
     */
    public function add_global_middleware($middleware)
    {
        $this->global_middleware = array_merge(
            $this->global_middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );
        return $this;
    }

    /**
     * Group Route
     *
     * @param mixed $options
     * @param callable $callback
     * @return void
     */
    public function group($options, $callback)
    {
        $previous_group_prefix = $this->group_prefix;
        $previous_group_middleware = $this->group_middleware;

        // prefix
        if (isset($options['prefix'])) {
            $prefix = $options['prefix'];
            if (strpos($prefix, '/') !== 0) $prefix = '/' . $prefix;
            $this->group_prefix .= $prefix;
        }

        // middleware
        if (isset($options['middleware'])) {
            $this->group_middleware = is_array($options['middleware']) 
                ? $options['middleware'] 
                : [$options['middleware']];
        }

        call_user_func($callback, $this);

        // restore
        $this->group_prefix = $previous_group_prefix;
        $this->group_middleware = $previous_group_middleware;
    }

    /**
     * Convert Pattern
     *
     * @param string $url
     * @param array $constraints
     * @return string
     */
    private function convert_to_regex_pattern($url, $constraints)
    {
        $segments = array_filter(explode('/', trim($url, '/')));

        if ($segments === []) {
            return '#^/?$#';
        }

        $parts = [];

        foreach ($segments as $segment) {
            if (preg_match('#^\{([a-zA-Z0-9_]+)(\?)?\}$#', $segment, $m)) {
                $name     = $m[1];
                $optional = !empty($m[2]);
                $capture  = $constraints[$name] ?? '[^/]+';

                $parts[] = $optional
                    ? '(?:/(' . $capture . '))?'
                    : '/(' . $capture . ')';
            } else {
                $parts[] = '/' . preg_quote($segment, '#');
            }
        }

        return '#^' . implode('', $parts) . '/?$#';
    }

    /**
     * Check if URL matches route pattern
     *
     * @param string $url
     * @param array $route
     * @return bool
     */
    private function url_matches_route($url, $route)
    {
        $pattern = $this->convert_to_regex_pattern($route['url'], $route['constraints']);
        return (bool) preg_match($pattern, $url);
    }

    /**
     * Call controller from callback
     *
     * @param mixed $callback
     * @param array $matches
     * @return void
     */
    private function call_controller_from_callback($callback, $matches)
    {
        if (is_array($callback) && count($callback) === 2) {
            $controller = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            $method     = $callback[1];

            // Strip namespace if fully qualified (e.g. App\Controllers\UserController → UserController)
            $controller = basename(str_replace('\\', '/', $controller));

            $controller_file = APP_DIR . 'controllers/' . ucfirst($controller) . '.php';

            if (!file_exists($controller_file)) {
                throw new RuntimeException("Controller {$controller} does not exist.");
            }

            require_once($controller_file);

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new RuntimeException("Method {$controller}->{$method} does not exist.");
            }

            call_user_func_array([$instance, $method], $matches);
            return;
        }
        if (is_string($callback)) {
            $controller = '';
            $method = 'index';

            if (strpos($callback, '::') !== false) {
                [$controller, $method] = explode('::', $callback);
            } elseif (strpos($callback, '->') !== false) {
                [$controller, $method] = explode('->', $callback);
            } elseif (strpos($callback, '@') !== false) {
                [$controller, $method] = explode('@', $callback);
            } else {
                $controller = $callback;
                $method = 'index';
            }

            $controller_file = APP_DIR . 'controllers/' . ucfirst($controller) . '.php';

            if (!file_exists($controller_file)) {
                throw new RuntimeException("Controller {$controller} does not exist.");
            }

            require_once($controller_file);

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new RuntimeException("Method {$controller}->{$method} does not exist.");
            }

            call_user_func_array([$instance, $method], $matches);

        } elseif (is_callable($callback)) {
            call_user_func_array($callback, array_values($matches));
        } else {
            throw new RuntimeException('Invalid callback.');
        }
    }


    /**
     * Execute callback for matched route
     *
     * @param string $url
     * @param array $route
     * @return void
     */
    private function execute_callback($url, $route)
    {
        $matches = [];
        if (preg_match($this->convert_to_regex_pattern($route['url'], $route['constraints']), $url, $matches)) {
            array_shift($matches);

            $callback    = $route['callback'];
            $middlewares = $route['middleware'] ?? [];

            if (!empty($middlewares)) {
                $runner = load_class('Middleware', 'kernel'); // make sure kernel middleware class exists
                $runner->run($middlewares, function () use ($callback, $matches) {
                    $this->call_controller_from_callback($callback, $matches);
                });
                return;
            }

            $this->call_controller_from_callback($callback, $matches);
        }
    }



    /**
     * Initiate Request
     *
     * @param string $url
     * @param string $method
     * @return void
     */
    public function initiate($url, $method)
    {
        if (empty($url)) {
            $url = '/';
        }
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }

        if ($method === 'OPTIONS') {
            handle_cors();
            http_response_code(204);
            exit;
        }

        // Security check for permitted characters
        $url_segments = explode('/', $url);
        array_shift($url_segments);
        foreach($url_segments as $uri)
        {
            if ($uri !== '' && ! preg_match('/^['.config_item('permitted_uri_chars').']+$/i', $uri))
            {
                if (defined('IS_CLI') && IS_CLI) {
                    echo "Error 400: Disallowed characters in command.\n";
                    exit(1);
                } else {
                    show_error('400 Bad Request', 'The URI you submitted has disallowed characters.', 'error_general', 400);
                }
            }
        }
        foreach ($this->routes as $route) {
            if (strtoupper($route['method']) === strtoupper($method) && $this->url_matches_route($url, $route)) {
                $this->execute_callback($url, $route);
                return;
            }
        }
        empty(config_item('404_override')) ? show_404() : show_404('', '', config_item('404_override'));
    }

    /**
     * Add constraints to route parameters
     *
     * @param mixed $param
     * @param string|null $pattern
     * @return void
     */
    public function where($param, $pattern = null)
    {
        $last_route = end($this->routes);

        if ($last_route) {
            if(is_array($param)) {
                foreach($param as $key => $val) {
                    $last_route['constraints'][$key] = $val;
                }
            } else {
                $last_route['constraints'][$param] = $pattern;
                
            }
            $this->routes[key($this->routes)] = $last_route;
        }

        return $this;
    }

    /**
     * Numeric
     *
     * @param mixed $param
     * @return void
     */
    public function where_number($param)
    {
        return $this->where($param, '[0-9]+');
    }

    /**
     * Alpha
     *
     * @param mixed $param
     * @return void
     */
    public function where_alpha($param)
    {
        return $this->where($param, '[a-zA-Z]+');

    }

    /**
     * Alphanumeric
     *
     * @param mixed $param
     * @return void
     */
    public function where_alphanumeric($param)
    {
        return $this->where($param, '[a-zA-Z0-9]+');
    }

    /**
     * Uuid
     *
     * @param mixed $param
     * @return void
     */
    public function where_uuid($param)
    {
        return $this->where($param, '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    }

    /**
     * Uliad
     *
     * @param mixed $param
     * @return void
     */
    public function where_ulid($param)
    {
        return $this->where($param, '[0-9A-HJ-NP-Za-km-z]{26}');
    }

    /**
     * Where In
     *
     * @param mixed $param
     * @param array $values
     * @return void
     */
    public function where_in($param, $values)
    {
        $pattern = '(' . implode('|', array_map('preg_quote', $values)) . ')';
        return $this->where($param, $pattern);
    }

    /**
     * Set middleware for the last defined route
     * @param mixed $middleware
     * @return void
     */
    public function middleware($middleware)
    {
        $index = count($this->routes) - 1; // always last route
        if ($index >= 0) {
            $this->routes[$index]['middleware'] = array_merge(
                $this->routes[$index]['middleware'],
                is_array($middleware) ? $middleware : [$middleware]
            );
        }
        return $this;
    }

    /**
     * Set name of routes
     *
     * @param string $name
     * @return void
     */
    public function name($name)
    {
        $last_route = end($this->routes);
        $last_route['name'] = $name;
        $this->routes[key($this->routes)] = $last_route;
        return $this;
    }

    /**
     * Get route by name
     *
     * @param string $name
     * @return void
     */
    public function route_name($name)
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Check if route exist
     *
     * @param string $name_of_url
     * @return void
     */
    public function route_exists($name_of_url)
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name_of_url || $route['url'] === $name_of_url) {
                return true;
            }
        }
        return false;
    }

}
?>