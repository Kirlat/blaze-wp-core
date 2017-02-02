<?php
namespace Blaze;

/**
 * Blaze class autoloader
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Autoloader
{
	protected static $instance;

	private $blazePrefix;
	private $baseDir;

	/**
	 * Autoloader constructor.
	 *
	 * @param string $baseDir Blaze Core base directory (default: dirname(__FILE__))
	 */
	private function __construct($baseDir = null)
	{
		$this->blazePrefix = 'Blaze\\';

		if ($baseDir === null) {
			$baseDir = dirname(__FILE__);
		}

		// realpath doesn't always work, for example, with stream URIs
		$realDir = realpath($baseDir);
		if (is_dir($realDir)) {
			$this->baseDir = $realDir;
		} else {
			$this->baseDir = $baseDir;
		}
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * WpSite instance.
	 *
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Private unserialize method to prevent unserializing of the WpSite
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}

	/**
	 * Register a new instance as an SPL autoloader.
	 *
	 * @param string $baseDir Blaze Core base directory (default: dirname(__FILE__))
	 *
	 * @return Autoloader Registered Autoloader instance
	 */
	public static function register($baseDir = null)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($baseDir);
			spl_autoload_register(array(self::$instance, 'autoload'));
		}
		return self::$instance;
	}

	/**
	 * Autoload Blaze Core classes.
	 *
	 * @param string $class
	 */
	public function autoload($class)
	{
		if ($class[0] === '\\') {
			$class = substr($class, 1);
		}

		// Handle only Blaze subnamespaces
		if (strpos($class, $this->blazePrefix) !== 0) {
			return;
		}

		$name = substr($class, strlen($this->blazePrefix));
		$name = explode('\\', str_replace('_', '-', strtolower($name)));
		$name[sizeof($name) - 1] = 'class-' . $name[sizeof($name)-1];
		$name = implode(DIRECTORY_SEPARATOR, $name);

		$file = sprintf('%s' . DIRECTORY_SEPARATOR .'%s.php', $this->baseDir, $name);
		if (is_file($file)) {
			require $file;
		}
	}
}

Autoloader::register();
