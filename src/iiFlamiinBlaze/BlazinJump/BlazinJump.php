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
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class BlazinJump extends PluginBase implements Listener{

    private const VERSION = "v1.0.2";
    private const PREFIX = TextFormat::AQUA . "BlazinJump" . TextFormat::GOLD . " > ";

    /** @var array $jumps */
    public $jumps = [];
    /** @var self $instance */
    private static $instance;

    public function onEnable() : void{
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getLogger()->info("BlazinJump " . self::VERSION . " by BlazeTheDev is enabled");
    }

    private function multiWorldCheck(Entity $entity) : bool{
        if(!$entity instanceof Player) return false;
        if($this->getConfig()->get("multi-world") === "on"){
            if(in_array($entity->getLevel()->getName(), $this->getConfig()->get("worlds"))){
                return true;
            }else{
                $entity->sendMessage(self::PREFIX . TextFormat::RED . "You are not in the right world for you to be able to double jump");
                return false;
            }
        }elseif($this->getConfig()->get("multi-world") === "off") return true;
        return true;
    }

    public function onPreLogin(PlayerPreLoginEvent $event) : void{
        $player = $event->getPlayer();
        $this->jumps[$player->getName()] = 0;
    }

    public function onJump(PlayerJumpEvent $event) : void{
        $player = $event->getPlayer();
        $this->jumps[$player->getName()]++;
        if($this->multiWorldCheck($player) === false) return;
        if($this->jumps[$player->getName()] === 1) $this->getServer()->getScheduler()->scheduleDelayedTask(new BlazinJumpTask($this, $player), 30);
        if($this->jumps[$player->getName()] === 2){
            $player->knockBack($player, 0, $player->getDirectionVector()->getX(), $player->getDirectionVector()->getZ(), (int)$this->getConfig()->get("jump-power"));
            $this->jumps[$player->getName()] = 0;
        }
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}