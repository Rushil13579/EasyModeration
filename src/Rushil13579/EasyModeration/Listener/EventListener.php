<?php

namespace Rushil13579\EasyModeration\Listener;

use DateTime;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use Rushil13579\EasyModeration\Main;
use Rushil13579\EasyModeration\utils\Expiry;

class EventListener implements Listener {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;
    }

    public function isMutedChat(PlayerChatEvent $ev) {
        if($ev->isCancelled()) {
            return null;
        }

        $player = $ev->getPlayer();
        $muteList = $this->main->getNameMutes();
        $ipmuteList = $this->main->getIPMutes();

        if($muteList->isBanned($player->getName())) {

            $entries = $muteList->getEntries();
            $entry = $entries[strtolower($player->getName())];
            $reason = $entry->getReason();
            $muteMsg = '';

            if($entry->getExpires() === null) {
                $muteMsg = "§4You are muted forever for §c$reason";
            } else {
                if($entry->hasExpired()) {
                    $muteList->remove($player->getName());
                    $player->sendMessage('§aYour mute has expired!');
                    return null;
                }

                $expiryToString = Expiry::expirationTimerToString($entry->getExpires(), new DateTime());
                $muteMsg = "§4You are muted until §c$expiryToString §4for §c$reason";
            }
            $ev->cancel();
            $player->sendMessage($muteMsg);
        }
    }

    public function chatSensor(PlayerChatEvent $ev) {
        if($ev->isCancelled()) {
            return null;
        }

        $player = $ev->getPlayer();
        $msg = $ev->getMessage();

        if(!$player->hasPermission('easymoderation.chatcensor.bypass')) {
            foreach($this->main->cfg->get('chat-censor') as $censorword) {
                if(str_contains(strtolower($msg), strtolower($censorword))) {
                    $player->sendMessage('§cYou cannot use that word!');
                    $ev->cancel();
                }
            }
        }
    }

    public function chatInterval(PlayerChatEvent $ev) {
        if($ev->isCancelled()) {
            return null;
        }

        $player = $ev->getPlayer();

        if(!$player->hasPermission('easymoderation.chatinterval.bypass')) {
            $interval = $this->main->cfg->get('chat-interval');
            if(!isset($this->main->chatinterval[$player->getName()])) {
                $this->main->chatinterval[$player->getName()] = time() + $interval;
            } else {
                if(time() < $this->main->chatinterval[$player->getName()]) {
                    $rem = $this->main->chatinterval[$player->getName()] - time();
                    $player->sendMessage("§cPlease dont spam! You can talk again in §6$rem §cseconds");
                    $ev->cancel();
                    return false;
                } else {
                    unset($this->main->chatinterval[$player->getName()]);
                    $this->main->chatinterval[$player->getName()] = time() + $interval;
                }
            }
        }
    }
}