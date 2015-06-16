ALTER TABLE `events` ADD `user_id` INT(11)  UNSIGNED  NOT NULL  AFTER `id`;
UPDATE TABLE `events` SET `user_id` = 1 WHERE `user_id` = 0;
