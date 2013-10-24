CREATE TABLE `events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cover_media_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL DEFAULT '',
  `start` date NOT NULL,
  `end` date DEFAULT NULL,
  `is_open_end` tinyint(1) NOT NULL DEFAULT '0',
  `location` varchar(250) DEFAULT NULL,
  `teaser` text,
  `body` text NOT NULL,
  `url` varchar(250) DEFAULT NULL,
  `tags` varchar(250) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;