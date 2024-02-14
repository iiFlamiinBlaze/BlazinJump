<?php
/**
 *  ____  _            _______ _          _____
 * |  _ \| |          |__   __| |        |  __ \
 * | |_) | | __ _ _______| |  | |__   ___| |  | | _____   __
 * |  _ <| |/ _` |_  / _ \ |  | '_ \ / _ \ |  | |/ _ \ \ / /
 * | |_) | | (_| |/ /  __/ |  | | | |  __/ |__| |  __/\ V /
 * |____/|_|\__,_/___\___|_|  |_| |_|\___|_____/ \___| \_/
 *
 * Copyright (C) 2018 iiFlamiinBlaze
 *
 * iiFlamiinBlaze's plugins are licensed under MIT license!
 * Made by iiFlamiinBlaze for the PocketMine-MP Community!
 *
 * @author iiFlamiinBlaze
 * Twitter: https://twitter.com/iiFlamiinBlaze
 * GitHub: https://github.com/iiFlamiinBlaze
 * Discord: https://discord.gg/znEsFsG
 */
declare(strict_types=1);

namespace iiFlamiinBlaze\BlazinJump;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class BlazinJump extends PluginBase implements Listener{
	
	private const VERSION = "v1.2.0";
	private const PREFIX = TextFormat::AQUA . "BlazinJump" . TextFormat::GOLD . " > ";
	
	public array $jumps = [];
	private static self $instance;
	
	public function onEnable() : void{
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->getLogger()->info("BlazinJump " . self::VERSION . " by BlazeTheDev is enabled");
	}
	
	public function onLogin(PlayerLoginEvent $event) : void{
		$this->jumps[$event->getPlayer()->getName()] = 0;
	}
	
	public function onJoin(PlayerJoinEvent $event) : void{
		$this->multiWorldCheck($event->getPlayer());
	}
	
	public function onQuit(PlayerQuitEvent $event) : void{
		if(isset($this->jumps[$event->getPlayer()->getName()])) unset($this->jumps[$event->getPlayer()->getName()]);
	}
	
	public function onJump(PlayerJumpEvent $event) : bool{
		$player = $event->getPlayer();
		$this->jumps[$player->getName()]++;
		if($this->jumps[$player->getName()] === 1) $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) : void{
			$this->jumps[$player->getName()] = 0;
		}), 30);
		if($this->jumps[$player->getName()] === 2){
			if($this->multiWorldCheck($player) === false) return false;
			$player->knockBack($player->getDirectionVector()->getX(), $player->getDirectionVector()->getZ(), (float)$this->getConfig()->get("jump-power"));
			$this->jumps[$player->getName()] = 0;
		}
		return true;
	}
	
	private function multiWorldCheck(Entity $entity) : bool{
		if(!$entity instanceof Player) return false;
		if($this->getConfig()->get("multi-world") === "on"){
			if(!in_array($entity->getWorld()->getDisplayName(), $this->getConfig()->get("worlds"))){
				$entity->sendMessage(self::PREFIX . TextFormat::RED . "You are not in the right world to be able to double jump");
				return false;
			}
		}elseif($this->getConfig()->get("multi-world") === "off") return true;
		return true;
	}
	
	public static function getInstance() : self{
		return self::$instance;
	}
}