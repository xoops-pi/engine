# $Id$

# logging data
CREATE TABLE `log` (
  `user`            int(10)         unsigned NOT NULL,
  `activate_time`   int(10)         unsigned default NULL,
  `activate_ip`     char(15)        default NULL,
  `update_time`     int(10)         unsigned default NULL,
  `update_ip`       char(15)        default NULL,
  `login_time`      int(10)         unsigned default NULL,
  `login_ip`        char(15)        default NULL,
  `attempts`        smallint(3)     unsigned default NULL,
  `attempt_time`    int(10)         unsigned default NULL,
  `attempt_ip`      char(15)        default NULL,

  PRIMARY KEY  (`user`)
);

# hash for registration activation or for password resetting
CREATE TABLE `hash` (
  `hash`            char(8)         NOT NULL,
  `user`            int(10)         unsigned NOT NULL,
  `expire`          int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`hash`)
);

# Category for profile display
CREATE TABLE `category` (
  `id`              smallint(5)     unsigned    NOT NULL    auto_increment,
  `key`             varchar(64)     NOT NULL,
  `title`           varchar(255)    NOT NULL default '',
  `order`           smallint(5)     unsigned    NOT NULL default '9',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `identity` (`key`)
);

# Profile field-category list for profile display with order
CREATE TABLE `meta_category` (
  `meta`            varchar(64)     NOT NULL,
  `category`        varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned    NOT NULL default '99',

  PRIMARY KEY  (`meta`)
);
