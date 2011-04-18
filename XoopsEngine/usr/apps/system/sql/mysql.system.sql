# $Id$
# --------------------------------------------------------

#
# Table structure for table `session`
#

CREATE TABLE `session` (
  `id`          varchar(32) NOT NULL default '',
  `modified`    int(10) unsigned NOT NULL default '0',
  `lifetime`    int(10) unsigned NOT NULL default '0',
  `data`        text,
  PRIMARY KEY  (`id`),
  KEY `modified` (`modified`)
);
# --------------------------------------------------------

CREATE TABLE `navigation` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `cache`           int(10)         unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE `navigation_page` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `left`            int(10)         unsigned    NOT NULL default '0',
  `right`           int(10)         unsigned    NOT NULL default '0',
  `depth`           smallint(3)     unsigned    NOT NULL default '0',
  `navigation`      varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     default     NULL,
  `label`           varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     default     NULL,
  `controller`      varchar(64)     default     NULL,
  `action`          varchar(64)     default     NULL,
  `route`           varchar(64)     default     NULL,
  `uri`             varchar(255)    default     NULL,
  `resource`        varchar(255)    NOT NULL    default '',
  `params`          varchar(255)    NOT NULL    default '',
  `visible`         tinyint(1)      unsigned    NOT NULL default '1',
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `left` (`left`),
  UNIQUE KEY `right` (`right`)
# UNIQUE KEY `module_name` (`navigation`, `module`, `name`)
);

CREATE TABLE `acl_role` (
# `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `description`     text,
  `active`          tinyint(1)      unsigned    NOT NULL default '1',
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`name`)
);

# role inheritance or edge: edge ID, start vertex, end vertex
CREATE TABLE `acl_inherit` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `child`           varchar(64)     NOT NULL    default '',
  `parent`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `pair` (`child`, `parent`)
);

# extended edge table: edge ID, entry edge ID, direct edge, exit edge, start vertex, end vertex
# DAG (Directed Acyclic Graph) algorithm
# see: http://www.codeproject.com/KB/database/Modeling_DAGs_on_SQL_DBs.aspx#Table5
CREATE TABLE `acl_edge` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `start`           varchar(64)     NOT NULL    default '',
  `end`             varchar(64)     NOT NULL    default '',
  `entry`           int(10)         unsigned    NOT NULL default '0',
  `direct`          int(10)         unsigned    NOT NULL default '0',
  `exit`            int(10)         unsigned    NOT NULL default '0',
  `hops`            int(10)         unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `pair` (`start`, `end`)
);

CREATE TABLE `acl_user` (
  `user`            int(10)         unsigned NOT NULL,
  `role`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`user`)
);

CREATE TABLE `acl_resource` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `left`            int(10)         unsigned    NOT NULL default '0',
  `right`           int(10)         unsigned    NOT NULL default '0',
  `depth`           smallint(3)     unsigned    NOT NULL default '0',
  `section`         varchar(64)     NOT NULL    default '', # page resource: admin, front; other resource: block
  `name`            varchar(64)     NOT NULL    default '', # pattern: generated - module[:controller]; or custom - module-resource
  `item`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
# `parent`          varchar(64)     NOT NULL    default '',
# `controller`      varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `type`            varchar(64)     default     NULL, # potential values: system - created by module installation; page - created by page creation; custom - created manually

  PRIMARY KEY  (`id`),
  UNIQUE KEY `left` (`left`),
  UNIQUE KEY `right` (`right`),
  UNIQUE KEY `pair` (`section`, `module`, `name`, `item`)
);

CREATE TABLE `acl_privilege` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `resource`        int(10)         unsigned    NOT NULL default '0', # resource ID
  `name`            varchar(64)     NOT NULL    default '', # Privilege name
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY (`id`),
  UNIQUE KEY `pair` (`resource`, `name`)
);

CREATE TABLE `acl_rule` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `section`         varchar(64)     NOT NULL    default '',
  `role`            varchar(64)     NOT NULL    default '',
  `resource`        varchar(64)     NOT NULL    default '',
