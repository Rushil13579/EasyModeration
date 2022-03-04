<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class Kick extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('kick', 'Removes the specified player from the server', '/kick <player> [reason...]');
        $this->setPermission('easymoderation.kick');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /kick <player> [reason...]');
            return false;
        }

        if($this->main->getServer()->getPlayerExact($args[0]) === null) {
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }
        $player = $this->main->getServer()->getPlayerExact($args[0]);

        if(count($args) > 2) {
            $reason = implode(' ', array_slice($args, 1));
        } else {
            $reason = 'Kicked by administrator';
        }

        $sendername = $sender->getName();
        $playername = $player->getName();

        $msg = "§4You have been Kicked!\n§cReason: $reason\nKicked By: $sendername";
        $player->kick($msg, false);

        $msg = Main::PREFIX . " §aYou have Kicked §6$playername §afor §6$reason";
        $sender->sendMessage($msg);

        if($this->main->cfg->get('kick-discord-post') == 'enabled') {
            $webhook = $this->main->cfg->get('kick-webhook');

            $msg = "__**NEW KICK**__\nPlayer Kicked: $playername\nKicked By: $sendername\nReason: $reason";
            $this->main->postToDiscord($webhook, $msg);
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}