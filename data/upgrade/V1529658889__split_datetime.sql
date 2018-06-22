ALTER TABLE `events` ADD `start_time` TIME  NULL  AFTER `start`;
UPDATE events SET start_time = SUBSTRING_INDEX(start, ' ', 2);
UPDATE events SET start = DATE(start);
ALTER TABLE events MODIFY start DATE;
UPDATE events SET start_time = NULL WHERE start = '00:00:00';
ALTER TABLE `events` CHANGE `start` `start_date` DATE  NOT NULL;

ALTER TABLE `events` ADD `end_time` TIME  NULL  AFTER `end`;
UPDATE events SET end_time = SUBSTRING_INDEX(end, ' ', 2);
UPDATE events SET end = DATE(end);
ALTER TABLE events MODIFY end DATE;
UPDATE events SET end_time = NULL WHERE start = '00:00:00';
ALTER TABLE `events` CHANGE `end` `end_date` DATE  NULL  DEFAULT NULL;

ALTER TABLE `events` ADD `timezone` VARCHAR(100)  NULL  DEFAULT 'UTC'  AFTER `end_time`;
