<?php

declare(strict_types=1);

namespace Fludixx\Friends;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as f;

class FriendAPI extends PluginBase implements Listener {

	const PREFIX = f::DARK_GRAY."[".f::YELLOW."Friends".f::DARK_GRAY."] | ".f::WHITE;
	const NAME = f::YELLOW."Friends".f::WHITE;
	const VERSION = 0.1;
	const API = 3;
	const LIMIT = 50;
	private static $instance = null;

	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		self::$instance = $this;
		$this->registerCommands();
	}
	public function addFriend(string $playername, string $newfriend): bool {
		$c = new Config("/cloud/users/$playername.yml", 2);
		$friends = (array)$c->get("friends");
		$action = array_push($friends, $newfriend);
		$c->set("friends", $friends);
		$c->save();
		return (bool)$action;
	}
	public function rmFriend(string $playername, string $oldFriend): void {
		$c = new Config("/cloud/users/$playername.yml", 2);
		$friends = (array)$c->get("friends");
		if (($key = array_search($oldFriend, $friends)) !== false) {
			 unset($friends[$key]);
		}
		$c->set("friends", $friends);
		$c->save();
	}
	public function listFriends(string $playername): array {
		$c = new Config("/cloud/users/$playername.yml", 2);
		$freinds = (array)$c->get("friends");
		return $freinds;
	}

	public function onDisable() : void{
		$this->getLogger()->info("Friends-API Disabled");
	}
	public static function getInstance(){
		return self::$instance;
	}
	private function registerCommands(){
		$map = $this->getServer()->getCommandMap();
		$commands = [
			"\\Fludixx\\Friends\\commands\\friends" => "friends"
		];
		foreach($commands as $class => $cmd){
			$map->register("Friends", new $class($this));
		}
	}
	public function onJoin(PlayerJoinEvent $event) {
		$c = new Config("/cloud/users/".$event->getPlayer()->getName().".yml", 2);
		$c->set("online", $this->getServer()->getPort());
		$c->save();
	}
	public function onQuit(PlayerQuitEvent $event) {
		$c = new Config("/cloud/users/".$event->getPlayer()->getName().".yml", 2);
		$c->set("online", false);
		$c->save();
	}
}
