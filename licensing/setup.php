<?php
/**
 * Prevent direct access to the rest of the file.
 */
defined( 'ABSPATH' ) || exit( 'WP absolute path is not defined.' );


/**
 * The starter class.
 *
 * @author EDD SL Quick Plugin Starter
 * @author https://esqps.com
 */
class EDD_SL_Quick_Plugin_Starter {


	/********************************************************************************
	 * General definitions.
	 */
	private $_prefix,
			$_index,
			$_root,
			$_root_url,
			$_website_url,
			$_product_data,
			$_admin_data,
			$_validation_data;


	/********************************************************************************
	 * Constructor.
	 */
	public function __construct( $product_args = [], $admin_args = [], $validation_args = [], $index = '' ) {

		// Run tests to make sure we aren't wasting time, and exit program if bad
		$this->_test_defaults( $product_args, $admin_args, $validation_args, $index );

		// Set plugin pathing options
		$this->_prefix      = $admin_args['Prefix'];
		$this->_index       = $index;
		$this->_root        = plugin_dir_path( $this->_index );
		$this->_root_url    = plugin_dir_url( $this->_index );
		$this->_website_url = get_home_url();

		// Set product data
		$this->_product_data['url']        = $product_args['Website'];
		$this->_product_data['name']       = $product_args['Name'];
		$this->_product_data['product_id'] = $product_args['ID'];
		$this->_product_data['version']    = $product_args['Version'];

		// Set admin data
		$this->_admin_data['menu_title']          = $admin_args['Menu Title'];
		$this->_admin_data['capability']          = $admin_args['User Capability'];
		$this->_admin_data['is_top_level']        = $admin_args['Top Level Menu'];
		$this->_admin_data['merge_pages']         = $admin_args['License on Home'];
		$this->_admin_data['use_nav_tabs']        = $admin_args['Use Tabs'];
		$this->_admin_data['include_system_info'] = $admin_args['Include System Info'];

		// Icon URL auto-sets to null if not included
		$this->_admin_data['icon_url'] = isset( $admin_args['Icon URL'] ) ? $admin_args['Icon URL'] : '';

		// Only add parent slug if available
		if ( isset( $admin_args['Parent Slug'] ) && $admin_args['Parent Slug'] !== '' ) {
			$this->_admin_data['parent_slug'] = $admin_args['Parent Slug'];
		}

		// Only add menu position if available
		if ( isset( $admin_args['Menu Position'] ) && $admin_args['Menu Position'] !== null ) {
			$this->_admin_data['menu_position'] = $admin_args['Menu Position'];
		}

		// Validation data
		$this->_validation_data['use_remote_on_init']       = $validation_args['Use Remote on Init'];
		$this->_validation_data['check_remote_on_shutdown'] = $validation_args['Check Remote on Shutdown'];

		// Create admin helper instance
		if ( ! class_exists( 'EDD_SL_Quick_Plugin_Starter_Admin_Helper' ) ) {
			require_once $this->_root . 'admin/class.helper.php';
		}

		$this->admin = new EDD_SL_Quick_Plugin_Starter_Admin_Helper(
			$this->_prefix,
			$this->_admin_data['capability']
		);

		// Run the plugin updater
		add_action( 'admin_init', [ $this, 'plugin_updater'] );

		// Create default plugin admin pages
		// Use priority 999 to ensure it's loaded later, just in case
		add_action( 'admin_menu', [ $this, 'create_default_admin_pages' ], 999 );

		// Register default admin settings
		add_action( 'admin_init', [ $this, 'register_default_admin_settings' ] );

		// Register license setting
		add_action( 'admin_init', [ $this, 'register_license_setting' ] );

		// Add license actions
		add_action( 'admin_init', [ $this, 'activate_license' ] );
		add_action( 'admin_init', [ $this, 'deactivate_license' ] );
		add_action( 'admin_init', [ $this, 'clear_license' ] );
		add_action( 'admin_notices', [ $this, 'license_notices' ] );

		// Admin styles and scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
	}


