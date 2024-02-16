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
*  Class Performance
* ------------------------------------------------------
 */
class Registry
{
    /**
     * Class name arrays
     *
     * @var array
     */
	private $_classes = array();

    /**
     * Instance
     *
     * @var object
     */
	private static $_instance;
	
    /**
     * Get Instance of Registry
     */
    public static function instance()
    {
    	if(!isset(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get
     * @param string $key
     * @return mixed
     */
    protected function get($key)
    {
    	
        if(isset($this->_classes[$key]))
        {	
            return $this->_classes[$key];
        }
        return NULL;
    }

    /**
     * @param string $key
     * @param object $object
     * @return void
     */
    protected function set($key, $object)
    {
        $this->_classes[$key] = $object;
    }

    /**
     * @param string $key
     * @return object
     */
    static function get_object($key)
    {
		return self::instance()->get($key);
	}

    /**
     * @param string $key
     * @param object $object
     * @return object
     */
	static function store_object($key, $object)
	{
		return self::instance()->set($key, $object);
	}
}