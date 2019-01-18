<?php
/*
Plugin Name: Genesis Featured Image Header
Plugin URI: https://amplifyplugins.com/
Description: Easily add a featured image to the header of every page on your site including Custom Post Type archive pages.
Version: 1.2.9
Author: Scott DeLuzio
Author URI: https://scottdeluzio.com
Text Domain: genesis-featured-image-header
*/
/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

/* Check if Genesis is Installed
--------------------------------------------- */
function gfih_require() {
	$files = array(
		'gfih'
	); //array for future use

	foreach( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'lib/' . $file . '.php';
	}
}
gfih_require();

$gfih = new Genesis_Featured_Image_Header();
$gfih->gfih_run();

/* Load text domain
--------------------------------------------- */
add_action( 'plugins_loaded', 'gfih_load_textdomain' );
function gfih_load_textdomain() {
	load_plugin_textdomain( 'genesis-featured-image-header', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

/* Add Featured Image to Header
--------------------------------------------- */
// Featured Image on Normal Pages
function gfih_image_before_posts() {
	$post_types = gfih_get_cpts();

	if ( function_exists( 'is_woocommerce' ) ) {
		if (is_product() && has_post_thumbnail() && get_option( 'gfih_on_product' ) == 'yes' && get_option( 'gfih_product_hide_single_cpt_page' ) != 'yes' ) {
			the_post_thumbnail( 'full' );
		} else {
			if ( is_shop() ) {
				$page_id = get_option( 'woocommerce_shop_page_id' );
				if ( has_post_thumbnail( $page_id ) ) {
					echo get_the_post_thumbnail( $page_id );
				}
			}
			if ( is_cart() ) {
				$page_id = get_option( 'woocommerce_cart_page_id' );
				if ( has_post_thumbnail( $page_id ) ) {
					echo get_the_post_thumbnail( $page_id );
				}
			}
			if ( is_checkout() ) {
				$page_id = get_option( 'woocommerce_checkout_page_id' );
				if ( has_post_thumbnail( $page_id ) ) {
					echo get_the_post_thumbnail( $page_id );
				}
			}
			if ( is_product_category() ) {
				global $wp_query;
				$cat = $wp_query->get_queried_object();
				$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
				$image = wp_get_attachment_url( $thumbnail_id );
				if ( $image ) {
					echo '<img src="' . $image . '" alt="" />';
				}
			}
		}
		if ( ( is_page() || is_single() ) && get_option( 'gfih_on_pages' ) == 'yes' ) {
			if ( in_array( get_post_type(), $post_types ) ) {
				if ( get_option( 'gfih_' . get_post_type() . '_hide_single_cpt_page' != 'yes' ) ) {
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'full' );
					}
				}
			} else {
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'full' );
				}
			}
		}
	} else {
		if ( ( is_page() || is_single() ) && get_option( 'gfih_on_pages' ) == 'yes' ) {
			if ( in_array( get_post_type(), $post_types ) ) {
				if ( get_option( 'gfih_' . get_post_type() . '_hide_single_cpt_page' != 'yes' ) ) {
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'full' );
					}
				}
			} else {
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'full' );
				}
			}
		}
	}
	foreach ( $post_types  as $post_type ) {
		if( is_post_type_archive( $post_type ) && !is_admin() ) {
			if( '' != get_option( 'gfih_' . $post_type . '_featured_image' ) ){
				echo '<img src="' . get_option( 'gfih_' . $post_type . '_featured_image' ) . '" class="attachment-full wp-post-image" alt="' . $post_type . '" />';
			}
		}
	}
}
add_action( get_option( 'gfih_action_location' ), 'gfih_image_before_posts' );

/* Featured Image On Archive Pages Settings Page */

// Hook for adding admin menus
add_action( 'admin_menu', 'gfih_cpt_archive_settings' );

