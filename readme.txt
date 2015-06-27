=== Cookie Notice by dFactory===
Contributors: dfactory
Donate link: http://www.dfactory.eu/
Tags: cookie, cookies, notice, notification, notify, cookie, cookie compliance, cookie law, eu cookie, privacy, privacy directive, consent, Bootstrap
Requires at least: 3.3
Tested up to: 4.2.2
Stable tag: 1.2.28
License: MIT License
License URI: http://opensource.org/licenses/MIT

Cookie Notice allows you to elegantly inform users that your site uses cookies and to comply with the EU cookie law regulations.

== Description ==

[Cookie Notice](http://www.dfactory.eu/plugins/cookie-notice/) allows you to elegantly inform users that your site uses cookies and to comply with the EU cookie law regulations.

For more information, check out plugin page at [dFactory](http://www.dfactory.eu/) or plugin [support forum](http://www.dfactory.eu/support/forum/cookie-notice/).

= Features include: =

* Customize the cookie message
* Redirect users to specified page for more cookie information
* Set cookie expiry
* Link to more info page
* Option to accept cookies on scroll
* Option to set on scroll offset
* Option to refuse functional cookies
* Select the position of the cookie message box
* Animate the message box after cookie is accepted
* Select bottons style from None, WordPress and Bootstrap
* Set the text and bar background colors
* WPML and Polylang compatible
* .pot file for translations included

= Usage: =

If you'd like to code a functionality depending on the cookie notice value use the function below:

`if ( function_exists('cn_cookies_accepted') && cn_cookies_accepted() ) {
	// Your third-party non functional code here
}`

= Get involved =

Feel free to contribute to the source code on the [dFactory GitHub Repository](https://github.com/dfactoryplugins).

= Translations: =

* Croatian - by [Marko Beus](http://www.markobeus.com/)
* Czech - by [Adam Laita](http://laita.cz)
* Danish - by Lui Wallentin Gottler
* Dutch - by [Heleen van den Bos](http://www.bostekst.nl/)
* Finnish - by [Daniel Storgards](www.danielstorgards.com)
* French - by [Laura Orsal](http://www.traductrice-independante.fr)
* German - by Alex Ernst
* Hebrew - by [Ahrale Shrem](http://atar4u.com/)
* Hungarian - by [Surbma](http://surbma.hu)
* Italian - by [Luca](http://www.lucacicca.it)
* Polish - by Bartosz Arendt
* Portuguese - by Luis Maia
* Slovenian - by Thomas Cuk
* Spanish - by Fernando Blasco
* Swedish - by [Daniel Storgards](http://www.danielstorgards.com/)

== Installation ==

1. Install Cookie Notice either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Cookie Notice settings and set your options.

== Frequently Asked Questions ==

No questions yet.

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.2.28 =
* New: Option to set on scroll offset

= 1.2.27 =
* Tweak: Correctly remove scroll event, limit possible conflicts
* Tweak: Italian translation update

= 1.2.26 =
* Fix: Accept cookies on scroll option working unchecked.
* Fix: call_user_func() warning on lower version of WP

= 1.2.25 =
* New: Option to accept cookies on scroll, thanks to [Cristian Pascottini](http://cristian.pascottini.net/)

= 1.2.24 =
* New: Option to refuse to accept cookies
* New: setCookieNotice custom jQuery event
* Tweak: Italian translation updated, thanks to Luca Speranza

= 1.2.23 =
* New: Finnish translation, thanks to [Daniel Storgards](www.danielstorgards.com)

= 1.2.22 =
* Tweak: Swedish translation updated, thx to Ove Kaufeldt

= 1.2.21 =
* New: Plugin development moved to [dFactory GitHub Repository](https://github.com/dfactoryplugins)
* Tweak: Code cleanup

= 1.2.20 =
* New: Option to select scripts placement, header or footer

= 1.2.19 =
* New: Danish translation, thanks to Lui Wallentin Gottler

= 1.2.18.1 =
* Fix: Quick fix for 1.2.18 print_r in code

= 1.2.18 =
* New: More info link target option
* Tweak: Additional HTML ids, for more flexible customization

= 1.2.17 =
* New: Hebrew translation, thanks to [Ahrale Shrem](http://atar4u.com/)

= 1.2.16 =
* Tweak: Dutch translation missing due to a typo 

= 1.2.15 =
* New: Danish translation, thanks to Hans C. Jorgensen
* Fix: Notice bar not visible if no animation selected

= 1.2.14 =
* New: Hungarian translation, thanks to [Surbma](http://surbma.hu)

= 1.2.13 =
* New: Croatian translation, thanks to [Marko Beus](http://www.markobeus.com/)

= 1.2.12 =
* New: Slovenian translation, thanks to Thomas Cuk

= 1.2.11 =
* New: Swedish translation, thanks to [Daniel Storgards](http://www.danielstorgards.com/)

= 1.2.10 =
* New: Italian translation, thanks to [Luca](http://www.lucacicca.it)
* Tweak: Confirmed WP 4.0 compatibility

= 1.2.9.1 =
* Tweak: Enable HTML in cookie message text
* New: Option to donate this plugin :)

= 1.2.8 =
* New: Czech translation, thanks to [Adam Laita](http://laita.cz)

= 1.2.7 =
* New: French translation, thanks to [Laura Orsal](http://www.traductrice-independante.fr)
* New: Deleting plugin settings on deactivation as an option

= 1.2.6 =
* New: German translation, thanks to Alex Ernst

= 1.2.5 =
* New: Spanish translation, thanks to Fernando Blasco

= 1.2.4 =
* New: Added filter hooks to customize where and how display the cookie notice

= 1.2.3 =
* New: Portuguese translation, thanks to Luis Maia

= 1.2.2 =
* Fix: Read more linking to default site language in WPML & Polylang

= 1.2.1 =
* Tweak: UI improvements for WP 3.8

= 1.2.0 =
* Fix: Cookie not saving in IE
* Fix: Notice hidden under Admin bar bug
* Tweak: Improved WPML & Polylang compatibility

= 1.1.0 =
* New: Rewritten cookie setting method to pure JS
* Fix: Compatibility with WP Super Cache and other caching plugins

= 1.0.2 =
* New: Dutch translation, thanks to Heleen van den Bos

= 1.0.1 =
* Tweak: Changed setting cookie mode from AJAX to JS driven

= 1.0.0 =
Initial release

== Upgrade Notice ==

= 1.2.28 =
* New: Option to set on scroll offset