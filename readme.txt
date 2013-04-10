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

`[awesome-weather location="Montreal" units="F" size="tall" override_title="MTL" forecast_days=2 hide_stats=true]`

=Settings=
* Location: Enter like Montreal, CA or just Montreal. You may need to try different variations to get the right city
* Units: F (default) or C
* Size: wide (default) or tall
* Override Title: Change the title in the header bar to whatever, sometimes it pulls weather from a close city
* Forecast Days: How many days to show in the forecast bar
* Hide stats: Hide the text stats like humidity, wind, high and lows, etc

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

== Changelog ==

= 1.0 =
* Initial load of the plugin.