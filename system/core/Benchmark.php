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
 * @version Version 1.3.4
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

class Benchmark {

    public function __construct() {
        
    }

    /**
     * List of all benchmark markers and when they were added
     *
     * @var array
     */
    var $marker = array();

    /**
     * Set a benchmark marker
     *
     * Multiple calls to this function can be made so that several
     * execution points can be timed
     *
     * @access  public
     * @param   string  $name   name of the marker
     * @return  void
     */
    public function mark($name)
    {
        $this->marker[$name] = microtime();
    }

    /**
     * Calculates the time difference between two marked points.
     *
     * If the first parameter is empty this function instead returns the
     * {elapsed_time} pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     *
     * @access  public
     * @param   string  a particular marked point
     * @param   string  a particular marked point
     * @param   integer the number of decimal places
     * @return  mixed
     */
    public function elapsed_time($point1 = '', $point2 = '', $decimals = 4)
    {
        if ($point1 == '')
        {
            return $this->elapsed_time('loading_time:_base_classes_start', 'loading_time:_base_classes_end');
        }

        if ( ! isset($this->marker[$point1]))
        {
            return '';
        }

        if ( ! isset($this->marker[$point2]))
        {
            $this->marker[$point2] = microtime();
        }

        list($sm, $ss) = explode(' ', $this->marker[$point1]);
        list($em, $es) = explode(' ', $this->marker[$point2]);

        return number_format(($em + $es) - ($sm + $ss), $decimals);
    }

    /**
     * Memory Usage
     *
     * This function returns the {memory_usage} pseudo-variable.
     * This permits it to be put it anywhere in a template
     * without the memory being calculated until the end.
     * The output class will swap the real value for this variable.
     *
     * @access  public
     * @return  string
     */
    public function memory_usage()
    {
        return round(memory_get_usage() / 1024 / 1024, 2).'MB';
    }

}
?>