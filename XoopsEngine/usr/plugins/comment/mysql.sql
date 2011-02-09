# $Id$
# --------------------------------------------------------

# Comment records
CREATE TABLE `comment_post` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # comment unique ID
  `item`            int(10)         unsigned    NOT NULL    default '0',    # item ID => id in comment_item
# `category`        int(10)         unsigned    NOT NULL    default '0',    # category ID, id in comment_category
# `param`           int(10)         unsigned    NOT NULL    default '0',
  `created`         int(10)         unsigned    NOT NULL    default '0',    # time of creation
  `modified`        int(10)         unsigned    NOT NULL    default '0',    # time of update
  `user`            int(10)         unsigned    NOT NULL    default '0',    # user's ID
  `ip`              varchar(15)     NOT NULL    default '',                 # user's IP
  `content`         text,
  `active`          tinyint(1)      unsigned    NOT NULL default '0',       # approved
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  KEY `item` (`item`),
  KEY `user` (`user`),
  KEY `module` (`module`)
);

# Stats of an item to which comments belong
CREATE TABLE `comment_item` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # item unique ID
  `title`           varchar(255)    NOT NULL    default '',                 # item's title, not implemented yet
  `disabled`        tinyint(1)      unsigned    NOT NULL    default '0',    # disabled specificly
  `category`        int(10)         unsigned    NOT NULL    default '0',    # category ID, id in comment_category
  `param`           int(10)         unsigned    NOT NULL    default '0',    # parameter value to identify item resource
  `active`          int(10)         unsigned    NOT NULL    default '0',    # count of approved comments
  `pending`         int(10)         unsigned    NOT NULL    default '0',    # count of pending comments
  `updated`         int(10)         unsigned    NOT NULL    default '0',    # last update time, to clear cache
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `item` (`category`, `param`),
  KEY `module` (`module`)
);

# Category of items to which comments belong
CREATE TABLE `comment_category` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # category unique ID
  `title`           varchar(255)    NOT NULL    default '',                 # category title, for admin
  `active`          tinyint(1)      unsigned    NOT NULL default '0',
  `key`             varchar(64)     NOT NULL    default '',                 # category module wide unique key
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `param_item`      varchar(64)     NOT NULL    default '',                 # param name to identify item resource
  `param_page`      varchar(64)     NOT NULL    default '',                 # param name to identify comment page
  `template`        varchar(255)    NOT NULL    default '',                 # template for displaying comments, default as "comment.html" in theme
# `items_perpage`   smallint(3)     unsigned    NOT NULL default '0',     
  `expire`          int(10)         unsigned    NOT NULL default '0',       # cache expires

  PRIMARY KEY  (`id`),
  UNIQUE `key` (`module`, `key`)
);
