=== Cookie Notice for GDPR ===
Contributors: dfactory
Donate link: http://www.dfactory.eu/
Tags: gdpr, cookie, cookies, notice, notification, notify, cookie, cookie compliance, cookie law, eu cookie, privacy, privacy directive, consent
Requires at least: 3.3
Requires PHP: 5.2.4
Tested up to: 5.3
Stable tag: 1.2.48
License: MIT License
License URI: http://opensource.org/licenses/MIT

Cookie Notice allows you to elegantly inform users that your site uses cookies and to comply with the EU cookie law GDPR regulations.

== Description ==

[Cookie Notice](http://www.dfactory.eu/plugins/cookie-notice/) allows you to elegantly inform users that your site uses cookies and to comply with the EU cookie law GDPR regulations.

For more information, check out plugin page at [dFactory](http://www.dfactory.eu/) or plugin [support forum](http://www.dfactory.eu/support/forum/cookie-notice/).

= Features include: =

* Customizable cookie message
* Redirects users to specified page for more cookie information
* Multiple cookie expiry options
* Link to Privacy Policy page
* WordPress Privacy Policy page synchronization
* Option to accept cookies on scroll
* Option to set on scroll offset
* Option to refuse functional cookies
* Option to revoke the user consent
* Option to manually block scripts
* Option to reload the page after cookies are accepted
* Select the position of the cookie message box
* Animate the message box after cookie is accepted
* Select bottons style from None, WordPress and Bootstrap
* Set the text and bar background colors
* WPML and Polylang compatible
* SEO friendly
* .pot file for translations included

= Usage: =

If you'd like to code a functionality depending on the cookie notice value use the function below:

`if ( function_exists('cn_cookies_accepted') && cn_cookies_accepted() ) {
	// Your third-party non functional code here
}`

= Get involved =

Feel free to contribute to the source code on the [dFactory GitHub Repository](https://github.com/dfactoryplugins).

== Installation ==

1. Install Cookie Notice either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Cookie Notice settings and set your options.

== Frequently Asked Questions ==

No questions yet.

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.2.48 =
* Fix: HTML tags stripped from cookie message
* Fix: Link target not accepted in inline privacy link

= 1.2.47 =
* New: Option to select the privacy policy link position
* Tweak: Do not relad the page on refuse button click
* Tweak: Added aria-label attribute to cookie notice container

= 1.2.46 =
* Tweak: Remove WP Super Cache cookie on deactivation
* Tweak: Remove plugin version from the db on deactivation

= 1.2.45 =
* Tweak: Improved WP Super Cache support
* Tweak: CSS container style issue and media query for mobile

= 1.2.44 =
* Fix: The text of the revoke button ignored in shortcode
* Fix: Revoke consent button not displayed automatically in top position
* Tweak: Add shortcode parsing for content of [cookies_accepted], thanks to [dsturm](https://github.com/dsturm)

= 1.2.43 =
* New: Option to revoke the user consent
* New: Script blocking extended to header and footer
* New: Synchronization with WordPress 4.9.6 Privacy Policy page
* New: Custom button class option
* Tweak: Added 1 hour cookie expiry option

= 1.2.42 =
* New: Introducing [cookies_accepted][/cookies_accepted] shortcode
* Fix: Infinite cookie expiry issue

= 1.2.41 =
* Fix: Infinite redirection loop with scroll enabled

= 1.2.40 =
* Fix: Div align center on some themes
* Tweak: Extended list of allowed HTML tags in refuse code
* Tweak: Minified CSS and JS

= 1.2.39 =
* New: Option to reload the page after cookies are accepted

= 1.2.38 =
* Tweak: Move frontend cookie js functions before the document ready call, thanks to [fgreinus](https://github.com/fgreinus)
* Tweak: Adjust functional javascript code handling 
* Fix: Chhromium infinity expiration date not valid
* Fix: Remove deprecated screen_icon() function

= 1.2.37 =
* Tweak: Add aria landmark role="banner"
* Tweak: Extend cn_cookie_notice_args with button class

= 1.2.36.1 =
* Fix: Repository upload issue with 1.2.36

= 1.2.36 =
* Fix: String translation support for WMPL 3.2+ 
* Fix: Global var possible conflict with other plugins
* Tweak: Add $options array to "cn_cookie_notice_output" filter, thanks to [chesio](https://github.com/chesio).
* Tweak: Removed local translation files in favor of WP repository translations.

= 1.2.35 =
* Tweak: Use html_entity_decode on non-functional code block
* Tweak: get_pages() function placement optimization
* Tweak: Filterable manage cookie notice capability

= 1.2.34 =
* Fix: Empty href in links HTML validation issue

= 1.2.33 =
* New: Greek translation thanks to Elias Stefanidis

= 1.2.32 =
* Fix: Accept cookie button hidden on acceptance instead of the cookie message container

= 1.2.31 =
* New: Non functional Javascript code field
* Fix: Minified Javascript caching issue

= 1.2.30 =
* Fix: jQuery error after accepting cookies

= 1.2.29 =
* Tweak: Add class to body element when displayed
* Tweak: Italian translation update

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

= 1.2.48 =
* Fix: HTML tags stripped from cookie message
* Fix: Link target not accepted in inline privacy link