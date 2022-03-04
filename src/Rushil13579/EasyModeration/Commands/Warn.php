<?php

namespace Rushil13579\EasyModeration\Commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\sound\TotemUseSound;
use Rushil13579\EasyModeration\Main;

class Warn extends Command {

    /** @var Main */
    private $main;

    public function __construct(Main $main) {
        $this->main = $main;

        parent::__construct('warn', 'Send a warning to a player', '/warn <player> [reason...]');
        $this->setPermission('easymoderation.warn');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            $sender->sendMessage(Main::PREFIX . ' §cYou do not have permission to use this command');
            return false;
        }

        if(count($args) < 2) {
            $sender->sendMessage(Main::PREFIX . ' §cUsage: /warn <player> [reason...]');
            return false;
        }

        if($this->main->getServer()->getPlayerExact($args[0]) === null) {
            $sender->sendMessage(Main::PREFIX . ' §cPlayer not found');
            return false;
        }
        $player = $this->main->getServer()->getPlayerExact($args[0]);

        $reason = implode(' ', array_slice($args, 1));

        $sendername = $sender->getName();
        $playername = $player->getName();

        $player->sendTitle('§4[§cWARNING§4]');
        $msg = "§4[§cWARNING§4] §4You have been Warned for §c$reason §cby §c$sendername";
        $player->sendMessage($msg);
        $player->getWorld()->addSound($player->getPosition(), new TotemUseSound(), $this->main->getServer()->getOnlinePlayers());

        $msg = Main::PREFIX . " §aYou have Warned §6$playername §afor §6$reason";
        $sender->sendMessage($msg);

        if($this->main->cfg->get('warn-discord-post') == 'enabled') {
            $webhook = $this->main->cfg->get('warn-webhook');

            $msg = "__**NEW WARN**__\nPlayer Warned: $playername\nWarned By: $sendername\nReason: $reason";
            $this->main->postToDiscord($webhook, $msg);
        }
        return true;
    }

    public function getPlugin(): Plugin {
        return $this->main;
    }
}