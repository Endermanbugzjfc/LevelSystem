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
namespace Endermanbugzjfc\LevelSystem\LevelSystem;

use pocketmine\{
	plugin\PluginBase
};
use pocketmine\event\{
	Listener
};

use poggit\libasynql\{libasynql, DataConnector, SqlError};

class LevelSystem extends PluginBase implements Listener {

	private static $instance = null;

	/**
	 * @var DataConnector|null
	 */
	private $db = null;
	
	public function onLoad() : void {
		self::$instance = $this;
	}

	public function onEnable() : void {
		$this->db = libasynql::create($this, [
			'type' => 'sqlite',
			'worker-limit' => 1,
			'sqlite' => ['file' => $this->getDataFolder() . 'data.db']
		], ['sqlite' => 'sqlite.sql']);
		$this->db->excuteGeneric('levelsystem.init', [], function() : void {
			$this->getLogger()->log('Database initialized sccessfully');
		}, function(SqlError $err) : void {
			$this->getLogger()->critical('Failed to initialize database');
			$this->getServer()->getPluginManager()->disablePlugin($this);
		});
		$this->db->waitAll();
	}

	public function onDisable() : void {
		if (isset($this->db)) $this->db->close();
	}

	public function getDataConnectorInstance() : ?DataConnector {
		return $this->db;
	}

	public static function getInstance() : ?self {
		return self::$instance;
	}
	
}
