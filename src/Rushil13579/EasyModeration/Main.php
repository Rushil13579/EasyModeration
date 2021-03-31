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
            $this->getServer()->warning('§cThe configuration file for EasyModeration is outdated! Please delete it and restart the server to install the latest version!');
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
        $cmdMap->register('ipban', new IpBan($this));
        $cmdMap->register('tempipban', new TempIpBan($this));
        $cmdMap->register('ipunban', new IpUnban($this));
        $cmdMap->register('permban', new Permban($this));
        $cmdMap->register('tempban', new Tempban($this));
        $cmdMap->register('unban', new Unban($this));
        $cmdMap->register('kick', new Kick($this));
        $cmdMap->register('kickall', new Kickall($this));
        //$cmdMap->register('ipmute', new IpMute($this));
        //$cmdMap->register('ipunmute', new IpUnmute($this));
        $cmdMap->register('mute', new Mute($this));
        $cmdMap->register('unmute', new Unmute($this));
        $cmdMap->register('mutelist', new Mutelist($this));
        $cmdMap->register('mutechat', new Mutechat($this));
        $cmdMap->register('warn', new Warn($this));
        $cmdMap->register('report', new Report($this));
        $cmdMap->register('alts', new Alts($this));
        $cmdMap->register('spy', new Spy($this));
        $cmdMap->register('staffchat', new Staffchat($this));
        $cmdMap->register('vanish', new Vanish($this));
        $cmdMap->register('freeze', new Freeze($this));
        $cmdMap->register('unfreeze', new Unfreeze($this));
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