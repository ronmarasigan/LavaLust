<?php

/**
 * Formr
 *
 * a php micro-framework which helps you build and validate web forms quickly and painlessly
 *
 * https://formr.github.io
 *
 * copyright(c) 2013-2020 Tim Gavin
 * https://github.com/timgavin
 *
 * requires php >= 5.6 and gd2 (for uploads)
 *
 **/


# load the 'plugin' classes
if (file_exists(dirname(__FILE__) . '/classes/my.wrappers.php')) {
    # load the custom wrapper class
    require_once 'classes/my.wrappers.php';
} else {
    # load the default wrapper class
    require_once 'lib/class.formr.wrappers.php';
}

if (file_exists(dirname(__FILE__) . '/classes/my.dropdowns.php')) {
    require_once 'classes/my.dropdowns.php';
}

if (file_exists(dirname(__FILE__) . '/classes/my.forms.php')) {
    require_once 'classes/my.forms.php';
}

# load the remaining default classes
require_once 'lib/class.formr.dropdowns.php';
require_once 'lib/class.formr.forms.php';


class Formr
{
    # each of these public properties acts as a 'preference' for Formr
    # and can be defined at instantiation. see documentation for more info.

    # fields are not required by default
    public $required = false;

    # form's ID
    public $id = null;

    # form's name
    public $name = null;

    # default form action (useful with fastform())
    public $action = null;

    # default form method (useful with fastform())
    public $method = 'post';

    # default submit button value
    public $submit = 'Submit';

    # default character set
    public $charset = 'utf-8';

    # default doctype
    public $doctype = 'html';

    # visually lets the user know a field is required inside the field's label tag
    public $required_indicator = '';

    # link from error messages to related fields by setting anchor tags
    public $link_errors = false;

    # inline validation is off by default
    public $inline_errors = false;

    # inline validation CSS class: displays error icon next to form fields
    public $inline_errors_class = 'error_inline';

    # removes all line breaks and minifies code
    public $minify = false;

    # define a session
    public $session = null;

    # use session values in form fields on page load
    public $session_values = null;

    # comment each form field for easier debugging
    public $comments = false;

    # accepted file types/mime types for uploading files
    public $upload_accepted_types = null;
    public $upload_accepted_mimes = null;

    # max file size for uploaded files (2MB)
    public $upload_max_filesize = 2097152;

    # the full path the the directory in which we're uploading files
    public $upload_directory = null;

    # rename a file after upload
    public $upload_rename = null;

    # resize images after upload
    public $upload_resize = null;

    # init the $uploads property
    public $uploads = true;

    # sanitize input with HTMLPurifier
    public $html_purifier = SYSTEM_DIR . 'libraries/Escaper/HTMLPurifier.php';

    #xss on?
    public $xss_clean = FALSE;

    # suppress Formr's validation error messages and only display your own
    public $custom_validation_messages = false;

    # create an empty errors array for form validation
    public $errors = array();

    # put default class names into an array
    public $controls = array();

    # default wrapper types which Formr supports
    public $default_wrapper_types = array('div', 'p', 'ul', 'ol', 'dl', 'li');


    # default string delimiters
    # $delimiter[0] is for separating field values in fastform()
    # $delimiter[1] is for parsing values within fastform() strings and the post() validation rules
    # example : input_text('Name $delimiter[0] Label $delimiter[0] Value[Value1 $delimiter[1] Value2 $delimiter[1] Value3 ]');
    # example : form->post('email','Email','valid_email $delimiter[1] min[3] $delimiter[1] max[60]')
    private $delimiter = array(',', '|');

    # we don't want to create form attributes from these keywords if they're in the $data array
    private $no_keys = array('string', 'checked', 'selected', 'required', 'inline', 'label', 'fastform', 'options', 'group', 'multiple');

    function __construct($wrapper = '')
    {
        # determine our field wrapper and CSS classes

        if (!$wrapper) {
            # no wrapper, default divs and css
            $this->wrapper = '';
        } else {
            # user-defined wrapper
            $this->wrapper = strtolower($wrapper);

            # default bootstrap to version 4
            if($wrapper == 'bootstrap') {
                $this->wrapper = 'bootstrap4';
            }
            
            $wrapper_css = $this->wrapper . '_css';
        }


        if (!$this->wrapper || in_array($this->wrapper, $this->default_wrapper_types)) {
            # use default css
            $this->controls = Wrapper::css_defaults();
        } else {
            # custom wrapper/control types
            try {
                # check the Controls class for the supplied method
                $method = new ReflectionMethod('wrapper::' . $wrapper_css);
                if ($method->isStatic()) {
                    $this->controls = Wrapper::$wrapper_css();
                }
            } catch (ReflectionException $e) {
                #   method does not exist, spit out error and set default controls
                echo '<h5 style="color:red">' . $e->getMessage() . '.</h5><p>If you are using custom wrappers, please make sure the custom wrapper file is named "my.wrappers.php". If you are NOT using custom wrappers, please make sure a file does not exist at "classes/my.wrappers.php".</p>';
                $this->controls = Wrapper::css_defaults();
            }
        }
    }

    # HELPERS & UTILITY
    public function printr($data)
    {
        # aids in debugging by not making you have to type all of
        # this nonsense out each time you want to print_r() something
        if ($data === 'post') {
            echo '<tt><pre>';
            print_r($_POST);
            echo '</pre></tt>';
        } elseif ($data === 'get') {
            echo '<tt><pre>';
            print_r($_GET);
            echo '</pre></tt>';
        } else {
            echo '<tt><pre>';
            print_r($data);
            echo '</pre></tt>';
        }
    }

    # alias of printr() for Laravel users
    function dd($data)
    {
        return $this->printr($data);
    }

    public function form_info()
    {
        # prints the current form settings

        # set some defaults
        $info = array(
            'Form ID' => '',
            'Form name' => '',
            'Form method' => '',
            'Charset' => 'utf-8',
            'All Fields Required' => 'FALSE',
            'Link to Error' => 'FALSE',
            'Inline Validation' => 'FALSE',
            'Required Indicator' => '',
            'FastForm Wrapper'=> '',
            'HTML Purifier' => 'FALSE',
        );
        $return = '';

        if (!empty($this->id)) {
            $info['Form ID'] = $this->id;
        }

        if (!empty($this->name)) {
            $info['Form name'] = $this->name;
        }

        $info['Form method'] = strtoupper($this->method);

        $info['Charset'] = $this->charset;

        if ($this->required === '*') {
            $info['All Fields Required'] = 'TRUE';
        }

        if ($this->link_errors == true) {
            $info['Link to Error'] = 'TRUE';
        }

        if ($this->inline_errors == true) {
            $info['Inline Validation'] = 'TRUE';
        }

        $info['Required Indicator'] = htmlspecialchars($this->required_indicator);

        $info['FastForm Wrapper'] = htmlspecialchars($this->wrapper);

        if($this->xss_clean == true) {
            if (isset($this->html_purifier)) {
                if (!file_exists($this->html_purifier)) {
                    $info['HTML Purifier'] = 'Can\'t find class at the specified path';
                } else {
                    $info['HTML Purifier'] = 'TRUE';
                }
            }
        }

        $return .= '<table>';
        foreach ($info as $key => $value) {
            $return .= '<tr><td><strong>' . $key . '</strong></td><td>' . $value . '</td></tr>';
        }
        $return .= '</table>';

        $return = str_replace('TRUE', '<span style="color:green">TRUE</span>', $return);
        $return = str_replace('FALSE', '<span style="color:red">FALSE</span>', $return);

        echo '<h3>Form Settings</h3><tt>' . $return . '</tt><br><br><br>';
    }

