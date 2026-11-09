<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !defined('GetYourBill') ){
	exit();
}




	$p_obj = new WC_Payment_Gateways();
	$enabled_gateways = $p_obj->payment_gateways();
	$settings = array();
	$settings['section_title'] = array(
		'name'     => GetYourBill_Messaggi_Settings["gest_pagamenti"],
		'type'     => 'title',
		'desc'     => sprintf( GetYourBill_Messaggi_Settings["gest_pagamenti_desc"], admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=info#paymethod' ) ),
		'id'       => 'wc_settings_getyourbill_settings_bugs_section_title'
	);
	foreach ( $enabled_gateways as $key => $pay ) {
		$pay_setup = \models\GetyourbillApp::get_info('payments_'.$key); // if is null mai settato
		$settings["payments_".$key] = array(
			'name' => $pay->title,
			'type' => 'select',
			'desc' => $pay->description,
			'id'   => 'wc_settings_getyourbill_settings_payments_'.$key,
			'options' => $this->getyourbillpayments
		);
	}
	$settings['section_end'] = array(
		'type' => 'sectionend',
		'id' => 'wc_settings_getyourbill_settings_bugs_section_end'
	);

?>
