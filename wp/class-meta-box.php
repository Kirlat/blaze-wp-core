<?php
namespace Blaze\WP;

/**
 * Post Meta Box
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Meta_Box
{
	private $ID;
	private $title;
	private $screen;
	private $context;
	private $priority;

	private $items = array();

	private $nonce_action;
	private $nonce_name;

	public function __construct($id, array $params = null)
	{
		$this->ID = $id;
		$this->title = !isset($params['title']) ? $id : $params['title'];
		$this->screen = !isset($params['screen']) ? null : $params['screen'];
		$this->context = !isset($params['context']) ? 'advanced' : $params['context'];
		$this->priority = !isset($params['priority']) ? 'default' : $params['priority'];
		$this->showForTemplate = !isset($params['showForTemplate']) ? '' : $params['showForTemplate'];

		$this->nonce_action = $this->ID . "_nonce_action";
		$this->nonce_name = $this->ID . "_nonce";


		add_action('add_meta_boxes', array($this, 'register'));
		add_action('save_post', array($this, 'save'));
	}

	public function register() {
		global $post;
		$page_template = get_page_template_slug($post->ID);

		// If template for which to show specified, show metabox for this template only
		if (isset($this->showForTemplate) && $this->showForTemplate === $page_template) {
			add_meta_box($this->ID, $this->title, array($this, 'render'), $this->screen, $this->context, $this->priority);
		}

	}

	public function addDataItem(Data_Item $dataItem) {
		$this->items[] = $dataItem;
	}

	public function addHtmlItem($html) {
		$this->items[] = new Meta_Box_HTML_Item($html);
	}

	public function render($post)
	{
		$html = '';
		$html .= wp_nonce_field($this->nonce_action, $this->nonce_name, true, false);
		foreach ($this->items as $item) {
			if ($item instanceof Data_Item) {
				$html .= $this->getDataItemHTML($item, $post);
			}
			else if ($item instanceof Meta_Box_Html_Item) {
				$html .= $item->getHtml();
			}

		}
		echo $html;
	}

	function getDataItemHTML(Data_Item $dataItem, $post) {
		if ($dataItem->isCustomField()) {
			if ($dataItem->isSingleLineCtrl())
				return $this->renderSingleLineCtrl($dataItem, $post);
			if ($dataItem->isMultiLineCtrl())
				return $this->renderMultiLineCtrl($dataItem, $post);
			else if ($dataItem->isSingleSelectCtrl())
				return $this->renderSingleSelectCtrl($dataItem, $post);
		}
		return '';
	}

	function renderSingleLineCtrl(Data_Item $dataItem, $post) {
		$post_id = $post->ID;
		$current_val =$dataItem->getValue($post_id);
		$dataItem_id = $dataItem->getID();
		$placeholder = $dataItem->getDefaultValue();
		$id = 'id-' . $dataItem_id;
		$label = $dataItem->getLabel();
		$html = "
			<div class='admin-mtbx-form-group'>
				<label class='admin-mtbx-label' for='{$id}'>{$label}</label><br>
				<input type='text' class='admin-mtbx-form-control' name='{$dataItem_id}' id='{$id}' placeholder='{$placeholder}' value='{$current_val}' />
			</div>
		";
		return $html;
	}

	function renderMultiLineCtrl(Data_Item $dataItem, $post) {
		$post_id = $post->ID;
		$current_val =$dataItem->getValue($post_id);
		$dataItem_id = $dataItem->getID();
		$placeholder = $dataItem->getDefaultValue();
		$id = 'id-' . $dataItem_id;
		$label = $dataItem->getLabel();
		$html = "
			<div class='admin-mtbx-form-group'>
				<label class='admin-mtbx-label' for='{$id}'>{$label}</label><br>
				<textarea rows='5' cols='50' class='admin-mtbx-form-control--textarea' name='{$dataItem_id}' id='{$id}' placeholder='{$placeholder}'>{$current_val}</textarea>
			</div>
		";
		return $html;
	}

	function renderSingleSelectCtrl(Data_Item $dataItem, $post) {
		$post_id = $post->ID;
		$current_value =$dataItem->getValue($post_id);
		$dataItem_id = $dataItem->getID();
		$select_list = $dataItem->getSelectList();
		$id = 'id-' . $dataItem_id;
		$label = $dataItem->getLabel();
		$html = "
			<div class='admin-mtbx-form-group'>
				<label class='admin-mtbx-label' for='{$id}'>{$label}</label><br>
				<select class='admin-mtbx-form-control' name='{$dataItem_id}' id='{$id}'>";
					foreach ($select_list as $select_list_item) {
						$title = $select_list_item["title"];
						$value = $select_list_item["value"];
						$selected = ($value == $current_value)? " selected='selected'": "";
						$html .= "<option value='{$value}' {$selected}>{$title}</option>";
					}
			$html .= "</select>
			</div>
		";
		return $html;
	}

	function save($post_id)
	{
		// Bail if we're doing an auto save
		if(defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		// if our nonce isn't there, or we can't verify it, bail
		if(!isset($_POST[$this->nonce_name]) || !wp_verify_nonce($_POST[$this->nonce_name], $this->nonce_action)) return;

		// if our current user can't edit this post, bail
		if(!current_user_can('edit_post')) return;

		// if metabox is set to be shown for certain tempate only and template name does not match, bail
		$page_template = get_page_template_slug($post_id);
		if (!isset($this->showForTemplate) || $this->showForTemplate != $page_template) return;

		// Make sure data is set before trying to save it
		foreach ($this->items as $item) {
			if ($item instanceof Data_Item) {
				if(isset( $_POST[$item->getID()] ) )
					$item->save($_POST[$item->getID()], $post_id);
			}
		}
	}
}