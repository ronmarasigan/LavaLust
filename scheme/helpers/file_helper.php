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

if(! function_exists('write_file'))
{
    /**
     * Undocumented function
     *
     * @param string $file
     * @param string $content
     * @param string $mode
     * @return void
     */
    function write_file($file, $content, $mode = 'w')
    {
        $fp = fopen($file, $mode);

        if (flock($fp, LOCK_EX))
        {
            $result = fwrite($fp, $content);

            flock($fp, LOCK_UN);
            fclose($fp);

            return $result;
        }
    }
}

if(! function_exists('delete_files'))
{
    /**
     * Delete Directory and Files
     *
     * @param string $dir_path
     * @param boolean $htdocs
     * @return void
     */
    function delete_files($dir_path, $del_dir = FALSE, $htdocs = FALSE)
    {
        if (! empty($dir_path) && is_dir($dir_path))
        {
            $objects = scandir($dir_path);
            foreach ($objects as $object)
            {
                if ($object != '.' && $object != '..')
                {
                    if (filetype($dir_path . DIRECTORY_SEPARATOR . $object) == 'dir')
                    {
                        delete_files($dir_path . DIRECTORY_SEPARATOR . $object, $htdocs);
                    } else if($htdocs !== TRUE OR ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $object)) {
                        unlink($dir_path . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
        reset($objects);
        return ($del_dir) ?? rmdir($dir_path);
        }
    }
}

if(! function_exists('copy_file'))
{
    /**
     * Copy or Move file to a new direcotry
     *
     * @param string $path
     * @param string $copy_path
     * @param boolean $remove_original
     * @return void
     */
    function copy_file($path, $copy_path, $remove_original = FALSE)
    {
        return $remove_original ? rename($path, $copy_path) : copy($path, $copy_path);
    }
}
?>