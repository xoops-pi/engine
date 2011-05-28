<?php
// $Id$
// _LANGCODE: en
// _CHARSET : UTF-8
// Translator: XOOPS Translation Team

$message = <<<'EOD'
<h2>Congratulatons, everything is done!</h2>
<p>You can now visit <a href='../index.php'>your site</a>.</p>
<h3>Security configuration</h3>
<ol>For security considerations you are strongly recommended to complete the following actions:
    <li>Remove the installation folder <strong>%s</strong> from your server.</li>
    <li>Set configuration directories and files to readonly: %s</li>
</ol>
<h3>Support</h3>
<p>Visit <a href='http://www.xoopsengine.org/' rel='external'>Xoops Engine Development Site</a> in case you need any help.</p>
EOD;
define("_INSTALL_FINISH_MESSAGE", $message);