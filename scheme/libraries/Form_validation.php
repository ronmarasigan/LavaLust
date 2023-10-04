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
 * Class Form Validation
 */
class Form_validation {
    /**
     * Reference to the LavaLust instance
     *
     * @var object
     */
    private $LAVA;

    //Default Error Messages
    private static $err_required = '%s is required';
    private static $err_matches = '%s does not match with the other field';
    private static $err_differs = '%s matches with the other field';
    private static $err_is_unique = '%s is not unique';
    private static $err_exact_length = '%s not in exact length';
    private static $err_min_length = 'Please enter more than %d character/s';
    private static $err_max_length = 'Please enter less than %d character/s';
    private static $err_email = '%s contains invalid email address';
    private static $err_alpha = '%s accepts letters only';
    private static $err_alphanum = '%s accepts letters and numbers only';
    private static $err_alphanumspace = '%s accepts letters, numbers and spaces only';
    private static $err_alphaspace = '%s accepts letters and spaces only';
    private static $err_alphanumdash = '%s accepts letters, numbers and dashes only';
    private static $err_numeric = '%s accepts numbers only';
    private static $err_greater_than = 'Please enter a value less than %f';
    private static $err_less_than = 'Please enter a value greater than %f';
    private static $err_greater_than_equal_to = 'Please enter a value less than or equal to %f';
    private static $err_less_than_equal_to = 'Please enter a value greater than or equal to %f';
    private static $err_in_list = '%s is not in the list';
    private static $err_pattern = 'Please is not in %s format';
    private static $err_valid_name = '%s is not a valid name';

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

    /**
     * Hold all errors encountered
     *
     * @var array
     */
    public $errors = array();

    /**
     * POST field names
     *
     * @var array
     */
    private $post_arrays = array();

    /**
     * Name of the input field
     *
     * @var string
     */
    private $name;

