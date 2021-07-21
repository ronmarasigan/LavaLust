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
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @since Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * Cache Class
 */
class Cache
{
	private $_lava;
	private $_config;
	private $_path;
	private $_contents;
	private $_filename;
	private $_expires;
	private $_default_expires;
	private $_created;
	private $_dependencies;

	/**
	 * Constructor - Initializes and references LAVA
	 */
	function __construct()
	{
		$this->_lava =& lava_instance();
		$this->_reset();

		$this->_config =& get_config();

		$this->_path = $this->_config['cache_dir'];
		$this->_default_expires = $this->_config['cache_default_expires'];
		if ( ! is_dir($this->_path))
		{
			show_error();
		}
	}

	/**
	 * Initialize Cache object to empty
	 *
	 * @access	private
	 * @return	void
	 */
	private function _reset()
	{
		$this->_contents = NULL;
		$this->_filename = NULL;
		$this->_expires = NULL;
		$this->_created = NULL;
		$this->_dependencies = array();
	}

	/**
	 * Call a library's cached result or create new cache
	 *
	 * @access	public
	 * @param	string
	 * @return	array
	 */
	public function library($library, $method, $arguments = array(), $expires = NULL)
	{
		if ( ! class_exists(ucfirst($library)))
		{
			$this->_lava->call->library($library);
		}

		return $this->_call($library, $method, $arguments, $expires);
	}

	/**
	 * Call a model's cached result or create new cache
	 *
	 * @access	public
	 * @return	array
	 */
	public function model($model, $method, $arguments = array(), $expires = NULL)
	{
		if ( ! class_exists(ucfirst($model)))
		{
			$this->_lava->call->model($model);
		}

		return $this->_call($model, $method, $arguments, $expires);
	}

	private function _call($property, $method, $arguments = array(), $expires = NULL)
	{
		if ( !  is_array($arguments))
		{
			$arguments = (array) $arguments;
		}

		// Clean given arguments to a 0-index array
		$arguments = array_values($arguments);

		$cache_file = $property.DIRECTORY_SEPARATOR.hash('sha1', $method.serialize($arguments));

		// See if we have this cached or delete if $expires is negative
		if($expires >= 0)
		{
			$cached_response = $this->get($cache_file);
		}
		else
		{
			$this->delete($cache_file);
			return;
		}

		// Not FALSE? Return it
		if($cached_response !== FALSE && $cached_response !== NULL)
		{
			return $cached_response;
		}

		else
		{
			// Call the model or library with the method provided and the same arguments
			$new_response = call_user_func_array(array($this->_lava->$property, $method), $arguments);
			$this->write($new_response, $cache_file, $expires);

			return $new_response;
		}
	}

	/**
	 * Helper functions for the dependencies property
	 */
	function set_dependencies($dependencies)
	{
		if (is_array($dependencies))
		{
			$this->_dependencies = $dependencies;
		}
		else
		{
			$this->_dependencies = array($dependencies);
		}

		return $this;
	}

	function add_dependencies($dependencies)
	{
		if (is_array($dependencies))
		{
			$this->_dependencies = array_merge($this->_dependencies, $dependencies);
		}
		else
		{
			$this->_dependencies[] = $dependencies;
		}

		return $this;
	}

	function get_dependencies() { return $this->_dependencies; }

	/**
	 * Helper function to get the cache creation date
	 */
	function get_created($created) { return $this->_created; }


	/**
	 * Retrieve Cache File
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	mixed
	 */
	function get($filename = NULL, $use_expires = true)
	{
		// Check if cache was requested with the function or uses this object
		if ($filename !== NULL)
		{
			$this->_reset();
			$this->_filename = $filename;
		}

		// Check directory permissions
		if ( ! is_dir($this->_path) OR ! is_writable($this->_path))
		{
			return FALSE;
		}

		// Build the file path.
		$filepath = $this->_path.$this->_filename.'.cache';

		// Check if the cache exists, if not return FALSE
		if ( ! @file_exists($filepath))
		{
			return FALSE;
		}

		// Check if the cache can be opened, if not return FALSE
		if ( ! $fp = @fopen($filepath, 'rb'))
		{
			return FALSE;
		}

		// Lock the cache
		flock($fp, LOCK_SH);

		// If the file contains data return it, otherwise return NULL
		if (filesize($filepath) > 0)
		{
			$this->_contents = unserialize(fread($fp, filesize($filepath)));
		}
		else
		{
			$this->_contents = NULL;
		}

		// Unlock the cache and close the file
		flock($fp, LOCK_UN);
		fclose($fp);

		// Check cache expiration, delete and return FALSE when expired
		if ($use_expires && ! empty($this->_contents['__cache_expires']) && $this->_contents['__cache_expires'] < time())
		{
			$this->delete($filename);
			return FALSE;
		}

		// Check Cache dependencies
		if(isset($this->_contents['__cache_dependencies']))
		{
			foreach ($this->_contents['__cache_dependencies'] as $dep)
			{
				$cache_created = filemtime($this->_path.$this->_filename.'.cache');

				// If dependency doesn't exist or is newer than this cache, delete and return FALSE
				if (! file_exists($this->_path.$dep.'.cache') or filemtime($this->_path.$dep.'.cache') > $cache_created)
				{
					$this->delete($filename);
					return FALSE;
				}
			}
		}

		// Instantiate the object variables
		$this->_expires		= isset($this->_contents['__cache_expires']) ? $this->_contents['__cache_expires'] : NULL;
		$this->_dependencies = isset($this->_contents['__cache_dependencies']) ? $this->_contents['__cache_dependencies'] : NULL;
		$this->_created		= isset($this->_contents['__cache_created']) ? $this->_contents['__cache_created'] : NULL;

		// Cleanup the meta variables from the contents
		$this->_contents = @$this->_contents['__cache_contents'];

		// Return the cache
		return $this->_contents;
	}

