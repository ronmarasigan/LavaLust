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

if ( ! function_exists('form_open'))
{
    /**
     * Form Declaration
     *
     * Creates the opening portion of the form.
     *
     * @param   string  the URI segments of the form destination
     * @param   array   a key/value pair of attributes
     * @param   array   a key/value pair hidden data
     * @return  string
     */
    function form_open($action = '', $attributes = array(), $hidden = array())
    {
        $LAVA =& lava_instance();
        // If no action is provided then set to the current url
        if ( ! $action)
        {
            $action = site_url(ltrim(html_escape($_SERVER['REQUEST_URI']), '/'));
        }
        // If an action is not a full URL then turn it into one
        elseif (strpos($action, '://') === FALSE)
        {
            $action = site_url($action);
        }

        $attributes = _attributes_to_string($attributes);

        if (stripos($attributes, 'method=') === FALSE)
        {
            $attributes .= ' method="post"';
        }

        if (stripos($attributes, 'accept-charset=') === FALSE)
        {
            $attributes .= ' accept-charset="'.strtolower(config_item('charset')).'"';
        }

        $form = '<form action="'.$action.'"'.$attributes.">\n";

        if (is_array($hidden))
        {
            foreach ($hidden as $name => $value)
            {
                $form .= '<input type="hidden" name="'.$name.'" value="'.html_escape($value).'" />'."\n";
            }
        }

        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
        if (config_item('csrf_protection') === TRUE && strpos($action, BASE_URL) !== FALSE && ! stripos($form, 'method="get"'))
        {
            // Prepend/append random-length "white noise" around the CSRF
            // token input, as a form of protection against BREACH attacks
            if (FALSE !== ($noise = random_bytes(1)))
            {
                list(, $noise) = unpack('c', $noise);
            }
            else
            {
                $noise = mt_rand(-128, 127);
            }

            // Prepend if $noise has a negative value, append if positive, do nothing for zero
            $prepend = $append = '';
            if ($noise < 0)
            {
                $prepend = str_repeat(" ", abs($noise));
            }
            elseif ($noise > 0)
            {
                $append  = str_repeat(" ", $noise);
            }
            
            $form .= sprintf(
                '%s<input type="hidden" name="%s" value="%s" />%s%s',
                $prepend,
                $LAVA->security->get_csrf_token_name(),
                $LAVA->security->get_csrf_hash(),
                $append,
                "\n"
            );
        }

        return $form;
    }
}

if ( ! function_exists('form_open_multipart'))
{
    /**
     * Form Declaration - Multipart type
     *
     * Creates the opening portion of the form, but with "multipart/form-data".
     *
     * @param   string  the URI segments of the form destination
     * @param   array   a key/value pair of attributes
     * @param   array   a key/value pair hidden data
     * @return  string
     */
    function form_open_multipart($action = '', $attributes = array(), $hidden = array())
    {
        if (is_string($attributes))
        {
            $attributes .= ' enctype="multipart/form-data"';
        }
        else
        {
            $attributes['enctype'] = 'multipart/form-data';
        }

        return form_open($action, $attributes, $hidden);
    }
}

if ( ! function_exists('form_hidden'))
{
    /**
     * Hidden Input Field
     *
     * Generates hidden fields. You can pass a simple key/value string or
     * an associative array with multiple values.
     *
     * @param   mixed   $name       Field name
     * @param   string  $value      Field value
     * @param   bool    $recursing
     * @return  string
     */
    function form_hidden($name, $value = '', $recursing = FALSE)
    {
        static $form;

        if ($recursing === FALSE)
        {
            $form = "\n";
        }

        if (is_array($name))
        {
            foreach ($name as $key => $val)
            {
                form_hidden($key, $val, TRUE);
            }

            return $form;
        }

        if ( ! is_array($value))
        {
            $form .= '<input type="hidden" name="'.$name.'" value="'.html_escape($value)."\" />\n";
        }
        else
        {
            foreach ($value as $k => $v)
            {
                $k = is_int($k) ? '' : $k;
                form_hidden($name.'['.$k.']', $v, TRUE);
            }
        }

        return $form;
    }
}

