# $Id$
# --------------------------------------------------------
#
# Table structure for table `avatar`
#

CREATE TABLE avatar (
  avatar_id mediumint(8) unsigned NOT NULL auto_increment,
  avatar_file varchar(30) NOT NULL default '',
  avatar_name varchar(100) NOT NULL default '',
  avatar_mimetype varchar(30) NOT NULL default '',
  avatar_created int(10) NOT NULL default '0',
  avatar_display tinyint(1) unsigned NOT NULL default '0',
  avatar_weight smallint(5) unsigned NOT NULL default '0',
  avatar_type char(1) NOT NULL default '',
  PRIMARY KEY  (avatar_id),
  KEY avatar_type (avatar_type, avatar_display)
);
# --------------------------------------------------------

#
# Table structure for table `avatar_user_link`
#

CREATE TABLE avatar_user_link (
  avatar_id mediumint(8) unsigned NOT NULL default '0',
  user_id mediumint(8) unsigned NOT NULL default '0',
  KEY avatar_user_id (avatar_id,user_id)
);
# --------------------------------------------------------

#
# Table structure for table `banner`
#

CREATE TABLE banner (
  bid smallint(5) unsigned NOT NULL auto_increment,
  cid tinyint(3) unsigned NOT NULL default '0',
  imptotal mediumint(8) unsigned NOT NULL default '0',
  impmade mediumint(8) unsigned NOT NULL default '0',
  clicks mediumint(8) unsigned NOT NULL default '0',
  imageurl varchar(255) NOT NULL default '',
  clickurl varchar(255) NOT NULL default '',
  date int(10) NOT NULL default '0',
  htmlbanner tinyint(1) NOT NULL default '0',
  htmlcode text,
  PRIMARY KEY  (bid),
  KEY idxbannercid (cid),
  KEY idxbannerbidcid (bid,cid)
);
# --------------------------------------------------------

#
# Table structure for table `bannerclient`
#

CREATE TABLE bannerclient (
  cid smallint(5) unsigned NOT NULL auto_increment,
  name varchar(60) NOT NULL default '',
  contact varchar(60) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  login varchar(10) NOT NULL default '',
  passwd varchar(10) NOT NULL default '',
  extrainfo text,
  PRIMARY KEY  (cid),
  KEY login (login)
);
# --------------------------------------------------------

#
# Table structure for table `bannerfinish`
#

CREATE TABLE bannerfinish (
  bid smallint(5) unsigned NOT NULL auto_increment,
  cid smallint(5) unsigned NOT NULL default '0',
  impressions mediumint(8) unsigned NOT NULL default '0',
  clicks mediumint(8) unsigned NOT NULL default '0',
  datestart int(10) unsigned NOT NULL default '0',
  dateend int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (bid),
  KEY cid (cid)
);
# --------------------------------------------------------

#
# Table structure for table `block_module_link`
#

CREATE TABLE block_module_link (
  block_id mediumint(8) unsigned NOT NULL default '0',
  module_id smallint(5) NOT NULL default '0',
  PRIMARY KEY (`module_id`, `block_id`)
);
# --------------------------------------------------------

#
# Table structure for table `comments`
#

CREATE TABLE xoopscomments (
  com_id mediumint(8) unsigned NOT NULL auto_increment,
  com_pid mediumint(8) unsigned NOT NULL default '0',
  com_rootid mediumint(8) unsigned NOT NULL default '0',
  com_modid smallint(5) unsigned NOT NULL default '0',
  com_itemid mediumint(8) unsigned NOT NULL default '0',
  com_icon varchar(25) NOT NULL default '',
  com_created int(10) unsigned NOT NULL default '0',
  com_modified int(10) unsigned NOT NULL default '0',
  com_uid mediumint(8) unsigned NOT NULL default '0',
  com_ip varchar(15) NOT NULL default '',
  com_title varchar(255) NOT NULL default '',
  com_text text,
  com_sig tinyint(1) unsigned NOT NULL default '0',
  com_status tinyint(1) unsigned NOT NULL default '0',
  com_exparams varchar(255) NOT NULL default '',
  dohtml tinyint(1) unsigned NOT NULL default '0',
  dosmiley tinyint(1) unsigned NOT NULL default '0',
  doxcode tinyint(1) unsigned NOT NULL default '0',
  doimage tinyint(1) unsigned NOT NULL default '0',
  dobr tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (com_id),
  KEY com_pid (com_pid),
  KEY com_itemid (com_itemid),
  KEY com_uid (com_uid),
  KEY com_title (com_title(40))
);
# --------------------------------------------------------

# RMV-NOTIFY
# Table structure for table `notifications`
#