    public function submit()
    {
        # checks if submit button was clicked
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            # check if we're using csrf
            if (isset($_POST['csrf_token']))
            {
                # check if token in SESSION equals token in POST array
                if (hash_equals($_SESSION['token'], $_POST['csrf_token']))
                {
                    # compare current time to time of token expiration
                    if (time() >= $_SESSION['token-expires']) {
                        $this->add_to_errors('Session has timed out. Please refresh the page.');
                        
                        return false;
                    }
                  } else {
                    $this->add_to_errors('Token mismatch. Please refresh the page.');
                    
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    # alias of submit()
    public function submitted() {
        return $this->submit();
    }

    public function in_errors($key)
    {
        # checks the errors array for the supplied key

        $key = trim($key, '[]');

        if (in_array($key, $this->errors)) {
            return true;
        }

        return false;
    }

    public function in_errors_if($key, $string)
    {
        # if the key is in the errors array, return a user-defined string
        if ($this->in_errors($key)) {
            return $string;
        }
    }

    public function in_errors_else($key, $error_string, $default_string)
    {
        # return a different user-defined string depending on if the field is in the errors array
        if (!$this->in_errors($key)) {
            return $default_string;
        } else {
            return $error_string;
        }
    }

    public function errors()
    {
        # checks the errors array and returns the errors
        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return false;
        }
    }

    public function add_to_errors($str)
    {
        # add a string to the errors array
        array_push($this->errors, $str);
    }

    public function value($name, $value = '')
    {
        # return SESSION value
        if ($this->session) {
            $_SESSION[$this->session][$name] = $this->_clean_value($_POST[$name]);
            return true;
        }

        # return POSTed field value
        if (isset($_POST[$name])) {
            return $this->_clean_value($_POST[$name]);
        } elseif ($value) {
            return $value;
        }

        return false;
    }

    public function slug($str)
    {
        # create a twitter-style username...
        # allow only letters, numbers and underscores
        $return = str_replace('-', '_', $str);
        $return = str_replace(' ', '_', $return);
        $return = preg_replace('/[^A-Za-z0-9_]/', '', $return);
        return $return;
    }

    protected function _generate_hash($length = 32)
    {
        # don't add vowels and we won't get dirty words...
        $chars = 'BCDFGHJKLMNPQRSTVWXYZbcdfghjklmnpqrstvwxyz1234567890';

        # length of character list
        $chars_length = (strlen($chars) - 1);

        # create our string
        $string = $chars[rand(0, $chars_length)];

        # generate random string
        for ($i = 1; $i < $length; $i = strlen($string)) {

            # grab a random character
            $r = $chars[rand(0, $chars_length)];

            # make sure the same characters don't appear next to each other
            if ($r != $string[$i - 1]) $string .= $r;
        }

        return $string;
    }

    protected function _wrapper_type()
    {
        # determines what our field element wrapper will be

        # set the default to none (no wrapper)
        $return['type'] = 'none';
        $return['open'] = '';
        $return['close'] = '';

        if (is_array($this->wrapper)) {

            # the user entered a custom wrapper
            $return['type'] = 'array';
            $return['open'] = $this->wrapper[0];
            $return['close'] = $this->wrapper[1];
            return $return;
        
        } else {

            # use a pre-defined wrapper
            if(! in_array($this->wrapper, ['ul', 'ol', 'dl', 'p', 'div'])) {
                # set the wrapper's name
                $return['type'] = $this->wrapper;
                
                $return['open'] = $return['close'] = null;
                
                return $return;
            }

            # if tags were entered, strip the brackets
            $str = strtolower(trim($this->wrapper, '<>'));

            # wrapper is a list
            if ($str == 'ul') {
                $return['type'] = 'ul';
                $return['open'] = '<ul class="' . $this->controls['list-ul'] . '">';
                $return['close'] = '</ul>';
                return $return;
            }
            if ($str == 'ol') {
                $return['type'] = 'ol';
                $return['open'] = '<ol class="' . $this->controls['list-ol'] . '">';
                $return['close'] = '</ol>';
                return $return;
            }
            if ($str == 'dl') {
                $return['type'] = 'dl';
                $return['open'] = '<dl class="' . $this->controls['list-dl'] . '">';
                $return['close'] = '</dl>';
                return $return;
            }

            # wrapper is inline
            if ($str == 'p') {
                $return['type'] = 'p';
                $return['open'] = '<p>';
                $return['close'] = '</p>';
                return $return;
            }
            if ($str == 'div') {
                $return['type'] = 'div';
                $return['open'] = '<div class="' . $this->controls['div'] . '">';
                $return['close'] = '</div>';
                return $return;
            }
        }
    }

    protected function _wrapper($element, $data)
    {
        # wraps and formats field elements
        # $element is the field element in HTML
        # $data is the $data array containing the element's attributes

        # get the wrapper type
        $wrapper_context = $this->_wrapper_type();

        # enclose the element in a custom field wrapper (such as bootstrap) from the Wrapper class
        if (!empty($this->wrapper) && !in_array($this->wrapper, $this->default_wrapper_types)) {

            # dynamically build the method's name...
            # $method = the method's name in the Wrapper class
            $method = $wrapper_context['type'];

            $wrapper = new Wrapper($this);
            
            return $wrapper->$method($element, $data);
        
        } else {

          # enclose the element in the default wrapper
          $wrapper = new Wrapper($this);
          
          return $wrapper->default_wrapper($wrapper_context, $element, $data);
        }
    }

    protected function _input_types($type)
    {
        # defines input types for use in other methods
        if ($type == 'button') {
            return array('submit', 'reset', 'button');
        }
        if ($type == 'checkbox') {
            return array('checkbox', 'radio');
        }
        if ($type == 'text') {
            return array('text', 'textarea', 'password', 'color', 'email', 'date', 'datetime', 'datetime_local', 'month', 'number', 'range', 'search', 'tel', 'time', 'url', 'week');
        }
    }

    protected function _attributes($data)
    {
        # adds additional attributes and classes to form fields

        $string = $classes = null;

        # remove autocorrect, etc from certain field types
        if ($data['type'] == 'email' || $data['type'] == 'password' || $data['type'] == 'search' || $data['type'] == 'url') {
            $string .= ' autocorrect="off" autocapitalize="off" ';
        }

        # add the button class
        if (in_array($data['type'], $this->_input_types('button'))) {
            # don't add the class if it's empty
            if ($this->controls['button']) {
                $string .= 'class="' . $this->controls['button'] . ' ';
            }
        }

        # add a default class for input types
        if ((in_array($data['type'], $this->_input_types('text'))) || ($data['type'] == 'select')) {

            # don't add the class if it's empty
            if ($this->controls['input']) {
                if (!empty($data['class'])) {
                    $string .= 'class="' . $this->controls['input'] . ' ' . $data['class'] . ' ';
                } else {
                    $string .= ' class="' . $this->controls['input'] . ' ';
                }
            }
        }

        # check if field is required
        if ($this->_check_required($data['name'], $data)) {

            # add the errors class
            if ($this->in_errors($data['name']) && $this->wrapper != 'bootstrap') {
                $classes .= $this->controls['text-error'] . ' ';
            }

            # classes exist. build the string
            if ($classes != null) {
                $string = 'class="' . $classes;
            }
        }

        # working with the 'string' argument
        if (!empty($data['string'])) {

            # see if there are existing classes...
            if (stristr($data['string'], 'class="') && $string != null) {
                # add new classes to existing classes
                return ' ' . str_replace('class="', $string, $data['string']);
            }

            if ($string != null) {
                # classes do not exist, however this field is required, so add them
                return rtrim($string, ' ') . '" ' . $data['string'];
            }

            # just return the string
            return ' ' . $data['string'];
        }

        return false;
    }

    protected function _fix_classes($string, $data)
    {
        # 'fixes' the class attribute
        # merges existing and default classes...

        $return = null;

        if (strpos($string, 'class=') === false) {

            if (empty($data['string'])) {

                if (!empty($this->controls['input'])) {

                    if ($data['type'] != 'submit' && $data['type'] != 'button' && $data['type'] != 'checkbox' && $data['type'] != 'radio') {
                        if($data['type'] == 'file') {
                            # file input gets its own class
                            $return .= ' class="' . $this->controls['file'];
                        } else {
                            $return .= ' class="' . $this->controls['input'];
                        }

                        if ($this->in_errors($data['name'])) {
                            # add bootstrap 4 error class on element
                            if ($this->wrapper == 'bootstrap4') {
                                $return .= ' '.$this->controls['is-invalid'];
                            }
                        }

                        # close the attribute
                        $return .= '"';
                    }

                    # bootstrap 4 inline checkboxes & radios
                    if(isset($data['checkbox-inline']) || ($this->type_is_checkbox($data) && !isset($data['checkbox-inline']))) {
                        if (strpos($this->wrapper, 'bootstrap') !== false) {
                            $return .= ' class="' . $this->controls['form-check-input'] . '"';
                        }
                    }

                    if ($data['type'] == 'submit' || $data['type'] == 'button') {
                        if (strpos($this->wrapper, 'bootstrap') !== false) {
                            if(!$data['string']) {
                                $return .= ' class="' . $this->controls['button-primary'] . '"';
                            } else {
                                $return .= ' class="' . $this->controls['button'] . '"';
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    protected function _set_array_values($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '')
    {
        # puts the entered strings into an array
        if (!is_array($data)) {
            $data = array(
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options
            );
        }
        return $data;
    }

    protected function _clean_value($str = '', $html = false)
    {
        # makes entered values a little safer.

        # this function was left somewhat sparse because i didn't want to assume i knew what kind of data you were going to allow,
        # so i just put in some basic sanitizing functions. if you want more control, just tweak this to your heart's desire! :)

        # Formr can also use HTMLPurifier
        # just download the HTMLPurifier class and drop it in at the top of this script. Formr will do the rest.
        # http://htmlpurifier.org

        # return an empty value
        if ($str == '') {
            return '';
        }
        if($this->xss_clean == true) {
            if ($this->html_purifier && file_exists($this->html_purifier)) {

                # we're using HTML Purifier

                if ($this->charset == strtolower('utf-8')) {
                    # include the HTML Purifier class
                    require_once($this->html_purifier);

                    # set it up using default settings (feel free to alter these if needed)
                    $p_config = HTMLPurifier_Config::createDefault();
                    $purifier = new HTMLPurifier($p_config);
                    return $purifier->purify($str);
                } else {
                    $config = HTMLPurifier_Config::createDefault();
                    $config->set('Core', 'Encoding', $this->charset);
                    $config->set('HTML', 'Doctype', $this->doctype);
                    $purifier = new HTMLPurifier($config);
                }
            }
        } else {

            if (is_string($str)) {

                # trim...
                $str = trim($str);

                # perform basic sanitization...
                if ($html == false) {

                    # strip html tags and prevent against xss
                    $str = strip_tags($str);
                } else {
                    # allow html but make it safer
                    # $str = filter_var($str,FILTER_SANITIZE_SPECIAL_CHARS);
                }
                return $str;
            } else {
                # clean and return the array
                foreach ($str as $value) {

                    if ($html == false) {

                        # strip html tags and prevent against xss
                        $value = strip_tags($value);
                    } else {

                        # allow html but make it safer
                        # $value = filter_var($value,FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
                return $value;
            }
        }
    }

    protected function _parse_fastform_values($key, $data)
    {
        if (!is_array($data)) {
            # the fastform() values are in a string
            # explode them and get each value
            $explode = explode($this->delimiter[0], $data);

            # $data is currently a string, convert it to an array
            $data = array();

            # determind the field's type
            $data = $this->_fastform_define_field_type($key, $data);

            # start populating the $data array
            if (!empty($explode[0])) {
                $data['name'] = trim($explode[0]);
            } else {
                die('error: please provide a name for the <strong>' . $key . '</strong> field');
            }

            if (!empty($explode[1])) {
                $data['label'] = trim($explode[1]);
            } else {
                $data['label'] = '';
            }

            if (!empty($explode[2])) {
                $data['value'] = trim($explode[2]);
            } else {
                $data['value'] = '';
            }

            if (!empty($explode[3])) {
                $data['id'] = trim($explode[3]);
            } else {
                $data['id'] = '';
            }

            if (!empty($explode[4])) {
                $data['string'] = trim($explode[4]);
            } else {
                $data['string'] = '';
            }

            if (!empty($explode[5])) {
                $data['inline'] = trim($explode[5]);
            } else {
                $data['inline'] = '';
            }

            if (!empty($explode[6])) {
                $data['selected'] = trim($explode[6]);
            } else {
                $data['selected'] = '';
            }

            if (!empty($explode[7])) {
                $data['options'] = trim($explode[7]);
            } else {
                $data['options'] = '';
            }

            # hidden types don't really require an id, so we'll insert the id into the value
            if ($data['type'] == 'hidden') {
                $data['value'] = $data['id'];
            }
        } else {

            # determine the field's type
            $data = $this->_fastform_define_field_type($key, $data);

            if (empty($data['id'])) {
                $data['id'] = '';
            }
            if (empty($data['value'])) {
                $data['value'] = '';
            }
            if (empty($data['string'])) {
                $data['string'] = '';
            }
            if (empty($data['label'])) {
                $data['label'] = '';
            }
            if (empty($data['inline'])) {
                $data['inline'] = '';
            }
            if (empty($data['selected'])) {
                $data['selected'] = '';
            }
            if (empty($data['options'])) {
                $data['options'] = '';
            }
        }

        return $data;
    }

    protected function _fastform_define_field_type($key, $data)
    {
        # this method assigns a field type based on the $key's value
        if (!is_array($data)) {
            $data = array();
        }

        # determines if the field name is in the array's key or value
        if ($this->_starts_with($key, 'select') || $this->_starts_with($key, 'state') || $this->_starts_with($key, 'states') || $this->_starts_with($key, 'country')) {
            $data['type'] = 'select';
        } elseif ($this->_starts_with($key, 'submit')) {
            $data['type'] = 'submit';
        } elseif ($this->_starts_with($key, 'reset')) {
            $data['type'] = 'reset';
        } elseif ($this->_starts_with($key, 'button')) {
            $data['type'] = 'button';
        } elseif ($this->_starts_with($key, 'hidden')) {
            $data['type'] = 'hidden';
        } elseif ($this->_starts_with($key, 'password')) {
            $data['type'] = 'password';
        } elseif ($this->_starts_with($key, 'file')) {
            $data['type'] = 'file';
        } elseif ($this->_starts_with($key, 'image')) {
            $data['type'] = 'image';
        } elseif ($this->_starts_with($key, 'checkbox')) {
            $data['type'] = 'checkbox';
        } elseif ($this->_starts_with($key, 'radio')) {
            $data['type'] = 'radio';
        } elseif ($this->_starts_with($key, 'textarea')) {
            $data['type'] = 'textarea';
        } elseif ($this->_starts_with($key, 'color')) {
            $data['type'] = 'color';
        } elseif ($this->_starts_with($key, 'email')) {
            $data['type'] = 'email';
        } elseif ($this->_starts_with($key, 'date')) {
            $data['type'] = 'date';
        } elseif ($this->_starts_with($key, 'datetime')) {
            $data['type'] = 'datetime';
        } elseif ($this->_starts_with($key, 'datetime_local')) {
            $data['type'] = 'datetime_local';
        } elseif ($this->_starts_with($key, 'month')) {
            $data['type'] = 'month';
        } elseif ($this->_starts_with($key, 'number')) {
            $data['type'] = 'number';
        } elseif ($this->_starts_with($key, 'range')) {
            $data['type'] = 'range';
        } elseif ($this->_starts_with($key, 'search')) {
            $data['type'] = 'search';
        } elseif ($this->_starts_with($key, 'tel')) {
            $data['type'] = 'tel';
        } elseif ($this->_starts_with($key, 'time')) {
            $data['type'] = 'time';
        } elseif ($this->_starts_with($key, 'url')) {
            $data['type'] = 'url';
        } elseif ($this->_starts_with($key, 'week')) {
            $data['type'] = 'week';
        } elseif ($this->_starts_with($key, 'label')) {
            $data['type'] = 'label';
        } else {
            $data['type'] = 'text';
        }

        return $data;
    }

    protected function _build_input_groups($data)
    {
        # we're builing a checkbox or radio group based on multiple field names inside $data['value']
        # check if $data['value'] starts with a left bracket
        # if so, we know we have multiple values

        if ($this->is_in_brackets($data['value'])) {

            $return = null;

            # the values are comma-delimited, trim the brackets and break the value apart
            $additional_fields = explode($this->delimiter[1], trim($data['value'], '[]'));

            # output the label text for the group
            if ($this->wrapper == 'bootstrap') {
                # bootstrap control-label
                $return .= '<label class="' . $this->controls['label'] . '">' . $data['label'] . '</label>' . $this->_nl(1);
            } else {
                # 'regular' label
                $return .= $this->label($data) . $this->_nl(1);
            }

            # make sure we're dealing with an array, just to be safe
            if (is_array($additional_fields)) {

                # output the label text for the group
                //$return .= $this->_t(1).$this->label($data);

                # loop through each new field name and print it out - wrapped in a label
                foreach ($additional_fields as $key => $value) {

                    # make the element's label the same as the value
                    $data['label'] = ucwords($value);

                    # add the element's value
                    $data['value'] = $value;

                    # make the ID the element's name so that the label is clickable
                    $data['id'] = $value;

                    $data['group'] = true;

                    # if using bootstrap, wrap the elements in a bootstrap class
                    if ($this->wrapper == 'bootstrap') {
                        $return .= $this->_t(1) . '<div class="' . $this->controls[$data['type']] . '">';
                    }

                    # return the element wrapped in a label
                    $return .= $this->_nl(1) . $this->_t(1) . $this->label_open($data);
                    $return .= $this->_create_input($data);
                    $return .= $this->label_close($data);

                    # close the bootstrap class
                    if ($this->wrapper == 'bootstrap') {
                        $return .= $this->_nl(1) . $this->_t(1) . '</div>' . $this->_nl(1);
                    }
                }
            }

            return $return;
        } else {
            return false;
        }
    }

    protected function _comment($string)
    {
        # creates an HTML comment
        if ($this->minify || !$this->comments) {
            return false;
        } else {
            return '<!-- ' . $string . ' -->';
        }
    }

    protected function _check_filesize($handle)
    {
        $kb = 1024;
        $mb = $kb * 1024;

        if ($handle['size'] > $this->upload_max_filesize) {

            # convert bytes to megabytes because it's more human readable
            $size = round($this->upload_max_filesize / $mb, 2) . ' MB';

            $this->errors['file-size'] = 'File size exceeded. The file can not be larger than ' . $size;

            return true;
        } else {
            return false;
        }
    }

    protected function _get_file_extension($handle)
    {
        # get's a file's extension
        return strtolower(ltrim(strrchr($handle['name'], '.'), '.'));
    }

    protected function _check_upload_accepted_types($handle)
    {
        # get the accepted file types
        # we can check either the extension or the mime type, depending on what the user entered

        if (!$this->_upload_accepted_types() && !$this->_upload_accepted_mimes()) {
            $this->errors['accepted-types'] = 'Oops! You must specify the allowed file types using either $upload_accepted_types or $upload_accepted_mimes.';
            return false;
        }

        # see if it's in the accepted upload types
        if ($this->upload_accepted_types && !in_array($handle['ext'], $this->_upload_accepted_types())) {
            $this->errors['accepted-types'] = 'Oops! The file was not uploaded because it is in an unsupported file type.';
            return false;
        }

        $parts = @getimagesize($handle['tmp_name']);

        # see if it's in the accepted mime types
        if ($this->upload_accepted_mimes && !in_array($parts['mime'], $this->_upload_accepted_mimes())) {
            $this->errors['accepted-types'] = 'Oops! The file was not uploaded because it is an unsupported mime type.';
            return false;
        }

        return true;
    }

    protected function _slug_filename($filename)
    {
        # slug the filename to make it safer
        return strtolower(preg_replace('/[^A-Z0-9._-]/i', '_', $filename));
    }

    protected function _rename_file($handle)
    {
        $new_filename = null;

        # if the file extension is .jpeg, rename to .jpg
        if ($handle['ext'] == 'jpeg') {
            $handle['ext'] = 'jpg';
        }

        # rename the uploaded file with a unique hash
        if (mb_substr($this->upload_rename, 0, 4) == 'hash') {

            # user wants to specify the length of the hash
            if (mb_substr($this->upload_rename, 0, 5) == 'hash[') {

                # get the length of the hash
                $length = trim($this->upload_rename, 'hash[]');

                # rename the file
                $new_filename = $this->_generate_hash($length) . '.' . $handle['ext'];
            } else {
                # rename with the default hash length
                $new_filename = $this->_generate_hash() . '.' . $handle['ext'];
            }
        }

        # rename the uploaded file with a timestamp
        if ($this->upload_rename == 'timestamp') {
            $new_filename = time() . '.' . $handle['ext'];
        }

        # rename the uploaded file with a prepended string
        if (substr($this->upload_rename, 0, 7) == 'prepend') {

            # strip the brackets from our prepend string
            $prepend = trim($this->upload_rename, 'prepend[]');

            # get the file extension
            $ext = '.' . $handle['ext'];

            # remove the extension from the file name
            $name = str_replace($ext, '', $handle['name']);

            $new_filename = $prepend . $name . '.' . $handle['ext'];
        }

        return $new_filename;
    }

    protected function _upload_accepted_types()
    {
        if ($this->upload_accepted_types) {
            # we're allowing jpg, gif and png
            if ($this->upload_accepted_types == 'images') {
                return array('jpg', 'jpeg', 'gif', 'png');
            }

            # explode the accepted file types into an array
            $types = explode(',', $this->upload_accepted_types);
            return $types;
        } else {
            return false;
        }
    }

    protected function _upload_accepted_mimes()
    {
        if ($this->upload_accepted_mimes) {

            # we're allowing jpg, gif and png
            if ($this->upload_accepted_mimes == 'images') {
                return array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');
            }

            # explode the accepted file types into an array
            $types = explode(',', $this->upload_accepted_mimes);
            return $types;
        } else {
            return false;
        }
    }

    protected function _upload_files($name)
    {
        # don't upload if there are form errors
        if (!empty($this->errors)) {
            return false;
        }

        $files = array();

        if (!empty($_FILES[$name]['tmp_name'])) {

            if (is_array($_FILES[$name]['tmp_name']) && count($_FILES[$name]['tmp_name']) > 1) {

                # we're dealing with multiple uploads

                for ($i = 0; $i < count($_FILES[$name]['tmp_name']); $i++) {

                    if (!empty($_FILES[$name]['tmp_name']) && is_uploaded_file($_FILES[$name]['tmp_name'][$i])) {

                        # make for a prettier array and reassign the key/values

                        $handle['key'] = $name;
                        $handle['name'] = $_FILES[$name]['name'][$i];
                        $handle['size'] = $_FILES[$name]['size'][$i];
                        $handle['type'] = $_FILES[$name]['type'][$i];
                        $handle['tmp_name'] = $_FILES[$name]['tmp_name'][$i];

                        # put each array into the $files array
                        array_push($files, $this->_process_image($handle));
                    }
                }

                return $files;
            
            } else {

                # we're dealing with a single upload
                if (!empty($_FILES[$name]['tmp_name']) && is_uploaded_file($_FILES[$name]['tmp_name'])) {

                    # make for a prettier array and reassign the key/values

                    $handle['key'] = $name;
                    $handle['name'] = $_FILES[$name]['name'];
                    $handle['size'] = $_FILES[$name]['size'];
                    $handle['type'] = $_FILES[$name]['type'];
                    $handle['tmp_name'] = $_FILES[$name]['tmp_name'];

                    return $this->_process_image($handle);
                }
            }
        }

        return false;
    }

    protected function _process_image($handle)
    {
        # get the file's extension
        $handle['ext'] = $this->_get_file_extension($handle);

        # see if user wants to rename the file
        if ($this->upload_rename) {
            $handle['name'] = $this->_rename_file($handle);
        } else {
            # if the file extension is .jpeg, rename to .jpg
            if ($handle['ext'] == 'jpeg') {
                $handle['name'] = rtrim($handle['filename'], 'jpeg') . 'jpg';
                $handle['ext'] = 'jpg';
            }
        }

        # make sure file is in the accepted types / accepted mimes array
        if (!$this->_check_upload_accepted_types($handle)) {
            return false;
        }

        # make sure file is not over the max_filesize
        if ($this->_check_filesize($handle)) {
            return false;
        }

        # add a trailing slash if $upload_dir doesn't have one
        if (substr($this->upload_dir, -1) != '/') {
            $this->upload_dir = $this->upload_dir . '/';
        }

        # define the upload path and new file name
        $upload_directory = $this->upload_dir . $handle['name'];

        # move the file to its final destination
        if (move_uploaded_file($handle['tmp_name'], $upload_directory)) {

            # see if we're resizing the image
            if ($this->upload_resize) {
                # loop through the resize array and process each key
                foreach ($this->upload_resize as $resize_key => $resize_values) {
                    $handle[$resize_key] = $this->_resize_image($handle, $resize_key, $resize_values);
                }
            }

            # return the array
            return $handle;
        } else {
            $this->errors['upload-error-move'] = 'There was an error uploading a file: it could not be moved to the final destination. Please check the directory permissions on ' . $this->upload_dir . ' and try again.';
            # we want to display the correct error messages, so we'll return true because the file was uploaded
            return true;
        }
    }

    protected function _resize_image($handle, $resize_key, $resize_values)
    {
        # don't upload if there are form errors
        if (!empty($this->errors)) {
            return false;
        }

        # $upload_resize hasn't been set, so don't resize!
        if (!$this->upload_resize) {
            return false;
        }

        $prepend = '';

        # put the resize values into an array
        $parts = explode(',', $resize_values);

        # get the thumb width
        if (isset($parts[0])) {
            $thumb_width = $parts[0];
            $handle[$resize_key]['width'] = $parts[0];
        } else {
            $this->errors[$handle['key']] = $handle['key'] . ': image not resized. You must specify a numeric width for this resized image.';
            return false;
        }

        # get the thumb height
        if (isset($parts[1])) {
            $thumb_height = $parts[1];
            $handle[$resize_key]['height'] = $parts[1];
        } else {
            $this->errors[$handle['key']] = $handle['key'] . ': image not resized. You must specify a numeric height for this resized image.';
            return false;
        }

        # check if we're prepending something onto the file name
        if (isset($parts[2])) {
            $prepend = $parts[2];
            $handle[$resize_key]['prepend'] = $parts[2];
        }

        # if the user hasn't specified a resize (tn) directory, put resized image in the same directory
        if (isset($parts[3])) {
            $thumb_dir = $parts[3];
            $handle[$resize_key]['dir'] = $parts[3];
        } else {
            $thumb_dir = $this->upload_dir;
        }

        # add a trailing slash if necessary
        if (substr($thumb_dir, 0, -1) != '/') {
            $thumb_dir = $thumb_dir . '/';
        }

        # get the resized image's quality, or default to 80% (JPG only)
        if (isset($parts[4])) {
            $quality = $parts[4];
        } else {
            $quality = 80;
        }


        # load original image and get size and type
        if ($handle['type'] == 'image/jpeg') {
            $original_file = imagecreatefromjpeg($this->upload_dir . $handle['name']);
        }
        if ($handle['type'] == 'image/png') {
            $original_file = imagecreatefrompng($this->upload_dir . $handle['name']);
        }
        if ($handle['type'] == 'image/gif') {
            $original_file = imagecreatefromgif($this->upload_dir . $handle['name']);
        }

        $original_file_width  = imagesx($original_file);
        $original_file_height = imagesy($original_file);

        # calculate resized image's size
        $new_width  = $thumb_width;
        $new_height = floor($original_file_height * ($new_width / $original_file_width));


        # if upload's width or height is larger than specified width or heigh, perform resize
        if ($original_file_width > $new_width || $original_file_height > $new_height) {

            # create a new temporary image
            $tmp_image = imagecreatetruecolor($new_width, $new_height);

            # copy and resize old image into new image
            imagecopyresized($tmp_image, $original_file, 0, 0, 0, 0, $new_width, $new_height, $original_file_width, $original_file_height);

            # save resized image
            if ($handle['type'] == 'image/jpeg') {
                imagejpeg($tmp_image, $thumb_dir . $prepend . $handle['name'], $quality);
            }
            if ($handle['type'] == 'image/png') {
                imagepng($tmp_image, $thumb_dir . $prepend . $handle['name']);
            }
            if ($handle['type'] == 'image/gif') {
                imagegif($tmp_image, $thumb_dir . $prepend . $handle['name']);
            }

            $handle[$resize_key]['name'] = $prepend . $handle['name'];

            # return the file name
            return $handle[$resize_key];
        } else {
            return false;
        }
    }

    protected function _check_required($name)
    {
        # checks the field name to see if that field is required

        $this->required_fields = array();
        // $this->typested_fields = array();

        # all fields are required
        if ($this->required === '*') {
            return true;
        }

        # required fields are set. determine which individual fields are required
        if ($this->required == true) {

            # get any required fields
            $required_fields = explode($this->delimiter[0], rtrim($this->required, '[]'));

            # get any omitted fields inside round brackets ()
            if (preg_match_all('#\((([^()]+|(?R))*)\)#', rtrim($this->required, '[]'), $matches)) {
                $fields = implode(',', $matches[1]);
                $omitted_fields = explode($this->delimiter[0], $fields);
            }

            # if the omitted_fields array in not empty...
            if (!empty($omitted_fields)) {
                if (in_array($name, $omitted_fields)) {
                    # field name is not required
                    return false;
                } else {
                    # everything *but* this field is required
                    return true;
                }
            }

            # field name is required
            if (in_array($name, $required_fields)) {
                return true;
            }
        } else {
            return false;
        }
    }

    protected function _nl($count)
    {
        # adds as many new lines as we need for formatting our html

        if ($this->minify) {
            return false;
        }

        $return = null;

        if ($count > 1) {
            for ($i = 0; $i <= $count; $i++) {
                $return .= "\r\n";
            }
        } else {
            $return = "\r\n";
        }
        return $return;
    }

    protected function _t($count)
    {
        # adds as many tabs as we need for formatting our html

        if ($this->minify) {
            return false;
        }

        $return = null;

        if ($count > 1) {
            for ($i = 0; $i <= $count; $i++) {
                $return .= "\t";
            }
        } else {
            $return = "\t";
        }
        return $return;
    }

    protected function is_not_empty($value)
    {
        # check if value is not empty - including zeros
        if (!empty($value) || (isset($value) && $value === "0") || (isset($value) && $value === 0)) {
            return true;
        } else {
            return false;
        }
    }



    # MESSAGING
    public function messages($open_tag = '', $close_tag = '')
    {
        # this function prints client-side validation error messages to the browser

        $return = null;

        # flash messages
        if (isset($_SESSION['flash'])) {

            if (!empty($_SESSION['flash']['success'])) {
                $this->success_message($_SESSION['flash']['success']);
            }

            if (!empty($_SESSION['flash']['error'])) {
                $this->error_message($_SESSION['flash']['error']);
            }

            if (!empty($_SESSION['flash']['warning'])) {
                $this->warning_message($_SESSION['flash']['warning']);
            }

            if (!empty($_SESSION['flash']['info'])) {
                $this->info_message($_SESSION['flash']['info']);
            }

            $_SESSION['flash'] = NULL;
            unset($_SESSION['flash']);
        }

        # returns a user-defined message
        if (isset($this->message)) {
            return $this->message;
        }

        # returns form errors
        if ($this->inline_errors == false) {

            # if custom HTML tags are not provided, let's set a default
            if (empty($open_tag) && empty($close_tag)) {
                //$open_tag  = '<div class="'.$this->error_class.'">';
                $open_tag  = '<div class="' . $this->controls['alert-e'] . '">';
                $close_tag = '</div>';
            }

            if ($this->errors()) {
                # check if the user has supplied their own error messages
                if (empty($this->error_messages)) {

                    $return .= $open_tag;

                    foreach ($this->errors as $key => $value) {
                        if ($this->link_errors == true) {
                            # user wants to link to the form fields upon error
                            $return .= '<a href="#' . $key . '" class="' . $this->controls['link'] . '">' . $value . '</a><br>';
                        } else {
                            # print the message
                            $return .= $value.'<br>';
                        }
                    }

                    $return .= $close_tag . $this->_nl(1);
                } else {
                    foreach ($this->error_messages as $key => $value) {
                        if ($this->in_errors($key)) {
                            # print the message
                            $return .= $open_tag . $value . $close_tag . $this->_nl(1);
                        }
                    }
                }

                return $return;
            }
        }
    }

    public function warning_message($str, $flash = false)
    {
        if ($flash == true) {
            return $_SESSION['flash']['warning'] = $str;
        }

        $return  = '<div class="' . $this->controls['alert-w'] . '" role="alert">';
        $return .= '    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $return .=      $str;
        $return .= '</div>';
        $this->message = $return;
    }
    public function success_message($str, $flash = false)
    {
        if ($flash == true) {
            return $_SESSION['flash']['success'] = $str;
        }

        $return  = '<div class="' . $this->controls['alert-s'] . '" role="alert">';
        $return .= '    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $return .=      $str;
        $return .= '</div>';
        $this->message = $return;
    }
    public function error_message($str, $flash = false)
    {
        if ($flash == true) {
            return $_SESSION['flash']['error'] = $str;
        }

        $return  = '<div class="' . $this->controls['alert-e'] . '" role="alert">';
        $return .= '    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $return .=      $str;
        $return .= '</div>';
        $this->message = $return;
    }
    public function info_message($str, $flash = false)
    {
        if ($flash == true) {
            return $_SESSION['flash']['info'] = $str;
        }

        $return  = '<div class="' . $this->controls['alert-i'] . '" role="alert">';
        $return .= '    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $return .=      $str;
        $return .= '</div>';
        $this->message = $return;
    }



    # PROCESS POST AND VALIDATE
    public function post($name, $label = '', $rules = '')
    {
        return $this->_post($name, $label, $rules);
    }

    public function get($name, $label = '', $rules = '')
    {
        return $this->_post($name, $label, $rules);
    }

    public function fastpost($name)
    {
        # for the truly lazy! ;)

        # returns an associative array of all posted keys/values, minus the submit button (if it's named 'submit')
        if ($name === 'POST') {
            $keys = '';
            foreach ($_POST as $key => $value) {
                if ($key != $this->submit && $key != 'submit') {
                    # automatically validate based on field name, ie; email = valid_email
                    # if a field name matches, and rules are assigned in the fastpost_rules() method, they'll be applied
                    $keys[$key] = $this->post($key, $key, $this->_fp_rules($key));
                }
            }
            return $keys;
        } else {

            # this part works with the Forms class to allow for quick validation by using pre-built form/validation sets

            # create the array by passing the function name and the validate flag to the Forms class
            $data = Forms::$name('validate');

            # run it through the validate function
            foreach ($data as $key => $value) {

                # $value[0] = custom strings
                # $value[1] = validation rules

                if (isset($value[1])) {
                    # a validation rule was set
                    $keys[$key] = $this->post($key, $value[0], $value[1]);
                } else {
                    # a validation rule was not set
                    $keys[$key] = $this->post($key, $value[0]);
                }
            }
            return $keys;
        }
    }

    protected function _post($name, $label = '', $rules = '')
    {
        # this method processes the $_POST/$_GET values and performs validation (if required)

        # set the variable in which we'll store our $_POST/$_GET data
        $post = null;

        # check for uploaded files first
        if ($this->uploads && !empty($_FILES[$name]['tmp_name'])) {

            if (!$this->upload_dir) {
                $this->errors[$name] = 'Please specify an upload directory.';
                return false;
            }
            if (!$this->upload_accepted_types && !$this->upload_accepted_mimes) {
                if (!empty($data['type']) == 'file') {
                    $this->errors[$name] = 'Please specify the type of file allowed for upload.';
                    return false;
                }
            }
            if ($return = $this->_upload_files($name)) {
                return $return;
            }
        }

        # prevents error classes from contaminating all the fields in a group
        if (stristr($name, '[]')) {
            $name = str_replace('[]', '', $name);
        }

        # process the POST data

        # see if we're dealing with $_POST or $_GET
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST[$name]) && $_POST[$name] != '') {
                $post = $_POST[$name];
            }
        } else {
            if (isset($_GET[$name]) && $_GET[$name] != '') {
                $post = $_GET[$name];
            }
        }

        if (is_array($post)) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                foreach ($_POST[$name] as $key => $value) {
                    #$post[$name][$key] = $this->_clean_value($value);
                    $post[$name][$key] = $value;

                    # add each value to a session
                    if ($this->session) {
                        #$_SESSION[$this->session][$key] = $this->_clean_value($value);
                        $_SESSION[$this->session][$key] = $value;
                    }
                }
            } else {
                foreach ($_GET[$name] as $key => $value) {
                    #$post[$name][$key] = $this->_clean_value($value);
                    $post[$name][$key] = $value;
                    # add each value to a session
                    if ($this->session) {
                        #$_SESSION[$this->session][$key] = $this->_clean_value($value);
                        $_SESSION[$this->session][$key] = $value;
                    }
                }
            }
        } else {
            #$post = $this->_clean_value($post);
            $post = $post;

            # add value to a session
            if ($this->session) {
                $_SESSION[$this->session][$name] = $post;
            }
        }


        # check to see if we have a human readable string and a custom error message string
        if (!empty($label) && stristr($label, $this->delimiter[1])) {

            # we have a custom error message string
            $parts = explode($this->delimiter[1], $label);

            # we'll put the human readable label into the $label property
            $label  = $parts[0];

            # we'll put the custom error message string into the $string property
            $string = $parts[1];

            $data['string'] = $string;
        }


        # check if this is required
        # we can't check if isset($_POST[$name]) because checkboxes and radios don't
        # post if they're not ticked so we have to check everything
        if ($this->_check_required($name) && $post == null) {
            if (!empty($label)) {
                if (!isset($string)) {
                    $this->errors[$name] = '<strong>' . $label . '</strong> is required';
                } else {
                    $this->errors[$name] = $string;
                }
            } else {
                $this->errors[$name] = 'The <strong>' . $name . '</strong> field is required';
            }
        }

        # separate the rules
        $rules = explode($this->delimiter[1], $rules);

        # push 'allow_html' to the back so it processes last
        if (($key = array_search('allow_html', $rules)) !== false) {
            unset($rules[$key]);
            array_push($rules, 'allow_html');
        }

        # get busy validating!
        $return = null;

        # get the $data array ready for the _process_post() method
        $data['post'] = $post;
        $data['label'] = $label;
        $data['name'] = $name;


        # process validation rules
        # if we're posting an array, don't run it through the validation rules because
        # each individual value could break the validation for the entire group
        if (!is_array($data['post'])) {
            foreach ($rules as $rule) {

                # insert the rule into the $data array
                $data['rule'] = $rule;

                # loop through any arrays
                if (is_array($post)) {
                    foreach ($post as $p) {
                        # add the $post value to the $data array
                        $data['post'] = $p;
                        $return .= $this->_process_post($data);
                    }
                } else {
                    # process single field
                    $return  = $this->_process_post($data);
                }
            }
            return $return;
        } else {
            # return the array without validation
            return $post[$name];
        }
    }

    protected function _process_post($data)
    {
        $name = $data['name'];
        $post = $data['post'];
        $label = $data['label'];
        $rule = $data['rule'];

        # allow HTML
        if ($rule == 'allow_html') {
            $html = true;
        } else {
            $html = false;
        }

        if ($post != null) {
            # match one field's contents to another
            if (mb_substr($rule, 0, 7) == 'matches') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match_field = $matches[1][0];

                if ($post != $_POST[$match_field])
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = 'The ' . $label . ' field does not match the ' . $match_field . ' field';
                    }

                    return false;
                }
            }

            # min length
            if (mb_substr($rule, 0, 10) == 'min_length') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match = $matches[1][0];

                if (strlen($post) < $match)
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be at least ' . $match . ' characters';
                    }

                    return false;
                }
            }

            # max length
            if (mb_substr($rule, 0, 10) == 'max_length') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match = $matches[1][0];

                if (strlen($post) > $match)
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be less than ' . $match . ' characters';
                    }

                    return false;
                }
            }

            # exact length
            if (mb_substr($rule, 0, 12) == 'exact_length') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match = $matches[1][0];

                if (strlen($post) != $match)
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be exactly ' . $match . ' characters';
                    }

                    return false;
                }
            }

            # less than (integer)
            if (mb_substr($rule, 0, 9) == 'less_than') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match = $matches[1][0];

                if (!is_numeric($post))
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be a number';
                    }

                    return false;
                }

                if ((int)$post >= (int)$match)
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be less than ' . $match;
                    }

                    return false;
                }
            }

            # greater than
            if (mb_substr($rule, 0, 12) == 'greater_than') {

                preg_match_all("/\[(.*?)\]/", $rule, $matches);
                $match = $matches[1][0];

                if (!is_numeric($post))
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be a number';
                    }

                    return false;
                }

                if ((int)$post <= (int)$match)
                {
                    if($this->_suppress_validation_errors($data)) {
                        $this->errors[$name] = $data['string'];
                    } else {
                        $this->errors[$name] = $label . ' must be greater than ' . $match;
                    }

                    return false;
                }
            }

            # alpha
            if ($rule == 'alpha' && !ctype_alpha($post))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must only contain letters';
                }

                return false;
            }

            # alphanumeric
            if ($rule == 'alpha_numeric' && !ctype_alnum($post))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must only contain letters and numbers';
                }
                
                return false;
            }

            # alpha_dash
            if ($rule == 'alpha_dash' && preg_match('/[^A-Za-z0-9_-]/', $post))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' may only contain letters, numbers, hyphens and underscores';
                }
            }

            # numeric
            if ($rule == 'numeric' && !is_numeric($post))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must be numeric';
                }

                return false;
            }

            # integer
            if ($rule == 'int' && !filter_var($post, FILTER_VALIDATE_INT))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must be a number';
                }

                return false;
            }

            # valid email
            if ($rule == 'valid_email' && !filter_var($post, FILTER_VALIDATE_EMAIL))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = 'Please enter a valid email address';
                }

                return false;
            }

            # valid IP
            if ($rule == 'valid_ip' && !filter_var($post, FILTER_VALIDATE_IP))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must be a valid IP address';
                }

                return false;
            }

            # valid URL
            if ($rule == 'valid_url' && !filter_var($post, FILTER_VALIDATE_URL))
            {
                if($this->_suppress_validation_errors($data)) {
                    $this->errors[$name] = $data['string'];
                } else {
                    $this->errors[$name] = $label . ' must be a valid IP address';
                }

                return false;
            }

            # sanitize string
            if ($rule == 'sanitize_string') {
                $post = filter_var($post, FILTER_SANITIZE_STRING);
            }

            # sanitize URL
            if ($rule == 'sanitize_url') {
                $post = filter_var($post, FILTER_SANITIZE_URL);
            }

            # sanitize email
            if ($rule == 'sanitize_email') {
                $post = filter_var($post, FILTER_SANITIZE_EMAIL);
            }

            # sanitize integer
            if ($rule == 'sanitize_int') {
                $post = filter_var($post, FILTER_SANITIZE_NUMBER_INT);
            }

            # md5
            if ($rule == 'md5') {
                $post = md5($post . $this->salt);
            }

            # sha1
            if ($rule == 'sha1') {
                $post = sha1($post . $this->salt);
            }

            # php's password_hash() function
            if ($rule == 'hash') {
                $post = password_hash($post, PASSWORD_DEFAULT);
            }

            # strip everything but numbers
            if ($rule == 'strip_numeric') {
                $post = preg_replace("/[^0-9]/", '', $post);
            }

            # create twitter-style username
            if ($rule == 'slug') {
                $post = $this->slug($post);
            }
        }

        # run it through the cleaning method as a final step
        return $this->_clean_value($post, $html);
    }

    protected function _fp_rules($key)
    {
        # used during fastpost()
        # if a field name matches, why not do some automatic validation?
        $rules = $this->fastpost_rules();

        if (array_key_exists($key, $rules)) {
            return $rules[$key];
        } else {
            return false;
        }
    }

    protected function fastpost_rules()
    {
        # validation rules for the fastpost() method

        # basically, we're using common field names, and if a posted field name
        # matches one of these field names (keys), the validation rule will be applied

        # 'field name' => 'validation rule'

        $rules = array(
            'email' => 'valid_email',
            'zip' => 'int|min_length[5]|max_length[10]',
            'zip_code' => 'int|min_length[5]|max_length[10]',
            'postal' => 'alphanumeric|min_length[6]|max_length[7]',
            'postal_code' => 'alphanumeric|min_length[6]|max_length[7]',
            'age' => 'int',
            'weight' => 'int',
            'url' => 'valid_url',
            'website' => 'valid_url',
            'ip_address' => 'valid_ip'
        );

        return $rules;
    }

    public function validate($string)
    {
        # even easier and more automatic way to process and validate your form fields
        
        # break apart the comma delimited string of form labels
        $parts = explode(',', $string);

        $array = array();

        foreach ($parts as $label)
        {
            $key = strtolower(str_replace(' ', '_', trim($label)));

            $rules = null;
            
            # we are adding validation rules to this field
            if(preg_match( '!\(([^\)]+)\)!', $label, $match))
            {                
                # get our field's validation rule(s)
                $rules = $match[1];
                
                # get the text before the double pipe for our new label
                $explode = explode('(', $label, 2);
                
                # set our new label text
                $label = $explode[0];
                
                # set our field's name
                $key = strtolower(str_replace(' ', '_', trim($label)));
            }

            if(strpos($key, 'email') !== false) {
                # this is an email address, so let's add the valid_email rule as well
                $array[$key] = $this->post($key, ucwords($key), 'valid_email|' . $rules);
            } else {
                $array[$key] = $this->post($key, $label, $rules);
            }
        }

        return $array;
    }



    # FORM
    protected function _form($data)
    {
        # define the form action
        if (!empty($data['action'])) {
            # use action passed directly to function
            $action = $data['action'];
        } else {
            if (isset($this->action)) {
                # use defined action
                $action = $this->action;
            } else {
                # use the current script
                $action = $_SERVER['SCRIPT_NAME'];
            }
        }


        # the form's method
        if (empty($data['method'])) {
            $data['method'] = 'post';
        }

        # open the tag
        $return = $this->_nl(1) . '<form action="' . $action . '"';

        # add the name
        if (!empty($data['name'])) {
            $return .= ' name="' . $data['name'] . '"';
        } elseif (isset($this->name)) {
            $return .= ' name="' . $this->name . '"';
        }

        # add an ID
        if (empty($data['id']) && !empty($data['name'])) {
            $return .= ' id="' . $data['name'] . '"';
        } elseif (!empty($data['id'])) {
            $return .= ' id="' . $data['id'] . '"';
        } elseif (isset($this->id)) {
            $return .= ' id="' . $this->id . '"';
        }

        # add the method and character set
        $return .= ' method="' . $data['method'] . '" accept-charset="' . $this->charset . '"';

        # print any additional user-defined attributes
        if (!empty($data['string'])) {
            $return .= ' ' . $data['string'];
        }

        # add multipart if required
        if ($data['form_type'] == 'multipart') {
            $return .= ' enctype="multipart/form-data"';
        }

        # close the tag
        $return .= '>' . $this->_nl(1);

        # print hidden input fields if present
        if (!empty($data['hidden'])) {
            foreach ($data['hidden'] as $key => $value) {
                $return .= $this->_nl(1) . $this->input_hidden($key, $value);
            }
            $return .= $this->_nl(1);
        }

        return $return . $this->_nl(1);
    }

    public function form_open($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        if (!$method) {
            if ($this->method == 'get') {
                $method = 'get';
            } else {
                $method = 'post';
            }
        }
        $data = array(
            'form_type' => 'open',
            'action' => $action,
            'method' => $method,
            'name' => $name,
            'id' => $id,
            'string' => $string,
            'hidden' => $hidden
        );

        return $this->_form($data);
    }

    public function form_open_multipart($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        $data = array(
            'form_type' => 'multipart',
            'action' => $action,
            'method'=> $method,
            'name' => $name,
            'id' => $id,
            'string' => $string,
            'hidden' => $hidden
        );

        return $this->_form($data);
    }

    public function form_close()
    {
        return $this->_nl(1) . '</form>' . $this->_nl(1);
    }




    # BUTTONS
    protected function _button($data)
    {
        # build the button tag
        $return  = '<button type="'.$data['type'].'"';

        # insert the button's name
        if (empty($data['name'])) {
            $data['name'] = 'button';
        }

        $return .= ' name="' . $data['name'] . '"';

        if (empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        $return .= ' id="' . $data['id'] . '"';

        if (empty($data['value'])) {
            $data['value'] = 'Submit';
        }

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # add user-entered string and additional attributes
        $return .= $this->_attributes($data);

        # insert the value and close the <button>
        $return .= '>' . $data['value'] . '</button>';

        $element = null;

        if (empty($data['fastform'])) {
            return $this->_wrapper($return, $data);
        } else {
            # we're using fastform(), which will run the element through wrapper()
            return $return;
        }
    }

    public function input_submit($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (!is_array($data)) {

            if (!$data) {
                $data  = 'submit';
            }

            if (!$value) {
                if ($this->submit) {
                    $value = $this->submit;
                } else {
                    $value = 'Submit';
                }
            }

            $data = array(
                'type' => 'submit',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            );
        } else {
            $data['type'] = 'submit';
        }

        return $this->_create_input($data);
    }

    public function input_reset($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'reset',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            );
        } else {
            $data['type'] = 'reset';
        }
        return $this->_create_input($data);
    }

    public function input_button($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'button',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            );
        } else {
            $data['type'] = 'button';
        }

        return $this->_button($data);
    }
    
    public function input_button_submit($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'submit',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            );
        } else {
            $data['type'] = 'submit';
        }

        return $this->_button($data);
    }

    public function inline($name)
    {
        # add div if using inline errors
        if ($this->in_errors($name) && $this->inline_errors) {
            return '<div class="' . $this->inline_errors_class . '"></div>';
        }
    }




    # INPUTS
    protected function _create_input($data)
    {
        if (!isset($_POST)) {
            if (empty($data['value'])) {
                return false;
            }
        }

        # echo an error if the field name hasn't been supplied
        if (!$this->is_not_empty($data['name'])) {
            echo '<p style="color:red">You must provide a name for the <strong>' . $data['type'] . '</strong> element.</p>';
            return false;
        }

        # open the element
        $return = '<input';

        # populate the field's value (on page load) with the session value
        if ($data['value'] == '' && $this->session_values && $this->session && !empty($_SESSION[$this->session][$data['name']])) {
            $data['value'] = $_SESSION[$this->session][$data['name']];
        }

        # if there are form errors, let's insert the posted value into
        # the array so the user doesn't have to enter the value again.
        # also, don't store passwords; always make the user re-type the password.

        if (!in_array($data['type'], $this->_input_types('checkbox'))) {

            # an ID wasn't specified, let's create one using the name
            if (empty($data['id'])) {
                $data['id'] = $data['name'];
            }
            
            if (isset($_POST[$data['name']]) && !empty($_POST[$data['name']]) && $data['type'] != 'password') {

                # run the data through the clean_value function and clean it
                if ($this->session_values && $this->session) {
                    $data['value'] = $_SESSION[$this->session][$data['name']];
                } else {
                    $data['value'] = $this->_clean_value($_POST[$data['name']]);
                }
            }

            # if we're dealing with an input array: such as <input type="text" name="name[key]">
            # we can print the array's value in a text field, but only with an array key
            # and the array key must match the field's ID - hey, we need *something* to match it with! :P
            if (!empty($_POST) && $data['type'] != 'file' && $data['type'] != 'submit' && $data['type'] != 'reset') {
                # tells us we're dealing with an array because of the trailing bracket ]
                if (substr(rtrim($data['name']), -1) == ']') {

                    # get the array key from between the brackets []
                    preg_match_all('^\[(.*?)\]^', $data['name'], $matches);

                    foreach ($matches[1] as $key) {

                        # strip out the brackets and array key to reveal the field name
                        $string = '[' . $key . ']';
                        $name = str_replace($string, '', $data['name']);

                        # if the POST array key matches the field's id, print the value
                        if ($key == $data['id']) {
                            $data['value'] = $_POST[$name][$key];
                        }
                    }
                }
            }
        
        } else {

            # checkboxes and radios..

            # an ID wasn't specified, let's create one using the value
            if (empty($data['id'])) {
                $data['id'] = $data['value'];
            }

            # print an error message alerting the user this field needs a value
            if (!$this->is_not_empty($data['value'])) {
                echo '<p style="color:red">Please enter a value for the ' . $data['type'] . ': <strong>' . $data['name'] . '</strong></p>';
            }

            # use the field's ID for the label if a radio or checkbox
            if (in_array($data['type'], $this->_input_types('checkbox')) && !empty($data['id'])) {
                $label_for = $data['id'];
            } else {
                $label_for = $data['name'];
            }

            # check the element on initial form load
            if (! $this->submitted()) {
                if(!isset($_POST[$this->_strip_brackets($data['name'])])) {
                    if (!empty($data['selected'])) {
                        if ($data['selected'] == $data['value'] || ($data['selected'] == 'checked' || $data['selected'] == 'selected')) {
                            $return .= ' checked';
                        }
                    }
                }
            } else {
                # check the element after the form has been posted
                if (isset($_POST[$this->_strip_brackets($data['name'])]) && $_POST[$this->_strip_brackets($data['name'])] == $data['value']) {
                    $return .= ' checked';
                }

                # checkbox group
                elseif (!empty($_POST[$this->_strip_brackets($data['name'])]) && is_array($_POST[$this->_strip_brackets($data['name'])])) {
                    foreach ($_POST[$this->_strip_brackets($data['name'])] as $pvalue) {
                        if ($pvalue == $data['value']) {
                            $return .= ' checked';
                        }
                    }
                }
            }
        }


        # loop through the array and print each attribute
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->no_keys)) {
                if($key != 'checkbox-inline') {
                    if ($value != '') {
                        $return .= ' ' . $key . '="' . $value . '"';
                    }
                }
            }
        }

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # an ID wasn't provided; use the name field as the ID
        # do not auto-generate an ID if the field is an array
        if (!$this->is_not_empty($data['id'])) {
            if (substr(rtrim($data['name']), -1) != ']') {
                $return .= ' id="' . $data['name'] . '"';
            }
        }

        if (!empty($data['multiple'])) {
            $return .= ' multiple';
        }

        # add user-entered string and additional attributes
        $return .= $this->_attributes($data);

        # if required
        if ($this->_check_required($data['name']) && $data['type'] != 'submit' && $data['type'] != 'reset') {
            $return .= ' required';
        }

        # insert the closing bracket
        $return .= '>';

        # if using inline validation
        $return .= $this->inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        if (empty($data['fastform'])) {
            # the element is completely built, now all we need to do is wrap it
            return $this->_wrapper($return, $data);
        } else {
            # we're using fastform(), which will run the element through wrapper()
            return $return;
        }
    }

    public function input_text($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'text',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'text';
        }
        return  $this->_create_input($data);
    }

    public function input_hidden($data, $value = '')
    {
        $return = '';

        if (!is_array($data)) {

            # build the element
            $return = '<input type="hidden" name="' . $data . '" id="' . $data . '" value="' . $value . '"';

            # insert the closing bracket
            $return .= '>';
        } else {
            # build the element
            $return .= '<input type="hidden" name="' . $data['name'] . '" id="' . $data['name'] . '" value="' . $data['value'] . '">'; 
        }

        return $return;
    }

    public function input_upload($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'file',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'file';
        }
        return  $this->_create_input($data);
    }

    public function input_file($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'file',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'file';
        }
        return  $this->_create_input($data);
    }

    public function input_upload_multiple($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'file',
                'multiple' => true,
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'file';
            $data['multiple'] = true;
        }
        return  $this->_create_input($data);
    }

    public function input_password($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'password',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'password';
        }
        return  $this->_create_input($data);
    }

    public function input_radio($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'radio',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'radio';
        }
        return  $this->_create_input($data);
    }

    public function input_radio_inline($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'radio',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline,
                'checkbox-inline' => 'inline'
            );
        } else {
            $data['type'] = 'radio';
            $data['checkbox-inline'] = 'inline';
        }
        return  $this->_create_input($data);
    }

    public function input_checkbox($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'checkbox',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'checkbox';
        }
        return  $this->_create_input($data);
    }

    public function input_checkbox_inline($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'checkbox',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline,
                'checkbox-inline' => 'inline'
            );
        } else {
            $data['type'] = 'checkbox';
            $data['checkbox-inline'] = 'inline';
        }
        return  $this->_create_input($data);
    }

    public function input_image($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'image',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'image';
        }
        return  $this->_create_input($data);
    }




    # ADDITIONAL FIELD ELEMENTS
    public function input_color($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'color',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'color';
        }
        return  $this->_create_input($data);
    }
    
    public function input_email($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'email',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'email';
        }
        return  $this->_create_input($data);
    }
    
    public function input_date($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'date',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'date';
        }
        return  $this->_create_input($data);
    }
    
    public function input_datetime($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'datetime',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'datetime';
        }
        return  $this->_create_input($data);
    }
    
    public function input_datetime_local($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'datetime-local',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'datetime-local';
        }
        return $this->_create_input($data);
    }
    
    public function input_month($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'month',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'month';
        }
        return $this->_create_input($data);
    }
    
    public function input_number($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'number',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'number';
        }
        return $this->_create_input($data);
    }
    
    public function input_range($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'range',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'range';
        }
        return $this->_create_input($data);
    }
    
    public function input_search($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'search',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'search';
        }
        return $this->_create_input($data);
    }
    
    public function input_tel($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'tel',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'tel';
        }
        return $this->_create_input($data);
    }
    
    public function input_time($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'time',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'time';
        }
        return $this->_create_input($data);
    }
    
    public function input_url($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'url',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'url';
        }
        return $this->_create_input($data);
    }
    
    public function input_week($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'week',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'week';
        }
        return $this->_create_input($data);
    }

    public function input($data)
    {
        # create inputs directly from arrays

        if(!array_key_exists('type', $data)) {
            return $this->_exception('You must assign a field type to the <code>'.$data['name'].'</code> array');
        }
        
        if ($data['type'] == 'select') {
            return $this->input_select($data);
        } elseif ($data['type'] == 'text') {
            return $this->input_text($data);
        } elseif ($data['type'] == 'password') {
            return $this->input_password($data);
        } elseif ($data['type'] == 'textarea') {
            return $this->input_textarea($data);
        } elseif ($data['type'] == 'file') {
            return $this->input_upload($data);
        } elseif ($data['type'] == 'color') {
            return $this->input_color($data);
        } elseif ($data['type'] == 'email') {
            return $this->input_email($data);
        } elseif ($data['type'] == 'date') {
            return $this->input_date($data);
        } elseif ($data['type'] == 'datetime') {
            return $this->input_datetime($data);
        } elseif ($data['type'] == 'datetime-local') {
            return $this->input_datetime_local($data);
        } elseif ($data['type'] == 'month') {
            return $this->input_month($data);
        } elseif ($data['type'] == 'number') {
            return $this->input_number($data);
        } elseif ($data['type'] == 'range') {
            return $this->input_range($data);
        } elseif ($data['type'] == 'search') {
            return $this->input_search($data);
        } elseif ($data['type'] == 'tel') {
            return $this->input_tel($data);
        } elseif ($data['type'] == 'time') {
            return $this->input_time($data);
        } elseif ($data['type'] == 'url') {
            return $this->input_url($data);
        } elseif ($data['type'] == 'week') {
            return $this->input_week($data);
        }
    }




    # TEXTAREA
    protected function _create_textarea($data)
    {
        # echo an error if the field name hasn't been supplied
        if (!$this->is_not_empty($data['name'])) {
            echo '<p style="color:red">You must provide a name for the <strong>' . $data['type'] . '</strong> element.</p>';
            return false;
        }

        # if ID is empty, create an ID using the name
        if (!$this->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        # open the element
        $return = '<textarea';

        # populate the field's value (on page load) with the session value
        if ($data['value'] == '' && $this->session_values && $this->session && !empty($_SESSION[$this->session][$data['name']])) {
            $data['value'] = $_SESSION[$this->session][$data['name']];
        }

        # loop through the $data array and print each attribute
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->no_keys) && $key != 'type' && $key != 'value') {
                $return .= ' ' . $key . '="' . $value . '"';
            }
        }

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # add user-entered string and additional attributes
        $return .= ' ' . $this->_attributes($data);

        # if required
        if ($this->_check_required($data['name'], $data)) {
            $return .= ' required';
        }

        # close the opening tag
        $return .= '>';

        # insert the posted value if available
        if (isset($_POST[$data['name']]) && !empty($_POST[$data['name']])) {
            $return .= $_POST[$data['name']];
        } else {
            # insert the default value if available
            if ($this->is_not_empty($data['value'])) {
                $return .= $data['value'];
            }
        }

        # insert the closing tag
        $return .= '</textarea>';

        # if using inline validation
        $return .= $this->inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        $element = null;

        if (empty($data['fastform'])) {
            return $this->_wrapper($return, $data);
        } else {
            # we're using fastform(), which will run the element through wrapper()
            return $return;
        }
    }

    public function input_textarea($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'textarea',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            );
        } else {
            $data['type'] = 'textarea';
        }
        return  $this->_create_textarea($data);
    }




    # SELECT MENU
    protected function _create_select($data)
    {
        # echo an error if the field name hasn't been supplied
        if (!$this->is_not_empty($data['name'])) {
            echo '<p style="color:red">You must provide a name for the <strong>' . $data['type'] . '</strong> element.</p>';
            return false;
        }

        # open the element
        $return  = '<select name="' . $data['name'] . '"';

        # if an ID wasn't supplied, create one from the field name
        if (!$this->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        # if we're selecting multiple items
        if (is_array($data['selected']) || isset($data['multiple'])) {
            $return .= ' multiple';
        }

        # add ID
        $return .= ' id="' . $data['id'] . '"';


        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);


        # add user-entered string and additional attributes
        $return .= ' ' . $this->_attributes($data);

        # if required
        if ($this->_check_required($data['name'], $data)) {
            $return .= ' required';
        }

        # close the opening tag
        $return .= '>' . $this->_nl(1);


        # a string was entered, so we'll grab the appropriate function from the Dropdowns class
        if (!empty($data['options']) && is_string($data['options'])) {
            if(isset($data['myarray'])) {
                # we're passing an array in the 9th parameter of the input_select() method
                $data['options'] = $this->_dropdowns($data['options'], $data['myarray']);
            } else {
                $data['options'] = $this->_dropdowns($data['options']);
            }
        }

        # if a default selected="selected" value is defined, use that one and give it an empty value
        # if one is set in an array, insert that one as we loop through it later on
        if (!is_array($data['selected']) && !array_key_exists($data['selected'], $data['options']) && !empty($data['selected'])) {
            $return .= $this->_t(1) . '<option value="">' . $data['selected'] . '</option>';
        }

        # options are user-defined
        # loop through the options array
        foreach ($data['options'] as $key => $value) {
            # if $value is an array, create an optgroup
            if (is_array($value)) {
                $return .= $this->_t(1) . '<optgroup label="' . $key . '">';
                # loop through the array
                foreach ($value as $value => $label) {
                    # if the form has been posted, print selected option
                    if (isset($_POST[$data['name']]) && $_POST[$data['name']] == $value) {
                        $return .= $this->_t(2) . '<option value="' . $value . '" selected="selected">' . $label . '</option>';
                    }
                    # print selected option(s) on form load
                    elseif ($data['selected'] == $value || (is_array($data['selected']) && in_array($value, $data['selected']))) {
                        $return .= $this->_t(2) . '<option value="' . $value . '" selected="selected">' . $label . '</option>';
                    }
                    # print remaining options
                    else {
                        $return .= $this->_t(2) . '<option value="' . $value . '">' . $label . '</option>';
                    }
                }
                $return .= $this->_t(1) . '</optgroup>';
            } else {
                # if the form has been posted, print selected option(s)
                
                # check if the select is an array (key has brackets, e.g; <select name="foo[]">)
                if (isset($_POST[trim($data['name'],'[]')]) && is_array($_POST[trim($data['name'],'[]')]) && in_array($key, $_POST[trim($data['name'],'[]')])) {
                    $return .= $this->_t(2) . '<option value="' . $key . '" selected>' . $value . '</option>' . $this->_nl(1);
                }
                elseif (isset($_POST[$data['name']]) && $_POST[$data['name']] == $key) {
                    $return .= $this->_t(2) . '<option value="' . $key . '" selected>' . $value . '</option>' . $this->_nl(1);
                }
                # print selected option on form load
                elseif (!isset($_POST[$data['name']]) && $data['selected'] === $key || (is_array($data['selected']) && in_array($key, $data['selected']))) {
                    # populate the field's value (on page load) with the session value
                    if ($this->session_values && $this->session && !empty($_SESSION[$this->session][$data['name']])) {
                        if ($_SESSION[$this->session][$data['name']] == $key) {
                            $return .= $this->_t(2) . '<option value="' . $key . '" selected>' . $value . '</option>' . $this->_nl(1);
                        }
                    } else {
                        $return .= $this->_t(2) . '<option value="' . $key . '" selected>' . $value . '</option>' . $this->_nl(1);
                    }
                }
                # print remaining options
                else {
                    # user has entered a value in the 'values' argument
                    if (!isset($_POST[$data['name']]) && $data['value'] === $key) {
                        $return .= $this->_t(2) . '<option value="' . $key . '" selected>' . $value . '</option>' . $this->_nl(1);
                    } else {
                        $return .= $this->_t(2) . '<option value="' . $key . '">' . $value . '</option>' . $this->_nl(1);
                    }
                }
            }
        }

        # close the element
        $return .= $this->_t(1) . '</select>';

        # if using inline validation
        $return .= $this->inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        $element = null;

        if (empty($data['fastform'])) {
            if (!$this->wrapper) {
                if (!empty($data['label'])) {
                    # output the element and label without a wrapper
                    if ($this->comments) {
                        $element .= $this->_nl(1) . '<!-- ' . $data['name'] . ' -->' . $this->_nl(1);
                    }
                    $element .= $this->label($data) . $this->_nl(1);
                    $element .= $return . $this->_nl(1);
                    return $element;
                } else {
                    # just return the element
                    if ($this->comments) {
                        $element .= $this->_nl(1) . '<!-- ' . $data['name'] . ' -->' . $this->_nl(1);
                    }
                    $element .= $return . $this->_nl(1);
                    return  $element;
                }
            } else {
                # wrap the element
                $element .= $return;
                return $this->_wrapper($element, $data);
            }
        } else {
            # we're using fastform(), which will run the element through wrapper()
            return $return;
        }
    }

    protected function _dropdowns($menu, $data = null)
    {
        # this function enables the Dropdowns class to be used as a plugin
        # all we're doing is returning the selected array from the Dropdowns class

        # if needed, strip underscore from the beginning
        $menu = ltrim($menu, '_');

        # load the appropriate function from the Dropdowns class...
        
        # we're passing an array in the 9th parameter of the input_select() method for the MyDropdowns class
        if($data) {
            return Dropdowns::$menu($data);
        }
        
        return Dropdowns::$menu();
    }

    public function input_select($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'select',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options,
                'myarray' => $myarray
            );
        } else {
            $data['type'] = 'select';
        }

        return $this->_create_select($data);
    }

    public function input_select_multiple($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'select',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options,
                'myarray' => $myarray
            );
        } else {
            $data['type'] = 'select';
        }

        $data['multiple'] = 'multiple';

        return $this->_create_select($data);
    }




    # FIELDSET
    public function fieldset_open($legend = '', $string = '')
    {
        $return = '<fieldset';

        if ($string) {
            $return .= ' ' . $string;
        }

        $return .= '>' . $this->_nl(1);

        if ($legend) {
            $return .= '<legend>' . $legend . '</legend>';
        }

        return $return . $this->_nl(1);
    }

    public function fieldset_close($string = '')
    {
        $return = '</fieldset>';

        if ($string) {
            $return .= $string;
        }

        return $return . $this->_nl(1);
    }




    # LABELS
    protected function _create_label($data)
    {
        $return = null;

        # if there's a post error, create an <a> anchor for this field
        if ($this->errors() && $this->link_errors) {
            $return = '<a name="' . $data['name'] . '"></a>';
        }

        if ($this->is_not_empty($data['id']) && !$this->_input_types('checkbox')) {
            $data['id'] = $data['name'];
        }

        # open the element
        $return .= '<label for="' . $data['id'] . '"';

        # add an bootstrap error class if required
        if ($this->in_errors($data['name']) && $this->wrapper == 'bootstrap') {
            $return .= ' class="' . $this->controls['text-error'] . '"';
        }

        # add the ID if available
        //if(!empty($data['id'])) {
        //$return .= ' id="'.$data['id'].'"';
        //}

        # insert the string data if available

        if (!empty($data['string']) && !in_array($data['type'], $this->_input_types('button'))) {
            $return .= ' ' . $data['string'];
        }

        # close the tag
        $return .= '>';

        # add the label text, etc. if not using the label_open() method
        if ($data['label_type'] != 'open' && $data['type']) {

            # don't include label text or indicators if this is a button
            if (!in_array($data['type'], $this->_input_types('button'))) {

                # add the label text
                if ($this->is_not_empty($data['label'])) {
                    $return .= $data['label'];
                }

                # if required, let the user know by adding an asterisk, etc.
                if ($this->_check_required($data['name']) && !empty($data['label'])) {
                    $return .= $this->required_indicator;
                }
            }

            # close the element
            $return .= '</label> ';
        }

        return $return;
    }

    public function label($data, $label = '', $id = '', $string = '')
    {
        if (!is_array($data)) {
            $data = array(
                'type' => 'label',
                'name' => $data,
                'label' => $label,
                'id' => $id,
                'string' => $string,
                'label_type' => 'label'
            );
        } else {
            $data['type'] = 'label';
            $data['label_type'] = 'label';
        }

        return $this->_create_label($data);
    }

    public function label_open($data, $label = '', $id = '', $string = '')
    {
        # opens a <label> tag

        if (!is_array($data)) {
            $data = array(
                'name' => $data,
                'label' => $label,
                'id' => $id,
                'string' => $string,
                'label_type' => 'open'
            );
        } else {
            $data['label_type'] = 'open';
        }

        return $this->_create_label($data);
    }

    public function label_close($data = '')
    {
        # closes a <label> tag
        # this is handy if we want to put our label text *after* the form element

        $return = null;

        if (!is_array($data)) {
            if ($data) {
                $return .= $data;
            }
        } else {
            if ($this->_check_required($data['name']) && $data['type'] != 'radio' && !in_array($data['type'], $this->_input_types('button'))) {
                # we don't want the indicator next to radios and checkboxes if they're in an group/array
                if (empty($data['group'])) {
                    $return .= $this->required_indicator;
                }
            }
            $return .= $data['label'];
        }

        $return .= $this->_nl(1) . '</label>';

        return $return;
    }





    public function create($string, $form = false, $action = null)
    {
        #  SIMPLE FORM CREATION
        # create and wrap inputs using labels as our keys
        
        # set our $return var for later
        $return = null;

        if($form && $action == null) {
            $return .= $this->form_open();
        } else {
            $return .= $this->form_open('', '', $action);
        }
        
        # break apart the comma delimited string of form labels
        $parts = explode(',', $string);

        # loop through each part and set the $data array values
        foreach ($parts as $label) {
            $data = [
                'type' => 'text',
                'name' => strtolower(str_replace(' ', '_', trim($label))),
                'id' => strtolower(str_replace(' ', '_', trim($label))),
                'value' => null,
                'string' => null,
                'label' => trim($label),
                'inline' => null
            ];

            # label string contains the word 'email', use email input type
            if(strpos(strtolower($label), 'email') !== false) {
                $return .= $this->input_email($data);
            }
            elseif(strpos(strtolower($label), '|') !== false) {
                # we want to use an specific input type
                $type = substr($label, strpos($label, '|') + 1);
                
                # correct our label text by removing the | and input type
                $data['label'] = str_replace('|'.$type, '', $label);

                # correct our input's name
                $data['name'] = strtolower(str_replace(' ', '_', trim($data['label'])));
                
                # correct our input's ID
                $data['id'] = strtolower(str_replace(' ', '_', trim($data['label'])));
                
                # define the method's name
                $name = 'input_'.$type;

                # add a default value for checkbox or radio
                if($type == 'checkbox' || $type == 'radio') {
                    $data['value'] = $data['name'];
                }
                
                # return the input
                $return .= $this->$name($data);
            }
            else {
                # default to text type
                $return .= $this->input_text($data);
            }
        }

        if($form) {
            $return .= $this->input_button_submit();
            $return .= $this->form_close();
        }

        return $return;
    }

    public function create_form($string, $action)
    {
        # alias of create(), except opens and closes form tag, plus adds submit button
        
        return $this->create($string, true, $action);
    }





    # FAST FORM
    protected function _faster_form($form_name, $multipart)
    {
        # this method enables the Forms class to be used as a plugin so that we can store
        # arrays of frequently used forms and pass them through the fastform() function

        # create the array by passing the function name to the Forms class
        $data = Forms::$form_name();

        # pass the array to the fastform() method
        if ($multipart) {
            return $this->fastform_multipart($data);
        } else {
            return $this->fastform($data);
        }
    }

    public function fastform($input, $multipart = '')
    {
        # method for automatically building and laying out a form with multiple elements

        if (is_string($input)) {
            # user entered a string and wants to use a pre-built form in the Forms class
            return $this->_faster_form($input, $multipart);
        }

        # build the <form> tag
        if ($multipart) {
            $return = $this->form_open_multipart();
        } else {
            $return = $this->form_open();
        }

        # add a fieldset
        $return .= $this->fieldset_open();

        # lets see if we need to wrap this in a list...
        $wrapper = $this->_wrapper_type();
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol' || $wrapper['type'] == 'dl') {
            $return .= $wrapper['open'];
        }

        # create an empty array outside of looping to store hidden inputs
        $hidden = array();

        # loop through the array and print/process each field value
        foreach ($input as $key => $data) {

            # check if it's required
            $required = $this->_check_required($data);

            # see if the entered values are in a string or an array
            $data = $this->_parse_fastform_values($key, $data);

            # tell other methods we're using fastform()
            $data['fastform'] = true;

            # print out the form fields
            
            if ($data['type'] == 'hidden')
            {
                # we're putting the hidden fields into an array and
                # printing them at the end of the form
                array_push($hidden, $this->input_hidden($data));
            }
            elseif ($data['type'] == 'label')
            {
                $return .= $this->label($data);
            }
            elseif ($data['type'] == 'radio' || $data['type'] == 'checkbox')
            {
                if ($this->is_in_brackets($data['value'])) {

                    # we have a radio/checkbox array
                    # loop through the value in the array, create elements and put them all into one wrapper with one label

                    # put each element value into an array and return them
                    $item = $this->_build_input_groups($data);

                    # strip out the label for the element
                    $data['label'] = '';

                    # wrap the element
                    $return .= $this->_wrapper($item, $data);
                } else {

                    # build each element individually and wrap it in a label
                    $item  = $this->label_open($data);

                    if ($data['type'] == 'radio') {
                        $item .= $this->input_radio($data);
                    } else {
                        $item .= $this->input_checkbox($data);
                    }

                    $item .= $this->label_close($data);

                    # prepare the item for the wrapper function
                    $data['label'] = '';

                    # wrap it
                    $return .= $this->_wrapper($item, $data);
                }
            } elseif ($data['type'] == 'select' || $data['type'] == 'select') {
                $item    = $this->input_select($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'text') {
                $item    = $this->input_text($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'password') {
                $item    = $this->input_password($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'textarea') {
                $item    = $this->input_textarea($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'file') {
                $item    = $this->input_upload($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'color') {
                $item    = $this->input_color($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'email') {
                $item    = $this->input_email($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'date') {
                $item    = $this->input_date($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'datetime') {
                $item    = $this->input_datetime($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'datetime-local') {
                $item    = $this->input_datetime_local($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'month') {
                $item    = $this->input_month($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'number') {
                $item    = $this->input_number($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'range') {
                $item    = $this->input_range($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'search') {
                $item    = $this->input_search($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'tel') {
                $item    = $this->input_tel($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'time') {
                $item    = $this->input_time($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'url') {
                $item    = $this->input_url($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'week') {
                $item    = $this->input_week($data);
                $return .= $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'button') {
                $item    = $this->input_button($data);
                $submit  = $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'submit') {
                # instead of using $item, we set the $submit variable.
                # if $submit isn't set, we'll create a submit button automatically later on...
                $item   = $this->input_submit($data);
                $submit = $this->_wrapper($item, $data);
            } elseif ($data['type'] == 'reset') {
                $item    = $this->input_reset($data);
                $return .= $this->_wrapper($item, $data);
            } else {
                # default to text
                $item    = $this->input_text($data);
                $return .= $this->_wrapper($item, $data);
            }
        }

        # see if a submit button was entered while building the form
        if (isset($submit)) {
            $return .= $submit;
        } else {
            # create a default submit with no options
            $data['type'] = 'submit';
            $data['name'] = 'submit';
            $data['label'] = '';
            $data['value'] = $this->submit;
            $data['id'] = 'submit';
            $data['string'] = '';
            $data['inline'] = '';
            $data['selected'] = '';
            $data['options'] = '';
            $item    = $this->input_submit($data);
            $return .= $this->_wrapper($item, $data);
        }

        # close the list tag
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol' || $wrapper['type'] == 'dl') {
            $return .= $this->_nl(1) . $wrapper['close'];
        }

        # if hidden fields are set, print them now
        if (!empty($hidden)) {
            foreach ($hidden as $hidval) {
                $return .= $hidval."\r\n";
            }
        }

        # close the fieldset
        $return .= $this->fieldset_close();

        # close the <form>
        $return .= $this->form_close();

        return $return;
    }

    public function fastform_multipart($data)
    {
        # for file uploads...
        return $this->fastform($data, true);
    }




    # MISC
    public function heading($name, $string)
    {
        # put your string in here and it'll be highlighted when the field receives an error
        # useful in questionnaires and the like.
        
        if (array_key_exists($name, $this->errors)) {
            return '<h2><span class="error">' . $string . '</span></h2>';
        } else {
            return '<h2>' . $string . '</h2>';
        }
    }

    public function send_email($to, $subject, $message, $from = '', $html = false)
    {
        # really simple method for firing off a quick email
        # something I was playing around with and forgot about...
        # TODO? may add to / improve this in the future

        $headers = $msg = null;

        if ($from) {
            $from = "From: " . $this->_clean_value($from) . "\r\n";
        }

        if ($html) {

            # we're sending an HTML email

            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            if ($from) {
                $headers .= $from;
            }

            $msg .= "<html>\r\n";
            $msg .= "<body>\r\n";
            $msg .= "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\r\n";
        }

        # loop through $_POST and print key => value
        if (strtolower($message) == 'post' || is_array($message)) {

            foreach ($_POST as $key => $value) {

                if ($key != 'submit' && $key != 'button') {

                    # make sure it's a valid email address
                    if ((strpos(strtolower($key), 'email') !== false) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$key] = 'Please enter a valid email address';
                    }

                    # check if required
                    if ($this->_check_required($key)) {

                        # add to errors array
                        if (empty($value)) {
                            $this->errors[$key] = '<strong>' . str_replace('_', ' ', ltrim($key, '_')) . '</strong> is required';
                        }
                    }

                    # if key is prepended with an underscore, replace all underscores with a space
                    # _First_Name becomes First Name

                    if ($key[0] == '_') {
                        $key = str_replace('_', ' ', ltrim($key, '_'));
                    }

                    # if key is an array, print all values

                    if (is_array($value)) {
                        $value = implode(', ',$value);
                    }

                    if ($html) {
                        $msg .= "<tr>\r\n";
                        $msg .= "\t<td><strong>$key:</strong></td>\r\n";
                        $msg .= "\t<td>" . $this->_clean_value($value) . "</td>\r\n";
                        $msg .= "</tr>\r\n";
                    } else {
                        $msg .= $key . ": \t" . $this->_clean_value($value) . "\r\n";
                    }
                }
            }
        } else {
            # message is supplied by user
            $msg .= $message;
        }

        if ($html) {
            $msg .= "</table>\r\n";
            $msg .= "</body>\r\n";
            $msg .= "</html>\r\n";
        }

        # send the email
        if (!$this->errors()) {
            if (mail($to, $subject, $msg, $headers)) {
                return true;
            }
        }

        return false;
    }

    public function send_html_email($to, $subject, $message, $from = '')
    {
        return $this->send_email($to, $subject, $message, $from, true);
    }

    public function get_ip_address($mysql = false)
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        if ($mysql) {
            return ip2long($ip_address);
        } else {
            return $ip_address;
        }
    }
    
    public function make_id($data)
    {
        # create an ID from the element's name attribute (if an ID was not specified)
        
        if($this->is_not_empty($data['id'])) {
            return $data['id'];
        }

        return $data['name'];
    }

    public function insert_required_indicator($data)
    {
        # insert the required_field indicator if applicable
        
        if($this->_check_required($data['name']) && $this->is_not_empty($data['label'])) {
            return $this->required_indicator;
        }
    }
    
    public function type_is_checkbox($data)
    {
        # determines if the element is a checkbox or radio
        
        if($data['type'] == 'checkbox' || $data['type'] == 'radio') {
            return true;
        }

        return false;
    }
    
    public function is_array($data)
    {
        # determines is the element's name is an array
        
        if(substr($data, -1) == ']') {
            return true;
        }

        return false;
    }
    
    public function is_in_brackets($data)
    {
        # determines if the given word is contained in brackets
        
        if(mb_substr($data, 0, 1) == '[') {
            return true;
        }

        return false;
    }

    public function csrf($timeout = 3600)
    {
        # add csrf protection
        # remember to put session_start() at the top of your script!

        if (! $this->submit())
        {
            if (function_exists('mcrypt_create_iv')) {
                $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
            } else {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }

            # put the token into a session
            $_SESSION['token'] = $token;

            # token expires in given number of seconds (default 1 hour)
            $_SESSION['token-expires'] = time() + $timeout;

            return '<input type="hidden" name="csrf_token" value="'.$token.'">';
        }
    }

    public function redirect($url)
    {
        # redirect to the given url after the form has been submitted
        
        header('Location: '.$url);
    }

    private function _starts_with($key, $str)
    {
        # check if a string starts with the given word
        
        return mb_substr($key, 0, strlen($str)) == $str;
    }

    private function _strip_brackets($str) {
        
        # strip brackets from a string
        
        return trim($str, '[]');
    }

    private function _suppress_validation_errors($data)
    {
        # suppress Formr's default validation error messages and only show user-defined messages
        
        if(array_key_exists('string', $data) && $this->custom_validation_messages) {
            return true;
        }

        return false;
    }

    private function _exception($string)
    {
        # working on a better error messaging system; this may change...

        return '<span style="color:red">!! '.$string.'</span><br>';
    }
}