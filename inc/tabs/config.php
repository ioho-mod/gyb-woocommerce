<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !defined('GetYourBill') ){
	exit();
}

$list_getyourbillfieldstypeaccounts = array( 'nessuno' => __( 'Nessuno' , 'getyourbill') );

foreach($this->getyourbillfieldstypeaccounts as $key => $name) {
	$list_getyourbillfieldstypeaccounts[$key] = $name;
}

$settings = array(
	'section_1_title' => array(
		'name'     => GetYourBill_Messaggi_Settings["config"],
		'type'     => 'title',
		'desc'     => '',
		'id'       => 'wc_settings_getyourbill_settings_config_title',
	),

	'config_automatiche_info' => array(
		'sample_html'  => sprintf( GetYourBill_Messaggi_Settings["config_automatiche_info"], admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=info#crontab' ) )
	),

	'onlive' => array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_started',
		'name'     => GetYourBill_Messaggi_Settings["activ_deactiv"],
		'desc'     => GetYourBill_Messaggi_Settings["activ_deactiv_desc"],
		'default'  => 'yes',
	),
	'isonline' => array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_isonline',
		'name'     => GetYourBill_Messaggi_Settings["config_isonline"],
		'desc'     => GetYourBill_Messaggi_Settings["config_isonline_desc"],
		'default'  => 'yes',
	),
	'fautomatiche' => array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_fautomatiche',
		'name'     => GetYourBill_Messaggi_Settings["config_automatiche"],
		'desc'     => GetYourBill_Messaggi_Settings["config_automatiche_desc"],
		'default'  => 'no',
	),
	'statuscolum' => array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_statuscolum',
		'name'     => GetYourBill_Messaggi_Settings["config_stato"],
		'desc'     => GetYourBill_Messaggi_Settings["config_stato_desc"],
		'default'  => 'no',
	),
	'invoicegenerator' => array(
		'type'     => 'select',
		'id'       => 'wc_settings_getyourbill_settings_config_invoicegenerator',
		'name'     => GetYourBill_Messaggi_Settings["config_pdf"],
		'desc' => GetYourBill_Messaggi_Settings["config_pdf_desc"],
		'options' => $this->getyourbillplugininvoice,
		'desc_tip' => true,
	),
	'statusgenerateinvoice' => array(
		'type'     => 'select',
		'id'       => 'wc_settings_getyourbill_settings_config_statusgenerateinvoice',
		'name'     => GetYourBill_Messaggi_Settings["config_statusgenerateinvoice"],
		'desc' => GetYourBill_Messaggi_Settings["config_statusgenerateinvoice_desc"],
		'options' => $this->statusgenerateinvoice,
		'desc_tip' => true,
	),

	'bugautosend' => array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_bugautosend',
		'name'     => GetYourBill_Messaggi_Settings["congif_onlive_name"],
		'desc'     => GetYourBill_Messaggi_Settings["congif_onlive_desc"],
		'default'  => 'yes',
	),

	'section_1_end' => array(
		'type' => 'sectionend',
		'id' => 'wc_settings_getyourbill_settings_config_section_end'
	),
	'section_reg_title' => array(
		'name'     => GetYourBill_Messaggi_Settings["config_reg_label"],
		'type'     => 'title',
		'desc'     => GetYourBill_Messaggi_Settings["config_reg_label_desc"],
		'id'       => 'wc_settings_getyourbill_settings_reg_config_title',
	),
	/*
    'inputonregiter' => array(
        'type'     => 'checkbox',
        'id'       => 'wc_settings_getyourbill_settings_config_inputonregiter',
        'name'     => __( GetYourBill_Messaggi_Settings["config_nputonregiter"] , 'getyourbill' ),
        'desc' => __( GetYourBill_Messaggi_Settings["config_nputonregiter_desc"], 'getyourbill'),
        'default'  => 'no',
    ),
    'namesurname' => array(
        'type'     => 'checkbox',
        'id'       => 'wc_settings_getyourbill_settings_config_namesurname',
        'name'     => __( GetYourBill_Messaggi_Settings["config_namesurname_extra"] , 'getyourbill' ),
        'desc' => __( GetYourBill_Messaggi_Settings["config_namesurname"], 'getyourbill'),
        'default'  => 'no',
    ),*/
	/*'section_2_title' => array(
        'name'     => __( GetYourBill_Messaggi_Settings["config_c_aggiuntivi"] , 'getyourbill' ),
        'type'     => 'title',
        'desc'     => '',
        'id'       => 'wc_settings_getyourbill_settings_2_config_title'
    ), */
	'config_seltype' => array(
		'name' => GetYourBill_Messaggi_Settings["config_seltype_name"],
		'type' => 'select',
		'desc' => GetYourBill_Messaggi_Settings["config_seltype_desc"],
		'desc_tip' => true,
		'id'	=> 'wc_settings_getyourbill_settings_config_seltype',
		'options' => array(
			'radio' => GetYourBill_Messaggi_Settings["config_seltype_options_radio"],
			'select' => GetYourBill_Messaggi_Settings["config_seltype_options_select"],
		)
	),
	'accountdefault' => array(
		'type'     => 'select',
		'id'       => 'wc_settings_getyourbill_settings_accountdefault',
		'name'     => GetYourBill_Messaggi_Settings["config_accountdefault"],
		'desc' => GetYourBill_Messaggi_Settings["config_accountdefault_desc"],
		'options' => $list_getyourbillfieldstypeaccounts,
		'desc_tip' => true,
	),
	/*'section_2_end' => array(
         'type' => 'sectionend',
         'id' => 'wc_settings_getyourbill_settings_config_section_2_end'
    ),*/
	'section_reg_end' => array(
		'type' => 'sectionend',
		'id' => 'wc_settings_getyourbill_settings_reg_config_title_end'
	),
);
$getyourbilltypeinvoice = array();
foreach($this->getyourbilltypeinvoice as $key => $name) {
	$getyourbilltypeinvoice[$key] = $name["name"];
}
foreach($this->getyourbillfieldstypeaccounts as $key => $name) {
	$has_error = false;

	if( $key === "psoleproprietor" ){
		$request = self::get_info("config_request_psoleproprietor");
		if( $request === "oprionals" ){
			$docType = self::get_info("config_typeinvoice_psoleproprietor");
			if( $docType !== "i_not"){
				$has_error = true;
				self::set_info("config_typeinvoice_psoleproprietor", "i_not");
			}
		}
	}
	$settings['section_'.$key.'_title'] = array(
		'name'     => sprintf( GetYourBill_Messaggi_Settings["config_section_conf"], $name ),
		'type'     => 'title',
		'desc'     => '',
		'id'       => 'wc_settings_getyourbill_settings_'.$key.'_config_title'
	);
	if( $key === "pa" ){
		$settings['view_billing_getyourbill_settings_pactive'] = array(
			'type'     => 'checkbox',
			'id'       => 'wc_settings_getyourbill_settings_pactive',
			'name'     => GetYourBill_Messaggi_Settings["config_pactive_name"],
			'desc'     => GetYourBill_Messaggi_Settings["config_pactive_desc"],
			'default'  => 'no',
		);
	}
	if( $key === "psoleproprietor" ){
		$settings['config_typeinvoice_'.$key] = array(
			'name' => GetYourBill_Messaggi_Settings["config_typeinvoice_name"],
			'type' => 'select',
			'desc' => GetYourBill_Messaggi_Settings["config_typeinvoice_desc"],
			'desc_tip' => true,
			'id'	=> 'wc_settings_getyourbill_settings_config_typeinvoice_'.$key,
			'options' => $getyourbilltypeinvoice
		);
		if( $has_error ){
			$settings['config_typeinvoice_'.$key]['errorgyb'] = sprintf( GetYourBill_Messaggi_Warning["config_typeinvoice_warning"], admin_url( "admin.php?page=wc-settings&tab=getyourbill&section=info" ) );
		}
	}
	$settings['config_request_'.$key] = array(
		'name' => GetYourBill_Messaggi_Settings["config_request_name"],
		'type' => 'select',
		'desc' => sprintf( GetYourBill_Messaggi_Settings["config_request_desc".(( $key === "psoleproprietor" )?"_p":"")], $name ),
		'desc_tip' => true,
		'id'	=> 'wc_settings_getyourbill_settings_config_request_'.$key,
		'options' => $this->getyourbill_sorting_by_type_account( $key ),
	);
	$settings['view_billing_getyourbillid_'.$key] = array(
		'type'     => 'checkbox',
		'id'       => 'wc_settings_getyourbill_settings_config_view_billing_getyourbillid_'.$key,
		'name'     => GetYourBill_Messaggi_Settings["billing_getyourbillid_name"],
		'desc'     => GetYourBill_Messaggi_Settings["view_billing_getyourbillid_desc"],
		'default'  => 'no',
	);
	$settings['section_'.$key.'_end'] = array(

		'type' => 'sectionend',
		'id'       => 'wc_settings_getyourbill_settings_'.$key.'_config_title'
	);
}


