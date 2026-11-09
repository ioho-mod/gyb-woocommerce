<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !defined('GetYourBill') ){
	exit();
}

	$plugin_data = get_plugin_data( GetYourBillFILE );
	$this_vs = $plugin_data["Version"];
	$gyb_isonline = get_option( "wc_settings_getyourbill_settings_config_isonline" , "yes" );
	$made_plug =  $gyb_isonline == "yes"?"Test":"Live";
	$key = \models\GetyourbillApp::get_info("isokkey");


	$settings = array(
		'section_title_' => array(
			'name'     => __( "Info plugin", 'getyourbill' ),
			'type'     => 'title',
		),
			'modeg' => array(
				'name' => GetYourBill_Messaggi_Settings["status_mode"],
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_plugin',
				'desc'     => $made_plug,
				'class'     => "hidden",
			),
			'plugin' => array(
				'name' => GetYourBill_Messaggi_Settings["congif_vs"],
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_plugin',
				'desc'     => GetYourBill_Messaggi_Settings["congif_vs_mini"].$this_vs,
				'class'     => "hidden",
			),
		'section_title_end_' => array(
			'id' => 'wc_settings_getyourbill_settings_section_end_',
			'type'     => 'sectionend',
		),
		'section_title' => array(
			'name'     => GetYourBill_Messaggi_Settings["congif_api"],
			'type'     => 'title',
		),
			'title' => array(
				'name' => GetYourBill_altro["licenza_key"]." Live",
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_apikey',
				'desc'     => ( ( $key == "success" )?"":GetYourBill_Messaggi_Error["validation_api_key_errore"] ),
				'class'     => ( ( $key == "success" )?"":"notice notice-error my-dismiss-notice" ),
			)
	);

	$getyourbill_code = \models\GetyourbillApp::get_info("getyourbill_code" );
	$getyourbill_license = \models\GetyourbillApp::get_info("getyourbill_license" );
	$getyourbill_locationId = \models\GetyourbillApp::get_info("getyourbill_locationId" );
	$getyourbill_supportID = \models\GetyourbillApp::get_info("getyourbill_supportID" );

	if( !empty($getyourbill_code) ){
		$settings['getyourbill_code'] = array(
				'name' => GetYourBill_altro["licenza"],
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_getyourbill_code',
				'desc'     => GetYourBillDevice["code"]." : ".$getyourbill_code,
				'class'     => "hidden",
		);
		$settings['getyourbill_license'] = array(
				'name' => "",
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_getyourbill_license',
				'desc' => GetYourBillDevice["license"]." : ".$getyourbill_license,
				'class'     => "hidden",
		);
		$settings['getyourbill_locationId'] = array(
				'name' => "",
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_getyourbill_locationId',
				'desc'     => GetYourBillDevice["locationId"]." : ".$getyourbill_locationId,
				'class'     => "hidden",
		);
	}
	if( !empty($getyourbill_supportID) ){
		$settings['getyourbill_supportID'] = array(
				'name' => GetYourBill_altro["support_id"],
				'type' => 'text',
				'id'   => 'wc_settings_getyourbill_settings_getyourbill_supportID',
				'desc'     => $getyourbill_supportID,
				'class'     => "hidden",
		);
	}
	$settings['section_end'] = array(
		 'type' => 'sectionend',
		 'id' => 'wc_settings_getyourbill_settings_section_end'
	);


	$key = \models\GetyourbillApp::get_info("isokkey_test");

	$settings['section_title_test'] = array(
		'name'     => GetYourBill_Messaggi_Settings["congif_api_test"],
		'type'     => 'title',
	);

	$settings['title_test'] = array(
		'name' => GetYourBill_altro["licenza_key"]." test",
		'type' => 'text',
		'id'   => 'wc_settings_getyourbill_settings_apikey_test',
		'desc'     => ( ( $key == "success" )?"":GetYourBill_Messaggi_Error["validation_api_key_errore_test"] ),
		'class'     => ( ( $key == "success" )?"":"notice notice-error my-dismiss-notice" ),
	);



		$getyourbill_code_test = \models\GetyourbillApp::get_info("getyourbill_code_test" );
		$getyourbill_license_test = \models\GetyourbillApp::get_info("getyourbill_license_test" );
		$getyourbill_locationId_test = \models\GetyourbillApp::get_info("getyourbill_locationId_test" );
		$getyourbill_supportID_test = \models\GetyourbillApp::get_info("getyourbill_supportID_test" );


		if( !empty($getyourbill_code_test) ){
			$settings['getyourbill_code_test'] = array(
					'name' => GetYourBill_altro["licenza"],
					'type' => 'text',
					'id'   => 'wc_settings_getyourbill_settings_getyourbill_code_test',
					'desc' => GetYourBillDevice["code"]." : ".$getyourbill_code_test,
					'class' => "hidden",
			);
			$settings['getyourbill_license_test'] = array(
					'name' => "",
					'type' => 'text',
					'id'   => 'wc_settings_getyourbill_settings_getyourbill_license_test',
					'desc' => GetYourBillDevice["license"]." : ".$getyourbill_license_test,
					'class' => "hidden",
			);
			$settings['getyourbill_locationId_test'] = array(
					'name' => "",
					'type' => 'text',
					'id'   => 'wc_settings_getyourbill_settings_getyourbill_locationId_test',
					'desc' => GetYourBillDevice["locationId"]." : ".$getyourbill_locationId_test,
					'class' => "hidden",
			);
		}

		if( !empty($getyourbill_supportID_test) ){
			$settings['getyourbill_supportID_test'] = array(
					'name' => GetYourBill_altro["support_id"],
					'type' => 'text',
					'id'   => 'wc_settings_getyourbill_settings_getyourbill_supportID_test',
					'desc' => $getyourbill_supportID_test,
					'class' => "hidden",
			);
		}


	$settings['section_end_test'] = array(
		 'type' => 'sectionend',
		 'id' => 'wc_settings_getyourbill_section_end_test'
	);

?>
