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
 * @version Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
 * ------------------------------------------------------
 *  Form_validation
 * ------------------------------------------------------
 */

class Form_validation {

    //Default Error Messages
    private static $err_required = '%s is required';
    private static $err_matches = '%s does not match with the other field';
    private static $err_min_length = 'Please enter less than %d character/s';
    private static $err_max_length = 'Please enter more than %d character/s';
    private static $err_email = '%s contains invalid email address';
    private static $err_aplha = '%s accepts letters only';
    private static $err_alphanum = '%s accepts letters and numbers only';
    private static $err_alphanumspace = '%s accepts letters, numbers and spaces only';
    private static $err_alphaspace = '%s accepts letters and spaces only';
    private static $err_alphanumdash = '%s accepts letters, numbers and dashes only';
    private static $err_numeric = '%s accepts numbers only';
    private static $err_grater_than = 'Please enter a value less than %f';
    private static $err_less_than = 'Please enter a value greater than %f';
    private static $err_in_list = '%s is not in the list';
    private static $err_pattern = 'Please is not in %s format';

    public $patterns = array(
        'url'           => '(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})+',
        'alpha'         => '[\p{L}]+',
        'words'         => '[\p{L}\s]+',
        'alphanum'      => '[\p{L}0-9]+',
        'int'           => '[0-9]+',
        'float'         => '[0-9\.,]+',
        'tel'           => '[0-9+\s()-]+',
        'text'          => '[a-zA-Z0-9.\s\d\w]+',
        'file'          => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+\.[A-Za-z0-9]{2,4}',
        'folder'        => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+',
        'address'       => '[\p{L}0-9\s.,()°-]+',
        'date_dmy'      => '[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}',
        'date_ymd'      => '[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}',
        'email'         => '[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+'
    );

    public $errors = array();
    private $post_arrays = array();
    private $name;
    private $value;


    public function __construct() {
        foreach($_POST as $key => $value) {
            $this->post_arrays[$key] = $value;
        }
    }

    /**
     * Check if from is submitted and not empty
     * @return [type] [description]
     */
    public function submitted() {
        return !empty($_POST) ? TRUE : FALSE;
    }

    /**
     * Setting up error message
     * @param string $custom
     * @param string $default
     * @param string $params
     */
    public function set_error_message($custom, $default, $params = NULL) {
        if(empty($custom))
                $this->errors[] = sprintf($default, $params);
            else
                $this->errors[] = $custom;
    }

