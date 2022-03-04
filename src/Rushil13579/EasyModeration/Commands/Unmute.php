<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class Unmute extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('unmute', 'Allows the specified player to chat on this server', '/unmute <name>');
        $this->setPermission('easymoderation.unmute');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /unmute <name>');
            return false;
        }

        $player = $this->main->getServer()->getPlayerExact($args[0]);

        if($player != null) {
            $playername = $player->getName();
        } else {
            $playername = $args[0];
        }

        $muteList = $this->main->getNameMutes();
        if(!$muteList->isBanned($args[0])) {
            $sender->sendMessage(Main::PREFIX . ' §cThis player is not muted');
            return false;
        }

        $muteList->remove($playername);

        if($player != null) {
            $player->sendMessage('§aYou have been unmuted!');
        }

        $sender->sendMessage(Main::PREFIX . ' §aYou have successfully unmuted §6' . $args[0]);

        if($this->main->cfg->get('unmute-discord-post') == 'enabled') {
            $webhook = $this->main->cfg->get('unmute-webhook');

            $msg = "__**NEW PLAYER UNMUTE**__\nPlayer Unmuted: $args[0]\nUnmuted By: " . $sender->getName();
            $this->main->postToDiscord($webhook, $msg);
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}