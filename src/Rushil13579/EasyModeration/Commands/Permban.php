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

use Rushil13579\EasyModeration\Main;

class Permban extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('permban', 'Prevents the specified player from using this server', '/permban <name> [reason...]');
        $this->setPermission('easymoderation.permban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /permban <name> [reason...]');
            return false;
        }

        $banList = $this->main->getServer()->getNameBans();
        if($banList->isBanned($args[0])){
            $sender->sendMessage(Main::PREFIX . ' §cThis player is already banned');
            return false;
        }

        $player = $this->main->getServer()->getPlayer($args[0]);

        if(count($args) > 2){
            $reason = implode(' ', array_slice($args, 1));
        } else {
            $reason = 'Banned by administrator';
        }

        if($player != null){
            $playername = $player->getName();
        } else {
            $playername = $args[0];
        }

        $banList->addBan($playername, $reason, null, $sender->getName());
        $sendername = $sender->getName();

        if($player != null){
            $msg = "§4You have been Permanently Banned!\n§cReason: $reason\nBanned By: $sendername";
            $player->kick($reason, false);
        }

        $msg = Main::PREFIX . " §aYou have Permanently Banned §6$playername §afor §6$reason";
        $sender->sendMessage($msg);

        if($this->main->cfg->get('permban-discord-post') == 'enabled'){
            $webhook = $this->main->cfg->get('permban-webhook');

            $msg = "__**NEW PERM BAN**__\nPlayer Banned: $playername\nBanned By: $sendername\nReason: $reason";
            $this->main->postToDiscord($webhook, $msg);
        }
    }

    public function getPlugin() : Main {
        return $this->main;
    }
}