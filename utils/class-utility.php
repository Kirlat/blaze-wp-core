<?php
namespace Blaze\Utils;

/**
 * Helper functions
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Utility
{

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
	}

	public static function log($message)
	{
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log(print_r($message, true));
			} else {
				error_log($message);
			}
		}
	}

	public static function getRandomString($length = 10) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		$charLength = strlen($chars);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= $chars[rand(0, $charLength - 1)];
		}
		return $string;
	}

	public static function forceTrailingSlash($path, $isPath = true) {
		if ($isPath) {
			return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		}
		else {
			return rtrim($path, '/') . '/';
		}
	}
}