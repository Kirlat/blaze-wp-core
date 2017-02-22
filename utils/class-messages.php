<?php
namespace Blaze\Utils;

/**
 * Standard text messages
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Messages
{

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
	}

	public function jsDisabledWarning()
	{
		return __("It looks like JavaScript is disabled. Please enable JavaScript or upgrade to a Javascript-capable browser to be able to view this website properly.", 'blaze_wp-core');
	}

	public function unsupportedFeaturesWarning()
	{
		return __("It looks like your browser is out of date. Please upgrade your browser in order to view this website properly.", 'blaze_wp-core');
	}
}