	/**
	 * Write Cache File
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	int
	 * @param	array
	 * @return	void
	 */
	function write($contents = NULL, $filename = NULL, $expires = NULL, $dependencies = array())
	{
		// Check if cache was passed with the function or uses this object
		if ($contents !== NULL)
		{
			$this->_reset();
			$this->_contents = $contents;
			$this->_filename = $filename;
			$this->_expires = $expires;
			$this->_dependencies = $dependencies;
		}

		// Put the contents in an array so additional meta variables
		// can be easily removed from the output
		$this->_contents = array('__cache_contents' => $this->_contents);

		// Check directory permissions
		if ( ! is_dir($this->_path) OR ! is_writable($this->_path))
		{
			return;
		}

		// check if filename contains dirs
		$subdirs = explode(DIRECTORY_SEPARATOR, $this->_filename);
		if (count($subdirs) > 1)
		{
			array_pop($subdirs);
			$test_path = $this->_path.implode(DIRECTORY_SEPARATOR, $subdirs);

			// check if specified subdir exists
			if ( ! @file_exists($test_path))
			{
				// create non existing dirs, asumes PHP5
				if ( ! @mkdir($test_path, 0755, TRUE)) return FALSE;
			}
		}

		// Set the path to the cachefile which is to be created
		$cache_path = $this->_path.$this->_filename.'.cache';

		// Open the file and log if an error occures
		if ( ! $fp = @fopen($cache_path, 'wb'))
		{
			return;
		}

		// Meta variables
		$this->_contents['__cache_created'] = time();
		$this->_contents['__cache_dependencies'] = $this->_dependencies;

		// Add expires variable if its set...
		if (! empty($this->_expires))
		{
			$this->_contents['__cache_expires'] = $this->_expires + time();
		}
		// ...or add default expiration if its set
		elseif (! empty($this->_default_expires) )
		{
			$this->_contents['__cache_expires'] = $this->_default_expires + time();
		}

		// Lock the file before writing or log an error if it failes
		if (flock($fp, LOCK_EX))
		{
			fwrite($fp, serialize($this->_contents));
			flock($fp, LOCK_UN);
		}
		else
		{
			return;
		}
		fclose($fp);
		@chmod($cache_path, 0666);

		// Reset values
		$this->_reset();
	}

	/**
	 * Delete Cache File
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function delete($filename = NULL)
	{
		if ($filename !== NULL) $this->_filename = $filename;

		$file_path = $this->_path.$this->_filename.'.cache';

		if (file_exists($file_path)) unlink($file_path);

		// Reset values
		$this->_reset();
	}

	/**
	 * Delete a group of cached files
	 *
	 * Allows you to pass a group to delete cache. Example:
	 *
	 * <code>
	 * $this->cache->write($data, 'nav_title');
	 * $this->cache->write($links, 'nav_links');
	 * $this->cache->delete_group('nav_');
	 * </code>
	 *
	 * @param 	string $group
	 * @return 	void
	 */
	public function delete_group($group = null)
	{
		if ($group === null)
		{
			return FALSE;
		}

		$this->_lava->call->helper('directory');
		$map = directory_map($this->_path, TRUE);

		foreach ($map AS $file)
		{
			if (strpos($file, $group)  !== FALSE)
			{
				unlink($this->_path.$file);
			}
		}

		// Reset values
		$this->_reset();
	}

	/**
	 * Delete Full Cache or Cache subdir
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function delete_all($dirname = '')
	{
		if (empty($this->_path))
		{
			return FALSE;
		}

		$this->_lava->call->helper('file');
		if (file_exists($this->_path.$dirname)) delete_files($this->_path.$dirname, TRUE, TRUE);

		// Reset values
		$this->_reset();
	}
}
?>