<?php

class Router
{
    /**
     * List of routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Group routes
     *
     * @var string
     */
    private $group_prefix = '';

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
    }

    /**
     * Any Method
     *
     * @param string $url
     * @param mixed $callback
     * @param string $methods
     * @return void
     */
    public function any($url, $callback, $methods)
    {
        $this->add_route($url, $callback, $methods);
    }

    /**
     * Grouping Routes
     *
     * @param string $prefix
     * @param mixed $callback
     * @return void
     */
    public function group($prefix, $callback)
    {
        $previous_group_prefix = $this->group_prefix;
        $this->group_prefix .= $prefix;

        call_user_func($callback);

        $this->group_prefix = $previous_group_prefix;
    }

    /**
     * Adding Routes
     *
     * @param string $url
     * @param mixed $callback
     * @param string $method
     * @return void
     */
    private function add_route($url, $callback, $method = 'GET')
    {

        $methods = explode('|', strtoupper($method));
        foreach ($methods as $method) {
            $this->routes[] = [
                'url' => $this->group_prefix . $this->sanitize_url($url, '/'),
                'callback' => $callback,
                'method' => $method
            ];
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
        foreach ($this->routes as $route) {
            if (strtoupper($route['method']) === strtoupper($method)) {
                $pattern = $this->convert_to_regex_pattern($route['url']);

                if (preg_match($pattern, $url, $matches)) {
                    array_shift($matches); // Remove the first element (full match)

                    $callback = $route['callback'];

                    if (is_string($callback) && strpos($callback, '@') !== false) {
                        [$controller, $method] = explode('@', $callback);

                        if ($this->is_valid_controller_and_method($controller, $method)) {
                            $this->call_controller_method($controller, $method, $matches);
                        } else {
                            show_error('Runtime Error', 'Invalid controller or method.');
                        }
                    } elseif (is_callable($callback)) {
                        call_user_func_array($callback,  array_values($matches));
                    } else {
                        show_error('Runtime Error', 'Invalid callback for route: ' . $route);
                    }
                    return;
                }
            }
        }
        empty(config_item('404_override')) ? show_404() : show_404('Route Not Found', "Route not found: $url", config_item('404_override'));
    }

    /**
     * Check if Controller and Method is Valid
     *
     * @param string $controller
     * @param string $method
     * @return boolean
     */
    private function is_valid_controller_and_method($controller, $method)
    {
        $valid_controller = preg_match('/^[a-zA-Z0-9_\\\\]+$/', $controller);
        $valid_method = preg_match('/^[a-zA-Z0-9_]+$/', $method);
        $app = APP_DIR .'controllers/'. ucfirst($controller) . '.php';
        if(file_exists($app)){
            require_once($app);
            return $valid_controller && $valid_method && class_exists($controller) && method_exists($controller, $method);
        }

    }

    /**
     * Call the Controller and Method
     *
     * @param string $controller
     * @param string $method
     * @param mixed $params
     * @return void
     */
    private function call_controller_method($controller, $method, $params)
    {
        $controller_instance = new $controller();

        if ($this->is_method_accessible($controller_instance, $method)) {
            call_user_func_array([$controller_instance, $method], $params);
        } else {
            show_error('Runtime Error', 'Invalid method.');
        }
    }

    /**
     * Check if Method is Accessible
     *
     * @param object $object
     * @param string $method
     * @return boolean
     */
    private function is_method_accessible($object, $method)
    {
        $reflectionMethod = new ReflectionMethod($object, $method);

        return $reflectionMethod->isPublic()
            && $reflectionMethod->getDeclaringClass()->getName() === get_class($object);
    }

    /**
     * Regex Pattern
     *
     * @param string $url
     * @return void
     */
    private function convert_to_regex_pattern($url)
    {
        $pattern = preg_replace('/\//', '\\/', $url);
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';
        return $pattern;
    }

    /**
     * Sanitize URL
     *
     * @param string $url
     * @return void
     */
    public function sanitize_url($url)
    {
        // Remove trailing slashes
        $url = rtrim($url, '/');
        // Remove special characters
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return $url;
    }

}
?>