    /**
     * Values
     *
     * @var string
     */
    private $value;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->LAVA =& lava_instance();
        foreach($_POST as $key => $value)
        {
            $this->post_arrays[$key] = $value;
        }
    }

    /**
     * Check if from is submitted and not empty
     * 
     * @return Bool
     */
    public function submitted()
    {
        return !empty($_POST) ? TRUE : FALSE;
    }

    /**
     * Setting up error message
     * @param string $custom
     * @param string $default
     * @param string $params
     */
    public function set_error_message($custom, $default, $params = NULL)
    {
        if(empty($custom))
                $this->errors[] = sprintf($default, $params);
            else
                $this->errors[] = $custom;
    }

    /**
     * Name
     *
     * @param string $name
     * @return void
     */
    public function name($name)
    {
        if (strpos($name, '|') !== false)
        {
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
     * @return void
     */
    public function pattern($name, $custom_error)
    {
        if($name == 'array')
        {
            if(!is_array($this->value))
            {
                $this->set_error_message($custom_error, self::$err_pattern, $this->name);
            }
        } else {
            $regex = '/^('.$this->patterns[$name].')$/u';
            if($this->value != '' && !preg_match($regex, $this->value))
            {
                $this->set_error_message($custom_error, self::$err_pattern, $this->name);
            }           
        }
        return $this;
    }

    /**
     * Custom Pattern
     *
     * @param string $pattern
     * @param string $custom_error
     * @return void
     */
    public function custom_pattern($pattern, $custom_error)
    {   
        $regex = '/^('.$pattern.')$/u';
        if($this->value != '' && !preg_match($regex, $this->value))
        {
            $this->set_error_message($custom_error, self::$err_pattern, $this->name);
        }
        return $this;
    }

    /**
     * Check if required field
     *
     * @param string $err Custom Error
     * @return void
     */
    public function required($custom_error = '')
    {     
        if(($this->value == '' || $this->value == null))
        {
            $this->set_error_message($custom_error, self::$err_required, $this->name);
        }            
        return $this;  
    }

    /**
     * Check if current field match the other field
     * 
     * @param  string $field
     * @param  string $err   Custom Error
     * @return void
     */
    public function matches($field, $custom_error = '')
    {
        if($this->value !== $this->post_arrays[$field])
        {
            $this->set_error_message($custom_error, self::$err_matches, $this->name);
        }
        return $this;
    }

    /**
     * Check if current field differs from other field
     * 
     * @param  string $field
     * @param  string $err   Custom Error
     * @return void
     */
    public function differs($field, $custom_error = '')
    {
        if($this->value === $this->post_arrays[$field])
        {
            $this->set_error_message($custom_error, self::$err_differs, $this->name);
        }
        return $this;
    }

    /**
     * Is Unique
     *
     * Check if the input value doesn't already exist
     * in the specified database field.
     *
     * @param   string  $str
     * @param   string  $field
     * @return  void
     */
    public function is_unique($table, $field, $str,  $custom_error = '')
    {
        $this->LAVA->call->database();
        $this->LAVA->db->table($table)->where($field, $str)->limit(1)->get();
        if($this->LAVA->db->row_count() !== 0)
        {
            $this->set_error_message($custom_error, self::$err_is_unique, $this->name);
        } 
        return $this;
    }

    /**
     * Exact length
     *
     * @param int $length
     * @param string $custom_error
     * @return void
     */
    public function exact_length($length, $custom_error = '')
    {
        if (! is_numeric($length)) 
        {
            return FALSE;
        }

        if(strlen($this->value) === (int) $length)
        {
            $this->set_error_message($custom_error, self::$err_exact_length, $length);
        }
        return $this;
    }

    /**
     * Minimum length
     *
     * @param int $length
     * @param string $custom_error
     * @return void
     */
    public function min_length($length, $custom_error = '')
    {
        if (! is_numeric($length)) 
        {
            return FALSE;
        }

        if(strlen($this->value) < (int) $length)
        {
            $this->set_error_message($custom_error, self::$err_min_length, $length);
        }
        return $this;
    }

    /**
     * Max length
     *
     * @param int $length
     * @param string $custom_error
     * @return void
     */
    public function max_length($length, $custom_error = '')
    {
        if (! is_numeric($length))
        {
            return FALSE;
        }

        if(strlen($this->value) > (int) $length)
        {
            $this->set_error_message($custom_error, self::$err_max_length, $length);
        }
        return $this;       
    }

    /**
     * Valid email
     *
     * @param string $custom_error
     * @return void
     */
    public function valid_email($custom_error = '')
    {
        if(!filter_var($this->value, FILTER_VALIDATE_EMAIL))
        {
            $this->set_error_message($custom_error, self::$err_email, $this->name);
        } 
        return $this;
    }

    /**
     * Alpha characters
     *
     * @param string $custom_error
     * @return void
     */
    public function alpha($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(! ctype_alpha($this->value))
            {
                $this->set_error_message($custom_error, self::$err_alpha, $this->name);
            }
        }   
        return $this; 
    }

    /**
     * Alpha-numeric 
     *
     * @param string $custom_error
     * @return void
     */
    public function alpha_numeric($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(! ctype_alnum((string) $this->value))
            {
                $this->set_error_message($custom_error, self::$err_alphanum, $this->name);
            } 
        }   
        return $this; 
    }

    /**
     * Alpha-numeric and spaces
     *
     * @param string $custom_error
     * @return void
     */
    public function alpha_numeric_space($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(! preg_match('/^[A-Z0-9 ]+$/i', $this->value))
            {
                $this->set_error_message($custom_error, self::$err_alphanumspace, $this->name);
            }
        }  
        return $this; 
    }

    /**
     * Alha characters and spaces
     *
     * @param string $custom_error
     * @return void
     */
    public function alpha_space($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(! preg_match('/^[A-Z ]+$/i', $this->value))
            {
                $this->set_error_message($custom_error, self::$err_alphaspace, $this->name);
            }   
        }
        return $this; 
    }

    /**
     * Alpha-numeric and dashes
     *
     * @param string $custom_error
     * @return void
     */
    public function alpha_numeric_dash($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(!preg_match('/^[a-z0-9_-]+$/i', $this->value))
            {
                $this->set_error_message($custom_error, self::$err_alphanumdash, $this->name);
            }
        }
        return $this; 
    }

   /**
    * Numeric
    *
    * @param string $custom_error
    * @return void
    */
    public function numeric($custom_error = '')
    {
        if(($this->value != '' || $this->value != null))
        {
            if(! preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $this->value))
            {
                $this->set_error_message($custom_error, self::$err_numeric, $this->name);
            }
        }  
        return $this; 

    }

    /**
     * Greater than
     *
     * @param mixed $min
     * @param string $custom_error
     * @return void
     */
    public function greater_than($min, $custom_error = '')
    {
        if(! is_numeric($this->value))
        {
            return FALSE;
        }
        if($this->value < $min)
        {
            $$this->set_error_message($custom_error, self::$err_greater_than, $min);
        }
        return $this; 
    }

    /**
     * Greater than or equal to
     *
     * @param mixed $min
     * @param string $custom_error
     * @return void
     */
    public function greater_than_equal_to($min, $custom_error = '')
    {
        if(! is_numeric($this->value))
        {
            return FALSE;
        }
        if($this->value <= $min)
        {
            $$this->set_error_message($custom_error, self::$err_greater_than_equal_to, $min);
        }
        return $this; 
    }

    /**
     * Less than
     *
     * @param mixed $max
     * @param string $custom_error
     * @return void
     */
    public function less_than($max, $custom_error = '')
    {
        if(! is_numeric($this->value))
        {
            return FALSE;
        }
        if($this->value > $max)
        {
            $this->set_error_message($custom_error, self::$err_less_than, $max);
        }
        return $this; 
    }

    /**
     * Less than or equal to
     *
     * @param mixed $max
     * @param string $custom_error
     * @return void
     */
    public function less_than_equal_to($max, $custom_error = '')
    {
        if(! is_numeric($this->value))
        {
            return FALSE;
        }
        if($this->value >= $max)
        {
            $this->set_error_message($custom_error, self::$err_less_than_equal_to, $max);
        }
        return $this; 
    }

    /**
     * In list
     *
     * @param string $list
     * @param string $custom_error
     * @return void
     */
    public function in_list($list, $custom_error = '')
    {
        if(! in_array($this->value, explode(',', $list), TRUE))
        {
            $this->set_error_message($custom_error, self::$err_in_list, $this->value);
        }
        return $this; 
    }

    /**
     * Check if format of Person name is valid
     *
     * @param string $custom_error
     * @return void
     */
    public function valid_name($custom_error = '') {
        if (!preg_match('/^[\p{L} ]+$/u', $this->value)) {
            $this->set_error_message($custom_error, self::$err_valid_name, $this->value);
        }
        return $this;
    }

    /**
     * Is validated
     * 
     * @return bool
     */
    public function run() {
        if(empty($this->errors))
        {
            return TRUE;
        }
    }

    /**
     * Get Errors
     * 
     * @return array
     */
    public function get_errors() {
        if(! $this->run())
        {
            return $this->errors;
        }
    }

    /**
     * Show/Display all errors
     *
     * @return string
     */
    public function errors() {
        if($_POST)
        {
            if(!empty($this->get_errors()))
            {
                $errors = '';
                foreach($this->get_errors() as $error)
                {
                    $errors = ! empty($errors) ? $errors.'<br>'.html_escape($error) : html_escape($error);
                }
                return $errors;  
            }
        } 
    }
}