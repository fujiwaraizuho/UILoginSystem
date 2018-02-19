<?php
/**
 * Created by PhpStorm.
 * User: izuho
 * Date: 2018/02/12
 * Time: 22:31
 */

namespace fujiwaraizuho;

/* Base */
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

/* Packet */
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class Login extends PluginBase
{
	const PLUGIN_NAME = "LoginSystem";

	const FORM_LANG_SELECT = 0;
	const FORM_LOGIN = 1;
	const FORM_REGISTER = 2;

	public $lang;
	public $db;

	public function onEnable()
	{
		if (!file_exists($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}

		$this->db = new DB($this->getDataFolder(), $this);
		$this->lang = new Lang($this->db);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this, $this->db), $this);

		$this->getLogger()->info("§aINFO §f> §aEnabled...");
	}


	public function onDisable()
	{
		$this->getLogger()->info("§cINFO §f> §cDisabled...");
	}


	public function sendForm(Player $player, string $data)
	{
		$pk = new ModalFormRequestPacket();

		$pk->formId = mt_rand(1, 999999999);
		$pk->formData = json_encode($data);

		$player->dataPacket($pk);

		return $pk->formId;
	}
}