if ( ! function_exists('form_input'))
{
    /**
     * Text Input Field
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_input($data = '', $value = '', $extra = '')
    {
        $defaults = array(
            'type' => 'text',
            'name' => is_array($data) ? '' : $data,
            'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

if ( ! function_exists('form_password'))
{
    /**
     * Password Field
     *
     * Identical to the input function but adds the "password" type
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_password($data = '', $value = '', $extra = '')
    {
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'password';
        return form_input($data, $value, $extra);
    }
}

if ( ! function_exists('form_upload'))
{
    /**
     * Upload Field
     *
     * Identical to the input function but adds the "file" type
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_upload($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'file', 'name' => '');
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'file';

        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

if ( ! function_exists('form_textarea'))
{
    /**
     * Textarea field
     *
     * @param   mixed   $data
     * @param   string  $value
     * @param   mixed   $extra
     * @return  string
     */
    function form_textarea($data = '', $value = '', $extra = '')
    {
        $defaults = array(
            'name' => is_array($data) ? '' : $data,
            'cols' => '40',
            'rows' => '10'
        );

        if ( ! is_array($data) OR ! isset($data['value']))
        {
            $val = $value;
        }
        else
        {
            $val = $data['value'];
            unset($data['value']); // textareas don't use the value attribute
        }

        return '<textarea '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
            .html_escape($val)
            ."</textarea>\n";
    }
}

if ( ! function_exists('form_multiselect'))
{
    /**
     * Multi-select menu
     *
     * @param   string
     * @param   array
     * @param   mixed
     * @param   mixed
     * @return  string
     */
    function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
    {
        $extra = _attributes_to_string($extra);
        if (stripos($extra, 'multiple') === FALSE)
        {
            $extra .= ' multiple="multiple"';
        }

        return form_dropdown($name, $options, $selected, $extra);
    }
}

