ALTER TABLE `events` ADD `is_promoted` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `is_published`;
