=== Plugin Name ===
Contributors: halgatewood
Donate link: http://halgatewood.com/awesome-weather/
Tags: widgets, sidebar, shortcode, openweathermap, weather, weather widget, forecast, global
Requires at least: 3.5
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Finally beautiful weather widgets for your site.

== Description ==

This plugin allows you to easily add super clean weather widgets to your site. The design is based off the site: http://weatherrr.net/ and the widget changes colors based on the current temp.

Use the built in widget or add it somewhere else with this shortcode: (all settings shown)

`[awesome-weather location="Montreal" units="F" size="tall" override_title="MTL" forecast_days=2 hide_stats=true background=http://urltoanimage.jpg]`

Settings:

*   Location: Enter like Montreal, CA or just Montreal. You may need to try different variations to get the right city
*   Units: F (default) or C
*   Size: wide (default) or tall
*   Override Title: Change the title in the header bar to whatever, sometimes it pulls weather from a close city
*   Forecast Days: How many days to show in the forecast bar
*   Hide stats: Hide the text stats like humidity, wind, high and lows, etc
*   Background: URL to an image that will be used as the background of the entire widget

All weather data is provided by http://openweathermap.org and is cached for one hour.


== Installation ==

1. Add plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use shortcode or widget


== Screenshots ==

1. Basic wide layout
2. Basic tall layout
3. Micro no features
4. Widget Settings
5. Background Image Option (1.2)

== Upgrade Notice ==

= 1.3 =
Upgraded to most recent OpenWeatherMap APIS and included translation files.

= 1.2.6 =
Improved error handling with API calls

= 1.2.5 =
Widget with custom background CSS issue.

= 1.2.4 =
Forecast now stops showing today.

= 1.2.3 =
Better API Support

= 1.2 =
Background Image Option

= 1.1.1 =
Google Font URL changed or I messed it up, probably me.

= 1.1 =
Errors are now commented out. Look in the source to see what the problem is.

= 1.0.2 =
Removed debugging code, sorry!

= 1.0.1 =
Changed API endpoints. Might not find weather without update.

== Changelog ==

= 1.3 =
* Upgraded to most recent OpenWeatherMap APIS
* Ready for translations
* Current Locales available from OpenWeatherMap: 
* English - en, Russian - ru, Italian - it, Spanish - sp, Ukrainian - ua, German - de, Portuguese - pt, Romanian - ro, Polish - pl, Finnish - fi, Dutch - nl, French - fr, Bulgarian - bg, Swedish - se, Chinese Traditional - zh_tw, Chinese Simplified - zh_cn, Turkish - tr 

= 1.2.6 =
* Improved error handling with API calls

= 1.2.5 =
* Widget with custom background CSS issue.

= 1.2.4 =
* Forecast now stops showing today.

= 1.2.3 =
* remove file_get_contents and used wp_remote_get

= 1.2.1 =
* Background Image Option in widget

= 1.2 =
* Background Image Option

= 1.1 =
* Errors are now commented out. Look in the source to see what the problem is.
* Ability to add link to openweathermap for extended forecast

= 1.0.2 =
* Removed debugging code, sorry!

= 1.0.1 =
* Changed API endpoints

= 1.0 =
* Initial load of the plugin.