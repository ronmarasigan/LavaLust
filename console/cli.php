#!/usr/bin/php -q
<?php
(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('CLI only');

define('APP_DIR', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('RUNTIME_DIR', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR);
define('COMMANDS_DIR', APP_DIR . 'commands' . DIRECTORY_SEPARATOR);

// Command Registry
$commands = [];

// Register built-in commands
register_command('run', 'handle_run_command', 'Start PHP built-in development server', [
    '[port]'         => 'Port number (default: 3000)',
    '[--port=<n>]'   => 'Port number via flag (e.g. --port=8080)'
]);

register_command('serve', 'handle_run_command', 'Start PHP built-in development server', [
    '[port]'         => 'Port number (default: 3000)',
    '[--port=<n>]'   => 'Port number via flag (e.g. --port=8080)'
]);

register_command('make:controller', 'handle_make_controller', 'Creates a controller', [
    'name' => 'Controller name (e.g., Dashboard or User/ProfileController)'
]);

register_command('make:model', 'handle_make_model', 'Creates a model', [
    'name' => 'Model name (e.g., User or Blog/PostModel)'
]);

register_command('make:helper', 'handle_make_helper', 'Creates a helper', [
    'name' => 'Helper name (e.g., text)'
]);

register_command('make:library', 'handle_make_library', 'Creates a library', [
    'name' => 'Library name (e.g., PDF)'
]);

register_command('make:view', 'handle_make_view', 'Creates a view file', [
    'name' => 'View name (e.g., homepage or admin/dashboard)'
]);

register_command('make:language', 'handle_make_language', 'Creates a language file', [
    'name' => 'Language file name (e.g., tag-PH)'
]);

register_command('make:config', 'handle_make_config', 'Creates a config file', [
    'name' => 'Config name (e.g., auth)'
]);

register_command('make:middleware', 'handle_make_middleware', 'Creates a middleware', [
    'name' => 'Middleware name (e.g., Auth or Admin/Role)'
]);

register_command('cache:clear', 'handle_cache_clear', 'Clear all files in runtime/cache', []);

register_command('make:command', 'handle_make_command', 'Creates a custom CLI command', [
    'name' => 'Command name (e.g., SendEmails)'
]);

register_command('route:list', 'handle_route_list', 'Display all registered routes', []);

register_command('key:generate', 'handle_key_generate', 'Generate a new application key', []);

register_command('jwt:generate', 'handle_jwt_generate', 'Generate JWT_SECRET and REFRESH_TOKEN_KEY and save them to .env', [
    '[--force]' => 'Overwrite values that are already set in .env',
    '[--show]'  => 'Print the generated values to the terminal'
]);

register_command('env:check', 'handle_env_check', 'Display current environment configuration summary', []);

autoload_commands();


$command    = $argv[1] ?? null;
$flags      = [];
$positional = [];

for ($i = 2; $i < $argc; $i++) {
    if (str_starts_with($argv[$i], '--')) {
        // --key=value  or  --key (boolean)
        $pair = explode('=', ltrim($argv[$i], '-'), 2);
        $flags[$pair[0]] = $pair[1] ?? true;
    } else {
        $positional[] = $argv[$i];
    }
}

$input = $positional[0] ?? null;
$input2 = $positional[1] ?? null;

if (!$command) {
    echo help_text($commands);
    exit;
}

if (!isset($commands[$command])) {
    echo danger("Invalid command: \"$command\"") . PHP_EOL;
    echo help_text($commands);
    exit;
}

call_user_func($commands[$command]['handler'], $input, $flags, $input2);

/**
 * Scan app/commands/ for classes that declare:
 *   public static $command     = 'name:of:command'
 *   public static $description = 'What this does'          (optional)
 *   public static $arguments   = ['--flag' => 'desc', ...]  (optional)
 *
 * Each file is required and the class auto-registered.
 * Files that do not follow the convention are silently skipped.
 */
function autoload_commands() {
    if (!is_dir(COMMANDS_DIR)) return;

    $files = glob(COMMANDS_DIR . '*.php');
    if (empty($files)) return;

    foreach ($files as $file) {
        // Track classes defined before this require
        $before = get_declared_classes();
        require_once $file;
        $after = get_declared_classes();

        $new_classes = array_diff($after, $before);

        foreach ($new_classes as $class) {
            $ref = new ReflectionClass($class);

            // Must have a static $command property with a non-empty string
            if (!$ref->hasProperty('command')) continue;

            $cmd_name = $ref->getStaticPropertyValue('command');
            if (empty($cmd_name) || !is_string($cmd_name)) continue;

            $description = $ref->hasProperty('description')
                ? $ref->getStaticPropertyValue('description')
                : '';

            $arguments = $ref->hasProperty('arguments')
                ? $ref->getStaticPropertyValue('arguments')
                : [];

            // Handler: [instance, 'handle']
            register_command(
                $cmd_name,
                [new $class, 'handle'],
                $description,
                is_array($arguments) ? $arguments : []
            );
        }
    }
}

function handle_run_command($port = null, array $flags = []) {
    $port = $flags['port'] ?? $port ?? 3000;

    if (!is_dir(PUBLIC_DIR)) {
        echo danger("Public directory not found at: " . PUBLIC_DIR);
        echo "Make sure you have a 'public' folder in your project root.\n";
        exit(1);
    }

    $host = '127.0.0.1';
    $url  = "http://{$host}:{$port}";

    echo success("Starting LavaLust development server...") . PHP_EOL;
    echo "Server running on: \033[1;36m{$url}\033[0m" . PHP_EOL;
    echo "Press Ctrl+C to stop the server." . PHP_EOL . PHP_EOL;

    $command = sprintf('php -S %s:%d -t %s', $host, $port, escapeshellarg(PUBLIC_DIR));
    passthru($command);
}

/**
 * Handle Make Controller Command
 *
 * @param string $name
 * @return void
 */
function handle_make_controller($name) {
    generate_class_file('controller', $name, 'Controllers', 'Controller', null, 'extends Controller');
}

/**
 * Handle Make Model Command
 *
 * @param string $name
 * @return void
 */
function handle_make_model($name) {
    generate_class_file('model', $name, 'Models', 'Model', null, 'extends Model');
}

/**
 * Handle Make Helper Command
 *
 * @param string $name
 * @return void
 */
function handle_make_helper($name) {
    generate_helper_file($name);
}

/**
 * Handle Make Library Command
 *
 * @param string $name
 * @return void
 */
function handle_make_library($name) {
    generate_class_file('library', $name, 'Libraries', 'Library');
}

/**
 * Handle Make Middleware Command
 *
 * @param string $name
 * @return void
 */
function handle_make_middleware($name) {
    generate_middleware_file($name);
}

/**
 * Handle Make View Command
 *
 * @param string $name
 * @return void
 */
function handle_make_view($name) {
    generate_view_file($name);
}

/**
 * Handle Make Language Command
 *
 * @param string $name
 * @return void
 */
function handle_make_language($name) {
    generate_language_file($name);
}

/**
 * Handle Make Config Command
 *
 * @param string $name
 * @return void
 */
function handle_make_config($name) {
    generate_config_file($name);
}

/**
 * Handle Cache Clear Command
 *
 * @return void
 */
function handle_cache_clear() {
    $cache_dir = RUNTIME_DIR . 'cache';

    if (!is_dir($cache_dir)) {
        echo danger("Cache directory not found at: {$cache_dir}");
        exit(1);
    }

    $files   = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    $deleted = 0;
    foreach ($files as $file) {
        if ($file->isFile()) {
            unlink($file->getPathname());
            $deleted++;
        } elseif ($file->isDir() && $file->getPathname() !== $cache_dir) {
            rmdir($file->getPathname());
        }
    }

    echo success("Cache cleared! {$deleted} file(s) removed from runtime/cache.") . PHP_EOL;
}

/**
 * Handle Make Command Command
 *
 * @param string $name
 * @return void
 */
function handle_make_command($name, array $flags = []) {
    if (!$name) {
        echo danger("Command name is required. Example: php lava make:command SendEmails");
        exit(1);
    }

    $class_name   = ucfirst($name);
    // Convert CamelCase → colon:separated  e.g. SendEmails → send:emails
    $command_name = strtolower(ltrim(preg_replace('/[A-Z]/', ':\0', $name), ':'));
    $command_name = preg_replace('/:+/', ':', $command_name);

    $folder    = COMMANDS_DIR;
    $file_path = $folder . "{$class_name}.php";

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $content = <<<PHP
<?php
/**
 * Command: {$class_name}
 *
 * Auto-discovered by the LavaLust CLI.
 * No registration needed — just drop this file in app/commands/.
 */
class {$class_name}
{
    /**
     * The CLI command name.
     * Usage: php lava {$command_name}
     */
    public static \$command = '{$command_name}';

    /** Short description shown in php lava help */
    public static \$description = 'Description for {$command_name}';

    /**
     * Argument/flag descriptions shown in help.
     *
     * Example:
     *   public static \$arguments = [
     *       'name'        => 'A positional argument',
     *       '[--flag=<v>]' => 'An optional flag',
     *   ];
     */
    public static \$arguments = [];

    /**
     * Command entry point.
     *
     * @param string|null \$input   First positional argument (php lava {$command_name} <input>)
     * @param array       \$flags   Associative array of --flag=value pairs
     */
    public function handle(\$input = null, array \$flags = [])
    {
        // TODO: Add your command logic here
        echo "Running {$class_name}..." . PHP_EOL;
    }
}
PHP;

    write_file($file_path, $content, 'Command', $class_name);
}

/**
 * Handle Route List Command
 *
 * @return void
 */
function handle_route_list() {
    $routes_file = APP_DIR . 'config' . DIRECTORY_SEPARATOR . 'routes.php';

    if (!file_exists($routes_file)) {
        echo danger("Routes file not found at: {$routes_file}");
        exit(1);
    }

    echo "\033[1;34mRegistered Routes\033[0m" . PHP_EOL;
    echo str_repeat('─', 72) . PHP_EOL;
    echo sprintf(
        "\033[1;33m%-8s %-30s %-28s\033[0m%s",
        'Method', 'URI', 'Handler', PHP_EOL
    );
    echo str_repeat('─', 72) . PHP_EOL;

    $content = file_get_contents($routes_file);

    // Match: $router->get('/path', 'Controller::method');
    $pattern = "/\\\$rout(?:es|er)\s*->\s*(get|post|put|patch|delete|options|any|match|group)\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]/i";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    if (empty($matches)) {
        echo "  No routes detected (ensure routes follow the standard pattern)." . PHP_EOL;
    } else {
        foreach ($matches as $m) {
            echo sprintf(
                "  \033[1;32m%-6s\033[0m %-30s %s%s",
                strtoupper($m[1]),
                $m[2],
                $m[3],
                PHP_EOL
            );
        }
    }

    echo str_repeat('─', 72) . PHP_EOL;
    echo success("Total: " . count($matches) . " route(s) found.") . PHP_EOL;
}

/**
 * Handle Key Generate Command
 *
 * @return void
 */
function handle_key_generate() {
    $key        = bin2hex(random_bytes(32)); // 64-char hex key
    $env_file   = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . '.env';

    echo success("Generated key: {$key}") . PHP_EOL;

    if (file_exists($env_file)) {
        $env = file_get_contents($env_file);

        if (preg_match('/^APP_KEY\s*=/m', $env)) {
            $env = preg_replace('/^APP_KEY\s*=.*/m', "APP_KEY={$key}", $env);
            file_put_contents($env_file, $env);
            echo success("APP_KEY updated in .env") . PHP_EOL;
        } else {
            file_put_contents($env_file, $env . PHP_EOL . "APP_KEY={$key}" . PHP_EOL);
            echo success("APP_KEY appended to .env") . PHP_EOL;
        }
    } else {
        echo "\033[0;33mNote: No .env file found. Copy the key above and set APP_KEY manually.\033[0m" . PHP_EOL;
    }
}

/**
 * Handle JWT Generate Command
 *
 * Generates JWT_SECRET and REFRESH_TOKEN_KEY and saves them to .env.
 * Values that are already set are kept unless --force is given.
 *
 * php lava jwt:generate            Generate missing keys
 * php lava jwt:generate --force    Overwrite keys already set in .env
 * php lava jwt:generate --show     Also print the generated values
 *
 * @param string|null $input
 * @param array       $flags
 * @return void
 */
function handle_jwt_generate($input = null, array $flags = []) {
    $env_file = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . '.env';
    $force    = !empty($flags['force']);
    $show     = !empty($flags['show']);

    if (file_exists($env_file)) {
        $env = file_get_contents($env_file);
    } else {
        $env = '';
        echo "\033[0;33mNo .env file found. A new one will be created at {$env_file}\033[0m" . PHP_EOL;
    }

    foreach (['JWT_SECRET', 'REFRESH_TOKEN_KEY'] as $name) {
        $current = env_file_get($env, $name);

        if ($current !== null && $current !== '' && !$force) {
            echo "\033[0;33m{$name} is already set. Skipped (use --force to overwrite).\033[0m" . PHP_EOL;
            continue;
        }

        $value = bin2hex(random_bytes(32)); // 64-char hex key, generated separately for each name
        $env   = env_file_set($env, $name, $value);

        echo success("{$name} generated and saved to .env") . PHP_EOL;

        if ($show) {
            echo "  {$name}={$value}" . PHP_EOL;
        }
    }

    if (file_put_contents($env_file, $env) === false) {
        echo danger("Could not write to {$env_file}");
        exit(1);
    }

    echo PHP_EOL . "Keep .env out of version control." . PHP_EOL;
    echo "Tokens signed with a replaced key will stop working." . PHP_EOL;
}

/**
 * Read a value from .env contents. Returns null when the key is absent.
 *
 * @param string $env
 * @param string $name
 * @return string|null
 */
function env_file_get($env, $name) {
    if (preg_match('/^' . preg_quote($name, '/') . '[ \t]*=[ \t]*(.*?)[ \t]*\r?$/m', $env, $m)) {
        return trim($m[1], " \t\"'");
    }
    return null;
}

/**
 * Set a value in .env contents: replace the line if it exists, append it otherwise.
 *
 * @param string $env
 * @param string $name
 * @param string $value
 * @return string
 */
function env_file_set($env, $name, $value) {
    $pattern = '/^' . preg_quote($name, '/') . '[ \t]*=.*$/m';

    if (preg_match($pattern, $env)) {
        return preg_replace_callback($pattern, function () use ($name, $value) {
            return "{$name}={$value}";
        }, $env, 1);
    }

    if ($env !== '' && substr($env, -1) !== "\n") {
        $env .= PHP_EOL;
    }

    return $env . "{$name}={$value}" . PHP_EOL;
}

/**
 * Handle Environment Check Command
 *
 * @return void
 */
function handle_env_check() {
    $env_file = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . '.env';

    echo "\033[1;34mEnvironment Summary\033[0m" . PHP_EOL;
    echo str_repeat('─', 50) . PHP_EOL;

    $safe_keys = ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'CACHE_DRIVER', 'SESSION_DRIVER'];

    if (!file_exists($env_file)) {
        echo danger(".env file not found at: {$env_file}");
        exit(1);
    }

    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key   = trim($key);
        $value = trim($value);

        if (!in_array($key, $safe_keys)) {
            $value = str_repeat('*', min(strlen($value), 8)) ?: '(empty)';
        }

        echo sprintf("  \033[1;33m%-22s\033[0m %s%s", $key, $value, PHP_EOL);
    }

    echo str_repeat('─', 50) . PHP_EOL;
    echo success("PHP " . PHP_VERSION . " · " . PHP_OS) . PHP_EOL;
}

