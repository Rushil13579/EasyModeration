<?php

namespace Rushil13579\EasyModeration;

use pocketmine\plugin\PluginBase;
use Rushil13579\EasyModeration\Commands\{IpBan, IpUnban, Kick, Mute, Mutelist, Permban, Tempban, Unban, Unmute, Warn};
use Rushil13579\EasyModeration\Discord\DiscordManager;
use Rushil13579\EasyModeration\Listener\EventListener;
use Rushil13579\EasyModeration\utils\{MutedList};

class Main extends PluginBase {

    const PREFIX = '§3[§bEasyModeration§3]';
    public $cfg;
    public $mutechat;
    public $chatinterval = [];

    public static function getNameMutes(): MutedList {
        $mutedList = new MutedList('muted-players.txt');
        $mutedList->load();
        return $mutedList;
    }

    public static function getIPMutes(): MutedList {
        $ipmutedList = new MutedList('muted-ips.txt');
        $ipmutedList->load();
        return $ipmutedList;
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig();

        $this->versionCheck();

        $this->unregisterCommands();
        $this->registerCommands();
    }

    public function versionCheck() {
        if($this->cfg->get('plugin-version') !== '1.0.0') {
            rename($this->getDataFolder() . 'config.yml', $this->getDataFolder() . 'old_config.yml');
            $this->getLogger()->warning('§cThe configuration file for EasyModeration was outdated so it has been renamed to old_config.yml and a new file has been generated!');
        }
    }

    public function unregisterCommands() {
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->unregister($cmdMap->getCommand('ban-ip'));
        $cmdMap->unregister($cmdMap->getCommand('unban-ip'));
        $cmdMap->unregister($cmdMap->getCommand('ban'));
        $cmdMap->unregister($cmdMap->getCommand('unban'));
        $cmdMap->unregister($cmdMap->getCommand('kick'));
    }

    public function registerCommands() {
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->register('EasyModeration', new IpBan($this));
        $cmdMap->register('EasyModeration', new IpUnban($this));
        $cmdMap->register('EasyModeration', new Permban($this));
        $cmdMap->register('EasyModeration', new Tempban($this));
        $cmdMap->register('EasyModeration', new Unban($this));
        $cmdMap->register('EasyModeration', new Kick($this));
        $cmdMap->register('EasyModeration', new Mute($this));
        $cmdMap->register('EasyModeration', new Unmute($this));
        $cmdMap->register('EasyModeration', new Mutelist($this));
        $cmdMap->register('EasyModeration', new Warn($this));
    }

    public function postToDiscord($webhook, $msg) {
        DiscordManager::postWebhook($webhook, $msg, $this->cfg->get('webhook-name'));
    }
}