CREATE TABLE xoopsnotifications (
  not_id mediumint(8) unsigned NOT NULL auto_increment,
  not_modid smallint(5) unsigned NOT NULL default '0',
  not_itemid mediumint(8) unsigned NOT NULL default '0',
  not_category varchar(30) NOT NULL default '',
  not_event varchar(30) NOT NULL default '',
  not_uid mediumint(8) unsigned NOT NULL default '0',
  not_mode tinyint(1) NOT NULL default 0,
  PRIMARY KEY (not_id),
  KEY not_modid (not_modid),
  KEY not_itemid (not_itemid),
  KEY not_class (not_category),
  KEY not_uid (not_uid),
  KEY not_event (not_event)
);
# --------------------------------------------------------

#
# Table structure for table `config`
#

CREATE TABLE configitem (
  `conf_id`         smallint(5) unsigned NOT NULL auto_increment,
  `conf_modid`      smallint(5) unsigned NOT NULL default '0',
  `conf_catid`      smallint(5) NOT NULL default '0',
  `conf_name`       varchar(25) NOT NULL default '',
  `conf_title`      varchar(255) NOT NULL default '',
  `conf_value`      text,
  `conf_desc`       varchar(255) NOT NULL default '',
  `conf_formtype`   varchar(15) NOT NULL default '',
  `conf_valuetype`  varchar(10) NOT NULL default '',
  `conf_order`      smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (conf_id),
  UNIQUE KEY `conf_module_key` (`conf_modid`, `conf_name`),
  KEY `conf_mod_cat_id` (`conf_modid`, `conf_catid`)
);
# --------------------------------------------------------

#
# Table structure for table `configcategory`
#

CREATE TABLE configcategory (
  confcat_id smallint(5) unsigned NOT NULL auto_increment,
  confcat_modid smallint(5) unsigned NOT NULL default '0',
  confcat_name varchar(255) NOT NULL default '',
  confcat_desc varchar(255) NOT NULL default '',
  confcat_key varchar(255) NOT NULL default '',
  confcat_order smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (confcat_id),
  UNIQUE KEY `cf_module_key` (`confcat_modid`, `confcat_key`)
);
# --------------------------------------------------------

#
# Table structure for table `configoption`
#

CREATE TABLE configoption (
  confop_id mediumint(8) unsigned NOT NULL auto_increment,
  confop_name varchar(255) NOT NULL default '',
  confop_value varchar(255) NOT NULL default '',
  conf_id smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (confop_id),
  KEY conf_id (conf_id)
);
# --------------------------------------------------------

#
# Table structure for table `groups`
#

CREATE TABLE groups (
  groupid smallint(5) unsigned NOT NULL auto_increment,
  name varchar(50) NOT NULL default '',
  description text,
  group_type varchar(10) NOT NULL default '',
  PRIMARY KEY  (groupid),
  KEY group_type (group_type)
);
# --------------------------------------------------------

#
# Table structure for table `group_permission`
#

CREATE TABLE group_permission (
  gperm_id int(10) unsigned NOT NULL auto_increment,
  gperm_groupid smallint(5) unsigned NOT NULL default '0',
  gperm_itemid mediumint(8) unsigned NOT NULL default '0',
  gperm_modid mediumint(5) unsigned NOT NULL default '0',
  gperm_name varchar(50) NOT NULL default '',
  PRIMARY KEY  (gperm_id),
  KEY groupid (gperm_groupid),
  KEY itemid (gperm_itemid),
  KEY gperm_modid (gperm_modid,gperm_name(10))
);
# --------------------------------------------------------


#
# Table structure for table `groups_users_link`
#

CREATE TABLE groups_users_link (
  linkid mediumint(8) unsigned NOT NULL auto_increment,
  groupid smallint(5) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (linkid),
  KEY groupid_uid (groupid,uid)
);
# --------------------------------------------------------

#
# Table structure for table `image`
#

CREATE TABLE image (
  image_id mediumint(8) unsigned NOT NULL auto_increment,
  image_name varchar(30) NOT NULL default '',
  image_nicename varchar(255) NOT NULL default '',
  image_mimetype varchar(30) NOT NULL default '',
  image_created int(10) unsigned NOT NULL default '0',
  image_display tinyint(1) unsigned NOT NULL default '0',
  image_weight smallint(5) unsigned NOT NULL default '0',
  imgcat_id smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (image_id),
  KEY imgcat_id (imgcat_id),
  KEY image_display (image_display)
);
# --------------------------------------------------------

#
# Table structure for table `imagebody`
#

