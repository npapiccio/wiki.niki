=== List Related Attachments ===
Contributors: twinpictures, baden03
Donate link: http://plugins.twinpictures.de/plugins/list-related-attachments/
Tags: widget, list, related, attachments, sidebar, uploaded, files, shortcode, twinpictures, plugin-oven, plugin oven
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 2.1.3

Listed Related Attachments will display a filtered list of all related attachments for the current post or page.

== Description ==
List Related Attachments is a sidebar widget and shortcode that will display a filtered, sorted and ordered list of all related attachments linked to current post or page. The widget options are: title, number of attachments to display, type of attachment to display by mime/type, order by value, order direction and what should be displayed (attachment title, caption or description).  A <a href='http://plugins.twinpictures.de/plugins/list-related-attachments/documentation/'>complete listing of shortcode options and attribute demos</a> are available that delight and inform. What's more, there is <a href='http://wordpress.org/support/plugin/list-related-attachments-widget'>excellent and free community support</a> and a super-duper online <a href=“https://translate.wordpress.org/projects/wp-plugins/list-related-attachments-widget”>language translation tool</a> to roll-your-own language files.

== Installation ==
1. Old-school: upload the `List Related Attachments` folder to the `/wp-content/plugins/` directory via FTP.  Hipster: Add the plugin via the WordPress Plugins menu.
1. Activate the Plugin
1. Add the Widget to the desired sidebar in the WordPress Widgets menu.
1. Configure the `Listed Related Attachments` widget options.
1. Add a the shortcode to your post like so: `[list-related-attach type="application/zip" count="3" orderby="title" order="ASC" show="caption"]`
1. Test that the this plugin meets your demanding needs.
1. Rate the plugin and verify that it works at wordpress.org.
1. Leave a comment regarding bugs, feature request, cocktail recipes at: http://wordpress.org/support/plugin/list-related-attachments-widget

== Frequently Asked Questions ==
= What are the default shortcode attributes? =
1. type = "application" (so basically everything)
1. count = "-1" (so everything)
1. orderby = "date" (by the date the attachment was added)
1. order = "DESC" (Descending in the newest to oldest direction)
1. show = "title" (The title of the attachment)
1. link_so = “file” (as opposed to the attachment page)

= I am going to a respectable cocktail bar, can you recommend a drink? =
Gin Basel Smash

= I am a Social Netwookiee, do you have a Facebook page? =
Yes, yes... <a href='http://www.facebook.com/twinpictures'>Twinpictures is on Facebook</a>.

= Does Twinpictures do the Twitter? =
Ah yes! <a href='http://twitter.com/#!/twinpictures'>@Twinpictures</a> does the twitter tweeting around here.

= Where can I go for jokes and fun? =
Why not try: <a href='http://jokesandfun.de/'>Jokes & Fun</a>

= What is the meaning of life, the universe and everything? =
42

= Who likes to rock the party? =
We like to rock the party.

== Screenshots ==
1. Shown here are the available options of the `List Related Attachments`.  See any more you like?  Let us know!
1. For your viewing pleasure:  The sidebar widget in the midst of some hot-hot sidebar widget action.  Calm down now... it's not THAT exciting.
1. Hide your kids, hide your husband, this is how the shortcode works.

== Changelog ==

= 2.1.3 =
* supports WordPress language packs, no longer ships with all languages
* fully tested with WordPress 4.5

= 2.1.2 =
* added new parent_id attribute to override current page

= 2.1.1 =
* adjusted php5 constructor play well with older php versions
* changed text domain so it works with new WordPress Translation service

= 2.1.0 =
* using new php5 constructor for widget

= 2.0.1 =
* cleaned up the readme file
* first check to make sure there is a pageID before trying to grab it’s attachments

= 2.0 =
* Rebuilt plugin
* Added options page
* added link_to option to link to file or attachment page
* added language translation

= 1.9 =
* Only the attachments of the first post will be displayed when the sidebar widget is used on the blog page.

= 1.8 =
* Plugin now supports multi-language and multiple instances

= 1.7 =
* Added the ability to chose a target for the attachment (for example a new tab)
* Expanded the display/show attribute to list multiple attributes such as title - caption or title (description)
* A big Thank You to oldbrit (http://wordpress.org/support/profile/oldbrit) suggesting these ideas! (http://wordpress.org/support/topic/plugin-list-related-attachments-title-caption-or-description)

= 1.6.1 =
* Vastly enhanced documentation and moved the plugin over to the plugin oven.

= 1.6 =
* Optimized code and added mime-type classes to list items.

= 1.5 =
* Fixed a bug in the shortcode (Big thanks to motylanogha for reporting this).  NOW the plugin works 100% like it should... I think.

= 1.4 =
* Options now allow to choose from the attachment title, caption or description as the text that is displayed in the attachment link.

= 1.3 =
* Expanded the options to include orderby and order... for *Ultimater Flexibility* -- yes, Ultimater is not a real word, but just go with it, OK?

= 1.2 =
* Added shortcode so that related attachments can be listed withing the page loop.  Listing related attachments is not just for sidebar widgets anymore.

= 1.1 =
* Changed mime/type to a text input field for *Ultimate Flexibility*.
* Pimped this readme.txt file.
* Added the plugin to wordpress.org.

= 1.0 =
* The plugin came to be.

== Upgrade Notice ==
Removed languages in favour of WordPress Language Packs.
Fully tested with WordPress 4.5
