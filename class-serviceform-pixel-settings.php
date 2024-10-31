<?php
/**
 * Plugin Name: Serviceform Pixel
 * Description: Add Serviceform pixel to your WordPress site quickly.
 * Author: Serviceform
 * Version: 2.0.0
 * License: GPLv3
 *
 * @package WordPress Serviceform Pixel
 */

/*
	Copyright 2020 Serviceform

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Main plugin class.
 *
 * @package WordPress Serviceform Pixel
 * @since   1.0.0
 */
class Serviceform_Pixel_Settings {
	/**
	 * Plugin version.
	 * Used for dependency checks.
	 *
	 * @since 1.0.0
	 */
	const VERSION = '1.0.0';

	/**
	 * The working instance of the plugin.
	 *
	 * @since 1.0.0
	 * @var Serviceform_Pixel_Settings|null
	 */
	private static $instance = null;

	/**
	 * Templates
	 */
	const TEMPLATE_SETTINGS = 'serviceform-pixel-settings';

	/**
	 * The plugin directory path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_dir = '';

	/**
	 * The URL to the plugin directory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_url = '';

	/**
	 * The plugin base name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * The plugin base name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_display_name = 'Serviceform';

	/**
	 * Gets the working instance of the plugin.
	 *
	 * @since 1.0.0
	 * @return Serviceform_Pixel_Settings|null
	 */
	public static function serviceform_get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Serviceform_Pixel_Settings();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Plugin uses Singleton pattern, hence the constructor is private.
	 *
	 * @since 1.0.0
	 * @return Serviceform_Pixel_Settings
	 */
	private function __construct() {
		$this->plugin_dir  = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_name = plugin_basename( __FILE__ );

		// The uninstall hook callback needs to be a static class method or function.
		register_uninstall_hook( $this->plugin_name, array( __CLASS__, 'serviceform_uninstall' ) );

		add_action( 'admin_init', array( $this, 'serviceform_init' ) );

	}

	/**
	 * Adding Plugin to Sidebar.
	 *
	 * Handles the backend settings page integration for Serviceform.
	 *
	 * @since 1.0.0
	 */
	public function serviceform_add_menu() {
		add_menu_page(
			$this->plugin_name,
			$this->plugin_display_name,
			'administrator',
			$this->plugin_name,
			array( $this, 'serviceform_settings' ),
			plugin_dir_url( __FILE__ ) . 'img/logo24.png',
			26
		);

		add_submenu_page(
			$this->plugin_name,
			$this->plugin_display_name,
			'Settings',
			'administrator',
			'serviceform-pixel-settings',
			array( $this, 'serviceform_settings' )
		);
	}

	/**
	 * Loading Pixel settings page.
	 *
	 * Handles the backend settings page integration for Serviceform pixel.
	 *
	 * @since 1.0.0
	 */
	public function serviceform_settings() {
		$this->render(
			self::TEMPLATE_SETTINGS
		);
	}

	/**
	 * Title for the input
	 *
	 * Pixel id input label.
	 *
	 * @since 1.0.0
	 */
	public function serviceform_display_settings() {
		echo '<p>Enter your Serviceform Pixel ID.</p>';
	}

	/**
	 * Populate the pixel ID Input field
	 *
	 * Pixel id input label.
	 *
	 * @since 1.0.0
	 */
	public function serviceform_populate_setting_fields() {
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options.
			'serviceform_general_section',
			// Title to be displayed on the administration page.
			'',
			// Callback used to render the description of the section.
			array( $this, 'serviceform_display_settings' ),
			// Page on which to add this section of options.
			'serviceform_pixel_settings'
		);

		unset( $args );
		$args = array(
			'type'             => 'input',
			'subtype'          => 'text',
			'id'               => 'serviceform_settings_id',
			'name'             => 'serviceform_settings_id',
			'required'         => 'true',
			'get_options_list' => '',
			'value_type'       => 'normal',
			'wp_data'          => 'option',
		);

		add_settings_field(
			'serviceform_settings_id',
			'Serviceform Pixel ID',
			array( $this, 'serviceform_pixel_settings_field' ),
			'serviceform_pixel_settings',
			'serviceform_general_section',
			$args
		);

