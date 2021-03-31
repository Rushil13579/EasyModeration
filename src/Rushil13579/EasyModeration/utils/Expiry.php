<?php

namespace Rushil13579\EasyModeration\utils;

use Rushil13579\EasyModeration\Main;

use DateTime;
use InvalidArgumentException;

class Expiry {

	private $date;

	private $negval = Main::PREFIX . ' §cValues under one are not allowed in the time format';
	private $invalidformat = Main::PREFIX . ' §cInvalid time format';

	public function __construct(string $format){
		if(is_numeric($format)){
			if(intval($format) <= 0){
				throw new InvalidArgumentException($this->negval);
			}
			$dateTime = new DateTime();
			$dateTime->setTimestamp($dateTime->getTimestamp() + intval($format));
			$this->date = $dateTime;
			return true;
		}
		$this->date = new DateTime();
		$second = 0;
		$minute = 0;
		$hour = 0;
		$day = 0;
		$month = 0;
		$year = 0;
		$currentChars = '';
		$formatChars = str_split($format);
		for($i = 0; $i < count($formatChars); $i++){
			if(is_numeric($formatChars[$i])){
				$currentChars .= $formatChars[$i];
				continue;
			}
			switch(strtolower($formatChars[$i])){
				case 's':
					if($currentChars == ''){
						throw new InvalidArgumentException($this->invalidformat);
					}
					if(intval($currentChars) <= 0){
						throw new InvalidArgumentException($this->negval);
					}
					$second = intval($currentChars);
					$currentChars = '';
					break;
				case 'm':
					if($currentChars == ''){
						throw new InvalidArgumentException($this->invalidformat);
					}
					if(intval($currentChars) <= 0){
						throw new InvalidArgumentException($this->negval);
					}
					if(isset($formatChars[$i + 1])){
						if(!is_numeric($formatChars[$i + 1])){
							switch(strtolower($formatChars[$i + 1])){
								case 'o':
									if(intval($currentChars) <= 0){
										throw new InvalidArgumentException($this->negval);
									}
									$month = intval($currentChars);
									$currentChars = '';
									break;
								default:
									throw new InvalidArgumentException($this->invalidformat);
							}
							$i += 1;
							break;
						}
					}
					$minute = intval($currentChars);
					$currentChars = '';
					break;
				case 'h':
					if($currentChars == ''){
						throw new InvalidArgumentException($this->invalidformat);
					}
					if(intval($currentChars) <= 0){
						throw new InvalidArgumentException($this->negval);
					}
					$hour = intval($currentChars);
					$currentChars = '';
					break;
				case 'd':
					if($currentChars == ''){
						throw new InvalidArgumentException($this->invalidformat);
					}
					if(intval($currentChars) <= 0){
						throw new InvalidArgumentException($this->negval);
					}
					$day = intval($currentChars);
					$currentChars = '';
					break;
				case 'y':
					if($currentChars == ''){
						throw new InvalidArgumentException($this->invalidformat);
					}
					if(intval($currentChars) <= 0){
						throw new InvalidArgumentException($this->negval);
					}
					$year = intval($currentChars);
					$currentChars = '';
					break;
				default:
					throw new InvalidArgumentException($this->invalidformat);
			}
		}
		while($second >= 60){
			$minute++;
			$second -= 60;
		}
		while($minute >= 60){
			$hour++;
			$minute -= 60;
		}
		while($hour >= 24){
			$day++;
			$hour -= 24;
		}
		while($day >= 30){
			$month++;
			$day -= 30;
		}
		while($month >= 12){
			$year++;
			$month -= 12;
		}
		$newSecond = intval($this->date->format('s')) + $second;
		$newMinute = intval($this->date->format('i')) + $minute;
		$newHour = intval($this->date->format('H')) + $hour;
		$newDay = intval($this->date->format('d')) + $day;
		$newMonth = intval($this->date->format('m')) + $month;
		$newYear = intval($this->date->format('Y')) + $year;
		$newDate = new DateTime();
		$newDate = $newDate->setDate($newYear, $newMonth, $newDay);
		$newDate = $newDate->setTime($newHour, $newMinute, $newSecond);
		$this->date = $newDate;
	}

	public function getDate() : DateTime {
		return $this->date;
	}

	public static function expirationTimerToString(DateTime $from, DateTime $to) : string {
		$string = '';
		$second = intval($from->format('s')) - intval($to->format('s'));
		$minute = intval($from->format('i')) - intval($to->format('i'));
		$hour = intval($from->format('H')) - intval($to->format('H'));
		$day = intval($from->format('d')) - intval($to->format('d'));
		$month = intval($from->format('n')) - intval($to->format('n'));
		$year = intval($from->format('Y')) - intval($to->format('Y'));
		if($second < 0){
			$second = 60 + $second;
			$minute--;
		}
		if($minute < 0){
			$minute = 60 + $minute;
			$hour--;
		}
		if($hour < 0){
			$hour = 24 + $hour;
			$day--;
		}
		if($day < 0){
			$day = 30 + $day;
			$month--;
		}
		if($month < 0){
			$month = 12 + $month;
			$year--;
		}
		$string .= $year > 0 ? strval($year) . ' ' . ($year > 1 ? 'years ' : 'year ') : '';
		$string .= $month > 0 ? strval($month) . ' ' . ($month > 1 ? 'months ' : 'month ') : '';
		$string .= $day > 0 ? strval($day) . ' ' . ($day > 1 ? 'days ' : 'day ') : '';
		$string .= $hour > 0 ? strval($hour) . ' ' . ($hour > 1 ? 'hours ' : 'hour ') : '';
		$string .= $minute > 0 ? strval($minute) . ' ' . ($minute > 1 ? 'minutes ' : 'minute ') : '';
		$string .= $second > 0 ? strval($second) . ' ' . ($second > 1 ? 'seconds ' : 'second ') : '';
		$string = substr($string, 0, strlen($string) - 1);
		return $string;
	}
}