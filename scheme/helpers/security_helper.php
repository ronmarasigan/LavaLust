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

if( ! function_exists('filter_io'))
{
	/**
	 * Filter variables
	 *
	 * @param string $type can be string, integer, etc...
	 * @param string $var the data to be filtered
	 * @return void
	 */
	function filter_io($type, $var)
	{
		switch(strtolower($type))
		{
			case 'string':
				return filter_var($var, FILTER_UNSAFE_RAW);
				break;
			case 'int':
			case 'integer':
				return filter_var($var, FILTER_SANITIZE_NUMBER_INT);
				break;
			case 'float':
				return filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT);
				break;
			case 'url':
				return filter_var($var, FILTER_SANITIZE_URL);
				break;
			case 'email':
				return filter_var($var, FILTER_SANITIZE_EMAIL);
				break;
			default:
				return filter_var($var, FILTER_UNSAFE_RAW);
		}
	}
}

if ( ! function_exists('sanitize_filename'))
{
	/**
	 * Sanitize Filename
	 *
	 * @param	string
	 * @return	string
	 */
	function sanitize_filename($filename)
	{
		return lava_instance()->security->sanitize_filename($filename);
	}
}

if( ! function_exists('csrf_field'))
{
	/**
	 * CSRF token
	 *
	 * @return void
	 */
	function csrf_field() {

		$LAVA =& lava_instance();

		if (FALSE !== ($noise = random_bytes(1)))
            {
                list(, $noise) = unpack('c', $noise);
            }
            else
            {
                $noise = mt_rand(-128, 127);
            }

            $prepend = $append = '';
            if ($noise < 0)
            {
                $prepend = str_repeat(" ", abs($noise));
            }
            elseif ($noise > 0)
            {
                $append  = str_repeat(" ", $noise);
            }

            $form = sprintf(
                '%s<input type="hidden" name="%s" value="%s" />%s%s',
                $prepend,
                $LAVA->security->get_csrf_token_name(),
                $LAVA->security->get_csrf_hash(),
                $append,
                "\n"
            );
		echo $form;
	}
}
?>