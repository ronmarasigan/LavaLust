<?php

class Wrapper extends Formr
{
    public $container;

    public function __construct($instance)
    {
        $this->formr = $instance;

        # formatting: inserts a new line (\n)
        $this->nl = $this->formr->_nl(1);
        
        # formatting: inserts a tab (\t)
        $this->t = $this->formr->_t(1);
    }

    # default css classes - go ahead and add/change whatever you like...
    public static function css_defaults()
    {
        $array = [
            'div' => 'div',
            'label' => 'label',
            'input' => 'input',
            'help' => 'help',
            'button' => 'button',
            'warning' => 'warning',
            'error' => 'error',
            'file' => 'file',
            'text-error' => 'text-error',
            'success' => 'success',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'link' => 'link',
            'list-ul' => 'list-ul',
            'list-ol' => 'list-ol',
            'list-dl' => 'list-dl',
            'alert-e' => 'alert-error',
            'alert-w' => 'alert-warning',
            'alert-s' => 'alert-success',
            'alert-i' => 'alert-info'
        ];

        return $array;
    }

    # default field wrapper
    public function default_wrapper($wrapper, $element, $data)
    {
        $return = null;

         # optional: add a comment for easier debugging in the html
        $return .= $this->_nl(2);
        
        if (in_array($data['type'], $this->_input_types('checkbox'))) {
            $return .= $this->_comment($data['id']);
        } else {
            $return .= $this->_comment($data['name']);
        }

        $return .= $this->_nl(1);

         # the types of html tags & lists we'll accept
        $accepted_tags = array('p', 'div', 'ul', 'ol', 'dl');
        $list_tags = array('ul', 'ol', 'dl');

        if ($wrapper['open']) {
            # don't print if using ul, li, dl
            if (!in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['open'];
            }
            $return .= $this->_nl(1);
        }

         # add the list tag if using fastForm
        if (!empty($data['fastform'])) {
            if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
                $return .= '<li>';
            }
            if ($wrapper['type'] == 'dl') {
                $return .= '<dt>';
            }
        }

         # checkboxes and radios
        if (in_array($data['type'], $this->_input_types('checkbox'))) {
            # wrap checkboxes and radios in a label because it's the decent thing to do
            if (!empty($data['label'])) {
                $return .= $this->label_open($data['value'], $data['label'], $data['id']) . "\n\t";
            }
            $return .= $element;
            if (!empty($data['label'])) {
                $return .= ' ' . $this->label_close($data);
            }
        } else {
            # everything else
            if (!empty($data['label'])) {
                $return .= $this->label($data['name'], $data['label'], $data['id']);
                $return .= $this->_nl(1);
            }
            # add the element
            $return .= $element;
        }

        # add a new line
        $return .= $this->_nl(1);

