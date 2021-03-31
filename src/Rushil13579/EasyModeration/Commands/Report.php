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

class Report extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('report', 'Report a player for flouting the rules', '/report <player> [reason...]');
        $this->setPermission('easymoderation.report');
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

        if(count($args) < 2){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /report <player> [reason...]');
            return false;
        }

        if($this->main->getServer()->getPlayer($args[0]) === null){
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }
        $player = $this->main->getServer()->getPlayer($args[0]);

        $reason = implode(' ', array_slice($args, 1));

        $sendername = $sender->getName();
        $playername = $player->getName();
        $cooldown = $this->main->cfg->get('report-cooldown');

        if($sendername === $playername){
            $sender->sendMessage(Main::PREFIX . ' §cYou cannot report yourself');
            return false;
        }

        if(!isset($this->main->reportcd[$sender->getName()])){
            $this->main->reportcd[$player->getName()] = time() + $cooldown;
        } else {
            if(time() < $this->main->reportcd[$player->getName()]){
                $rem = $this->main->reportcd[$player->getName()] - time();
                $sender->sendMessage(Main::PREFIX . " §cThis command is on cooldown for $rem seconds");
                return false;
            } else {
                unset($this->main->reportcd[$player->getName()]);
                $this->main->reportcd[$player->getName()] = time() + $cooldown;
            }
        }

        $sender->sendMessage(Main::PREFIX . " §aYou have reported §6$playername §afor §6$reason");

        if($this->main->cfg->get('report-discord-post') == 'enabled'){
            $webhook = $this->main->cfg->get('report-webhook');

            $msg = "__**NEW REPORT**__\nPlayer Reported: $playername\nReported By: $sendername\nReason: $reason";
            $this->main->postToDiscord($webhook, $msg);
        }
    }
}