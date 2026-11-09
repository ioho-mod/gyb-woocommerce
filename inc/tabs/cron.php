<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !defined('GetYourBill') ){
	exit();
}

//

	$settings = array(
		'section_title' => array(
			'name' => GetYourBill_Messaggi_Settings["congif_cron"],
			'type' => 'title',
			'id' => 'wc_settings_getyourbill_settings_bugs_section_title',
		),
		'html' => array(
			 'html' =>  __("Il Crontab è un sistema di schedulazione che permette l'esecuzione ricorsiva di uno script all'interno del sito, con un intervallo di tempo definito. La configurazione del Crontab non è obbligatoria, ma è indispensabile per rendere operativi gli automatismi messi a disposizione dal plug-in GetYourBill. Tutte le istruzioni per la corretta configurazione del Crontab sono contenute nella Guida al capitolo 'Configurazione del Crontab'.<br> Nel Crontab devi inserire il link dello script che verrà eseguito. Qui di seguito è riportato il link che devi inserire.", 'getyourbill'),
		),
		'html_link' => array(
			 'html' => '<br><br><h2>'.__('LINK', 'getyourbill').'</h2><input type="text" style="width: 100%;" value="'.esc_attr( sprintf( GetYourBill_Messaggi_Settings["link_cron"], admin_url( 'admin-ajax.php?action=getyourbill_cron&acckey=' ), get_option( "getyourbill_settings_cron_key" ) ) ).'" class="" placeholder="">',
		),
		'section_end' => array(
			 'type' => 'sectionend',
			 'id' => 'wc_settings_getyourbill_settings_bugs_section_end'
		)
	);

?>
