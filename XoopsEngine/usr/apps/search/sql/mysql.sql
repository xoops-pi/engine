# $Id$

CREATE TABLE `cache` (
  `id`      varchar(32)     NOT NULL    default '',
  `data`    text,
  `time`    int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`)
);