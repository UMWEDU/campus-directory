=== SAU Contact Directory ===
Contributors: cgrymala, smkeith
Tags: contact, employees, staff, directory, faculty
Requires at least: 3.2
Tested up to: 3.4
Stable tag: 0.1
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

== Changelog ==

= 0.1 =
This the first functional version of the plugin

