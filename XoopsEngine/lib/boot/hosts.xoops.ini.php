;<?php __halt_compiler();

;Hosts definition file
;Paths/URLs to system folders
;URIs without a leading slash are considered relative to the current XOOPS host location
;URIs with a leading slash are considered semi-relative (you must setup proper rewriting rules in your server conf)



;Host location
[location]
baseLocation = "http://localhost"
baseUrl = "/XoopsEngine/htdocs"

[paths]

;Document root
www[] = "E:/wamp/www/XoopsEngine/htdocs"
www[] = ""

;VAR or intermediate data directory, w/o URI access
var[] = "E:/wamp/www/XoopsEngine/var"
var[] = "browse.php?var"

;Library directory
lib[] = "E:/wamp/www/XoopsEngine/lib"
lib[] = "browse.php?lib"

;User extension directory
usr[] = "E:/wamp/www/XoopsEngine/usr"
usr[] = "browse.php?usr"

;Application module directory
app[] = "E:/wamp/www/XoopsEngine/usr/apps"
app[] = "browse.php?app"

;Plugin directory
plugin[] = "E:/wamp/www/XoopsEngine/usr/plugins"
plugin[] = "browse.php?plugin"

;Applet directory
applet[] = "E:/wamp/www/XoopsEngine/usr/applets"
applet[] = "browse.php?applet"

;Static file directory with independent URI
img[] = "E:/wamp/www/XoopsEngine/htdocs/img"
img[] = "http://localhost/XoopsEngine/htdocs/img"

;Legacy module directory
module[] = "E:/wamp/www/XoopsEngine/htdocs/modules"
module[] = "modules"

;Theme directory
theme[] = "E:/wamp/www/XoopsEngine/htdocs/themes"
theme[] = "themes"

;Upload directory with URI access
upload[] = "E:/wamp/www/XoopsEngine/htdocs/uploads"
upload[] = "uploads"
