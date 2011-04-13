# $Id$

CREATE TABLE `test` (
  `id`      int(10) unsigned        NOT NULL auto_increment,
  `message` varchar(255)            NOT NULL default '',
  `active`  tinyint(1)              NOT NULL default '1',

  PRIMARY KEY  (`id`)
);