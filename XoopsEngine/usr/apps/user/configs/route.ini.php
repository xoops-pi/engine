;<?php __halt_compiler();

; $Id$

; module application routes

; User profile route
user.section = "application"
user.priority = -10
user.route = "u/:user"
user.defaults.module = "user"
user.defaults.controller = "index"
user.defaults.action = "index"
user.defaults.user = ""

; Named user profile route
name.section = "application"
name.priority = -10
name.route = "name/:user/:name"
name.defaults.module = "user"
name.defaults.controller = "index"
name.defaults.action = "index"
name.defaults.user = ""
name.defaults.name = ""

; Self profile route
profile.section = "application"
logout.type = "Zend_Controller_Router_Route_Static"
profile.priority = -10
profile.route = "space"
profile.defaults.module = "user"
profile.defaults.controller = "profile"
profile.defaults.action = "index"

; User login route
login.section = "application"
login.priority = 50
;login.type = "Zend_Controller_Router_Route_Static"
login.route = "login/:redirect"
login.defaults.module = "user"
login.defaults.controller = "login"
login.defaults.action = "login"
login.defaults.redirect = ""

; User logout route
logout.section = "application"
logout.type = "Zend_Controller_Router_Route_Static"
logout.route = "logout"
logout.defaults.module = "user"
logout.defaults.controller = "login"
logout.defaults.action = "logout"

; User register route
register.section = "application"
register.priority = 100
;register.type = "Zend_Controller_Router_Route_Static"
register.route = "register/:redirect"
register.defaults.module = "user"
register.defaults.controller = "register"
register.defaults.action = "index"
register.defaults.redirect = ""
