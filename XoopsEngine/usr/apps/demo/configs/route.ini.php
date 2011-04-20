;<?php __halt_compiler();

; $Id$

; module application routes

; Search route
demo.section = "application"
demo.priority = 10
demo.type = "Zend_Controller_Router_Route_Hostname"
demo.route = "api.localhost"
demo.defaults.module = "demo"
demo.chains.index.route = ":module/:action/*"
demo.chains.index.defaults.controller = "api"
demo.chains.index.defaults.action = "index"
