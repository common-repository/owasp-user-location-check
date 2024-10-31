
=== OWASP User Location Check ===

Contributors:  Off-Site Services Inc.
Tags: OWASP security, OWASP location check, OWASP login validation, security, wordpress security, user country check, user location check, change user location
Requires at least: 3.0.1
Tested up to: 5.7
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sends warning notification to admin email if someone logs in to user's account from another country within setup hours of last login session.

== Description ==

How does it work?
Every time when user attempts to log in to WordPress CMS, OWASP security plugin receives and stores information about the user's country. That information comes from ipinfo.io, an external service which provides available information on user's IP address and does not in any way compromise WordPress security. Once the country of attempted login is identified, the plugin compares current locations with that of the previous successful CMS login within the last 2 hours. If country is different, the plugin flags it as unauthorized login attempt and sends notification to OWASP manager, with recommendation to change CMS password. Email address for OWASP manager is identified in plugin settings as "Notification email".

== Installation ==

1. Upload entire `/owasp-user-location-check` folder to `/wp-content/plugins/` directory
2. Activate plugin through *Plugins* menu in WordPress

== Frequently Asked Questions ==
 
== Screenshots ==
 
== Changelog ==

= 1.1 =
* Tested with WP 5.7
* Description updated

= 1.0 =
* Tested with WP 5.1.1
* Added ability to add IP to white and black lists
* Added ability to setup E-mail where notices will be send

= 0.4 =
* Tested with WP 5.1

= 0.3 =
* Tested with WP 5.0.3

== Upgrade Notice ==