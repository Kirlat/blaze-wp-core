<?php
namespace Blaze\WP;

/**
 * Single data item, such as custom field, or a theme mod item
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Data_Item {
	// Item type
	const DIT_THEME_MOD = "theme_mod"; // A theme option data item
	const DIT_CUSTOM_FIELD = "custom_field"; // A custom field
	const DIT_WIDGET_FIELD = "widget_field"; // A field of a widget

	// Data types
	const DDT_STRING = "string";

	// Control types
	const DCT_SINGLE_LINE = "single_line";
	const DCT_MULTI_LINE = "multi_line";
	const DCT_SINGLE_SELECT = "single_select";

	private $ID;
	private $itemType;
	private $dataType;
	private $defaultValue;
	private $selectList = array();

	private $controlType;
	private $label;
	private $description;

	private $allowed;

	public function __construct($id, array $params = null)
	{
		$this->ID = $id;

		$this->itemType = !isset($params['itemType']) ? Data_Item::DIT_THEME_MOD : $params['itemType'];
		$this->dataType = !isset($params['dataType']) ? Data_Item::DDT_STRING : $params['dataType'];
		$this->defaultValue = !isset($params['defaultValue']) ? '' : $params['defaultValue'];
		$this->controlType = !isset($params['controlType']) ? Data_Item::DCT_SINGLE_LINE : $params['controlType'];
		$this->label = !isset($params['label']) ? $id : $params['label'];
		$this->description = !isset($params['description']) ? null : $params['description'];
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
		return $this->itemType == Data_Item::DIT_THEME_MOD? true: false;
	}

	public function isCustomField() {
		return $this->itemType == Data_Item::DIT_CUSTOM_FIELD? true: false;
	}

	public function isWidgetField() {
		return $this->itemType == Data_Item::DIT_WIDGET_FIELD? true: false;
	}

	public function isSingleLineCtrl() {
		return $this->controlType == Data_Item::DCT_SINGLE_LINE? true: false;
	}

	public function isMultiLineCtrl() {
		return $this->controlType == Data_Item::DCT_MULTI_LINE? true: false;
	}

	public function isSingleSelectCtrl() {
		return $this->controlType == Data_Item::DCT_SINGLE_SELECT? true: false;
	}

	public function getID() {
		return $this->ID;
	}

	/**
	 * Returns a value of a data items. Depending on an item type, an argument may be required.
	 *
	 * @param null $data_id - id of a data object required to retrieve a data. What is it depends on the item type:
	 *                        - for DIT_THEME_MOD it is not required
	 *                        - for DIT_CUSTOM_FIELD it is a Post ID of a post whose data needs to be retrieved.
	 *                          If no Post ID provided, an ID of a current post will be used.
	 *                        - for DIT_WIZARD_FIELD it is an '$instance' object that contains widget fields data
	 * @return mixed|null|string - a value of a data item
	 */
	public function getValue($data_id = null) {
		if ($this->isThemeMod()) {
			return get_theme_mod($this->ID, $this->defaultValue);
		}
		else if ($this->isCustomField()) {
			$post_id = $data_id;
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
		if ($this->isWidgetField()) {
			$instance = $data_id;
			return $instance[$this->getID()];
		}
		else {
			// Unknown or incorrect item type
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

	public function getDescription() {
		return $this->description;
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