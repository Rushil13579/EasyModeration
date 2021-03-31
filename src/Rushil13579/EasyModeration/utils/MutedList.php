<?php

namespace Rushil13579\EasyModeration\utils;

use pocketmine\permission\{
	BanEntry,
	BanList
};

use DateTime;
use InvalidArgumentException;

class MutedList extends BanList {

	public function add(BanEntry $entry){
		if($entry instanceof MuteEntry){
			throw new InvalidArgumentException();
		}
		parent::add($entry);
	}
}