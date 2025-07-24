# Bioarchive Accounting
[![Build Status](https://github.com/IGBIllinois/bioarchive_accounting/actions/workflows/main.yml/badge.svg)](https://github.com/IGBIllinois/bioarchive_accounting/actions/workflows/main.yml)

Accounting program for IGB bioarchive tape archive

# Installation

## Prerequisisities
- PHP
- PHP Mysql
- PHP LDAP
- PHP XML

1. Git clone https://github.com/IGBIllinois/bioarchive_accounting.git or download a tagged tar.gz
```
git clone https://github.com/IGBIllinois/bioarchive_accounting.git
```
2. Add apache config to apache configuration to point to html directory
```
Alias /bioarchive_accounting /var/www/bioarchive_accounting/html
<Location /bioarchive_accounting>
	AllowOverride None
	Require all granted
</Location>
```

3.  Create MySQL Database
```
CREATE DATABASE bioarchive_accounting CHARACTER SET utf8;
```
4. Run sql/bioarchive_accounting.sql
```
mysql -u root -p bioarchive_accounting < sql/bioarchive_accounting.sql
```

5. Create a user/password on the mysql server with has select/insert/delete/update permissions on the bioarchive_accounting database
```
CREATE USER 'bioarchive_accounting'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON bioarchive_accounting.* to 'bioarchive_accounting'@'localhost';
```
6.  Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
7. Run composer to install php dependencies
```
composer install
```
8.  To enable cron to calculate storage usage
```
cp /var/www/bioarchive_accounting/conf/cron.dist /etc/cron.d/bioarchive_accounting
```

