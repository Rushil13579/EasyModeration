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

use pocketmine\entity\Entity;

use Rushil13579\EasyModeration\Main;

class Vanish extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('vanish', 'Toggles the vanish status of the specified player', '/vanish', ['v']);
        $this->setPermission('easymoderation.vanish');
        $this->setUsage('/vanish');
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

        if(isset($this->main->vanish[$sender->getName()])){
            $sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
            $sender->setNameTagVisible(true);
            $sender->sendMessage(Main::PREFIX . ' §cUnvanished!');
            unset($this->main->vanish[$sender->getName()]);
        } else {
            $sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
            $sender->setNameTagVisible(false);
            $sender->sendMessage(Main::PREFIX . ' §aVanished!');
            $this->main->vanish[$sender->getName()] = $sender->getName();
        }
    }
}