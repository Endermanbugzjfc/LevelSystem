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
	plugin\PluginBase,
	utils\UUID,
	scheduler\ClosureTask
};

use poggit\libasynql\{libasynql, DataConnector, SqlError};
use _64FF00\PureChat;

use function in_array;

class LevelSystem extends PluginBase {

	private static $instance = null;

	/**
	 * @var DataConnector|null
	 */
	private $db = null;

	/**
	 * @var array<string, int>
	 */
	private $runtimekills = [];
	
	public function onLoad() : void {
		self::$instance = $this;
		$this->initConfig();
	}

	public function onEnable() : void {
		if ($this->getPureChat() === null or !(bool)$this->getConfig()->get('enable-plugin')) {
			$this->getLogger()->warning('PureChat plugin dependency is not installed or loaded!');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}

		$this->db = libasynql::create($this, [
			'type' => 'sqlite',
			'worker-limit' => 1,
			'sqlite' => ['file' => $this->getDataFolder() . 'data.db']
		], ['sqlite' => 'sqlite.sql']);
		$this->db->executeGeneric('levelsystem.init', [], function() : void {
			$this->getLogger()->info('Database initialized sccessfully');
		}, function(SqlError $err) : void {
			$this->getLogger()->critical('Failed to initialize database');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			throw $err;
		});
		$this->db->waitAll();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener, $this);

		$this->getScheduler()->scheduleRepeatingEvent(new ClosureTask(function(int $ct) : void {
			foreach ($this->getServer()->getOnlinePlayers() as $p) $rkuid[] = $p->getUUID()->toString();
			foreach ($this->runtimekills as $puid => $kills) if (!in_array($puid, $this->runtimekills)) unset($this->runtimekills[$puid]);
		}), 20 * 60 * (int)$conf->get('cleanup-interval-minutes', 120));
	}

	protected function initConfig() : void {
		$this->saveDefaultConfig();
		$conf = $this->getConfig();
		foreach ($all = $conf->getAll() as $k => $v) $conf->remove($k);

		$conf->set('enable-plugin', (bool)($all['enable-plugin'] ?? true));
		$conf->set('level-prefix-format', (string)($all['level-prefix-format'] ?? '&e[&l{level}&r&e] '));
		$conf->set('cleanup-interval-minutes', (int)($all['cleanup-interval-minutes'] ?? 120));
		$conf->set('kills-per-level', (int)($all['kills-per-level'] ?? 45));

		$conf->save();
		$conf->reload();
	}

	/**
	 * @param Player|UUID|string $player 
	 * @param Closure|null $callback Compatible with <code>function(?<@link SqlError>$err)</code>
	 * @return void
	 */
	public function addKill($player, int $level = 1, ?\Closure $callback = null) : void {
		$uuid = self::getUUIDString($player);
		if (isset($this->runtimekills[$uuid])) $this->runtimekills[$uuid] + $level;
		$this->db->executeChange('levelsystem.add', [
			'uuid' => $uuid,
			'kills' => $level
		], function(int $affected) use ($callback) : void {
			if (isset($callback)) $callback(null);
		}, function(SqlError $err) use ($callback) : void {
			if (isset($callback)) $callback($err);
			else throw $err;
		});
	}

	/**
	 * @param Player|UUID|string $player 
	 * @param Closure|null $callback Compatible with <code>function(?<@link SqlError>$err)</code>
	 * @return void
	 */
	public function removeKill($player, int $level = 1, ?\Closure $callback = null) : void {
		self::addKill($player, -$level, $callback);
	}

	/**
	 * @param Player|UUID|string $player 
	 * @param Closure|null $success Compatible with <code>function(?int $level)</code>
	 * @param Closure|null $fail Compatible with <code>function(<@link SqlError> $err)</code>
	 * @return void
	 */
	public function getKills($player, \Closure $success, bool $nonnull = true, ?\Closure $fail = null) : void {
		$this->db->executeSelect('levelsystem.get', [
			'uuid' => self::getUUIDString($player)
		], function(array $result) use ($success, $nonnull) : void {
			$success($result[0]['kills'] ?? ($nonnull ? 0 : null));
		}, function(SqlError $err) use ($fail) : void {
			if (isset($fail)) $fail($err);
			else throw $err;
		});
	}

	/**
	 * @param Player|UUID|string $player 
	 * @param Closure|null $callback Compatible with <code>function(?<@link SqlError>$err)</code>
	 * @return void
	 */
	public function resetKills($player, ?\Closure $callback = null) : void {
		if (isset($this->runtimekills[$uuid])) $this->runtimekills[$uuid] = 0;
		$this->executeChange('levelsystem.reset', [
			'uuid' => self::getUUIDString($player)
		], function(int $affected) use ($callback) : void {
			if (isset($callback)) $callback(null);
		}, function(SqlError $err) use ($callback) : void {
			if (isset($callback)) $callback($err);
			else throw $err;
		});
	}

	/**
	 * @internal
	 */
	public function loadRuntimeKills(Player $player) : void {
		$uuid = $player->getUUID()->toString();
		$this->getKills($player, function(?int $kills) use ($uuid) : void {
			$this->runtimekills[$uuid] = $kills;
		}, true);
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
	
	/**
	 * @param Player|UUID|string $uuid 
	 * @return string
	 */
	protected static function getUUIDString($uuid) : string {
		if ($uuid instanceof Player) $uuid = $player->getUUID();
		if ($uuid instanceof UUID) $uuid = $uuid->toString();
		return $uuid;
	}

	public function getPureChat() : ?PureChat {
		foreach ($this->getServer()->getPluginManager()->getPlugins() as $pl) if ($pl instanceof PureChat) return $pl;
		return null;
	}

}
