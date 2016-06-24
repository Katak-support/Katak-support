ALTER TABLE `%TABLE_PREFIX%clients`
ADD `client_group_id` int(11) unsigned DEFAULT NULL;

DROP TABLE IF EXISTS `%TABLE_PREFIX%groups`;
CREATE TABLE `%TABLE_PREFIX%groups` (
  `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(32) NOT NULL,
  `group_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `group_can_edit_tickets` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `group_created` datetime NOT NULL,
  `group_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

