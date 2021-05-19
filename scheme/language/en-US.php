<?php
return array(
    /**
     * Pagination
     */
    'first_link' => '&lsaquo; First',
    'next_link' => '&gt;',
    'prev_link' => '&lt;',
    'last_link' => 'Last &rsaquo;',
    'page_delimiter' => '/?page=',//if query string is enabled in your config, you can use something like `/?page=` to this
    'classes' => array('nav' => '', 'ul' => 'pagination', 'li' => 'page-item', 'a' => 'page-link'),//default for bootstrap 4. You can change the value according to your choice.

    /**
     * Other String to be translated here
     */
    'welcome' => 'Hello {username} {type}',
    
);