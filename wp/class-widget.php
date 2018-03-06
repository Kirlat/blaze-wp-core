<?php
namespace Blaze\WP;

/**
 * Displays several latest posts
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Widget extends \WP_Widget {
    private $wID;
    private $wName;
    private $wDescription;
    private $wClassName;
    private $wHtmlBeforeWidget;
    private $wHtmlAfterWidget;
    private $wHtmlBeforeTitle;
    private $wHtmlAfterTitle;
    private $wHtmlBeforeBody;
    private $wHtmlAfterBody;

    protected $wDataItems;

	/**
	 * @param $id - Base ID for the widget, lowercase and unique
	 * @param array|null $params - an associative array of optional widget parameters. Parameters are:
	 *        name - name for the widget displayed on the configuration page
	 *        description - widget description for display in the widget administration panel and/or theme
     *        classname - class name for the widget's HTML container. Default is a shortened version of the output
     *                    callback name
     *        htmlBeforeWidget - HTML code to be inserted before the widget (default is '<div class="blaze-widget">')
     *        htmlAfterWidget - HTML code to be inserted after the widget (default is '</div>')
     *        htmlBeforeTitle - HTML code to be inserted before the widget title (default is '<h4 class="blaze-widget__title">')
     *        htmlAfterTitle - HTML code to be inserted after the widget title (default is '</h4>')
     *        htmlBeforeBody - HTML code to be inserted before the widget body (default is '<div class="blaze-widget__body">')
     *        htmlAfterBody - HTML code to be inserted after the widget body (default is '</div>')
     * @param array|null $dataItems - an array of dataItems that are displayed in the widget administration panel
	 *
	 */
	function __construct($id, array $params = null, array $dataItems = null) {
        // Names start with 'w' to avoid collision with WP_Widget class

		$this->wID = $id;

		$this->wName = !isset($params['name']) ? '' : $params['name'];
        $this->wDescription = !isset($params['description']) ? '' : $params['description'];
        $this->wClassName = !isset($params['className']) ? '' : $params['className'];
        $this->wHtmlBeforeWidget = !isset($params['htmlBeforeWidget']) ? '<div class="blaze-widget">' : $params['htmlBeforeWidget'];
        $this->wHtmlAfterWidget = !isset($params['htmlAfterWidget']) ? '</div>' : $params['htmlAfterWidget'];
        $this->wHtmlBeforeTitle = !isset($params['htmlBeforeTitle']) ? '<h4 class="blaze-widget__title">' : $params['htmlBeforeTitle'];
        $this->wHtmlAfterTitle = !isset($params['htmlAfterTitle']) ? '</h4>' : $params['htmlAfterTitle'];
        $this->wHtmlBeforeBody = !isset($params['htmlBeforeBody']) ? '<div class="blaze-widget__body">' : $params['htmlBeforeBody'];
        $this->wHtmlAfterBody = !isset($params['htmlAfterBody']) ? '</div>' : $params['htmlAfterBody'];

        $dataItems = array_merge($dataItems, array(
            'widget-container-classes' => new Data_Item('widget-container-classes', array(
                'itemType' => Data_Item::DIT_WIDGET_FIELD,
                'dataType' => Data_Item::DDT_STRING,
                'label' => 'Classes to be added to the widget container',
                'description' => 'Multiple class names should be separated by spaces: class1 class2',
                'defaultValue' => ''
            )),
            'widget-title-classes' => new Data_Item('widget-title-classes', array(
                'itemType' => Data_Item::DIT_WIDGET_FIELD,
                'dataType' => Data_Item::DDT_STRING,
                'label' => 'Classes to be added to the widget title',
                'description' => 'Multiple class names should be separated by spaces: class1 class2',
                'defaultValue' => ''
            )),
            'widget-body-classes' => new Data_Item('widget-body-classes', array(
                'itemType' => Data_Item::DIT_WIDGET_FIELD,
                'dataType' => Data_Item::DDT_STRING,
                'label' => 'Classes to be added to the widget body',
                'description' => 'Multiple class names should be separated by spaces: class1 class2',
                'defaultValue' => ''
            ))
        ));

        $this->wDataItems = $dataItems;

        // Not sure if this is required or not. Do we need this for an administration panel?
        $this->args = array(
            'before_title'  => $this->wHtmlBeforeTitle,
            'after_title'   => $this->wHtmlAfterTitle,
            'before_widget' => $this->wHtmlBeforeWidget,
            'after_widget'  => $this->wHtmlAfterWidget
        );

		parent::__construct(
			$this->wID,
			$this->wName,
			array(
				'classname' => $this->wClassName,
				'description' => $this->wDescription
			)
		);

	}

	public $args;

    public function getID() {
        return $this->wID;
    }

    /**
     * Generate HTML code to be displayed in the widget administration panel
     *
     * @param array $instance
     */
	public function form($instance) {

        $html = '';
        foreach ($this->wDataItems as $dataItem) {
            $id = esc_attr( $this->get_field_id( $dataItem->getID() ) );
            $title = esc_attr( $dataItem->getLabel() );
            $name = esc_attr( $this->get_field_name( $dataItem->getID() ) );
            $description = esc_attr($dataItem->getDescription());
            $value = '';
            if (method_exists($dataItem, "getDefaultValue")) {
                $default_value = $dataItem->getDefaultValue();
                $value = esc_html($default_value);
            }
            if (method_exists($dataItem, "getValue")) {
                $instance_value = $dataItem->getValue($instance);
                if (! empty($instance_value)) {
                    $value = esc_attr($instance_value);
                }
            }

            $html .= "<p><label for='{$id}'>{$title}</label>" .
			    "<input class='widefat' id='{$id}' name='{$name}' type='text' value='{$value}'>";
            if (!empty($description)) {
                $html .= "<span style='font-style: italic;'>{$description}</span>";
            }
            $html .= "</p>";
        }

		echo $html;

	}

    /**
     * Save the values of the widget when the widget is saved
     *
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
	public function update($new_instance, $old_instance) {

		$instance = array();

        foreach ($this->wDataItems as $dataItem) {
            $data_item_id = $dataItem->getID();

            $value = '';
            if (method_exists($dataItem, "getDefaultValue")) {
                $value = $dataItem->getDefaultValue();
            }
            if (method_exists($dataItem, "getValue")) {
                $new_instance_value = $dataItem->getValue($new_instance);
                if (! empty($new_instance_value)) {
                    $value = strip_tags($new_instance_value);
                }
            }
            $instance[$data_item_id] = $value;
        }

		return $instance;
	}

}