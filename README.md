## LavaLust Framework
<p align="center">
    ![alt text](https://github.com/ronmarasigan/LavaLust-Docs/tree/master/assets/images/logo.png?raw=true)
</p>
    LavaLust is a lightweight Web Framework - (using MVC pattern) - for people who are developing web sites using PHP. It helps
    you write code easily using Object-Oriented Approach. It also provides set of libraries for commonly needed tasks, as well as
    a helper functions to minimize the amount of time coding.

    LavaLust is somehow similar to CodeIgniter 3 structure and call to commonly used functions but way different when it comes to
    class construction. This is very simple and ultimately light. The main purpose of this is to teach the basics of OOP and how
    it will work with MVC. You will see in the documentation the similarities I am pertaining to.

## LavaLust Version 3

    This version will hold the mass updates of the kernel files and built-in libraries of LavaLust Framework. It will also attempt to fix all issues from the its version 2 with PHP 8.1+

## Documentation

[LavaLust Documentation Link](https://lavalust.netlify.app)

## Changelog

1. Remove xss_clean() using Escaper class. If you want to use it, install htmlpurifier library then
   create a function "xss_clean" inside a helper.
2. You can now use different instances of database connections. Changes also affect
   the database.php file inside the config folder. (Every instances depends on the key of database array
   variable.)
   $this->call->database();
   This will enable $this->db variable to handle queries.
   $this->call->database('other_connection');
   * This will open new connection based on the parameter "other_connection". You will
   need to create new index in database array named "other_connection" inside database.php
   file. It will enable $this->other_connection to handle queries.
3. Inside view fie, if you pass $data variable from controller, you can easily use the array key of
   it inside the view.
   Example: Controller

```php
        function index() {
            $this->call->model('user_model');
            $data['users] = $this->user_model->get_all_users();
            $this->call->view('user_view_file', $data);
        }
```

        Inside view file you can extract values of $data['users] by just using its key "users"
        Example: View

```php
            foreach($users as $user) {
                echo $user['some_index'];
            }
```

        Moreover, you can also pass a plain string to view file.
        Example: Controller

```php function index() {
                $this->call->model('user_model');
                $data = "This is a string.";
                $this->call->view('user_view_file', $data);
            }
```

4. In models, you dont't need to add exec() method in insert(), update() and delete() because Lavalust
        automatically added it for you.
    Example: model
```php
            $this->db->insert($bind);
```
5. You can now use put models inside sub directories.
    Example: model/sub_dir/Sample_model.php
```php
            $this->call->model('sub_dir/sample');
            $this->sample->method();
```
6. You can now use http verb in routes. Just add the type of request after the route
    Example: 
```php
    $route['delete/:num']['delete'] = 'welcome/delete/$1';
```
    See docs for more info.

### Overview of Changes in Version 3

#### Note: you can still use the framework version 3 the way you use the version 2 and 1 before

    See the repository brach "dev" to see the difference of version 2 to version 1
    *** Changelog of v3 against v2 will be posted if the official release of v3.0 was created.

## Installation and Tutorials

[Checkout LavaLust Tutorial's Youtube Channel](https://youtube.com/ronmarasigan)

## Special Thanks/Credits to the following

    CodeIgniter
    Github Comunity / Youtube for all the resouces I read to make this work.

### Licence

    MIT License

    Copyright (c) 2020 Ronald M. Marasigan

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.
