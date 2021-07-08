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

if ( ! function_exists('str_insert'))
{
    /**
     * Insert String
     * 
     * @param  array An associative array with key => value pairs.
     * @param  string The text with the strings to be replaced.
     * @return string
     */
    function str_insert($keyValue, $string)
    {
        if(array_keys($keyValue) !== range(0, count($keyValue) - 1))
        {
            $is_assoc = TRUE;
        }
        if ($is_assoc) {
            foreach ($keyValue as $search => $replace) {
                $string = str_replace($search, $replace, $string);
            }
        }

        return $string;
    }
}

if ( ! function_exists('str_between'))
{
    /**
     * String Between
     * 
     * @param  string $left   The left element of the string to search.
     * @param  string $right  The right element of the string to search.
     * @param  string $string The string to search in.
     * @return array          A result array with all matches of the search.
     */
    function str_between($left, $right, $string)
    {
        preg_match_all('/' . preg_quote($left, '/') . '(.*?)' . preg_quote($right, '/') . '/s', $string, $matches);
        return array_map('trim', $matches[1]);
    }
}

if ( ! function_exists('str_after'))
{
    /**
     * 
     * 
     * @param  string $search The string to search for.
     * @param  string $string The string to search in.
     * @return string The found string after the search string. Whitespaces at beginning will be removed.
     */
    function str_after($search, $string)
    {
        return $search === '' ? $string : ltrim(array_reverse(explode($search, $string, 2))[0]);
    }
}

if ( ! function_exists('str_before'))
{
    /**
     * 
     * 
     * @param  string $search The string to search for.
     * @param  string $string The string to search in.
     * @return string The found string before the search string. Whitespaces at end will be removed.
     */
    function str_before($search, $string)
    {
        return $search === '' ? $string : rtrim(explode($search, $string)[0]);
    }
}

if ( ! function_exists('str_limitwords'))
{
    /**
     * String Limit Words
     * 
     * @param  string  $string The string to limit the words.
     * @param  integer $limit  The number of words to limit. Defaults to 10.
     * @param  string  $end    The string to end the cut string. Defaults to '...'
     * @return string
     */
    function str_limitwords($string, $limit = 10, $end = '...')
    {
        $arrayWords = explode(' ', $string);

        if (sizeof($arrayWords) <= $limit) {
            return $string;
        }

        return implode(' ', array_slice($arrayWords, 0, $limit)) . $end;
    }
}

if ( ! function_exists('str_limitchars'))
{
    /**
     * String limit Characters
     * 
     * @param  string  $string The string to limit the words.
     * @param  integer $limit  The number of words to limit. Defaults to 10.
     * @param  string  $end    The string to end the cut string. Defaults to '...'
     * @return string
     */
    function str_limitchars($string, $limit = 100, $end = '...')
    {
        if (mb_strwidth($string, 'UTF-8') <= $limit)
        {
            return $string;
        }

        return rtrim(mb_strimwidth($string, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if ( ! function_exists('strip_slashes'))
{
    /**
     * Strip Slashes
     *
     * Removes slashes contained in a string or in an array
     *
     * @param   mixed   string or array
     * @return  mixed   string or array
     */
    function strip_slashes($str)
    {
        if ( ! is_array($str))
        {
            return stripslashes($str);
        }

        foreach ($str as $key => $val)
        {
            $str[$key] = strip_slashes($val);
        }

        return $str;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('strip_quotes'))
{
    /**
     * Strip Quotes
     *
     * Removes single and double quotes from a string
     *
     * @param   string
     * @return  string
     */
    function strip_quotes($str)
    {
        return str_replace(array('"', "'"), '', $str);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('quotes_to_entities'))
{
    /**
     * Quotes to Entities
     *
     * Converts single and double quotes to entities
     *
     * @param   string
     * @return  string
     */
    function quotes_to_entities($str)
    {
        return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('reduce_double_slashes'))
{
    /**
     * Reduce Double Slashes
     *
     * Converts double slashes in a string to a single slash,
     * except those found in http://
     *
     * http://www.some-site.com//index.php
     *
     * becomes:
     *
     * http://www.some-site.com/index.php
     *
     * @param   string
     * @return  string
     */
    function reduce_double_slashes($str)
    {
        return preg_replace('#(^|[^:])//+#', '\\1/', $str);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('reduce_multiples'))
{
    /**
     * Reduce Multiples
     *
     * Reduces multiple instances of a particular character.  Example:
     *
     * Fred, Bill,, Joe, Jimmy
     *
     * becomes:
     *
     * Fred, Bill, Joe, Jimmy
     *
     * @param   string
     * @param   string  the character you wish to reduce
     * @param   bool    TRUE/FALSE - whether to trim the character from the beginning/end
     * @return  string
     */
    function reduce_multiples($str, $character = ',', $trim = FALSE)
    {
        $str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);
        return ($trim === TRUE) ? trim($str, $character) : $str;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('random_string'))
{
    /**
     * Create a "Random" String
     *
     * @param   string  type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
     * @param   int number of characters
     * @return  string
     */
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type)
        {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('increment_string'))
{
    /**
     * Add's _1 to a string or increment the ending number to allow _2, _3, etc
     *
     * @param   string  required
     * @param   string  What should the duplicate number be appended with
     * @param   string  Which number should be used for the first dupe increment
     * @return  string
     */
    function increment_string($str, $separator = '_', $first = 1)
    {
        preg_match('/(.+)'.preg_quote($separator, '/').'([0-9]+)$/', $str, $match);
        return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('alternator'))
{
    /**
     * Alternator
     *
     * Allows strings to be alternated. See docs...
     *
     * @param   string (as many parameters as needed)
     * @return  string
     */
    function alternator()
    {
        static $i;

        if (func_num_args() === 0)
        {
            $i = 0;
            return '';
        }

        $args = func_get_args();
        return $args[($i++ % count($args))];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('repeater'))
{
    /**
     * Repeater function
     *
     *
     * @param   string  $data   String to repeat
     * @param   int $num        Number of repeats
     * @return  string
     */
    function repeater($data, $num = 1)
    {
        return ($num > 0) ? str_repeat($data, $num) : '';
    }
}
?>