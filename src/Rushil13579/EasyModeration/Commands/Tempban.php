<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\{
    Server,
    Player
};

use pocketmine\plugin\Plugin;

use pocketmine\command\{
    Command,
    CommandSender,
    PluginIdentifiableCommand
};

use DateTime;
use InvalidArgumentException;

use Rushil13579\EasyModeration\Main;
use Rushil13579\EasyModeration\utils\Expiry;

class Tempban extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('tempban', 'Temporarily prevent a player from accessing this server', '/tempban <player> <time> [reason...]');
        $this->setPermission('easymoderation.tempban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 2){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /tempban <player> <time> [reason...]');
            return false;
        }

        $player = $this->main->getServer()->getPlayer($args[0]);

        if(count($args) > 2){
            $reason = implode(' ', array_slice($args, 2));
        } else {
            $reason = 'Banned by administrator';
        }

        $banList = $sender->getServer()->getNameBans();
        $sendername = $sender->getName();
        
        if($player != null){
            $playername = $player->getName();
        } else {
            $playername = $args[0];
        }

        if($banList->isBanned($playername)){
            $sender->sendMessage(Main::PREFIX . ' §cThis player is already banned');
            return false;
        }

        try {
            $expiry = new Expiry($args[1]);
            $expiryToString = Expiry::expirationTimerToString($expiry->getDate(), new DateTime());
            
            $banList->addBan($playername, $reason, $expiry->getDate(), $sendername);
            
            if($player != null){
                $msg = "§4You have been Temporarily Banned!\n§cBan Time: $expiryToString\nReason: $reason\nBanned By: $sendername";
                $player->kick($msg, false);
            }

            $msg = Main::PREFIX . " §aYou have Temporarily Banned §6$playername §afor §6$expiryToString §afor §6$reason";
            $sender->sendMessage($msg);

            if($this->main->cfg->get('tempban-discord-post') == 'enabled'){
                $webhook = $this->main->cfg->get('tempban-webhook');
    
                $msg = "__**NEW TEMP BAN**__\nPlayer Banned: $playername\nBan Time: $expiryToString\nBanned By: $sendername\nReason: $reason";
                $this->main->postToDiscord($webhook, $msg);
            }
        } catch (InvalidArgumentException $msg){
            $sender->sendMessage($msg->getMessage());
        }
    }

    public function getPlugin() : Plugin {
        return $this->main;
    }
}