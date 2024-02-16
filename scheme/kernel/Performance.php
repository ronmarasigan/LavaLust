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
class Performance {
	public function __construct() {

    }
	/**
	 * Holds the mark points
	 *
	 * @var array
	 */
	public $tags = array();

	/**
	 * Start marking Points
	 *
	 * @param  string $point marker
	 * @return void
	 */
	public function tag($point)
	{
		$key = ! array_key_exists($point, $this->tags) ? 'start' : 'stop';
		$this->tags[$point][$key] = microtime(true);
	}

	/**
	 * Elapsed Time
	 *
	 * @param  string  $point    marker
	 * @param  integer $decimals
	 * @return float
	 */
	public function elapsed_time($point, $decimals = 4)
	{
		$split_time = $this->tags[$point]['stop'] - $this->tags[$point]['start'];
		return number_format($split_time, $decimals);
	}

	/**
	 * Memory Usage
	 *
	 * @return float
	 */
	public function memory_usage()
    {
        return round(memory_get_usage() / 1024 / 1024, 2).'MB';
    }

}
?>