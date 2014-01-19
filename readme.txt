=== Plugin Name ===
Contributors: jvandemerwe
Tags: tabs, ui-tabs, genesis, genesiswp, studiopress
Requires at least: 3.7
Tested up to: 3.7.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extended version of the Genesis Tabs plugin from Studiopress. Make selected posts unique on the page. And more...

== Description ==

= Credits =
Extended version of the Genesis Tabs plugin from Studiopress ! All credits go to them.

Credits: nathanrice, studiopress and Ron Rennick

Original plugin URL: http://wordpress.org/plugins/genesis-tabs/


= Requirements =
Note: This plugin only supports Genesis child themes.

= Support =
This plugin is not endorsed or supported by Studiopress. See FAQ for support.

= What is does =
Genesis Tabs Extended extends the Featured Post widget to create a simple tabbed area. Now you can
also select if the selection is exclusive to the selected category (in this category only) per tab
and you can add additional categories that always have to be excluded, when doing a query on the
tabbed category

= What's the extra =
The extra feature in this plugin is that you can select that every post has to be unique on the presented page.
It will even check if the post is uniquely taken when also in other selected tab categories.

It basically adds the unique feature, while it is also available in the Genesis Featured Post widget in the Genesis Theme (version 2)

= How it works =

= Choose up to 8 categories to show =
nothing new here

= Don't include posts from these categories =
Let's say that a certain is picked to be put in the tab, but it also in one of the following categories,
it will be skipped. It could be used if you have a category "headlines" and a post is in this category and in
the category "art" and "headlines" is selected here, it would skip the article when category "art" is selected
as one of the categories for your tabs.

= Don't show the same post more than once. =
Well that makes everything unique. But this is how it will work:

* You selected for tab 1 category X, that has posts A, B
* You selected for tab 2 category Y, that has posts A, B, C, D
* You selected for tab 3 category Q, that has posts A, C, D
* You selected for a non select category Z, that has post A, C
* Tab 1 will show post B (for A is also in a non display category).
* Tab 2 will show post D (for A is non display, B is in tab 1 and C is in non display)
* Tab 3 will show nothing for there are no posts left to show


== Installation ==

1. Upload the entire `genesis-tabs-extended` folder to the `/wp-content/plugins/` directory
1. DO NOT change the name of the `genesis-tabs-extended` folder
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In the "Widgets" screen, drag the "Genesis Tabs (extended)" widget to the widget area of your choice, and configure.

== Frequently Asked Questions ==

= I have a some questions and I want support where can I go? =

<a href="http://www.enovision.net/contact/">http://www.enovision.net/contact/</a> and drop your question.

== Changelog ==

= 1.0 =
* Initial release

== Screenshots ==

1. The extended plugin extra options dialog