    /**
     * Name
     * 
     * @param  string $name name from post
     * @return this
     */
    public function name($name) {
        if (strpos($name, '|') !== false) {
            $arr = explode('|', $name);
            $this->value = $this->post_arrays[array_shift($arr)];
            $this->name = end($arr);
        } else {
            $this->value = $this->post_arrays[$name];
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Check if pattern matched
     * 
     * @param  string $name Pattern
     * @return $this
     */
    public function pattern($name) {
        if($name == 'array'){
            if(!is_array($this->value)) {
                $this->set_error_message($custom_error, self::$err_required, $this->name);
            }
        } else {
            $regex = '/^('.$this->patterns[$name].')$/u';
            if($this->value != '' && !preg_match($regex, $this->value)){
                $this->set_error_message($custom_error, self::$err_required, $this->name);
            }           
        }
        return $this;
    }

    /**
     * Custom Patter
     * 
     * @param  string $pattern pattern
     * @return $this
     */
    public function custom_pattern($pattern) {   
        $regex = '/^('.$pattern.')$/u';
        if($this->value != '' && !preg_match($regex, $this->value)) {
            $this->set_error_message($custom_error, self::$err_required, $this->name);
        }
        return $this;
    }

    /**
     * Check if required field
     *
     * @param string $err Custom Error
     * @return $this
     */
    public function required($custom_error = '') {     
        if(($this->value == '' || $this->value == null)) {
            $this->set_error_message($custom_error, self::$err_required, $this->name);
        }            
        return $this;  
    }

    /**
     * Check if current field match the other field
     * 
     * @param  string $field
     * @param  string $err   Custom Error
     * @return $this
     */
    public function matches($field, $custom_error = '') {
        if($this->value !== $this->post_arrays[$field]){
            $this->set_error_message($custom_error, self::$err_matches, $this->name);
        }
        return $this;
    }

    /**
     * Check for minumum length
     * 
     * @param  int $length
     * @return $this
     */
    public function min_length($length, $custom_error = '') {
        if ( ! is_numeric($length))  
            return FALSE;

        if(mb_strlen($this->value) < $length){
            $this->set_error_message($custom_error, self::$err_min_length, $length);
        }
        return $this;
    }

    /**
     * Check for maximum length
     * 
     * @param  int $length
     * @return this
     */
    public function max_length($length, $custom_error = '') {
        if ( ! is_numeric($length))
            return FALSE;

        if(mb_strlen($this->value) > $length){
            $this->set_error_message($custom_error, self::$err_max_length, $length);
        }
        return $this;       
    }

    /**
     * Valid Email
     *
     * @param   string
     * @return  bool
     */

    public function valid_email($custom_error = ''){
        if(!filter_var($this->value, FILTER_VALIDATE_EMAIL))
            $this->set_error_message($custom_error, self::$err_email, $this->name);
        return $this;
    }

    /**
     * Alpha
     *
     * @param   string
     * @return  bool
     */
    
    public function alpha($custom_error = '')
    {
        if(!ctype_alpha($this->value))
            $this->set_error_message($custom_error, self::$err_alpha, $this->name);
        return $this; 
    }

    /**
     * Alpha-numeric
     *
     * @param   string
     * @return  bool
     */
    public function alpha_num($custom_error = '')
    {
        if(!ctype_alnum((string) $this->value))
            $this->set_error_message($custom_error, self::$err_alphanum, $this->name);
        return $this; 
    }

    /**
     * Alpha-numeric w/ spaces
     *
     * @param   string
     * @return  bool
     */
    public function alpha_num_space($custom_error = '')
    {
        if(!preg_match('/^[A-Z0-9 ]+$/i', $this->value))
            $this->set_error_message($custom_error, self::$err_alphanumspace, $this->name);
        return $this; 
    }

    /**
     * Alpha and Spaces
     * 
     * @param  string
     * @return bool
     */
    public function alpha_space($custom_error = '')
    {
        if(!preg_match('/^[A-Z ]+$/i', $this->value))
            $this->set_error_message($custom_error, self::$err_alphaspace, $this->name);
        return $this; 
    }

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @param   string
     * @return  bool
     */
    public function alpha_num_dash($custom_error = '')
    {
        if(!preg_match('/^[a-z0-9_-]+$/i', $this->value))
            $this->set_error_message($custom_error, self::$err_alphanumdash, $this->name);
        return $this; 
    }

    /**
     * Numeric
     *
     * @param   string
     * @return  bool
     */
    public function numeric($custom_error = '')
    {
        if(!preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $this->value))
            $this->set_error_message($custom_error, self::$err_numeric, $this->name);
        return $this; 

    }

    /**
     * Greater than
     *
     * @param   string
     * @param   int
     * @return  bool
     */
    public function greater_than($min, $custom_error = '')
    {
        if(!is_numeric($this->value))
            return FALSE;
        if($this->value < $min)
            $$this->set_error_message($custom_error, self::$err_numeric, $min);
        return $this; 
    }

    /**
     * Less than
     *
     * @param   string
     * @param   int
     * @return  bool
     */
    public function less_than($max, $custom_error = '')
    {
        if(!is_numeric($this->value))
            return FALSE;
        if($this->value > $min)
            $this->set_error_message($custom_error, self::$err_numeric, $max);
        return $this; 
    }

    /**
     * Value should be within an array of values
     *
     * @param   string
     * @param   string
     * @return  bool
     */
    public function in_list($list, $custom_error = '')
    {
        if(!in_array($this->value, explode(',', $list), TRUE))
            $this->set_error_message($custom_error, self::$err_numeric, $this->value);
        return $this; 
    }

    /**
     * Is validated
     * @return boolean
     */
    public function run() {
        if(empty($this->errors)) return true;
    }

    /**
     * Get Errors
     * @return string
     */
    public function get_errors() {
        if(!$this->run()) return $this->errors;
    }

    /**
     * Display errors
     * @return [type] [description]
     */
    public function errors() {
        if($_POST) {
            if(!empty($this->get_errors())) {
                $errors = '';
                foreach($this->get_errors() as $error){
                    $errors = $errors.'<br>'.html_escape($error);
                }
                $errors = ltrim($errors, '<br>');  
                return $errors;  
            }
        }        
    }
}