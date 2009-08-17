<!-- 
**** The following Readme is in Wiki format. Just copy/paste into a 
Wiki page to Read and as a reference for users. 
-->
Allows wiki author to choose to open existing media, images or a "://" type link into a new external window. An http reference must be in the correct fully named web format ''(http://www.google.com)'' or you'll get a 'bad reference' error. You'll get the same error if a local media file match isn't found as well.


This extension should be able to open any type of documents that can normally be opened with the installed web browser. I've personally tested Powerpoint, Word and Excel for Office 2003. It also works will with Adobe PDF files. 

''Note:'' With Powerpoint, I've noticed in my testing, that the Powerpoint process is still running even after you close the browser window... not sure if this is a standard issue with running Powerpoint in a browser window or if its my WinXP configuration.


Please report '''bugs/comments''' here: [http://code.google.com/p/mw-launchexternal/issues/entry Issue Tracker] ''(requires a free google account)''

== Syntax Examples ==
<pre>
<ext>Holiday_calendar.doc</ext>
<ext>Holiday_calendar.doc ::Paid Holidays</ext>
<ext>http://www.google.com</ext>

<ext>file://\\sharedserver\Holiday_calendar.doc</ext>
<ext>file://sharedserver/Holiday_calendar.doc</ext>
<ext>file:\\sharedserver\Holiday_calendar.doc</ext>

<ext>www.cnn.com</ext> <-- bad reference/missing "http://"
</pre>

== Installation ==
<source lang="php">
require_once("$IP/extensions/LaunchExternal/launchExternal.php");
</source>

To automatically attach a servername description to the link...
 '''$wgAddServerName_EXT = true;'''
 <ext>file:\\sharedserver\Holiday_calendar.doc</ext>
 Holiday_calendar.doc ''(sharedserver)''

== Recommended Script for "outside links only..." ==
This scipt, if added into either the 'Mediawiki:Common.js' or 'User:Common.js' forces all external links to open in a new window. The extension above opens uploaded media/images into a new window (this script doesn't). For http type links, you can either use this script or the above extension.

<pre>
externalLinks = function() {
        if (!document.getElementsByTagName) {
                return;
        }
        var anchors = document.getElementsByTagName("a");
        for (var i = 0; i < anchors.length; i++) {
                var anchor = anchors[i];
                if (anchor.getAttribute("href") && 
                                anchor.getAttribute("rel") != null && 
                                (anchor.getAttribute("rel").indexOf("external") >= 0 ||
                                        anchor.getAttribute("rel").indexOf("nofollow") >= 0)
                        ) {
                        anchor.target = "_blank";
                }
        }
}
if (window.addEventListener) {
        window.addEventListener("load", externalLinks, false);
}
else if (window.attachEvent) {
        window.attachEvent("onload", externalLinks);
}</pre>
