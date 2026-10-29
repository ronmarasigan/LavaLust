# LavaLust Changelog

This document records the changes, improvements, bug fixes, security updates, and migration notes for each LavaLust PHP Framework release.

## Version 4.7.2

### Changelog

- Fixed bug in Error class affects migration run.
- Fixed bug accessing controller from routes when inside subdirectory (404 not found).
- Fixed bug in loading controller and model from inside subdirectory.
- Fixed bug in Security_headers class. Always enforcing even turned off in config while Api class was loaded. 

## Version 4.7.1

### Changelog

- Added `where()` as an alias for the Model `filter()` method.
- Fixed bug in default error log file directory.
- Fixed bug in debug_helper when CSP enabled.
- Fixed bug in Session class.

**Usage:**

```php
UserModel::where();
```

## Version 4.7.0

### Changelog

- Added the `Security_headers` class to the kernel. Security headers can be enabled or disabled through `app/config/config.php`.
- Updated the `Profiler` class, error messages, and `welcome_page` to fix issues when Content Security Policy (CSP) is enabled in the configuration.

## Version 4.6.0

### Changelog

- Updated the default Composer vendor directory location. When the relevant configuration option is enabled, the vendor directory is placed outside the project root.
- Moved the front controller, `index.php`, outside the `public` directory.

## Version 4.5.0

### Changelog

- Added support for custom user-defined middleware.
- Added a lightweight queue and scheduler service.

## Version 4.4.1

### Changelog

- Fixed an issue with accessing view files located in subdirectories.
- Fixed other known bugs.

## Version 4.4.0

### Changelog

- Added Ember Template Engine, a lightweight template engine.
- Enhanced the Session class with security features, including session locking when potential security threats are detected.

## Version 4.3.1

### Changelog

- Fixed a critical bug in the Config class affecting `get_config()` and `config_item()`. Previously loaded configuration values were not properly merged with the default configuration.

## Version 4.3.0

### Changelog

- Added support for Hierarchical Model-View-Controller (HMVC) architecture.
- Added the `modules` directory under `app` for organizing application modules.
- Added the `lava` command-line interface (CLI) for generating application components.

**Example:**

```bash
php lava make:controller ControllerName
```

## Version 4.2.5

### Changelog

- Fixed an issue in the API class that caused authorization headers to be stripped when deployed on Render.

## Version 4.2.4

### Changelog

- Updated `app/views/errors/error_php.php` with additional content.
- Improved `.htaccess` security rules.
- Fixed pagination issues in the Tailwind theme.

## Version 4.2.3

### Changelog

- Redesigned `app/views/errors/error_404.php` with a modern layout.

## Version 4.2.2

### Changelog

- Expanded the CLI with commands for generating view files, libraries, language files, and helpers.

## Version 4.2.1

### Changelog

- Added support for additional database drivers, including PostgreSQL, SQLite, and SQL Server.
- Redesigned the welcome page and error pages to make debugging information easier to access.

## Version 4.2.0

### Changelog

- Fixed known bugs and improved framework stability.

## Version 4.1

### Changelog

- Added an ORM Model for simplified database operations.
- Added the Migration class.
- Added the RESTful API class.
- Added the Encryption class.

## Version 4.0

### Changelog

- Updated the database raw-query functionality to return the executed SQL statement.
- Revised the routing implementation.
- Added the Config and Logger classes to the kernel directory.
- Added the Encryption class to the libraries directory.

## Version 3.1.5

### Changelog

- Updated the Database class with offset and pagination methods. Refer to the documentation for usage details.
- Updated `csrf_field()` in the security helper to echo the generated output instead of returning it. Existing code must be adjusted accordingly.
- Added the `valid_name()` method to the Form_validation class for validating personal names.
- Updated the `initiate()` method in the Pagination class with a revised parameter list.

**Pagination usage:**

```php
initiate($table, $rows_per_page, $page_num, $url, $crumbs = 5);
```

The `$table` parameter specifies the database table to paginate.

## Version 3.1.4

*Compatible with PHP 8.2 and later.*

### Changelog

- Fixed the PHP deprecation notice caused by dynamic property creation.

## Version 3.1.0

**Recommended update:** Upgrade to this version to obtain the following fixes.

### Changelog

- Fixed known bugs.
- Fixed issues in selected kernel classes, libraries, and helpers.

## Version 3.0.1 and Later

### Requirements

- Requires PHP 7.4 or later.

## Version 3.0

### Changelog

#### Security and Compatibility

- Removed `xss_clean()` in favor of the Escaper class. To retain `xss_clean()` functionality, install the HTML Purifier library and implement the function in a helper.

#### Multiple Database Connections

Added support for multiple database connections. Each connection is configured as a separate entry in `database.php`.

**Default connection:**

```php
$this->call->database();
```

This initializes the `$this->db` property for database operations.

**Additional connection:**

```php
$this->call->database('other_connection');
```

Configure an entry named `other_connection` in `database.php`. The connection becomes available through `$this->other_connection`.

#### View Data Handling

Controller data can be accessed directly by its array keys within the view. Views can also receive a plain string.

**Controller example:**

```php
function index()
{
    $this->call->model('user_model');

    $data['users'] = $this->user_model->get_all_users();

    $this->call->view('user_view_file', $data);
}
```

**View example:**

```php
foreach ($users as $user) {
    echo $user['some_index'];
}
```

A plain string can also be passed to a view:

```php
function index()
{
    $data = "This is a string.";

    $this->call->view('user_view_file', $data);
}
```

