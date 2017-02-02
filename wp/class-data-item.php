<?php
namespace Blaze\WP;

// Setting types
const DIT_THEME_MOD = "theme_mod";
const DIT_CUSTOM_FIELD = "custom_field";

// Data types
const DDT_STRING = "string";

// Data types
const DCT_SINGLE_LINE = "single_line";
const DCT_MULTI_LINE = "multi_line";
const DCT_SINGLE_SELECT = "single_select";

/**
 * Single data item, such as custom field, or a theme mod item
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Data_Item {
    private $ID;
    private $itemType;
    private $dataType;
    private $defaultValue;
    private $selectList = array();

    private $controlType;
    private $label;

    private $allowed;

    public function __construct($id, array $params = null)
    {
        $this->ID = $id;

        $this->itemType = !isset($params['itemType']) ? DIT_THEME_MOD : $params['itemType'];
        $this->dataType = !isset($params['dataType']) ? DDT_STRING : $params['dataType'];
        $this->defaultValue = !isset($params['defaultValue']) ? '' : $params['defaultValue'];
        $this->controlType = !isset($params['controlType']) ? DCT_SINGLE_LINE : $params['controlType'];
        $this->label = !isset($params['label']) ? $id : $params['label'];
        $this->selectList = !isset($params['selectList']) ? null : $params['selectList'];
        $this->restrictTags = !isset($params['restrictTags']) ? true : $params['restrictTags'];

        // Allowed tags
        $this->allowed = array(
            'a' => array(
                'href' => true,
                'title' => true,
            ),
            'abbr' => array(
                'title' => true,
            ),
            'acronym' => array(
                'title' => true,
            ),
            'b' => array(),
            'blockquote' => array(
                'cite' => true,
            ),
            'cite' => array(),
            'code' => array(),
            'del' => array(
                'datetime' => true,
            ),
            'em' => array(),
            'i' => array(),
            'q' => array(
                'cite' => true,
            ),
            'strike' => array(),
            'strong' => array(),
        );
    }

    public function isThemeMod() {
        return $this->itemType == DIT_THEME_MOD? true: false;
    }

    public function isCustomField() {
        return $this->itemType == DIT_CUSTOM_FIELD? true: false;
    }

    public function isSingleLineCtrl() {
        return $this->controlType == DCT_SINGLE_LINE? true: false;
    }

    public function isMultiLineCtrl() {
        return $this->controlType == DCT_MULTI_LINE? true: false;
    }

    public function isSingleSelectCtrl() {
        return $this->controlType == DCT_SINGLE_SELECT? true: false;
    }

    public function getID() {
        return $this->ID;
    }

    public function getValue($post_id = null) {
        if ($this->isThemeMod()) {
            return get_theme_mod($this->ID, $this->defaultValue);
        }
        else if ($this->isCustomField()) {
            // If no post ID provided, use the one of the current page
            if (!$post_id) {
                global $post;
                $post_id = $post->ID;
            }
            $value = get_post_meta($post_id, $this->ID, true);
            // If value is empty, return a default value
            if ($value == '') $value = $this->getDefaultValue();
            return $value;
        }
        else {
            return null;
        }
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

    public function getSelectList() {
        return $this->selectList;
    }

    public function getDataType() {
        return $this->dataType;
    }

    public function getItemType() {
        return $this->itemType;
    }

    public function getControlType() {
        return $this->controlType;
    }

    public function getLabel() {
        return $this->label;
    }

    public function save($value, $post_id = null) {
        if ($this->isCustomField()) {
            if ($this->restrictTags) {
                $value = wp_kses($value, $this->allowed);
            }
            update_post_meta($post_id, $this->ID, $value);
        }
    }
}