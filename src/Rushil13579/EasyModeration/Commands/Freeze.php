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

class Freeze extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('freeze', 'Freezes the specified player', '/freeze <player>');
        $this->setPermission('easymoderation.freeze');
        $this->setUsage('/freeze <player>');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /freeze <player>');
            return false;
        }

        if($this->main->getServer()->getPlayer($args[0]) === null){
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }

        $player = $this->main->getServer()->getPlayer($args[0]);

        if(isset($this->main->frozen[$player->getName()])){
            $sender->sendMessage(Main::PREFIX . ' §cThe specified player is already frozen');
            return false;
        }

        $this->main->frozen[$player->getName()] = $player->getName();

        if($this->main->cfg->get('freeze-tag') == 'true'){
            $player->setNameTag($player->getName() . "\n§3[§bFrozen§3]");
        }
        $player->sendMessage('§4You have been Frozen!');
        
        $sender->sendMessage(Main::PREFIX . ' §aYou have Frozen §6' . $player->getName());
    }
}