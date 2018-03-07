<?php
/**
 * Created by PhpStorm.
 * User: fujiwaraizuho
 * Date: 2018/02/12
 * Time: 22:31
 */

namespace fujiwaraizuho;

/* Base */
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

/* Event */
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

/* Command */
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

/* Packet */
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;


class Login extends PluginBase implements Listener
{
	const PLUGIN_NAME = "LoginSystem";

	const FORM_LANG_SELECT = 0;
	const FORM_LOGIN = 1;
	const FORM_REGISTER = 2;
	const FORM_UNREGISTER = 3;

	public $lang;
	public $db;

	public function onEnable()
	{
		if (!file_exists($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}

		$this->db = new DB($this->getDataFolder(), $this);
		$this->lang = new Lang();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this, $this->db, $this->lang), $this);

		$this->getLogger()->info("§aINFO §f> §aEnabled...");
	}


	public function onDisable()
	{
		$this->getLogger()->info("§cINFO §f> §cDisabled...");
	}


	/**
	 * @param CommandSender $sender
	 * @param Command $command
	 * @param string $label
	 * @param array $args
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		switch ($label) {
			case "updatename":

				if (!$sender->isOp()) {
					$sender->sendMessage("§c>> Permission error！");
					return true;
				}
			
				if (!isset($args[0]) || !isset($args[1])) return false;

				$result = $this->db->updateName(strtolower($args[0]), strtolower($args[1]));

				if (is_null($result)) {
					$sender->sendMessage("§c>> Account NotFound！");
					return true;
				}

				$sender->sendMessage("§a>> Success！");

				return true;
				break;

			case "unregister":

				if ($sender->isOp()) {
					if (!isset($args[0])) {
						// OP自身のログインデータ削除
						if ($sender instanceof ConsoleCommandSender) {
							$sender->sendMessage("§c>> Permission error！");
							return true;
						}

						$langName = $this->db->getLang($sender);
						$data = $this->lang->getLang("re_unregister", $langName["lang"]);
						$returnId = $this->sendForm($sender, $data);

						$sender->unregister[self::PLUGIN_NAME] = strtolower($sender->getName());
						$sender->formId[self::PLUGIN_NAME][self::FORM_UNREGISTER] = $returnId;
					} else {
						// ほかのプレイヤーのデータ削除
						if ($sender instanceof ConsoleCommandSender) {
							// コンソール
							$selectPlayer = strtolower($args[0]);
							$unregister = $this->db->unRegister($selectPlayer);

							if (is_null($unregister)) {
								$sender->sendMessage("§c>> Account NotFound！");
								return true;
							}

							$allplayer = $this->getServer()->getOnlinePlayers();

							foreach ($allplayer as $player) {
								$name = $player->getName();
								$players[] = $name;
							}

							if (isset($players)) {
								if (in_array($selectPlayer, $players)) {
									$player = $this->getServer()->getPlayer($name);
									$player->kick("§c[LoginSystem]\n".
											  	"ログインデータが削除されました、再度ログインしてください！\n".
											  	"Login data deleted, please login again!"
												, false);
								}
							}	

							$sender->sendMessage("§a>> Success！");

							return true;

						} else {
							// プレイヤー
							$selectPlayer = strtolower($args[0]);

							$data = $this->db->getUserData(null, $selectPlayer);

							if (is_null($data)) {
								$sender->sendMessage("§c>> Account NotFound！");
								return true;
							}

							$data = [
								"type" => "modal",
								"title" => "§l§c確認",
								"content" => $selectPlayer ."さんのアカウントデータを削除します、本当によろしいですか？\n".
											 "消した場合二度とデーターは戻りません！",
								"button1" => "いいえ",
								"button2" => "はい" 
							];

							$returnId = $this->sendForm($sender, $data);

							$sender->unregister[self::PLUGIN_NAME] = $selectPlayer;
							$sender->formId[self::PLUGIN_NAME][self::FORM_UNREGISTER] = $returnId;
						}
					}
				} else {
					if ($sender instanceof ConsoleCommandSender) {
						$sender->sendMessage("§c>> Permission error！");
						return true;
					}

					$langName = $this->db->getLang($sender);
					$data = $this->lang->getLang("re_unregister", $langName["lang"]);
					$returnId = $this->sendForm($sender, $data);

					$sender->unregister[self::PLUGIN_NAME] = strtolower($sender->getName());
					$sender->formId[self::PLUGIN_NAME][self::FORM_UNREGISTER] = $returnId;
				}

				return true;
				break;
		}
	}


	/**
	 * @param Player $player
	 * @param array $data
	 * @return int
	 */
	public function sendForm(Player $player, array $data)
	{
		$pk = new ModalFormRequestPacket();

		$pk->formId = mt_rand(1, 999999999);
		$pk->formData = json_encode($data);

		$player->dataPacket($pk);

		return $pk->formId;
	}


	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();
		}
	}


	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();			
		}
	}


	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();			
		}
	}


	/**
	 * @param PlayerChatEvent $event
	 */
	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();
		if (!$player->logined) {
			$player->sendMessage("§c>> Permission error！");
			$event->setCancelled();
		}
	}
}