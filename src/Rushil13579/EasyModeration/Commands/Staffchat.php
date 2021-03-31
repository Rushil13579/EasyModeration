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

class Staffchat extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('staffchat', 'Toggle staff chat', '/staffchat', ['sc']);
        $this->setPermission('easymoderation.staffchat');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(Main::PREFIX . ' §cPlease use this command in-game');
            return false;
        }

        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(isset($this->main->staffchat[$sender->getName()])){
            unset($this->main->staffchat[$sender->getName()]);
            $sender->sendMessage(Main::PREFIX . ' §cStaff chat Disabled!');
        } else {
            $this->main->staffchat[$sender->getName()] = $sender->getName();
            $sender->sendMessage(Main::PREFIX . ' §aStaff chat Enabled!');
        }
    }
}