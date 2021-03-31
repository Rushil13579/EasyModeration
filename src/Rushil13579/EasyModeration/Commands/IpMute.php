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

class IpMute extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('ipmute', 'Temporarily prevent a ip address from chatting on this server', '/ipmute <address|name> <time> [reason...]');
        $this->setPermission('easymoderation.ipmute');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 2){
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /ipmute <address|name> <time> [reason...]');
            return false;
        }

        $ip = filter_var($args[0], FILTER_VALIDATE_IP);
        $player = $this->main->getServer()->getPlayer($args[0]);
        $sendername = $sender->getName();

        $ipmuteList = $this->main->getIPMutes();

        if($player != null){
            $target = $player->getAddress();
            if($ipmuteList->isBanned($target)){
                $sender->sendMessage(Main::PREFIX . ' §cThis Player is already ip muted');
                return false;
            }
        } elseif ($ip != null){
            $target = $ip;
            if($ipmuteList->isBanned($target)){
                $sender->sendMessage(Main::PREFIX . ' §cThis IP is already muted');
                return false;
            }
        }

        if(count($args) > 2){
            $reason = implode(' ', array_slice($args, 2));
        } else {
            $reason = 'Muted by administrator';
        }

        if($args[1] == 'inf' or $args[1] == 'infinite' or $args[1] == 'permanent' or $args[1] == 'perm' or $args[1] == 'forever'){
            
            if($ip != null){

                $ipmuteList->addBan($ip, $reason, null, $sendername);

                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    if($player->getAddress() == $ip){
                        $msg = "§4You have been Permanently IP Muted!\nReason: $reason\nMuted By: $sendername";
                        $player->sendMessage($msg);
                    }
                }

                $msg = Main::PREFIX . " §aYou have Permanently IP Muted §6$ip §afor §6$reason";
                $sender->sendMessage($msg);

                if($this->main->cfg->get('ipmute-discord-post') == 'enabled'){
                    $webhook = $this->main->cfg->get('ipmute-webhook');

                    $msg = "__**NEW PERM IP MUTE**__\nIP Muted: $ip\nMuted By: $sendername\nReason: $reason";
                    $this->main->postToDiscord($webhook, $msg);
                }

            } elseif ($player != null){
                
                $ipmuteList->addBan($ip, $reason, null, $sendername);

                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    if($player->getAddress() == $ip){
                        $msg = "§4You have been Permanently IP Muted!\nReason: $reason\nMuted By: $sendername";
                        $player->kick($msg, false);
                    }
                }
                    
                $msg = Main::PREFIX . " §aYou have Permanently IP Muted §6$ip belonging to §6$playername §afor §6$reason";
                $sender->sendMessage($msg);
    
                if($this->main->cfg->get('ipmute-discord-post') == 'enabled'){
                    $webhook = $this->main->cfg->get('ipmute-webhook');
                
                    $msg = "__**NEW PERM IP MUTE**__\nIP Muted: $ip\nBelonging To: $playername\nMuted By: $sendername\nReason: $reason";
                    $this->main->postToDiscord($webhook, $msg);
                }

            } else {
                $sender->sendMessage(Main::PREFIX . ' §cAddress|Player not found');
            }

        } else {
            try {
                if($ip != null){
                    $expiry = new Expiry($args[1]);
                    $expiryToString = Expiry::expirationTimerToString($expiry->getDate(), new DateTime());

                    $ipmuteList->addBan($ip, $reason, $expiry->getDate(), $sendername);

                    foreach($this->main->getServer()->getOnlinePlayers() as $player){
                        if($player->getAddress() == $ip){
                            $msg = "§4You have been Temporarily IP Muted!\n§cMute Time: $expiryToString\nReason: $reason\nMuted By: $sendername";
                            $player->sendMessage($msg);
                        }
                    }

                    $msg = Main::PREFIX . " §aYou have Temporarily IP Muted §6$ip §afor §6$expiryToString §afor §6$reason";
                    $sender->sendMessage($msg);

                    if($this->main->cfg->get('ipmute-discord-post') == 'enabled'){
                        $webhook = $this->main->cfg->get('ipmute-webhook');
    
                        $msg = "__**NEW TEMP IP MUTE**__\nIP Muted: $ip\nMute Time: $expiryToString\nMuted By: $sendername\nReason: $reason";
                        $this->main->postToDiscord($webhook, $msg);
                    }
                } elseif ($player != null){
                    $expiry = new Expiry($args[1]);
                    $expiryToString = Expiry::expirationTimerToString($expiry->getDate(), new DateTime());

                    $playername = $player->getName();
                    $ip = $player->getAddress();
                    
                    $ipmuteList->addBan($ip, $reason, $expiry->getDate(), $sendername);

                    foreach($this->main->getServer()->getOnlinePlayers() as $player){
                        if($player->getAddress() == $ip){
                            $msg = "§4You have been Temporarily IP Muted!\n§cMute Time: $expiryToString\nReason: $reason\nMuted By: $sendername";
                            $player->kick($msg, false);
                        }
                    }
                        
                    $msg = Main::PREFIX . " §aYou have Temporarily IP Muted §6$ip belonging to §6$playername §afor §6$expiryToString §afor §6$reason";
                    $sender->sendMessage($msg);
        
                    if($this->main->cfg->get('ipmute-discord-post') == 'enabled'){
                        $webhook = $this->main->cfg->get('ipmute-webhook');
                    
                        $msg = "__**NEW TEMP IP MUTE**__\nIP Muted: $ip\nBelonging To: $playername\nMute Time: $expiryToString\nMuted By: $sendername\nReason: $reason";
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
}