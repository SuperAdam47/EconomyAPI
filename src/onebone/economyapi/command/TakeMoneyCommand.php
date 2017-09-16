<?php

namespace onebone\economyapi\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

if(version_compare(\pocketmine\API_VERSION, "3.0.0-ALPHA7") >= 0){
	abstract class _TakeMoneyCommand extends Command{
		public function execute(CommandSender $sender, string $label, array $args): bool{
			return $this->_execute($sender, $label, $args);
		}

		abstract public function _execute(CommandSender $sender, string $label, array $args): bool;
	}
}else{
	abstract class _TakeMoneyCommand extends Command{
		public function execute(CommandSender $sender, $label, array $args){
			return $this->_execute($sender, $label, $args);
		}

		abstract public function _execute(CommandSender $sender, string $label, array $args): bool;
	}
}

class TakeMoneyCommand extends _TakeMoneyCommand{
	private $plugin;

	public function __construct(EconomyAPI $plugin){
		$desc = $plugin->getCommandMessage("takemoney");
		parent::__construct("takemoney", $desc["description"], $desc["usage"]);

		$this->setPermission("economyapi.command.takemoney");

		$this->plugin = $plugin;
	}

	public function _execute(CommandSender $sender, string $label, array $params): bool{
		if(!$this->plugin->isEnabled()) return false;
		if(!$this->testPermission($sender)){
			return false;
		}

		$player = array_shift($params);
		$amount = array_shift($params);

		if(!is_numeric($amount)){
			$sender->sendMessage(TextFormat::RED . "§3Kullanım :§7 " . $this->getUsage());
			return true;
		}

		if(($p = $this->plugin->getServer()->getPlayer($player)) instanceof Player){
			$player = $p->getName();
		}

		if($amount < 0){
			$sender->sendMessage($this->plugin->getMessage("takemoney-invalid-number", [$amount], $sender->getName()));
			return true;
		}

		$result = $this->plugin->reduceMoney($player, $amount);
		switch($result){
			case EconomyAPI::RET_INVALID:
			$sender->sendMessage($this->plugin->getMessage("takemoney-player-lack-of-money", [$player, $amount, $this->plugin->myMoney($player)], $sender->getName()));
			break;
			case EconomyAPI::RET_SUCCESS:
			$sender->sendMessage($this->plugin->getMessage("takemoney-took-money", [$player, $amount], $sender->getName()));

			if($p instanceof Player){
				$p->sendMessage($this->plugin->getMessage("takemoney-money-taken", [$amount], $sender->getName()));
			}
			break;
			case EconomyAPI::RET_CANCELLED:
			$sender->sendMessage($this->plugin->getMessage("takemoney-failed", [], $sender->getName()));
			break;
			case EconomyAPI::RET_NO_ACCOUNT:
			$sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
			break;
		}

		return true;
	}
}

