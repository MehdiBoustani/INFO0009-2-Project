SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'person'
CREATE TABLE IF NOT EXISTS `person` (
  `ID` int PRIMARY KEY,
  `FIRSTNAME` varchar(15) NOT NULL,
  `LASTNAME` char(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'person'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/PERSON.CSV' INTO TABLE `person` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'candidate'
CREATE TABLE IF NOT EXISTS `candidate` (
  `ID` int PRIMARY KEY
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'candidate'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/CANDIDATE.CSV' INTO TABLE `candidate` FIELDS TERMINATED BY ';' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'users'
CREATE TABLE IF NOT EXISTS `users` (
  `Login` varchar(20) PRIMARY KEY NOT NULL,
  `Pass` varchar(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/users.csv' INTO TABLE `users` FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'taskmaster'
CREATE TABLE IF NOT EXISTS `taskmaster` (
  `ID` int PRIMARY KEY
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'taskmaster'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TASKMASTER.CSV' INTO TABLE `taskmaster` FIELDS TERMINATED BY ';' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'assistant'
CREATE TABLE IF NOT EXISTS `assistant` (
  `ID` int PRIMARY KEY
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'assistant'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/ASSISTANT.CSV' INTO TABLE `assistant` FIELDS TERMINATED BY ';' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'job'
CREATE TABLE IF NOT EXISTS `job` (
  `CANDIDATE_ID` int NOT NULL,
  `JOB` varchar(50) NOT NULL,
  FOREIGN KEY (CANDIDATE_ID) REFERENCES candidate (ID)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'job'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/JOB.CSV' INTO TABLE `job` FIELDS TERMINATED BY ';' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'series'
CREATE TABLE IF NOT EXISTS `series` (
  `NAME` varchar(20) PRIMARY KEY,
  `NETWORK` varchar(20) NOT NULL,
  `STARTDATE` date NOT NULL,
  `ENDDATE` date NOT NULL,
  `TASKMASTER_ID` int NOT NULL,
  `ASSISTANT_ID` int NOT NULL,
  `CHAMPION_ID` int DEFAULT NULL,

  FOREIGN KEY (TASKMASTER_ID) REFERENCES taskmaster (ID),
  FOREIGN KEY (ASSISTANT_ID) REFERENCES assistant (ID),
  FOREIGN KEY (CHAMPION_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'series'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/SERIES.CSV' INTO TABLE `series` FIELDS TERMINATED BY ';' IGNORE 1 ROWS (NAME, NETWORK, STARTDATE, ENDDATE, TASKMASTER_ID, ASSISTANT_ID, @champion_id)
SET CHAMPION_ID = NULLIF(@champion_id,'');
-- -------------------------------------------------------------------------------------------------------------------------------

-- -------------------------------------------------------------------------------------------------------------------------------
-- Création de la table 'episode'
CREATE TABLE IF NOT EXISTS `episode` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TITLE` varchar(50) NOT NULL,
  `AIRDATE` date NOT NULL,
  `WINNER_ID` int,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (WINNER_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

ALTER TABLE `episode` ADD INDEX idx_episode_number (EPISODE_NUMBER);

-- Chargement des données dans la table 'episode'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/EPISODE.CSV' INTO TABLE `episode` FIELDS TERMINATED BY ';' IGNORE 1 ROWS (SERIES_NAME, EPISODE_NUMBER, TITLE, AIRDATE, @winner_id)
SET WINNER_ID = NULLIF(@winner_id,'');
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'feature'
CREATE TABLE IF NOT EXISTS `feature` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `CANDIDATE_ID` int NOT NULL,
  `CHAIR` int NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (CANDIDATE_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'feature'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/FEATURE.CSV' INTO TABLE `feature` FIELDS TERMINATED BY ';' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'team'
CREATE TABLE IF NOT EXISTS `team` (
  `ID` int PRIMARY KEY,
  `SERIES_NAME` varchar(20) NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'team'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TEAM.CSV' INTO TABLE `team` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'task'
CREATE TABLE IF NOT EXISTS `task` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,
  `DESCRIPTION` varchar(500) NOT NULL,
  `ISLIVETASK` BOOLEAN NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

ALTER TABLE `task` ADD INDEX task_number (TASK_NUMBER);

-- Chargement des données dans la table 'task'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TASK.CSV' INTO TABLE `task` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'individualtask'
CREATE TABLE IF NOT EXISTS `individualtask` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'individualtask'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/INDIVIDUALTASK.CSV' INTO TABLE `individualtask` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'membership'
CREATE TABLE IF NOT EXISTS `membership` (
  `TEAM_ID` int NOT NULL,
  `CANDIDATE_ID` int NOT NULL,

  FOREIGN KEY (TEAM_ID) REFERENCES team (ID),
  FOREIGN KEY (CANDIDATE_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'membership'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/MEMBERSHIP.CSV' INTO TABLE `membership` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'teamtask'
CREATE TABLE IF NOT EXISTS `teamtask` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'teamtask'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TEAMTASK.CSV' INTO TABLE `teamtask` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'tiebreaker'
CREATE TABLE IF NOT EXISTS `tiebreaker` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,
  `WINNER_ID` int DEFAULT NULL,
  `LOSER_ID` int DEFAULT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER),
  FOREIGN KEY (WINNER_ID) REFERENCES candidate (ID),
  FOREIGN KEY (LOSER_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'tiebreaker'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TIEBREAKER.CSV' INTO TABLE `tiebreaker` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'points'
CREATE TABLE IF NOT EXISTS `points` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,
  `CANDIDATE_ID` int NOT NULL,
  `POINTS` int NOT NULL,
  `WASDISQUALIFIED` BOOLEAN NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER),
  FOREIGN KEY (CANDIDATE_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'points'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/POINTS.CSV' INTO TABLE `points` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'points'
CREATE TABLE IF NOT EXISTS `teampoints` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,
  `TEAM_ID` int NOT NULL,
  `POINTS` int NOT NULL,
  `WASDISQUALIFIED` BOOLEAN NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER),
  FOREIGN KEY (TEAM_ID) REFERENCES team (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'teampoints'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TEAMPOINTS.CSV' INTO TABLE `teampoints` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

-- Création de la table 'tiebreakerresult'
CREATE TABLE IF NOT EXISTS `tiebreakerresult` (
  `SERIES_NAME` varchar(20) NOT NULL,
  `EPISODE_NUMBER` int NOT NULL,
  `TASK_NUMBER` int NOT NULL,
  `CANDIDATE_ID` int NOT NULL,
  `WON` BOOLEAN NOT NULL,

  FOREIGN KEY (SERIES_NAME) REFERENCES series (NAME),
  FOREIGN KEY (EPISODE_NUMBER) REFERENCES episode (EPISODE_NUMBER),
  FOREIGN KEY (TASK_NUMBER) REFERENCES task (TASK_NUMBER),
  FOREIGN KEY (CANDIDATE_ID) REFERENCES candidate (ID)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Chargement des données dans la table 'tiebreakerresult'
LOAD DATA INFILE '/docker-entrypoint-initdb.d/csv/TIEBREAKERRESULT.CSV' INTO TABLE `tiebreakerresult` FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
-- -------------------------------------------------------------------------------------------------------------------------------

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;
