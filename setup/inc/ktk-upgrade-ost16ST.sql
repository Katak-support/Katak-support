ALTER TABLE `%TABLE_PREFIX%config` 
ADD `staff_language` CHAR(8) NOT NULL DEFAULT 'en' AFTER `isonline`,
ADD `client_language` CHAR(8) NOT NULL DEFAULT 'en' AFTER `staff_language`,
ADD `reopen_grace_period` INT(10) UNSIGNED NOT NULL DEFAULT '90' AFTER `overdue_grace_period`,
ADD `enable_topic` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `use_email_priority`,
DROP `show_assigned_tickets`,
DROP `show_answered_tickets`,
DROP `admin_email`,
CHANGE `helpdesk_title` `helpdesk_title` VARCHAR(255) NOT NULL DEFAULT 'KataK Support Ticket System',
CHANGE `ostversion` `ktsversion` VARCHAR(16) NOT NULL;

DROP TABLE IF EXISTS `%TABLE_PREFIX%ticket_priority`;
CREATE TABLE `%TABLE_PREFIX%priority` (
  `priority_id` tinyint(4) NOT NULL auto_increment,
  `priority` varchar(60) NOT NULL default '',
  `priority_desc` varchar(30) NOT NULL default '',
  `priority_color` varchar(7) NOT NULL default '',
  `priority_urgency` tinyint(1) unsigned NOT NULL default '0',
  `ispublic` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`priority_id`),
  UNIQUE KEY `priority` (`priority`),
  KEY `priority_urgency` (`priority_urgency`),
  KEY `ispublic` (`ispublic`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `%TABLE_PREFIX%priority` (`priority_id`, `priority`, `priority_desc`, `priority_color`, `priority_urgency`, `ispublic`) VALUES
(1, 'low', 'Low', '#119911', 4, 1),
(2, 'normal', 'Normal', '#333333', 3, 1),
(3, 'high', 'High', '#CC9900', 2, 1),
(4, 'urgent', 'Urgent', '#CC3300', 1, 0);
ALTER TABLE `%TABLE_PREFIX%groups` RENAME TO `%TABLE_PREFIX%roles`;

ALTER TABLE `%TABLE_PREFIX%roles`
CHANGE `group_id` `role_id` INT(10) UNSIGNED NOT NULL,
CHANGE `group_enabled` `role_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `group_name` `role_name` VARCHAR(50) NOT NULL,
ADD `can_viewunassigned_tickets` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `dept_access`,
ADD `can_changepriority_tickets` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `can_edit_tickets`,
ADD `can_assign_tickets` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `can_changepriority_tickets`,
CHANGE `can_manage_kb` `can_manage_stdr` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `%TABLE_PREFIX%staff`
CHANGE `group_id` `role_id` INT(10) UNSIGNED NOT NULL,
CHANGE `phone` `phone` VARCHAR(28) NULL,
CHANGE `mobile` `mobile` VARCHAR(28) NULL,
DROP `phone_ext`,
ADD `language` CHAR(8) NOT NULL DEFAULT 'en' AFTER `timezone_offset`;

ALTER TABLE `%TABLE_PREFIX%kb_premade` RENAME TO `%TABLE_PREFIX%std_reply`;

ALTER TABLE `%TABLE_PREFIX%std_reply`
CHANGE `premade_id` `stdreply_id` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `%TABLE_PREFIX%ticket`
CHANGE `ip_address` `ip_address` VARCHAR(32) NOT NULL,
CHANGE `phone` `phone` VARCHAR(28) NULL,
DROP `phone_ext`,
ADD `firstresponse` DATETIME NULL AFTER `lastmessage`;

ALTER TABLE `%TABLE_PREFIX%ticket_message`
ADD `msg_type` ENUM('F','M','R') NULL AFTER `messageId`,
ADD `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `message`,
ADD `staff_name` VARCHAR(32) NULL AFTER `staff_id`;

UPDATE `%TABLE_PREFIX%ticket_message`
SET `msg_type`='M';

ALTER TABLE `%TABLE_PREFIX%ticket_note` RENAME TO `%TABLE_PREFIX%ticket_events`;

DROP TABLE IF EXISTS `%TABLE_PREFIX%timezone`;
CREATE TABLE `%TABLE_PREFIX%timezone` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `offset` float(3,1) NOT NULL default '0.0',
  `timezone` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `%TABLE_PREFIX%timezone` (`id`, `offset`, `timezone`) VALUES
(1, -12.0, 'Eniwetok, Kwajalein'),
(2, -11.0, 'Midway Island, Samoa'),
(3, -10.0, 'Hawaii'),
(4, -9.0, 'Alaska'),
(5, -8.0, 'Pacific Time (US & Canada)'),
(6, -7.0, 'Mountain Time (US & Canada)'),
(7, -6.0, 'Central Time (US & Canada), Mexico City'),
(8, -5.0, 'Eastern Time (US & Canada), Bogota, Lima'),
(9, -4.0, 'Atlantic Time (Canada), Caracas, La Paz'),
(10, -3.5, 'Newfoundland'),
(11, -3.0, 'Brasilia, Rio de Janeiro Buenos Aires, Georgetown, Santiago'),
(12, -2.0, 'Mid-Atlantic'),
(13, -1.0, 'Azores, Cape Verde Islands'),
(14, 0.0, 'Western Europe Time, London, Lisbon, Casablanca, Dakar'),
(15, 1.0, 'Brussels, Berlin, Copenhagen, Madrid, Paris, Rom, Brazzaville'),
(16, 2.0, 'Athens, Kaliningrad, South Africa'),
(17, 3.0, 'Baghdad, Riyadh, Moscow, St. Petersburg'),
(18, 3.5, 'Tehran'),
(19, 4.0, 'Abu Dhabi, Muscat, Baku, Tbilisi'),
(20, 4.5, 'Kabul'),
(21, 5.0, 'Ekaterinburg, Islamabad, Karachi, Tashkent'),
(22, 5.5, 'Bombay, Calcutta, Madras, New Delhi'),
(23, 6.0, 'Almaty, Dhaka, Colombo'),
(24, 7.0, 'Bangkok, Hanoi, Jakarta'),
(25, 8.0, 'Beijing, Perth, Singapore, Hong Kong'),
(26, 9.0, 'Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
(27, 9.5, 'Adelaide, Darwin'),
(28, 10.0, 'Eastern Australia, Guam, Vladivostok'),
(29, 11.0, 'Magadan, Solomon Islands, New Caledonia'),
(30, 12.0, 'Auckland, Wellington, Fiji, Kamchatka');