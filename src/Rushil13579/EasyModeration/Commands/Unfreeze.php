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

class Unfreeze extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('unfreeze', 'Unfreezes the specified player', '/unfreeze <player>');
        $this->setPermission('easymoderation.unfreeze');
        $this->setUsage('/unfreeze <player>');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /unfreeze <player>');
            return false;
        }

        if($this->main->getServer()->getPlayer($args[0]) === null){
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }

        $player = $this->main->getServer()->getPlayer($args[0]);

        if(!isset($this->main->frozen[$player->getName()])){
            $sender->sendMessage(Main::PREFIX . ' §cThe specified player is not frozen');
            return false;
        }

        unset($this->main->frozen[$player->getName()]);

        if($this->main->cfg->get('freeze-tag') == 'true'){
            $player->setNameTag($player->getName());
        }
        $player->sendMessage('§aYou have been Unfrozen!');
        
        $sender->sendMessage(Main::PREFIX . ' §aYou have Unfrozen §6' . $player->getName());
    }
}