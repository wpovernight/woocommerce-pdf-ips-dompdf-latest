<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips latest dompdf
 * Plugin URI: http://www.wpovernight.com
 * Description: Uses the latest release of dompdf instead of the legacy version bundled with the general release
 * Version: 1.0.0
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WCPDF_Custom_PDF_Maker_dompdf' ) ) :

class WCPDF_Custom_PDF_Maker_dompdf {
	public $html;
	public $settings;

	public function __construct( $html, $settings = array() ) {
		$this->html = $html;

		$default_settings = array(
			'paper_size'		=> 'A4',
			'paper_orientation'	=> 'portrait',
			'font_subsetting'	=> false,
		);
		$this->settings = $settings + $default_settings;
	}

	public function output() {
		if ( empty( $this->html ) ) {
			return;
		}
		
		require_once __DIR__ . '/vendor/autoload.php';

		// set options
		$options = new \Dompdf\Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'tempDir'					=> WPO_WCPDF()->main->get_tmp_path('dompdf'),
			'fontDir'					=> WPO_WCPDF()->main->get_tmp_path('fonts'),
			'fontCache'					=> WPO_WCPDF()->main->get_tmp_path('fonts'),
			'chroot'					=> array( WP_CONTENT_DIR ),
			'logOutputFile'				=> WPO_WCPDF()->main->get_tmp_path('dompdf') . "/log.htm",
			'defaultFont'				=> 'dejavu sans',
			'isRemoteEnabled'			=> true,
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> ( isset(WPO_WCPDF()->settings->debug_settings['use_html5_parser']) && extension_loaded('iconv') ) ? true : false,
			'isFontSubsettingEnabled'	=> $this->settings['font_subsetting'],
		) ) );

		// instantiate and use the dompdf class
		$dompdf = new \Dompdf\Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html );

		return $dompdf->output();
	}
}

endif; // class_exists

add_filter( 'wpo_wcpdf_pdf_maker', function ( $class ) {
	$class = 'WCPDF_Custom_PDF_Maker_dompdf';
	return $class;
});