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
<p>The installer will try to configure your site for security considerations. Please double check to make sure:
<ul>
    <li>The configuration files are set to readonly: %s.</li>
    <li>Installation folder <strong>install</strong> is removed.</li>
</ul>
</p>
EOD;
define("_INSTALL_FINISH_MESSAGE", $message);