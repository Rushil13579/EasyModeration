<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class IpUnban extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('ipunban', 'Allows the specified IP address to use this server', '/ipunban <address>');
        $this->setPermission('easymoderation.ipunban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /ipunban <address>');
            return false;
        }

        $ipbanList = $this->main->getServer()->getIPBans();
        if(!$ipbanList->isBanned($args[0])) {
            $sender->sendMessage(Main::PREFIX . ' §cThis IP is not banned');
            return false;
        }

        $ipbanList->remove($args[0]);

        $sender->sendMessage(Main::PREFIX . ' §aYou have successfully unbanned §6' . $args[0]);

        if($this->main->cfg->get('ipunban-discord-post') == 'enabled') {
            $webhook = $this->main->cfg->get('ipunban-webhook');

            $msg = "__**NEW IP UNBAN**__\nIP Unbanned: $args[0]\nUnbanned By: " . $sender->getName();
            $this->main->postToDiscord($webhook, $msg);
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}