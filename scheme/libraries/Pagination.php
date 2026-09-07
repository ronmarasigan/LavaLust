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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * LavaLust Pagination Class
 *
 * Provides pagination logic and customizable rendering for different frontend styles.
 */
class Pagination
{
    /**
     * @var array Stores pagination metadata (limit, current page, etc.)
     */
    protected $page_array = [];

    /**
     * @var int Current page number
     */
    protected $page_num;

    /**
     * @var int Number of rows per page
     */
    protected $rows_per_page;

    /**
     * @var int Number of page links to show (crumbs)
     */
    protected $crumbs;

    /**
     * @var array Final output for render
     */
    protected $pagination;

    /**
     * @var string Label for "First" page link
     */
    protected $first_link = '&lsaquo; First';

    /**
     * @var string Label for "Next" page link
     */
    protected $next_link = '&gt;';

    /**
     * @var string Label for "Previous" page link
     */
    protected $prev_link = '&lt;';

    /**
     * @var string Label for "Last" page link
     */
    protected $last_link = 'Last &rsaquo;';

    /**
     * @var string Delimiter used between base URL and page number
     */
    protected $page_delimiter = '/';

    /**
     * @var string Current theme layout: 'bootstrap', 'tailwind', or 'custom'
     */
    protected $theme = 'bootstrap';

    /**
     * @var array CSS class mappings for HTML generation
     */
    protected $classes = [
        'nav'      => 'pagination-nav',
        'ul'       => 'pagination-list',
        'li'       => 'pagination-item',
        'li_disabled' => 'disabled',
        'a'        => 'pagination-link',
        'active'   => 'active',
        'disabled' => 'disabled',
    ];

    /**
     * @var bool Whether to show first/last links
     */
    protected $show_first_last = TRUE;

    /**
     * @var bool Whether to show prev/next links
     */
    protected $show_prev_next = TRUE;

    /**
     * @var bool Whether to disable (not hide) first/prev on page 1
     *           and next/last on the last page
     */
    protected $use_disabled_state = TRUE;

    /**
     * @var string Query string key when using query-string mode (e.g. 'page')
     *             Set to empty string to use segment-based URLs (default)
     */
    protected $query_string_key = '';

    /**
     * @var object LavaLust core instance
     */
    protected $LAVA;

    // ---------------------------------------------------------------
    // CONSTRUCTOR
    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * Loads language and session libraries and initialises labels
     * from the active language file if translations exist.
     */
    public function __construct()
    {
        $this->LAVA = lava_instance();
        $this->LAVA->call->helper('language');
        $this->LAVA->call->library('session');

        $set_language = $this->LAVA->session->userdata('page_language') ?? config_item('language');
        language($set_language);

        foreach (['first_link', 'next_link', 'prev_link', 'last_link', 'page_delimiter'] as $key)
        {
            $this->$key = lang($key) ?? $this->$key;
        }

        $this->set_theme($this->theme);
    }

    // ---------------------------------------------------------------
    // THEME & CLASSES
    // ---------------------------------------------------------------

    /**
     * Set layout theme
     *
     * Applies a predefined set of CSS classes for the given framework.
     *
     * @param  string $theme  One of 'bootstrap', 'tailwind', or 'custom'
     * @return $this
     */
    public function set_theme($theme)
    {
        $this->theme = $theme;

        switch ($theme)
        {
            case 'bootstrap':
                $this->classes = [
                    'nav'         => 'd-flex justify-content-center',
                    'ul'          => 'pagination',
                    'li'          => 'page-item',
                    'li_disabled' => 'page-item disabled',
                    'a'           => 'page-link',
                    'active'      => 'active',
                    'disabled'    => 'disabled',
                ];
                break;

            case 'tailwind':
                $this->classes = [
                    'nav'         => 'flex justify-center mt-4',
                    'ul'          => 'inline-flex -space-x-px',
                    'li'          => 'px-1',
                    'li_disabled' => 'px-1 opacity-50 pointer-events-none',
                    'a'           => 'inline-flex items-center px-3 py-2 text-sm font-medium '
                                   . 'text-gray-700 bg-white border border-gray-300 '
                                   . 'hover:bg-gray-50 first:rounded-l-md last:rounded-r-md '
                                   . 'focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'active'      => 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 hover:bg-indigo-50',
                    'disabled'    => 'opacity-50 pointer-events-none',
                ];
                break;

            case 'custom':
                // Classes are set via set_custom_classes()
                break;
        }

        return $this;
    }

