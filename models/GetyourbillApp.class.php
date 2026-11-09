<?php
namespace models;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'GetYourBill' ) ) {
	exit();
}
class GetyourbillApp
{
	public function __construct() {
	}

	public static function test() {

		//echo $invoice = self::get_generate_template_return( wc_get_order( $_GET["post"] ) );
	}

	public static function autosedbugs() {
		$bugautosend = self::get_info("config_bugautosend");
		if( $bugautosend == "yes" ) {
			if( false == get_transient( 'getyourbill_config_bugautosend' ) ) {
				self::send_log( GetYourBill_Messaggi_Warning["send_log_auto"] );
				set_transient( 'getyourbill_config_bugautosend' , "config_bugautosend", DAY_IN_SECONDS );
			}
		}
	}


	public function gestione_status_invoice( $order_id = null , $print = false) {
		$view = false;
		if( $print ){
			$view = true;
		}
		$res_bool = false;
		$set_status_invoice = "yes";
		$getyourbill_send_ok_f_json = get_post_meta( $order_id, "getyourbill_send_ok_f_json", true );
		if( $getyourbill_send_ok_f_json !== "" && $getyourbill_send_ok_f_json == "yes" ){
			$is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
			$error_sdi = get_post_meta( $order_id, "getyourbill_if_error_sdi", true );
			$api = new \models\APIGetyourbill();
			$coder = get_post_meta( $order_id, "getyourbill_recoverCode", true );
			$res_info = get_post_meta( $order_id, "getyourbill_send_ok_f_info", true );
			$res = $api->get_status_fattura($coder);
			$res_info = (empty($res_info))?array():$res_info;
			$messaggio_string = "";

			if( $print ){
				//printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', print_r($res, true) );
				//printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', print_r($res_info, true) );
			}
			if( is_array($res) && array_key_exists("error", $res) && array_key_exists("code", $res) && $res["code"] == "404" ){
				self::set_info_order( $order_id, "getyourbill_status_invoice_ok", "error" );
				self::set_info_order($order_id, "getyourbill_if_error_mess", GetYourBill_Messaggi_Error["missing_status"]);
			}else if( is_array($res) ){
				$notice = "error";
				self::set_info_order($order_id, "getyourbill_status_invoice", $res);
				foreach($res as $key_status => $status) {
					if( !array_key_exists($key_status, $res_info) ){
						$res_info[$key_status] = "no";
					}
					if( $status != "" ){
						$view = false;
						if( array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) ){
							$messaggio_string = GetYourBillSdiErrorIntermediaError[$key_status]["name"]." : ".$status;
						}else{
							$messaggio_string = $status;
						}
					}
					if( $status != "" && !array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) ){
						\models\APIGetyourbill::set_error_log( __("Attenzione lo stato ricevuto non esiste: stato ", 'getyourbill').$key_status." : ". wp_json_encode($res));
					}
					if( $status != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) ){
						if( $res_info[$key_status] == "no" ){
							$order_note = wc_get_order( $order_id );
							$order_note->add_order_note( $messaggio_string );
							$order_note->save();
							$res_info[$key_status] = "yes";
						}
						if( $print ){
							if( $status != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) && GetYourBillSdiErrorIntermediaError[$key_status]["mod"] == "error" ){
								$set_status_invoice = "error";
								printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-' . sanitize_html_class( GetYourBillSdiErrorIntermediaError[ $key_status ]['status'] ) ), esc_html($messaggio_string) );
							}else if(  $status != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) && GetYourBillSdiErrorIntermediaError[$key_status]["mod"] == "result" ){
								$set_status_invoice = "processiong";
								printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-' . sanitize_html_class( GetYourBillSdiErrorIntermediaError[ $key_status ]['status'] ) ), esc_html($messaggio_string) );
							}
						}
						if( $key_status == 'deliveryDateTime' || $key_status == 'impossibleDeliveryDateTime'){
							$set_status_invoice = "end";
							$res_bool = $this->is_send_sdi($order_id, true, $messaggio_string);
							if( $key_status == 'impossibleDeliveryDateTime' ){
								self::set_info_order($order_id, "getyourbill_if_error_sdi", $messaggio_string);
							}else{
								self::set_info_order($order_id, "getyourbill_if_error_mess", "");
							}
						}else{
							delete_post_meta( $order_id, "getyourbill_is_send_sdi" );
						}
					}else{
						$res_info[$key_status] = "no";
					}
				}
				if( $print && $view ){
					printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["missing_status"] ) );
				}
				self::set_info_order( $order_id, "getyourbill_status_invoice_ok", $set_status_invoice );
				self::set_info_order($order_id, "getyourbill_send_ok_f_info", $res_info);
				self::set_info_order($order_id, "getyourbill_if_error_mess", $messaggio_string);
			}else{
				printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["missing_status"] ) );
			}
		}else{
			printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["missing_send"] ) );
		}
		return $res_bool;
	}

	public static function is_send_sdi( $stato, $mess, $order_id= 0 ) {
		$order_note = wc_get_order( $order_id );
		if( $stato ){
			$order_note->add_order_note( GetYourBill_Messaggi_Success["order_consegna_fattura_accettata"].". ".$mess, false, true );
			$order_note->save();
			self::set_info_order($order_id, "getyourbill_is_send_sdi", "yes");
			delete_post_meta( $order_id, "getyourbill_send_ok_f_info" );
			delete_post_meta( $order_id, "getyourbill_status_invoice" );
			return true;
		}else if( !$stato ){
			$order_note->add_order_note( GetYourBill_Messaggi_Warning["order_consegna_fattura_rifiutata"].". ".$mess );
			$order_note->save();
			self::set_info_order($order_id, "getyourbill_is_send_sdi", "yes");
			delete_post_meta( $order_id, "getyourbill_send_ok_f_info" );
			delete_post_meta( $order_id, "getyourbill_status_invoice" );
			delete_post_meta( $order_id, "getyourbill_status_invoice" );
			return true;
		}
		return false;
	}

	public function autostatus( $WC_Settings_GetyourbillTabs = null ) {
		global $wpdb;
		global $GetYourBill;

		// auto correzione dei errori
		// $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='%s' AND meta_value='%s' Limit 0, ".GetYourLimitResolveErroreOnLoop;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				AND meta_value = %s
				AND post_id IN (
					SELECT post_id FROM {$wpdb->postmeta}
					WHERE meta_key = %s
					AND meta_value IN ('send_fattura', 'number', 'pdf')
				)
				AND post_id NOT IN (
					SELECT option_value FROM {$wpdb->options}
					WHERE option_name LIKE %s
				)
				LIMIT %d, %d",
				'getyourbill_send_ok_f_json',
				'error',
				'getyourbill_send_error',
				'_transient_getyourbill_fattura_in_creazione_%',
				0,
				GetYourLimitResolveErroreOnLoop
			),
			ARRAY_A
		);

		foreach($res as $key => $order) {
			$resolve = false;
			$order_id = $order["post_id"];
			$order = new \WC_Order($order_id);
			if( !is_wp_error( $order ) ) {
				$getyourbill_send_error = get_post_meta( $order_id, "getyourbill_send_error", true );
				if( !empty($getyourbill_send_error) && $getyourbill_send_error == "send_fattura" ){
					set_transient( 'getyourbill_fattura_in_creazione_'.$order_id , $order_id, MINUTE_IN_SECONDS*GetYourBillCronErrorSendTime );
					$r_send = $WC_Settings_GetyourbillTabs->getyourbill_payment_complete( $order_id, true );
					if( $r_send ){
						$resolve = true;
					}
				}else if( !empty($getyourbill_send_error) && $getyourbill_send_error == "number" ){
					set_transient( 'getyourbill_fattura_in_creazione_'.$order_id , $order_id, MINUTE_IN_SECONDS*GetYourBillCronErrorSendTime );
					if( $WC_Settings_GetyourbillTabs->getyourbill_get_invoice_number($order_id) ){
						if( $WC_Settings_GetyourbillTabs->getyourbill_set_invoice_pdf($order_id, true) ){
							$resolve = true;
							update_post_meta( $order_id, "getyourbill_send_ok_f_json", "yes" );
							delete_post_meta($order_id, "getyourbill_send_error");
						}else{
							update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
							update_post_meta( $order_id, "getyourbill_send_error", "pdf" );
						}
					}else{
						update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
						update_post_meta( $order_id, "getyourbill_send_error", "number" );
					}
				}else if( !empty($getyourbill_send_error) && $getyourbill_send_error == "pdf" ){
					set_transient( 'getyourbill_fattura_in_creazione_'.$order_id , $order_id, MINUTE_IN_SECONDS*GetYourBillCronErrorSendTime );
					if( $WC_Settings_GetyourbillTabs->getyourbill_set_invoice_pdf($order_id, true) ){
						$resolve = true;
						update_post_meta( $order_id, "getyourbill_status_invoice_ok", "processiong" );
						update_post_meta( $order_id, "getyourbill_send_ok_f_json", "yes" );
						delete_post_meta($order_id, "getyourbill_send_error");
					}else{
						update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
						update_post_meta( $order_id, "getyourbill_send_error", "pdf" );
					}
				}
				if( $resolve ){
					delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
				}
			}
		}

		// auto completamento dei stati
		//$sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='%s' AND meta_value='%s' Limit 0, ".GetYourBillCronStatusTimeProcessiong;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				AND meta_value = %s
				AND post_id NOT IN (
					SELECT option_value FROM {$wpdb->options}
					WHERE option_name LIKE %s
				)
				LIMIT %d, %d",
				'getyourbill_status_invoice_ok',
				'processiong',
				'_transient_getyourbill_order_status_autosend_%',
				0,
				GetYourBillCronStatusTimeProcessiong
			),
			ARRAY_A
		);


		foreach($res as $key => $order) {
			$order_id = $order["post_id"];
			set_transient( 'getyourbill_order_status_autosend_'.$order_id , $order_id, MINUTE_IN_SECONDS*GetYourBillCronStatusTime );
			$order = new \WC_Order($order_id);
			$r_status = false;
			if( !is_wp_error( $order ) ) {
				$billing_user_type = get_post_meta( $order_id, "billing_user_type", true );
				if( empty($billing_user_type) )
					$billing_user_type = get_the_author_meta( "billing_user_type", $order->get_user_id() );
				$docType = $WC_Settings_GetyourbillTabs->get_typeinvoice_by_config($billing_user_type, true);
				if( $docType == "i_61" ){
					$r_status = $this->gestione_status_invoice($order_id);
				}
				delete_transient('getyourbill_order_status_autosend_'.$order_id);
				break;
			}
		}

	}

	public static function get_min_max_meta_value( $key, $type = 'max' ) {
		global $wpdb;

		$cache_key = md5( $key . $type );
		$results   = wp_cache_get( $cache_key );

		if ( false !== $results ) {
			return $results;
		}

		$is_min = ( 'min' === strtolower( $type ) );

		if ( $is_min ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
			$nr = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT MIN( CAST( meta_value AS UNSIGNED ) )
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s
					AND post_id IN (
						SELECT post_id FROM {$wpdb->postmeta}
						WHERE meta_key = %s
						AND meta_value = %s
					)
					",
					$key,
					'getyourbill_invoice_year',
					gmdate( 'Y' )
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
			$nr = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT MAX( CAST( meta_value AS UNSIGNED ) )
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s
					AND post_id IN (
						SELECT post_id FROM {$wpdb->postmeta}
						WHERE meta_key = %s
						AND meta_value = %s
					)
					",
					$key,
					'getyourbill_invoice_year',
					gmdate( 'Y' )
				)
			);
		}

		$nr = ( null === $nr ) ? 0 : (int) $nr;

		wp_cache_set( $cache_key, $nr );

		return $nr;
	}

	public static function get_last_order_send_number() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
		$max = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT MAX( post_id )
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				AND meta_value = %s
				AND post_id NOT IN (
					SELECT option_value FROM {$wpdb->options}
					WHERE option_name LIKE %s
				)
				",
				'getyourbill_send_ok_f_json',
				'yes',
				'_transient_getyourbill_fattura_in_creazione_%'
			)
		);

		return ( null === $max ) ? 0 : (int) $max;
	}
	public static function get_last_invoice_number( ){
		$max = self::get_min_max_meta_value("max", "getyourbill_invoice");
		return $max;
	}

	public static function set_info_order( $order_id= 0, $info = '', $val = '' ) {
		update_post_meta( $order_id, $info, $val );
	}
	// pdf gen
	public static function get_invoice_url( $order_id = null ) {
		if( is_null($order_id) )
			return null;
		$invoicegenerator = self::get_info("config_invoicegenerator");
		$invoice = null;
		$order =  wc_get_order( $order_id );
		$url = "#";
		if( $invoicegenerator == "woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php" ){
			$document = wcpdf_get_invoice($order);
			$url = wp_nonce_url(admin_url('admin-ajax.php').'?action=generate_wpo_wcpdf&document_type='.$document->get_type().'&order_ids='.$order_id , 'generate_wpo_wcpdf');
		}else if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
			$url = wp_nonce_url( add_query_arg( array(
				'post'         => $order_id,
				'action'       => 'edit',
				'bewpi_action' => "view",
			), admin_url( 'post.php' ) ), "view", 'nonce' );
			$url = apply_filters( 'bewpi_pdf_invoice_url', $url, $order_id, "view" ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Hook of the WooCommerce PDF Invoices plugin we integrate with.
		}else if( $invoicegenerator == "getyourbill_template" ){
			$url = admin_url('admin-ajax.php').'?action=generate_getyourbill_template&order_ids='.$order_id;
		}
		return $url;
	}

	public static function get_invoice_pdf_encode( $order = null ) {
		if( is_null($order) )
			return null;
		$invoicegenerator = self::get_info("config_invoicegenerator");
		$invoice = null;
		if( $invoicegenerator == "woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php" ){
			$document = wcpdf_get_invoice($order);
			$invoice = $document->get_pdf();
		}else if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
			$order_id = $order->get_order_number();
			$full_path = \BEWPI_Abstract_Invoice::exists( $order_id );
			if ( false === $full_path ) {
				$invoice = new \BEWPI_Invoice( $order_id );
				$invoice->generate();
				$full_path = \BEWPI_Abstract_Invoice::exists( $order_id );
			}else{
				$invoice = new \BEWPI_Invoice( $order_id );
				$invoice->update();
				$full_path = \BEWPI_Abstract_Invoice::exists( $order_id );
			}
			$invoice = file_get_contents( $full_path );
		}else if( $invoicegenerator == "getyourbill_template" ){
			$order_id = $order->get_order_number();
			$invoice = apply_filters( 'getyourbill_invoice_template', $order_id );
		}
		if( is_null($invoice) )
			return null;
		return base64_encode($invoice);
	}


	public static function annulla_fattura_order( $order_id = '', $force = false ) {
		$invoicegenerator = self::get_info("config_invoicegenerator");
		$invoice = null;
		if( $invoicegenerator == "woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php" ){
			delete_post_meta( $order_id, "_wcpdf_invoice_number" );
			delete_post_meta( $order_id, "_wcpdf_invoice_number_data" );
			delete_post_meta( $order_id, "_wcpdf_invoice_settings" );
			delete_post_meta( $order_id, "_wcpdf_invoice_date" );
			delete_post_meta( $order_id, "_wcpdf_invoice_date_formatted" );
			$order = wc_get_order( $order_id );
			if( !is_wp_error( $order ) ) {
				$document = wcpdf_get_invoice($order);
				if( !empty( $document ) ){
					$document = wcpdf_get_document( $document->get_type(), $order );
					if ( !empty($document) && $document->exists() ) {
						$document->delete();
					}
				}
			}
		}else if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
			\BEWPI_Invoice::delete( $order_id );
		}
		delete_post_meta($order_id, "getyourbill_send_ok_f_json");
		delete_post_meta($order_id, "getyourbill_status_invoice_ok");
		delete_post_meta($order_id, "getyourbill_send_error");
		delete_post_meta($order_id, "getyourbill_recoverCode");
		delete_post_meta($order_id, "getyourbill_send_ok_on_gyb");
		delete_post_meta($order_id, "getyourbill_invoice");
		delete_post_meta($order_id, "getyourbill_iniquenumberinvoice");
		delete_post_meta($order_id, "getyourbill_completenumber");
		delete_post_meta($order_id, "getyourbill_invoice_year");
		delete_post_meta($order_id, "getyourbill_is_send_sdi");
		return true;
	}

	public static function get_itemVatCode( $label = '' ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared query on plugin order meta; result must be fresh.
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_excerpt = %s",
				'GetYourBillImposta',
				$label
			)
		);

		return ( null !== $result ) ? $result : null;
	}

	private static function get_VatKind( $itemVatCode = "" ) {
		if( isset(GetYourBillTaxes[$itemVatCode]) && isset(GetYourBillTaxes[$itemVatCode]["VatKind"])){
			return GetYourBillTaxes[$itemVatCode]["VatKind"];
		}
		return null;
	}


	public static function set_buug_dattura_in_JSON($order_id, $order_note, $message ){
		update_post_meta( $order_id, "getyourbill_send_error", "bug_fattura" );
		update_post_meta( $order_id, "getyourbill_if_error_mess", $message );
		\models\APIGetyourbill::set_error_log( sprintf( GetYourBill_Messaggi_Error["order_errormess"], $message." order id : ".$order_id ) );
		$order_note->add_order_note( sprintf( GetYourBill_Messaggi_Error["order_errormess"], $message ) ) ;
		$order_note->save();
		update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
	}

	public static function prepare_fattura_in_JSON($docType, $order = null, $customer = null,  $transactionId = "", $recoverCode = "", $order_date = "", $note = "", $order_note = null, $order_id = 0, $billing_user_type = "psoleproprietor") {
		if( is_null($customer) )
			return null;
		$invoice = array(
			"transactionId" => $transactionId,
			"recoverCode" => $recoverCode,
			"autogenerateDocNumber" => true,
			"docDate" => $order_date,
			"docPdf" => "",
			"docType" => $docType,
			"customer" => $customer,
			"rows" => array( ),
			"vats" => array( ),
			"paymentCondition" => "TP02",
			"paymentDetails" => array( )
		);
		$wc_prices_tax = wc_prices_include_tax();
		$is_PA = false;
		if( $billing_user_type == "pa" ){
			$invoice["isPA"] = true;
			$is_PA = true;
		}


		if( $is_PA ){
			$billing_cup = get_post_meta($order_id, "_billing_cup", true );
			$billing_cig = get_post_meta($order_id, "_billing_cig", true );
			$invoice["docPurchaseOrder"] = array(
				"completeDocNumber" => $order_id,
				"cupCode" => $billing_cup,
				"cigCode" => $billing_cig,
			);
		}

		$order_shipping_total = $order->get_shipping_total(); //totale di spedizione
		$order_shipping_tax = $order->get_shipping_tax(); // tassa applicata alla spedizione
		$order_total_discount = $order->get_discount_total(); // totale sconto, lo sottraggo al totale e al subtotale
		$TotalTax = $order->get_total();

		$VatAmount_totale = $order->get_total_tax();

		$virtualStampAmount = 0;
		$virtualStampAmountif = 0;
		$config_bollo = \WC_Settings_GetyourbillTabs::get_info("config_bollo");
		if( $config_bollo == "yes" && $VatAmount_totale == 0 ){
			$virtualStampAmountif = floatval(\WC_Settings_GetyourbillTabs::get_info("config_bollo_totale"));
			if( $TotalTax > $virtualStampAmountif ){
				$virtualStampAmount = floatval(\WC_Settings_GetyourbillTabs::get_info("config_bollo_quanto"));
			}
		}

		$virtualStampAmount = apply_filters( 'getyourbill_virtualStampAmount', $virtualStampAmount, $order );
		if( floatval($virtualStampAmount) > 0  ){
			$invoice["virtualStampAmount"] = floatval($virtualStampAmount);
		}


		$row_discout = 0;
		$row_tax = 0;

		$coupons = $order->get_coupon_codes();
		$all_vats = array();
		$totale_controlatto = 0;
		$has_tax = true;


		$taxes_list = array();
		foreach($order->get_items( 'tax' ) as $item_id => $item_tax)
		{
			$tax_data = $item_tax->get_data();
			$taxes_list[] = $tax_data;
		}

		$discount_total = 0;

		foreach ($order->get_items() as $key => $prod) {

			$order_product = $prod->get_data(); // ordine info
			$wc_product = $prod->get_product(); // product wc info
			$product_id = $order_product['product_id'];
			$product = wc_get_product($product_id); // single product
			$_getyourbill_box_text = get_post_meta($product_id, "_getyourbill-box-text", true);

			if( !empty($product) ){

				$itemPrice = 0;
				$vatAmount = 0;
				$vatPercent = 0;
				$label = "";
				$rate_id = null;

				$taxes = $order_product["taxes"];
				if( $has_tax ){
					$taxes = $order_product["taxes"];
					if(is_array($taxes) && array_key_exists("total", $taxes) ){
						foreach ( $taxes["total"] as $ktax => $ktax_item ) {
							if( $ktax_item != "" ){
								foreach ( $taxes_list as $klist => $klist_item ) {
									if( $ktax == $klist_item["rate_id"] ){
										$vatPercent = $klist_item["rate_percent"];
										$label = $klist_item["label"];
										$rate_id = $klist_item["rate_id"];
									}
								}
							}
						}
					}
				}

				$quantity = (int)$order_product['quantity'];


				/*

      if( $has_tax ){
                      $tax_rates = \WC_Tax::get_base_tax_rates($product->get_tax_class(true));
                      $tax_rates_wc_product = \WC_Tax::get_base_tax_rates($wc_product->get_tax_class(true));
                  }

                  if( $has_tax ){
                      if( !empty($tax_rates) && is_array($tax_rates) && count($tax_rates)>0 ){
                          $tax = reset($tax_rates);
                          $vatPercent = $tax['rate'];
                          $label = $tax['label'];
                      }
                  }

    */

				$vatCode = self::get_itemVatCode( $rate_id );
				if( is_null($rate_id) ){
					self::set_buug_dattura_in_JSON($order_id, $order_note, sprintf( GetYourBill_Messaggi_Error["ivaerrorproduct"], $wc_product->get_title() ) );
					return false;
				}

				$VatKind = self::get_VatKind( $vatCode );

				if( $wc_prices_tax ){
					$itemPrice = \models\APIGetyourbill::fix_numero( (($order_product['subtotal'] + $order_product['subtotal_tax'])/$quantity ));
				}else{
					$itemPrice = \models\APIGetyourbill::fix_numero( \models\APIGetyourbill::fix_numero($order_product['subtotal']/$quantity) * ( 1+($vatPercent/100)) );
				}

				$grossAmount = \models\APIGetyourbill::fix_numero( $itemPrice * $quantity );

				$discount = \models\APIGetyourbill::fix_numero( ($order_product['subtotal'] - $order_product['total']) * ( 1+($vatPercent/100)) );

				if( $discount > 0 ){
					$row_discout += $discount;
				}else{
					$discount = 0;
				}

				$discount_total += $discount;

				$grossAmount_vats = \models\APIGetyourbill::fix_numero($grossAmount - $discount);

				$netAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats /( 1+($vatPercent/100) ) );
				$vatAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats - $netAmount );


				$code_prodotto = "";
				if( !is_null($VatKind) )
					$code_prodotto .= $VatKind."_";
				if( !is_null($vatCode) )
					$code_prodotto .= $vatCode."_";

				$code_prodotto = sanitize_title($code_prodotto);
				$code_prodotto .= $wc_product->get_sku();

				$row_item = array(
					"code" => $code_prodotto,
					"itemVatAmount" => $vatPercent,
					"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount, 2),
					"quantity" => $quantity,
					"itemPrice" => \models\APIGetyourbill::fix_numero($itemPrice, 2),
					"itemDescription" => $wc_product->get_title()
				);
				if( $_getyourbill_box_text !== "" ){
					$row_item["note"] = $_getyourbill_box_text;
				}

				$totale_controlatto = \models\APIGetyourbill::fix_numero($totale_controlatto)+$grossAmount;

				if( !is_null($vatCode) )
					$row_item["itemVatCode"] = $vatCode;
				if( !is_null($VatKind) )
					$row_item["itemVatKind"] = $VatKind;

				if( $discount > 0 ){
					$row_item["discount"] = $discount;
				}

				$row_item_iva = array(
					"vatPercent" => $vatPercent,
					"vatAmount" => \models\APIGetyourbill::fix_numero($vatAmount, 2),
					"netAmount" => \models\APIGetyourbill::fix_numero($netAmount, 2),
					"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount_vats, 2),
				);

				if( !is_null($vatCode) )
					$row_item_iva["vatCode"] = $vatCode;

				$all_vats[$vatPercent."_".$vatCode."_".$VatKind][] = $row_item_iva;
				$invoice["rows"][] = $row_item;
			}
		}

		// dati di spedizione

		foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){

			$shipping_item_data = $shipping_item_obj->get_data();

			$vatAmount = 0;
			$vatPercent = 0;
			$label = "";
			$rate_id = null;

			$shipping_data_total_tax = $shipping_item_data['total_tax'];
			$fee_item_tax_zero_rate = $shipping_data_total_tax == 0; // restituisce true o false

			// gestione tax
			if( !$fee_item_tax_zero_rate ){
				$taxes = $shipping_item_data["taxes"];
				if(is_array($taxes) && array_key_exists("total", $taxes) ){
					foreach ( $taxes["total"] as $ktax => $ktax_item ) {
						if( $ktax_item != "" ){
							foreach ( $taxes_list as $klist => $klist_item ) {
								if( $ktax == $klist_item["rate_id"] ){
									$vatPercent = $klist_item["rate_percent"];
									$label = $klist_item["label"];
									$rate_id = $klist_item["rate_id"];
								}
							}
						}
					}
				}
			}else{
				$vatPercent = 0;
				$label = GetYourBillTaxes["ESN4"]["name"];
			}

			if( is_null($rate_id) ){
				$vatCode = "ESN4"; // esente
			}else{
				$vatCode = self::get_itemVatCode( $rate_id );
			}

			if( is_null($vatCode) ){
				self::set_buug_dattura_in_JSON($order_id, $order_note, sprintf( GetYourBill_Messaggi_Error["ivaerrorshipping"], $shipping_item_data['name'] ) );
				return false;
			}

			$VatKind = self::get_VatKind( $vatCode );


			$itemPrice = \models\APIGetyourbill::fix_numero( ($shipping_item_data['total'] + $shipping_data_total_tax) );

			$grossAmount = \models\APIGetyourbill::fix_numero( $itemPrice );

			$grossAmount_vats = \models\APIGetyourbill::fix_numero($grossAmount);

			if( !$fee_item_tax_zero_rate ){
				$netAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats /( 1+($vatPercent/100) ) );
			}else{
				$netAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats );
			}
			$vatAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats - $netAmount );


			$code_prodotto = "";
			if( !is_null($VatKind) )
				$code_prodotto .= $VatKind."_";
			if( !is_null($vatCode) )
				$code_prodotto .= $vatCode."_";

			$code_prodotto = sanitize_title($code_prodotto);
			$code_prodotto .= sanitize_title($shipping_item_data['method_title']);

			$row_item = array(
				"code" => $code_prodotto,
				"itemVatAmount" => $vatPercent,
				"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount, 2),
				"quantity" => 1,
				"itemPrice" => \models\APIGetyourbill::fix_numero($itemPrice, 2),
				"itemDescription" => $shipping_item_data['name'],
			);
			$totale_controlatto = \models\APIGetyourbill::fix_numero($totale_controlatto)+$grossAmount;

			if( !is_null($vatCode) )
				$row_item["itemVatCode"] = $vatCode;
			if( !is_null($VatKind) )
				$row_item["itemVatKind"] = $VatKind;

			$row_item_iva = array(
				"vatPercent" => $vatPercent,
				"vatAmount" => \models\APIGetyourbill::fix_numero($vatAmount, 2),
				"netAmount" => \models\APIGetyourbill::fix_numero($netAmount, 2),
				"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount_vats, 2),
			);

			if( !is_null($vatCode) )
				$row_item_iva["vatCode"] = $vatCode;


			$all_vats[$vatPercent."_".$vatCode."_".$VatKind][] = $row_item_iva;
			$invoice["rows"][] = $row_item;
		}




		// gestione delle commissioni

		foreach ($order->get_items('fee') as $fee_id => $fee_item) {

			$fee_item_data = $fee_item->get_data();

			$vatAmount = 0;
			$vatPercent = 0;
			$label = "";
			$rate_id = null;

			$fee_item_tax_zero_rate = $fee_item_data['total_tax'] == 0; // restituisce true o false
			// gestione tax
			if( !$fee_item_tax_zero_rate ){
				$taxes = $fee_item_data["taxes"];
				if(is_array($taxes) && array_key_exists("total", $taxes) ){
					foreach ( $taxes["total"] as $ktax => $ktax_item ) {
						if( $ktax_item !== "" ){
							foreach ( $taxes_list as $klist => $klist_item ) {
								if( $ktax == $klist_item["rate_id"] ){
									$vatPercent = $klist_item["rate_percent"];
									$label = $klist_item["label"];
									$rate_id = $klist_item["rate_id"];
								}
							}
						}
					}
				}
			}else{
				$vatPercent = 0;
				$label = GetYourBillTaxes["ESN4"]["name"];
			}

			if( is_null($rate_id) ){
				$vatCode = "ESN4"; // esente
			}else{
				$vatCode = self::get_itemVatCode( $rate_id );
			}

			if( is_null($vatCode) ){
				self::set_buug_dattura_in_JSON($order_id, $order_note, sprintf( GetYourBill_Messaggi_Error["ivaerrorfee"], $fee_item_data['name'] ) );
				return false;
			}

			$VatKind = self::get_VatKind( $vatCode );

			$itemPrice = \models\APIGetyourbill::fix_numero( $fee_item_data['total'] + $fee_item_data['total_tax']);


			$grossAmount = \models\APIGetyourbill::fix_numero( $itemPrice );

			$grossAmount_vats = \models\APIGetyourbill::fix_numero($grossAmount);

			if( !$fee_item_tax_zero_rate ){
				$netAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats /( 1+($vatPercent/100) ) );
			}else{
				$netAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats );
			}
			$vatAmount = \models\APIGetyourbill::fix_numero( $grossAmount_vats - $netAmount );

			$code_prodotto = "";
			if( !is_null($VatKind) )
				$code_prodotto .= $VatKind."_";
			if( !is_null($vatCode) )
				$code_prodotto .= $vatCode."_";

			$code_prodotto = sanitize_title($code_prodotto);
			$code_prodotto .= sanitize_title($fee_item_data['name']);

			$row_item = array(
				"code" => $code_prodotto,
				"itemVatAmount" => $vatPercent,
				"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount, 2),
				"quantity" => 1,
				"itemPrice" => \models\APIGetyourbill::fix_numero($itemPrice, 2),
				"itemDescription" => $fee_item_data['name'],
			);
			$totale_controlatto = \models\APIGetyourbill::fix_numero($totale_controlatto)+$grossAmount;

			if( !is_null($vatCode) )
				$row_item["itemVatCode"] = $vatCode;

			if( !is_null($VatKind) )
				$row_item["itemVatKind"] = $VatKind;

			$row_item_iva = array(
				"vatPercent" => $vatPercent,
				"vatAmount" => \models\APIGetyourbill::fix_numero($vatAmount, 2),
				"netAmount" => \models\APIGetyourbill::fix_numero($netAmount, 2),
				"grossAmount" => \models\APIGetyourbill::fix_numero($grossAmount_vats, 2),
			);

			if( !is_null($vatCode) )
				$row_item_iva["vatCode"] = $vatCode;


			$all_vats[$vatPercent."_".$vatCode."_".$VatKind][] = $row_item_iva;
			$invoice["rows"][] = $row_item;

		}

		// castelletto  IVa
		$row_item_iva = array( );
		foreach ($all_vats as $key_vat => $vat) {
			if( !array_key_exists($key_vat, $row_item_iva) ){
				$row_item_iva[$key_vat] = array();
			}
			if( !array_key_exists("vatPercent", $row_item_iva[$key_vat]) ){
				$row_item_iva[$key_vat]["vatPercent"] = "";
			}
			if( !array_key_exists("vatAmount", $row_item_iva[$key_vat]) ){
				$row_item_iva[$key_vat]["vatAmount"] = 0;
			}
			if( !array_key_exists("netAmount", $row_item_iva[$key_vat]) ){
				$row_item_iva[$key_vat]["netAmount"] = 0;
			}
			if( !array_key_exists("grossAmount", $row_item_iva[$key_vat]) ){
				$row_item_iva[$key_vat]["grossAmount"] = 0;
			}
			if( !array_key_exists("vatCode", $row_item_iva[$key_vat]) ){
				$row_item_iva[$key_vat]["vatCode"] = "";
			}
			foreach ($vat as $vat_info) {
				if( array_key_exists("vatPercent", $vat_info) ){
					$row_item_iva[$key_vat]["vatPercent"] = $vat_info["vatPercent"];
				}
				if( array_key_exists("vatAmount", $vat_info) ){
					$row_item_iva[$key_vat]["vatAmount"] += $vat_info["vatAmount"];
				}
				if( array_key_exists("netAmount", $vat_info) ){
					$row_item_iva[$key_vat]["netAmount"] += $vat_info["netAmount"];
				}
				if( array_key_exists("grossAmount", $vat_info) ){
					$row_item_iva[$key_vat]["grossAmount"] += $vat_info["grossAmount"];
				}
				if( array_key_exists("vatCode", $vat_info) ){
					$row_item_iva[$key_vat]["vatCode"] = $vat_info["vatCode"];
				}
			}
		}

		foreach ($row_item_iva as $vat => $data_vat) {
			$data_vat["vatAmount"] = \models\APIGetyourbill::fix_numero($data_vat["vatAmount"]);
			$data_vat["netAmount"] = \models\APIGetyourbill::fix_numero($data_vat["netAmount"]);
			if( false && $is_PA ){
				$data_vat["grossAmount"] = \models\APIGetyourbill::fix_numero( $data_vat["netAmount"] );
			}else{
				$data_vat["grossAmount"] = \models\APIGetyourbill::fix_numero( $data_vat["vatAmount"]+$data_vat["netAmount"]);
			}
			if( $is_PA ){
				$data_vat["vatCollection"] = "S";
			}

			//if( $data_vat["vatCode"] != "" ){
			$invoice["vats"][] = $data_vat;
			//}
		}

		$Total = \models\APIGetyourbill::fix_numero( ($order->get_subtotal() + $order_shipping_tax + $order_shipping_total - $order_total_discount ) + $VatAmount_totale );

		if( $is_PA ){
			$invoice["total"] = \models\APIGetyourbill::fix_numero($order->get_total() + $VatAmount_totale, 2);
		}else{
			$invoice["total"] = \models\APIGetyourbill::fix_numero($order->get_total(), 2);
		}

		if( $discount_total > 0 ){
			//	$invoice["discount"] = $discount_total;
		}

		$totale_controlatto = \models\APIGetyourbill::fix_numero($totale_controlatto, 2);

		/*	if( $totale_controlatto != $invoice["total"] ){
                if( isset( $_GET["ttest"] ) ){
                    var_dump($totale_controlatto);
                    var_dump($invoice);
                }
                self::set_buug_dattura_in_JSON($order_id, $order_note, GetYourBill_Messaggi_Error["order_total_errors"] );
                return false;
            }
    */

		$pay_mod = self::get_paymets_method_getyourbill($order->get_payment_method());

		if( !is_null($pay_mod ) ){
			$pay_mod = array(
				"modality" => (is_array($pay_mod))?implode("",$pay_mod):$pay_mod,
				"amount" => $invoice["total"],
			);
			$invoice["paymentDetails"][] = $pay_mod;
		}
		if( $note != "" ){
			$invoice["note"] = $note;
		}
		return $invoice;
	}


	public function register_invoice_to_get_your_bill($order_id, $invoice_nr = 0 ){
		// get_recoverCode
		global $GetYourBill;
		$api = new \models\APIGetyourbill();
		$long_int = $GetYourBill->WC_Settings_GetyourbillTabs->get_new_long_int($GetYourBill->WC_Settings_GetyourbillTabs->generate_long_int());
		$recoverCode = $api->get_recoverCode( $order_id );
		if( !is_null($recoverCode) ){
			self::set_info_order($order_id, "getyourbill_recoverCode", $recoverCode);
			self::set_info_order($order_id, "getyourbill_iniquenumberinvoice", $long_int);
		}
		return $recoverCode;
	}


	public static function is_last_order( $order_id = '' ) {
		$args = array(
			'p' => $order_id,
			'post_type' => 'shop_order'
		);
		$wp_query = new WP_Query( $args );
		return ($wp_query->current_post + 1) < ($wp_query->post_count);
	}

	public static function get_info( $info = '' ) {
		return get_option( "wc_settings_getyourbill_settings_".$info , null );
	}

	public static function set_info( $info = '', $val = '' ) {
		return update_option( "wc_settings_getyourbill_settings_".$info , $val );
	}

	public static function set_log( $val = '' ) {
		$all_logs = gmdate("F j, Y, g:i a")."\n\n ".$val." \n\n<hr>";
		$all_logs .= self::get_info("logs");
		return update_option( "wc_settings_getyourbill_settings_logs" , $all_logs );
	}


	public static function send_log($mod = "") {
		$all_logs = self::get_info("logs");
		if( $all_logs == ""){
			return true;
		}
		$getyourbill_supportID = self::get_info("getyourbill_supportID");
		$to = "getyourbillbugs@mab21.com";
		$subject = 'ID support : '.$getyourbill_supportID." bugs del : ".gmdate("d/m/Y h:m");
		$body = '<h4>ID support : '.$getyourbill_supportID.'</h4>';
		$body .= '<p>url '.get_home_url().'</p>';
		$body .= '<p>';
		$body .= $all_logs;
		$body .= '</p>';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$send = false;
		if( $send = wp_mail( $to, $subject, $body, $headers ) ){
			$all_logs = "";
			$bugautosend = self::get_info("bugautosend");
			if( $bugautosend == "yes" ){
				delete_transient( 'getyourbillbugs' );
			}
		}else{
			$all_logs .= "\n\n\n <span style='color:red'>".gmdate("F j, Y, g:i a")." error send log send<span>";
		}
		update_option( "wc_settings_getyourbill_settings_logs" , $all_logs );
		return $send;
	}



	public static function get_paymets_method_name( $key = ""){
		$pay_setup = self::get_info('payments_'.$key);
		if( !empty($pay_setup) ){
			if( array_key_exists($pay_setup, GetYourBillPayments) ){
				return GetYourBillPayments[$pay_setup];
			}
		}
		return null;
	}
	public static function get_paymets_method_getyourbill( $key = ""){
		return self::get_info('payments_'.$key);
	}

}



?>
