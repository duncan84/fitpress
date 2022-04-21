=== FitPress ===
Contributors: Daniel Walmsley, Duncan Bell
Tags: fitness, fitpress, fitbit
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Publish your FitBit statistics on your WordPress blog.

== Description ==

Currently:

* A shortcode for "fp_badges" which displays user badges
* A shortcode for "fp_profile" which displays the user avater and basic profile info
* A shortcode for "fitpress_profile" which allows user to link Fitbit account (should be merged with above)
* A shortcode for "heartrate" which takes a "date" parameter and prints a simple list of time spent in each heart rate zone for the day
* A shortcode for "steps" which prints a graph of steps taken over a 7 day period before the given "date"
* A shortcode for "goals" which prints a table of daily and weekly goals
See the "Usage" section for an example.

The hope is that eventually this plugin will provide the following functionality:

* Sidebar Widgets which display fitness statistics (e.g. heart rate over time)
* Post types which post to FitBit, e.g. meals
* Shortcodes to include graphs and tables of fitbit data in posts

== Installation ==

1. Save this plugin in your wp-content/plugins directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Usage ==

1. Connect the FitBit account for your user account by clicking "Link my FitBit Account" at the bottom of your profile page.
1. In any post:

```
My heart rate: [heartrate date="2015-12-04"]

Steps: [steps date="2015-12-04"]

Distance: [fp_distance date="2022-01-01"]

Calories: [fp_calories date="2022-01-01"]

BMI: [fp_bmi date="2022-01-01"]

Fat: [fp_fat date="2022-01-01"]

Weight: [fp_weight date="2022-01-01"]

Nutrition Info: [fp_nutrition_info date="2015-12-04" data_type="water|caloriesIn"]

Goals: [fp_goals]

Badges: [fp_badges]

Activites: [fp_activites]
```

== Changelog ==

= 0.6 = 
Added fp_nutrition_info shortcode to display nutrition info
Added fp_distance shortcode to display distance info
Added fp_calories shortcode to display daily calorie info
Added fp_bmi shortcode to display BMI
Added fp_fat shortcode to display fat info

= 0.5 =
Added fp_profile shortcode to display Fitbit profile avatar and info.
Added fp_activites for daily summaries

= 0.4 =
Fixed bug in steps shortcode preventing empty date fields.
Fixed bug in heartrate shortcode preventing empty date fields.

= 0.3 =

Added shortcode to show goals
Added shortcode for displaying FitBit account linking on posts
Added support for custom redirect after linking
Added support for multiple users (theoretically)

= 0.2 =

Switch to simple pure-PHP OAuth2 implementation

= 0.1 =

Non-functional version with settings and dependencies only

== Credits ==

This plugin was originally a thin wrapper around other people's work, but has now evolved significantly. Nevertheless, credit where credit is due to these awesome projects:

* [FitbitPHP](https://github.com/heyitspavel/fitbitphp) by *heyitspavel*
* [PHPoAuthLib](https://github.com/Lusitanian/PHPoAuthLib) by [David Desberg](https://daviddesberg.com/)