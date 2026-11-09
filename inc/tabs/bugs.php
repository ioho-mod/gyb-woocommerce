<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( !defined('GetYourBill') ){
	exit();
}
 
$all_logs = "";
	if( isset($_GET["sendlog"]) ){
		check_admin_referer( 'getyourbill_sendlog' );
		$send = \models\GetyourbillApp::send_log("manualmente");
		if ( ! headers_sent() ) {
			wp_safe_redirect(admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=bugs&successendlog='.($send?"true":"") ));
			exit;
		}
		if ( $send ) {
			$all_logs .= "<p>".GetYourBill_Messaggi_Success["notice_send_log_succes"]."</p>";
		}
	}
	$all_logs_ = \models\GetyourbillApp::get_info("logs");
	$all_logs_ = ($all_logs_=="")?GetYourBill_Messaggi_Warning["missing_logs"]:$all_logs_;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by the redirect above.
	if( isset($_GET["successendlog"]) && "true" === $_GET["successendlog"] ){
		$all_logs .= "<p>".GetYourBill_Messaggi_Success["notice_send_log_succes"]."</p>";
	}
	$all_logs .= '<br><br><br><div style="background: #fff;border: 1px solid #ccd0d4;margin: 5px 15px 2px;padding: 12px 12px;max-height: 450px; overflow: auto;">'.$all_logs_.'</div>';
	$all_logs .= '<a href="'.esc_url( wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=bugs&sendlog' ), 'getyourbill_sendlog' ) ).'" class="page-title-action">'.GetYourBill_altro["button_label_send_logs"].'</a>';
	$settings = array(
		'section_title' => array(
			'name' => GetYourBill_Messaggi_Settings["congif_list_log"],
			'type' => 'title',
			'id' => 'wc_settings_getyourbill_settings_bugs_section_title',
			'desc' => $all_logs,
		),
		'section_end' => array(
			 'type' => 'sectionend',
			 'id' => 'wc_settings_getyourbill_settings_bugs_section_end'
		)
	);

?>
