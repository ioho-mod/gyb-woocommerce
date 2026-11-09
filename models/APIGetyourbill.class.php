<?php
namespace models;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'GetYourBill' ) || ! defined( 'GetYourBillApiUrl' ) ) {
	exit();
}

	class APIGetyourbill
	{
		public static $access_token = "";
		public static $refresh_token = "";
		public static $live_test_mode = "no";
		public static $is_valid_refresh_token = "";
		public static $call_url_api = GetYourBillApiUrl;
		public static $timeout = 100;
		public function __construct() {
			self::$live_test_mode = \models\GetyourbillApp::get_info("config_isonline", 'yes');
			self::$refresh_token = \models\GetyourbillApp::get_info( "apikey".(( self::$live_test_mode !== "yes" )?"":"_test") );
			self::$is_valid_refresh_token = \models\GetyourbillApp::get_info("isokkey");
      self::$call_url_api = GetYourBillApiUrl;

			if( self::$is_valid_refresh_token === "success" ){
				self::$access_token = self::get_access_token();
			}
		}

		private function get_server_name() {
			if ( ! isset( $_SERVER['SERVER_NAME'] ) || '' === $_SERVER['SERVER_NAME'] ) {
				return wp_parse_url( home_url(), PHP_URL_HOST );
			}

			return sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
		}

		private function get_request_args( $headers = array(), $method = 'GET', $body = null ) {
			$args = array(
				'method'      => $method,
				'timeout'     => self::$timeout,
				'redirection' => 0,
				'headers'     => $headers,
			);

			if ( null !== $body ) {
				$args['body'] = $body;
			}

			return $args;
		}

		public function get_info_getyourbill(){
			$res = $this->get_("Device/Code", array(), true, "GET");
			if( is_array($res) && array_key_exists("license", $res) ){
				return $res;
			}
			return null;
		}

		public function veriffica_access_token($refresh_token_param = null, $work_mode_param = "live"){

      $work_mode = (( self::$live_test_mode !== "yes" )?"":"_test");
      if( is_null($refresh_token_param) ){
  			$json = array(
  				"token" => self::$refresh_token
  			);
      }else{
        self::set_error_log( "Inizio verifica ".$work_mode_param." key " );
  			$json = array(
  				"token" => $refresh_token_param
  			);
        $work_mode = $work_mode_param;

        if( $work_mode == "test"  ){
          self::$call_url_api = GetYourBillApiUrlTEST;
        }else{
          self::$call_url_api = GetYourBillApiUrlLIVE;
        }

      }
			$res = $this->get_("token", $json, null );


			if( is_array($res) && array_key_exists("access_token", $res) && array_key_exists("refresh_token", $res) ){
  			self::$access_token = $res["access_token"];
				self::set_access_token("access_token".$work_mode, $res["access_token"]);
				return true;
			}else{
				return false;
			}
		}

		public function put_invoce_number( $docDate = "", $recoverCode = "", $transactionId = "", $completeDocNumber = "", $docPDF = "" ){

			$JSON = array(
				"docDate" => $docDate,
				"recoverCode" => $recoverCode,
				"transactionId" => $transactionId,
				"completeDocNumber" => $completeDocNumber,
				"docPdf" => $docPDF,
			);
			return $this->get_("Doc/Pdf", $JSON, true, "PUT");
		}




		public function get_invoce_number( $docDate = "", $recoverCode = "", $transactionId = ""){

			$JSON = array(
				"docDate" => $docDate,
				"recoverCode" => $recoverCode,
				"transactionId" => $transactionId,
			);
			$res = $this->get_("Doc/CompleteDocNumber/", $JSON, true);
			if( is_array($res) && array_key_exists("completeDocNumber", $res) ){
				return $res;
			}
			return null;
		}

		public function get_recoverCode( $invoice_bumber = 0){
			$res = $this->get_("Doc/RecoverCode/".$invoice_bumber, array(), true, "GET");
			if( is_array($res) && array_key_exists("recoverCode", $res) ){
				return $res["recoverCode"];
			}
			return null;
		}

		public function get_status_fattura( $recoverCode = "" ){
			return $res = $this->get_("Doc/State/".$recoverCode, array(), true, "GET");
		}

		public function annullamento_fattura( $recoverCode = "" ){
			//self::set_error_log( "DELETE DOC ".$recoverCode." date ".date("d-m-Y h:m") );
			return $res = $this->get_("Doc/".$recoverCode, array(), true, "DELETE");
		}

		public function send_fattura_json( $JSON = array() ){
			$res = $this->get_("Doc", $JSON, true);
			//self::set_error_log( "send_fattura_json ".wp_json_encode($JSON) );
			//self::set_error_log( "send_fattura_json   :".((is_array($res))?print_r( $res, true ):$res)  );
			if( $res === true){
				return true;
			}
			return $res;
		}


		public function get_anagrafiche( $Code = "", $VatNumber = "", $TaxCode = "", $country = "IT", $pa = "" ){
			//self::set_error_log( "get_anagrafiche ".$Code."  ".$VatNumber."  ".$TaxCode );
			$JSON = array(
        "Country" => $country,
			);
      if( $Code != "" ){
  			$JSON = array(
  				"Code" => $Code,
  			);
      }else if( $VatNumber != "" ){
  			$JSON = array(
  				"VatNumber" => $VatNumber,
          "Country" => $country,
  			);
      }else if( $TaxCode != "" ){
  			$JSON = array(
  				"TaxCode" => $TaxCode,
          "Country" => $country,
  			);
      }else if( $pa != "" ){
  			$JSON = array(
  				"IPACode" => $pa,
  			);
      }

      //self::set_error_log( "JSON  anagrafiche ".wp_json_encode($JSON) );
			$res = $this->get_('Location/Check', $JSON, true);
			foreach($res as $index => $val) {
				if( is_null($val) || $val == "0"){
					$res[$index] = "";
				}
			}
		//	self::set_error_log( "res  anagrafiche ".wp_json_encode($res) );
			return $res;
		}


		public static function get_access_token(){
      $work_mode = (( self::$live_test_mode !== "yes" )?"":"_test");
			$access_token = \models\GetyourbillApp::get_info("access_token".$work_mode);
			return ( !is_null($access_token) && !empty($access_token) )?$access_token:null;
		}

		public static function set_access_token($info = '', $val = ''){
			return \models\GetyourbillApp::set_info($info, $val);
		}

		public static function set_error_log( $mess = "" ){
			\models\GetyourbillApp::set_log( $mess );
		}

		public static function get_iva_by_numero( $n = 1, $iva = 10 ){
			$ris = ( $n ) * pow(10,2);
			$ris = round($ris);
			return number_format($ris / pow(10,2),2);
		}

		public static function fix_numero( $n = 1, $p = 6 ){

			$ris = ( $n ) * pow(10,2);
			$ris = round($ris);
			return number_format($ris / pow(10,2),$p, '.', '');
		}

		public function refresh_access_token(){
			// refresh_access_token

			$url_api = self::$call_url_api."token";
			$JSON = array(
				"token" => self::$refresh_token
			);
			if( trim($url_api) == ""){
				return json_decode(array(), true);
			}
			$request = wp_remote_request(
				$url_api,
				$this->get_request_args(
					array(
						'Content-Type' => 'application/json',
						'Referer'      => $this->get_server_name(),
					),
					'POST',
					wp_json_encode( $JSON, JSON_FORCE_OBJECT )
				)
			);

			if ( is_wp_error( $request ) ) {
				self::set_error_log( 'Bad request :' . $request->get_error_message() );
				return false;
			}

			$result        = wp_remote_retrieve_body( $request );
			$response_code = (int) wp_remote_retrieve_response_code( $request );
			if ( $response_code !== 200 ) {
				if( $response_code == 401 ){
					self::set_error_log( sprintf( GetYourBill_Messaggi_Error["api_refresh"], wp_json_encode($result) ) );
					return false;
				}else{
					self::set_error_log( "Bad request :".wp_json_encode($result) );
					return false;
				}
			}
			$res = json_decode($result, true);
			if( is_array($res) && array_key_exists("access_token", $res) && array_key_exists("refresh_token", $res) ){
        $work_mode = (( self::$live_test_mode !== "yes" )?"":"_test");
  			self::$access_token = $res["access_token"];
				self::set_access_token("access_token".$work_mode, $res["access_token"]);
				self::set_access_token("apikey".$work_mode, $res["refresh_token"]);
				self::$refresh_token = $res["refresh_token"];
				self::$access_token = $res["access_token"];
				return true;
			}else{
				self::set_error_log( sprintf( GetYourBill_Messaggi_Error["api_response"], wp_json_encode($result) ) );
				return false;
			}
		}


		public function get_support_key($k = null){
			if( is_null($k) ){
				return array();
			}
			$url_api = GetYourBillSupportUrl.GetYourBillSUPPORT."/".$k;
			$request = wp_remote_get(
				$url_api,
				$this->get_request_args(
					array(
						'Accept'       => 'application/json',
						'Content-Type' => 'application/json',
						'Referer'      => $this->get_server_name(),
					)
				)
			);

			if ( is_wp_error( $request ) ) {
				return null;
			}

			$result        = wp_remote_retrieve_body( $request );
			$response_code = (int) wp_remote_retrieve_response_code( $request );
			if ( $response_code !== 200 ) {
				return null;
			}
			$res = json_decode($result, true);
			if( is_array($res) && array_key_exists("stato", $res) && $res["stato"] == 200 ){
				return $res["out"]["key"];
			}
			return null;
		}

		public function get_( $stringURL = "" , $JSON = array(), $header = null, $method = "POST"){

			// if( is_null(self::$access_token) ){
			// 	return array();
			// }
			$url_api = self::$call_url_api.$stringURL;
			if( trim($url_api) == ""){
				return array();
			}

      $lang = get_locale();
      $lang = explode( "_", $lang );
      $lang = strtolower($lang[0]);

      if( !in_array($lang, array("it", "en", "fr")) ){
        $lang = "en";
      }


			$dominio = $this->get_server_name();  // il dominio deve essere uguale al sito che chiama l'api altrimento l'api annulla la conessione

			$headers = array(
				'Referer'         => $dominio,
				'Accept-language' => $lang,
			);

			if( is_null($header) ){
				$headers['Content-Type'] = 'application/json';
			}else{
				$headers['Authorization'] = 'Bearer ' . self::$access_token;
				$headers['Accept']        = 'application/json';
				$headers['Content-Type']  = 'application/json';
			}

			$request = wp_remote_request(
				$url_api,
				$this->get_request_args(
					$headers,
					$method,
					( $method == "POST" ||  $method == "PUT" ) ? wp_json_encode( $JSON ) : null
				)
			);

			if ( is_wp_error( $request ) ) {
				return array("error" => $request->get_error_message(), "code" => 0 );
			}

			$result        = wp_remote_retrieve_body( $request );
			$response_code = (int) wp_remote_retrieve_response_code( $request );
			if ( $response_code !== 200 ) {
				if(array_key_exists("docPdf", $JSON))
					$JSON["docPdf"] = "eliminato per peso";
				if ( $response_code == 204 ) {
					return true; // Response Status in caso di successo per invio della fattura al panel nel formato JSON
				}else if( $response_code == 400 ){
					self::set_error_log(  sprintf( GetYourBill_Messaggi_Error["api_badrequest"] , wp_json_encode($result), $response_code) );
					return array("error" => $result, "code" => $response_code);
				}else if( $response_code == 401 ){
					if( $this->refresh_access_token() ){
						return $this->get_($stringURL, $JSON, $header, $method );
					}
				}else{
					self::set_error_log(  sprintf( GetYourBill_Messaggi_Error["api_curl_err"], wp_json_encode($result), $stringURL, wp_json_encode($JSON), $response_code) );
				}
				return array("error" => $result, "code" => $response_code );
			}
			return @json_decode($result, true);
		}


	}

?>
