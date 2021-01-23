-- #! sqlite
-- #{ levelsystem
-- #	{ init
CREATE TABLE IF NOT EXISTS player_levels(
	uuid VARCHAR(32) NOT NULL PRIMARY KEY,
	level INT SIGNED NOT NULL
);
-- #	}
-- #	{ add
-- #	  :uuid string
-- #	  :value int 1
INSERT OR REPLACE INTO player_levels(
	uuid,
	level
) VALUES (
	REPLACE (:uuid, '-', ''),
	(
		SELECT IFNULL(level, 0) AS level
		FROM player_levels
		WHERE uuid = REPLACE (:uuid, '-', '')
	) + :value
);
-- #	}
-- #	{ get
-- # 	  :uuid string
-- # 	  :nonnull bool true
SELECT ISNULL(level, IF(:nonnull, 0, NULL)) AS level
FROM player_levels
WHERE uuid = REPLACE (:uuid, '-', '');
-- #	}
-- #	{ reset
-- # 	  :uuid string
DELETE FROM player_levels
WHERE uuid = REPLACE (:uuid, '-', '');
-- #	}
-- #}