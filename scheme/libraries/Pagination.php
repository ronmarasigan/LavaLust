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

class Pagination
{
    //for different pages status
    public $page_array = array();

    //for current page
    public $page_num = array();

    //for pagination links
    public $pagination;

    /**
     * Set Page Details for Paging
     * 
     * @param int $total_rows
     * @param int $rows_per_page
     * @param int $page_num
     */
    public function set_pages($total_rows, $rows_per_page, $page_num, $url)
    {
        $this->page_array['url'] = html_escape($url);
        $last_page = ceil($total_rows / $rows_per_page);

        $this->page_num = (int) $page_num;
        if ($this->page_num < 1)
        {
           $this->page_num = 1;
        } 
        elseif ($this->page_num > $last_page)
        {
           $this->page_num = $last_page;
        }
        $upto = ($this->page_num - 1) * $rows_per_page;
        $this->page_array['limit'] = 'LIMIT '.$upto.',' .$rows_per_page;
        $this->page_array['current'] = $this->page_num;
        if ($this->page_num == 1)
            $this->page_array['previous'] = $this->page_num;
        else
            $this->page_array['previous'] = $this->page_num - 1;
        if ($this->page_num == $last_page)
            $this->page_array['next'] = $last_page;
        else
            $this->page_array['next'] = $this->page_num + 1;
        $this->page_array['last'] = $last_page;
        $this->page_array['info'] = 'Page ('.$this->page_num.' of '.$last_page.')';
        $this->page_array['pages'] = $this->render_pages($this->page_num, $last_page, $this->page_array['next']);
        return $this->page_array;
    }

    /**
     * Calculate then Render Pages
     * 
     * @param  int $page_num
     * @param  int $last_page
     * @param  int $next
     * @return array
     */
    public function render_pages($page_num, $last_page, $next)
    {
        $arr = array();
        $show = 10;

        if ($page_num == 1)
        {
            if ($next == $page_num) return array(1);
            for ($i = 0; $i < $show; $i++)
            {
                if ($i == $last_page) break;
                array_push($arr, $i + 1);
            }
            return $arr;
        }

        if ($page_num == $last_page)
        {
            $start = $last_page - $show;
            if ($start < 1) $start = 0;
            for ($i = $start; $i < $last_page; $i++)
            {
                array_push($arr, $i + 1);
            }
            return $arr;
        }

        $start = $page_num - ceil($show / 2);
        $end = $page_num + floor($show / 2 );
        if ($end >= $last_page) $start = $last_page - $show;
        if ($start < 0) { $start = 0; $end = $show; }
        for ($i = $start; $i < $end; $i++)
        {
            if ($i == $last_page) break;
            array_push($arr, $i + 1);
        }
        return $arr;
    }

    /**
     * Render the Output
     * 
     * @return string
     */
    public function paginate()
    {
        echo '
            <nav>
                <ul class="pagination">
        ';

        if($this->page_array['current'] != 1) {
            $this->pagination = '
                <li class="page-item"><a class="page-link" href="'.site_url($this->page_array['url']).'/'.$this->page_array['previous'].'"><<</a></li>
            ';
        }

        if($this->page_array['current'] != 1 && $this->page_num > 3) {
            $this->pagination .= '
                <li class="page-item"><a class="page-link" href="'.site_url($this->page_array['url']).'/1">1</a></li>
            ';
        }

        foreach($this->page_array['pages'] as $pages) {

            if($pages == $this->page_array['current'])
                $active = 'active';
            else
                $active = '';

            $this->pagination .= '
                <li class="page-item '.$active.'"><a class="page-link" href="'.site_url($this->page_array['url']).'/'.(int)$pages.'">'.(int)$pages.'</a></li>
            ';
        }
        
        if($this->page_num != $this->page_array['last']) {
            $this->pagination .= '
                <li class="page-item"><a class="page-link" href="'.site_url($this->page_array['url']).'/'.$this->page_array['next'].'">>></a></li>
                <li class="page-item"><a class="page-link" href="'.site_url($this->page_array['url']).'/'.$this->page_array['last'].'">Last</a></li>
                </ul>
            </nav>
            ';
        }

        return $this->pagination;
    }
}

?>