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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;


class EventListener implements Listener
{
	private $db;
	private $owner;
	/**
	 * EventListener constructor.
	 * @param Login $owner
	 */
	public function __construct(Login $owner, DB $db)
	{
		$this->owner = $owner;
		$this->db = $db;
	}


	public function onLogin(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
		$result = $this->db->isRegister($player);

		for ($i = 0; $i <= 2; $i++) { 
			$player->formId[Login::PLUGIN_NAME][$i] = null;
		}

		if ($result) {
			$login = $this->db->login($player);
			if (is_null($login)) {
				$player->logined = false;
				$lang = $this->db->getLang($player);
				$error_message = $this->owner->lang->getForm("kick_error_safety", $lang["lang"]);
				$player->kick("§4[LoginSystem]\n\n".
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
				$message = $this->owner->lang->getForm("auto_login", $lang["lang"]);

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

			$formId = $this->owner->sendForm($player, $data);

			$player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT] = $formId;

			$player->setImmobile(true);

			unset($player->register);
		}

		if (isset($player->login)) {
			$langName = $this->db->getLang($player);

			$data = $this->owner->lang->getForm("login", $langName["lang"]);

			$formId = $this->owner->sendForm($player, $data);

			$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $formId;

			$player->setImmobile(true);

			unset($player->login);
		}
	}


	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();
		}
	}


	public function onBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();			
		}
	}


	public function onPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();			
		}
	}


	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();
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
			if (!$player->formId[0] === Login::PLUGIN_NAME) return; 
			if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT]) {
				if ($formData) {
					$player->lang = "jpn";
				} else {
					$player->lang = "eng";
				}

				$data = $this->owner->lang->getForm("register", $player->lang);
				
				$formId = $this->owner->sendForm($player, $data);

				$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $formId;
				$player->formId[Login::PLUGIN_NAME][Login::FORM_LANG_SELECT] = null;

			} else if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER]) {
				if (empty($formData)) {
					$player->logined = false;
					$player->kick("§4[LoginSystem]\n\n".
								  "§4このサーバーはアカウント登録をしないとプレイできません！"
								 , false);

					return;
				}

				if ($formData[0] === "" || $formData[1] === "") {

					$data = $this->owner->lang->getForm("register", $player->lang);
					$error_message = $this->owner->lang->getForm("error_empty", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				if (strlen($formData[1]) < 8) {

					$data = $this->owner->lang->getForm("register", $player->lang);
					$error_message = $this->owner->lang->getForm("error_under", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				if ($formData[1] == $player->getName()) {

					$data = $this->owner->lang->getForm("register", $player->lang);
					$error_message = $this->owner->lang->getForm("error_notSafety", $player->lang);

					$data["content"][1]["text"] = $error_message;

					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				if (!$formData[1] === $formData[2]) {

					$data = $this->owner->lang->getForm("register", $player->lang);
					$error_message = $this->owner->lang->getForm("error_match", $player->lang);

					$data["content"][1]["text"] = $error_message;
				
					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				$player->pass = $formData[1];

				$this->db->register($player);

				$player->logined = true;
				$player->formId[Login::PLUGIN_NAME][Login::FORM_REGISTER] = null;

				$lang = $this->db->getLang($player);
				$register_message = $this->owner->lang->getForm("register_message", $lang["lang"]);
				$player->sendMessage($register_message);

			} else if ($formId === $player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN]) {
				if (empty($formData)) {
					$player->logined = false;
					$lang = $this->db->getLang($player);
					$error_message = $this->owner->lang->getForm("kick_error_login", $lang["lang"]);
					$player->kick("§4[LoginSystem]\n\n".
								  $error_message
								 , false);

					return;
				}

				if ($formData[1] === "") {

					$lang = $this->db->getLang($player);
					$data = $this->owner->lang->getForm("login", $lang["lang"]);
					$error_message = $this->owner->lang->getForm("error_empty", $lang["lang"]);

					$data["content"][1]["text"] = $error_message;
				
					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				if (strlen($formData[1]) < 8) {

					$lang = $this->db->getLang($player);
					$data = $this->owner->lang->getForm("login", $lang["lang"]);
					$error_message = $this->owner->lang->getForm("login_error_under", $lang["lang"]);

					$data["content"][1]["text"] = $error_message;
				
					$formId = $this->owner->sendForm($player, $data);

					$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = $formId;
					$data["content"][1]["text"] = "";

					return;
				}

				$data = $this->db->getUserData($player);

				$result = password_verify($formData[1], $data["pass"]);

				$player->formId[Login::PLUGIN_NAME][Login::FORM_LOGIN] = null;

				if ($result) {
					$this->db->updateIp($player);
					$lang = $this->db->getLang($player);
					$update_ip = $this->owner->lang->getForm("update_ip", $lang["lang"]);
					$login_message = $this->owner->lang->getForm("login_message", $lang["lang"]);
					$player->sendMessage($login_message);
					$player->sendMessage($update_ip);
					$player->setImmobile(false);
					$player->logined = true;
				} else {
					$player->logined = false;
					$lang = $this->db->getLang($player);
					$error_message = $this->owner->lang->getForm("kick_error_safety", $lang["lang"]);
					$player->kick("§4[LoginSystem]\n\n".
								  $error_message
								 , false);
				}
			}
		}
	}
}