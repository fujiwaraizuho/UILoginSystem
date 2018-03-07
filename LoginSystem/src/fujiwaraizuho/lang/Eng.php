<?php
/**
 * Created by PhpStorm.
 * User: fujiwaraizuho
 * Date: 2018/02/13
 * Time: 14:32
 */

namespace fujiwaraizuho\lang;


class Eng
{
	public $lang = [
		"login" => [
			"type" => "custom_form",
			"title" => "LoginSystem",
			"content" => [
				[
					"type" => "label",
					"text" => "§cData did not match Login！"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "Password"
				]
			]
		],
		"register" => [
			"type" => "custom_form",
			"title" => "LoginSystem",
			"content" => [
				[
					"type" => "label",
					"text" => "§aPlease set a password"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "Password"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "Confirm password"
				]
			]
		],
		"re_unregister" => [
			"type" => "modal",
			"title" => "§l§cConfirmation",
			"content" => "Delete your account Please select yes if you do not mind.",
			"button1" => "No",
			"button2" => "Yes"
		],
		"error_empty" => "§cPassword has not been entered！",
		"error_under" => "§cA password of 8 characters or less can not be registered for safety！",
		"login_error_under" => "A password of 8 characters or less is not registered！",
		"error_notSafety" => "§cThis password is not secure！",
		"error_match" => "§cConfirmation password did not match！",
		"auto_login" => "I logged in automatically",
		"kick_error_login" => "§4This server can not play without logging in！",
		"kick_error_safety" => "§4I could not confirm that I am the principal！",
		"update_ip" => "§a>> I updated the IP address！",
		"login_message" => "§a>> You are now logged！",
		"register_message" => "§a>> Account registration is completed！"
	];
}