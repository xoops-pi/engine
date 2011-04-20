;<?php __halt_compiler();

; $Id$

; module application routes

; Search route
search.section = "application"
search.priority = 10
search.route = "search/:q"
search.defaults.module = "search"
search.defaults.controller = "index"
search.defaults.action = "index"
search.defaults.q = ""
