;<?php __halt_compiler();

; Security options

; IP check: deny 'bad' IPs, approve 'good' IPs
;ip.bad = "^127.0|^10.0"
;ip.good = "^127.0|^10.0"
ip.checkProxy = true

; Super GLOBALS
globals = "GLOBALS, _SESSION, _GET, _POST, _COOKIE, _REQUEST, _SERVER, _ENV, _FILES, xoopsDB, xoopsUser, xoopsUserIsAdmin, xoopsOption"

; XSS check
xss.post = true
xss.get = true
xss.filter = 1
xss.length = 32

; DoS
;dos = 1

; bots
;bot = 'bad bot|evil bot'

; If you wanna check both DoS and bots, use this one instead
; HTTP_USER_AGENT
agent.dos = 1
agent.bot = 'bad bot|evil bot'