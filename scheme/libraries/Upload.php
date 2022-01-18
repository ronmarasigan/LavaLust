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
	protected $file = array();
	/**
	 * Default list of allowed file extensions separated by "|"
	 *
	 * @var array
	 */
	protected $default_allowed_files = array('gif', 'jpg', 'jpeg', 'png', 'txt', 'zip', 'rar', 'tar', 'gz', 'mov', 'flv', 'mpg','mpeg', 'mp4', 'wmv', 'avi', 'mp3', 'wav', 'ogg');
	/**
	 * List of allowed file extensions
	 *
	 * @var array
	 */
	protected $allowed_files = array();

	/**
	 * Upload Directory
	 *
	 * @var string
	 */
	protected $dir = '';

	/**
	 * Max file size
	 *
	 * @var integer
	 */
	protected $max_size = 1024;

	/**
	 * Arrays of errors during uploading
	 *
	 * @var array
	 */
	protected $errors = array();
	 

	/**
	 * Upload
	 *
	 * @param array $file
	 */
	public function __construct($file = array())
	{
		// instance
		$this->LAVA =& lava_instance();
		$this->file = $file;
		$this->allowed_files = $this->default_allowed_files;
	}

	/**
	 * Allowed extension. Use default if not set
	 *
	 * @param array $ext
	 * @return void
	 */
	public function allowed_extension($ext = array()) {
		if(is_array($ext)) {
			$this->allowed_files = $ext;
		}
	}

	public function set_dir($dir)
	{
		return $this->dir = $dir . '/';
	}

	public function max_size($size) {
		return $this->max_size = $size;
	}

	public function is_image() {
		if(getimagesize($this->file['tmp_name'])) {
			return true;
		}
		return false;
	}

	public function errors() {
		return $this->errors;
	}

	/**
	 * Try to Upload the given file returning the filename on success
	 *
	 * @param array $file $_FILES array element
	 * @param boolean $overwrite existing files of the same name?
	 */
	public function do_upload($overwrite = FALSE)
	{
		// Invalid upload?
		if( ! isset($this->file['tmp_name'], $this->file['name'], $this->file['error'], $this->file['size']) OR $this->file['error'] != UPLOAD_ERR_OK)
		{
			return $this->errors = array_push($this->errors, 'Invalid file to uplod');
		}

		// File too large?
		if($this->max_size AND $this->max_size > $this->file['size'])
		{
			return $this->errors = array_push($this->errors, 'File too large');
		}

		// Create $basename, $filename, $dirname, & $extension variables
		extract(pathinfo($this->file['name']) + array('extension' => ''));

		// Make the name file system safe
		$this->LAVA->call->helper('security');
		$filename = sanitize_filename($filename);

		// We must have a valid name and file type
		if(empty($filename) OR empty($extension)) {
			return $this->errors = array_push($this->errors, 'Invalid name or mime type');
		}

		$extension = strtolower($extension);

		// Don't allow just any file!
		if( ! $this->allowed_file($extension)) {
			return $this->errors = array_push($this->errors, 'Invalid mime type');
		}

		// Make sure we can use the destination directory
		$this->LAVA->call->helper('directory');
		is_dir_usable($this->dir);

		// Create a unique name if we don't want files overwritten
		$name = $overwrite ? "$filename.$ext" : $this->unique_filename($this->dir, $filename, $extension);

		// Move the file to the correct location
		return (move_uploaded_file($this->file['tmp_name'], $this->dir . $name)) ? TRUE : FALSE;
	}


	/**
	 * Is the file extension allowed
	 *
	 * @param string $ext of the file
	 * @return boolean
	 */
	public function allowed_file($ext)
	{
		return in_array($ext, $this->allowed_files);
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
		while(file_exists("$dir$file$x.$ext"))
		{
			$x++;
		}
		return "$file$x.$ext";
	}

}