         # close the list tag if using fastForm
        if (!empty($data['fastform'])) {
            if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
                $return .= '</li>';
            }
            if ($wrapper['type'] == 'dl') {
                $return .= '</dt>';
            }
        }

        # close the wrapper
        if ($wrapper['close']) {
            # don't print if using ul, li, dl
            if (!in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['close'];
            }
            $return .= $this->_nl(1);
        }

        return $return;
    }


    # bootstrap 3 css classes
    public static function bootstrap3_css($key = '')
    {
        $array = [
            'div' => 'form-group',
            'label' => 'control-label',
            'input' => 'form-control',
            'file' => 'form-control',
            'help' => 'help-block',
            'button' => 'btn',
            'button-primary' => 'btn btn-primary',
            'warning' => 'has-warning',
            'error' => 'has-error',
            'text-error' => 'text-danger',
            'success' => 'has-success',
            'checkbox' => 'checkbox',
            'checkbox-inline' => 'checkbox-inline',
            'form-check-input' => 'form-check-input',
            'radio' => 'radio',
            'link' => 'alert-link',
            'list-ul' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-dl' => 'list-unstyled',
            'alert-e'=> 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
            'is-invalid' => 'is-invalid',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    # bootstrap 3 field wrapper
    public function bootstrap3($element = '', $data = '')
    {
        if (empty($data)) {
            return false;
        }

        # if an ID is not present, create one using the name field
        $data['id'] = $this->formr->make_id($data);

        # set the label array value to null if a label is not present
        if(!isset($data['label'])) {
            $data['label'] = null;
        }

        $return = null;

        if ($data['type'] == 'checkbox') {
            # input is a checkbox
            # don't print the label if we're printing an array

            # notice that we're adding an id to the enclosing div, so that you may prepend/append jQuery, etc.
            if (substr($data['value'], -1) != ']') {
                $return = $this->nl.'<div id="_'.$data['id'].'" class="';

                # inline checkbox
                if (!empty($data['checkbox-inline'])) {
                    $return .= static::bootstrap3_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap3_css('checkbox');
                }
            } else {
                $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap3_css('div').'">';
            }
        } elseif ($data['type'] == 'radio') {
            # input is a radio
            # don't print the label if we're printing an array
            if (substr($data['value'], -1) != ']') {
                $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap3_css('radio');

                # inline radio
                if (!empty($data['radio-inline'])) {
                    $return .= static::bootstrap3_css('radio-inline');
                } else {
                    $return .= static::bootstrap3_css('radio');
                }
            } else {
                $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap3_css('div').'">';
            }
        } else {
            $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap3_css('div');
        }

        # concatenate the error class if required
        if ($this->formr->in_errors($data['name'])) {
            $return .= ' '.static::bootstrap3_css('error');
        }

        if (substr($data['value'], -1) != ']') {
            $return .= '">';
        }


        # always add a label...
        # if the label is empty add .sr-only, otherwise add .control-label
        if ($this->formr->is_not_empty($data['label'])) {
            $label_class = static::bootstrap3_css('label');
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if (substr($data['name'], -1) == ']') {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            # we'll add the label text later...
            $return .= $this->t.'<label for="'.$data['id'].'">'.$this->nl;
        } else {
            if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
                # no default class on a checkbox or radio
                # don't insert the label text here; we're doing it elsewhere
                if($this->formr->is_not_empty($data['label'])) {
                    $return .= $this->nl.$this->t.'<label class="'.$label_class.'" for="'.$data['id'].'">'.$this->nl.$this->t;
                }
            } else {
                $return .= $this->nl.$this->t.'<label class="'.$label_class.'" for="'.$data['id'].'">'.$data['label'];
            }
        }

        # add a required field indicator
        if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
            if ($data['type'] != 'checkbox' && $data['type'] != 'radio') {
                $return .= $this->formr->required_indicator;
            }
        }

        # close the label if NOT a checkbox or radio
        if ($data['type'] != 'checkbox' && $data['type'] != 'radio') {
            $return .= '</label>'.$this->nl;
        }

        # add the field element
        $return .= $this->t.$element;

        # inline help text
        if (!empty($data['inline'])) {

            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if (mb_substr($data['inline'], 0, 1) == '[') {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= $this->nl.$this->t.'<p class="'.static::bootstrap3_css('help').'">'.trim($data['inline'], '[]').'</p>';
                }
            } else {
                # show this text on page load
                $return .= $this->nl.$this->t.'<p class="'.static::bootstrap3_css('help').'">'.$data['inline'].'</p>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if (!empty($data['label']) && $data['type'] == 'checkbox' || $data['type'] == 'radio') {
            $return .= ' '.$data['label'];
            # add a required field indicator
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }
            $return .= $this->nl.$this->t.'</label>'.$this->nl;
            $return .= '</div>'.$this->nl;
        } else {
            # close the controls div
            $return .= $this->nl.'</div>'.$this->nl;
        }

        return $return;
    }

    # bootstrap 4 css classes
    public static function bootstrap4_css($key = '')
    {
        $array = [
            'div' => 'form-group',
            'label' => 'control-label',
            'input' => 'form-control',
            'file' => 'form-control-file',
            'help' => 'form-text',
            'button' => 'btn',
            'button-primary' => 'btn btn-primary',
            'warning' => 'has-warning',
            'error' => 'invalid-feedback',
            'text-error' => 'text-danger',
            'success' => 'has-success',
            'checkbox' => 'form-check',
            'checkbox-label' => 'form-check-label',
            'checkbox-inline' => 'form-check form-check-inline',
            'form-check-input' => 'form-check-input',
            'radio' => 'form-check',
            'link' => 'alert-link',
            'list-ul' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-dl' => 'list-unstyled',
            'alert-e'=> 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
            'is-invalid' => 'is-invalid',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    # bootstrap 4 field wrapper
    public function bootstrap4($element = '', $data = '')
    {
        if (empty($data)) {
            return false;
        }

        # if an ID is not present, create one using the name field
        $data['id'] = $this->formr->make_id($data);

        # set the label array value to null if a label is not present
        if(!isset($data['label'])) {
            $data['label'] = null;
        }

        # create our $return variable
        $return = null;

        if ($this->formr->type_is_checkbox($data))
        {
            # input is a checkbox or radio
            # don't print the label if we're printing an array
            if (! $this->formr->is_array($data['value']))
            {
                # add an ID to the enclosing div so that we may target it with javascript if required
                $return = $this->nl.'<div id="_'.$data['id'].'" class="';

                # inline checkbox
                if (!empty($data['checkbox-inline'])) {
                    $return .= static::bootstrap4_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap4_css('checkbox');
                }
            } else {
                # open the form group div
                $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap4_css('div').'">';
            }
        } else {
            # open the form group div tag. note that we may be adding additional attributes...
            $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::bootstrap4_css('div');
        }

        if (! $this->formr->is_array($data['value'])) {
            # no additional attributes
            $return .= '">';
        }

        # add the field element here (before the label) if checkbox or radio
        if ($this->formr->type_is_checkbox($data)) {
            $return .= $this->nl.$this->t.$element;
        }

        # if the label is empty add .sr-only, otherwise...
        if ($this->formr->is_not_empty($data['label'])) {
            if($this->formr->type_is_checkbox($data)) {
                $label_class = static::bootstrap4_css('checkbox-label');
            } else {
                $label_class = static::bootstrap4_css('label');
            }
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if ($this->formr->is_array($data['name'])) {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            # we'll add the label text later...
            $return .= $this->t.'<label for="'.$data['id'].'">'.$this->nl;
        } else {
            # we are not in an array
            if ($this->formr->type_is_checkbox($data)) {
                # no default class on a checkbox or radio
                if($this->formr->is_not_empty($data['label'])) {
                    # open the label, but don't insert the label text here; we're doing it elsewhere
                    $return .= $this->nl.$this->t.'<label class="'.$label_class.'" for="'.$data['id'].'">';
                }
            } else {
                # open the label and insert the label text
                $return .= $this->nl.$this->t.'<label class="'.$label_class.'" for="'.$data['id'].'">'.$data['label'];
            }
        }

        # add a required field indicator (*)
        if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
            if (! $this->formr->type_is_checkbox($data)) {
                $return .= $this->formr->required_indicator;
            }
        }

        # close the label if NOT a checkbox or radio
        if (! $this->formr->type_is_checkbox($data)) {
            $return .= '</label>'.$this->nl;
        }

        # add the field element here if NOT a checkbox or radio
        if (! $this->formr->type_is_checkbox($data)) {
            $return .= $this->t.$element;
        }

        # inline help text
        if (!empty($data['inline']))
        {
            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if ($this->formr->is_in_brackets($data['inline'])) {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= $this->nl.$this->t.'<p class="'.static::bootstrap4_css('help').'">'.trim($data['inline'], '[]').'</p>';
                }
            } else {
                # show this text on page load
                $return .= $this->nl.$this->t.'<p class="'.static::bootstrap4_css('help').'">'.$data['inline'].'</p>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if ($this->formr->is_not_empty($data['label']) && $this->formr->type_is_checkbox($data))
        {
            # add label text
            $return .= ' '.$data['label'];
            
            # add a required field indicator (*)
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }

            # close the label tag
            $return .= $this->nl.$this->t.'</label>'.$this->nl;
            
            # close the controls div
            $return .= '</div>'.$this->nl;
        } else {
            # close the controls div with additional formatting
            $return .= $this->nl.'</div>'.$this->nl;
        }

        return $return;
    }
}
