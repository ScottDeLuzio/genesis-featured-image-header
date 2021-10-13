=== Genesis Featured Image Header ===
Contributors: scott.deluzio, ampmode
Tags: genesis, featured image, header, custom post type
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CSWJFA3B587U6
Requires at least: 3.5
Tested up to: 5.8.1
Stable tag: 1.2.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add a featured image to the header of every page on your site including Custom Post Type archive pages.

== Description ==

Featured images are a standard feature in WordPress, but not every Genesis child theme will insert them on your site's pages.

Even more troublesome, is if you have custom post types with archive pages that you would like to have a featured image set on. These archive pages usually have no option to set a featured image without editing theme files and messing around with complicated code.

This plugin will allow you to set a different banner style image on every page on your site including custom post type archive pages. See the screenshots for an example of what type of header image you can use.

You can set the featured image for a custom post type archive page by clicking "Featured Image" on the admin menu for that post type. Check out the screenshots and FAQs for more info.

**This plugin requires your site to be using a Genesis child theme in order to work. It is not compatible with other themes.**

*Plugin author is a third-party developer who is not affiliated with StudioPress, the owner of the Genesis Framework.*

== Installation ==
1. Download archive and unzip in wp-content/plugins or install via Plugins � Add New.
2. Activate the plugin through the Plugins menu in WordPress.

== Frequently Asked Questions ==
= How do I set the featured image on a custom post type archive page? =

If your custom post type is called "Books", you can navigate to the Books > Featured Image page on the admin menu. Once there, you can use the upload button to upload a new image or select an existing image from your site's Media Library.

= How do I unset the archive page image? =

Simply follow the steps above, but instead of clicking the upload image button simply delete the URL in the text box and click save changes.

= Can I use this with WooCommerce? =

Yes, however you can only set one image for all WooCommerce pages (store, product category pages, and product tag pages). This is done by navigating to Products > Featured Image in your admin section. The cart and checkout pages are regular WordPress pages, and can have different images from the rest of the store if you want.

= How do I set a featured image for individual pages? =

First, make sure the checkbox above on the plugin's settings page (Genesis > Featured Images) is checked and that you clicked Save Changes. Then on the page edit screen, simply click the Set Featured Image link. If you do not see this option, click Screen Options at the top of the page and check the box next to Featured Image. If you do not want to have a featured image on a certain page, simply do not add one and nothing will show up.

= My pages already show the featured image someplace else now it is showing twice. How can I get rid of that? =

If you already have a featured image but this plugin is making it show up in another location, simply uncheck the box above and your pages will no longer use this plugin's settings. You can still continue to use the plugin for custom post type archives.

= Is there a "global" featured image setting? =

No. This plugin is designed to allow you to set a different image for each page and custom post type archive. If you want the same image for each page, you can still use this plugin. Simply use the same image for each page. If you have too many pages to set manually, you may want to look into adding the image another way.

= I need help identifying the featured image locations. =

The locations in the dropdown menu above are more or less in a top to bottom order (some locations are next to each other). If you are working on a site that is not live, you could simply try out the different locations to see where they will show up on your site. There are also some great guides available that will help you identify where these locations are on your site. One is the [StudioPress Hook Reference guide](https://my.studiopress.com/docs/hook-reference/) (for StudioPress customers only). Another reference is the [Visual Hook Guide from Genesis Tutorials](http://genesistutorials.com/visual-hook-guide/). This tutorial helps you visualize where the image will show up. If you want to see the same visualization on your own site, check out the [Genesis Visual Hook Guide Plugin](https://wordpress.org/plugins/genesis-visual-hook-guide/).

= My theme has a custom action where I want the featured image to be displayed. =

You can filter the array of locations in the plugin's settings with the `gfih_location_options` filter to add your location. You would need to merge your location in as an array with the key being the action name, and the value being the human readable format.

```add_filter( 'gfih_location_options', 'my_custom_function' );
function my_custom_function( $genesis_actions ){
	$my_location = array(
		'my_custom_action'	=> 'My custom action name'
	);
	return array_merge( $genesis_actions, $my_location );
}
```

= What are the dimensions of the images I should be using? =

This will depend on a few factors. Each theme may have slightly larger or smaller widths or heights for each location where your image will show up. This answer will vary based on each different Genesis child theme. Also, each location that you can choose from to insert your image has different dimensions. So not only will the answer vary based on the theme you are using, it will also vary depending on the location where you are inserting the image. The plugin will insert the full size image that you upload (no cropping is done). If you want exact dimensions, check out the Genesis Visual Hook Guide Plugin (see link above). When viewing the page use the Action Hooks option then right click the area you want to get the width for and click "Inspect Element". In many browsers this will pop up the dimensions of the area you are looking at.

= The featured image is not showing up correctly or at all. What can I do? =

This plugin is set to show an image on the following post types: custom post type archive pages, regular WordPress pages, regular WordPress posts, and WooCommerce shop pages. Some plugins may use different templates or methods for calling their pages so this plugin may not work on all pages. For example, we had to add special support for WooCommerce pages - without this support the plugin wouldn't be able to insert an image on WooCommerce pages. Please let us know of any pages that are not working correctly in the plugin support forum.

== Screenshots ==
1. Add a featured image to your page, and save or update your changes.
2. View your page to see where the featured image shows up.
3. If you want to change the featured image location, visit Genesis > Featured Images, and choose a new location from the dropdown menu. Be sure to save your changes.
4. View your page again and see where the image is now.
5. For custom post type archive pages, find the "Featured Image" link in the archive's menu. Use the Upload Image button to upload a new image or to select a previously uploaded image. Click the save changes button to save your settings.

== Changelog ==
= 1.2.12 =
* Updated tested up to version.
* Added Contributors.

= 1.2.11 =
* Updated tested up to version.

= 1.2.10 =
* Fix: Dropdown menu of Genesis actions was not loading correctly after update to Genesis 3.1
* New: Added a filter to allow for additional actions to be added to the setting menu. `gfih_location_options`

= 1.2.9 =
* Fix: Conditional statement checking for WooCommerce pages was incorrect.
* Update: Bumped tested up to version.

= 1.2.8 =
* Fix: Removed small debugging code left behind from previous update.

= 1.2.7 =
* Fix: Check for active Genesis theme had bad styling that threw off the layout of the page.
* Fix: WooCommerce shop page was displaying the first product's featured image in addition to the main featured image.

= 1.2.2 - 1.2.6 =
* Minor Updates

= 1.2.3 =
New in 1.2.2: Added support for 13 additional hooks.
Minor bug fix.

= 1.2.2 =
New: Added support for 13 additional hooks.

= 1.2.1 =
* New: Added support for genesis_after, genesis_after_footer, genesis_before_site_title, and genesis_after_site_title.

= 1.2 =
* New: Allow custom post type pages to hide the featured image in the event it is being used elsewhere in a theme template file for that particular custom post type. This can be set for each custom post type.

= 1.1.1 =
* Minor bug fixes related to sites not also running WooCommerce.

= 1.1 =
Bug Fixes:

* Custom Post Type archive images were not saving correctly.
* WooCommerce product pages were forcing the featured image to display in the WooCommerce template and through this plugin. New option to turn this plugin off on WooCommerce single product pages.

= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.2.12 =
* Updated tested up to version.
* Added Contributors.