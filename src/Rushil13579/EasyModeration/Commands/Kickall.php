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

class Kickall extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('kickall', 'Removes all the players from the server', '/kickall [reason...]');
        $this->setPermission('easymoderation.kickall');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(isset($args[0])){
            $reason = implode(' ', $args);
        } else {
            $reason = 'Kicked by administrator';
        }

        $sendername = $sender->getName();

        foreach($this->main->getServer()->getOnlinePlayers() as $player){
            if($player->getName() != $sendername){
                $msg = "§4You have been Kicked!\n§cReason: $reason\nKicked By: $sendername";
                $player->kick($msg, false);
            }
        }

        $msg = Main::PREFIX . " §aYou have Kicked everyone for §6$reason";
        $sender->sendMessage($msg);

        if($this->main->cfg->get('kickall-discord-post') == 'enabled'){
            $webhook = $this->main->cfg->get('kickall-webhook');

            $msg = "__**NEW KICKALL**__\nKicked By: $sendername\nReason: $reason";
            $this->main->postToDiscord($webhook, $msg);
        }
    }
}