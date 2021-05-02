-- #! sqlite
-- #{ levelsystem
-- #	{ init
CREATE TABLE IF NOT EXISTS player_kills
(
    uuid  VARCHAR(32) NOT NULL PRIMARY KEY,
    kills INT SIGNED  NOT NULL
);
-- #	}
-- #	{ add
-- #	  :uuid string
-- #	  :kills int 1
INSERT OR
REPLACE
INTO player_kills(uuid,
                  kills)
VALUES (LOWER(REPLACE(:uuid, '-', '')),
        IFNULL((
                   SELECT kills
                   FROM player_kills
                   WHERE uuid = LOWER(REPLACE(:uuid, '-', ''))
               )
            , 0) + :kills);
-- #	}
-- #	{ get
-- # 	  :uuid string
SELECT kills
FROM player_kills
WHERE uuid = LOWER(REPLACE(:uuid, '-', ''));
-- #	}
-- #	{ reset
-- # 	  :uuid string
DELETE
FROM player_kills
WHERE uuid = LOWER(REPLACE(:uuid, '-', ''));
-- #	}
-- #}