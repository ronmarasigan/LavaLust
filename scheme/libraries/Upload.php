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

class Upload
{
	/**
	 * LavaLust Instance Object
	 *
	 * @var object
	 */
	private $LAVA;

	/**
	 * Uploaded file from forms
	 *
	 * @var array
	 */
	public $file = array();
	
	/**
	 * File extension
	 *
	 * @var string
	 */
	private $extension;

	/**
	 * File size
	 *
	 * @var int
	 */
	private $file_size;

	/**
	 * Default list of allowed file extensions
	 *
	 * @var array
	 */
	private $default_allowed_extensions = array('gif', 'jpg', 'jpeg', 'png');

	/**
	 * Default list of allowed file mimes
	 *
	 * @var array
	 */
	private $default_allowed_mimes = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');
	
	/**
	 * List of allowed file extensions
	 *
	 * @var array
	 */
	private $allowed_extensions = array();

	/**
	 * List of allowed file mimes
	 *
	 * @var array
	 */
	private $allowed_mimes = array();

	/**
	 * Upload Directory
	 *
	 * @var string
	 */
	private $dir = '';

	/**
	 * Max file size (MB)
	 *
	 * @var int
	 */
	private $max_size;
	
	/**
	 * Min file size (MB)
	 *
	 * @var int
	 */
	private $min_size;

	/**
	 * Arrays of errors during uploading
	 *
	 * @var array
	 */
	private $upload_errors = array();
	
	/**
	 * Filename
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * check if image
	 *
	 * @var bool
	 */
	private $is_image = FALSE;

	/**
	 * Check if encrypted
	 *
	 * @var boolean
	 */
	public $encrypted = FALSE;

	/**
	 * Check mime type
	 *
	 * @var string
	 */
	public $mime;

	/**
	 * Upload
	 *
	 * @param array $file
	 */
	public function __construct($file = array())
	{
		// Instance
		$this->LAVA =& lava_instance();
		//Uploaded file
		$this->file = $file;
		// Allowed extensions
		$this->allowed_extensions = $this->default_allowed_extensions;
		// Allowed mimes
		$this->allowed_mimes = $this->default_allowed_mimes;
	}

	/**
	 * Allowed extension. Use default if not set
	 *
	 * @param array $ext
	 * @return void
	 */
	public function allowed_extensions($ext = array())
	{
		if(is_array($ext))
		{
			$this->allowed_extensions = $ext;
		}
		return $this;
	}

	/**
	 * Allowed mime type
	 *
	 * @param array $mime
	 * @return void
	 */
	public function allowed_mimes($mimes = array())
	{
		if(is_array($mimes))
		{
			$this->allowed_mimes = $mimes;
		}
		return $this;
	}

	/**
	 * Setting directory
	 *
	 * @param string $dir
	 * @return void
	 */
	public function set_dir($dir)
	{
		$this->dir = $dir . '/';
		return $this;
	}

	/**
	 * Maximum size
	 *
	 * @param int $size
	 * @return void
	 */
	public function max_size($size)
	{
		$this->max_size = $size * pow(1024, 2);
		return $this;
	}

	/**
	 * Minimum size
	 *
	 * @param int $size
	 * @return void
	 */
	public function min_size($size)
	{
		$this->min_size = $size * pow(1024, 2);
		return $this;
	}

	/**
	 * Check if file is image
	 *
	 * @return boolean
	 */
	public function is_image()
	{
		$this->is_image = TRUE;
		return $this;
	}

	/**
	 * Upload errors
	 *
	 * @return void
	 */
	public function get_errors() {
		return $this->upload_errors;
	}

