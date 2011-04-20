;<?php __halt_compiler();

; system application routes

; Legacy route
legacy.section = "legacy"
legacy.priority = -100
legacy.type = "xoops_Zend_Controller_Router_Route_Legacy"
legacy.route = "modules/:module/*"
legacy.defaults.module = "default"
legacy.defaults.controller = "legacy"
legacy.defaults.action = "index"

; Admin route
admin.section = "admin"
admin.priority = -100
legacy.type = "xoops_Zend_Controller_Router_Route_Admin"
admin.route = "admin/*"
admin.defaults.module = "system"
admin.defaults.controller = "index"
admin.defaults.action = "index"

; Feed route
feed.section = "feed"
feed.priority = -100
legacy.type = "xoops_Zend_Controller_Router_Route_Feed"
feed.route = "feed/*"
feed.defaults.module = "default"
feed.defaults.controller = "feed"
feed.defaults.action = "index"

; Application route
default.section = "application"
default.route = "*"
default.defaults.module = "default"
default.defaults.controller = "index"
default.defaults.action = "index"