if ( ! function_exists('form_dropdown'))
{
    /**
     * Drop-down Menu
     *
     * @param   mixed   $data
     * @param   mixed   $options
     * @param   mixed   $selected
     * @param   mixed   $extra
     * @return  string
     */
    function form_dropdown($data = '', $options = array(), $selected = array(), $extra = '')
    {
        $defaults = array();

        if (is_array($data))
        {
            if (isset($data['selected']))
            {
                $selected = $data['selected'];
                unset($data['selected']); // select tags don't have a selected attribute
            }

            if (isset($data['options']))
            {
                $options = $data['options'];
                unset($data['options']); // select tags don't use an options attribute
            }
        }
        else
        {
            $defaults = array('name' => $data);
        }

        is_array($selected) OR $selected = array($selected);
        is_array($options) OR $options = array($options);

        // If no selected state was submitted we will attempt to set it automatically
        if (empty($selected))
        {
            if (is_array($data))
            {
                if (isset($data['name'], $_POST[$data['name']]))
                {
                    $selected = array($_POST[$data['name']]);
                }
            }
            elseif (isset($_POST[$data]))
            {
                $selected = array($_POST[$data]);
            }
        }

        $extra = _attributes_to_string($extra);

        $multiple = (count($selected) > 1 && stripos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

        $form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

        foreach ($options as $key => $val)
        {
            $key = (string) $key;

            if (is_array($val))
            {
                if (empty($val))
                {
                    continue;
                }

                $form .= '<optgroup label="'.$key."\">\n";

                foreach ($val as $optgroup_key => $optgroup_val)
                {
                    $sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
                    $form .= '<option value="'.html_escape($optgroup_key).'"'.$sel.'>'
                        .(string) $optgroup_val."</option>\n";
                }

                $form .= "</optgroup>\n";
            }
            else
            {
                $form .= '<option value="'.html_escape($key).'"'
                    .(in_array($key, $selected) ? ' selected="selected"' : '').'>'
                    .(string) $val."</option>\n";
            }
        }

        return $form."</select>\n";
    }
}

if ( ! function_exists('form_checkbox'))
{
    /**
     * Checkbox Field
     *
     * @param   mixed
     * @param   string
     * @param   bool
     * @param   mixed
     * @return  string
     */
    function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
    {
        $defaults = array('type' => 'checkbox', 'name' => ( ! is_array($data) ? $data : ''), 'value' => $value);

        if (is_array($data) && array_key_exists('checked', $data))
        {
            $checked = $data['checked'];

            if ($checked == FALSE)
            {
                unset($data['checked']);
            }
            else
            {
                $data['checked'] = 'checked';
            }
        }

        if ($checked == TRUE)
        {
            $defaults['checked'] = 'checked';
        }
        else
        {
            unset($defaults['checked']);
        }

        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

if ( ! function_exists('form_radio'))
{
    /**
     * Radio Button
     *
     * @param   mixed
     * @param   string
     * @param   bool
     * @param   mixed
     * @return  string
     */
    function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
    {
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'radio';

        return form_checkbox($data, $value, $checked, $extra);
    }
}

if ( ! function_exists('form_submit'))
{
    /**
     * Submit Button
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_submit($data = '', $value = '', $extra = '')
    {
        $defaults = array(
            'type' => 'submit',
            'name' => is_array($data) ? '' : $data,
            'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

if ( ! function_exists('form_reset'))
{
    /**
     * Reset Button
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_reset($data = '', $value = '', $extra = '')
    {
        $defaults = array(
            'type' => 'reset',
            'name' => is_array($data) ? '' : $data,
            'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults)._attributes_to_string($extra)." />\n";
    }
}

if ( ! function_exists('form_button'))
{
    /**
     * Form Button
     *
     * @param   mixed
     * @param   string
     * @param   mixed
     * @return  string
     */
    function form_button($data = '', $content = '', $extra = '')
    {
        $defaults = array(
            'name' => is_array($data) ? '' : $data,
            'type' => 'button'
        );

        if (is_array($data) && isset($data['content']))
        {
            $content = $data['content'];
            unset($data['content']); // content is not an attribute
        }

        return '<button '._parse_form_attributes($data, $defaults)._attributes_to_string($extra).'>'
            .$content
            ."</button>\n";
    }
}

if ( ! function_exists('form_label'))
{
    /**
     * Form Label Tag
     *
     * @param   string  The text to appear onscreen
     * @param   string  The id the label applies to
     * @param   mixed   Additional attributes
     * @return  string
     */
    function form_label($label_text = '', $id = '', $attributes = array())
    {

        $label = '<label';

        if ($id !== '')
        {
            $label .= ' for="'.$id.'"';
        }

        $label .= _attributes_to_string($attributes);

        return $label.'>'.$label_text.'</label>';
    }
}

if ( ! function_exists('form_fieldset'))
{
    /**
     * Fieldset Tag
     *
     * Used to produce <fieldset><legend>text</legend>.  To close fieldset
     * use form_fieldset_close()
     *
     * @param   string  The legend text
     * @param   array   Additional attributes
     * @return  string
     */
    function form_fieldset($legend_text = '', $attributes = array())
    {
        $fieldset = '<fieldset'._attributes_to_string($attributes).">\n";
        if ($legend_text !== '')
        {
            return $fieldset.'<legend>'.$legend_text."</legend>\n";
        }

        return $fieldset;
    }
}

if ( ! function_exists('form_fieldset_close'))
{
    /**
     * Fieldset Close Tag
     *
     * @param   string
     * @return  string
     */
    function form_fieldset_close($extra = '')
    {
        return '</fieldset>'.$extra;
    }
}

if ( ! function_exists('form_close'))
{
    /**
     * Form Close Tag
     *
     * @param   string
     * @return  string
     */
    function form_close($extra = '')
    {
        return '</form>'.$extra;
    }
}

if ( ! function_exists('_parse_form_attributes'))
{
    /**
     * Parse the form attributes
     *
     * Helper function used by some of the form helpers
     *
     * @param   array   $attributes List of attributes
     * @param   array   $default    Default values
     * @return  string
     */
    function _parse_form_attributes($attributes, $default)
    {
        if (is_array($attributes))
        {
            foreach ($default as $key => $val)
            {
                if (isset($attributes[$key]))
                {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) > 0)
            {
                $default = array_merge($default, $attributes);
            }
        }

        $att = '';

        foreach ($default as $key => $val)
        {
            if ($key === 'value')
            {
                $val = html_escape($val);
            }
            elseif ($key === 'name' && ! strlen($default['name']))
            {
                continue;
            }

            $att .= $key.'="'.$val.'" ';
        }

        return $att;
    }
}

if ( ! function_exists('_attributes_to_string'))
{
    /**
     * Attributes To String
     *
     * Helper function used by some of the form helpers
     *
     * @param   mixed
     * @return  string
     */
    function _attributes_to_string($attributes)
    {
        if (empty($attributes))
        {
            return '';
        }

        if (is_object($attributes))
        {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes))
        {
            $atts = '';

            foreach ($attributes as $key => $val)
            {
                $atts .= ' '.$key.'="'.$val.'"';
            }

            return $atts;
        }

        if (is_string($attributes))
        {
            return ' '.$attributes;
        }

        return FALSE;
    }

    function validation_errors() {
        $LAVA =& lava_instance();
    	$LAVA->call->library('form_validation');
        return $LAVA->form_validation->errors();
    }
}