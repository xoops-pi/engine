<?php
// $Id$
// _LANGCODE: en
// _CHARSET : UTF-8
// Translator: XOOPS Translation Team

$message = <<<'EOD'
<h3>Your site</h3>
<p>You can now access the <a href='../index.php'>home page of your site</a>.</p>
<h3>Support</h3>
<p>Visit <a href='http://www.xoopsengine.org/' rel='external'>Xoops Engine Development</a></p>
<h3>Security configuration</h3>
<p>For security considerations you are strongly recommended to complete the following actions:
<ul>
    <li>Set configuration directories and files to readonly: %s.</li>
    <li>Remove the installation folder <strong>install</strong> from your server.</li>
</ul>
</p>
EOD;
define("_INSTALL_FINISH_MESSAGE", $message);