<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| LAVALust - a lightweight PHP MVC Framework is free software:
| -------------------------------------------------------------------   
| you can redistribute it and/or modify it under the terms of the
| GNU General Public License as published
| by the Free Software Foundation, either version 3 of the License,
| or (at your option) any later version.
|
| LAVALust - a lightweight PHP MVC Framework is distributed in the hope
| that it will be useful, but WITHOUT ANY WARRANTY;
| without even the implied warranty of
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
| GNU General Public License for more details.
|
| You should have received a copy of the GNU General Public License
| along with LAVALust - a lightweight PHP MVC Framework.
| If not, see <https://www.gnu.org/licenses/>.
|
| @author       Ronald M. Marasigan
| @copyright    Copyright (c) 2020, LAVALust - a lightweight PHP Framework
| @license      https://www.gnu.org/licenses
| GNU General Public License V3.0
| @link     https://github.com/BABAERON/LAVALust-MVC-Framework
|
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
 
