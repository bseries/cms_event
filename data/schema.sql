CREATE TABLE `events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) unsigned NOT NULL,
  `cover_media_id` int(11) DEFAULT NULL,
  `title` varchar(250) NOT NULL DEFAULT '',
  `start` date NOT NULL,
  `end` date DEFAULT NULL,
  `location` varchar(250) DEFAULT NULL,
  `teaser` text,
  `body` text NOT NULL,
  `tags` varchar(250) DEFAULT NULL,
  `ticket_url` varchar(250) DEFAULT NULL,
  `is_sold_out` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_published` (`is_published`),
  KEY `start` (`start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
