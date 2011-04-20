;<?php __halt_compiler();

;; Accept defaults for production
; bug_compat_42
; bug_compat_warn
; cache_expire = 30
; cache_limiter
; cookie_domain
; cookie_lifetime
; cookie_path
; cookie_secure
; entropy_file
; entropy_length
; gc_divisor
; gc_maxlifetime
; gc_probability
; hash_bits_per_character
; hash_function
; name should be unique for each PHP application sharing the same domain name
name = XOOPSSESSION
; referer_check
; save_handler
; save_path
; serialize_handler
; use_cookies
; use_only_cookies
; use_trans_sid

; remember_me_seconds = <integer seconds>
; strict = on|off

;savehandler.class = "Xoops_Zend_Session_SaveHandler_Legacy"
savehandler = "memcache"