CREATE TABLE imagebody (
  image_id mediumint(8) unsigned NOT NULL default '0',
  image_body mediumblob,
  KEY image_id (image_id)
);
# --------------------------------------------------------

#
# Table structure for table `imagecategory`
#

CREATE TABLE imagecategory (
  imgcat_id smallint(5) unsigned NOT NULL auto_increment,
  imgcat_name varchar(100) NOT NULL default '',
  imgcat_maxsize int(8) unsigned NOT NULL default '0',
  imgcat_maxwidth smallint(3) unsigned NOT NULL default '0',
  imgcat_maxheight smallint(3) unsigned NOT NULL default '0',
  imgcat_display tinyint(1) unsigned NOT NULL default '0',
  imgcat_weight smallint(3) unsigned NOT NULL default '0',
  imgcat_type char(1) NOT NULL default '',
  imgcat_storetype varchar(5) NOT NULL default '',
  PRIMARY KEY  (imgcat_id),
  KEY imgcat_display (imgcat_display)
);
# --------------------------------------------------------

#
# Table structure for table `modules`
#

CREATE TABLE modules (
  mid smallint(5) unsigned NOT NULL auto_increment,
  name varchar(150) NOT NULL default '',
  version varchar(64) NOT NULL default '',
  last_update int(10) unsigned NOT NULL default '0',
  weight smallint(3) unsigned NOT NULL default '0',
  isactive tinyint(1) unsigned NOT NULL default '0',
  dirname varchar(25) NOT NULL default '',
  hasmain tinyint(1) unsigned NOT NULL default '0',
  hasadmin tinyint(1) unsigned NOT NULL default '0',
  hassearch tinyint(1) unsigned NOT NULL default '0',
  hasconfig tinyint(1) unsigned NOT NULL default '0',
  hascomments tinyint(1) unsigned NOT NULL default '0',
  hasnotification tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (mid),
  UNIQUE KEY dirname (dirname),
  KEY hasmain (hasmain),
  KEY hasadmin (hasadmin),
  KEY hassearch (hassearch),
  KEY hasnotification (hasnotification),
  KEY name (name(15))
);
# --------------------------------------------------------

#
# Table structure for table `newblocks`
#

CREATE TABLE newblocks (
  bid mediumint(8) unsigned NOT NULL auto_increment,
  mid smallint(5) unsigned NOT NULL default '0',
  func_num tinyint(3) unsigned NOT NULL default '0',
  options varchar(255) NOT NULL default '',
  name varchar(150) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  content text,
  side tinyint(1) unsigned NOT NULL default '0',
  weight smallint(5) unsigned NOT NULL default '0',
  visible tinyint(1) unsigned NOT NULL default '0',
  block_type char(1) NOT NULL default '',
  c_type char(1) NOT NULL default '',
  isactive tinyint(1) unsigned NOT NULL default '0',
  dirname varchar(50) NOT NULL default '',
  func_file varchar(50) NOT NULL default '',
  show_func varchar(50) NOT NULL default '',
  edit_func varchar(50) NOT NULL default '',
  template varchar(50) NOT NULL default '',
  bcachetime int(10) unsigned NOT NULL default '0',
  last_modified int(10) unsigned NOT NULL default '0',
  cache varchar(64) NOT NULL default '',
  PRIMARY KEY  (bid),
  KEY mid (mid),
  KEY visible (visible),
  KEY isactive_visible_mid (isactive,visible,mid),
  KEY mid_funcnum (mid,func_num)
);
# --------------------------------------------------------

#
# Table structure for table `online`
#

CREATE TABLE online (
  online_uid mediumint(8) unsigned NOT NULL default '0',
  online_uname varchar(25) NOT NULL default '',
  online_updated int(10) unsigned NOT NULL default '0',
  online_module smallint(5) unsigned NOT NULL default '0',
  online_ip varchar(15) NOT NULL default '',
  KEY online_module (online_module)
);
# --------------------------------------------------------

#
# Table structure for table `priv_msgs`
#

CREATE TABLE priv_msgs (
  msg_id mediumint(8) unsigned NOT NULL auto_increment,
  msg_image varchar(100) default NULL,
  subject varchar(255) NOT NULL default '',
  from_userid mediumint(8) unsigned NOT NULL default '0',
  to_userid mediumint(8) unsigned NOT NULL default '0',
  msg_time int(10) unsigned NOT NULL default '0',
  msg_text text,
  read_msg tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (msg_id),
  KEY to_userid (to_userid),
  KEY touseridreadmsg (to_userid,read_msg),
  KEY msgidfromuserid (msg_id,from_userid)
);
# --------------------------------------------------------

#
# Table structure for table `ranks`
#

