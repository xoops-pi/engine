function showHideHelp( butt )
{
    butt.className = ( butt.className == 'on' ) ? 'off': 'on';
    document.body.className = ( butt.className == 'on' ) ? 'show-help': '';
}

function xoopsExternalLinks()
{
    if (!document.getElementsByTagName) return;
    var anchors = document.getElementsByTagName("a");
    for (var i=0; i<anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.getAttribute("href") ) {
            // Check rel value with extra rels, like "external noflow". No test for performance yet
            $pattern = new RegExp("external", "i");
            if ($pattern.test(anchor.getAttribute("rel"))) {
                anchor.target = "_blank";
            }
        }
    }
}

function xoopsGetElementById(id)
{
    return $(id);
}

window.onload = xoopsExternalLinks;