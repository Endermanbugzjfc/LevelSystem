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

use pocketmine\{Player, utils\TextFormat as TF};
use pocketmine\event\{
	Listener,
	player\PlayerPreLoginEvent,
	player\PlayerChatEvent,
	entity\EntityDamageByEntityEvent
};

use function str_ireplace;

class EventListener implements Listener {
	
	/**
	 * @param PlayerPreLoginEvent $ev 
	 * @return void
	 * 
	 * @ignoreCancelled
	 * @priority MONITOR
	 */
	public function onPlayerPreLogin(PlayerPreLoginEvent $ev) : void {
		LevelSystem::getInstance()->loadRuntimeKills($ev->getPlayer());
	}

	/**
	 * @param PlayerChatEvent $ev 
	 * @return void
	 * 
	 * @ignoreCancelled
	 * @priority MONITOR
	 */
	public function onPlayerChat(PlayerChatEvent $ev) : void {
		$ev->setFormat(TF::colorize(
			str_ireplace('{level}',
				(int)(LevelSystem::getInstance()->getRuntimeKills($ev->getPlayer()) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level')),
				LevelSystem::getInstance()->getConfig()->get('level-prefix-format'))
		) . $ev->getFormat());
	}

	/**
	 * @param EntityDamageByEntityEvent $ev 
	 * @return void
	 * 
	 * @ignoreCancelled
	 * @priority MONITOR
	 */
	public function onEntityDamageByEntity(EntityDamageByEntityEvent $ev) : void {
		if (!(($ev->getEntity() instanceof Player) and ($ev->getDamager() instanceof Player))) return;
		if ($ev->getFinalDamage() < $ev->getEntity()->getHealth()) return;
		LevelSystem::getInstance()->addKill($ev->getDamager());
		$kt = (string)LevelSystem::getInstance()->getConfig()->get('kill-tips');
		$lm = (string)LevelSystem::getInstance()->getConfig()->get('levelup-msg');
		$nlv = LevelSystem::getInstance()->getRuntimeKills($ev->getPlayer()) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level');
		if ((int)$nlv == $nvl) if (!empty($ml)) $p->sendMessage(Utils::treatTagsAndColors($ml));
		else if (!empty($kt))  $p->sendPopup(Utils::treatTagsAndColors($kt));
	}
	
}
