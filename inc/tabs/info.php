<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	if ( !defined('GetYourBill') ){
		exit();
	}

	$infotab = GetYourBillPATH."inc/tabs/guida/it_IT";
	if( file_exists(GetYourBillPATH."inc/tabs/guida/".get_locale()) ){
		$infotab = GetYourBillPATH."inc/tabs/guida/".get_locale();
	}
	$settings['html'] = array(
		'html' => '<div class="gyb_info">'.str_replace( 'src="', 'src="'.GetYourBillURL."assets/image/", WC_Settings_GetyourbillTabs::load_template_part($infotab, array() ) )."</div>"
	);

?>
