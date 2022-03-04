<?php

namespace Rushil13579\EasyModeration\utils;

use pocketmine\permission\BanEntry;

class MuteEntry extends BanEntry {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Muted by administrator");
    }
}