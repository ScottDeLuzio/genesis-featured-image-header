<?php
if ( !defined( 'ABSPATH' ) ) {
	die( "Sorry, you are not allowed to access this page directly." );
}
if( !class_exists( 'AmplifyPluginsSuggestions' ) ){
	/**
	 * Amplify Plugins suggested plugins class
	 * @version 1.0
	 */
	class AmplifyPluginsSuggestions {

		/**
		 * Initialize
		 * @since 1.0
		 */
		public static function init(){
			add_action( 'admin_init', array( __CLASS__, 'add_woocommerce_suggestions' ) );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_suggestions_meta_box' ) );
		}

		/**
		 * Add Suggestions to WooCommerce Marketplace Suggestions
		 * @since 1.0
		 */
		public static function add_woocommerce_suggestions(){
			if( class_exists( 'WooCommerce' ) ){
				global $woocommerce;
				// We don't want to continue if running a version of WooCommerce under 3.6
				if( version_compare( $woocommerce->version, '3.6', '<' ) ){
					return;
				}
				$suggestion_transient = get_transient( 'amplify_plugins_woo_suggestions_update' );
				if( !$suggestion_transient ){
					// Get existing WooCommerce suggestions
					$data = get_option( 'woocommerce_marketplace_suggestions', array() );
					$suggestions = self::get_amplify_plugins_suggestions();
					if( $suggestions ){
						// Remove any of our suggestions before adding them back to update any data.
						$data['suggestions'] = self::reset_amplify_plugins_suggestions( $data['suggestions'], $suggestions );
						// Maybe add suggestions depending on if certain plugins are active.
						foreach( $suggestions as $plugin ){
							switch ( $plugin['slug'] ) {
								case 'product-edit-conditional-checkout-fields':
									if( !defined( 'CWCFP_PLUGIN' ) ){
										array_unshift( $data['suggestions'], $plugin );
									}
									break;

								case 'product-edit-quick-checkout':
									if( !defined( 'WQC_PLUGIN_FILE' ) ){
										array_unshift( $data['suggestions'], $plugin );
									}
									break;

								case 'product-edit-full-screen-background-images':
									if( is_plugin_active( 'simple-full-screen-background-image/simple-full-screen-background.php' ) ){
										array_unshift( $data['suggestions'], $plugin );
									}
									break;

								case 'product-edit-wp1099-affiliate':
								case 'product-edit-wp1099-vendor':
									if( !defined( 'WP_TEN_NINETY_NINE' ) ){
										array_unshift( $data['suggestions'], $plugin );
									}
									break;

								default:
									array_unshift( $data['suggestions'], $plugin );
									break;
							}
						}

						// Clean up duplicates, which there shouldn't be any of at this point.
						$data['suggestions'] = self::search_woo_marketplace( $data['suggestions'], 'slug' );
						// Update marketplace suggestions
						update_option( 'woocommerce_marketplace_suggestions', $data );
						// Set transient to prevent updating more than once per day.
						set_transient( 'amplify_plugins_woo_suggestions_update', time(), DAY_IN_SECONDS );
					}
				}
			}
		}

		/**
		 * Add suggestions meta boxes.
		 * @since 1.0
		 */
		public static function add_suggestions_meta_box(){
			// Suggestions are only displayed if user can install plugins.
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			$single_suggestion = self::get_one_amplify_plugins_suggestion();

			// Make sure we have a suggestion to offer.
			if( empty( $single_suggestion ) || !array( $single_suggestion ) ){
				return false;
			}

			// Get all post types as objects
			$post_types = get_post_types(
				array(
					'show_ui' => true
				),
				'objects'
			);

			// Loop through each post type
			foreach( $post_types as $post_type ) {
				// This is to make sure the meta box is not added to certain post types
				$exclude = array(
					'attachment',
					'menu',
					'modals'
				);

				// Check if WooCommerce is active
				if( class_exists( 'WooCommerce' ) ){
					global $woocommerce;
					// We don't want to continue adding a metabox to WooCommerce product edit screens above 3.6
					if( version_compare( $woocommerce->version, '3.6', '>=' ) ){
						$exclude[] = 'product';
					}
					// We never want to add a metabox to the orders, coupons, webhooks, product visibility, or product variations.
					$exclude[] = 'shop_order';
					$exclude[] = 'shop_coupon';
					$exclude[] = 'shop_webhook';
					$exclude[] = 'product_visibility';
					$exclude[] = 'product_variation';
				}

				if( !in_array( $post_type->name, $exclude ) ) {
					add_meta_box(
						'amplify-plugins-meta-box',
						$single_suggestion['title'],
						array( __CLASS__, 'show_meta_box' ),
						$post_type->name,
						'side',
						'high'
					);
				}
			}
		}

		/**
		 * Retrieve one random suggestion
		 * @since 1.0
		 * @return array
		 */
		public static function get_one_amplify_plugins_suggestion(){
			$random_suggestion	= get_transient( 'amplify_plugins_random_suggestion' );
			if( !$random_suggestion ){
				$suggestions		= get_transient( 'amplify_plugins_suggestions' );
				// If no suggestions are stored or $suggestions is not an array return an empty array.
				if( !$suggestions || !is_array( $suggestions ) ){
					return array(); // empty() == true
				}

				// Select a single random suggestion from the array of suggestions.
				$random_key			= array_rand( $suggestions, 1 );

				// Some suggestions we only want to show if another dependent plugin is active.
				if( isset( $suggestions[$random_key]['show-if-active'] ) ){
					$is_active				= array();
					$active_plugin_slugs	= array_map( 'dirname', get_option( 'active_plugins' ) );
					// Loop through until we get a plugin that matches the show if active condition.
					do{
						foreach( $suggestions[$random_key]['show-if-active'] as $active_check ){
							if( in_array( $active_check, $active_plugin_slugs ) ){
								$is_active[] = true;
							}
						}
					} while ( !in_array( true, $is_active ) );
				}
				$random_suggestion	= $suggestions[$random_key];
				set_transient( 'amplify_plugins_random_suggestion', $random_suggestion, HOUR_IN_SECONDS );
			}

			return $random_suggestion;
		}

		/**
		 * Show the content of the suggestion metabox
		 * @since 1.0
		 */
		public static function show_meta_box() {
			$post		= get_post();
			$suggestion	= self::get_one_amplify_plugins_suggestion();
			$url		= $suggestion['url'];
			$url		= str_replace( 'utm_medium=wc-suggestions', 'utm_medium=metabox', $url );
			$obj		= get_post_type_object( $post->post_type );
			$pt_name	= $obj->labels->singular_name;
			$copy		= $suggestion['copy'];
			$title		= $suggestion['title'];

			echo '<div>' . $copy . '</div>';
			echo '<div style="margin-top:10px;"><a target="_blank" class="button button-primary" href="' . $url . '">' . __( 'Get ' ) . $title . '</a></div>';
		}

		/**
		 * Make sure WooCommerce Marketplace Suggestions are returning unique values
		 * @since 1.0
		 */

		public static function search_woo_marketplace( $array, $key ){
			$temp_array = array();
			$i = 0;
			$key_array = array();

			foreach( $array as $val ) {
				if ( !in_array( $val[$key], $key_array ) ) {
					$key_array[$i]	= $val[$key];
					$temp_array[$i]	= $val;
				}
				$i++;
			}
			return $temp_array;
		}

		/**
		 * Reset Amplify Plugins suggestions
		 * @since 1.0
		 */
		public static function reset_amplify_plugins_suggestions( $array, $body ){
			$suggestions = array();
			foreach( $body as $slug ){
				$suggestions[] = $slug['slug'];
			}
			foreach( $array as $key => $value ){
				if( in_array( $value['slug'], $suggestions ) ){
					unset( $array[$key] );
				}
			}
			return $array;
		}

		/**
		 * Retrieve Amplify Plugin suggestions remotely
		 * @since 1.0
		 * @return array of suggestions from Amplify Plugins
		 */
		public static function get_amplify_plugins_suggestions(){
			$suggestions = get_transient( 'amplify_plugins_suggestions' );
			if( !$suggestions ){
				// Retrieve Amplify Plugins suggestions
				$url		= 'https://amplifyplugins.com/wp-content/uploads/suggestions/suggestions.json';
				$request	= wp_safe_remote_get( $url );

				// Error checking
				if ( is_wp_error( $request ) ) {
					// If there is an error, reschedule transient for one day to check back again.
					set_transient( 'amplify_plugins_suggestions', array(), DAY_IN_SECONDS );
					return;
				}

				$body = wp_remote_retrieve_body( $request );
				if ( empty( $body ) ) {
					// If the $body is empty, reschedule transient for one day to check back again.
					set_transient( 'amplify_plugins_suggestions', array(), DAY_IN_SECONDS );
					return;
				}

				$body = json_decode( $body, true );
				if ( empty( $body ) || ! is_array( $body ) ) {
					// If the $body is empty or not an array, reschedule transient for one day to check back again.
					set_transient( 'amplify_plugins_suggestions', array(), DAY_IN_SECONDS );
					return;
				} else {
					// Sets a transient to only check once every week so this doesn't run constantly.
					set_transient( 'amplify_plugins_suggestions', $body, WEEK_IN_SECONDS );
				}
				// Update $suggestions with the new response or empty array().
				$suggestions = get_transient( 'amplify_plugins_suggestions' );
			}
			return $suggestions;
		}
	}
	AmplifyPluginsSuggestions::init();
}