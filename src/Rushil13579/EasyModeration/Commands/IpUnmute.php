<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\{
    Server,
    Player
};

use pocketmine\command\{
    Command,
    CommandSender
};

use Rushil13579\EasyModeration\Main;

class IpUnmute extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('ipunmute', 'Allows the specified IP address to chat on this server', '/ipunmute <address>');
        $this->setPermission('easymoderation.ipunmute');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /ipunmute <address>');
            return false;
        }

        $ipmuteList = $this->main->getIPMutes();
        if(!$ipmuteList->isBanned($args[0])){
            $sender->sendMessage(Main::PREFIX . ' §cThis IP is not muted');
            return false;
        }

        $ipmuteList->remove($args[0]);

        $sender->sendMessage(Main::PREFIX . ' §aYou have successfully unmuted §6' . $args[0]);

        if($this->main->cfg->get('ipunmute-discord-post') == 'enabled'){
            $webhook = $this->main->cfg->get('ipunmute-webhook');

            $msg = "__**NEW IP UNMUTE**__\nIP Unmuted: $args[0]\nUnmuted By: " . $sender->getName();
            $this->main->postToDiscord($webhook, $msg);
        }
    }
}