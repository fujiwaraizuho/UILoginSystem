<?php
/**
 * Created by PhpStorm.
 * User: izuho
 * Date: 2018/02/12
 * Time: 22:33
 */

namespace fujiwaraizuho;

/* Base */
use pocketmine\event\Listener;

/* Event */
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

/* Packet */
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;


class EventListener implements Listener
{
	private $db;
	private $owner;
	/**
	 * EventListener constructor.
	 * @param Login $owner
	 */
	public function __construct(Login $owner, DB $db, Lang $lang)
	{
		$this->owner = $owner;
		$this->db = $db;
		$this->lang = $lang;
	}


	public function onLogin(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
		$result = $this->db->isRegister($player);

		for ($i = 0; $i <= 3; $i++) { 
			$player->formId[Login::PLUGIN_NAME][$i] = null;
		}

		if ($result) {
			$login = $this->db->login($player);
			if (is_null($login)) {
				$player->logined = false;
				$lang = $this->db->getLang($player);
				if (is_null($lang)) {
					$error_message = "§4ユーザーIDが変わった時は運営に連絡してください！\n§4Please contact the operation when the user ID changes!";
				} else {
					$error_message = $this->lang->getLang("kick_error_safety", $lang["lang"]);
				}
				$player->kick("§4[LoginSystem]\n".
							$error_message
						  	, false);

				$event->setCancelled();
			}
			if (!$login) {
				$player->autoLogin = true;
			} else {
				$player->login = true;
			}
		}
	}


	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$result = $this->db->isRegister($player);

		if ($result) {
			if (empty($player->autoLogin)) return; 
			if (isset($player->autoLogin)) {

				$lang = $this->db->getLang($player);
				$message = $this->lang->getLang("auto_login", $lang["lang"]);

				$player->logined = true;

				$player->sendMessage("§a>> ". $message ."！");
				unset($player->autoLogin);
			}
		} else {
			$player->register = true;
		}
	}


	public function onMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();

		if (isset($player->register)) {
			$data = [
				"type" => "modal",
				"title" => "§lLoginSystem",
				"content" => "言語を選択してください\n".
							 "Please select a language.",
				"button1" => "日本語",
				"button2" => "English"
			];

			$returnId = $this->owner->sendForm($player, $data);

			$player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT] = $returnId;

			$player->setImmobile(true);

			unset($player->register);
		}

		if (isset($player->login)) {
			$langName = $this->db->getLang($player);

			$data = $this->lang->getLang("login", $langName["lang"]);

			$returnId = $this->owner->sendForm($player, $data);

			$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $returnId;

			$player->setImmobile(true);

			unset($player->login);
		}
	}
	

	public function onData(DataPacketReceiveEvent $event)
	{
		$packet = $event->getPacket();
		if ($packet instanceof ModalFormResponsePacket) {
			$player = $event->getPlayer();
			$formId = (int) $packet->formId;
			$formData = json_decode($packet->formData, true);

			if (!isset($player->formId)) return;
			if (!array_key_exists(Login::PLUGIN_NAME, $player->formId)) return;
			if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT]) {
				if ($formData) {
					$player->lang = "jpn";
				} else {
					$player->lang = "eng";
				}

				$data = $this->lang->getLang("register", $player->lang);
				
				$returnId = $this->owner->sendForm($player, $data);

				$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $returnId;
				$player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT] = null;

			} else if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER]) {
				if (empty($formData)) {
					$player->logined = false;
					$player->kick("§4[LoginSystem]\n".
								  "§4このサーバーはアカウント登録をしないとプレイできません！"
								 , false);

					return;
				}

				if ($formData[0] === "" || $formData[1] === "") {

					$data = $this->owner->lang->getLang("register", $player->lang);
					$error_message = $this->lang->getLang("error_empty", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				if (strlen($formData[1]) < 8) {

					$data = $this->lang->getLang("register", $player->lang);
					$error_message = $this->lang->getLang("error_under", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				if ($formData[1] == $player->getName()) {

					$data = $this->lang->getLang("register", $player->lang);
					$error_message = $this->lang->getLang("error_notSafety", $player->lang);

					$data["content"][1]["text"] = $error_message;

					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				if ($formData[1] !== $formData[2]) {

					$data = $this->lang->getLang("register", $player->lang);
					$error_message = $this->lang->getLang("error_match", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				$player->pass = $formData[1];

				$this->db->register($player);

				$player->logined = true;
				$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = null;

				$lang = $this->db->getLang($player);
				$register_message = $this->lang->getLang("register_message", $lang["lang"]);
				$player->sendMessage($register_message);

			} else if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN]) {
				if (empty($formData)) {
					$player->logined = false;
					$lang = $this->db->getLang($player);
					$error_message = $this->lang->getLang("kick_error_login", $lang["lang"]);
					$player->kick("§4[LoginSystem]\n".
								  $error_message
								 , false);

					return;
				}

				if ($formData[1] === "") {

					$lang = $this->db->getLang($player);
					$data = $this->lang->getLang("login", $lang["lang"]);
					$error_message = $this->lang->getLang("error_empty", $lang["lang"]);

					$data["content"][1]["text"] = $error_message;
				
					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				if (strlen($formData[1]) < 8) {

					$lang = $this->db->getLang($player);
					$data = $this->lang->getLang("login", $lang["lang"]);
					$error_message = $this->lang->getLang("login_error_under", $lang["lang"]);

					$data["content"][1]["text"] = $error_message;
				
					$returnId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $returnId;
					$data["content"][1]["text"] = "";

					return;
				}

				$data = $this->db->getUserData($player);

				$result = password_verify($formData[1], $data["pass"]);

				$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = null;

				if ($result) {
					$this->db->updateIp($player);
					$lang = $this->db->getLang($player);
					$update_ip = $this->lang->getLang("update_ip", $lang["lang"]);
					$login_message = $this->lang->getLang("login_message", $lang["lang"]);
					$player->sendMessage($login_message);
					$player->sendMessage($update_ip);
					$player->setImmobile(false);
					$player->logined = true;
				} else {
					$player->logined = false;
					$lang = $this->db->getLang($player);
					$error_message = $this->lang->getLang("kick_error_safety", $lang["lang"]);
					$player->kick("§4[LoginSystem]\n".
								  $error_message
								 , false);
				}
			} else if ($player->formId[Login::PLUGIN_NAME][Login::FORM_UNREGISTER]) {
				if (!$formData) {
					$unregister = $this->db->unRegister($player->unregister[Login::PLUGIN_NAME]);

					if (is_null($unregister)) {
						$sender->sendMessage("§c>> Account NotFound！");
						return;
					}

					$allplayer = $this->owner->getServer()->getOnlinePlayers();

					foreach ($allplayer as $player) {
						$name = $player->getName();
						$players[] = $name;
					}

					if (in_array($player->unregister[Login::PLUGIN_NAME], $players)) {
						$player = $this->owner->getServer()->getPlayer($player->unregister[Login::PLUGIN_NAME]);
						$player->kick("§c[LoginSystem]\n".
									  "ログインデータが削除されました、再度ログインしてください！\n".
									  "Login data deleted, please login again!"
									  , false);
					}

					$player->sendMessage("§a>> Success！");
				}

				$player->formId[Login::PLUGIN_NAME][Login::FORM_UNREGISTER] = null;
				unset($player->unregister[Login::PLUGIN_NAME]);
			}
		}
	}
}
