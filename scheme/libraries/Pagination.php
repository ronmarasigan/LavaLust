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
  * Class Pagination
  */
class Pagination
{
    /**
     * Array to hold different page info
     * 
     * @var array
     */
    protected $page_array = array();

    /**
     * Page number
     * 
     * @var array
     */
    protected $page_num;

    /**
     * Rows Per Page
     * 
     * @var int
     */
    protected $rows_per_page;

    /**
     * Crumbs
     *
     * @var int
     */
    protected $crumbs;

    /**
     * Links
     * 
     * @var string
     */
    protected $pagination;

    /**
     * First link
     *
     * @var string
     */
    protected $first_link = '&lsaquo; First';

    /**
     * Next link
     *
     * @var string
     */
    protected $next_link = '&gt;';

    /**
     * Previous link
     *
     * @var string
     */
    protected $prev_link = '&lt;';

    /**
     * Last link
     *
     * @var string
     */
    protected $last_link = 'Last &rsaquo;';

    /**
     * Classes for CSS
     * 
     * @var array
     */
    protected $classes = array();

    /**
     * Page delimiter
     * 
     * @var string
     */
    protected $page_delimiter = '/';
    /**
     * LavaLust Instance
     * 
     * @var
     */
    protected $LAVA;

    public function __construct()
    {
        $this->LAVA =& lava_instance();
        $this->LAVA->call->helper('language');
        $this->LAVA->call->library('session');

        //check if different language was set to session
        //if no session page_language is set use the default language
        if($this->LAVA->session->has_userdata('page_language'))
        {
            $set_language = $this->LAVA->session->userdata('page_language');
        } else {
            $set_language = config_item('language');
        }

        //set language
        language($set_language);

        foreach (array('first_link', 'next_link', 'prev_link', 'last_link', 'classes', 'page_delimiter') as $key)
        {
            $this->$key = lang($key);
        }

        if(config_item('enable_query_strings') == FALSE)
        {
            $this->page_delimiter = '/';
        }

    }
    /**
     * Initialize Variables for Paging
     * 
     * @param int $total_rows
     * @param int $rows_per_page
     * @param int $page_num
     */
    public function initialize($total_rows, $rows_per_page, $page_num, $url, $crumbs = 5)
    {
        $this->crumbs = $crumbs;
        $this->rows_per_page = (int) $rows_per_page;
        $this->page_array['url'] = $url;
        $last_page = ceil($total_rows / $this->rows_per_page);
        $this->page_num = (int) $page_num;

        if ($this->page_num < 1)
        {
           $this->page_num = 1;
        } elseif ($this->page_num > $last_page) {
           $this->page_num = $last_page;
        }

        $offset = ($this->page_num - 1) * $this->rows_per_page;
        $this->page_array['limit'] = 'LIMIT '.$offset.',' .$this->rows_per_page;
        $this->page_array['current'] = $this->page_num;

        if ($this->page_num == 1)
        {
            $this->page_array['previous'] = $this->page_num;
        } else {
            $this->page_array['previous'] = $this->page_num - 1;
        }

        if ($this->page_num == $last_page)
        {
            $this->page_array['next'] = $last_page;
        } else {
            $this->page_array['next'] = $this->page_num + 1;
        }

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

        if ($page_num == 1)
        {
            if ($next == $page_num) return array(1);
            for ($i = 0; $i < $this->crumbs; $i++)
            {
                if ($i == $last_page) break;
                array_push($arr, $i + 1);
            }
            return $arr;
        }

        if ($page_num == $last_page)
        {
            $start = $last_page - $this->crumbs;
            if ($start < 1) $start = 0;
            for ($i = $start; $i < $last_page; $i++)
            {
                array_push($arr, $i + 1);
            }
            return $arr;
        }

        $start = $page_num - ceil($this->crumbs / 2);
        $end = $page_num + floor($this->crumbs / 2 );
        if ($end >= $last_page) $start = $last_page - $this->crumbs;
        if ($start < 0) { $start = 0; $end = $this->crumbs; }
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
        if(! empty($this->page_array['pages'])) {
            $this->pagination = '
                <nav class="'.$this->classes['nav'].'">
                    <ul class="'.$this->classes['ul'].'">
            '; 
            
            $this->pagination .= '
                <li class="'.$this->classes['li'].'"><a class="'.$this->classes['a'].'" href="'.site_url($this->page_array['url']).''.$this->page_delimiter.'1">'.$this->first_link.'</a></li>
            ';
            $this->pagination .= '
                <li class="'.$this->classes['li'].'"><a class="'.$this->classes['a'].'" href="'.site_url($this->page_array['url']).''.$this->page_delimiter.''.$this->page_array['previous'].'">'.$this->prev_link.'</a></li>
            ';

            foreach($this->page_array['pages'] as $pages)
            {

                if($pages == $this->page_array['current'])
                    $active = 'active';
                else
                    $active = '';

                $this->pagination .= '
                    <li class="'.$this->classes['li'].' '.$active.'"><a class="'.$this->classes['a'].'" href="'.site_url($this->page_array['url']).''.$this->page_delimiter.''.(int)$pages.'">'.(int)$pages.'</a></li>
                ';
            }
            
            $this->pagination .= '
                <li class="'.$this->classes['li'].'"><a class="'.$this->classes['a'].'" href="'.site_url($this->page_array['url']).''.$this->page_delimiter.''.$this->page_array['next'].'">'.$this->next_link.'</a></li>
                <li class="'.$this->classes['li'].'"><a class="'.$this->classes['a'].'" href="'.site_url($this->page_array['url']).''.$this->page_delimiter.''.$this->page_array['last'].'">'.$this->last_link.'</a></li>
                </ul>
            </nav>
            ';
            return $this->pagination;
        }
    }
}

?>