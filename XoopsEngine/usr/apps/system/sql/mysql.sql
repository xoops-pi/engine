# $Id$
CREATE TABLE `shortcut` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `title`   varchar(255)            default NULL,
  `link`    varchar(255)            default NULL,
  `icon`    varchar(255)            default NULL,
  `order`   int(10) unsigned default '99',

  PRIMARY KEY  (`id`)
);

CREATE TABLE `task` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `memo`    varchar(255)            default NULL,
  `order`   int(10) unsigned        default '99',
  `uid_created`     int(10) unsigned default '0',
  `uid_finished`    int(10) unsigned default '0',
  `time_created`    int(10) unsigned default '0',
  `time_finished`   int(10) unsigned default '0',

  PRIMARY KEY  (`id`)
);

CREATE TABLE `update` (
  `id`          int(10)         unsigned NOT NULL auto_increment,
  `title`       varchar(255)    default NULL,
  `content`     text,
  `module`      varchar(64)     default NULL,
  `controller`  varchar(64)     default NULL,
  `action`      varchar(64)     default NULL,
  `route`       varchar(64)     default NULL,
  `params`      varchar(255)    default NULL,
  `uri`         varchar(255)    default NULL,
  `time`        int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`)
);