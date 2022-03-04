<?php

namespace Rushil13579\EasyModeration\utils;

use InvalidArgumentException;
use pocketmine\permission\{BanEntry, BanList};

class MutedList extends BanList {

    public function add(BanEntry $entry): void {
        if($entry instanceof MuteEntry) {
            throw new InvalidArgumentException();
        }
        parent::add($entry);
    }
}