# `item`            varchar(64)     NOT NULL    default '',
  `privilege`       varchar(64)     NOT NULL    default '',
  `deny`            tinyint(1)      unsigned    NOT NULL default '0',   # 0 for allowed; 1 for denied
  `module`          varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  KEY `pair` (`resource`, `privilege`),
  KEY `section_module` (`section`, `module`)
);

# mvc pages
CREATE TABLE `page` (
  `id`              int(8)    unsigned    NOT NULL auto_increment,
  `title`           varchar(64)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '', # page resource: admin, front; other resource: block
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `cache_expire`    int(10)         NOT NULL    default '0',            # positive: for cache expiration; negative: for inheritance
  `cache_level`     varchar(64)     NOT NULL    default '',
  `block`           tinyint(1)      unsigned    NOT NULL default '0',   # block inheritance: 1 - for self-setting; 0 - for inheriting form parent
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `mca` (`section`, `module`, `controller`, `action`)
);

CREATE TABLE `page_block` (
  `id`              int(8)    unsigned    NOT NULL auto_increment,
  `page`            int(8)    unsigned    NOT NULL    default '0',
  `block`           int(8)    unsigned    NOT NULL    default '0',
  `position`        smallint(5)     unsigned    NOT NULL    default '0', #potential value: 0 - left, 1 - right, 2 - topleft, 3 - topcenter, 4 - topright, 5 - bottomleft, 6 - bottomcenter, 7 - bottomright
  `order`           int(8)    NOT NULL    default '5',    # positive: display orer; negative: id of global page-block link that will be disabled on a specific page

  PRIMARY KEY  (`id`),
  KEY `page_block` (`page`, `order`, `block`)    # not necessarily UNIQUE
);

CREATE TABLE `block` (
  `id`              int(8)          unsigned NOT NULL auto_increment,
  `root`            int(8)          unsigned NOT NULL default '0',  # root ID for cloned block
  `key`             varchar(64)     NOT NULL default '',            # internal key
  `name`            varchar(64)     NOT NULL default '',            # user key, empty or unique string, for calling from template
  `title`           varchar(255)    NOT NULL default '',

  `description`     text,                                           # Description
  `type`            varchar(64)     NOT NULL default '',            # Content type: "" - generated; C - Compound; H - HTML style; P - PHP enabled; S - bbcode with smiley; T - bbcode without smiley
  `render`          varchar(64)     NOT NULL default '',            # for generated, render class::method
  `options`         text,                                           # serialized options. regular block: for content generating; block compound: display options for blocks
  `active`          tinyint(1)      unsigned NOT NULL default '1',  # for generated, updated by system on module activation
  `module`          varchar(64)     NOT NULL default '',            # module generating the block
  `content`         text,                                           # for custom
  `cache_expire`    int(10)         unsigned NOT NULL default '0',
  `cache_level`     varchar(64)     NOT NULL default '',            # for custom

  `title_hidden`    tinyint(1)      unsigned NOT NULL default '0',  # Hide the title
  `link`            varchar(255)    NOT NULL default '',            # URL the title linked to
  `style`           varchar(255)    NOT NULL default '',            # regular block: specified stylesheet class for display; block compound: display style for blocks

  #@+ Legacy fields
  `func_file`       varchar(64)     NOT NULL default '',            # for generated
  `show_func`       varchar(64)     NOT NULL default '',            # for generated
  `edit_func`       varchar(64)     NOT NULL default '',            # for generated
  `template`        varchar(64)     NOT NULL default '',            # for generated
  #@-

  PRIMARY KEY  (`id`)
);

CREATE TABLE `block_compound` (
  `id`              int(8)    unsigned    NOT NULL auto_increment,
  `compound`        int(8)    unsigned    NOT NULL    default '0',
  `block`           int(8)    unsigned    NOT NULL    default '0',
  `order`           int(8)    NOT NULL    default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `compound_block` (`compound`, `block`)
);


