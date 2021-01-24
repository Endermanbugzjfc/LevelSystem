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

use pocketmine\utils\TextFormat as TF;
use pocketmine\event\{
	Listener,
	player\PlayerPreLoginEvent,
	player\PlayerChatEvent
};

use function str_ireplace;

class EventListener implements Listener {
	
	/**
	 * @param PlayerPreLoginEvent $ev 
	 * @return void
	 * 
	 * @priority MONITOR
	 */
	public function onPlayerPreLogin(PlayerPreLoginEvent $ev) : void {
		if ($ev->isCancelled()) return;
		LevelSystem::getInstance()->loadRuntimeKills($ev->getPlayer());
	}

	/**
	 * @param PlayerChatEvent $ev 
	 * @return void
	 * 
	 * @priority HIGHEST
	 */
	public function onPlayerChat(PlayerChatEvent $ev) : void {
		if ($ev->isCancelled()) return;
		$ev->setMessage(TF::colorize(str_ireplace('{level}', LevelSystem::getInstance()->getLevel($ev->getPlayer())), LevelSystem::getInstance()->getConfig()->get('level-prefix-format')));
	}
	
}
