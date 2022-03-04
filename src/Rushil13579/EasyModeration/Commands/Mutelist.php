<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\BanEntry;
use pocketmine\plugin\Plugin;
use Rushil13579\EasyModeration\Main;

class Mutelist extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('mutelist', 'View all players banned from this server', '/mutelist');
        $this->setPermission('easymoderation.mutelist');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(isset($args[0])) {
            if($args[0] == 'players') {
                $list = $this->main->getNameMutes();
            } else {
                if($args[0] == 'ips') {
                    $list = $this->main->getIPMutes();
                } else {
                    $sender->sendMessage(Main::PREFIX . ' §cUsage: /mutelist [ips|players]');
                    return false;
                }
            }
        } else {
            $list = $this->main->getNameMutes();
            $args[0] = 'players';
        }

        $list = array_map(function(BanEntry $entry): string {
            return $entry->getName();
        }, $list->getEntries());
        sort($list, SORT_STRING);
        $message = implode(", ", $list);

        $countmsg = '';
        if($args[0] == 'players') {
            $count = count($list);
            $countmsg = "There are $count total muted players:";
        } elseif($args[0] == 'ips') {
            $count = count($list);
            $countmsg = "There are $count total muted IP addresses";
        }

        $sender->sendMessage($countmsg);
        $sender->sendMessage($message);
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}