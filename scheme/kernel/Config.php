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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
* ------------------------------------------------------
*  Class Config
* ------------------------------------------------------
 */
class Config {

    /**
     * Array of configurations
     *
     * @var array
     */
    private $config = [];

    /**
	 * List of all loaded config files
	 *
	 * @var	array
	 */
	public $is_loaded =	array();
    

    public function __construct()
	{
		$this->config = get_config();

		// Set the base_url automatically if none was provided
		if (empty($this->config['base_url']))
		{
			if (isset($_SERVER['SERVER_ADDR']))
			{
				if (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE)
				{
					$server_addr = '['.$_SERVER['SERVER_ADDR'].']';
				}
				else
				{
					$server_addr = $_SERVER['SERVER_ADDR'];
				}

				$base_url = (is_https() ? 'https' : 'http').'://'.$server_addr
					.substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
			}
			else
			{
				$base_url = 'http://localhost/';
			}
			$this->set('base_url', $base_url);
		}

	}
    /**
     * Load Config file
     *
     * @param mixed $file
     * @param boolean $use_sections
     * @return void
     */
    public function load($file, $use_sections = FALSE)
    {
        $file = ($file === '') ? 'config' : str_replace('.php', '', $file);
        $loaded = FALSE;
        $file = is_array($file) ? $file : array($file);

        foreach($file as $location)
        {
            $file_path = APP_DIR . 'config/'.$location.'.php';

            if(! file_exists($file_path))
            {
                continue;
            }

            if (in_array($file_path, $this->is_loaded, TRUE))
            {
                return TRUE;
            }

            include($file_path);

            if ($use_sections === TRUE)
            {
                $this->config[$file] = isset($this->config[$file])
                    ? array_merge($this->config[$file], $config)
                    : $config;
            }
            else
            {
                $this->config = array_merge($this->config, $config);
            }

            $this->is_loaded[] = $file_path;
            $config = NULL;
            $loaded = TRUE;
        }
        if ($loaded === TRUE)
		{
			return TRUE;
		}
        show_404('', 'The configuration file '.$file.'.php does not exist.');
    }

    /**
	 * Fetch a config file item
	 *
	 * @param	string	$item	Config item name
	 * @param	string	$index	Index name
	 * @return	string|null	The configuration item or NULL if the item doesn't exist
	 */
    public function get($item, $index = '')
	{
		if ($index == '')
		{
			return isset($this->config[$item]) ? $this->config[$item] : NULL;
		}

		return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : NULL;
	}

    /**
	 * Set a config file item
	 *
	 * @param	string	$item	Config item key
	 * @param	string	$value	Config item value
	 * @return	void
	 */
    public function set($item, $value)
	{
		$this->config[$item] = $value;
	}
}