CREATE TABLE ranks (
  rank_id smallint(5) unsigned NOT NULL auto_increment,
  rank_title varchar(50) NOT NULL default '',
  rank_min mediumint(8) unsigned NOT NULL default '0',
  rank_max mediumint(8) unsigned NOT NULL default '0',
  rank_special tinyint(1) unsigned NOT NULL default '0',
  rank_image varchar(255) default NULL,
  PRIMARY KEY  (rank_id),
  KEY rank_min (rank_min),
  KEY rank_max (rank_max),
  KEY rankminrankmaxranspecial (rank_min,rank_max,rank_special),
  KEY rankspecial (rank_special)
);
# --------------------------------------------------------

#
# Table structure for table `session`
#

CREATE TABLE session (
  id varchar(32) NOT NULL default '',
  modified int(10) unsigned NOT NULL default '0',
  lifetime int(10) unsigned NOT NULL default '0',
  data text,
  PRIMARY KEY  (id),
  KEY modified (modified)
);
# --------------------------------------------------------

#
# Table structure for table `smiles`
#

CREATE TABLE smiles (
  id smallint(5) unsigned NOT NULL auto_increment,
  code varchar(50) NOT NULL default '',
  smile_url varchar(100) NOT NULL default '',
  emotion varchar(75) NOT NULL default '',
  display tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id)
);
# --------------------------------------------------------

# Table structure for table `users`
#

CREATE TABLE users (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  name varchar(60) NOT NULL default '',
  uname varchar(25) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  url varchar(100) NOT NULL default '',
  user_avatar varchar(30) NOT NULL default 'blank.gif',
  user_regdate int(10) unsigned NOT NULL default '0',
  user_icq varchar(15) NOT NULL default '',
  user_from varchar(100) NOT NULL default '',
  user_sig tinytext,
  user_viewemail tinyint(1) unsigned NOT NULL default '0',
  actkey varchar(8) NOT NULL default '',
  user_aim varchar(18) NOT NULL default '',
  user_yim varchar(25) NOT NULL default '',
  user_msnm varchar(100) NOT NULL default '',
  pass varchar(32) NOT NULL default '',
  posts mediumint(8) unsigned NOT NULL default '0',
  attachsig tinyint(1) unsigned NOT NULL default '0',
  rank smallint(5) unsigned NOT NULL default '0',
  level tinyint(3) unsigned NOT NULL default '1',
  theme varchar(100) NOT NULL default '',
  timezone_offset float(3,1) NOT NULL default '0.0',
  last_login int(10) unsigned NOT NULL default '0',
  umode varchar(10) NOT NULL default '',
  uorder tinyint(1) unsigned NOT NULL default '0',
  notify_method tinyint(1) NOT NULL default '1',
  notify_mode tinyint(1) NOT NULL default '0',
  user_occ varchar(100) NOT NULL default '',
  bio tinytext,
  user_intrest varchar(150) NOT NULL default '',
  user_mailok tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (uid),
  KEY uname (uname),
  KEY email (email),
  KEY uiduname (uid,uname),
  KEY unamepass (uname,pass)
);
# --------------------------------------------------------

#
# Table structure for table `cache_model`
#

CREATE TABLE cache_model (
  `cache_key`     varchar(64)     NOT NULL default '',
  `cache_expires` int(10)         unsigned NOT NULL default '0',
  `cache_data`    text,

  PRIMARY KEY  (`cache_key`),
  KEY `cache_expires` (`cache_expires`)
);

# Should be merged into `page`?
CREATE TABLE `cache` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `expire`          int(10)         NOT NULL    default '0',
  `level`           varchar(64)     NOT NULL    default '',
# `key`             varchar(64)     NOT NULL    default '',
  `title`           varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `custom`          tinyint(1)      unsigned    NOT NULL default '0',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `mca` (`module`, `controller`, `action`)
# UNIQUE KEY `module_key` (`module`, `key`)
);

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
  `id`              mediumint(8)    unsigned    NOT NULL auto_increment,
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
  `id`              mediumint(8)    unsigned    NOT NULL auto_increment,
  `page`            mediumint(8)    unsigned    NOT NULL    default '0',
  `block`           mediumint(8)    unsigned    NOT NULL    default '0',
  `position`        smallint(5)     unsigned    NOT NULL    default '0', #potential value: 0 - left, 1 - right, 2 - topleft, 3 - topcenter, 4 - topright, 5 - bottomleft, 6 - bottomcenter, 7 - bottomright
  `order`           mediumint(8)    NOT NULL    default '5',    # positive: display orer; negative: id of global page-block link that will be disabled on a specific page

  PRIMARY KEY  (`id`),
  KEY `page_block` (`page`, `order`, `block`)    # not necessarily UNIQUE
);

