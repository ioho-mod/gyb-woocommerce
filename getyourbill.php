<?php
/*
Plugin Name: GetYourBill
Plugin URI:  https://www.getyourbill.com/getyourbill
Description: Create your invoices with GetYourBill
Version: 1.1.15
Text Domain: getyourbill
Author: GetYourBill
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: woocommerce
Tested up to: 7.1
WC requires at least: 8.0
WC tested up to: 10.8
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

define( 'GetYourBill', true );
define( 'GetYourBillDEVMOD', isset( $_COOKIE['GetYourBillDEVMOD'] ) );
define( 'TABGetYourBillNAME', "GetYourBill" );
define( 'GetYourBillImposta', "GetYourBillImposta" );
define( 'GetYourBillPATH', plugin_dir_path( __FILE__ ) );
define( 'GetYourBillURL', plugin_dir_url( __FILE__ ) );
define( 'GetYourBillFILE',  __FILE__ );
define( 'GetYourBillApiUrlTEST', "https://coretest.getyourbill.com/api/v3/" );
define( 'GetYourBillApiUrlLIVE', "https://core.getyourbill.com/api/v3/" );
$getyourbill_isonline = get_option( "wc_settings_getyourbill_settings_config_isonline" , "yes" );
if( $getyourbill_isonline === "yes" ){
  define( 'GetYourBillApiUrl', GetYourBillApiUrlTEST );
}else{
  define( 'GetYourBillApiUrl', GetYourBillApiUrlLIVE );
}
define( 'GetYourBillSupportUrl', "https://api.mab21.com/getyourbill/" );
define( 'GetYourBillSUPPORT', "WRlbnRpdHkvY2xhaW1zL3JvbGUiOiJERVZJQ0UiLCJodHR" );
define( 'GetYour24H', DAY_IN_SECONDS*2 );
define( 'GetYourLimitResolveErroreOnLoop', 5 );
define( 'GetYourBillCronStatusTime', 1 );
define( 'GetYourBillCronErrorSendTime', 1 );
define( 'GetYourBillCronStatusTimeProcessiong', 5 );

if (!class_exists("GetYourBill")) :

	include_once(GetYourBillPATH.'/filter/add_filter.php');
	include_once GetYourBillPATH."/src/WC_Settings_GetyourbillTabs.php";
	require_once GetYourBillPATH."/autoload.php";
  if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  }
	class GetYourBill {
		public $vs = "1.1.15";
		public $WC_Settings_GetyourbillTabs = null;
		public  $id	= "getyourbill";
    // Generic sanitizing readers; nonce verification is done by the calling handlers.
    private function get_request_text( $key, $source = INPUT_GET, $default = '' ) {
      $input = INPUT_POST === $source ? $_POST : $_GET; // phpcs:ignore WordPress.Security.NonceVerification
      if ( ! isset( $input[ $key ] ) ) {
        return $default;
      }

      return sanitize_text_field( wp_unslash( $input[ $key ] ) );
    }

    private function get_request_key( $key, $default = '' ) {
      // phpcs:disable WordPress.Security.NonceVerification.Recommended
      if ( ! isset( $_GET[ $key ] ) ) {
        return $default;
      }

      return sanitize_key( wp_unslash( $_GET[ $key ] ) );
      // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    private function get_request_absint( $key, $source = INPUT_GET, $default = 0 ) {
      $input = INPUT_POST === $source ? $_POST : $_GET; // phpcs:ignore WordPress.Security.NonceVerification
      if ( ! isset( $input[ $key ] ) || '' === $input[ $key ] ) {
        return $default;
      }

      return absint( wp_unslash( $input[ $key ] ) );
    }

		/**
		 * Boots the plugin on `init`, so translations are loaded before the
		 * translated message constants in inc/defined.php and inc/messaggi.php are defined.
		 */
		public static function instance() {
			if ( ! isset( $GLOBALS['GetYourBill'] ) || ! ( $GLOBALS['GetYourBill'] instanceof self ) ) {
				add_filter( 'load_textdomain_mofile', array( __CLASS__, 'get_your_bill_load_my_own_textdomain' ), 10, 2 );
				self::getyourbill_load_textdomain();
				include_once GetYourBillPATH . '/inc/defined.php';
				include_once GetYourBillPATH . '/inc/messaggi.php';
				$GLOBALS['GetYourBill'] = new self();
			}
			return $GLOBALS['GetYourBill'];
		}

		public static function activate() {
			self::instance()->getyourbill_register_activation_hook();
		}

		public static function deactivate() {
			flush_rewrite_rules();
		}

		public function __construct()
		{
			$plugin_data = get_plugin_data( __FILE__, false, false );
			$this->vs = $plugin_data["Version"];
      add_action( 'admin_notices', array( $this, 'getyourbill_admin_notices' ) );
      add_action( 'init', array($this, 'GetYourBillImpostType'));
      add_action("add_meta_boxes", array($this, "add_getyourbill_meta_box"));
      add_action( 'save_post',  array($this, 'getyourbill_save_postdata') );

      if ( $this->is_woocommerce_active() ) {
   			$this->WC_Settings_GetyourbillTabs = new WC_Settings_GetyourbillTabs();
  			add_action( 'admin_head', array($this, 'custom_admin_js'));

        //add_filter( 'getyourbill_invoice_template', array( $this, 'getyourbill_invoice_template' ) );
  			add_action('admin_enqueue_scripts',  array($this, 'getyourbill_admin_style'));
  			add_action('wp_enqueue_scripts',  array($this, 'getyourbill_for_setting_up_scripts') );
  			add_action( 'wp_ajax_getyourbill_get_campi_by_user',  array($this, 'getyourbill_get_campi_by_user') ); // only loged
  			add_action( 'wp_ajax_getyourbill_info',  array($this, 'getyourbill_get_get_yourbill_user_info_by') ); // only loged
  			add_action( 'wp_ajax_nopriv_getyourbill_info',  array($this, 'getyourbill_get_get_yourbill_user_info_by') ); // only not loged

  			add_action( 'wp_ajax_getyourbill_add_tax',  array($this, 'getyourbill_add_tax') ); // only loged
  			add_action( 'wp_ajax_getyourbill_cron',  array($this, 'getyourbill_cron') );
  			add_action( 'wp_ajax_nopriv_getyourbill_cron',  array($this, 'getyourbill_cron') ); // called by the server crontab

  			add_action( 'init', function(){
          $generate_template = $this->get_request_text( 'generate_getyourbill_template' );
          $order_ids = $this->get_request_text( 'order_ids' );
          if ( '' !== $generate_template && '' !== $order_ids ) {
      			if( is_user_logged_in() && is_admin() ){
              return apply_filters( 'getyourbill_invoice_template', $order_ids );
            }
          }
        });
      }

		}

    private function is_woocommerce_active() {
      if ( class_exists( 'WooCommerce' ) ) {
        return true;
      }

      if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }

      return function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' );
    }

    function getyourbill_admin_notices() {
      if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'manage_woocommerce' ) ) {
        return;
      }

      if ( ! $this->is_woocommerce_active() ) {
        printf(
          '<div class="notice notice-error"><p>%s</p></div>',
          esc_html( __( 'GetYourBill requires WooCommerce to be installed and active.', 'getyourbill' ) )
        );
        return;
      }

      if ( ! function_exists( 'curl_version' ) ) {
        printf(
          '<div class="notice notice-error"><p>%s</p></div>',
          esc_html( __( 'GetYourBill requires the PHP cURL extension.', 'getyourbill' ) )
        );
      }

      if ( $this->WC_Settings_GetyourbillTabs ) {
        $this->WC_Settings_GetyourbillTabs->getyourbill_notice();
      }
    }

    public static function get_your_bill_load_my_own_textdomain( $mo, $textdomain ) {

      $plugin_domain = 'getyourbill';

  		$mopath = trailingslashit( dirname( $mo ) );
  		$mofile = basename( $mo );

			if ( $plugin_domain == $textdomain ) {
  			if ( file_exists( $mo ) ) {
  				return $mo;
  			}
  			$pofile = str_replace( '.mo', '.po', $mofile );
  			if ( !file_exists( $mopath.$pofile ) ) {
          $bundled_file = GetYourBillPATH . '/languages/'. $mofile;
          if ( file_exists( $bundled_file ) ) {
            return $bundled_file;
          } else {
            return '';
          }
  			}
  		}

  		return $mopath.$mofile;
  	}


    public static function getyourbill_load_textdomain() {

      if ( function_exists( 'determine_locale' ) ) { // WP5.0+
        $locale = determine_locale();
      } else {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
      }
      $locale = apply_filters( 'plugin_locale', $locale, 'getyourbill' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
      $dir    = trailingslashit( WP_LANG_DIR );

      $textdomains = array( 'getyourbill' );

      foreach ( $textdomains as $textdomain ) {
        unload_textdomain( $textdomain );
        load_textdomain( $textdomain, $dir . 'getyourbill/getyourbill-' . $locale . '.mo' );
        load_textdomain( $textdomain, $dir . 'plugins/getyourbill/getyourbill-' . $locale . '.mo' );
        // Source strings are Italian: the bundled languages/ files (en_US, fr_FR) are the fallback until translate.wordpress.org has them.
        load_plugin_textdomain( $textdomain, false, dirname( plugin_basename(__FILE__) ) . '/languages' ); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
      }


    }

		function custom_admin_js() {
			global $wpdb;
			if( is_user_logged_in() && is_admin() ){
				$getyorbill_all_tax = array();
				$get_list_WC_Tax = WC_Settings_GetyourbillTabs::get_list_WC_Tax();
				foreach ( $get_list_WC_Tax[0] as $key => $taxes ) {
					if( count($taxes) >0  ){
						foreach ( $taxes as $tax ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared lookup of the plugin's own tax-mapping posts by excerpt; no WP API for this.
							$q = $wpdb->get_var(
								$wpdb->prepare(
									"SELECT ID FROM {$wpdb->posts} WHERE post_excerpt = %s",
									$tax->tax_rate_id
								)
							);
							if( $q !== null ){
								$post = get_post($q);
								if( !is_wp_error( $post ) ) {
									$getyorbill_all_tax[] = array(
										"id" => $post->ID,
										"edit" => false,
										"tax" => $post->post_excerpt,
										"gyb_tax" => $post->post_content,
										"title_tax" => WC_Settings_GetyourbillTabs::get_name_tax($post->post_excerpt, $get_list_WC_Tax[1]),
										"title_gyb_tax" => WC_Settings_GetyourbillTabs::get_name_gyb_tax($post->post_content, $get_list_WC_Tax[1]),
									);
								}
							}else{
								$getyorbill_all_tax[] = array(
									"id" => 0,
									"edit" => true,
									"tax" => $tax->tax_rate_id,
									"gyb_tax" => "",
									"title_tax" => WC_Settings_GetyourbillTabs::get_name_tax($tax->tax_rate_id, $get_list_WC_Tax[1]),
									"title_gyb_tax" => "",
								);
							}
						}
					}
				}
				?>
				<script type="text/javascript">
					var getyorbill_all_tax = <?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
					echo wp_json_encode( $getyorbill_all_tax, JSON_FORCE_OBJECT );
					?>;
				</script>
				<?php
			}
		}

		function getyourbill_add_tax() {
			check_ajax_referer( 'getyourbill_get_campi_by_user', 'getyourbill_nonce' );
      if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json( array( 'error' => 'forbidden' ), 403 );
      }
			$data = $this->get_request_text( 'data', INPUT_POST );
			$data = '' !== $data ? WC_Settings_GetyourbillTabs::prepara_object_struttura( $data ) : array();
			$res = array("error" => "missing data", "mess" => __("Dati mancanti", 'getyourbill'));
			if( !isset($data['id']) || !isset($data['tax']) ){
				wp_send_json( array("error" => "missing data", "mess" => __("Riprovare", 'getyourbill')) );
			}
			$id = intval($data['id']);
			$gyb_tax = isset($data['gyb_tax']) ? sanitize_text_field( $data['gyb_tax'] ) : "";
      $data['tax'] = sanitize_text_field( $data['tax'] );
			$get_list_WC_Tax = WC_Settings_GetyourbillTabs::get_list_WC_Tax();
			if( $id > 0 && $gyb_tax === "-1" ){
				$r = wp_delete_post($id, true );
				if( $r !== false && !is_null($r) ){
					$res = array( "success" => true, "item" => 0, "action" => "add", "title_tax" => WC_Settings_GetyourbillTabs::get_name_tax($data['tax'], $get_list_WC_Tax[1]), "title_gyb_tax" => "", "tax" => $data['tax'], "gyb_tax" => "" );
				}
			}else if( $id === 0 ){
				$args_item = array(
					'post_title' => "",
					'post_type' => GetYourBillImposta,
					'post_excerpt' => $data['tax'],
					'post_content' => $data['gyb_tax'],
				);
				$item_response = wp_insert_post( wp_slash($args_item));
				if ( !is_wp_error($item_response) ){
					$res = array( "success" =>  true, "item" => $item_response, "action" => "add", "title_tax" => WC_Settings_GetyourbillTabs::get_name_tax($data['tax'], $get_list_WC_Tax[1]), "title_gyb_tax" => WC_Settings_GetyourbillTabs::get_name_gyb_tax($data['gyb_tax'], $get_list_WC_Tax[1]), "tax" => $data['tax'], "gyb_tax" => $data['gyb_tax'] );
				}
			}else if( $id > 0 ){
				$my_post = array(
					  'ID' => $id,
					  'post_type' => GetYourBillImposta,
					  'post_excerpt' => $data['tax'],
					  'post_content' => $data['gyb_tax'],
				);
				$item_response = wp_update_post( $my_post );
				if ( !is_wp_error($item_response) ){
					$res = array("success" => true, "item" => $item_response, "action" => "edit", "title_tax" => WC_Settings_GetyourbillTabs::get_name_tax($data['tax'], $get_list_WC_Tax[1]), "title_gyb_tax" => WC_Settings_GetyourbillTabs::get_name_gyb_tax($data['gyb_tax'], $get_list_WC_Tax[1]),"tax" => $data['tax'], "gyb_tax" => $data['gyb_tax']);
				}
			}
			wp_send_json($res);
		}

		function GetYourBillImpostType() {
			register_post_type( GetYourBillImposta,
				array(
					'public' => false,
					'publicly_queryable' => false,
					'exclude_from_search' => false,
					'show_ui' => false,
					'query_var' => false,
					'capability_type' => 'page',
					'hierarchical' => false,
				)
			);
		}


		function getyourbill_admin_style() {
			wp_enqueue_style('getyourbill_admin_style', GetYourBillURL.'assets/css/admin/admin-style.css', array(), $this->vs);
		}

		function getyourbill_register_activation_hook() {
      $this->GetYourBillImpostType();
			if ( class_exists('WooCommerce') ){
				$this->WC_Settings_GetyourbillTabs->registre_method_pre_pauments_gateways();
				$cron_key = wp_generate_password(20, false);
				$cron_key_wp = get_option( "getyourbill_settings_cron_key" );
				if( empty($cron_key_wp) ){
					update_option( "getyourbill_settings_cron_key" , $cron_key );
				}
			}
      flush_rewrite_rules();
		}
		function getyourbill_register_deactivation_hook() {
      flush_rewrite_rules();
		}

		public function getyourbill_is_schedule( $k = "") {
			$cron_key = (string) get_option( "getyourbill_settings_cron_key" );
			return ( '' !== $cron_key && hash_equals( $cron_key, (string) $k ) );
		}

		/**
		 * Crontab endpoint: admin-ajax.php?action=getyourbill_cron&acckey=<key>.
		 * Authenticated by the secret cron key instead of a nonce, since the caller is the server crontab.
		 */
		public function getyourbill_cron() {
			$access_key = $this->get_request_text( 'acckey' );
			if ( '' !== $access_key && $this->getyourbill_is_schedule( $access_key ) ) {
				wp_send_json( array( 'mes' => $this->getyourbill_do_schedule() ) );
			}
			wp_send_json( array( 'mes' => 'Call admin server' ), 403 );
		}

		public function getyourbill_do_schedule( ) {
			$app = new \models\GetyourbillApp();
			$app->autosedbugs();
			$app->autostatus($this->WC_Settings_GetyourbillTabs);
			return "do_schedule";
		}



		function getyourbill_for_setting_up_scripts() {

            if (!wp_style_is('dashicons', 'queue') && !wp_style_is('dashicons', 'done')) {
                wp_enqueue_style( 'dashicons' );
            }

			wp_register_style('getyourbill_style_front', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), $this->vs);
			wp_register_script ('getyourbill_script_front', plugin_dir_url(__FILE__) . 'assets/js/script.js', array(), $this->vs, true);
			wp_enqueue_style('getyourbill_style_front');
			wp_enqueue_script('getyourbill_script_front');
		}

		function getyourbill_get_campi_by_user() {
			check_ajax_referer( 'getyourbill_get_campi_by_user', 'getyourbill_nonce' );
      if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json( array( 'error' => 'forbidden' ), 403 );
      }
			$resonse = array( "error" => "" );
			$user_id = $this->get_request_absint( 'id' );
			if( $user_id > 0 ){
				$var = $this->WC_Settings_GetyourbillTabs->get_type_user_account($user_id);
				$resonse = array("success" => $var, "user" => $user_id );
			}
			wp_send_json($resonse);
		}

		function getyourbill_get_get_yourbill_user_info_by() {
			check_ajax_referer( 'getyourbill_get_campi_by_user', 'getyourbill_nonce' );
			$resonse = array( "error" => "" );
			$pi = $this->get_request_text( 'pi' );
			$cf = $this->get_request_text( 'cf' );
			$bilid = $this->get_request_text( 'bilid' );
			$country = $this->get_request_text( 'country', INPUT_GET, 'IT' );
			$paid = $this->get_request_text( 'paid' );
			if( $pi != "" || $cf != "" || $bilid != "" || $paid != "" ){

				if( $pi != "" && !$this->WC_Settings_GetyourbillTabs::is_pi($pi) ){
					$resonse = array( "error" => "missing" , "mess" => GetYourBill_Messaggi_Warning["pi_non_valida"]  );
				}else if( $cf != "" && !$this->WC_Settings_GetyourbillTabs::is_cf($cf) ){
					$resonse = array( "error" => "missing" , "mess" => GetYourBill_Messaggi_Warning["cf_non_valida"]  );
				}else if( $bilid != "" && !$this->WC_Settings_GetyourbillTabs::is_gid($bilid) ){
					$resonse = array( "error" => "missing" , "mess" => GetYourBill_Messaggi_Warning["bilid_non_valida"]  );
				}else if( $paid != "" && !$this->WC_Settings_GetyourbillTabs::is_pa($paid) ){
					$resonse = array( "error" => "missing" , "mess" => GetYourBill_Messaggi_Warning["paid_non_valida"]  );
				}else{
					$api = new \models\APIGetyourbill();
					$info = array();
					$info = $api->get_anagrafiche($bilid, $pi, $cf, $country, $paid);
					$info_ok = false; $ind = 0;
					foreach ( $info as $key => $val ) {
						if($val != ""){
							$ind++;
						}
						if( $ind > 2 ){
							$info_ok = true;
						}
					}
					foreach ( GetYourBillRefer as $key => $val ) {
						if( !array_key_exists($key,  $info) ){
							$info[$key] = "";
						}
					}
					if( $info_ok ){
						$resonse = array( "success" => $info, "data_refer" => $this->WC_Settings_GetyourbillTabs->data_refer, "mess" => GetYourBill_altro["dati_autocomplete_success"] );
					}else{
						$resonse = array( "error" => "missing" , "mess" => GetYourBill_altro["dati_autocomplete_missing"]  );
					}
				}
			}else{
				$resonse = array( "error" => "campi", "mess" => GetYourBill_Messaggi_Warning["almenu_uno_dei_campi"] );
			}
			wp_send_json($resonse);
		}

    function getyourbill_meta_box_markup($post)
    {
      wp_nonce_field( 'getyourbill_meta_box', 'getyourbill_meta_box_nonce' );
     ?>
     <div>
         <textarea style="width: 100%;" name="getyourbill-box-text"><?php echo esc_textarea( get_post_meta($post->ID, "_getyourbill-box-text", true) ); ?></textarea>
     </div>
     <?php
    }

    function getyourbill_save_postdata( $post_id ) {
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
      }

      if ( ! isset( $_POST['getyourbill_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['getyourbill_meta_box_nonce'] ) ), 'getyourbill_meta_box' ) ) {
        return;
      }

      if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
      }

      if ( array_key_exists( 'getyourbill-box-text', $_POST ) ) {
        update_post_meta(
          $post_id,
          '_getyourbill-box-text',
          sanitize_textarea_field( wp_unslash( $_POST['getyourbill-box-text'] ) )
        );
      }
    }

    function add_getyourbill_meta_box()
    {
        add_meta_box("getyourbill-meta-box", __("Descrizione prodotto Fatturazione elettronica", "getyourbill"), array( $this, "getyourbill_meta_box_markup"), "product");
    }


	}
    register_activation_hook( __FILE__, array( 'GetYourBill', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'GetYourBill', 'deactivate' ) );
    add_action( 'init', array( 'GetYourBill', 'instance' ), 0 );

endif;
