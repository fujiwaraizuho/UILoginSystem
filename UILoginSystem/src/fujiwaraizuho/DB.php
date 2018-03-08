<?php
/**
 * Created by PhpStorm.
 * User: fujiwaraizuho
 * Date: 2018/02/12
 * Time: 22:34
 */

namespace fujiwaraizuho;

/* Base */
use pocketmine\Player;

/* Utils */
use pocketmine\utils\MainLogger;


class DB
{
	/**
	 * DB constructor.
	 * @param string $dir
	 * @param Login $owner
	 */
	public function __construct(string $dir, Login $owner)
	{
		$this->owner = $owner;

		$this->db = new \SQLite3($dir ."data.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS userdata (
				name PRIMARY KEY,
				pass,
				ip  ,
				xuid,
				lang 
		)");
	}


	/**
	 * @param Player $player
	 */
	public function register(Player $player)
	{
		$value = "INSERT INTO userdata (name, pass, ip, xuid, lang) VALUES (:name, :pass, :ip, :xuid, :lang)";
		$db = $this->db->prepare($value);

		$name = strtolower($player->getName());
		$pass = $player->pass;
		$ip   = $player->getAddress();
		$xuid = $player->getXuid();
		$lang = $player->lang;

		$pass_hash = password_hash($pass, PASSWORD_DEFAULT);

		$db->bindValue(":name", $name);
		$db->bindValue(":pass", $pass_hash);
		$db->bindValue(":ip"  , $ip);
		$db->bindValue(":xuid", $xuid);
		$db->bindValue(":lang", $lang);

		$db->execute();

		unset($player->lang);
		unset($player->pass);

		MainLogger::getLogger()->notice($name ." Register Account！");

		$player->setImmobile(false);
	}


	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isRegister(Player $player)
	{
		$xuid = $player->getXuid();

		$value = "SELECT xuid FROM userdata WHERE xuid = :xuid";
		$db = $this->db->prepare($value);

		$db->bindValue(":xuid", $xuid);

		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);

		return empty($result) ? false : true;
	}


	/**
	 * @param Player $player
	 */
	public function unRegister(string $name)
	{
		$data = $this->getUserData(null, $name);

		if (is_null($data)) return null;
		
		$value = "DELETE FROM userdata WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $name);

		$db->execute();

		MainLogger::getLogger()->info("§a". $name ." Deleted Account！");

		return true;
	}


	/**
	 * @param Player $player
	 * @return bool|null
	 */
	public function login(Player $player)
	{
		$name = strtolower($player->getName());
		$xuid = $player->getXuid();
		$ip   = $player->getAddress();

		$result = $this->isRegister($player);

		if ($result) {
			$data = $this->getUserData($player, $name);
			if (is_null($data)) return;
			if ($data["name"] === $name && $data["xuid"] === $xuid) {
				return $data["ip"] === $ip ? false : true;
			} else {
				return null;
			}
		}
	}


	/**
	 * @param Player $player
	 */
	public function updateIp(Player $player)
	{
		$name = strtolower($player->getName());
		$newIp = $player->getAddress();

		$value = "SELECT ip FROM userdata WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $name);

		$oldIp = $db->execute()->fetchArray(SQLITE3_ASSOC);

		if (empty($oldIp)) return null;

		$value = "UPDATE userdata SET ip = :ip WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $name);
		$db->bindValue(":ip"  , $newIp);

		$db->execute();

		MainLogger::getLogger()->notice("[". $name ."] ". $oldIp["ip"] ." => ". $newIp ." Updated IPAddress！");
	}


	/**
	 * @param Player $player
	 */
	public function updateName(string $oldName, string $newName)
	{
		$value = "SELECT name FROM userdata WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $oldName);

		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);

		if (empty($result)) return null;

		$value = "UPDATE userdata SET name = :newname WHERE name = :oldname";
		$db = $this->db->prepare($value);

		$db->bindValue(":oldname", $oldName);
		$db->bindValue(":newname", $newName);

		$db->execute();

		MainLogger::getLogger()->notice($oldName ." => ". $newName ."  Updated Name！");

		return true;
	}


	/**
	 * @param $player
	 * @return array|bool
	 */
	public function getUserData($player = null, $namae)
	{
		if (!is_null($player)) {
			$name = strtolower($player->getName());
		} else {
			$name = strtolower($namae);
		}

		$data = [];

		$value = "SELECT * FROM userdata WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $name);

		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);

		if (empty($result)) return null;

		foreach ($result as $key => $value) {
			$data[$key] = $value;
		}

		return $data;
	}


	/**
	 * @param Player $player
	 * @return array|bool
	 */
	public function getLang(Player $player)
	{
		$name = strtolower($player->getName());

		$value = "SELECT lang FROM userdata WHERE name = :name";
		$db = $this->db->prepare($value);

		$db->bindValue(":name", $name);

		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);

		if (empty($result)) return null;

		return $result;
	}

}