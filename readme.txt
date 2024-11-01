=== ZigTweets ===
Tags: zigtweets, zig, zigpress, recent tweets, twitter widget, twitter api v1.1, cache, twitter, tweets, social media, classicpress
Requires at least: 4.5
Tested up to: 5.4
Requires PHP: 5.3
Stable tag: 1.0

Plugin to fetch tweets from an account and display them in a widget

== Description ==

**Due to abuse received from plugin repository users we are ceasing development of free WordPress plugins and this is the last release of this plugin. It will be removed from the repository in due course. Our pro-bono plugin development will now be exclusively for the ClassicPress platform.**

Plugin to fetch tweets from an account and display them in a widget. It uses the Twitter API v1.1 and stores tweets in the cache. It means that it will read status messages from your database and it doesn't query Twitter.com for every page load so you won't be rate limited. You can set how often you want to update the cache.

Compatible with ClassicPress.

== Installation ==

1. Unzip the downloaded zip file.
2. Upload the `zigtweets` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate ZigTweets from Plugins page.
4. Go to your Widgets menu, add ZigTweets` widget to a widget area.
5. Visit [https://apps.twitter.com/](https://apps.twitter.com/ "Twitter") in a new tab, sign in with your account, click on `Create a new application` and create your own keys if you haven't already.
6. Fill all your widget settings.
7. Enjoy your new Twitter feed! :)

== Frequently Asked Questions ==

= How can I get Consumer Key, Consumer Secret, Access Token and Access Token Secret?  =

You will need to visit [https://apps.twitter.com/](https://apps.twitter.com/ "Twitter"), sign in with your account and create your own keys.

== Changelog ==

= 1.0 =
* Notice of cessation of free WordPress plugin development
= 0.4.3 =
* Tiny fix in widget form code to eliminate PHP warning
= 0.4.2 =
* Verified compatibility with WordPress 5.3
* Verified compatibility with ClassicPress 1.1.x
= 0.4.1 =
* Verified compatibility with WordPress 5.2
* Verified compatibility with ClassicPress 1.0.x
= 0.4 =
* Verified compatibility with WordPress 4.9.8
* Verified compatibility with ClassicPress 1.0.0-beta1
* Increased minimum WordPress version to 4.5
= 0.3.4 =
* Confirmed compatibility with WordPress 4.9
= 0.3.3 =
* Removed some now-redundant @ warning suppressors which were causing problems on some servers
= 0.3.2 =
* Confirmed compatibility with WordPress 4.7.2
* Removed die statement to allow graceful failure when the connection to Twitter fails for some reason
* Updated description
= 0.3.1 =
* Confirmed compatibility with WordPress 4.7
= 0.3 =
* Added optional follow link after list of tweets
* Confirmed compatibility with WordPress 4.6.1
= 0.2.1 =
* Confirmed compatibility with WordPress 4.4
= 0.2 =
* Complete object-oriented rewrite
* Demoted admin page from top level to settings section
* Improved folder structure of plugin
* Proper uninstall file to remove options when plugin removed (not when deactivated)
* Updated admin page content
* Restructured storage in options table
* Set minimum PHP version to 5.3 (ZigPress policy) with graceful self-deactivation on older versions
= 0.1 =
* First forked version of Recent Tweets Widget
* Made it WordPress 4.3 compatible
* Removed SumoMe marketing content
