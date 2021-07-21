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
	//vars
	private $LAVA;

	// Default list of allowed file extensions separated by "|"
	public $default_allowed_files = array('gif', 'jpg', 'jpeg', 'png', 'txt', 'zip', 'rar', 'tar', 'gz', 'mov', 'flv', 'mpg','mpeg', 'mp4', 'wmv', 'avi', 'mp3', 'wav', 'ogg');
	// List of allowed file extensions
	public $allowed_files;

	/**
	 * Constructor - Initializes and references LAVA
	 */
	public function __construct(array $allowed_files = NULL) {
		// instance
		$this->LAVA =& lava_instance();
		if($allowed_files == NULL) {
			$this->allowed_files = $this->default_allowed_files;
		} else {
			$this->allowed_files = $allowed_files;
		}
	}

	/**
	 * Try to Upload the given file returning the filename on success
	 *
	 * @param array $file $_FILES array element
	 * @param string $dir destination directory
	 * @param boolean $overwrite existing files of the same name?
	 * @param integer $size maximum size allowed (can also be set in php.ini or server config)
	 */
	public function do_upload($file, $dir, $overwrite = FALSE, $size = FALSE)
	{
		// Invalid upload?
		if( ! isset($file['tmp_name'], $file['name'], $file['error'], $file['size']) OR $file['error'] != UPLOAD_ERR_OK)
		{
			return FALSE;
		}

		// File to large?
		if($size AND $size > $file['size'])
		{
			return FALSE;
		}

		// Create $basename, $filename, $dirname, & $extension variables
		extract(pathinfo($file['name']) + array('extension' => ''));

		// Make the name file system safe
		$this->LAVA->call->helper('security');
		$filename = sanitize_filename($filename);

		// We must have a valid name and file type
		if(empty($filename) OR empty($extension)) return FALSE;

		$extension = strtolower($extension);

		// Don't allow just any file!
		if( ! $this->allowed_file($extension)) return FALSE;

		// Make sure we can use the destination directory
		$this->LAVA->call->helper('directory');
		is_dir_usable($dir);

		// Create a unique name if we don't want files overwritten
		$name = $overwrite ? "$filename.$ext" : $this->unique_filename($dir, $filename, $extension);

		// Move the file to the correct location
		if(move_uploaded_file($file['tmp_name'], $dir . $name))
		{
			return $name;
		}
	}


	/**
	 * Is the file extension allowed
	 *
	 * @param string $ext of the file
	 * @return boolean
	 */
	public function allowed_file($ext)
	{
		if( ! $this->allowed_files) return TRUE;
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