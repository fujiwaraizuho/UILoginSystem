<?php
/**
 * Created by PhpStorm.
 * User: izuho
 * Date: 2018/02/13
 * Time: 16:10
 */

namespace fujiwaraizuho;

use fujiwaraizuho\lang\JpnForm;
use fujiwaraizuho\lang\EngForm;

class Lang
{
	private $db;

	public function __construct(DB $db)
	{
		$this->db = $db;
	}


	public function getForm(string $id, string $lang = "jpn")
	{
		switch ($lang) {
			case "jpn":

				$langClass = new JpnForm();

				$data = $langClass->Form[$id];

				return $data;

				break;

			case "eng":

				$langClass = new EngForm();

				$data = $langClass->Form[$id];

				return $data;
				break;
		}
		return null;
	}
}