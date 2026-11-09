<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( !defined('GetYourBill') ){
		exit();
	}   

	$settings['html'] = array(
		'html'     => WC_Settings_GetyourbillTabs::load_template_part(GetYourBillPATH."inc/tabs/aliquote_html", array())
	);

?>