$settings['section_bollo_title'] = array(
	'name'     => GetYourBill_Messaggi_Settings["config_bollo_label"],
	'type'     => 'title',
	'id'       => 'wc_settings_getyourbill_section_bollo_title',
);

$settings['bollo'] = array(
	'type'     => 'checkbox',
	'id'       => 'wc_settings_getyourbill_settings_config_bollo',
	'name'     => GetYourBill_Messaggi_Settings["congif_bollo_name"],
	'desc'     => GetYourBill_Messaggi_Settings["congif_bollo_desc"]
);
$settings['bollo_totale'] = array(
	'type'     => 'text',
	'id'       => 'wc_settings_getyourbill_settings_config_bollo_quanto',
	'name'     => GetYourBill_Messaggi_Settings["congif_bollo_attiva_quanto_name"],
	'desc'     => GetYourBill_Messaggi_Settings["congif_bollo_attiva_quanto_desc"],
	'default' => 0,
	'desc_tip' => true,
);
$settings['bollo_tottal'] = array(
	'type'     => 'text',
	'id'       => 'wc_settings_getyourbill_settings_config_bollo_totale',
	'name'     => GetYourBill_Messaggi_Settings["congif_bollo_attiva_da_name"],
	'desc'     => GetYourBill_Messaggi_Settings["congif_bollo_attiva_da_desc"],
	'default' => 0,
	'desc_tip' => true,
);

