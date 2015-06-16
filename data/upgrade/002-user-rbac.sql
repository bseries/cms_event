ALTER TABLE `events` ADD `owner_id` INT(11)  UNSIGNED  NOT NULL  AFTER `id`;
UPDATE TABLE `events` SET `owner_id` = 1 WHERE `owner_id` = 0;
