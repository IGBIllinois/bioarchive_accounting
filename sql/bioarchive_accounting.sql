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
  `usage_time` datetime DEFAULT CURRENT_TIMESTAMP,
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
  `time_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `directories` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`directory` varchar(256) NOT NULL DEFAULT '',
	`time_created` datetime DEFAULT CURRENT_TIMESTAMP,
	`is_enabled` int(11) NOT NULL DEFAULT '1',
	`do_not_bill` int(11) NOT NULL DEFAULT '0',
	CONSTRAINT directory UNIQUE(directory),
	PRIMARY KEY (`id`)
);

CREATE TABLE settings (
	key VARCHAR(64) NOT NULL DEFAULT '',
	value VARCHAR(64) NOT NULL DEFAULT '',
 	modified DATETIME DEFAULT CURRENT_TIMESTAMP,
	name VARCHAR(64) NOT NULL DEFAULT '',
	PRIMARY KEY (key)
);

INSERT INTO `settings` (`key`, `name`)
VALUES
	('data_cost','Cost per TB'),
	('min_billable_data','Min Billable Data (MB)'),
	('small_file_size','Small File Size (KB)');

CREATE TABLE `settings_values` (
  `key` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(64) NOT NULL DEFAULT '',
  `modified` datetime DEFAULT CURRENT_TIMESTAMP,
  `current` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`key`,`modified`)
);

CREATE TABLE `transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) unsigned NOT NULL,
  `amount` int(11) NOT NULL,
  `usage_id` int(11) unsigned DEFAULT NULL,
  `transaction_time` datetime DEFAULT CURRENT_TIMESTAMP,
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
	`time_created` datetime DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT username UNIQUE(username);
	PRIMARY KEY (`id`)
);

CREATE VIEW `billing_report` AS
SELECT `u`.`id` AS `id`,
`u`.`directory_id` AS `directory_id`,
`d`.`directory` AS `directory`,
`u`.`directory_size` AS `directory_size`,
ceiling(`u`.`directory_size` / 1048576) AS `bracket`,
`u`.`usage_time` AS `usage_time`,
`u`.`cost` AS `cost`,
`u`.`billed_cost` AS `billed_cost`,
`t`.`transaction_amount` * -1 AS `tokens_used`
FROM ((`archive_usage` `u` join `directories` `d` on(`d`.`id` = `u`.`directory_id`))
LEFT JOIN `token_transactions` `t` on(`u`.`token_transaction_id` = `t`.`id`))

INSERT INTO `settings_values` (`key`, `value`, `modified`, `current`)
VALUES
        ('data_cost','150','2015-01-01 00:00:00',1),
        ('min_billable_data','51200','2015-01-01 00:00:00',1),
        ('small_file_size','1048576','2015-01-01 00:00:00',1);

