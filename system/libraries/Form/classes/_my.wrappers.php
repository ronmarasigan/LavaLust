<?php

/*
    this file is where you will put your custom wrappers

    to use this file, rename it from '_my.wrappers.php' to 'my.wrappers.php' (remove the underscore)
    doing that tells Formr to use this file instead of the default wrappers.
    
    follow the naming convention: if you want your wrapper to be named 'mywrapper', then your wrapper 
    function needs to be named 'mywrapper()' and your css function needs to be named 'mywrapper_css()'

*/

class Wrapper extends Formr
{
    public function __construct($instance)
    {
        $this->formr = $instance;

        # for HTML formatting: inserts a new line (\n)
        $this->nl = $this->formr->_nl(1);
        
        # for HTML formatting: inserts a tab (\t)
        $this->t = $this->formr->_t(1);
    }

    # mywrapper css classes
    public static function mywrapper_css($key='')
    {
        # the following block of classes are required by Formr.
        # Formr will take these classes and use them while building the wrapper.
        # Formr will look up the class name by the key so the key name must stay the same,
        # however the values can be whatever you want.
        
        $array = [
            'div' => 'form-group',                      // example: <div class="form-group">
            'label' => 'control-label',                 // example: <label class="control-label">
            'input' => 'form-control',                  // example: <input type="text" class="form-control">
            'file' => 'form-control-file',              // example: <input type="file" class="form-control-file">
            'link' => 'alert-link',                     // example: <div class="alert alert-danger"><a href="..." class="alert-link"></a></div>
            'button' => 'btn',                          // example: <button class="btn">
            'text-error' => 'text-danger',              // example: <span class="text-error">
            'list-ul' => 'list-unstyled',               // example: <ul class="list-unstyled">
            'list-ol' => 'list-unstyled',               // example: <ol class="list-unstyled">
            'list-dl' => 'list-unstyled',               // example: <dl class="list-unstyled">
            'alert-e'=> 'alert alert-danger',           // example: <div class="alert alert-danger">
            'alert-w' => 'alert alert-warning',         // example: <div class="alert alert-warning">
            'alert-s' => 'alert alert-success',         // example: <div class="alert alert-success">
            'alert-i' => 'alert alert-info',            // example: <div class="alert alert-info">

            # feel free to add more classes below
            # in this example we're adding additional Bootstrap classes

            'checkbox' => 'form-check',
            'checkbox-label' => 'form-check-label',
            'checkbox-inline' => 'form-check form-check-inline',
            'help' => 'help-block',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    # this is a copy of the bootstrap4 wrapper; go ahead and play with it; it's yours now, you earned it. :)
    public function mywrapper($element='', $data='')
    {
        # enter the name of your css function so we can use it when calling classes
        $css = 'mywrapper_css';
        
        if (empty($data)) {
            return false;
        }

        # if an ID is not present, create one using the name field
        $data['id'] = $this->formr->make_id($data);

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
                    $return .= static::$css('checkbox-inline');
                } else {
                    $return .= static::$css('checkbox');
                }
            } else {
                # open the form group div
                $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::$css('div').'">';
            }
        } else {
            # open the form group div tag. note that we may be adding additional attributes...
            $return = $this->nl.'<div id="_'.$data['id'].'" class="'.static::$css('div');
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
            if ($this->formr->type_is_checkbox($data)) {
                $label_class = static::$css('checkbox-label');
            } else {
                $label_class = static::$css('label');
            }
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if ($this->formr->is_array($data['name'])) {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            $return .= $this->t.'<label for="'.$data['id'].'">'.$data['label'].$this->nl;
        } else {
            # we are not in an array
            if ($this->formr->type_is_checkbox($data)) {
                # no default class on a checkbox or radio
                if ($this->formr->is_not_empty($data['label'])) {
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
                    $return .= $this->nl.$this->t.'<p class="'.static::$css('help').'">'.trim($data['inline'], '[]').'</p>';
                }
            } else {
                # show this text on page load
                $return .= $this->nl.$this->t.'<p class="'.static::$css('help').'">'.$data['inline'].'</p>';
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

    /*
        the following functions are an example of a really basic wrapper.
        this demonstrates that your wrappers can be as simple - or as complex - as you like.
    */
    
    public static function simple_wrapper_css($key='')
    {
        # we still need to have these classes so that Formr may use them elsewhere.
        # However, these classes don't actually have to exist in your css files, we
        # just need to have they keys here so Formr doesn't throw an error.
        
        $array = [
            'div' => 'my-div',
            'label' => 'control-label',
            'input' => 'form-control',
            'file' => 'form-control-file',
            'link' => 'alert-link',
            'button' => 'btn',
            'text-error' => 'text-danger',
            'list-ul' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-dl' => 'list-unstyled',
            'alert-e'=> 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    public function simple_wrapper($element='', $data='')
    {
        if (empty($data)) {
            return false;
        }

        # notice how we're hard-coding our css into the wrapper? since this is such a simple 
        # example we won't bother using the static functions as in the other wrappers.
        # we're also not using the formatting functions; just keeping it simple...

        # enough talk; let's build our wrapper!

        # open the enclosing <div>
        $return = '<div id="'.$this->formr->make_id($data).'" class="my-div">';

        # let's create a <label> (if label text was supplied)
        if ($this->formr->is_not_empty($data['label']))
        {
            # open the <label>
            $return .= '<label class="my-label" for="'.$this->formr->make_id($data).'">';
            
            # insert the <label> text
            $return .= $data['label'];
            
            # add a required field indicator (*) if present
            $return .= $this->formr->insert_required_indicator($data);

            # close the <label>
            $return .= '</label>';
        }

        # add the field element, e.g.; <input type="text">
        $return .= $element;

        # close the <div>
        $return .= '</div>';

        return $return;
    }
}
