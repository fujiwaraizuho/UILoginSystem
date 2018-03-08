<?php
/**
 * Created by PhpStorm.
 * User: fujiwaraizuho
 * Date: 2018/02/13
 * Time: 16:10
 */

namespace fujiwaraizuho;

use fujiwaraizuho\lang\Jpn;
use fujiwaraizuho\lang\Eng;


class Lang
{

	/**
	 * Lang constructor.
	 */
	public function __construct(){}


	/**
	 * @param string $id
	 * @param string $lang
	 * @return null
	 */
	public function getLang(string $id, string $lang = "jpn")
	{
		switch ($lang) {
			case "jpn":

				$langClass = new Jpn();

				$data = $langClass->lang[$id];

				return $data;

				break;

			case "eng":

				$langClass = new Eng();

				$data = $langClass->lang[$id];

				return $data;
				break;
		}
		return null;
	}
}