// action function for above hook
function gfih_cpt_archive_settings() {
	$post_types = gfih_get_cpts();
	foreach ( $post_types  as $post_type ) {
		add_submenu_page( 'edit.php?post_type=' . $post_type, __( 'Featured Image', 'gfih' ), __( 'Featured Image', 'gfih' ), 'manage_options', 'gfih-'. $post_type . '-image-settings', 'gfih_cpt_featured_image_settings' );
	}
	add_submenu_page( 'genesis', __( 'Genesis Featured Image Header', 'gfih' ), __( 'Featured Images','gfih' ), 'manage_options', 'gfih-settings', 'gfih_main_settings' );
}
//get cpt names
function gfih_get_cpts() {
	$args = array(
		'public'   => true,
		'_builtin' => false
	);

	$output		= 'names'; // names or objects, note names is the default
	$operator	= 'and'; // 'and' or 'or'

	$post_types	= get_post_types( $args, $output, $operator );

	return $post_types;
}
//add option for each cpt to store image information
function activate_gfih_cpt_image_settings() {
	$post_types = gfih_get_cpts();
	foreach ( $post_types  as $post_type ) {
		add_option( 'gfih_' . $post_type . '_featured_image' );
		add_option( 'gfih_' . $post_type . '_hide_single_cpt_page' );
	}
	add_option( 'gfih_on_pages', 'yes' );
	add_option( 'gfih_on_product', 'yes' );
	add_option( 'gfih_action_location', 'genesis_before_content_sidebar_wrap' );
}
//delete option for each cpt's image information
function deactivate_gfih_cpt_image_settings() {
	$post_types = gfih_get_cpts();
	foreach ( $post_types  as $post_type ) {
		delete_option( 'gfih_' . $post_type . '_featured_image' );
		delete_option( 'gfih_' . $post_type . '_hide_single_cpt_page' );
	}
	delete_option( 'gfih_on_pages' );
	delete_option( 'gfih_on_product' );
	delete_option( 'gfih_action_location' );
}
//register hooks to add or delete cpt image information.
register_activation_hook( __FILE__, 'activate_gfih_cpt_image_settings' );
register_uninstall_hook( __FILE__, 'deactivate_gfih_cpt_image_settings' );
//register group for cpt images
add_action( 'admin_init', 'register_gfih_featured_image_settings' );
function register_gfih_featured_image_settings() {
	$post_types = gfih_get_cpts();
	foreach ( $post_types  as $post_type ) {
		register_setting( 'gfih_'. $post_type . '_group', 'gfih_' . $post_type . '_featured_image' );
		register_setting( 'gfih_'. $post_type . '_group', 'gfih_' . $post_type . '_hide_single_cpt_page' );
	}
	register_setting( 'gfih_featured_image_settings', 'gfih_on_pages' );
	register_setting( 'gfih_featured_image_settings', 'gfih_on_product' );
	register_setting( 'gfih_featured_image_settings', 'gfih_action_location' );
}
// Create CPT settings page(s)
function gfih_cpt_featured_image_settings() {
	$currentScreen = get_current_screen();
	$post_type	= $currentScreen->post_type;
	$postID		= 'gfih_' . $post_type . '_featured_image';
	$cptPage	= 'gfih_' . $post_type . '_hide_single_cpt_page';
	$title		= str_replace( '-',' ',$post_type );
	if( isset( $_GET['settings-updated'] ) ) { ?>
		<div id="message" class="updated">
			<p><strong><?php _e( 'Settings saved.', 'genesis-featured-image-header' ); ?></strong></p>
		</div>
	<?php } ?>
	<script>
	jQuery(document).ready(function($){


		var custom_uploader;


		$('#upload_image_button').click(function(e) {

			e.preventDefault();

			//If the uploader object has already been created, reopen the dialog
			if (custom_uploader) {
				custom_uploader.open();
				return;
			}

			//Extend the wp.media object
			custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				multiple: false
			});

			//When a file is selected, grab the URL and set it as the text field's value
			custom_uploader.on('select', function() {
				attachment = custom_uploader.state().get('selection').first().toJSON();
				$('#<?php echo $postID; ?>').val(attachment.url);
			});

			//Open the uploader dialog
			custom_uploader.open();

		});


	});
	</script>
	<div class="wrap">
		<div class="postbox">
			<h1><?php _e( 'Choose Image for', 'genesis-featured-image-header' ); ?> <?php echo ucwords( $title ); ?> <?php _e( 'Archive Page', 'genesis-featured-image-header' ); ?></h1>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( 'gfih_'. $post_type . '_group' ); ?>
				<input id="<?php echo $postID; ?>" type="text" size="36" name="<?php echo $postID; ?>" value="<?php echo get_option( $postID ); ?>" />
				<input id="upload_image_button" class="button" type="button" value="Upload Image" />
				<p><?php _e( 'Hide featured image in header for single posts in this post type?', 'genesis-featured-image-header' ); ?></p>
				<input id="<?php echo $cptPage; ?>" type="checkbox" name="<?php echo $cptPage; ?>" value="yes" <?php if ( get_option( $cptPage ) == 'yes' ) { echo 'checked'; } ?> />
				<input type="hidden" name="action" value="update" />
				<?php submit_button(); ?>
			</form>
			<?php if( get_option( 'gfih_' . $post_type . '_featured_image' ) != '' ) { ?>
				<h3><?php _e( 'Current Image:', 'genesis-featured-image-header' ); ?></h3><img src="<?php echo get_option( 'gfih_' . $post_type . '_featured_image' ); ?>" />
			<?php } ?>
		</div>
	</div>
