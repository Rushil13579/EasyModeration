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

use DateTime;
use InvalidArgumentException;

use Rushil13579\EasyModeration\Main;
use Rushil13579\EasyModeration\utils\Expiry;

class TempIpBan extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('tempipban', 'Temporarily prevent a ip address from accessing this server', '/tempipban <address|name> <time> [reason...]');
        $this->setPermission('easymoderation.tempipban');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 2){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /tempipban <address|name> <time> [reason...]');
            return false;
        }

        $ip = filter_var($args[0], FILTER_VALIDATE_IP);
        $player = $this->main->getServer()->getPlayer($args[0]);
        $sendername = $sender->getName();

        $ipbanList = $this->main->getServer()->getIPBans();

        if($player != null){
            $target = $player->getAddress();
            if($ipbanList->isBanned($target)){
                $sender->sendMessage(Main::PREFIX . ' §cThis Player is already ip banned');
                return false;
            }
        } elseif ($ip != null){
            $target = $ip;
            if($ipbanList->isBanned($target)){
                $sender->sendMessage(Main::PREFIX . ' §cThis IP is already banned');
                return false;
            }
        }

        if(count($args) > 2){
            $reason = implode(' ', array_slice($args, 2));
        } else {
            $reason = 'Banned by administrator';
        }

        try {
            if($ip != null){
                $expiry = new Expiry($args[1]);
                $expiryToString = Expiry::expirationTimerToString($expiry->getDate(), new DateTime());

                $ipbanList->addBan($ip, $reason, $expiry->getDate(), $sendername);

                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    if($player->getAddress() === $ip){
                        $msg = "§4You have been Temporarily IP Banned!\n§cBan Time: $expiryToString\nReason: $reason\nBanned By: $sendername";
                        $player->kick($msg, false);
                    }
                }

                $msg = Main::PREFIX . " §aYou have Temporarily IP Banned §6$ip §afor §6$expiryToString §afor §6$reason";
                $sender->sendMessage($msg);

                if($this->main->cfg->get('tempipban-discord-post') == 'enabled'){
                    $webhook = $this->main->cfg->get('tempipban-webhook');
  
                    $msg = "__**NEW TEMP IP BAN**__\nIP Banned: $ip\nBan Time: $expiryToString\nBanned By: $sendername\nReason: $reason";
                    $this->main->postToDiscord($webhook, $msg);
                }
            } elseif ($player != null){
                $expiry = new Expiry($args[1]);
                $expiryToString = Expiry::expirationTimerToString($expiry->getDate(), new DateTime());

                $playername = $player->getName();
                $ip = $player->getAddress();
                
                $ipbanList->addBan($ip, $reason, $expiry->getDate(), $sendername);

                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    if($player->getAddress() === $ip){
                        $msg = "§4You have been Temporarily IP Banned!\n§cBan Time: $expiryToString\nReason: $reason\nBanned By: $sendername";
                        $player->kick($msg, false);
                    }
                }
                    
                $msg = Main::PREFIX . " §aYou have Temporarily IP Banned §6$ip belonging to §6$playername §afor §6$expiryToString §afor §6$reason";
                $sender->sendMessage($msg);
    
                if($this->main->cfg->get('tempipban-discord-post') == 'enabled'){
                    $webhook = $this->main->cfg->get('ipban-webhook');
                
                    $msg = "__**NEW TEMP IP BAN**__\nIP Banned: $ip\nBelonging To: $playername\nBan Time: $expiryToString\nBanned By: $sendername\nReason: $reason";
                    $this->main->postToDiscord($webhook, $msg);
                }
            } else {
                $sender->sendMessage(Main::PREFIX . ' §cAddress|Player not found');
            }
        } catch (InvalidArgumentException $msg){
            $sender->sendMessage($msg->getMessage());
        }
    }
}