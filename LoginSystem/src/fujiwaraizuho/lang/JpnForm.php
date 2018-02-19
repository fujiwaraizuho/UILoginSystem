<?php
/**
 * Created by PhpStorm.
 * User: izuho
 * Date: 2018/02/13
 * Time: 14:32
 */

namespace fujiwaraizuho\lang;


class JpnForm
{
	public $Form = [
		"login" => [
			"type" => "custom_form",
			"title" => "LoginSystem",
			"content" => [
				[
					"type" => "label",
					"text" => "§c情報が一致しませんでしたログインしてください！"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "パスワード"
				]
			]
		],
		"register" => [
			"type" => "custom_form",
			"title" => "LoginSystem",
			"content" => [
				[
					"type" => "label",
					"text" => "§aパスワードを設定してください"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "パスワード"
				],
				[
					"type" => "input",
					"text" => "",
					"placeholder" => "確認パスワード"
				]
			]
		],
		"error_empty" => "§cパスワードが入力されていません！",
		"error_under" => "§c8文字以下のパスワードは安全のため登録できません！",
		"login_error_under" => "§c8文字以下のパスワードは登録されていません！",
		"error_notSafety" => "§cこのパスワードは安全ではありません！",
		"error_match" => "§c確認パスワードと一致しませんでした！",
		"auto_login" => "自動ログインしました",
		"kick_error_login" => "§4このサーバーはログインをしないとプレイできません！",
		"kick_error_safety" => "§4本人であることが確認できませんでした！",
		"update_ip" => "§a>> IPアドレスを更新しました！",
		"login_message" => "§a>> ログインしました！",
		"register_message" => "§a>> アカウント登録が完了しました！"
	];
}