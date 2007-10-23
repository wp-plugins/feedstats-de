=== FeedStats ===
Contributors: Bueltge
Donate link: http://bueltge.de/wunschliste/
Tags: feed, statistics, stats, rss
Requires at least: 1.5
Tested up to: 2.3
Stable tag: 3.3

Simple statistictool for feeds.

== Description ==
Simple statistictool for feeds, by [Andres Nieto Porras](http://www.anieto2k.com "Andres Nieto Porras") and [Frank Bueltge](http://bueltge.de "Frank Bueltge").

_You can customize in options:_

* Amount of days that is supposed to be saved in the statistics.
* Minimum level of WordPress-user, who is allowed to see the statistics.
* Time of a stay/visit (1hour values 3600seconds is common but might be changed)
* Visitors onlinetime (5minutes value 300s is a recommendation)
* IP, that is supposed not to be saved, ex.: your own IP
* Statistics can be shown on the dashboard

The Plugin is in english language and have the german and traditional chinese translation in language-file. For traditional chinese translation give it a another icon for feedreaders in an button.

Please visit [the official website](http://bueltge.de/wp-feedstats-de-plugin/171/ "FeedStats") for further details and the latest information on this plugin.

== Installation ==
1. Unpack the download-package
1. Upload all files to the `/wp-content/plugins/` directory, without folder
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Got to 'Options' menu and configure the plugin

See on [the official website](http://bueltge.de/wp-feedstats-de-plugin/171/ "FeedStats").

== Screenshots ==
1. Statistic-area (Example) for 10 Days
1. configure-area

== Other Notes ==
Use the follow function for write the statistic on your blog.

`<php fs_getfeeds(); ?>`

_Example:_

`<?php if (function_exists('fs_getfeeds')) : fs_getfeeds(); endif; ?>`

You can also use an Button with your average feedreaders. Use function `fs_getfeeds_button()` and format with CSS.

_Example:_

`<div id="feeds_button"><?php fs_getfeeds_button(); ?></div>`

`#feeds_button {
	width: 74px;
	height: 14px;
	text-align: left;
	font-size: 10px;
	padding: 1px 15px 15px 3px;
	color: #fff;
	background: url(wp-content/plugins/wp-feedstats/wp-feedstats.gif) no-repeat;
	margin-bottom: 2px;
}`

for style-css in traditional Chinese (zh_TW) translation:

`#feeds_button {
	width: 74px;
	height: 14px;
	text-align: left;
	font-size: 10px;
	padding: 1px 15px 15px 3px;
	color: #fff;
	background: url(wp-content/plugins/wp-feedstats/wp-feedstats-zh_TW.gif) no-repeat;
	margin-bottom: 2px;
}`

= Acknowledgements =
FeedReaderButton (gif) by [Christoph Schr&ouml;der](http://www.nasendackel.de "Christoph Schr&ouml;der")

FeedReaderButton (gif - traditional Chinese (zh_TW)) and traditional Chinese translation by [Neil Lin](http://www.wmfield.idv.tw/485 "Neil Lin")

Turkish translation by [Baris Unver](http://beyn.org "Baris Unver")

French translation by [burningHat](http://blog.burninghat.net/ "burningHat")

== Frequently Asked Questions ==

= Where can I get more information? =

Please visit [the official website](http://bueltge.de/wp-feedstats-de-plugin/171/ "FeedStats") for the latest information on this plugin.

= I love this plugin! How can I show the developer how much I appreciate his work? =

Please visit [the official website](http://bueltge.de/wp-feedstats-de-plugin/171/ "FeedStats") and let him know your care or see the [wishlist](http://bueltge.de/wunschliste/ "Wishlist") of the author.