CREATE TABLE block (
  `id`              mediumint(8)    unsigned NOT NULL auto_increment,
  `key`             varchar(64)     NOT NULL default '',            # internal key
  `name`            varchar(64)     NOT NULL default '',            # user key, empty or unique string, for calling from template
  `title`           varchar(255)    NOT NULL default '',
  `description`     text,                                           # Description
  `type`            varchar(64)     NOT NULL default '',            # "" - generated; H - HTML style; P - PHP enabled; S - bbcode with smiley; T - bbcode without smiley
  `options`         text,                                           # for generated, delimited by "|"
  `active`          tinyint(1)      unsigned NOT NULL default '1',  # for generated, updated by system on module activation
  `module`          varchar(64)     NOT NULL default '',            # for generated
  `func_file`       varchar(64)     NOT NULL default '',            # for generated
  `show_func`       varchar(64)     NOT NULL default '',            # for generated
  `edit_func`       varchar(64)     NOT NULL default '',            # for generated
  `template`        varchar(64)     NOT NULL default '',            # for generated
  `content`         text,                                           # for custom
  `cache_expire`    int(10)         unsigned NOT NULL default '0',
  `cache_level`     varchar(64)     NOT NULL default '',
  PRIMARY KEY  (`id`)
# UNIQUE KEY `module_key` (`module`, `key`)
# KEY mid (mid),
# KEY visible (visible),
# KEY isactive_visible_mid (isactive,visible,mid),
# KEY mid_funcnum (mid,func_num)
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
  `extra`           varchar(255)    NOT NULL    default '',
  `time`            int(10)         unsigned NOT NULL   default '0',
  PRIMARY KEY  (`id`)
) type = ARCHIVE;


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

CREATE TABLE module (
  `id`          smallint(5)     unsigned NOT NULL auto_increment,
  `name`        varchar(64)     NOT NULL default '',
  `version`     varchar(64)     NOT NULL default '',
  `update`      int(10)         unsigned NOT NULL default '0',
  `active`      tinyint(1)      unsigned NOT NULL default '1',
  `dirname`     varchar(64)     NOT NULL default '',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `dirname` (`dirname`),
  KEY           `name` (`name`(15))
);

CREATE TABLE theme (
  `id`          smallint(5)     unsigned NOT NULL auto_increment,
  `name`        varchar(64)     NOT NULL default '',
  `version`     varchar(64)     NOT NULL default '',
  `author`      varchar(255)    NOT NULL default '',
  `license`     varchar(255)    NOT NULL default '',
  `screenshot`  varchar(255)    NOT NULL default '',
  `update`      int(10)         unsigned NOT NULL default '0',
  `active`      tinyint(1)      unsigned NOT NULL default '1',
  `dirname`     varchar(64)     NOT NULL default '',
  `parent`      varchar(64)     NOT NULL default '',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `dirname` (`dirname`)
);


CREATE TABLE search (
  `id`          smallint(5)     unsigned NOT NULL auto_increment,
  `module`      varchar(64)     NOT NULL default '',
  `callback`    varchar(64)     NOT NULL default '',
  `func`        varchar(64)     NOT NULL default '',
  `file`        varchar(255)    NOT NULL default '',
  `active`      tinyint(1)      unsigned NOT NULL default '1',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `module` (`module`)
);


CREATE TABLE `route` (
  `id`              mediumint(8)    unsigned    NOT NULL auto_increment,
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
CREATE TABLE config (
  `id`              smallint(5)     unsigned NOT NULL auto_increment,
  `module`          varchar(64)     NOT NULL    default '',
  `category`        varchar(64)     NOT NULL    default '',
  `name`            varchar(64)     NOT NULL    default '',
  `title`           varchar(255)    NOT NULL default '',
  `value`           text,
  `description`     varchar(255)    NOT NULL default '',
# `formtype`        varchar(64)     NOT NULL default '',
# `valuetype`       varchar(64)     NOT NULL default '',
  `edit`            tinytext        default NULL,           # callback options for edit
  `filter`          varchar(64)     NOT NULL default '',
  `order`           smallint(5)     unsigned NOT NULL default '0',

  PRIMARY KEY   (`id`),
  UNIQUE KEY    `module_key`   (`module`, `name`),
  KEY `module_category`  (`module`, `category`)
);

# System config category
CREATE TABLE config_category (
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
CREATE TABLE config_option (
  `id`              mediumint(8)    unsigned NOT NULL auto_increment,
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
  `name`            varchar(64)     NOT NULL default '',
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

  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
);