$settings['section_bollo_end'] = array(
	'type' => 'sectionend',
	'id'       => 'wc_settings_getyourbill_section_bollo_end'
);


$settings['section_dati_richiesti_title'] = array(
	'name'     => GetYourBill_Messaggi_Settings["config_dati_richiesti_label"],
	'type'     => 'title',
	'id'       => 'wc_settings_getyourbill_section_dati_richiesti_title',
);


$settings['phonenotrequest'] = array(
	'type'     => 'checkbox',
	'id'       => 'wc_settings_getyourbill_settings_config_phonenotrequest',
	'name'     => GetYourBill_Messaggi_Settings["congif_phone_name"],
	'desc'     => GetYourBill_Messaggi_Settings["congif_phone_desc"]
);
$settings['getyourbillinputsreg'] = array(
	'type'     => 'select',
	'id'       => 'wc_settings_getyourbill_settings_config_getyourbillinputsreg',
	'name'     => GetYourBill_Messaggi_Settings["config_reg"],
	'desc' => GetYourBill_Messaggi_Settings["config_reg_desc"],
	'options' => $this->getyourbillinputsreg,
	'desc_tip' => true,
);


$settings['section_dati_richiesti_end'] = array(
	'type' => 'sectionend',
	'id'       => 'wc_settings_getyourbill_section_dati_richiesti_end'
);
?>