<?php }
// Create main settings page
function gfih_main_settings() {
	//Create array of location options
	$a = array( 'genesis_before','genesis_before_header','genesis_header','genesis_before_site_title','genesis_site_title','genesis_after_site_title','genesis_site_description','genesis_header_right','genesis_after_header','genesis_before_content_sidebar_wrap','genesis_before_content','genesis_before_loop','genesis_loop','genesis_before_post','genesis_before_post_title','genesis_post_title','genesis_after_post_title','genesis_before_post_content','genesis_post_content','genesis_after_post_content','genesis_after_post','genesis_after_sidebar_widget_area','genesis_after_sidebar_alt_widget_area','genesis_before_sidebar_widget_area','genesis_before_sidebar_alt_widget_area','genesis_before_entry','genesis_entry_header','genesis_entry_content','genesis_entry_footer','genesis_after_entry','genesis_after_endwhile','genesis_after_loop','genesis_after_content','genesis_after_content_sidebar_wrap','genesis_before_footer','genesis_footer','genesis_after_footer','genesis_after' );
	$b = array( 'Before Content','Before Header','Inside Header','Before Site Title','Site Title Area','After Site Title','Site Description Area','Right Side of Header','After Header','Before Content and Sidebar','Before Content Column','Before Content in Loop','Loop','Before Post','Before Post Title','Post Title','After Post Title','Before Post Content','Post Content','After Post Content','After Post','After Sidebar Widget Area','After Sidebar Alt Widget Area','Before Primary Sidebar Widget Area','Before Sidebar Alt Widget Area','Before Entry','Entry Header','Entry Content','Entry Footer','After Entry','After Endwhile','After Content in Loop','After Content','After Primary Sidebar Widget Area','Before Footer','Inside Footer','After Footer','After Full Page' );
	?>
	<div class="wrap">
		<?php if( isset( $_GET['settings-updated'] ) ) { ?>
			<div id="message" class="updated">
				<p><strong><?php _e( 'Settings saved.', 'genesis-featured-image-header' ); ?></strong></p>
			</div>
		<?php } ?>
		<div class="postbox">
		<h2><?php _e( 'Genesis Featured Image Header Settings and Help', 'genesis-featured-image-header' ); ?></h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field( 'update-options' ); ?>
			<?php settings_fields( 'gfih_featured_image_settings' ); ?>
			<p><?php _e( 'This plugin will allow you to insert an image on any Custom Post Type archive page you have on your site. Do you want it to also insert a featured image on regular WordPress pages, posts, archives, etc.?', 'genesis-featured-image-header' ); ?></p>
			<p><?php _e( 'Check to use on pages also:', 'genesis-featured-image-header');?> <input type="checkbox" id="gfih_on_pages" name="gfih_on_pages" value="yes" <?php if ( get_option( 'gfih_on_pages' ) == 'yes' ) { echo 'checked'; } ?> /></p>
			<?php if ( function_exists( 'is_woocommerce' ) ) { ?>
				<p><?php _e( 'Check to use on WooCommerce Product Pages:', 'genesis-featured-image-header' );?> <input type="checkbox" id="gfih_on_product" name="gfih_on_product" value="yes" <?php if ( get_option( 'gfih_on_product' ) == 'yes' ) { echo 'checked'; } ?> /></p>
			<?php } ?>
			<p><?php _e( 'Choose where you would like to have your featured image positioned on your pages. The setting you choose here will move all of the images set by this plugin to the location selected.', 'genesis-featured-image-header' ); ?></p>
			<p>
				<select name="gfih_action_location">
					<?php foreach ( $a as $index => $v ) {
						if ( function_exists( $v ) ) { ?>
							<option value="<?php echo $v; ?>" <?php if ( get_option( 'gfih_action_location' ) == $v ) { echo 'selected'; } ?>><?php echo $b[$index]; ?> (<?php echo $v; ?>)</option>
						<?php }
					} ?>
				</select>
			</p>
			<input type="hidden" name="action" value="update" /><?php submit_button(); ?>
		</form>
		<h2><?php _e( 'Need Help?', 'genesis-featured-image-header' ); ?></h2>
			<p><strong><?php _e( 'How do I set the featured image on a custom post type archive page?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'If your custom post type is called "Books", you can navigate to the Books > Featured Image page on the admin menu. Once there, you can use the upload button to upload a new image or select an existing image from your site\'s Media Library.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'How do I unset the archive page image?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'Simply follow the steps above, but instead of clicking the upload image button simply delete the URL in the text box and click save changes.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'Can I use this with WooCommerce?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'Yes, however you can only set one image for all WooCommerce pages (store, individual product pages, product category pages, and product tag pages). This is done by navigating to Products > Featured Image in your admin section. The cart and checkout pages are regular WordPress pages, and can have different images from the rest of the store if you want.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'How do I set a featured image for individual pages?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'First, make sure the checkbox above on this page is checked and that you clicked Save Changes. Then on the page edit screen, simply click the Set Featured Image link. If you do not see this option, click Screen Options at the top of the page and check the box next to Featured Image. If you do not want to have a featured image on a certain page, simply do not add one and nothing will show up.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'My pages already show the featured image someplace else now it is showing twice. How can I get rid of that?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'If you already have a featured image but this plugin is making it show up in another location, simply uncheck the box above and your pages will no longer use this plugin\'s settings. You can still continue to use the plugin for custom post type archives.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'Is there a "global" featured image setting?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'No. This plugin is designed to allow you to set a different image for each page and custom post type archive. If you want the same image for each page, you can still use this plugin. Simply use the same image for each page. If you have too many pages to set manually, you may want to look into adding the image another way.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'I need help identifying the featured image locations.', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'The locations in the dropdown menu above are more or less in a top to bottom order (some locations are next to each other). If you are working on a site that is not live, you could simply try out the different locations to see where they will show up on your site. There are also some great guides available that will help you identify where these locations are on your site. One is the ', 'genesis-featured-image-header'); ?><a href="http://www.shareasale.com/r.cfm?u=947944&b=241369&m=28169&afftrack=&urllink=my%2Estudiopress%2Ecom%2Fdocs%2Fhook%2Dreference%2F"><?php _e('StudioPress Hook Reference guide', 'genesis-featured-image-header'); ?></a> <?php _e(' (for StudioPress customers only). Another reference is the ', 'genesis-featured-image-header'); ?><a href="http://genesistutorials.com/visual-hook-guide/"><?php _e('Visual Hook Guide from Genesis Tutorials.', 'genesis-featured-image-header'); ?></a> <?php _e('This tutorial helps you visualize where the image will show up. If you want to see the same visualization on your own site, check out the', 'genesis-featured-image-header'); ?> <a href="https://wordpress.org/plugins/genesis-visual-hook-guide/"><?php _e('Genesis Visual Hook Guide Plugin.', 'genesis-featured-image-header' ); ?></a></p>
			<p><strong><?php _e( 'What are the dimensions of the images I should be using?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'This will depend on a few factors. Each theme may have slightly larger or smaller widths or heights for each location where your image will show up. This answer will vary based on each different Genesis child theme. Also, each location that you can choose from to insert your image has different dimensions. So not only will the answer vary based on the theme you are using, it will also vary depending on the location where you are inserting the image. The plugin will insert the full size image that you upload (no cropping is done). If you want exact dimensions, check out the Genesis Visual Hook Guide Plugin (see link above). When viewing the page use the Action Hooks option then right click the area you want to get the width for and click "Inspect Element". In many browsers this will pop up the dimensions of the area you are looking at.', 'genesis-featured-image-header' ); ?></p>
			<p><strong><?php _e( 'The featured image is not showing up correctly or at all. What can I do?', 'genesis-featured-image-header' ); ?></strong><br />
			<?php _e( 'This plugin is set to show an image on the following post types: custom post type archive pages, regular WordPress pages, regular WordPress posts, and WooCommerce shop pages. Some plugins may use different templates or methods for calling their pages so this plugin may not work on all pages. Please let us know of any pages that are not working correctly in the WordPress support forum.', 'genesis-featured-image-header' ); ?></p>
		</div>
	</div>
<?php }
// Add Media Uploader Script
function gfih_image_upload_script() {
	$post_types = gfih_get_cpts();
	foreach ( $post_types  as $post_type ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'gfih-'. $post_type . '-image-settings' ) {
			wp_enqueue_media();
			wp_enqueue_script('jquery');
			/*wp_register_script('image-upload-js', plugins_url( 'js/image-upload.js', __FILE__ ), array('jquery'));
			wp_enqueue_script('image-upload-js');*/
		}
	}
}
add_action( 'admin_enqueue_scripts', 'gfih_image_upload_script' );
?>