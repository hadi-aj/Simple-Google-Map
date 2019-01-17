<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://clarknikdelpowell.com
 * @since      3.0.0
 *
 * @package    Simple_Google_Map
 * @subpackage Simple_Google_Map/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_Google_Map
 * @subpackage Simple_Google_Map/public
 * @author     Taylor Gorman <taylor@clarknikdelpowell.com>, Glenn Welser <glenn@clarknikdelpowell.com>
 */
class Simple_Google_Map_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The path of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $plugin_path The string path of this plugin.
	 */
	protected $plugin_path;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Enqueue scripts
	 *
	 * @since 3.1.0
	 */
	public function enqueue_scripts() {

		global $post;
		if ( has_shortcode( $post->post_content, 'SGM' ) || is_active_widget( false, false, 'simple-google-map-widget', true ) ) {
			$source = 'https://maps.googleapis.com/maps/api/js';
			$source = 'https://maps.google.com/maps/api/js?sensor=false&callback=initMap';

			$api_key = $this->get_api_key();
			if ( $api_key ) {
				$source .= '&key=' . $api_key;
			}

			wp_enqueue_script( 'google-maps', $source );
		}

	}

	/**
	 * Output CSS into header
	 *
	 * @since    3.0.0
	 */
	public function output_css() {

		$sgm_options = get_option( 'SGMoptions' );
		if ( isset( $sgm_options['nostyle'] ) ) {
			return;
		}

		echo "<!-- styles for Simple Google Map -->\n<style type='text/css'>\n";
		echo get_option( 'SGMcss' );
		echo "\n</style>\n<!-- end styles for Simple Google Map -->\n";

	}

	/**
	 * Output CSS into header
	 *
	 * @since    3.0.0
	 */
	public function map( $atts ) {
                $randId = rand(1, 1000);

		$sgm_options = get_option( 'SGMoptions' ); // get options defined in admin page
		$sgm_options = wp_parse_args( $sgm_options, Simple_Google_Map::$default_options );

		$style           = isset( $atts['style'] ) ? $atts['style'] : '';
		$class           = isset( $atts['class'] ) ? $atts['class'] : '';
		$lat           = isset( $atts['lat'] ) ? $atts['lat'] : '0';
		$lng           = isset( $atts['lng'] ) ? $atts['lng'] : '0';
		$zoom          = isset( $atts['zoom'] ) ? $atts['zoom'] : $sgm_options['zoom'];
		$type          = isset( $atts['type'] ) ? strtoupper( $atts['type'] ) : $sgm_options['type'];
		$content       = isset( $atts['content'] ) ? $atts['content'] : $sgm_options['content'];
		$directions_to = isset( $atts['directionsto'] ) ? $atts['directionsto'] : '';
		$auto_open     = isset( $atts['autoopen'] ) ? $atts['autoopen'] : false;
		$icon          = isset( $atts['icon'] ) ? esc_url( $atts['icon'], array(
			'http',
			'https',
		) ) : $sgm_options['icon'];

		$content = Simple_Google_Map::strip_last_chars( htmlspecialchars_decode( $content ), array(
			'<br>',
			'<br/>',
			'<br />',
		) );

		$directions_form = '';
		if ( $directions_to ) {
			$directions_form = '<form method="get" action="//maps.google.com/maps"><input type="hidden" name="daddr" value="' . $directions_to . '" /><input type="text" class="text" name="saddr" /><input type="submit" class="submit" value="Directions" /></form>';
		}

		$marker = "var marker = new google.maps.Marker({
			position: latlng,
			map: map,
			title: '',";

		if ( $icon ) {
			$icon   = "var image = {
				url: '$icon',
			};";
			$marker .= "\n" . 'icon: image,' . "\n";
		}

		$marker .= '});';

		$infowindow_arr     = array( $content, $directions_form );
		$infowindow_content = implode( '<br>', array_filter( $infowindow_arr ) );
		$infowindow_content = str_replace( "\r", "", $infowindow_content );
		$infowindow_content = str_replace( "\n", "", $infowindow_content );

		$infowindow_open = $auto_open ? 'infowindow.open(map,marker);' . "\n" : '';
                $map = '<div id="SGM'.$randId.'" class="SGM '.$class.'" style="'.$style.'"></div>';
		$map .= '<script type="text/javascript">';
		$map .= "function makeMap".$randId."() {
				let latlng = new google.maps.LatLng($lat, $lng);
				let myOptions = {
					zoom: $zoom,
					center: latlng,
					mapTypeControl: true,
					mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
					navigationControl: true,
					navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
					mapTypeId: google.maps.MapTypeId.$type
				};
				let map = new google.maps.Map(document.getElementById('SGM".$randId."'), myOptions);
				let contentstring = '<div class=\"infoWindow\">$infowindow_content</div>';
				let infowindow = new google.maps.InfoWindow({
					content: contentstring
				});
				$icon
				$marker
				google.maps.event.addListener(marker, 'click', function() {
				  infowindow.open(map,marker);
				});
				$infowindow_open
			};
                        makeMap".$randId."();";
		$map .= '</script>';

		return $map;
	}

	/**
	 * Get the Google Maps API Key
	 *
	 * @since   4.0.0
	 */
	public function get_api_key() {

		$sgm_options = get_option( 'SGMoptions' );
		$sgm_options = wp_parse_args( $sgm_options, Simple_Google_Map::$default_options );
		$api_key     = $sgm_options['api_key'];

		return $api_key;
	}
}