	/**
	 * Try to Upload the given file returning the filename on success
	 *
	 * @param array $file $_FILES array element
	 * @param boolean $overwrite existing files of the same name?
	 */
	public function do_upload($overwrite = FALSE, $no_extension = FALSE)
	{
		// Invalid upload?
		if( ! isset($this->file['tmp_name'], $this->file['name'], $this->file['error'], $this->file['size']) OR $this->file['error'] != UPLOAD_ERR_OK)
		{
			array_push($this->upload_errors, 'No file selected');
		}

		// Check if file is image
		if($this->is_image)
		{
			if(! getimagesize($this->file['tmp_name']))
			{
				array_push($this->upload_errors, 'Uploaded file is not an image.');
			}
		}

		// File too large?
		if(isset($this->max_size) && $this->file['size'] > $this->max_size)
		{
			array_push($this->upload_errors, 'Uploaded file size is too large.');
		}

		// File too small?
		if(isset($this->min_size) && $this->file['size'] < $this->min_size)
		{
			array_push($this->upload_errors, 'Uploaded file size is too small.');
		}

		// Create $basename, $filename, $dirname, & $extension variables
		$file_info = pathinfo($this->file['name']);

		// Make the name file system safe
		$this->LAVA->call->helper('security');
		$filename = sanitize_filename($file_info['filename']);

		// Get file extension
		$this->extension = strtolower($file_info['extension']);

		// Don't allow just any file extension!
		if( ! $this->allowed_extension($this->extension))
		{
			array_push($this->upload_errors, 'Invalid uploaded file extension.');
		}

		// Get mime type
		$this->mime = mime_content_type($this->file['tmp_name']);

		// Don't allow just any file mime!
		if( ! $this->allowed_mime($this->mime))
		{
			array_push($this->upload_errors, 'Invalid uploaded file mime type.');
		}

		// Make sure we can use the destination directory
		$this->LAVA->call->helper('directory');
		if(! is_dir_usable($this->dir))
		{
			array_push($this->upload_errors, 'Directory is not usable.');
		}

		// Create a unique name if we don't want files overwritten	
		if($overwrite)
		{
			$this->filename = $no_extension ? $filename : $filename.'.'.$this->extension;
		}
		else
		{
			if($this->encrypted)
			{
				$filename = sha1($filename . "-" . rand(10000, 99999) . "-" . time());
				$this->filename = $no_extension ? $filename : sha1($filename . "-" . rand(10000, 99999) . "-" . time()).'.'.$this->extension;
			}
			else
			{
				$this->filename = $this->unique_filename($this->dir, $filename, $no_extension ? NULL : $this->extension);
			}
		}
		
		if(empty($this->upload_errors))
		{
			// Move the file to the correct location
			return (move_uploaded_file($this->file['tmp_name'], $this->dir . $this->filename)) ? TRUE : FALSE;
		}
		else
		{
			return FALSE;
		}
		
	}


	/**
	 * Is the file extension allowed
	 *
	 * @param string $ext of the file
	 * @return boolean
	 */
	private function allowed_extension($ext)
	{
		return in_array($ext, $this->allowed_extensions);
	}

	/**
	 * Is file mime allowed
	 *
	 * @param string $mime
	 * @return boolean
	 */
	public function allowed_mime($mime)
	{
		return in_array($mime, $this->allowed_mimes);
	}

	/**
	 * Create a unique filename by appending a number to the end of the file
	 *
	 * @param string $dir to check
	 * @param string $file name to check
	 * @param string $ext of the file
	 * @return string
	 */
	public function unique_filename($dir, $file, $ext)
	{
		// We start at null so a number isn't added unless needed
		$x = NULL;

		// Check if no extension is required
		$ext = is_null($ext) ? NULL : ".$ext";

		while(file_exists("$dir$file$x$ext"))
		{
			$x++;
		}
		return "$file$x$ext";
	}

	/**
	 * Get filename
	 *
	 * @return void
	 */
	public function get_filename()
	{
		return $this->filename;
	}

	/**
	 * File extension
	 *
	 * @return void
	 */
	public function get_extension()
	{
		return $this->extension;
	}

	/**
	 * Get file size
	 *
	 * @return void
	 */
	public function get_size()
	{
		return ($this->file_size);
	}

	/**
	 * Encrypt filename
	 *
	 * @param string $filename
	 * @return void
	 */
	public function encrypt_name()
	{
		$this->encrypted = TRUE;
		return $this;
	}

}