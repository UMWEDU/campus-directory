=== SAU Contact Directory ===
Contributors: cgrymala, smkeith
Tags: contact, employees, staff, directory, faculty
Requires at least: 3.2
Tested up to: 3.4
Stable tag: 1.0.1a
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin requires the Genesis framework. Enables the creation of a staff/faculty directory.

== Description ==

*This plugin requires the [Genesis Framework](http://www.studiopress.com/)*

This plugin creates a new post type for Contact entries, allows an alphabetical listing of all contacts, an alphabetical listing of all "departments" (a custom taxonomy) and an alphabetical listing of all "buildings" (another custom taxonomy).

= Initial setup =

* Activate the plugin
* If you have existing posts with this data in them, you should visit Tools -> Directory Conversion to convert them into Contact entries. Doing so will convert *all* published posts into Contact entries and will convert all appropriate categories to "Departments"
* Create a new page on the site for each of the following special archives:
    * A to Z contact list
    * List of departments
    * List of buildings
* Visit Genesis -> Theme Settings
* Specify which pages to use for the A to Z list of contacts, which to use for the list of departments and which to use for the list of buildings
* Start adding new contacts

== Installation ==

= Manual Installation =

**Upload the ZIP**

1. Go to Plugins -&gt; Add New -&gt; Upload in your administration area.
1. Click the "Browse" (or "Choose File") button and find the ZIP file you downloaded.
1. Click the "Upload" button.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

**FTP Installation**

1. Unzip the file somewhere on your harddrive.
1. FTP into your Web server and navigate to the /wp-content/plugins directory.
1. Upload the collapsible-widget-area folder and all of its contents into your plugins directory.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

= Must-Use Installation =

If you would like to **force** this plugin to be active (generally only useful for Multi Site installations) without an option to deactivate it, you can upload the contents of the sau-campus-directory folder to your /wp-content/mu-plugins folder. If that folder does not exist, you can safely create it.

== Frequently Asked Questions ==

= How do I set up the pages that are used as placeholders? =

Go to Genesis -> Theme Settings and choose the appropriate pages

= How do I use a custom template for the taxonomy feeds? =

By default, the taxonomy feeds (buildings and departments) use a template located at templates/taxonomy-feed.php within the plugin folder. If you would like to use a custom template for either or both of those feeds, you can hook into the `sau-contact-tax-feed-path` filter to filter that location. Your hook's callback should return an absolute path to the file that should be used.

The `sau-contact-tax-feed-path` filter can send two parameters: the original location of the template file and the taxonomy being used (currently either `department` or `building`).

= Where will I find the custom feeds that this plugin creates? =

By default, the plugin creates new feeds at the following URLs:

1. /a/feed/ - the full alphabetical listing of contacts
1. /b/feed/ - the full list of buildings
1. /d/feed/ - the full list of departments

You can change those locations by using the `sau-contact-feed-slug` filter. That filter can send 2 parameters: the slug that needs to be changed/returned, and the type of feed for which the slug is being generated (`building`, `alpha` or `department`).

If you filter these feed slugs, make sure to flush your permalinks before trying to visit the new locations.

= Why am I getting a 404/Page not Found error when I try to visit one of the custom feeds? =

Try visiting Settings -> Permalinks in your admin area to flush your permalink settings; then try visiting one of the feeds again.

= How do I use a custom loop template for archive entries or single contacts? =

Before being returned and output, each entry is run through a filter. For archive pages, the entries are run through the `sau-contact-archive-entry` filter; for single contacts, the entries are run through the `sau-contact-single-entry` filter.

Keep in mind that any changes you make need to be returned, not echo'd/printed.

By default, the same loop template is used to generate the content/description within the RSS2 feeds.

= How are the custom feeds structured? =

The full alphabetical list of contacts is structured so that:

* The "description" (excerpt) uses the exact same HTML used when displaying a contact on an archive page
* The "content" uses the exact same HTML used for a single contact
* The featured image (post_thumbnail) is output as an enclosure if it exists. If it doesn't, the `image_path_wpcm_value` post meta value is used to output an enclosure. If that doesn't exist, either, no enclosure is output.

The taxonomy feeds use the following structure by default:

* The `description` and `content` elements both contain the taxonomy term's Description
* The `wfw:commentRss` element contains the URL to the RSS feed for the individual taxonomy term

= How do I implement pagination on the archive pages? =

1. Add a hook into the `sau-contact-items-per-page` filter so that you can change the number of entries that are shown on the page.
2. Add a hook into the `sau-contact-start-archive-loop` and/or `sau-contact-done-archive-loop` action and output the pagination HTML.

= What filters are available in this plugin? =

* `sau-contact-feed-slug` - Filters the slug used for each of the custom feeds. Sends 2 parameters: slug, feed type (department|alpha|building)
* `sau-contact-tax-feed-path` - Filters the absolute path that points to the template used to generate the taxonomy feed. Sends 2 parameters: path, taxonomy type (department|building)
* `sau-contact-items-per-feed` - Filters the number of items that should be shown in the custom alphabetical feed (defaults to -1)
* `sau-contact-alpha-feed-title` - Filters the title used for the custom alphabetical feed
* `sau-contact-buildings-feed-title` = Filters the title used for the custom "buildings" taxonomy feed
* `sau-contact-departments-feed-title` - Filters the title of the custom RSS feed for the Department taxonomy
* `sau-contact-meta-fields` - Filters the array of custom meta fields that should be shown on edit post screen
* `sau-contact-no-posts` - Filters the HTML that should be displayed when no results are found for a page
* `title-wpcm-value` - Filters the value of a specific contact's job title
* `name-wpcm-value` - Filters a specific contact's full name. Can send 2 parameters: the full name, and an array of the separate parts of the name
* `email-wpcm-value` - Filters a specific contact's email address
* `phone-wpcm-value` - Filters a specific contact's phone number
* `sau-contact-archive-entry` - Filters the HTML output used for an entry on an archive page
* `sau-contact-single-entry` - Filters the HTML output used for an entry on a single contact page
* `sau-default-contact-image` - Filters the location of the default image to be used when a contact doesn't have their own photo set
* `sau-contact-enclosure-size` - Filters the thumbnail/image size that should be included in the feed (defaults to `full`)
* `sau-contact-items-per-page` - Filters the number of entries that should appear on a single archive page

== Changelog ==

= 1.0.4a =

* Diagnosed bug that stopped all contacts within child terms from showing on top-level term archive page. The solution is to edit each child "Department" and re-save it, which will get WordPress to recognize the correct parent term when loading the archive page.
* Added link to building archive for employees with building taxonomy set
* Fixed bug that stopped all entries from appearing on taxonomy archive pages.

= 1.0.1a =

* Images are now automatically added as "featured image" wherever appropriate during directory conversion

= 1.0a =
First full version of the plugin

= 0.1 =
This the first functional version of the plugin