# block option
CREATE TABLE `block_option` (
  `id`              int(10)         unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL default '',            # key, empty or unique string for a block
  `block`           int(8)          unsigned NOT NULL default '0',  # block ID
# `module`          varchar(64)     NOT NULL    default '',         # Dirname of module
  `title`           varchar(255)    NOT NULL default '',
  `description`     varchar(255)    NOT NULL default '',
  `edit`            tinytext,                                       # callback options for edit
  `options`         text,                                           # serialized options data
  `filter`          varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned NOT NULL default '0',
  `default`         tinytext,

  PRIMARY KEY   (`id`),
  KEY `block_option`  (`block`, `order`)
);

CREATE TABLE `audit` (
  `id`              int(10)         unsigned NOT NULL auto_increment,
  `user`            varchar(64)     NOT NULL    default '',
  `ip`              varchar(15)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `memo`            varchar(255)    NOT NULL    default '',
  `extra`           text,
  `time`            int(10)         unsigned NOT NULL   default '0',
  PRIMARY KEY  (`id`)
);


CREATE TABLE `event` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `active`          tinyint(1)      NOT NULL    default '1',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`module`, `name`)
);


CREATE TABLE `event_observer` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `event`           varchar(64)     NOT NULL    default '',
  `event_module`    varchar(64)     NOT NULL    default '',
  `class`           varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `active`          tinyint(1)      NOT NULL    default '1',

  PRIMARY KEY  (`id`)
);

CREATE TABLE `module` (
  `id`          smallint(5)         unsigned NOT NULL auto_increment,
  `name`        varchar(64)         NOT NULL default '',
  `version`     varchar(64)         NOT NULL default '',
  `update`      int(10)             unsigned NOT NULL default '0',
  `active`      tinyint(1)          unsigned NOT NULL default '1',
  `dirname`     varchar(64)         NOT NULL default '',
  `parent`      varchar(64)         NOT NULL default '',                # dirname of original module, from which the module is cloned

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `dirname` (`dirname`),
  KEY           `name` (`name`(15))
);

CREATE TABLE `plugin` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL default '',
  `dirname`         varchar(64)     NOT NULL default '',
  `version`         varchar(64)     NOT NULL default '',
  `author`          varchar(255)    NOT NULL default '',
  `description`     tinytext,
  `update`          int(10)         unsigned NOT NULL default '0',
  `active`          tinyint(1)      unsigned NOT NULL default '1',
  `official`        tinyint(1)      unsigned NOT NULL default '0',
  `autoload`        tinyint(1)      unsigned NOT NULL default '0',
  `order`           smallint(5)     NOT NULL default '0',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `dirname` (`dirname`)
);


CREATE TABLE `theme` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL default '',
  `dirname`         varchar(64)     NOT NULL default '',
  `version`         varchar(64)     NOT NULL default '',
  `author`          varchar(255)    NOT NULL default '',
  `update`          int(10)         unsigned NOT NULL default '0',
  `active`          tinyint(1)      unsigned NOT NULL default '1',
  `parent`          varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned NOT NULL default '0',
  `screenshot`      varchar(255)    NOT NULL default '',
# `license`         varchar(255)    NOT NULL default '',
  `type`            varchar(32)     NOT NULL default 'both',   # Type of theme: both - both front and admin; front - front; admin - admin

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `dirname` (`dirname`)
);


CREATE TABLE `search` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `callback`        varchar(64)     NOT NULL default '',
  `func`            varchar(64)     NOT NULL default '',
  `file`            varchar(255)    NOT NULL default '',
  `active`          tinyint(1)      unsigned NOT NULL default '1',

  PRIMARY KEY       (`id`),
  UNIQUE KEY        `module` (`module`)
);


CREATE TABLE `route` (
  `id`              int(8)          unsigned    NOT NULL auto_increment,
  `priority`        smallint(5)     NOT NULL    default '0',
  `section`         varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,
  `active`          tinyint(1)      unsigned NOT NULL default '1',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`module`, `name`)
);


