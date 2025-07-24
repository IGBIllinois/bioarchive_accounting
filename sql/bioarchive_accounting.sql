CREATE TABLE `archive_files` (
  `filename` varchar(256) NOT NULL DEFAULT '',
  `filesize` int(11) NOT NULL,
  `usage_id` int(11) unsigned NOT NULL DEFAULT '0',
  `file_time` datetime DEFAULT NULL,
  PRIMARY KEY (`filename`,`usage_id`),
  KEY `usage_id` (`usage_id`),
  CONSTRAINT `archive_files_ibfk_1` FOREIGN KEY (`usage_id`) REFERENCES `archive_usage` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE `archive_usage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) unsigned NOT NULL,
  `directory_size` int(11) NOT NULL DEFAULT '0' COMMENT 'directory size in MB',
  `num_small_files` int(11) NOT NULL DEFAULT '0',
  `usage_time` datetime NOT NULL,
  `cost` varchar(16) NOT NULL DEFAULT '0' COMMENT 'what the cost should be',
  `billed_cost` int(11) DEFAULT NULL COMMENT 'actual billed amount',
  `tokens_used` int(11) DEFAULT NULL COMMENT 'number of tokens used',
  `pending` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
);

CREATE TABLE `cfops` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) unsigned NOT NULL,
  `cfop` varchar(64) NOT NULL DEFAULT '',
  `activity_code` varchar(16) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `time_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `directories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `directory` varchar(256) NOT NULL DEFAULT '',
  `time_created` datetime NOT NULL,
  `is_enabled` int(11) NOT NULL DEFAULT '1',
  `do_not_bill` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `settings` (
  `key` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
);

INSERT INTO `settings` (`key`, `name`)
VALUES
	('data_cost','Cost per TB'),
	('min_billable_data','Min Billable Data (MB)'),
	('small_file_size','Small File Size (KB)');

CREATE TABLE `settings_values` (
  `key` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(64) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `current` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`key`,`modified`)
);

INSERT INTO `settings_values` (`key`, `value`, `modified`, `current`)
VALUES
	('data_cost','150','2015-01-01 00:00:00',1),
	('min_billable_data','51200','2015-01-01 00:00:00',1),
	('small_file_size','1048576','2015-01-01 00:00:00',1);

CREATE TABLE `transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `usage_id` int(11) unsigned DEFAULT NULL,
  `transaction_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usage_id` (`usage_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`usage_id`) REFERENCES `archive_usage` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `is_admin` int(11) NOT NULL DEFAULT '0',
  `is_enabled` int(11) NOT NULL DEFAULT '0',
  `time_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
