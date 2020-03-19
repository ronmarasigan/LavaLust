LAVALust - a lightweight PHP MVC Framework the version BABAERON PHP MVC Framework (my first attempt).
 It is more likely developed based on the structure introduced by CodeIgniter.
 It is not a professional framework but it could help other people specially students who are new to PHP and OOP.
 It can be used to develop a small to medium size web application.
 
 Features are include in the documentation of this Software

Update

1. Ability to load models and libraries in view
	-> $this->[name of class]
2. xss_clean is located in Common.php. Other global functions can be included there.
3. Security is in the core (xss_clean->the main function which extends htmlawed from library folder). CSRF protect is also loacated there which uses the session helper pre loaded in the autoload file. But if not auto loaded session class was default called inside each functions.
4. New routes is available by putting it inside routes.php
5. Input is the core (need to check for update)
6. Files inside core can call class from libraries and models by using load function of by autoloading
7. Helpers are now functions which can be called anywhere