/**
 * Generate Class File
 *
 * @param string $type
 * @param string $path
 * @param string $sub_dir
 * @param string $class_type
 * @param string $interface
 * @param string $extends
 * @return void
 */
function generate_class_file($type, $path, $sub_dir, $class_type, $interface = null, $extends = null) {
    $parts      = explode('/', str_replace('\\', '/', $path));
    $class_name = ucfirst(array_pop($parts));
    $relative   = implode(DIRECTORY_SEPARATOR, $parts);
    $folder     = APP_DIR . $sub_dir . DIRECTORY_SEPARATOR . $relative;
    $file_path  = $folder . DIRECTORY_SEPARATOR . $class_name . '.php';

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $extends_str   = $extends   ? " {$extends}"         : '';
    $interface_str = $interface ? " implements {$interface}" : '';

    $content = "<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * {$class_type}: {$class_name}
 * 
 * Automatically generated via CLI.
 */
class {$class_name}{$extends_str}{$interface_str} {
";

    if ($type === 'model') {
        $content .= "    protected \$table = '';\n";
        $content .= "    protected \$primary_key = 'id';\n";
        $content .= "    protected \$fillable = [];\n";
        $content .= "    protected \$guarded = ['id'];\n\n";
    }

    // Only add constructor if type is not 'library'
    if ($type !== 'library') {
        $content .= "    public function __construct()
    {
        parent::__construct();
    }
";
    }

    $content .= "}";

    write_file($file_path, $content, $class_type, $class_name);
}

/**
 * Generate Helper File
 *
 * @param string $name
 * @return void
 */
function generate_helper_file($name) {
    $parts     = explode('/', str_replace('\\', '/', $name));
    $base_name = array_pop($parts);
    $relative  = implode(DIRECTORY_SEPARATOR, $parts);
    $file_name = $base_name . '_helper.php';
    $folder    = APP_DIR . 'helpers' . DIRECTORY_SEPARATOR . $relative;
    $file_path = $folder . DIRECTORY_SEPARATOR . $file_name;

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $func_name = strtolower($base_name) . '_helper';

    $content = "<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Helper: {$file_name}
 * 
 * Automatically generated via CLI.
 */

function {$func_name}()
{
    // Your helper logic here
}
";

    write_file($file_path, $content, 'Helper', $file_name);
}

/**
 * Generate Middleware File
 *
 * @param string $name
 * @return void
 */
function generate_middleware_file($name) {
    $parts      = explode('/', str_replace('\\', '/', $name));
    $class_name = ucfirst(array_pop($parts));
    $relative   = implode(DIRECTORY_SEPARATOR, $parts);
    $folder     = APP_DIR . 'middlewares' . DIRECTORY_SEPARATOR . $relative;
    $file_path  = $folder . DIRECTORY_SEPARATOR . $class_name . 'Middleware.php';

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $content = "<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * Middleware: {$class_name}Middleware
 * 
 * Automatically generated via CLI.
 */
class {$class_name}Middleware
{
    /**
     * Handle the incoming request
     *
     * @param Closure \$next
     * @return mixed
     */
    public function handle(Closure \$next)
    {
        // TODO: Add your middleware logic here (authentication, authorization, etc.)

        return \$next();
    }
}
";

    write_file($file_path, $content, 'Middleware', $class_name . 'Middleware');
}

/**
 * Generate View File
 *
 * @param string $name
 * @return void
 */
function generate_view_file($name) {
    $parts     = explode('/', str_replace('\\', '/', $name));
    $base_name = array_pop($parts);
    $relative  = implode(DIRECTORY_SEPARATOR, $parts);
    $file_name = $base_name . '.php';
    $folder    = APP_DIR . 'views' . DIRECTORY_SEPARATOR . $relative;
    $file_path = $folder . DIRECTORY_SEPARATOR . $file_name;

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $content = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>" . ucfirst($base_name) . "</title>
</head>
<body>
    <h1>Welcome to " . ucfirst($base_name) . " View</h1>
</body>
</html>";

    write_file($file_path, $content, 'View', $file_name);
}

/**
 * Generate Language File
 *
 * @param string $name
 * @return void
 */
function generate_language_file($name) {
    $parts     = explode('/', str_replace('\\', '/', $name));
    $file_base = array_pop($parts);
    $relative  = implode(DIRECTORY_SEPARATOR, $parts);
    $folder    = APP_DIR . 'language' . DIRECTORY_SEPARATOR . $relative;
    $file_path = $folder . DIRECTORY_SEPARATOR . $file_base . '.php';

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $content = "<?php
return array(
    /**
     * Other String to be translated here
     */
    'welcome' => 'Hello {username} {type}',
);
";

    write_file($file_path, $content, 'Language', $file_base);
}

/**
 * Generate Config File
 *
 * @param string $name
 * @return void
 */
function generate_config_file($name) {
    $parts     = explode('/', str_replace('\\', '/', $name));
    $file_base = array_pop($parts);
    $relative  = implode(DIRECTORY_SEPARATOR, $parts);
    $folder    = APP_DIR . 'config' . DIRECTORY_SEPARATOR . $relative;
    $file_path = $folder . DIRECTORY_SEPARATOR . $file_base . '.php';

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $content = "<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Config: {$file_base}
 * 
 * Automatically generated via CLI.
 */

// Add your configuration here
";

    write_file($file_path, $content, 'Config', $file_base);
}

/**
 * Register a CLI command
 *
 * @param string $command
 * @param callable $handler
 * @param string $description
 * @param array $arguments
 * @return void
 */
function register_command($command, $handler, $description = '', $arguments = []) {
    global $commands;
    $commands[$command] = [
        'handler'     => $handler,
        'description' => $description,
        'arguments'   => $arguments,
    ];
}

/**
 * Write content to a file with success or error message
 *
 * @param string $path
 * @param string $content
 * @param string $type
 * @param string $name
 * @return void
 */
function write_file($path, $content, $type, $name) {
    if (!file_exists($path)) {
        file_put_contents($path, $content);
        echo success("{$type} \"{$name}\" created successfully at {$path}") . PHP_EOL;
    } else {
        echo danger("{$type} \"{$name}\" already exists.");
    }
}

/**
 * Format a string as a danger/error message
 *
 * @param string $string
 * @param bool $padding
 * @return string
 */
function danger($string = '', $padding = true) {
    $length = strlen($string) + 4;
    $output = '';

    if ($padding) $output .= "\e[0;41m" . str_pad(' ', $length) . "\e[0m\n";
    $output .= "\e[0;41m" . ($padding ? '  ' : '') . $string . ($padding ? '  ' : '') . "\e[0m\n";
    if ($padding) $output .= "\e[0;41m" . str_pad(' ', $length) . "\e[0m\n";

    return $output;
}

/**
 * Format a string as a success message
 *
 * @param string $string
 * @return string
 */
function success($string = '') {
    return "\e[0;32m" . $string . "\e[0m";
}

/**
 * Generate help text for CLI commands
 *
 * @param array $commands
 * @return string
 */
function help_text($commands) {
    $help  = "\033[1;34mLavaLust CLI Code Generator\033[0m\n";
    $help .= "Usage: \033[1;33mphp lava <command> [options]\033[0m\n\n";

    $built_in_groups = [
        'Server'    => ['serve', 'run'],
        'Cache'     => ['cache:clear'],
        'Makers'    => ['make:controller', 'make:model', 'make:middleware', 'make:helper', 'make:library', 'make:view', 'make:language', 'make:config', 'make:command'],
        'Utilities' => ['route:list', 'key:generate', 'jwt:generate', 'env:check'],
    ];

    $all_built_in = array_merge(...array_values($built_in_groups));

    // Collect custom commands (anything not in the built-in list)
    $custom_commands = array_filter(
        $commands,
        fn($key) => !in_array($key, $all_built_in),
        ARRAY_FILTER_USE_KEY
    );

    foreach ($built_in_groups as $group => $cmds) {
        $help .= "\033[1;36m{$group}:\033[0m\n";
        foreach ($cmds as $cmd) {
            if (!isset($commands[$cmd])) continue;
            $details = $commands[$cmd];
            $help   .= "  \033[1;32m" . str_pad($cmd, 22) . "\033[0m → {$details['description']}\n";
            foreach ($details['arguments'] as $arg => $desc) {
                $help .= "    \033[1;33m" . str_pad($arg, 20) . "\033[0m {$desc}\n";
            }
        }
        $help .= "\n";
    }

    // Show custom commands section only when at least one exists
    if (!empty($custom_commands)) {
        $help .= "\033[1;36mCustom Commands:\033[0m\n";
        foreach ($custom_commands as $cmd => $details) {
            $help .= "  \033[1;32m" . str_pad($cmd, 22) . "\033[0m → {$details['description']}\n";
            foreach ($details['arguments'] as $arg => $desc) {
                $help .= "    \033[1;33m" . str_pad($arg, 20) . "\033[0m {$desc}\n";
            }
        }
        $help .= "\n";
    }

    $help .= "\033[1;36mExamples:\033[0m\n";
    $help .= "  php lava serve or php lava run\n";
    $help .= "  php lava serve --port=8080 or php lava run --port=8080\n";
    $help .= "  php lava make:controller Dashboard\n";
    $help .= "  php lava make:model User\n";
    $help .= "  php lava cache:clear\n";
    $help .= "  php lava route:list\n";
    $help .= "  php lava key:generate\n";
    $help .= "  php lava jwt:generate\n\n";

    return $help;
}