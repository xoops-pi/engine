;<?php __halt_compiler();

[servers]
; Single server
host = 127.0.0.1
port = 11211
weight = 1


; Multiple servers
;0.host = 127.0.0.1
;0.port = 11211
;0.weight = 10

;a.host = memcache.localhost
;a.port = 11211
;a.weight = 1

[client]
compression = TRUE
prefix_key = ""
serialize = SERIALIZER_PHP
hash = HASH_MD5