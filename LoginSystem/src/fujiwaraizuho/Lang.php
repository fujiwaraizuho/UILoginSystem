<?php
/**
 * Created by PhpStorm.
 * User: izuho
 * Date: 2018/02/13
 * Time: 16:10
 */

namespace fujiwaraizuho;

use fujiwaraizuho\lang\Jpn;
use fujiwaraizuho\lang\Eng;


class Lang
{
	private $db;

	public function __construct(){}


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