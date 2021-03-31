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

class Mutechat extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('mutechat', 'Prevents all players from chatting on the server', '/mutechat [reason...]');
        $this->setPermission('easymoderation.mutechat');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) > 0){
            $reason = implode(' ', $args);
        } else {
            $reason = 'Muted by administrator';
        }

        $sendername = $sender->getName();

        if(isset($this->main->mutechat)){
            unset($this->main->mutechat);
            $this->main->getServer()->broadcastMessage(Main::PREFIX . ' §aGlobal chat is now unmuted! You can talk!');
        } else {
            $this->main->mutechat = 'on';
            $this->main->getServer()->broadcastMessage(Main::PREFIX . " §4Global chat is now muted! You cannot talk!\n§cMuted By: $sendername\nReason: $reason");

            if($this->main->cfg->get('mutechat-discord-post') == 'enabled'){
                $webhook = $this->main->cfg->get('mutechat-webhook');
    
                $msg = "__**NEW MUTECHAT**__\nMuted By: $sendername\nReason: $reason";
                $this->main->postToDiscord($webhook, $msg);
            }
        }
    }
}