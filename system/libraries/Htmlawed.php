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
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
* ------------------------------------------------------
*  Class HTMLawed
* ------------------------------------------------------
*/
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license LGPL-3.0
 */

/**
 * A class wrapper for the htmLawed library.
 */
class Htmlawed {

    public static $defaultConfig = [
        'anti_link_spam' => ['`.`', ''],
        'balance' => 1,
        'cdata' => 3,
        'comment' => 1,
        'css_expression' => 0,
        'deny_attribute' => 'on*,style',
        'direct_list_nest' => 1,
        'elements' => '*-applet-button-form-input-textarea-iframe-script-style-embed-object',
        'keep_bad' => 0,
        'schemes' => 'classid:clsid; href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; style: nil; *:file, http, https', // clsid allowed in class
        'unique_ids' => 0,
        'valid_xhtml' => 0,
    ];

    public static $defaultSpec = [
        'object=-classid-type, -codebase',
        'embed=type(oneof=application/x-shockwave-flash)'
    ];

    /**
     * Filters a string of html with the htmLawed library.
     *
     * @param string $html The text to filter.
     * @param array|null $config Config settings for the array.
     * @param string|array|null $spec A specification to further limit the allowed attribute values in the html.
     * @return string Returns the filtered html.
     * @see http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm
     */
    public static function filter($html, array $config = null, $spec = null) {
        require_once __DIR__.'/htmLawed/htmLawed.php';

        if ($config === null) {
            $config = self::$defaultConfig;
        }

        if (isset($config['spec']) && !$spec) {
            $spec = $config['spec'];
        }

        if ($spec === null) {
            $spec = static::$defaultSpec;
        }

        return htmLawed($html, $config, $spec);
    }


    /**
     * Filter a string of html so that it can be put into an rss feed.
     *
     * @param $html The html text to fitlter.
     * @return string Returns the filtered html.
     * @see Htmlawed::filter().
     */
    public static function filterRSS($html) {
        $config = array(
            'anti_link_spam' => ['`.`', ''],
            'comment' => 1,
            'cdata' => 3,
            'css_expression' => 1,
            'deny_attribute' => 'on*,style,class',
            'elements' => '*-applet-form-input-textarea-iframe-script-style-object-embed-comment-link-listing-meta-noscript-plaintext-xmp',
            'keep_bad' => 0,
            'schemes' => 'classid:clsid; href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; style: nil; *:file, http, https', // clsid allowed in class
            'valid_xhtml' => 1,
            'balance' => 1
        );
        $spec = static::$defaultSpec;

        $result = static::filter($html, $config, $spec);

        return $result;
    }
}
 