	/********************************************************************************
	 * Defaults testing.
	 */
	private function _test_defaults( $product_args = [], $admin_args = [], $validation_args = [], $index = '' ) {

		/**
		 * Required product arguments
		 */
		if ( empty( $product_args ) ) {
			exit( 'Product Arguments cannot be empty.' );
		}

		if ( ! isset( $product_args['Website'] ) || $product_args['Website'] === '' ) {
			exit( 'Website must be set in Product Arguments.');
		}

		if ( ! isset( $product_args['Name'] ) || $product_args['Name'] === '' ) {
			exit( 'Name must be set in Product Arguments.');
		}

		if ( ! isset( $product_args['ID'] ) || $product_args['ID'] === '' ) {
			exit( 'ID must be set in Product Arguments.');
		}

		if ( ! isset( $product_args['Version'] ) || $product_args['Version'] === '' ) {
			exit( 'Version must be set in Product Arguments.');
		}


		/**
		 * Required admin arguments
		 */
		if ( empty( $admin_args ) ) {
			exit( 'Admin Arguments cannot be empty.' );
		}

		if ( ! isset( $admin_args['Prefix'] ) || $admin_args['Prefix'] === '' ) {
			exit( 'Prefix must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['Menu Title'] ) || $admin_args['Menu Title'] === '' ) {
			exit( 'Menu Title must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['User Capability'] ) || $admin_args['User Capability'] === '' ) {
			exit( 'User Capability must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['Top Level Menu'] ) || $admin_args['Top Level Menu'] === '' ) {
			exit( 'Top Level Menu must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['License on Home'] ) || $admin_args['License on Home'] === '' ) {
			exit( 'License on Home must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['Icon URL'] ) ) {
			exit( 'Icon URL can be \'\', but must be set in Admin Arguments.');
		}

		if ( ! isset( $admin_args['Use Tabs'] ) ) {
			exit( 'Use Tabs must be set to true or false in Admin Arguments.');
		}


		/**
		 * Special test for parent slug
		 */
		if ( isset( $admin_args['Top Level Menu'] ) && $admin_args['Top Level Menu'] === false && ( ! isset( $admin_args['Parent Slug'] ) || $admin_args['Parent Slug'] === '' ) ) {
			exit( 'Parent Slug must be set in Admin Arguments because Top Level Menu is false.');
		}


		/**
		 * Special test for menu position
		 */
		if ( isset( $admin_args['Top Level Menu'] ) && $admin_args['Top Level Menu'] === true && ( ! isset( $admin_args['Menu Position'] ) || $admin_args['Menu Position'] === null ) ) {
			exit( 'Menu Position must be set in Admin Arguments because Top Level Menu is true.');
		}


		/**
		 * Required validation arguments
		 */
		if ( empty( $validation_args ) ) {
			exit( 'Admin Arguments cannot be empty.' );
		}

		if ( ! isset( $validation_args['Use Remote on Init'] ) ) {
			exit( 'Use Remote on Init must be set to true or false in Validation Arguments.');
		}

		if ( ! isset( $validation_args['Check Remote on Shutdown'] ) ) {
			exit( 'Check Remote on Shutdown must be set to true or false in Validation Arguments.');
		}


		/**
		 * Required index, which shouldn't have been touhed
		 */
		if ( $index === '' ) {
			exit ( 'Index has been removed. Put it back!' );
		}
	}


	/********************************************************************************
	 * General getters.
	 */

	/**
	 * Return the plugin's prefix.
	 *
	 * This is the path of the root plugin.php file.
	 *
	 * @return string
	 */
	public function get_plugin_prefix() {

		return $this->_prefix;
	}


	/**
	 * Return the plugin's index.
	 *
	 * This is the path of the root plugin.php file.
	 *
	 * @return string
	 */
	public function get_plugin_index() {

		return $this->_index;
	}


	/**
	 * Return the plugin directory's root.
	 *
	 * This returns a string directory path with a trailing slash.
	 *
	 * @return string
	 */
	public function get_plugin_root() {

		return $this->_root;
	}


	/**
	 * Return the plugin's root URI.
	 *
	 * This returns a string path as a URL with a trailing slash.
	 *
	 * @return string
	 */
	public function get_plugin_root_url() {

		return $this->_root_url;
	}


	/**
	 * Return the plugin's product information.
	 *
	 * This is set in the Arguments modified in the root plugin.php file.
	 *
	 * @param string
	 * @return string
	 */
	public function get_product_info( $data_request = '' ) {

		// Skip bad entries
		// Data doesn't exist || Data is still null || Data is incorrectly requested
		if ( ! $data_request || $data_request === '' /* || ! in_array( $data_request, $this->_product_data ) */ ) {
			return false;
		}

		// Return the result
		return $this->_product_data[$data_request];
	}


	/**
	 * Return the plugin's admin information.
	 *
	 * This is set in the Arguments modified in the root plugin.php file.
	 *
	 * @param string
	 * @return string
	 */
	public function get_admin_info( $data_request = '' ) {

		// Skip bad entries
		// Data doesn't exist || Data is still null || Data is incorrectly requested
		if ( ! $data_request || $data_request === '' /* || ! in_array( $data_request, $this->_product_data ) */ ) {
			return false;
		}

		// Return the result
		return $this->_admin_data[$data_request];
	}


	/********************************************************************************
	 * Default admin page creation with settings.
	 */

	/**
	 * Create the default pages.
	 */
	public function create_default_admin_pages() {

		// If top level
		if ( $this->_admin_data['is_top_level'] ) {

			add_menu_page(
				$this->_product_data['name'],			// Page title
				$this->_admin_data['menu_title'],		// Menu title
				$this->_admin_data['capability'],		// Minimum privilege
				$this->_prefix . '_home',				// Home slug
				[ $this, 'get_home_page' ],				// Callback
				$this->_admin_data['icon_url'],			// Icon
				$this->_admin_data['menu_position']		// Position
			);

			// Add Home submenu link
			$this->admin->register_page(
				'Home',
				'home',
				[ $this, 'get_home_page' ]
			);
		}

		// If sub level
		else {

			add_submenu_page(
				$this->_admin_data['parent_slug'],				// Parent slug
				$this->_product_data['name'],					// Page title
				$this->_admin_data['menu_title'],				// Menu title
				$this->_admin_data['capability'],				// Minimum privilege
				$this->_prefix . '_home',	// Menu slug
				[ $this, 'get_home_page' ],						// Callback
				$this->_admin_data['menu_position']				// Position
			);

			register_setting( $this->_prefix . '_home', $this->_prefix . '_home' );
		}

		// If not merging home and license, register separately
		if ( ! $this->_admin_data['merge_pages'] ) {

			$this->admin->register_page(
				'License',
				'license_key',
				[ $this, 'get_license_page' ]
			);
		}
	}


	/**
	 * Register the default settings for admin, specifically for license page
	 *
	 * Not used in most cases, but here just in case.
	 */
	public function register_default_admin_settings() {}


	/**
	 * Quick call for tabs.
	 *
	 * This is used when the home page is a submenu item and can be amended within
	 * a custom action. The action is prefix_navigation_tabs.
	 */
	public function get_navigation_tabs() {

		if ( $this->_admin_data['use_nav_tabs'] ) {

			?>
			<div class="nav-tab-wrapper">
				<a href="?page=<?php echo $this->_prefix; ?>_home" class="nav-tab">Home</a>
				<?php

				if ( ! $this->_admin_data['merge_pages'] ) {

					?>
					<a href="admin.php?page=<?php echo $this->_prefix; ?>_license_key" class="nav-tab">License</a>
					<?php

					if ( $this->_admin_data['include_system_info'] ) {
						?>
						<a href="admin.php?page=<?php echo $this->_prefix; ?>_system_info" class="nav-tab">System</a>
						<?php
					}

				}

				// This is where more tabs can be added from the plugin interface!
				do_action( $this->_prefix . '_navigation_tabs' );

				?>
			</div>
			<?php
		}
	}


	/**
	 * Admin home page callback.
	 */
	public function get_home_page() {

		// Allow prefix in included files
		$prefix = $this->_prefix;

		// Start the page wrapper
		echo '<div class="wrap">';

			// If the home and license pages are not merged, include tabs
			$this->get_navigation_tabs();

			// Get the homepage
			include_once $this->_root . 'admin/home.php';

			// .. And add the licensing below if merge is requested
			if ( $this->_admin_data['merge_pages'] ) {

				include_once $this->get_plugin_root() . 'admin/license.php';

				if ( $this->_admin_data['include_system_info'] ) {
					include_once $this->get_plugin_root() . 'admin/system.php';
				}
			}


		// End the page wrapper
		echo '</div>';
	}


	/**
	 * Admin license page callback.
	 */
	public function get_license_page() {

		// Allow prefix in included files
		$prefix = $this->_prefix;

		// Start the page wrapper
		echo '<div class="wrap">';

			// If the home and license pages are not merged, include tabs
			$this->get_navigation_tabs();

			// Get the license page
			include_once $this->get_plugin_root() . 'admin/license.php';

		// End the page wrapper
		echo '</div>';
	}


	/**
	 * Run the updater.
	 */
	public function plugin_updater() {

		if ( ! class_exists( 'EDD_SL_Quick_Plugin_Starter_Plugin_Updater' ) ) {
			require_once 'updater.php';
		}

		// retrieve our license key from the DB
		$license_key = trim( get_option( $this->_prefix . '_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Quick_Plugin_Starter_Plugin_Updater(
			$this->_product_data['url'],
			$this->_index,
			array(
				'version' => $this->_product_data['version'],
				'license' => $license_key,
				'item_id' => $this->_product_data['product_id'],
				'author'  => 'Ukuwi',
				'beta'    => false,
			)
		);
	}


	/********************************************************************************
	 * License validation.
	 */

	/**
	 * Tests the license via the database.
	 *
	 * When the license is first activated via remote validation, several entries
	 * will be added to the database for swift management. This prevents the need to
	 * connect remotely every time the plugin is loaded to check for live validity.
	 */
	public function check_license_locally() {

		$license_key = get_option( $this->_prefix . '_license_key' );
		$license_status = get_option( $this->_prefix . '_license_status' );

		if ( $license_key && $license_status == 'valid' ) {
			return true;
		}

		return false;
	}


	/**
	 * Tests the license remotely.
	 *
	 * Reaches out to the plugin server to verify the live license validity. Further,
	 * if the live license returns invalid, but the database suggests it is valid,
	 * this will update the database and render it all as invalid, thus resetting the
	 * plugin's valid credentials.
	 *
	 * Personally, I like to run this at the very end - after all other scripts have
	 * run - using late hooks. This will allow the program to run smoothly once
	 * through if assumed valid via DB, then update the DB credentials to invalid if
	 * remotely suggests. Then, on next run, the program will cease until the remote
	 * test performs a valid verification. This helps to lock up those who paid once,
	 * entered the license key, got the DB credentials required, then requested a
	 * refund to game the system.
	 */
	public function check_license_remotely() {

		// If the license key doesn't exist in the database, no sense running validation
		if ( $license_key = get_option( $this->_prefix . '_license_key' ) ) {

			/**
			 * Run the remote license check
			 */
			global $wp_version;

			$license = trim( $license_key );

			$api_params = [
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->_product_data['name'] ),
				'url'        => $this->_website_url
			];

			// Call the custom API.
			$response = wp_remote_post(
				$this->_product_data['url'],
				[
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				]
			);

			// If no response, return early
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// .. Otherwise, decode the data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// What is the result of the validity test?
			$remote_validity = $license_data->license == 'valid' ? true : false;

			/**
			 * If not valid with remote connection, but status exists in DB, then destroy the records
			 * This is a failsafe for developers who use products on client websites but forget to 
			 * remove their license keys. With this, they can deactivate sites from their accounts and
			 * the license will be revoked on those sites.
			 *
			 * This also checks whether the status in the DB exists at all because if so, it means that
			 * at one point it probably was valid, but now is not (for whatever reason).
			 */
			if ( ! $remote_validity && get_option( $this->_prefix . '_license_status' ) ) {

				// Update db with invalid credentials, or delete them altogether
				delete_option( $this->_prefix . '_license_key' );
				delete_option( $this->_prefix . '_license_status' );
			}

			// It's valid remotely annd the license key exists in the database, which also means it
			// is the correct license key, since it was sent to the server for validation. In this
			// case, update the status to valid, which is the same as activating it.
			elseif ( $remote_validity && get_option( $this->_prefix . '_license_key' ) ) {

				// Update db with valid credentials
				update_option( $this->_prefix . '_license_status', $license_data->license );
			}

			// Return the $remote_validity, as it will always be the correct response
			return $remote_validity;
		}
	}


	/**
	 * Register license option.
	 *
	 * @since 1.0.2
	 */
	public function register_license_setting() {

		register_setting(
			$this->_prefix . '_license_key',
			$this->_prefix . '_license_key',
			[ $this, 'sanitize_license' ]
		);
	}


	/**
	 * Activate license key.
	 *
	 * @since 1.0.2
	 */
	public function activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[$this->_prefix . '_license_activate'] ) ) {

			$redirect_suffix = $this->_admin_data['merge_pages'] ? '_home' : '_license_key';

			// run a quick security check
		 	if ( ! check_admin_referer( $this->_prefix . '_nonce', $this->_prefix . '_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			if ( isset( $_POST[$this->_prefix . '_license_key'] ) && $_POST[$this->_prefix . '_license_key'] !== '' )  {
				update_option( $this->_prefix . '_license_key', $_POST[$this->_prefix . '_license_key'] );
			}

			else {
				return;
			}

			// retrieve the license from the database
			$license = trim( get_option( $this->_prefix . '_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->_product_data['name'] ), // the name of our product in EDD
				'url'        => $this->_website_url
			);

			// Call the custom API.
			$response = wp_remote_post(
				$this->_product_data['url'],
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				}

				else {
					$message = __( 'An error occurred, please try again.' );
				}
			}

			else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'disabled' :
						case 'revoked' :

							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $this->_product_data['name'] );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default :

							$message = __( 'An error occurred, please try again.' );
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {

				$base_url = admin_url( 'admin.php?page=' . $this->_prefix . $redirect_suffix );

				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );

				exit();
			}

			// Either "valid" or "invalid"
			update_option( $this->_prefix . '_license_status', $license_data->license );
			wp_redirect( admin_url( 'admin.php?page=' . $this->_prefix . $redirect_suffix ) );

			exit();
		}
	}


	/**
	 * Deactivate license key.
	 */
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST[$this->_prefix . '_license_deactivate'] ) ) :

			$redirect_suffix = $this->_admin_data['merge_pages'] ? '_home' : '_license_key';

			// run a quick security check
		 	if( ! check_admin_referer( $this->_prefix . '_nonce', $this->_prefix . '_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( $this->_prefix . '_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->_product_data['name'] ), // the name of our product in EDD
				'url'        => $this->_website_url
			);

			// Call the custom API.
			$response = wp_remote_post(
				$this->_product_data['url'],
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params
				)
			);

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) :

				if ( is_wp_error( $response ) ) :
					$message = $response->get_error_message();

				else :
					$message = __( 'An error occurred, please try again.' );

				endif;

				$base_url = admin_url( 'admin.php?page=' . $this->_prefix . $redirect_suffix );

				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );

				exit();
			endif;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( $this->_prefix . '_license_status' );
				delete_option( $this->_prefix . '_license_key' );

				// In case there are transients
				if ( get_option( $this->_prefix . '_api_data' ) ) :

					delete_option( '_transient_' . $this->_prefix . '_api_data' );
					delete_option( '_transient_timeout_' . $this->_prefix . '_api_data' );

				endif;
			}

			wp_redirect( admin_url( 'admin.php?page=' . $this->_prefix . $redirect_suffix ) );

			exit();
		endif;
	}


	/**
	 * Clear the license from the database.
	 */
	public function clear_license() {

		// listen for our clear button to be clicked
		if ( isset( $_POST[$this->_prefix . '_license_clear'] ) ) :

			delete_option( $this->_prefix . '_license_status' );
			delete_option( $this->_prefix . '_license_key' );

			// In case there are transients
			if ( get_option( '_transient_timeout_' . $this->_prefix . '_api_data' ) ) :

				delete_option( '_transient_' . $this->_prefix . '_api_data' );
				delete_option( '_transient_timeout_' . $this->_prefix . '_api_data' );

			endif;

			$redirect_suffix = $this->_admin_data['merge_pages'] ? '_home' : '_license_key';

			wp_redirect( admin_url( 'admin.php?page=' . $this->_prefix . $redirect_suffix ) );

			exit();
		endif;
	}


	/**
	 * Admin notices for license.
	 */
	public function license_notices() {

		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) :

			switch( $_GET['sl_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );

					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php

					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;
			}
		endif;
	}


	/**
	 * Sanitize license.
	 */
	public function sanitize_license( $new ) {

		$old = get_option( $this->_prefix . '_license_key' );

		if ( $old && $old != $new )
			delete_option( $this->_prefix . '_license_status' ); // new license has been entered, so must reactivate

		return $new;
	}


	/**
	 * Styles and scripts
	 */
	public function enqueue_admin_scripts( $hook ) {

		// Check to make sure the prefix is included in the page hook
		// to prevent loading these files on other admin pages.
		if ( strpos( $hook, $this->_prefix ) ) :
			
			wp_enqueue_script(
				$this->_prefix . '-admin-scripts',
				$this->_root_url . 'admin/scripts.js',
				[],
				$this->_product_data['version']
			);

			wp_enqueue_style(
				$this->_prefix . '-admin-styles',
				$this->_root_url . 'admin/styles.css',
				[],
				$this->_product_data['version']
			);

		endif;
	}


	/********************************************************************************
	 * Initialize the licensing validation methods.
	 *
	 * Returns true if license is valid. Returns false if license is either not
	 * activated or invalid.
	 *
	 * @return bool
	 */
	public function init() {

		/**
		 * Right before the entire WP program shuts down, check validation remotely
		 * and update the database credentials with the real validity. Helps to
		 * prevent those who wish to take advantage of the payment/refund system.
		 */
		if ( $this->_validation_data['check_remote_on_shutdown'] )
			add_action( 'shutdown', [ $this, 'check_license_remotely' ] );


		// Return the validity, as requested
		// If not valid, the plugin will not initialize and nothing further will run.
		return $this->_validation_data['use_remote_on_init'] ? $this->check_license_remotely() : $this->check_license_locally();
	}

}// End of EDD_SL_Quick_Plugin_Starter class
