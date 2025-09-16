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
*  Class Controller
* ------------------------------------------------------
 */
class Controller
{
	/**
	 * Controller Instance
	 *
	 * @var object
	 */
	private static $instance;
	/**
	 * Load class
	 *
	 * @var object
	 */
	public $call;

	/**
	 * Dynamic Properties using __set and __get
	 *
	 * @var array
	 */
	public $properties = [];

	/**
	 * Set Dynamic Properties
	 *
	 * @param string $prop
	 * @param string $val
	 */
	public function __set($prop, $val) {
		$this->properties[$prop] = $val;
	}

	/**
	 * Get Dynamic Properties
	 *
	 * @param string $prop
	 * @return void
	 */
	public function __get($prop) {
		if (array_key_exists($prop, $this->properties)) {
			return $this->properties[$prop];
		} else {
			throw new Exception("Property $prop does not exist");
		}
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->before_action();

		self::$instance = $this;

		foreach (loaded_class() as $var => $class)
		{
			$this->properties[$var] = load_class($class);
		}

		$this->call = load_class('invoker', 'kernel');
		$this->call->initialize();
	}

	/**
     * Called before the controller action.
     * Used to perform logic that needs to happen before each controller action.
     *
     */
    public function before_action(){}

	/**
	 * Instance of controller
	 *
	 * @return object
	 */
	public static function &instance()
	{
		return self::$instance;
	}

}

?>