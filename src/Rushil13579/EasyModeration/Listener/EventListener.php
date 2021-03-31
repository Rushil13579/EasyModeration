<?php

namespace Rushil13579\EasyModeration\Listener;

use pocketmine\{
    Server,
    Player
};

use pocketmine\command\ConsoleCommandSender;

use pocketmine\event\Listener;
use pocketmine\event\player\{
    PlayerJoinEvent,
    PlayerQuitEvent,
    PlayerChatEvent,
    PlayerMoveEvent,
    PlayerCommandPreprocessEvent
};
use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent
};
use pocketmine\event\server\CommandEvent;

use DateTime;

use Rushil13579\EasyModeration\Main;
use Rushil13579\EasyModeration\utils\Expiry;

class EventListener implements Listener {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;
    }

    public function altJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();

        if(!file_exists($this->main->getDataFolder() . 'alts/' . $player->getAddress())){
            file_put_contents($this->main->getDataFolder() . 'alts/' . $player->getAddress(), $player->getName() . ",\n");
            $this->main->getLogger()->info('§aNo records found for ' . $player->getName() . '!');
            return null;
        }

        $file = explode(",\n", file_get_contents($this->main->getDataFolder() . "alts/" . $player->getAddress(), true));

		if(!in_array($player->getName(), $file)){
			file_put_contents($this->main->getDataFolder() . "alts/" . $player->getAddress(), $player->getName() . ",\n", FILE_APPEND);
			$this->main->getLogger()->info('§4' . $player->getName() . ' might be an alt account!');
            return null;
		} else {
            if(count($file) > 2){
                $this->main->getLogger()->info('§4' . $player->getName() . ' might be an alt account!');
                return null;
            } else {
                $this->main->getLogger()->info('§aNo records found for ' . $player->getName() . '!');
                return null;
            }
        }
    }

    public function quitFrozen(PlayerQuitEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->frozen[$player->getName()])){
            if($this->main->cfg->get('frozen-quit-commands') !== null and is_array($this->main->cfg->get('frozen-quit-commands'))){
                foreach($this->main->cfg->get('frozen-quit-commands') as $cmd){
                    $cmd = str_replace('{player}', $player->getName(), $cmd);
                    $this->main->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                }
            }
        }
        unset($this->main->frozen[$player->getName()]);
    }

    public function staffChat(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();
        $msg = $ev->getMessage();

        if($player->hasPermission('easymoderation.staffchat')){
            $array = explode(' ', trim($msg));
            $hash = $array[0];
            if($hash == '#'){
                $nmsg = str_replace('# ', '', $msg);
                foreach($this->main->getServer()->getOnlinePlayers() as $pl){
                    if($pl->hasPermission('easymoderation.staffchat')){
                        $pl->sendMessage('§4[§cSC§4] §b> §6' . $player->getName() . ': §d' . $nmsg);
                    }
                }
                $this->main->getLogger()->info('§4[§cSC§4] §b> §6' . $player->getName() . ': §d' . $nmsg);
                $ev->setCancelled();

            }
        }

        if(isset($this->main->staffchat[$player->getName()])){
            foreach($this->main->getServer()->getOnlinePlayers() as $pl){
                if($pl->hasPermission('easymoderation.staffchat')){
                    $pl->sendMessage('§4[§cSC§4] §b> §6' . $player->getName() . ': §d' . $msg);
                }
            }
            $this->main->getLogger()->info('§4[§cSC§4] §b> §6' . $player->getName() . ': §d' . $msg);
            $ev->setCancelled();
        }
    }

    public function isMutedChat(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();
        $muteList = $this->main->getNameMutes();
        $ipmuteList = $this->main->getIPMutes();

        if($muteList->isBanned($player->getName())){

            $entries = $muteList->getEntries();
            $entry = $entries[strtolower($player->getName())];
            $reason = $entry->getReason();
            $muteMsg = '';

            if($entry->getExpires() === null){
                $muteMsg = "§4You are muted forever for §c$reason";
            } else {
                if($entry->hasExpired()){
                    $muteList->remove($player->getName());
                    return null;
                }

                $expiryToString = Expiry::expirationTimerToString($entry->getExpires(), new DateTime());
                $muteMsg = "§4You are muted until §c$expiryToString §4for §c$reason";
            }
            $ev->setCancelled();
            $player->sendMessage($muteMsg);
        }

        if($ipmuteList->isBanned($player->getAddress())){

            $entries = $ipmuteList->getEntries();
            $entry = $entries[strtolower($player->getAddress())];
            $reason = $entry->getReason();
            $muteMsg = '';

            if($entry->getExpires() === null){
                $muteMsg = "§4You are ip muted forever for §c$reason";
            } else {
                if($entry->hasExpired()){
                    $ipmuteList->remove($player->getAddress());
                    return null;
                }

                $expiryToString = Expiry::expirationTimerToString($entry->getExpires(), new DateTime());
                $muteMsg = "§4You are ip muted until §c$expiryToString §4for §c$reason";
            }
            $ev->setCancelled();
            $player->sendMessage($muteMsg);
        }
    }

    public function muteChat(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();

        if(isset($this->main->mutechat) and $this->main->mutechat == 'on'){
            if(!$player->hasPermission('easymoderation.mutechat.bypass')){
                $player->sendMessage('§cGlobal chat is muted! You cannot talk!');
                $ev->setCancelled();
                return false;
            }
        }
    }

    public function frozenChat(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();

        if(isset($this->main->frozen[$player->getName()])){
            if($this->main->cfg->get('disable-chat-while-frozen') == 'true'){
                $player->sendMessage('§cYou cannot do this while being frozen');
                $ev->setCancelled();
            }
        }
    }

    public function chatSensor(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();
        $msg = $ev->getMessage();

        if(!$player->hasPermission('easymoderation.chatcensor.bypass')){
            foreach($this->main->cfg->get('chat-censor') as $censorword){
                if(strpos(strtolower($msg), strtolower($censorword)) !== false){
                    $player->sendMessage('§cYou cannot use that word!');
                    $ev->setCancelled();
                    if($this->main->cfg->get('chat-censor-discord-post') == 'enabled'){
                        $webhook = $this->main->cfg->get('chat-censor-webhook');
                        $msg = "__**NEW MESSAGE CENSORED**__\nMessage: $msg\nMessage Sender: " . $player->getName();
                        $this->main->postToDiscord($webhook, $msg);
                    }
                }
            }
        }
    }

    public function chatInterval(PlayerChatEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();

        if(!$player->hasPermission('easymoderation.chatinterval.bypass')){
            $interval = $this->main->cfg->get('chat-interval');
            if(!isset($this->main->chatinterval[$player->getName()])){
                $this->main->chatinterval[$player->getName()] = time() + $interval;
            } else {
                if(time() < $this->main->chatinterval[$player->getName()]){
                    $rem = $this->main->chatinterval[$player->getName()] - time();
                    $player->sendMessage("§cPlease dont spam! You can talk again in §6$rem §cseconds");
                    $ev->setCancelled();
                    return false;
                } else {
                    unset($this->main->chatinterval[$player->getName()]);
                    $this->main->chatinterval[$player->getName()] = time() + $interval;
                }
            }
        }
    }

    public function frozenMove(PlayerMoveEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->frozen[$player->getName()])){
            $player->setImmobile();
        }
    }

    public function frozenCommand(PlayerCommandPreprocessEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $player = $ev->getPlayer();
        $cmd = $ev->getMessage();

        $ncmd = str_replace('/', '', strtok($cmd, ' '));

        if(isset($this->main->frozen[$player->getName()])){
            if($this->main->cfg->get('frozen-disabled-commands') !== null and is_array($this->main->cfg->get('frozen-disabled-commands'))){

                if($this->main->cfg->get('frozen-disabled-commands')[0] == '*'){
                    $player->sendMessage('§cYou cannot do this while being frozen');
                    $ev->setCancelled();
                    return null;
                }
            
                if(!in_array($ncmd, $this->main->cfg->get('frozen-disabled-commands'))){
                    $player->sendMessage('§cYou cannot do this while being frozen');
                    $ev->setCancelled();
                }
            }
        }
    }

    public function vanishDamage(EntityDamageEvent $ev){
        $player = $ev->getEntity();

        if(!$player instanceof Player){
            return null;
        }

        if(isset($this->main->vanish[$player->getName()])){
            $ev->setCancelled();
        }
    }

    public function frozenDamage(EntityDamageEvent $ev){
        $player = $ev->getEntity();

        if(!$player instanceof Player){
            return null;
        }

        if(isset($this->main->frozen[$player->getName()])){
            if($this->main->cfg->get('frozen-damage-disabled') == 'true'){
                $ev->setCancelled();
            }
        }

        if($ev instanceof EntityDamageByEntityEvent){
            $damager = $ev->getDamager();

            if(!$damager instanceof Player){
                return null;
            }

            if(isset($this->main->frozen[$damager->getName()])){
                if($this->main->cfg->get('frozen-damage-disabled') == 'true'){
                    $damager->sendMessage('§cYou cannot do this while being frozen');
                    $ev->setCancelled();
                }
            }
        }
    }

    public function spyCommand(CommandEvent $ev){
        if($ev->isCancelled()){
            return null;
        }

        $sender = $ev->getSender();
        $cmd = $ev->getCommand();

        if($sender instanceof Player){
            if(!$sender->hasPermission('easymoderation.spy.exempt')){
                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    if(isset($this->main->spy[$player->getName()])){
                        $player->sendMessage('§4Spy 》 §6' . $sender->getName() . ': §d/' . $cmd);
                    }
                }
            }
            $this->main->getLogger()->info('§4Spy 》 §6' . $sender->getName() . ': §d/' . $cmd);
        }
    }
}