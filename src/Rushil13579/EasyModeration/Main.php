<?php

namespace Rushil13579\EasyModeration;

use pocketmine\{
    Server,
    Player
};

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use Rushil13579\EasyModeration\Commands\{
    IpBan, TempIpBan, IpUnban, Permban, Tempban,
    Unban, Kick, Kickall, IpMute, IpUnmute,
    Mute, Unmute, Mutelist, Mutechat, Warn,
    Report, Alts, Spy, Staffchat, Vanish,
    Freeze, Unfreeze
};

use Rushil13579\EasyModeration\Discord\DiscordManager;

use Rushil13579\EasyModeration\Listener\EventListener;

use Rushil13579\EasyModeration\utils\{
    Expiry,
    MutedList
};

class Main extends PluginBase {

    public $cfg;

    public $mutechat;
    public $chatinterval = [];
    public $reportcd = [];
    public $spy = [];
    public $staffchat = [];
    public $frozen = [];
    public $vanish = [];

    const PREFIX = '§3[§bEasyModeration§3]';

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig();

        $this->versionCheck();

        $this->unregisterCommands();
        $this->registerCommands();

		@mkdir($this->getDataFolder() . "alts/");
    }

    public function versionCheck(){
        if($this->cfg->get('plugin-version') !== '1.0.0'){
            $this->getLogger()->warning('§cThe configuration file for EasyModeration is outdated! Please delete it and restart the server to install the latest version!');
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    public function unregisterCommands(){
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->unregister($cmdMap->getCommand('ban-ip'));
        $cmdMap->unregister($cmdMap->getCommand('unban-ip'));
        $cmdMap->unregister($cmdMap->getCommand('ban'));
        $cmdMap->unregister($cmdMap->getCommand('unban'));
        $cmdMap->unregister($cmdMap->getCommand('kick'));
    }

    public function registerCommands(){
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->register('EasyModeration', new IpBan($this));
        $cmdMap->register('EasyModeration', new TempIpBan($this));
        $cmdMap->register('EasyModeration', new IpUnban($this));
        $cmdMap->register('EasyModeration', new Permban($this));
        $cmdMap->register('EasyModeration', new Tempban($this));
        $cmdMap->register('EasyModeration', new Unban($this));
        $cmdMap->register('EasyModeration', new Kick($this));
        $cmdMap->register('EasyModeration', new Kickall($this));
        $cmdMap->register('EasyModeration', new IpMute($this));
        $cmdMap->register('EasyModeration', new IpUnmute($this));
        $cmdMap->register('EasyModeration', new Mute($this));
        $cmdMap->register('EasyModeration', new Unmute($this));
        $cmdMap->register('EasyModeration', new Mutelist($this));
        $cmdMap->register('EasyModeration', new Mutechat($this));
        $cmdMap->register('EasyModeration', new Warn($this));
        $cmdMap->register('EasyModeration', new Report($this));
        $cmdMap->register('EasyModeration', new Alts($this));
        $cmdMap->register('EasyModeration', new Spy($this));
        $cmdMap->register('EasyModeration', new Staffchat($this));
        $cmdMap->register('EasyModeration', new Vanish($this));
        $cmdMap->register('EasyModeration', new Freeze($this));
        $cmdMap->register('EasyModeration', new Unfreeze($this));
    }

    public static function getNameMutes() : MutedList {
        $mutedList = new MutedList('muted-players.txt');
        $mutedList->load();
        return $mutedList;
    }

    public static function getIPMutes() : MutedList {
        $ipmutedList = new MutedList('muted-ips.txt');
        $ipmutedList->load();
        return $ipmutedList;
    }

    public function postToDiscord($webhook, $msg){
        DiscordManager::postWebhook($webhook, $msg, $this->cfg->get('webhook-name'));
    }
}