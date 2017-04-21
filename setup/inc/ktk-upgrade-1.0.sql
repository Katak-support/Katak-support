DROP TABLE IF EXISTS `%TABLE_PREFIX%clients`;
CREATE TABLE `%TABLE_PREFIX%clients` (
  `client_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_email` varchar(128) NOT NULL,
  `client_firstname` varchar(32) DEFAULT NULL,
  `client_lastname` varchar(32) DEFAULT NULL,
  `client_password` varchar(128) DEFAULT NULL,
  `client_organization` varchar(128) DEFAULT NULL,
  `client_phone` varchar(28) DEFAULT NULL,
  `client_mobile` varchar(28) DEFAULT NULL,
  `client_isactive` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `client_group_id` int(11) unsigned DEFAULT NULL,
  `client_created` datetime DEFAULT NULL,
  `client_lastlogin` datetime DEFAULT NULL,
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `email` (`client_email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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

ALTER TABLE `%TABLE_PREFIX%config` 
CHANGE `client_language` `user_language` CHAR(8) NOT NULL DEFAULT 'en',
ADD `user_log_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `staff_session_timeout`,
ADD `response_notice_active` tinyint(1) unsigned NOT NULL default '0' AFTER `message_autoresponder`,
ADD `assignment_alert_active` tinyint(1) unsigned NOT NULL default '0' AFTER `message_alert_dept_manager`;