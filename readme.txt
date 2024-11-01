
=== Google Speed Optimization by dTevik ===
Contributors: dTevik
Tags: Google, Google Speed, Google Speed Optimization, PageSpeed Insights, minify, compress JS, compress CSS, compress HTML, SEO Optimization, compress site, speed site
Requires at least: 4.0
Tested up to: 5.2.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Plugin Google Speed Optimization is minimizes the HTML output of the site, combines JS and CS files into one, and also minimizes JS and CSS. 

It compresses the HTML output, removing unnecessary spaces, reduces the size of the output of your site, which significantly increases the speed of your site, since the output will be less content. 

Also, the plugin can cut out single JS codes from content, minimizes them and adds them before the closing BODY tag after the optimized main JS file (so as not to break anything) 

This is the best way to compress the HTML output of your site and improve performance in Google Speed ​​Test (PageSpeed ​​Insights)!

The module uses the Minify library, settings and library description by the link https://github.com/matthiasmullie/minify

Example

Testing with the working plugin https://anira-web.ru/

Testing without minimization https://anira-web.ru/?tevik_no_minimized=true

Features of this plug-in
- Minimizes JS, CSS, HTML

Important

Plugin may conflict with server based cache services like as Nginx and Varnish,
If you have any problems with server environment, please write me on email: info@anira-web.ru, i'll try to help you.

= Installation =

1. In your WordPress Dashboard go to "Plugins" -> "Add Plugin".
2. Search for "Speed Site Optimization by dTevik".
3. Install the plugin by pressing the "Install" button.
4. Activate the plugin by pressing the "Activate" button.


= Updating =
* Use WordPress automatic updates to upgrade to the latest version. Ensure to backup your site just in case.

= Minimum Requirements =
* WordPress version 4.0 or greater.
* PHP version 5.4 or greater.


= Recommended  Requirements =
* Latest WordPress version.
* PHP version 5.6 or greater.


== Frequently Asked Questions ==

= I installed the plugin, but styles disappeared on the site! =

1. Check that the /cache/minified folder is present in the wp-content folder.
2. Check that the css-minified.css and js-minified.js files are in the /wp-content/cache/minified folder

= Why do I have some styles and scripts do not work after installation? =
This plugin compresses all styles and scripts into 1 file. If in some file before the compression there were errors, then you need to find this file and fix it. To do this, open the browser console and fix the errors. 

= Do you provide plugin customization services for my site? =

Yes! You can order a paid plugin setting for your site. The cost is negotiated for each site separately. If you need assistance in setting up the plugin, please write to e-mail: info@anira-web.ru

== Screenshots ==
1. This image show as plugin compress HTML code
2. This image show as plugin compress CSS code
2. This image show as plugin compress JS code

== Changelog ==

= 1.00 (2019-04-30) =
* Initial release