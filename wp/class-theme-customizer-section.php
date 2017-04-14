<?php
namespace Blaze\WP;

/**
 * A WP theme customizer section
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Theme_Customizer_Section {
	private $ID;
	private $title;
	private $description;
	private $priority;

	private $settings;

	public function __construct($id, array $params = null) {
		$this->ID = $id;

		$this->title = !isset($params['title']) ? '' : $params['title'];
		$this->description = !isset($params['description']) ? '' : $params['description'];
		$this->priority = !isset($params['priority']) ? '' : $params['priority'];
	}

	public function addSetting(Data_Item $setting) {
		$this->settings[$setting->getID()] = $setting;
		return $setting;
	}

	public function register(\WP_Customize_Manager $wp_customize) {
		$wp_customize->add_section(
			$this->ID,
			array(
				'title' => $this->title,
				'description' => $this->description,
				'priority' => $this->priority,
			)
		);

		$this->registerAllSettings($wp_customize);
	}

	public function registerSetting(Data_Item $setting, \WP_Customize_Manager $wp_customize)
	{
		// Allow registration of supported types only
		if ($setting->isThemeMod()) {
			$type = 'text';
			if ($setting->isSingleLineCtrl()) {
				$type = 'text';
			}
			else if ($setting->isMultiLineCtrl()) {
				$type = 'textarea';
			}

			$wp_customize->add_setting(
				$setting->getID(),
				array(
					'default' => $setting->getDefaultValue(),
				)
			);

			$wp_customize->add_control(
				$setting->getID(),
				array(
					'label' => $setting->getLabel(),
					'section' => $this->ID,
					'type' => $type,
				)
			);
		}
	}

	public function registerAllSettings(\WP_Customize_Manager $wp_customize) {
		foreach ($this->settings as $setting) {
			$this->registerSetting($setting, $wp_customize);
		}
	}
}