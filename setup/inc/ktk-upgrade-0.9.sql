ALTER TABLE `%TABLE_PREFIX%help_topic`
ADD `autoassign_id` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `dept_id`;

ALTER TABLE `%TABLE_PREFIX%syslog`
DROP `updated`;

ALTER TABLE `%TABLE_PREFIX%ticket`
DROP `helptopic`;

ALTER TABLE `%TABLE_PREFIX%ticket_message`
DROP `updated`;

ALTER TABLE `%TABLE_PREFIX%ticket_attachment`
DROP `deleted`,
DROP `updated`;