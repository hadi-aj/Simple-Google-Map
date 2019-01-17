<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://clarknikdelpowell.com
 * @since      3.0.0
 *
 * @package    Simple_Google_Map
 * @subpackage Simple_Google_Map/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    Simple_Google_Map
 * @subpackage Simple_Google_Map/includes
 * @author     Taylor Gorman <taylor@clarknikdelpowell.com>, Glenn Welser <glenn@clarknikdelpowell.com>
 */
class Simple_Google_Map {

	/**
	 * Plugin instance.
	 *
	 * @since    3.0.0
	 * @access   protected
	 */
	protected static $instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Simple_Google_Map_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The path of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $plugin_path The string path of this plugin.
	 */
	protected $plugin_path;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Default map options
	 *
	 * @since 3.3
	 * @var array
	 */
	static $default_options = array(
		'api_key'      => '',
		'zoom'         => '12',
		'type'         => 'ROADMAP',
		'icon'         => '',
		'directionsto' => '',
		'content'      => '',
		'autoopen'     => false,
	);

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'simple-google-map';
		$this->version     = '4.3.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simple_Google_Map_Loader. Orchestrates the hooks of the plugin.
	 * - Simple_Google_Map_i18n. Defines internationalization functionality.
	 * - Simple_Google_Map_Admin. Defines all hooks for the admin area.
	 * - Simple_Google_Map_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-google-map-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-google-map-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simple-google-map-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-simple-google-map-public.php';


		/**
		 * Our widget class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/simple-google-map-widget.php';

		$this->loader = new Simple_Google_Map_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simple_Google_Map_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Simple_Google_Map_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Simple_Google_Map_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'plugin_menu' );

		$this->loader->add_action( 'widgets_init', $plugin_admin, 'register_widgets' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Simple_Google_Map_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'output_css' );

		$this->loader->add_shortcode( 'SGM', $plugin_public, 'map' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0.0
	 */
	public function run() {

		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0.0
	 * @return    Simple_Google_Map_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {

		return $this->version;
	}

	/**
	 * Retrieve the path of the plugin.
	 *
	 * @since     3.0.0
	 * @return    string    The path of the plugin.
	 */
	public function get_plugin_path() {

		return $this->plugin_path;
	}

	/**
	 * Removes the specified string from the end of the target string.
	 *
	 * @since 3.2
	 *
	 * @param string       $haystack
	 * @param string|Array $needles
	 *
	 * @return string
	 */
	public static function strip_last_chars( $haystack, $needles ) {

		if ( empty( $haystack ) ) {
			return $haystack;
		}

		if ( ! is_array( $needles ) ) {
			if ( substr( $haystack, strlen( $needles ) * - 1 ) === $needles ) {
				$haystack = substr( $haystack, 0, strlen( $haystack ) - strlen( $needles ) );
			}
		}

		if ( is_array( $needles ) ) {
			foreach ( $needles as $needle ) {
				if ( substr( $haystack, strlen( $needle ) * - 1 ) === $needle ) {
					$haystack = substr( $haystack, 0, strlen( $haystack ) - strlen( $needle ) );
					break;
				}
			}
		}

		return $haystack;
	}
}
