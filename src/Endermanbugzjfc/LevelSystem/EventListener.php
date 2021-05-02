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

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
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
                    (string)(LevelSystem::getInstance()->getRuntimeKills($ev->getPlayer()) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level')),
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
        $sp = $ev->getEntity();
        $p = $ev->getDamager();
        if (!(($sp instanceof Player) and ($p instanceof Player))) return;
        if ($ev->getFinalDamage() < $sp->getHealth()) return;
        LevelSystem::getInstance()->addKill($ev->getDamager());
        $kt = (string)LevelSystem::getInstance()->getConfig()->get('kill-tips');
        $ml = (string)LevelSystem::getInstance()->getConfig()->get('levelup-msg');
        $nlv = LevelSystem::getInstance()->getRuntimeKills($p) / (int)LevelSystem::getInstance()->getConfig()->get('kills-per-level');
        if ((int)$nlv == $nlv) if (!empty($ml)) $p->sendMessage(Utils::treatTagsAndColors($ml, $sp));
        else if (!empty($kt)) $p->sendPopup(Utils::treatTagsAndColors($kt, $sp));
    }

}
