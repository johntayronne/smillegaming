=== Advanced Steam Widget ===
Contributors: harpercl
Donate link: http://www.SnakeByteStudios.com/contribute
Tags: widget, Steam, gaming, template
Requires at least: 3.0
Tested up to: 4.3.1
Stable tag: trunk

Displays Steam gaming statistics in a widget with increased flexibility, stability, and performance

== Description ==

NOTICE: STEAM RECENTLY ADDED A NEW PROFILE PRIVACY OPTION THAT BY DEFAULT DISABLES SHOWING GAME INFORMATION FOR THE WIDGET. Make sure you edit your profile privacy settings and turn game details (and your profile) to public.

This plugin will add a widget that displays your Steam gaming statistics. It employs caching to keep your site's performance up and make it less susceptible to Steam outages or errors.

The widget comes with the following preset looks (see screenshots) that you can easily switch between:

* Profile Only
* Profile Small
* Profile + Games
* Games Only
* Games Grid
* Full-page Profile

You can also customize the widget to suit your needs by editing the templates, which support the following attributes pulled from your Steam profile:

* Recently Played Games
    - Game Name
    - Steam URL
	- Player Stats URL
    - Icon URL (32)
    - Small Logo URL (120 x 45)
    - Large Logo URL (184 x 69)
    - Time Played Last Two Weeks
    - Time Played Total
* Player Profile
    - Steam Username
	- 64-bit Steam ID
	- Status
	- Profile URL
    - Avatar Icon URL (32)
    - Avatar Medium URL (64)
    - Avatar Large URL (184)
    - Time Played Last Two Weeks

== Installation ==

1. Copy the contents of this archive to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'Appearance > Widgets' menu and add 'Steam Widget' to a sidebar
1. Expand the widget's options, enter a Steam Profile ID, and save

== Frequently Asked Questions ==

= I put the widget into my sidebar but nothing is displayed on my site. =

1. First, make sure you put your Steam Profile ID in the widget's settings.
1. Check that your Steam profile is set to public.
1. Try saving the widget's settings a few times (forcing a data refresh) in case Steam was just experiencing an intermittent problem.

= How do I find my Steam Profile ID? =

In either Steam itself or on the Steam website, go to the upper-right where it says "your account" and then click on "View Profile". The URL on the page will either be in the format http://steamcommunity.com/id/XXX or http://steamcommunity.com/profiles/XXX where your Steam Profile ID is XXX. For the latter, it will be a unique 17-digit number. If you want to use the former, which has a prettier URL, go to "Edit Profile" and enter a "Custom URL".

= How do I customize the look of my widget? =

In the widget options, change the preset to "Custom". The two template boxes will appear. The "Game Template" is used to output every game played recently and the "Main Template" is for everything else. To show the game template in the main template, you must use the pattern %GAMES_TWOWEEKS% in the main template.

Use "Toggle Show Patterns" below each template to show the patterns that each template supports. These patterns are replaced with data from your Steam profile.

Patterns that start with "IF_" are conditionals that can output based on whether or not some condition is true. For example, for IF_GAME_STATS{XXX}, if a game supports stats, "XXX" is output, otherwise nothing is output. Conditionals can also be followed with an "ELSE" pattern that outputs if the preceding conditional was false. For example, IF_GAME_STATS{%GAME_STATS_URL%}ELSE{%GAME_URL%} will output the URL for your game stats if the game supports stats, otherwise it will output the URL for the game's Steam community page.

= What is the shortcode for? =

When you save the widget's settings, it will show you a shortcode that you can use to display that widget in pages or posts. Just copy and paste the code where you want the widget. It's recommended to use the full-page preset with the shortcode. You can move the widget into the "Inactive Widgets" area when only using the shortcode.

== Screenshots ==

1. Profile Only preset
2. Profile Small preset
3. Profile + Games preset
4. Games Only preset
5. Games Grid preset
6. Widget options

== Changelog ==

= 1.7 =
* Steam data update is done using cron now instead of at intervals on widget display. should remove any possible data propagation delays for visitors
* added timer displays for last and next update in widget options
* added additional error checking and reporting
* added cleanup on plugin deactivation and uninstall
* added profile URL and box-sizing CSS to all presets
* fixed CURL redirect issue involving PHP-deprecated safe mode
* fixed WP-deprecated widget constructor error
* fixed a PHP notice
* various optimizations

= 1.6.1 =
* workaround for Steam bug where hours played last two weeks is reported as 0.0
* fixed case where stats conditional was true when there were no stats

= 1.6 =
* added two new presets
* added basic shortcode support
* added ability to not have a widget title
* added profile URL pattern
* added conversion for Steam IDs to Profile IDs
* various template tweaks

= 1.5 =
* added four preset templates
* added conditional patterns for stats and user status
* added stats URL pattern for games
* simplified widget options
* fixed minor bug with curl (thanks Andrewsk1)

= 1.0.1 =
* a few default template fixes
* more error checking for the Steam API output

= 1.0 =
* First public release