		register_setting(
			'serviceform_pixel_settings',
			'serviceform_settings_id'
		);

	}

	/**
	 * Populate Settings field based args type.
	 *
	 * Pixel id input label.
	 *
	 * @since 1.0.0
	 * @param array $args The data to pass to the template file.
	 */
	public function serviceform_pixel_settings_field( $args ) {
		$option_name = $args['name'];
		$option_main = $args['wp_data'];
		$post_id     = $args['post_id'];
		$post_name   = $args['name'];
		$args_id     = $args['id'];

		if ( 'option' === $option_main ) {
			$wp_data_value = get_option( $option_name );
		} elseif ( 'post_meta' === $option_main ) {
			$wp_data_value = get_post_meta( $post_id, $post_name, true );
		}

		$value = $wp_data_value;
		echo '<input type="text" id="' . esc_attr( $args_id ) . '" " style="" name="' . esc_attr( $post_name ) . '" value="' . esc_attr( $value ) . '" placeholder="Enter your Pixel ID">';
	}

	/**
	 * Initializes the plugin.
	 *
	 * Register hooks inject Pixel in frontend.
	 * Handles the backend admin page integration.
	 *
	 * @since 1.0.0
	 */
	public function serviceform_init() {
		if ( is_admin() ) {
			$this->serviceform_init_admin();
		} else {
			$this->serviceform_init_frontend();
		}
	}

	/**
	 * Hook callback function for uninstalling the plugin.
	 *
	 * Remove serviceform settings from DB.
	 *
	 * @since 1.0.0
	 */
	public static function serviceform_uninstall() {
		delete_option( 'serviceform_settings_id' );
	}


	/**
	 * Renders a template file.
	 *
	 * The file is expected to be located in the plugin "pages" directory.
	 *
	 * @since 1.0.0
	 * @param string $template The name of the template.
	 */
	public function render( $template ) {
		$file = $template . '.php';
		require $this->plugin_dir . 'pages/' . $file;
	}

	/**
	 * Initializes the plugin admin part.
	 *
	 * Adds a new integration into the WordPress settings structure.
	 *
	 * @since 1.0.0
	 */
	protected function serviceform_init_admin() {
		// Add Serviceform Menu and submenu to Sidebar.
		add_action( 'admin_menu', array( $this, 'serviceform_add_menu' ), 9 );
		add_action( 'admin_init', array( $this, 'serviceform_populate_setting_fields' ) );
	}

	/**
	 * Initializes the plugin frontend part.
	 *
	 * Adds all hooks needed by the plugin in the frontend.
	 *
	 * @since 1.0.0
	 */
	protected function serviceform_init_frontend() {
		$this->serviceform_init_settings();
		add_action( 'wp_footer', array( $this, 'load_serviceform_pixel_script' ), 11, 0 );
	}

	/**
	 *
	 * Loads the plugin settings from WP options table.
	 *
	 * Applies the settings as member variables to $this.
	 *
	 * @since 1.0.0
	 */
	protected function serviceform_init_settings() {
		$settings = get_option( 'serviceform_settings_id' );
		if ( is_array( $settings ) ) {
			foreach ( $settings as $key => $value ) {
				if ( isset( $this->$key ) ) {
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 *
	 * Loads Pixel
	 *
	 * Inject it to head of the frontend.
	 *
	 * @since 1.0.0
	 */
	public function load_serviceform_pixel_script() {
        $pixel_id = get_option( 'serviceform_settings_id' );
        if ( trim( $pixel_id ) ) {
            // Load V3 script for pixel IDs longer than 12 digits
            if ( strlen( $pixel_id ) > 12 ) {
                ?>
                <!-- Start Serviceform V3 Pixel -->
                <script>
                    var tD=(new Date).toISOString().slice(0,10);
                    window.sf3pid = "<?php echo esc_attr( $pixel_id ); ?>" ;
                    var u='https://dash.serviceform.com/embed/sf-pixel.js?'+tD,
                        t=document.createElement('script');
                    t.setAttribute('type','text/javascript'),
                        t.setAttribute('src',u),t.async=!0,
                        (document.getElementsByTagName('head')[0]||document.documentElement).appendChild(t);
                </script>
                <!-- End Serviceform V3 Pixel -->
                <?php
            } else {
                ?>
                <!-- Start Serviceform V2 Pixel -->
                <script>
                    var tD=(new Date).toISOString().slice(0,10);
                    window.sfpid = <?php echo esc_attr( $pixel_id ); ?> ;
                    var u='https://serviceform.com/analytics/sf-pixel.js?'+tD,
                        t=document.createElement('script');
                    t.setAttribute('type','text/javascript'),
                        t.setAttribute('src',u),t.async=!0,
                        (document.getElementsByTagName('head')[0]||document.documentElement).appendChild(t);
                </script>
                <!-- End Serviceform V2 Pixel -->
                <?php
            }
        }
    }

	/**
	 * Checks plugin dependencies.
	 *
	 * Mainly that the WordPress and WordPress versions are equal to or greater than
	 * the defined minimums.
	 *
	 * @since 1.0.0
	 * @return bool
	 */

}

add_action( 'plugins_loaded', array( Serviceform_Pixel_Settings::serviceform_get_instance(), 'serviceform_init' ) );
