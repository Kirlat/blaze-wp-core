<?php
namespace Blaze;
/**
 * @package     BlazeCore
 * @link
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 *
 * @wordpress-plugin
 * Plugin Name:       Blaze Core
 * Plugin URI:
 * Description:       Custom functionality plugin for WordPress themes
 * Version:           1.0.0
 * Author:
 * Author URI:
 * Text Domain:
 * Domain Path:       /Languages
 * Minimal Requirements:       PHP 5.4+ (?) (LightnCandy template engine), PHP 5.3.0+ (namespaces, Mustache template engine)
 */
// If this file is called directly, abort.


if ( !defined( 'WPINC' ) ) {
	die;
}

require_once dirname(__FILE__) . 'autoloader.php';
$instance = Wp\Site::getInstance();