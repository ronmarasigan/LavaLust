## LavaLust Version 3

    This version will hold the mass updates of the kernel files and built-in libraries of LavaLust Framework. It will also attempt to fix all issues from the its version 2 with PHP 8.1+

## Changelog

1. Remove xss_clean() using htmlpurifier class. If you want to use it, install the class then
   create a function "xss_clean" inside a helper.
2. In version 3, you can use different instance of database connection. Changes also affectfa-spin
   the database.php file inside the config folder. (You can't autoload database if you are planning
   to use multiple connection.)
   $this->call->database();
   _ This will enable $this->db variable to handle queries.
   $this->call->database('other\*connection');
   - This will open new connection based on the parameter "other_connection". You will
     need to create new index in database array named "other_connection" inside database.php
     file.
3. Inside view fie, if you pass $data variable from controller, you can easily use the array key of the
it inside the view.
    Example:
        function index() {
            $this->call->model('user_model');
            $data['users] = $this->user_model->get_all_users();
            $this->call->view('user_view_file', $data);
        }
        inside view file you can extract values of $data['users] by just using its key "users"
        Example:
            foreach($users as $user) {
   echo $user['some_index'];
   }
   More over, you can also pass a plain string to view file.
   Example:
   function index() {
   $this->call->model('user_model');
   $data = "This is a string.";
   $this->call->view('user_view_file', $data);
   }
4. Updating new methods in Database Class and Form Form_validation

### Overview of Changes in Version 3

#### Note: you can still use the framework version 3 the way you use the version 2 and 1 before

    See the repository brach "dev" to see the difference of version 2 to version 1
    *** Changelog of v3 against v2 will be posted if the official release of v3.0 was created.

## What is LavaLust?

    LavaLust is a lightweight Web Framework - (using MVC pattern) - for people who are developing web sites using PHP. It helps
    you write code easily using Object-Oriented Approach. It also provides set of libraries for commonly needed tasks, as well as
    a helper functions to minimize the amount of time coding.

    LavaLust is somehow similar to CodeIgniter 3 structure and call to commonly used functions but way different when it comes to
    class construction. This is very simple and ultimately light. The main purpose of this is to teach the basics of OOP and how
    it will work with MVC. You will see in the documentation the similarities I am pertaining to.

## Installation and Tutorials

[Github Page](https://ronmarasigan.github.io)

[Youtube Channel](https://youtube.com/ronmarasigan)

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
