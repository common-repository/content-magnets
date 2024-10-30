<?php

class CMG_CFIELD
{

    private static $initiated = false;

    public static function init()
    {

        if (!self::$initiated) {
            self::init_hooks();
        }

    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks()
    {
        self::$initiated = true;

    }

    public static function post_selectbox($field, $value = '')
    {
        $posts = get_pages(array('numberposts' => -1, 'post_type' => 'page', 'post_parent' => 0));
        //cmg_print($value);

        $html = '<div>';
        $html .= '<label for="field_' . str_replace(array('[',']'), array('-', ''), $field['id'] )  . '">' . $field['title'] . '</label> ';

        $html .= '<select name="' . $field['id'] . '" id="field_' . str_replace(array('[',']'), array('-', ''), $field['id'] ) . '" >';
        $html .= '<option value="null" > --- </option>';
        if( !empty($posts) )
            foreach ($posts as $post) {
                $i_selected = '';
                if ($value == $post->ID) $i_selected = 'selected';
                $html .= '<option value="' . $post->ID . '" ' . $i_selected . '  >' . $post->post_title . '</option>';
            }
        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }
    public static function selectbox($field, $value = '')
    {
        $options = $field['options'];
        $id = ($field['id'])?$field['id']:'field_'.str_replace(array('[',']'), array('-', ''), $field['name'] );
        $css_class = ($field['class'])?$field['class']:$id;
        $placeholder = ($field['placeholder'])?$field['placeholder']:'';


        $html = '<select name="' . $field['name'] . '" id="' . $id . '" class="'.$css_class.'" >';
        $html .= '<option value="" > '.$placeholder.' </option>';
        foreach ($options as $option_key => $option_val) {
            $i_selected = '';
            if ($value == $option_key) $i_selected = 'selected';
            $html .= '<option value="' . $option_key . '" ' . $i_selected . '  >' . $option_val . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

}