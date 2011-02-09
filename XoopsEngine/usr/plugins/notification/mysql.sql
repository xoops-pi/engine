# $Id$
# --------------------------------------------------------


# Notification records
CREATE TABLE `notification_subscription` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # comment unique ID
  `item`            int(10)         unsigned    NOT NULL    default '0',    # ID of item resource, identified by param in notification_item
  `category`        int(10)         unsigned    NOT NULL    default '0',    # category ID, id in notification_category
  `user`            int(10)         unsigned    NOT NULL    default '0',    # user's ID
  `module`          varchar(64)     NOT NULL    default '',                 # module to which the item belongs, solely for maintenance

  PRIMARY KEY  (`id`),
  KEY `item` (`item`),
  KEY `category` (`category`),
  KEY `user` (`user`)
);

# Stats of an item to which notifications belong
CREATE TABLE `notification_item_NOUSE` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # item unique ID
  `disabled`        tinyint(1)      unsigned    NOT NULL    default '0',    # disabled specificly
  `category`        int(10)         unsigned    NOT NULL    default '0',    # category ID, id in comment_category
  `param`           int(10)         unsigned    NOT NULL    default '0',    # parameter value to identify item resource
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `item` (`category`, `param`),
  KEY `module` (`module`)
);

# Category of items to which notifications belong
CREATE TABLE `notification_category` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment, # category unique ID
  `title`           varchar(255)    NOT NULL    default '',                 # category title, for admin
  `active`          tinyint(1)      unsigned    NOT NULL default '0',
  `name`            varchar(64)     NOT NULL    default '',                 # category module wide unique key
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `param`           varchar(64)     NOT NULL    default '',                 # param name to identify item resource
  `callback`        varchar(255)    NOT NULL    default '',                 # callback for generating notification content
  `content`         text,                                                   # template for notification content if callback is not set
  `translate`       varchar(64)     NOT NULL    default '',                 # translation file for notification, optional

  PRIMARY KEY  (`id`),
  UNIQUE `name` (`module`, `name`)
);