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

class Alts extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('alts', 'Get a list of the players who have logged in from the same ip address as the specified player', '/alts <player>');
        $this->setPermission('easymoderation.alts');
        $this->setUsage('/alts <player>');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /alts <player>');
            return false;
        }

        if($this->main->getServer()->getPlayer($args[0]) === null){
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }
        $player = $this->main->getServer()->getPlayer($args[0]);

        $altList = '';
        $banList = $this->main->getServer()->getNameBans();
        $content = file_get_contents($this->main->getDataFolder() . "alts/" . $player->getAddress(), true);
		$array = array_unique(explode(",\n", $content));
        array_pop($array);
        foreach($array as $alt){
            $this->main->getServer()->broadcastMessage($alt);
            if($banList->isBanned($alt)){
                $altList .= '§4' . $alt . '§7, ';
            } else {
                $altList .= '§a' . $alt . '§7, ';
            }
        }
		$sender->sendMessage("§bCurrent accounts found under players IP: \n$altList");
    }
}