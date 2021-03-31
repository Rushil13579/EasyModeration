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

class Spy extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('spy', 'Spy on the commands run by other players', '/spy');
        $this->setPermission('easymoderation.spy');
        $this->setUsage('/spy');
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

        if(isset($this->main->spy[$sender->getName()])){
            unset($this->main->spy[$sender->getName()]);
            $sender->sendMessage(Main::PREFIX . ' §cDisabled Spy mode!');
        } else {
            $this->main->spy[$sender->getName()] = 'on';
            $sender->sendMessage(Main::PREFIX . ' §aEnabled Spy mode!');
        }
    }
}