# System config
CREATE TABLE `config` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `module`          varchar(64)     NOT NULL    default '',             # Dirname of module, or "plugin" for plugins
  `category`        varchar(64)     NOT NULL    default '',             # Category name of configs, or plugin dirname
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL default '',
  `value`           text,
  `description`     varchar(255)    NOT NULL default '',
  `edit`            tinytext        default NULL,           # callback options for edit
  `filter`          varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned NOT NULL default '0',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `module_key`   (`module`, `category`, `name`),
  KEY `module_category`  (`module`, `category`)
);

# System config category
CREATE TABLE `config_category` (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `module`          varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     NOT NULL    default '',
  `description`     varchar(255)    NOT NULL    default '',
  `key`             varchar(64)     NOT NULL    default '',
  `order`           smallint(5)     unsigned NOT NULL default '99',

  PRIMARY KEY  (`id`),
  UNIQUE KEY        `module_key`   (`module`, `key`)
);

# System config option
CREATE TABLE `config_option` (
  `id`              int(8)          unsigned NOT NULL auto_increment,
  `name`            varchar(64)     NOT NULL    default '',
  `value`           varchar(255)    NOT NULL default '',
  `config`          smallint(5)     unsigned NOT NULL default '0',

  PRIMARY KEY  (`id`),
  KEY `config` (`config`)
);


# user ID: the unique identity in the system
# user identity: the user's unique identity, generated by the system or sent from other systems like openID
# all local data of a user should be indexed by user ID

# User accout and authentication data
CREATE TABLE `user_account` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `identity`        varchar(32)     NOT NULL,
  `credential`      varchar(255)    NOT NULL default '',    # Credential hash
  `salt`            varchar(255)    NOT NULL default '',    # Hash salt
  `email`           varchar(64)     NOT NULL,
  `name`            varchar(255)    NOT NULL default '',
  `active`          tinyint(1)      NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `identity` (`identity`),
  UNIQUE KEY `email` (`email`)
# KEY `authenticate` (`identity`, `credential`)
);


# System root users
CREATE TABLE `user_root` (
  `identity`        varchar(32)     NOT NULL,
  `credential`      varchar(255)    NOT NULL default '',    # Credential hash
  `salt`            varchar(255)    NOT NULL default '',    # Hash salt
  `email`           varchar(64)     NOT NULL,
  `name`            varchar(64)     NOT NULL default '',

  PRIMARY KEY  (`identity`)
);

# User profile data entities
CREATE TABLE `user_profile` (
  `user`            int(10)         unsigned NOT NULL,

  PRIMARY KEY  (`user`)
);

# custom meta
CREATE TABLE `user_meta` (
  `id`              smallint(5)     unsigned    NOT NULL    auto_increment,
  `key`             varchar(64)     NOT NULL,
  `category`        varchar(64)     NOT NULL default '',
  `title`           varchar(255)    NOT NULL default '',
  `attribute`       varchar(255)    default NULL,           # profile column attribute
  `view`            varchar(255)    default NULL,           # callback function for view
  `edit`            tinytext        default NULL,           # callback options for edit
  `admin`           tinytext        default NULL,           # callback options for administration
  `search`          tinytext        default NULL,           # callback options for search
  `options`         tinytext        default NULL,           # value options
  `module`          varchar(64)     NOT NULL default '',
  `active`          tinyint(1)      NOT NULL default '0',
  `required`        tinyint(1)      NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY  `key` (`key`)
);


# Tables created upon module installation
CREATE TABLE `table` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `name`            varchar(64)     NOT NULL,
  `module`          varchar(64)     NOT NULL,
  `type`            enum('table', 'view')   NOT NULL default 'table',

  PRIMARY KEY  (`id`)
# UNIQUE KEY `name` (`name`)
);

# Monitoring callback
CREATE TABLE `monitor` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `module`          varchar(64)     NOT NULL default '',
  `callback`        varchar(64)     NOT NULL default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `module` (`module`)
);