#### Database Operations in Models

The framework automatically executes insert, update, and delete operations. Calling `exec()` separately is not required for these operations.

**Example:**

```php
$this->db->insert($bind);
```

#### Models in Subdirectories

Models can be organized into subdirectories.

**Example structure:**

```text
app/
└── models/
    └── sub_dir/
        └── Sample_model.php
```

**Usage:**

```php
$this->call->model('sub_dir/sample');
$this->sample->method();
```

#### HTTP Verb Support in Routes

Routes can be configured to respond to specific HTTP request methods.

**Example:**

```php
$route['delete/:num']['delete'] = 'welcome/delete/$1';
```

Refer to the documentation for additional routing details.

### Todo

- Clean up the files in the `scheme` directory.

## Version 2

### Security Configuration

To serve the front controller from the `public` directory, place `index.php` inside `public` and update the following configuration.

**1. Update `ROOT_DIR` in `index.php`:**

```php
ROOT_DIR = dirname(__DIR__);
```

**2. Add an `.htaccess` file to the project root:**

```apache
AddDefaultCharset UTF-8

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*) public/$1 [L]
</IfModule>
```

**3. Add an `.htaccess` file to the `public` directory:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,L]
</IfModule>
```

### URL Helper

- Added an `exit` option as the fourth parameter of the URL helper function.

### Optional URL Rewriting

URL rewriting can be disabled by removing the `.htaccess` file. Routes can then be accessed using URLs in the following format:

```text
http://website.com/index.php/controller/method/args
```

To enable URL rewriting, rename `htaccess.example` to `.htaccess`.

### PHP Compatibility Fixes

- Fixed the cookie `SameSite` issue in PHP versions earlier than 7.3.

**Example:**

```php
set_cookie($name, $value, $expiration, array());
```

### Version 2.0.4

- Fixed issues in the Session class, including `flashdata()` compatibility with PHP 8.
- Updated model-loading methods in controllers.

#### Model Loading Changes

In versions 2.0.3 and earlier, a model could be loaded using its short name:

```php
$this->call->model('example');
```

Starting with version 2.0.4, the model-loading method accepts an optional second parameter specifying the object name.

For a model declared as `Example_model`, use:

```php
$this->call->model('example_model', 'example');
```

Access its methods through the specified object name:

```php
$this->example->some_method();
```

Models can also be loaded using an associative array:

```php
$this->call->model(array(
    'example' => 'example_model'
));
```

The array key specifies the object name, while the value identifies the model class.

**Migration note:** If your application uses the previous model-loading syntax, update the calls to use the model class name and, when necessary, the desired object name.

### Version 2.0.3

- Added the Pagination class.

### Version 2.0.2

- Restored the `exec()` method for technical requirements.

### Version 2.0.1

- Removed the need to call `->exec()` separately when performing insert, update, and delete operations through the query builder.

### Version 2.0.0 — March 30, 2021

- Added an option in the configuration to enable query strings in URLs. This option is useful when applications require optional URL parameters.

## LavaLust Version 2 — Initial Changelog

### Version 1.0.2 — February 23, 2021

#### Upload Class

- Added the Upload library.
- Renamed Benchmark to Performance.

The Performance class supports named performance markers for measuring execution time.

**Example:**

```php
$this->performance->tag('your_pointer');
```

Multiple markers can be created, provided each marker is stopped before calling `elapsed_time()`.

### Version 1.0.2 — February 19, 2021

#### Directory and Naming Changes

Several directories, classes, methods, and functions were renamed. Review the files in your application directory when upgrading.

The updated directory structure includes the following changes:

| Previous name | New name |
|---|---|
| `application` | `app` |
| `system` | `scheme` |
| `core` | `kernel` |
| `Common` | `Routine` |

The `app` and `public` directories remain configurable.

#### Configuration Updates

Update the following settings as needed:

- In `config.php`, configure `$config['composer_autoload'] = FALSE;`.
- Set the cache directory using `$config['cache_dir'] = 'runtime/cache/';`.
- Configure `$config['cache_default_expires'] = 0;`.
- Add the database port setting to `database.php`.
- In `routes.php`, configure `$route['translate_uri_dashes'] = FALSE;`.
- Remove `mimes.php` and `constants.php`, as they are no longer used by the framework.

The `runtime` directory replaces the previous `cache` directory and now stores other dynamically generated files in addition to cached data.

#### Core API Changes

The following methods and functions have been renamed:

| Previous name | New name |
|---|---|
| `get_instance()` | `lava_instance()` |
| `$this->load` | `$this->call` |

These changes affect framework-level calls. Other application coding conventions remain largely unchanged.

### Version 1.0.1 — February 10, 2021

#### Auth Library

- Fixed known bugs.
- Added two additional methods.

### Version 1.0.1 — February 3, 2021

#### Auth Library

- Updated the library so its methods can be used without manually loading the Database and Session classes.

#### Form Helper

- Updated `error_validation()` so it can be used without manually loading Form_validation. Refer to the method documentation for details.

### Version 1.0.0 — January 29, 2021

#### Session Library

- Renamed `get_userdata()` to `userdata()`.

### Version 1.0.0 — January 22, 2021

#### Form Validation Library

- Fixed known bugs.

#### Language Class

- Removed the requirement for the Locale class when retrieving the country locale code.
- Removed the need to update `php.ini` on the server solely to retrieve the locale code.

### General Maintenance

- Fixed minor bugs and made maintenance improvements.