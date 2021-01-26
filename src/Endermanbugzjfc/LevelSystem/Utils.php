<?php

/*

     					_________	  ______________		
     				   /        /_____|_           /
					  /————/   /        |  _______/_____    
						  /   /_     ___| |_____       /
						 /   /__|    ||    ____/______/
						/   /    \   ||   |   |   
					   /__________\  | \   \  |
					       /        /   \   \ |
						  /________/     \___\|______
						                   |         \ 
							  PRODUCTION   \__________\	

							   翡翠出品 。 正宗廢品  
 
*/

declare(strict_types=1);
namespace Endermanbugzjfc\LevelSystem;

use pocketmine\{
	Player,
	item\Item,
	utils\TextFormat as TF
};

use function str_ireplace;

class Utils {
	
	public static function treatTagsAndColors(string $string, Player $target) : string {
		$string = str_ireplace('{target}', $target->getName(), $string);
		$string = str_ireplace('{item-in-hand}', $iih->getId() === Item::AIR ? 'Fist' : $iih->getName(), $string);
		$string = str_ireplace('{next-level}', (int)(LevelSystem::getInstance()->getRuntimeKills($ev->getPlayer()) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level')) + 1, $string);
		$string = str_ireplace('{current-level}', (int)(LevelSystem::getInstance()->getRuntimeKills($ev->getPlayer()) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level')), $string);
		$string = TF::colorize($string);

		return $string;
	}
	
}
