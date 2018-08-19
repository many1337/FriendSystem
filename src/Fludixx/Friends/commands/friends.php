<?php
namespace Fludixx\Friends\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as f;
use pocketmine\Player;
use Fludixx\Friends\FriendAPI as api;
class friends extends Command
{
	private $api;
	public function __construct(api $api)
	{
		parent::__construct("friends", "Friends Command -> /friends <ADD|DEL|LIST|ACCEPT> \"Optional Argument\"", "/friends <ADD|DEL|LIST|ACCEPT> 'Optional Argument'");
		$this->api = $api;
	}
	public function execute(CommandSender $sender, string $label, array $args): bool
	{
		if(isset($args[0])) {
			if($args[0] == "add" || $args[0] == "ADD" || $args[0] == "invite") {
				if(isset($args[1])) {
					$player = $this->api->getServer()->getPlayer($args[1]);
					$friends = $this->api->listFriends($sender->getName());
					$areAlreadyFriends = array_search($player->getName(), $friends);
					if($player && $player->getName() != $sender->getName() && !$areAlreadyFriends) {
						$player->sendMessage(api::PREFIX."Hey! ".$sender->getName()."want's to be your Friend!\n"
							.f::GOLD."Use: ".f::WHITE."/friends accept ".$sender->getName().f::GOLD." to accept the Request!");
						$friend_data = new Config("/cloud/friend_requests.json", Config::JSON);
						$friend_data->set($sender->getName(), $player->getName());
						$friend_data->save();
						$sender->sendMessage(api::PREFIX.$player->getName()." has recived your Friendrequest!");
						return true;
					} else {
						$sender->sendMessage(api::PREFIX."Sorry! :( That Player isen't Online!");
						return true;
					}
				} else {
					$sender->sendMessage(api::PREFIX."/friends add PLAYERNAME");
					return true;
				}
			}
			elseif($args[0] == "accept" || $args[0] == "ACCEPT" || $args[0] == "apt") {
				if(isset($args[1])) {
					$friend_data = new Config("/cloud/friend_requests.json", Config::JSON);
					if($friend_data->get($args[1]) == $sender->getName()) {
						$friend_data->set($args[1], false);
						$this->api->addFriend($sender->getName(), $args['1']);
						$this->api->addFriend($args[1], $sender->getName());
						$player = $this->api->getServer()->getPlayerExact($args[1]);
						if($player) {
							$player->sendMessage(api::PREFIX.$sender->getName()." accepted your Friendrequest!");
						}
						$sender->sendMessage(api::PREFIX.$args[1]." is now in your Friendlist!");
						return true;
					} else {
						$sender->sendMessage(api::PREFIX."That Player diden't send you an Friendrequest! :(");
						return true;
					}
				} else {
					$sender->sendMessage(api::PREFIX."/friends accept PLAYERNAME");
					return true;
				}
			}
			elseif($args[0] == "del" || $args[0] == "DELETE" || $args[0] == "rm") {
				if(isset($args[1])) {
					$c = new Config("/cloud/users/".$sender->getName().".yml", 2);
					$friends = (array)$c->get("friends");
					$isFriend = array_search($args[1], $friends);
					if(!$isFriend) {
						$sender->sendMessage(api::PREFIX."Hmm.. i coundn't find $args[1] in your Friendlist!");
						return true;
					} else {
						$this->api->rmFriend($sender->getName(), $args[1]);
						$this->api->rmFriend($args[1], $sender->getName());
						$sender->sendMessage(api::PREFIX."You and $args[1] aren't Friends anymore!");
						return true;
					}
				} else {
					$sender->sendMessage(api::PREFIX."/friends del PLAYERNAME");
					return true;
				}
			}
			elseif($args[0] == "list" || $args[0] == "LIST" || $args[0] == "lst") {
				$friends = $this->api->listFriends($sender->getName());
				$counter = 0;
				foreach($friends as $nr => $friend) {
					if($nr != 0) {
						$string = $friend;
						$c = new Config("/cloud/users/$friend.yml", 2);
						$online = $c->get("online");
						if ($online != false) {
							$string = $friend . " " . f::GREEN . " [ONLINE] ".f::YELLOW."[$online]";
						} else {
							$string = $friend . " " . f::RED . " [OFFLINE]";
						}
						$sender->sendMessage($string);
						$counter++;
					}
				}
				$sender->sendMessage(api::PREFIX."You have $counter Freinds!");
				return true;
			}
		}
		return false;
	}
}