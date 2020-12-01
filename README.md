# LAVALust-MVC-Framework

1. Ability to load models and libraries in view
	-> $this->[name of class]
2. xss_clean is located in Common.php. Other global functions can be included there. (now is esc funtion)
3. Security is in the core (xss_clean->the main function which extends htmlawed from library folder). CSRF protect is also loacated there which uses the session helper pre loaded in the autoload file. But if not auto loaded session class was default called inside each functions. (now is with the use htmlpurifier)
4. New routes is available by putting it inside routes.php
5. Input is the core (need to check for update) (now is IO class)
6. Files inside core can call class from libraries and models by using load function or by autoloading
7. Helpers are now functions which can be called anywhere
8. Fix class locations in load_class function
9. Can call get_instance(function from controller and lavalust) in controller and view
10. Fix load_class, can call without system and app dir constant
11. can load library from application folder
12. session class updated
13. email library (can add cc/bcc)
14. database/Database.php was added. to load it $this->load->database() using extend Model() or super object (now just call $this->load->database())
15. session class updated again.
16. all classes was loaded inside controller class based on the classess loaded in lavalust file
17. super object get_instance is much usable inside the whole project
18. a. can load different data params together with load->view() - extract($data) inside view method
    b. can use with array keys or simple array load->view($view, $data) or load->view($view, array('data' => $data))
    c. in views you can use $data['element'] or using looping (foreach $key => $value)
19. form class was added to create form with validation (using formr)
20. htmlpurifier was added to prevent xss (using esc function inside common-xss_clean before)
21. benchmark was added (simple/beta)
22. cache was also included (simple) . config is inside config.php $data = $this->cache->model()
23. new helpers were added
24. input class was io class now
25. auth class was added (session must be active and database was configured) (check auth inside libraries folder)
26. database class update. use get() to use fetch mode $this->db->table('user')->get(PDO::FETCH_OBJ)->username; and getAll if you want to use the old style

if($_POST) {
			foreach($this->io->post() as $key => $val)
			{
			  echo "<p>Key: ".$key. " Value:" . $val . "</p>\n";
			}
		}

