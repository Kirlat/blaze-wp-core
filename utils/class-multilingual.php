<?php
namespace Blaze\Utils;

/**
 * Multilingual functions
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Multilingual
{

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		/* Display Transposh widget shortcode */
		add_shortcode('blaze-transposh-widget', array($this, 'transposhWidget'));
	}

	public function transliterate($word)
	{
		//GOST 7.79-2000
		$translit = array(
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'yo',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'j',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'x',
			'ц' => 'c',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'shh',
			'ь' => '\'',
			'ы' => 'y\'',
			'ъ' => '\'\'',
			'э' => 'e\'',
			'ю' => 'yu',
			'я' => 'ya',
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'YO',
			'Ж' => 'Zh',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'J',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'X',
			'Ц' => 'C',
			'Ч' => 'CH',
			'Ш' => 'SH',
			'Щ' => 'SHH',
			'Ь' => '\'',
			'Ы' => 'Y\'',
			'Ъ' => '\'\'',
			'Э' => 'E\'',
			'Ю' => 'YU',
			'Я' => 'YA'
		);

		return strtr($word, $translit);
	}

	function transposhWidget()
	{
		ob_start();
		if (function_exists("transposh_widget")) {
			transposh_widget(array(), array('title' => '', 'before_title' => '', 'after_title' => '', 'widget_file' => 'flagslist/tpw_list_with_flags_css.php'));
		}
		$contents = ob_get_clean();
		return $contents;
	}
}