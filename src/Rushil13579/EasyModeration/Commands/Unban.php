<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class Unban extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('unban', 'Allows the specified player to use this server', '/unban <name>');
        $this->setPermission('easymoderation.unban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /unban <name>');
            return false;
        }

        $banList = $this->main->getServer()->getNameBans();
        if(!$banList->isBanned($args[0])) {
            $sender->sendMessage(Main::PREFIX . ' §cThis player is not banned');
            return false;
        }

        $banList->remove($args[0]);

        $sender->sendMessage(Main::PREFIX . ' §aYou have successfully unbanned §6' . $args[0]);

        if($this->main->cfg->get('unban-discord-post') == 'enabled') {
            $webhook = $this->main->cfg->get('unban-webhook');

            $msg = "__**NEW PLAYER UNBAN**__\nPlayer Unbanned: $args[0]\nUnbanned By: " . $sender->getName();
            $this->main->postToDiscord($webhook, $msg);
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}