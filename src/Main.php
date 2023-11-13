<?php

namespace GameParrot\PlayerShortcuts;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\EventPriority;
use pocketmine\Server;

class Main extends PluginBase implements Listener {
    private function dist3d(float $x1, float $y1, float $z1, float $x2, float $y2, float $z2): float {
        $dx = abs($x1 - $x2);
        $dy = abs($y1 - $y2);
        $dz = abs($z1 - $z2);
        return sqrt(pow($dx,2) + pow($dy,2) + pow($dz,2));
    }
	public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\server\\CommandEvent", function(CommandEvent $event) {
            $sender = $event->getSender();
            if (!$sender instanceof Player || $sender->hasPermission("playershortcuts.use")) {
                $args = explode(" ", trim($event->getCommand()));
                for($i=1; $i<count($args); $i++) {
                    if($args[$i] == "@s" && $sender instanceof Player) {
                        $args[$i] = $sender->getName();
                    }
                    if($args[$i] == "@p" && $sender instanceof Player) {
                        $closestDist = -1;
                        $nearestPlayer = $sender;
                        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                            if ($player != $sender) {
                                $dist=$this->dist3d($sender->getPosition()->x, $sender->getPosition()->y, $sender->getPosition()->z, $player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z);
                                if ($closestDist == -1 || $dist < $closestDist) {
                                    $nearestPlayer = $player;
                                    $closestDist = $dist;
                                }
                            }
                        }
                        $args[$i] = $nearestPlayer->getName();
                    }
                    if($args[$i] == "@r") {
                        if (count(Server::getInstance()->getOnlinePlayers()) > 0) {
                            $ii = 0;
                            $chosen = rand(0,count(Server::getInstance()->getOnlinePlayers())-1);
                            foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                                if($ii==$chosen) {
                                    $args[$i] = $player->getName();
                                    break;
                                }
                                $ii++;
                            }
                        }
                    }
                }
                for($i=1; $i<count($args); $i++) {
                    if($args[$i] == "@a") {
                        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                            $args[$i] = $player->getName();
                            Server::getInstance()->getCommandMap()->dispatch($sender, implode(" ", $args));
                            $event->cancel();
                        }
                        return;
                    }
                }
                $event->setCommand(implode(" ", $args));
            }
        }, EventPriority::HIGHEST, $this);
    }
}