    /**
     * Override one or more CSS classes for the current theme
     *
     * @param  array $classes  Associative array of class keys and values
     * @return $this
     */
    public function set_custom_classes(array $classes)
    {
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    // ---------------------------------------------------------------
    // CONFIGURATION
    // ---------------------------------------------------------------

    /**
     * Set one or more pagination options at once
     *
     * Any public/protected property on the class can be set:
     * first_link, prev_link, next_link, last_link, page_delimiter,
     * show_first_last, show_prev_next, use_disabled_state, query_string_key
     *
     * @param  array $options  Associative array of option keys and values
     * @return $this
     */
    public function set_options(array $options)
    {
        foreach ($options as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * Show or hide the First and Last navigation links
     *
     * @param  bool $show
     * @return $this
     */
    public function show_first_last($show = TRUE)
    {
        $this->show_first_last = (bool) $show;
        return $this;
    }

    /**
     * Show or hide the Previous and Next navigation links
     *
     * @param  bool $show
     * @return $this
     */
    public function show_prev_next($show = TRUE)
    {
        $this->show_prev_next = (bool) $show;
        return $this;
    }

    /**
     * Enable query-string pagination mode (e.g. ?page=3)
     * instead of segment-based URLs (e.g. /page/3)
     *
     * @param  string $key  The query string parameter name (default: 'page')
     * @return $this
     */
    public function use_query_string($key = 'page')
    {
        $this->query_string_key = $key;
        return $this;
    }

    // ---------------------------------------------------------------
    // INITIALISE
    // ---------------------------------------------------------------

    /**
     * Initialise pagination values and logic
     *
     * Must be called before paginate(). Calculates all page metadata
     * and stores it internally.
     *
     * @param  int    $total_rows     Total number of database rows
     * @param  int    $rows_per_page  Rows to display per page
     * @param  int    $page_num       Current page number (1-based)
     * @param  string $url            Base URL segment for page links
     * @param  int    $crumbs         Number of visible page links in the window
     * @return array  Pagination metadata
     */
    public function initialize($total_rows, $rows_per_page, $page_num, $url, $crumbs = 5)
    {
        $this->crumbs        = max(1, (int) $crumbs);
        $this->rows_per_page = max(1, (int) $rows_per_page);

        $this->page_array['url'] = rtrim($url, '/');

        $last_page      = max(1, (int) ceil($total_rows / $this->rows_per_page));
        $this->page_num = max(1, min((int) $page_num, $last_page));

        $offset = ($this->page_num - 1) * $this->rows_per_page;

        $this->page_array['limit']      = 'LIMIT ' . $offset . ',' . $this->rows_per_page;
        $this->page_array['offset']     = $offset;
        $this->page_array['current']    = $this->page_num;
        $this->page_array['previous']   = max(1, $this->page_num - 1);
        $this->page_array['next']       = min($last_page, $this->page_num + 1);
        $this->page_array['last']       = $last_page;
        $this->page_array['total_rows'] = (int) $total_rows;
        $this->page_array['info']       = 'Page (' . $this->page_num . ' of ' . $last_page . ')';
        $this->page_array['pages']      = $this->render_pages($this->page_num, $last_page);

        $this->page_array['is_first_page'] = ($this->page_num === 1);
        $this->page_array['is_last_page']  = ($this->page_num === $last_page);

        return $this->page_array;
    }

    // ---------------------------------------------------------------
    // METADATA ACCESSORS
    // ---------------------------------------------------------------

    /**
     * Get the raw pagination metadata array
     *
     * Returns the same array as initialize() without re-running the calculation.
     * Returns an empty array if initialize() has not been called.
     *
     * @return array
     */
    public function get_meta()
    {
        return $this->page_array;
    }

    /**
     * Get the SQL LIMIT clause for the current page
     *
     * @return string  e.g. "LIMIT 20,10"
     */
    public function get_limit()
    {
        return $this->page_array['limit'] ?? 'LIMIT 0,10';
    }

    /**
     * Get the numeric offset for the current page
     *
     * Useful for database drivers that accept offset and limit separately.
     *
     * @return int
     */
    public function get_offset()
    {
        return $this->page_array['offset'] ?? 0;
    }

    /**
     * Check whether the current page is the first page
     *
     * @return bool
     */
    public function is_first_page()
    {
        return $this->page_array['is_first_page'] ?? TRUE;
    }

    /**
     * Check whether the current page is the last page
     *
     * @return bool
     */
    public function is_last_page()
    {
        return $this->page_array['is_last_page'] ?? TRUE;
    }

    /**
     * Return pagination data as a plain array suitable for a JSON API response
     *
     * Does not include the SQL limit string or the HTML pages array.
     *
     * @return array
     */
    public function to_array()
    {
        return [
            'total'        => $this->page_array['total_rows']  ?? 0,
            'per_page'     => $this->rows_per_page             ?? 0,
            'current_page' => $this->page_array['current']     ?? 1,
            'last_page'    => $this->page_array['last']        ?? 1,
            'from'         => ($this->page_array['offset'] ?? 0) + 1,
            'to'           => min(
                                ($this->page_array['offset'] ?? 0) + ($this->rows_per_page ?? 0),
                                $this->page_array['total_rows'] ?? 0
                              ),
        ];
    }

    // ---------------------------------------------------------------
    // RENDERING
    // ---------------------------------------------------------------

    /**
     * Generate the array of page numbers for the visible window
     *
     * @param  int $page_num   Current page
     * @param  int $last_page  Last page number
     * @return array
     */
    protected function render_pages($page_num, $last_page)
    {
        $arr = [];

        if ($page_num === 1)
        {
            $count = min($this->crumbs, $last_page);
            for ($i = 1; $i <= $count; $i++)
            {
                $arr[] = $i;
            }
        }
        elseif ($page_num === $last_page)
        {
            $start = max(1, $last_page - $this->crumbs + 1);
            for ($i = $start; $i <= $last_page; $i++)
            {
                $arr[] = $i;
            }
        }
        else
        {
            $half  = (int) floor($this->crumbs / 2);
            $start = max(1, $page_num - $half);
            $end   = min($last_page, $start + $this->crumbs - 1);

            // Shift window left if we hit the right boundary
            if ($end === $last_page)
            {
                $start = max(1, $end - $this->crumbs + 1);
            }

            for ($i = $start; $i <= $end; $i++)
            {
                $arr[] = $i;
            }
        }

        return $arr;
    }

    /**
     * Render the full pagination HTML
     *
     * Returns an empty string if initialize() has not been called or
     * there is only one page.
     *
     * @return string  HTML output
     */
    public function paginate()
    {
        if (empty($this->page_array['pages']) || $this->page_array['last'] <= 1)
        {
            return '';
        }

        $is_first = $this->page_array['is_first_page'];
        $is_last  = $this->page_array['is_last_page'];

        $html  = '<nav class="' . $this->classes['nav'] . '" aria-label="Pagination">';
        $html .= '<ul class="' . $this->classes['ul'] . '">';

        // First link
        if ($this->show_first_last)
        {
            $html .= ($this->use_disabled_state && $is_first)
                ? $this->build_disabled_link($this->first_link)
                : $this->build_link(1, $this->first_link);
        }

        // Previous link
        if ($this->show_prev_next)
        {
            $html .= ($this->use_disabled_state && $is_first)
                ? $this->build_disabled_link($this->prev_link)
                : $this->build_link($this->page_array['previous'], $this->prev_link);
        }

        // Page number links
        foreach ($this->page_array['pages'] as $page)
        {
            $is_active = ($page === $this->page_array['current']);
            $html .= $this->build_link($page, $page, $is_active ? $this->classes['active'] : '');
        }

        // Next link
        if ($this->show_prev_next)
        {
            $html .= ($this->use_disabled_state && $is_last)
                ? $this->build_disabled_link($this->next_link)
                : $this->build_link($this->page_array['next'], $this->next_link);
        }

        // Last link
        if ($this->show_first_last)
        {
            $html .= ($this->use_disabled_state && $is_last)
                ? $this->build_disabled_link($this->last_link)
                : $this->build_link($this->page_array['last'], $this->last_link);
        }

        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Generate an individual page link
     *
     * @param  int    $page         Target page number
     * @param  string $label        Link text (may contain HTML entities)
     * @param  string $active_class Optional additional class(es) for the anchor
     * @return string  HTML <li> element
     */
    protected function build_link($page, $label, $active_class = '')
    {
        $href = $this->build_url($page);

        $li_classes = trim($this->classes['li'] . ' ' . $active_class);

        return '<li class="' . $li_classes . '">'
            . '<a class="' . $this->classes['a'] . '" href="' . $href . '" aria-label="Page ' . (int) $page . '">'
            . $label
            . '</a></li>';
    }

    /**
     * Generate a disabled (non-clickable) navigation link
     *
     * Used for the First/Prev links on page 1 and the Next/Last links
     * on the last page when use_disabled_state is TRUE.
     *
     * @param  string $label  Link text
     * @return string  HTML <li> element with disabled state
     */
    protected function build_disabled_link($label)
    {
        $li_class = isset($this->classes['li_disabled'])
            ? $this->classes['li_disabled']
            : $this->classes['li'] . ' ' . $this->classes['disabled'];

        $a_class = trim($this->classes['a'] . ' ' . $this->classes['disabled']);

        return '<li class="' . $li_class . '">'
             . '<a class="' . $a_class . '" href="#" aria-disabled="true" tabindex="-1">'
             . $label
             . '</a></li>';
    }

    /**
     * Build the URL for a given page number
     *
     * Supports both segment-based URLs (default) and query-string URLs.
     *
     * @param  int $page
     * @return string  Absolute URL
     */
    protected function build_url($page)
    {
        if ($this->query_string_key !== '')
        {
            // Query-string mode: base_url/controller?page=N
            $base = site_url($this->page_array['url']);
            return $base . '?' . urlencode($this->query_string_key) . '=' . (int) $page;
        }

        // Segment mode: base_url/controller/delimiter/N
        return site_url($this->page_array['url'] . $this->page_delimiter . (int) $page);
    }
}