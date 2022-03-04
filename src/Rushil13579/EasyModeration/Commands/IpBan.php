<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class IpBan extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('ipban', 'Prevents the specified IP address from using this server', '/ipban <address|name> [reason...]');
        $this->setPermission('easymoderation.ipban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 1) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /ipban <address|name> [reason...]');
            return false;
        }

        $ip = filter_var($args[0], FILTER_VALIDATE_IP);
        $player = $this->main->getServer()->getPlayerExact($args[0]);
        $sendername = $sender->getName();

        $ipbanList = $this->main->getServer()->getIPBans();

        if($player != null) {
            $target = $player->getNetworkSession()->getIp();
            if($ipbanList->isBanned($target)) {
                $sender->sendMessage(Main::PREFIX . ' §cThis Player is already ip banned');
                return false;
            }
        } elseif($ip != null) {
            $target = $ip;
            if($ipbanList->isBanned($target)) {
                $sender->sendMessage(Main::PREFIX . ' §cThis IP is already banned');
                return false;
            }
        }

        if(count($args) > 1) {
            $reason = implode(' ', array_slice($args, 1));
        } else {
            $reason = 'Banned by administrator';
        }

        if($ip != null) {
            $ipbanList->addBan($ip, $reason, null, $sendername);

            foreach($this->main->getServer()->getOnlinePlayers() as $player) {
                if($player->getNetworkSession()->getIp() === $ip) {
                    $msg = "§4You have been IP Banned!\n§cReason: $reason\nBanned By: $sendername";
                    $player->kick($msg, false);
                }
            }

            $msg = Main::PREFIX . " §aYou have IP Banned §6$ip §afor §6$reason";
            $sender->sendMessage($msg);

            if($this->main->cfg->get('ipban-discord-post') == 'enabled') {
                $webhook = $this->main->cfg->get('ipban-webhook');

                $msg = "__**NEW PERM IP BAN**__\nIP Banned: $ip\nBanned By: $sendername\nReason: $reason";
                $this->main->postToDiscord($webhook, $msg);
            }
        } elseif($player != null) {
            $ip = $player->getNetworkSession()->getIp();
            $ipbanList->addBan($ip, $reason, null, $sendername);

            foreach($this->main->getServer()->getOnlinePlayers() as $player) {
                if($player->getNetworkSession()->getIp() === $ip) {
                    $msg = "§4You have been IP Banned!\n§cReason: $reason\nBanned By: $sendername";
                    $player->kick($msg, false);
                }
            }

            $playername = $player->getName();
            $msg = Main::PREFIX . " §aYou have IP Banned §6$ip belonging to §6$playername §afor §6$reason";
            $sender->sendMessage($msg);

            if($this->main->cfg->get('ipban-discord-post') == 'enabled') {
                $webhook = $this->main->cfg->get('ipban-webhook');

                $msg = "__**NEW PERM IP BAN**__\nIP Banned: $ip\nBelonging To: $playername\nBanned By: $sendername\nReason: $reason";
                $this->main->postToDiscord($webhook, $msg);
            }
        } else {
            $sender->sendMessage(Main::PREFIX . ' §cAddress|Player not found');
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}

