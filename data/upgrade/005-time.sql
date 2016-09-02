ALTER TABLE `events` ADD `is_promoted` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `is_published`;
ALTER TABLE `events` CHANGE `start` `start` DATETIME  NOT NULL;
ALTER TABLE `events` CHANGE `end` `end` DATETIME  NULL;
