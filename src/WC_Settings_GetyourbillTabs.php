<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! defined( 'GetYourBill' ) ) {
    exit();
}

class WC_Settings_GetyourbillTabs {

    // Woocomerce config
    public  $id	= "getyourbill";

    public $getyourbillfieldslist = array();
    public $getyourbillfieldsdesc_senza_testo ="";
    public $getyourbillfieldsdesc_opzionale ="";
    public $getyourbillfieldsdesc ="";
    public $getyourbillfieldsoptions = array();
    public $getyourbill_pre_payments = array( );
    public $getyourbillpayments = array( );
    public $getyourbillcontroout = array( );
    public $getyourbillcontrooutsort = array( );
    public $getyourbillplugininvoice = array();
    public $getyourbillinputsreg = array();
    public $getyourbilltypeinvoice = array();
    public $getyourbill_messagges = array();
    public $getyourbill_namesurnamersociale = array();
    public $data_refer = array( );
    public $label;
    public $getyourbillplugininvoice_pre;
    public $statusgenerateinvoice;
    public $getyourbillfieldsoptionsBackEnd;
    public $config_seltype;
    public $getyourbillfieldstypeaccounts;
    public $getyourbillfieldstypeaccountsselect;
    public $s_typeaccount;
    public $request;
    public $request_billing_getyourbillid;
    public $type_input_select;
    private $user_id = null;
    private $order_id = null;
    private $order_tax = array( );
    private $order_field_precompilati = array();
    private $order_object = null;
    private $typeaccount = null;
    public static $in_pay_status = false;
    public static $checkout_update = false;
    public static $billing_user_type = null;
    public static $progress_order_id = null;

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
     * Builds an order action link (gybevent). The link carries a nonce bound to the order,
     * checked by is_authorized_gybevent() before the action runs.
     */
    public static function get_gybevent_url( $order_id, $event, $args = array() ) {
        $edit_url = get_edit_post_link( $order_id, 'raw' );
        if ( empty( $edit_url ) ) {
            $edit_url = admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' );
        }
        $url = add_query_arg( array_merge( array( 'gybevent' => $event ), $args ), $edit_url );
        return wp_nonce_url( $url, 'getyourbill_gybevent_' . $order_id, 'gybnonce' );
    }

    private function is_authorized_gybevent( $order_id ) {
        if ( $order_id <= 0 || ! is_user_logged_in() || ! current_user_can( 'edit_shop_orders' ) ) {
            return false;
        }
        $nonce = isset( $_GET['gybnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['gybnonce'] ) ) : '';
        return false !== wp_verify_nonce( $nonce, 'getyourbill_gybevent_' . $order_id );
    }

    private static function get_post_data_request() {
        $post_data = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only; WooCommerce verifies the checkout nonce.
        if ( isset( $post_data['post_data'] ) && is_string( $post_data['post_data'] ) ) {
            parse_str( $post_data['post_data'], $post_data );
        }

        return $post_data;
    }

    public function __construct() {

        $gyb_isonline = self::get_info( "config_isonline" , "yes" );
        $isokkey = self::get_info( "isokkey".(( $gyb_isonline !== "yes" )?"":"_test") );
        $started = $isokkey == "success"; // && self::is_checked_by_config("started");
        self::$in_pay_status = false;
        self::$checkout_update = false;
        self::$billing_user_type = null;
        self::$progress_order_id = null;
        $this->getyourbillinputsreg = GetYourBillInputsreg;

        $this->getyourbillcontroout = GetYourBillControout;
        $this->data_refer = GetYourBillRefer;

        $this->getyourbilltypeinvoice = GetYourBillInvoice;

        if( function_exists('is_user_logged_in') && is_user_logged_in() ){
            $this->user_id = get_current_user_id();
        }
        if(  !is_null($this->user_id) ){
            $this->order_id = $this->get_request_absint( 'post', INPUT_GET, null );
        }
        $this->label = TABGetYourBillNAME;


        $this->getyourbill_pre_payments = GetYourBillPayDef;
        $this->getyourbillpayments = GetYourBillPayments;
        $this->getyourbillplugininvoice_pre = GetYourBillPluginPDF;
        $this->statusgenerateinvoice = GetYourBillPluginStatusGenerateInvoice;
        $this->getyourbillplugininvoice = $this->getyourbillplugininvoice_pre;

        $this->getyourbillfieldsoptions = GetYourBillOptionsRequestInputs;
        $this->getyourbillfieldsoptionsBackEnd = GetYourBillOptionsRequestInputsBackEnd;

        $this->getyourbillcontrooutsort = GetYourBillOptionsRequestInputsSorByAccount;

        $this->config_seltype = self::get_info("config_seltype");
        $this->getyourbillfieldstypeaccounts = GetYourBillAccounts;
        $this->getyourbillfieldstypeaccountsselect = GetYourBillAccountsSelect;


        $this->s_typeaccount = $this->get_type_user_account();
        $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );
        $this->request = $this->is_request_by_config_request($data);
        $this->getyourbillfieldsdesc_senza_testo = " <abbr class='required' title='" . esc_attr__( 'required', 'getyourbill' ) . "'>*</abbr> ";
        $this->getyourbillfieldsdesc_opzionale = " <abbr class='optional' title='" . esc_attr__( 'optional', 'getyourbill' ) . "'> ( " . esc_html__( 'optional', 'getyourbill' ) . " ) </abbr> ";
        $mess = ( array_key_exists($data["data"], $this->getyourbillfieldsoptions) )?$this->getyourbillfieldsoptions[$data["data"]]:"";
        if( $mess != "" ){
            $this->getyourbillfieldsdesc = $this->getyourbill_get_getyourbillfieldsoptions($this->getyourbillfieldsoptions[$data["data"]]);
        }

        $request_billing_pecaddress = self::is_request_by_config("billing_pecaddress");
        $billing_sdiid = self::is_request_by_config("billing_sdiid");
        $this->request_billing_getyourbillid = false;

        add_action('wp_loaded', function() {

            $post_data = $this->get_post_data_request();
            $gybevent = $this->get_request_key( 'gybevent' );
            $action = $this->get_request_key( 'action' );
            $order_id = $this->get_request_absint( 'post' );
            $is_dev_test = GetYourBillDEVMOD && '' !== $this->get_request_text( 'ttest' );
            if ( '' !== $gybevent && $order_id > 0 && ! $this->is_authorized_gybevent( $order_id ) ) {
                wp_die( esc_html__( 'Link scaduto o non autorizzato.', 'getyourbill' ), 403 );
            }
            $this->getyourbill_namesurnamersociale = array(
                "billing_first_name" => array(
                    'type' => 'text',
                    'label' => GetYourBill_altro["nome"],
                    'placeholder' => GetYourBill_altro["nome"],
                    'required' => true,
                    'class' => array("form-row form-row-first"),
                    'autocomplete' => false,
                    'extra_name' => "first_name",
                ),
                "billing_last_name" => array(
                    'type' => 'text',
                    'label' => GetYourBill_altro["cognome"],
                    'placeholder' => GetYourBill_altro["cognome"],
                    'required' => true,
                    'class' => array("form-row form-row-last"),
                    'autocomplete' => false,
                    'extra_name' => "last_name",
                ),
                "billing_company" => array(
                    'type' => 'text',
                    'label' => GetYourBill_altro["name_company"],
                    'placeholder' => GetYourBill_altro["name_company"],
                    'required' => ($this->s_typeaccount == "pperson" || $this->s_typeaccount == "pcompany" || $this->s_typeaccount == "pa")?true:false,
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                )
            );

            $this->type_input_select = $this->getyourbillfieldstypeaccounts;
            if( $this->config_seltype == "select" ){
                $this->type_input_select = $this->getyourbillfieldstypeaccountsselect;
            }

            $this->s_typeaccount = $this->get_type_user_account();
            $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );

            $this->request = $this->is_request_by_config_request($data);

            $pactive = self::get_info("pactive");
            if( $pactive !== "yes" && isset($this->type_input_select["pa"]) ){
                unset($this->type_input_select["pa"]);
            }

            $this->getyourbillfieldslist = array(
                'billing_user_type' => array(
                    'value' => 'psoleproprietor',
                    'type' => ( is_null($this->config_seltype) )?'radio':$this->config_seltype,
                    'label' => GetYourBill_altro["tipo_account"],
                    'required_checkout' => true,
                    'required' => true,
                    'view_registre' => true,
                    'options' => $this->type_input_select,
                    'priority'    => 1,    // define the priority
                    'view_order' => false,
                    'default' => "psoleproprietor",
                    'class' => array("form-row form-row-wide", "billing_user_type_field_class", "validate-required"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_pi' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'pi',
                    'label' => GetYourBill_altro["label_piva"],
                    'placeholder' => GetYourBill_altro["placeholder_piva"],
                    'required_checkout' => ( $this->request["billing_pi"] === "se" || $this->request["billing_pi"] === true ),
                    'required' => ($this->request["billing_pi"] === true),
                    'view_registre' => true,
                    'important' => true,
                    'validatemode' => array(),
                    'description' => ( $this->request["billing_pi"] === "se" || $this->request["billing_pi"] === true )?$this->getyourbillfieldsdesc:"",
                    'desc_tip' => ( $this->request["billing_pi"] === "se" || $this->request["billing_pi"] === true ),
                    'priority'    => 31,    // define the priority
                    'view_order' => ( $this->request["billing_pi"] === "se" || $this->request["billing_pi"] === true ),
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_cf' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'cf',
                    'label' => GetYourBill_altro["label_cf"],
                    'placeholder' => GetYourBill_altro["placeholder_cf"],
                    'required_checkout' => ( $this->request["billing_cf"] === "se" || $this->request["billing_cf"] === true ),
                    'required' => ($this->request["billing_cf"] === true),
                    'view_registre' => true,
                    'important' => true,
                    'validatemode' => array(),
                    'description' => ( $this->request["billing_cf"] === "se" || $this->request["billing_cf"] === true )?$this->getyourbillfieldsdesc:"",
                    'desc_tip' => ( $this->request["billing_cf"] === "se" || $this->request["billing_cf"] === true ),
                    'priority'    => 32,    // define the priority
                    'maxlength' => 16,
                    'view_order' => ( $this->request["billing_cf"] === "se" || $this->request["billing_cf"] === true ),
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_pecaddress' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'pec',
                    'label' => GetYourBill_altro["label_pec"],
                    'placeholder' => GetYourBill_altro["placeholder_pec"],
                    'required_checkout' => ( $this->request["billing_pecaddress"] === "se" || $this->request["billing_pecaddress"] === true ),
                    'required' => ($this->request["billing_pecaddress"] === true),
                    'view_registre' => true,
                    'description' => ( $this->request["billing_pecaddress"] === "se" )?$this->getyourbillfieldsdesc:"",
                    'desc_tip' => ( $this->request["billing_pecaddress"] === "se" ),
                    'important' => true,
                    'validatemode' => array(),
                    'priority' => 33,    // define the priority
                    'view_order' => ( $this->request["billing_pecaddress"] === "se" || $this->request["billing_pecaddress"] === true ),
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_sdiid' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'sdi',
                    'label' => GetYourBill_altro["label_sdiid"],
                    'placeholder' => GetYourBill_altro["placeholder_sdiid"],
                    'required_checkout' => ( $this->request["billing_sdiid"] === "se" || $this->request["billing_sdiid"] === true ),
                    'required' => ($this->request["billing_sdiid"] === true),
                    'view_registre' => true,
                    'description' => ( $this->request["billing_sdiid"] === "se" )?$this->getyourbillfieldsdesc:"",
                    'desc_tip' => ( $this->request["billing_sdiid"] === "se" ),
                    'important' => true,
                    'validatemode' =>  array(),
                    'value' => "0000000",
                    'priority' => 34, // define the priority
                    'view_order' => ( $this->request["billing_sdiid"] === "se" || $this->request["billing_sdiid"] === true ),
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_getyourbillid' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'gid',
                    'label' => GetYourBill_altro["label_getyourbillid"],
                    'placeholder' => GetYourBill_altro["placeholder_getyourbillid"],
                    'required_checkout' => $this->request_billing_getyourbillid,
                    'required' => $this->request_billing_getyourbillid,
                    'view_registre' => true,
                    'important' => false,
                    'validatemode' => array(),
                    'priority'    => 35, // define the priority
                    'view_order' => true,
                    'class' => array("form-row form-row-wide gyb_icon"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_ipa' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'ipa',
                    'label' => GetYourBill_altro["billing_ipa"],
                    'placeholder' => GetYourBill_altro["placeholder_billing_ipa"],
                    'required_checkout' => true,
                    'required' => true,
                    'view_registre' => true,
                    'description' => ( $this->request["billing_ipa"] === "se" || $this->request["billing_ipa"] === true )?$this->getyourbillfieldsdesc:"",
                    'desc_tip' => ( $this->request["billing_ipa"] === "se" || $this->request["billing_ipa"] === true ),
                    'important' => true,
                    'validatemode' => array(),
                    'priority'    => 30, // define the priority
                    'view_order' => ( $this->request["billing_ipa"] === "se" || $this->request["billing_ipa"] === true ),
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => true,
                ),
                'billing_cig' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'cig',
                    'label' => GetYourBill_altro["billing_cig"],
                    'placeholder' => GetYourBill_altro["placeholder_billing_cig"],
                    'required_checkout' => false,
                    'required' => false,
                    'view_registre' => false,
                    'important' => false,
                    'validatemode' => array("pa"),
                    'priority'    => 37, // define the priority
                    'view_order' => true,
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => false,
                ),
                'billing_cup' => array(
                    'value' => '',
                    'type' => 'text',
                    'mod' => 'cup',
                    'label' => GetYourBill_altro["billing_cup"],
                    'placeholder' => GetYourBill_altro["placeholder_billing_cup"],
                    'required_checkout' => false,
                    'required' => false,
                    'view_registre' => false,
                    'important' => false,
                    'validatemode' => array("pa"),
                    'priority'    => 38, // define the priority
                    'view_order' => true,
                    'class' => array("form-row form-row-wide"),
                    'autocomplete' => false,
                    'isinputbybglobal' => false,
                ),
            );

            global $invoicegenerator;




            if( in_array( $gybevent, array( 'invoicepdf_test', 'invoicepdf_print' ), true ) && $order_id > 0 ){
                header("Content-type:application/pdf");
                $invoicePDF = \models\GetyourbillApp::get_invoice_pdf_encode(new WC_Order($order_id));
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw PDF bytes are intentionally streamed to the browser.
                echo base64_decode( $invoicePDF );
                exit();
            }else if( 'send_invoice_invoicepdf_test' === $gybevent && $order_id > 0 ){
                $completeDocNumber = get_post_meta( $order_id, "getyourbill_invoice", true );
                $invoicePDF = \models\GetyourbillApp::get_invoice_pdf_encode(new WC_Order($order_id));
                do_action( 'getyourbill_after_send_to_gyb_invoice_pdf', $order_id, $completeDocNumber, $invoicePDF );
            }
            if( !isset($post_data["getyourbill_invoice"]) && '' === $gybevent && 'edit' === $action && $order_id > 0 && is_admin() && current_user_can( 'edit_shop_orders' ) ){
                $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
                $this->getyourbill_invoice_update_plugin_extra($order_id, ($getyourbill_invoice));
            }

            if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
                if( function_exists("WPI") ){
                    if( WPI()->get_option( 'general', 'invoice_number_column' ) ){
                        $bewpi_general_settings = get_option( "bewpi_general_settings" );
                        $bewpi_general_settings["bewpi_invoice_number_column"] = false;
                        update_option( "bewpi_general_settings", $bewpi_general_settings );
                    }
                }
            }

            if( $is_dev_test ){
                if( 'setinvoicepaytest' === $gybevent && $order_id > 0 ){
                    //$invoice = $this->getyourbill_payment_complete( $order_id, true );
                    $invoice = $this->getyourbill_payment_complete( $order_id, true );

                    exit();
                }
            }else if( 'printbase64' === $gybevent && $order_id > 0 ){


            }


            if( is_admin() && $order_id > 0 ){
                $this->order_id = $order_id;
                $post = get_post($this->order_id);

                if( !is_wp_error( $post )  && $post->post_type == "shop_order" ){
                    $this->order_object = new WC_Order( $this->order_id );
                    $this->typeaccount = get_post_meta( $this->order_id , "billing_user_type", true );
                    $fields = $this->getyourbill_get_account_fields( true, $this->order_object->get_user_id() , false);

                    foreach ( $fields as $key => $field_args ) {
                        $field_args["id"] = "_billing_".$key;
                        $field_args["name"] = $key;
                        $field_args["desc_tip"] = false;
                        $field_args["class"] = "short first";
                        $field_args["description"] = "";
                        $field_args["wrapper_class"] = '_transaction_id_field';
                        $field_args["value"] = get_post_meta( $this->order_id, $key, true );
                        $this->order_field_precompilati[$key] = $field_args;
                    }
                }
            }
        });


        if( isset($post_data["gyb_woocommerce_pay"]) ){
            WC_Settings_GetyourbillTabs::$progress_order_id = $post_data["gyb_woocommerce_pay"];
            WC_Settings_GetyourbillTabs::$billing_user_type = get_post_meta( WC_Settings_GetyourbillTabs::$progress_order_id, "_billing_user_type", true );
            WC_Settings_GetyourbillTabs::$in_pay_status = true;
        }


        /**
         * Add a custom field (in an order) to the emails
         */
        add_filter( 'woocommerce_email_after_order_table', array( $this, 'getyourbill_woocommerce_email_order_meta_fields_'), 10, 3 );
        $invoicegenerator = self::get_info("config_invoicegenerator");
        if( $invoicegenerator == "woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php" ){
            add_action( 'wpo_wcpdf_after_order_data', array( $this, 'getyourbill_wcpdf_delivery_date'), 10, 2 );
            add_action( 'wpo_wcpdf_after_document', array( $this, 'getyourbill_wcpdf_footer_document'), 10, 2 );
        }else if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
            add_action( 'wpi_after_formatted_billing_address', array( $this, 'getyourbill_pdf_invoices_formatted_billing_address'), 99 );
            add_filter( 'bewpi_terms' , array( $this, 'getyourbill_pdf_invoices_notes') );
        }
        add_action( 'before_delete_post', array($this, 'getyourbill_delete_post_type') );

        add_filter( 'woocommerce_checkout_update_customer_data', array($this, 'getyourbill_checkout_update_customer_data_callback'), 10, 2 );

        if( is_admin() && !is_null($this->user_id) ){

            add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 50 );
            add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
            add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
            add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections') ) ;
            add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu_'. $this->id), 10000) ;

        }

        if(  $started  ){

            if( is_admin() && !is_null($this->user_id) ){

                $config_statuscolum = self::get_info("config_statuscolum");
                if( $config_statuscolum == "yes" ){
                    add_filter( 'manage_edit-shop_order_columns', array($this, 'getyourbill_shop_order_table_head') );
                    add_action( 'manage_shop_order_posts_custom_column', array($this, 'getyourbill_shop_order_table_content'), 10, 2 );
                }


                // add campi
                add_action( 'show_user_profile', array( $this, 'getyourbill_user_admin_fields') );
                add_action( 'edit_user_profile',  array( $this, 'getyourbill_user_admin_fields') );

                // data user back end validation
                add_action( 'user_profile_update_errors', array( $this, 'getyourbill_validate_user_profile_update_errors'),10,3);

                // save/update data user back end
                add_action( 'edit_user_profile_update', array( $this, 'getyourbill_save_account_fields') );
                add_action( 'personal_options_update', array( $this, 'getyourbill_save_account_fields') );



                // order add campi
                add_filter('woocommerce_admin_order_data_after_billing_address', array( $this, 'getyourbill_order_fields'), 10, 3 );
                add_filter('woocommerce_admin_order_data_after_order_details', array( $this, 'getyourbill_order_buttons'), 45, 2 );


                // aggiono i datti getyourbill meta nel ordine
                add_action('woocommerce_process_shop_order_meta', array( $this, 'getyourbill_woocommerce_edit_order_fields'), 9999, 2);

                // nascondo i campi getyourbill meta per evita modifiche non verificate
                add_filter('is_protected_meta', array( $this, 'getyourbill_protected_meta_filter'), 10, 2);

                // aggiorno i datti con il clic su Carica indirizzo di fatturazione
                add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'getyourbill_ajax_get_customer_details') );

            }
            // add campi to checkout
            //add_filter( 'woocommerce_after_checkout_billing', array( $this, 'getyourbill_checkout_fields') );


            // add auto load info user
            add_filter( 'woocommerce_before_checkout_billing_form', array( $this, 'getyourbill_checkout_fields_load_info_by') );
            //add_filter( 'woocommerce_before_edit_address_form_billing', array( $this, 'getyourbill_woocommerce_before_edit_address_form_') );

            // add campi to checkout nmod 2
            add_filter('woocommerce_checkout_fields',  array( $this, 'getyourbill_checkout_fields_position') );
            add_action( 'woocommerce_after_order_notes', array( $this, 'getyourbill_checkout_fields_hidden') );

            // add campi register/account
            add_action( 'woocommerce_register_form_start', array( $this, 'getyourbill_print_user_frontend_fields') );
            add_action( 'woocommerce_edit_account_form_start', array( $this, 'getyourbill_print_user_frontend_fields'), 1 );

            // save/update data user frontend
            add_action( 'woocommerce_save_account_details', array( $this, 'getyourbill_save_account_fields') ); // edit WC account
            add_action( 'woocommerce_created_customer', array( $this, 'getyourbill_save_account_fields') );


            // register validation
            add_action( 'woocommerce_register_post', array( $this, 'getyourbill_register_validation'), 1, 3 );
            add_action( 'woocommerce_save_account_details_errors', array( $this, 'getyourbill_validate_user_profile_update_errors'),10,3);


            // update info by user
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'getyourbill_process_shop_order_meta') );

            // validation front checkout
            add_action( 'woocommerce_after_checkout_validation', array( $this, 'getyourbill_after_checkout_validationa'), 10, 2 );
            //add_action( 'woocommerce_checkout_process', array( $this, 'getyourbill_woocommerce_checkout_process'));

            add_action( 'wp_head', array( $this, 'getyourbill_custom_js_json') );
            add_action( 'admin_head', array( $this, 'getyourbill_custom_js_json') );
            add_action( 'wp_footer', array( $this, 'getyourbill_custom_footer') );
            add_action('admin_enqueue_scripts', array( $this,'getyourbill_custom_js_admin'));

            // hook paycomplete
            add_action( 'woocommerce_order_status_cancelled', array( $this, 'getyourbill_payment_cancelled') );



            $config_fautomatiche = self::get_info("config_fautomatiche");
            if( $config_fautomatiche == "yes" ) {
                $conf_statusgenerateinvoice = self::get_info("config_statusgenerateinvoice");
                if( array_key_exists($conf_statusgenerateinvoice, $this->statusgenerateinvoice) ){
                    add_action( 'woocommerce_order_status_'.$conf_statusgenerateinvoice, array( $this, 'getyourbill_payment_complete'), 10, 1 );
                }else{
                    add_action( 'woocommerce_payment_complete', array( $this, 'getyourbill_payment_complete'), 10, 1 );
                }
            }



            $conf_phonenotrequest = self::get_info("config_phonenotrequest");
            if( $conf_phonenotrequest == "yes" ){
                add_filter( 'woocommerce_billing_fields', array( $this, 'getyourbill_filter_phone'), 100, 1 );
            }

            add_filter( 'woocommerce_billing_fields', array( $this, 'getyourbill_filter_city'), 10, 1 );

            // preparazione della fattura  in 2 status
            //add_action( 'woocommerce_order_status_on-hold', array( $this, 'getyourbill_order_first_status'), 10, 1);
            //add_action( 'woocommerce_order_status_processing', array( $this, 'getyourbill_order_first_status'), 10, 1);

            $invoicegenerator = self::get_info("config_invoicegenerator");

            //	if( $invoicegenerator == "getyourbill_template" ){
            //add_filter( 'wpo_wcpdf_custom_attachment_condition', 'getyourbill_wpo_wcpdf_processing_accepted_email', 100, 4 );
            if( function_exists("getyourbill_wpo_wcpdf_processing_accepted_email") ){
                function getyourbill_wpo_wcpdf_processing_accepted_email( $condition, $order, $email_id, $document_type ) {
                    // disable attachment of the invoice to the processing email if the status is not order-accepted
                    return $condition;
                    if ( $document_type == 'invoice' && $email_id == 'customer_processing_order' && $order->get_status() != 'order-accepted') {
                        return false;
                    } else {
                        return $condition;
                    }
                }
            }
            //  }


            // filtra PA

            // define the woocommerce_pay_order_before_submit callback
            function getyourbill_woocommerce_pay_order_before_submit() {
                global $wp;
                if ( isset($wp->query_vars['order-pay']) && absint($wp->query_vars['order-pay']) > 0 ) {
                    $order_id = absint($wp->query_vars['order-pay']); // The order ID
                    printf(
                        '<input type="hidden" name="gyb_woocommerce_pay" value="%s">',
                        esc_attr( $order_id )
                    );
                }
            };

            // add the action
            add_action( 'woocommerce_pay_order_before_submit', 'getyourbill_woocommerce_pay_order_before_submit', 10, 0 );
            // define the woocommerce_checkout_order_processed callback
            function getyourbill_checkout_order_processed( $order_id ) {
                // make action magic happen here...
                WC_Settings_GetyourbillTabs::$progress_order_id = $order_id;
                WC_Settings_GetyourbillTabs::$billing_user_type = get_post_meta( $order_id , "_billing_user_type", true );
                WC_Settings_GetyourbillTabs::$in_pay_status = true;
            };

            // add the action
            add_action( 'woocommerce_checkout_order_processed', 'getyourbill_checkout_order_processed', 10, 1 );


            add_action( 'woocommerce_cart_totals_after_order_total', 'getyourbill_woocommerce_cart_totals_after_order_total_action' );
            add_action( 'woocommerce_review_order_after_order_total', 'getyourbill_woocommerce_cart_totals_after_order_total_action' );
            function getyourbill_woocommerce_cart_totals_after_order_total_action(){
                if( WC_Settings_GetyourbillTabs::is_type_pa_by_post() ){
                    echo '<tr class="order-total order-total-is-split-payments"><th>' . esc_html(__( 'Si applica la scissione dei pagamenti', 'getyourbill' )) . '</th><td></td></tr>';
                }
            }

            add_filter( 'woocommerce_get_formatted_order_total', 'getyourbill_woocommerce_cart_totals_after_order_total_action_thankyou', 10, 4 );
            function getyourbill_woocommerce_cart_totals_after_order_total_action_thankyou( $formatted_total, $order, $tax_display, $display_refunded ){
                if( WC_Settings_GetyourbillTabs::is_type_pa_by_post($order) ){
                    return $formatted_total . " <span class='getyourbill-split-pay'>(" . esc_html(__( 'Si applica la scissione dei pagamenti', 'getyourbill' )) . ')</span>';
                }
                return $formatted_total;
            }

            // define the woocommerce_order_item_product callback
            function getyourbill_order_item_product( $order_get_product_from_item_item, $item ) {
                // make filter magic happen here...
                if( WC_Settings_GetyourbillTabs::$in_pay_status && WC_Settings_GetyourbillTabs::$billing_user_type === "pa" ){
                    $order_get_product_from_item_item->set_tax_class("");
                    $item->set_product($order_get_product_from_item_item);
                }
                return $order_get_product_from_item_item;
            };

            // add the filter
            add_filter( 'woocommerce_order_item_product', 'getyourbill_order_item_product', 10, 2 );

            function getyourbill_checkout_update_order_review( $posted_data) {
                WC_Settings_GetyourbillTabs::$checkout_update = true;
            }

            add_action('woocommerce_checkout_update_order_review', 'getyourbill_checkout_update_order_review');

            add_filter( 'woocommerce_cart_get_total', function( $totale ) {
                if( WC_Settings_GetyourbillTabs::$checkout_update && WC_Settings_GetyourbillTabs::is_type_pa_by_post() ){
                    $cart = WC()->session->get("cart_totals");
                    if( is_array($cart) && isset($cart["total"]) ){
                        $total_tax = $cart["total_tax"];
                        return $totale - $total_tax;
                    }
                }
                return $totale;
            });

            add_filter( 'woocommerce_order_get_total', function( $totale, $instance ) {
                if( !WC_Settings_GetyourbillTabs::$in_pay_status && WC_Settings_GetyourbillTabs::is_type_pa_by_post($instance) ){
                    $total_tax = $instance->get_total_tax();
                    return $totale - $total_tax;
                }
                return $totale;
            }, 10, 2);
        }



    }


    public static function admin_bar_menu_getyourbill($admin_bar){
        if( is_admin() ){
            $gyb_isonline = get_option( "wc_settings_getyourbill_settings_config_isonline" , "yes" );
            if( $gyb_isonline === "yes" ){
                $admin_bar->add_menu( array( 'id'=>'getyourbillconfig','title'=> __(  "MODALITA' TEST", 'getyourbill' ) ,'href'=> get_admin_url()."admin.php?page=wc-settings&tab=getyourbill&section=config" ) );
            }
        }
    }


    public static function is_type_pa_by_post($instance = null){
        $post_data = self::get_post_data_request();
        if( !is_null($instance) && is_object($instance) ){
            return get_post_meta( $instance->get_id() , "_billing_user_type", true ) === "pa";
        }else if( isset( $post_data["billing_user_type"]) ){
            return $post_data["billing_user_type"] == "pa";
        }
        return false;
    }


    function getyourbill_checkout_update_customer_data_callback( $boolean, $checkout ) {
        $customer_id = get_current_user_id();
        $post_data = $this->get_post_data_request();
        if( !isset($post_data["billing_update_info_on_checkout"]) ){
            return false;
        }
        return true;
    }

    function getyourbill_filter_phone( $address_fields ) {
        $address_fields['billing_phone']['required'] = false;
        return $address_fields;
    }

    function getyourbill_filter_city( $address_fields ) {
        //$address_fields['billing_city']['required'] = true;
        //$address_fields['billing_city']['validate'] = array("city");

        // $address_fields['billing_state']['required'] = true;
        // unset( $address_fields['billing_state']['country_field'] );
        // unset( $address_fields['billing_state']['country'] );

        //var_dump( $address_fields );
        return $address_fields;
    }


    public function getyourbill_pdf_invoices_notes ( ) {
        $order_id = BEWPI_WC_Order_Compatibility::get_id( WPI()->templater()->get_order() );
        return $this->getyourbill_footer_info_document(new WC_Order($order_id), true);
    }


    // pdf gen
    public function getyourbill_invoice_update_plugin_extra($order_id , $invoice_nr){

        $invoicegenerator = self::get_info("config_invoicegenerator");
        if( $invoicegenerator == "woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php" ){
            $data_invoice = array (
                'number' => $invoice_nr,
                'formatted_number' => $invoice_nr,
                'prefix' => '',
                'suffix' => '',
                'document_type' => 'invoice',
                'order_id' => $order_id,
                'padding' => '',
            );
            update_post_meta( $order_id, "_wcpdf_invoice_number", $invoice_nr );
            update_post_meta( $order_id, "_wcpdf_invoice_date", strtotime(get_the_date("Y-m-d h:i:s", $order_id)) );
            update_post_meta( $order_id, "_wcpdf_invoice_number_data", $data_invoice);
            update_post_meta( $order_id, "_wcpdf_invoice_date_formatted", get_the_date("Y-m-d h:i:s", $order_id) );
        }else if( $invoicegenerator == "woocommerce-pdf-invoices/bootstrap.php" ){
            update_post_meta( $order_id, "_bewpi_invoice_number", $invoice_nr );
            update_post_meta( $order_id, "_bewpi_invoice_date", get_the_date("Y-m-d H:i:s", $order_id) );
        }
    }

    public function getyourbill_notice(){
        global $wpdb;
        $invoicegenerator = self::get_info("config_invoicegenerator");
        $error_pdf = true;
        if( $invoicegenerator  !== "getyourbill_template" ){
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
            foreach ( $this->getyourbillplugininvoice_pre as $key => $field_args ) {
                if( in_array($key, $active_plugins) ){
                    $error_pdf = false;
                }
            }
            if( $error_pdf ){
                printf(
                    '<div class="%1$s"><p>%2$s</p></div>',
                    'notice notice-error',
                    wp_kses_post(
                        sprintf(
                            GetYourBill_Messaggi_Error['missing_invoicegenerator'],
                            esc_url( admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=info' ) )
                        )
                    )
                );
            }
        }

        $get_list_WC_Tax = WC_Settings_GetyourbillTabs::get_list_WC_Tax();
        foreach ( $get_list_WC_Tax[0] as $key => $taxes ) {
            if( count($taxes) >0  ){
                foreach ( $taxes as $tax ) {
                    if( intval($tax->tax_rate) === 0 ){
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared lookup of the plugin's own tax-mapping posts.
                        $q = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT post_content FROM {$wpdb->posts} WHERE post_excerpt = %s",
                                $tax->tax_rate_id
                            )
                        );
                        if( $q  === null ){
                            printf(
                                '<div class="%1$s"><p>%2$s</p></div>',
                                'notice notice-error',
                                wp_kses_post(
                                    sprintf(
                                        __( "Attenzione, è necessario settare l'aliquota %1\$s Consultare la <a href='%2\$s'><string>configurazione aliquote</string></a>", 'getyourbill' ),
                                        esc_html( $tax->tax_rate_country . ' : ' . $tax->tax_rate_name ),
                                        esc_url( admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=aliquote' ) )
                                    )
                                )
                            );
                        }
                    }
                }
            }
        }

        if( !$error_pdf ){
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
            if( !in_array($invoicegenerator, $active_plugins) ){
                printf(
                    '<div class="%1$s"><p>%2$s</p></div>',
                    'notice notice-error',
                    wp_kses_post(
                        sprintf(
                            GetYourBill_Messaggi_Error['config_invoicegenerator'],
                            esc_url( admin_url( 'admin.php?page=wc-settings&tab=getyourbill&section=config' ) )
                        )
                    )
                );
                $active_plugins = apply_filters('active_plugins', get_option('active_plugins')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
                foreach ( $this->getyourbillplugininvoice_pre as $key => $field_args ) {
                    if( in_array($key, $active_plugins) ){
                        self::set_info( "config_invoicegenerator" , $key );
                        continue;
                    }
                }
            }
        }

        $live_test_mode = \models\GetyourbillApp::get_info("config_isonline", 'yes');
        $key = self::get_info( "apikey".( ($live_test_mode!=='yes')?"":"_test") );
        if( trim( $key ) == "" ){
            printf(
                '<div class="%1$s"><p>%2$s</p></div>',
                'notice notice-error',
                wp_kses_post(
                    sprintf(
                        GetYourBill_Messaggi_Error['missing_apikey'],
                        esc_url( admin_url( '/admin.php?page=wc-settings&tab=getyourbill' ) )
                    )
                )
            );
        }else if( 'invoicestatus' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $app = new \models\GetyourbillApp();
            $order_id = $this->get_request_absint( 'post' );
            $app->gestione_status_invoice($order_id, true);
        }else if( 'invoicenumber' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $order_id = $this->get_request_absint( 'post' );
            $r = $this->getyourbill_get_invoice_number($order_id );
            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( wp_json_encode( $r ) ) );
        }else if( 'invoicepdf' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $order_id = $this->get_request_absint( 'post' );
            $r = $this->getyourbill_set_invoice_pdf($order_id );
            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( $r ) );
        }else if( 'errorvalid' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $order_id = $this->get_request_absint( 'post' );
            $getyourbill_send_error = get_post_meta( $order_id, "getyourbill_send_error", true );
            update_post_meta( $order_id, "getyourbill_status_invoice_ok", "yes" );
            delete_post_meta( $order_id, "getyourbill_send_ok_f_info" );
            delete_post_meta( $order_id, "getyourbill_status_invoice" );
            $getyourbill_if_error_mess = get_post_meta( $order_id, "getyourbill_if_error_mess", true );
            $order_note = wc_get_order(  $order_id );
            $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Success["order_anulamento_errore"], $getyourbill_if_error_mess ), false, true);
            $order_note->save();
            update_post_meta( $order_id, "getyourbill_if_error_mess", "" );
            update_post_meta( $order_id, "getyourbill_send_error", "" );
            if( $getyourbill_send_error == "bug_fattura" ){
                $invoice = $this->getyourbill_payment_complete( $order_id );
                if( $invoice === true ){
                    printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( GetYourBill_Messaggi_Success["order_send_fattura_ok"] ) );
                }else{
                    if( is_array($invoice) && array_key_exists("error", $invoice) ){
                        $error = json_decode($invoice["error"]);
                        if( !empty($error->errorMessage) ){
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_error_mess"], $error->errorMessage ) ) );
                        }else{
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["undefined_error"] ) );
                        }
                    }else{
                        $messaggio = get_post_meta( $order_id, "getyourbill_if_error_mess", true );
                        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_error_mess"], $messaggio ) ) );
                    }
                }
            }

        }else if( 'invoiceannulla' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $api = new \models\APIGetyourbill();
            $order_id = $this->get_request_absint( 'post' );
            $order_note = wc_get_order( $order_id );
            $getyourbill_is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
            $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
            if( $getyourbill_is_send_sdi != "yes" ){
                $coder = get_post_meta( $order_id, "getyourbill_recoverCode", true );
                if( !empty($coder) ){
                    $res = $api->annullamento_fattura($coder);
                    if( $res === true ){
                        \models\GetyourbillApp::annulla_fattura_order( $order_id );
                        $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Success["order_anulamento_fattura_ok"], $getyourbill_invoice, $getyourbill_invoice, $coder ), false, true);
                        $order_note->save();
                        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( sprintf( GetYourBill_Messaggi_Success["order_anulamento_fattura_ok"], $getyourbill_invoice, $getyourbill_invoice, $coder ) ) );
                    }else{
                        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_anulamento_fattura_error"], $getyourbill_invoice, $coder ) ) );
                    }
                }else{
                    printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["missing_code_Recoveri"] ) );
                }
            }else{
                $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Warning["order_consegna_is_send_to_sdi"], $getyourbill_invoice, $coder ), false, true);
                $order_note->save();
                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Warning["order_consegna_is_send_to_sdi"] ) );
            }
        }else if( 'invoiceannullaforce' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $api = new \models\APIGetyourbill();
            $order_id = $this->get_request_absint( 'post' );
            $coder = get_post_meta( $order_id, "getyourbill_recoverCode", true );
            $res = $api->annullamento_fattura($coder);
            $order_note = wc_get_order( $order_id );
            $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
            if( $res === true ){
                \models\GetyourbillApp::annulla_fattura_order( $order_id, true );
                $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Success["order_anulamento_fattura_ok"], $getyourbill_invoice, $getyourbill_invoice, $coder ), false, true);
                $order_note->save();
                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( sprintf( GetYourBill_Messaggi_Success["order_anulamento_fattura_ok"], $getyourbill_invoice, $getyourbill_invoice, $coder ) ) );
            }else{
                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_anulamento_fattura_error"], $getyourbill_invoice, $coder ) ) );
            }
        }else if( 'setinvoicepay' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $order_id = $this->get_request_absint( 'post' );
            if( false == get_transient( 'getyourbill_fattura_in_creazione_'.intval($order_id)) ) {
                $fields = $this->getyourbill_get_account_fields();
                $orders_info = array();
                foreach ( $fields as $key => $field_args ) {
                    $field_args['value'] = get_post_meta( $order_id, $key, true );
                    $orders_info[$key] = $field_args;
                }
                $this->s_typeaccount = get_post_meta( $order_id, "billing_user_type", true );
                $country = get_post_meta( $order_id, "_billing_country", true );

                if( !$this->validation_global($orders_info, "notice", true, $this->s_typeaccount, $country, $order_id ) ){
                }else{
                    $invoice = $this->getyourbill_payment_complete( $order_id );

                    if( $invoice === true ){
                        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( GetYourBill_Messaggi_Success["order_send_fattura_ok"] ) );
                    }else{
                        if( is_array($invoice) && array_key_exists("error", $invoice) ){
                            $error = json_decode($invoice["error"]);
                            if( !empty($error->errorMessage) ){
                                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_error_mess"], $error->errorMessage ) ) );
                            }else{
                                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["undefined_error"] ) );
                            }
                        }else{
                            $messaggio = get_post_meta( $order_id, "getyourbill_if_error_mess", true );
                            if($messaggio == ""){
                                $messaggio = GetYourBill_Messaggi_Error["undefined_error"];
                            }
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_error_mess"], $messaggio ) ) );
                        }
                    }
                }

            }else{
                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( GetYourBill_Messaggi_Warning["automat_send"] ) );
            }
        }

        if( '' === $this->get_request_key( 'gybevent' ) && $this->get_request_absint( 'post' ) > 0 ){
            $order_id = $this->get_request_absint( 'post' );
            $next_errors = false;
            $getyourbill_is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
            $error_sdi = get_post_meta( $order_id, "getyourbill_if_error_sdi", true );
            if( $getyourbill_is_send_sdi == "yes" ){
                $messaggio =  esc_html( GetYourBill_Messaggi_Success["order_colum_status_sdi_ok"] );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', $messaggio );
            }


            $fields = $this->getyourbill_get_account_fields();
            $orders_info = array();
            foreach ( $fields as $key => $field_args ) {
                $field_args['value'] = get_post_meta( $order_id, $key, true );
                $orders_info[$key] = $field_args;
            }
            $country = get_post_meta( $order_id, "_billing_country", true );

            $this->s_typeaccount = get_post_meta( $order_id, "billing_user_type", true );
            if( !empty($this->s_typeaccount) ){
                $next_errors = $this->validation_global($orders_info, "notice", true, $this->s_typeaccount, $country, $order_id);
            }
            $res = get_post_meta( $order_id, "getyourbill_status_invoice", true );
            $res = ( !empty($res) )?$res:array();
            foreach($res as $key_status => $status) {
                $messaggio_string = "";
                if( $status != "" ){
                    if( array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) ){
                        $messaggio_string = GetYourBillSdiErrorIntermediaError[$key_status]["name"]." : ".esc_html( $status );
                    }else{
                        $messaggio_string = esc_html( $status );
                    }
                }
                if( $messaggio_string != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) ){
                    if( $status != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) && GetYourBillSdiErrorIntermediaError[$key_status]["mod"] == "error" ){
                        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-' . sanitize_html_class( GetYourBillSdiErrorIntermediaError[ $key_status ]["status"] ) ), esc_html( $messaggio_string ) );
                    }else if(  $status != "" && array_key_exists($key_status, GetYourBillSdiErrorIntermediaError) && GetYourBillSdiErrorIntermediaError[$key_status]["mod"] == "result" ){
                        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-' . sanitize_html_class( GetYourBillSdiErrorIntermediaError[ $key_status ]["status"] ) ), esc_html( $messaggio_string ) );
                    }
                }
            }

            if( $next_errors && 'edit' === $this->get_request_key( 'action' ) ){
                if( false == get_transient( 'getyourbill_fattura_in_creazione_'.intval($order_id)) ) {
                    $getyourbill_send_ok_f_json = get_post_meta( $order_id, "getyourbill_send_ok_f_json", true );
                    $getyourbill_status_invoice_ok = get_post_meta( $order_id, "getyourbill_status_invoice_ok", true );
                    if( $getyourbill_send_ok_f_json == "error" ){
                        $getyourbill_send_error = get_post_meta( $order_id, "getyourbill_send_error", true );
                        if( $getyourbill_send_error == "send_fattura" || $getyourbill_send_error == "pdf" || $getyourbill_send_error == "number" ){
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Warning["validare_error_server_not_response"] ) );
                        }else if( $getyourbill_send_error == "bug_fattura" ){
                            $messaggio = get_post_meta( $order_id, "getyourbill_if_error_mess", true );
                            if( $messaggio != ""){
                                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( sprintf( GetYourBill_Messaggi_Error["order_error_mess"], $messaggio ) ) );
                            }else{
                                printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Warning["validare_error_server_not_response"] ) );
                            }
                        }else{
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Error["undefined_error"] ) );
                        }
                    }
                    if( $getyourbill_status_invoice_ok == "validerror"){
                        printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( GetYourBill_Messaggi_Warning["validare_error_messaggio"] ) );
                    }
                }else{
                    printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-success', esc_html( GetYourBill_Messaggi_Warning["fattura_in_esecuzioni"] ) );
                }
            }
        }

        if( 'getyourbill' === $this->get_request_key( 'tab' ) ){
            foreach($this->getyourbillfieldstypeaccounts as $key => $name) {
                if( $key === "psoleproprietor" ){
                    $request = \models\GetyourbillApp::get_info("config_request_psoleproprietor");
                    if( $request === "oprionals" ){
                        $docType = \models\GetyourbillApp::get_info("config_typeinvoice_psoleproprietor");
                        if( $docType !== "i_not"){
					        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                            printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error',  sprintf( esc_html( GetYourBill_Messaggi_Warning["tab_typeinvoice"] ),  admin_url( "admin.php?page=wc-settings&tab=getyourbill&section=info" ) ) );
                        }
                    }
                }
            }

        }

    }

    function getyourbill_delete_post_type( $post_id ){
        $post = get_post($post_id);
        if( is_null( $post ) ){
            return true;
        }
        $post_type = $post->post_type;
        $tipes = array("shop_order");
        if( !in_array($post_type, $tipes) ){
            return true;
        }
        \models\GetyourbillApp::annulla_fattura_order( $post_id, true);
    }


    public function getyourbill_footer_info_document ($order , $mod = false ) {

        $out = '';
        $out .=  '<div class="custom-text">'.esc_html(GetYourBill_altro["footer_fattura"])." ".$config_bollo.'</div>';

        $user_id = $order->get_user_id();
        $billing_user_type = get_post_meta( $order->get_order_number(), "billing_user_type", true );
        if( empty($billing_user_type) )
            $billing_user_type = get_the_author_meta( "billing_user_type", $user_id );

        $docType = $this->get_typeinvoice_by_config($billing_user_type, true);
        if( $docType == "i_61" ){
            $out .=  '<div class="custom-text">'.esc_html(GetYourBill_altro["footer_fattura_if_elletronica"]).'</div>';
        }
        if(  $mod  ){
            return $out;
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
        echo $out;
    }

    public function getyourbill_pdf_invoices_formatted_billing_address ( $order  ) {
        echo wp_kses_post( $this->getyourbill_wcpdf_delivery_delivery_notes( "", $order->order ) );
    }
    public function getyourbill_wcpdf_footer_document ( $template_type, $order) {

        if ( $template_type != 'packing-slip') {
            $this->getyourbill_footer_info_document($order);
        }
    }

    function getyourbill_wcpdf_delivery_delivery_notes ( $adress_string, $order) {
        $user_id = $order->get_user_id();

        $billing_user_type = get_post_meta( $order->get_order_number(), "billing_user_type", true );
        if( empty($billing_user_type) )
            $billing_user_type = get_the_author_meta( "billing_user_type", $user_id );

        $docType = $this->get_typeinvoice_by_config($billing_user_type, true);
        if( $docType != "i_not" ){
            $fields_gyb = $this->getyourbill_get_account_fields();
            unset($fields_gyb["billing_user_type"]);
            unset($fields_gyb["billing_sdiid"]);
            unset($fields_gyb["billing_getyourbillid"]);
            foreach ( $fields_gyb as $key => $field_args ) {
                $field_ = wc_clean(get_post_meta( $order->get_id(), $key, true ));
                if( !empty( $field_ ) && $field_ != "" ){
                    $adress_string .= "<br>".$field_args["label"].":". $field_;
                }
            }
            $field_ = wc_clean(get_post_meta( $order->get_id(), "getyourbill_recoverCode", true ));
            if( !empty( $field_ ) && $field_ != "" ){
                $adress_string .= "<br>".esc_html( __("Codice di recupero GetYourBill", 'getyourbill') ).":". $field_;
            }

        }
        return $adress_string;
    }

    function getyourbill_wcpdf_delivery_date ($template_type, $order) {
        if ($template_type != 'packing-slip') {
            $user_id = $order->get_user_id();

            $billing_user_type = get_post_meta( $order->get_order_number(), "billing_user_type", true );
            if( empty($billing_user_type) )
                $billing_user_type = get_the_author_meta( "billing_user_type", $user_id );

            $docType = $this->get_typeinvoice_by_config($billing_user_type, true);
            if( $docType != "i_not" ){
                $fields_gyb = $this->getyourbill_get_account_fields();
                unset($fields_gyb["billing_user_type"]);
                unset($fields_gyb["billing_sdiid"]);
                unset($fields_gyb["billing_getyourbillid"]);
                foreach ( $fields_gyb as $key => $field_args ) {
                    $field_ = wc_clean(get_post_meta( $order->get_id(), $key, true ));
                    if( !empty( $field_ ) && $field_ != "" ){
                        ?>
                        <tr class="delivery-date">
                            <th><?php echo esc_html( $field_args["label"] ); ?>:</th>
                            <td><?php echo esc_html( $field_ ); ?></td>
                        </tr>
                        <?php
                    }
                }
                $field_ = wc_clean(get_post_meta( $order->get_id(), "getyourbill_recoverCode", true ));
                if( !empty( $field_ ) && $field_ != "" ){
                    ?>
                    <tr class="delivery-date">
                        <th><?php echo esc_html( GetYourBill_altro['label_recoveri'] ); ?>:</th>
                        <td><?php echo esc_html( $field_ ); ?></td>
                    </tr>
                    <?php
                }

                $TotalTax = $order->get_total();
                $VatAmount_totale = $order->get_total_tax();
                $virtualStampAmount = 0;
                $virtualStampAmountif = 0;
                $config_bollo = \WC_Settings_GetyourbillTabs::get_info("config_bollo");
                if( $config_bollo == "yes" && $VatAmount_totale == 0 ){
                    $virtualStampAmountif = floatval(\WC_Settings_GetyourbillTabs::get_info("config_bollo_totale"));
                    if( $TotalTax > $virtualStampAmountif ){
                        $virtualStampAmount = floatval(\WC_Settings_GetyourbillTabs::get_info("config_bollo_quanto"));
                        $virtualStampAmount = apply_filters( 'getyourbill_virtualStampAmount', $virtualStampAmount, $order );
                        if( floatval($virtualStampAmount) > 0  ){
                            $virtualStampAmount = floatval($virtualStampAmount);
                        }
                        ?>
                        <tr class="delivery-date">
                            <th><?php echo esc_html( apply_filters( 'getyourbill_fattura_bollo_test', GetYourBill_altro['footer_fattura_bollo'] ) ); ?>:</th>
                            <td><?php echo esc_html( $virtualStampAmount . ' ' . esc_html (GetYourBill_altro['fattura_bollo_valuta'] ) ); ?></td>
                        </tr>
                        <?php
                    }
                }
            }
        }
    }

    function getyourbill_payment_cancelled( $order_id ) {
        $order_note = wc_get_order( $order_id );
        if( \models\GetyourbillApp::annulla_fattura_order( $order_id ) ){
            $coder = get_post_meta( $order_id, "getyourbill_recoverCode", true );
            $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
            $api = new \models\APIGetyourbill();
            $res = $api->annullamento_fattura($coder);
            if( $res === true ){
                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Warning["order_anulamento_fattura_warning"] ) ), false, true);
                $order_note->save();
            }else{
                $order_note->add_order_note(  sprintf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error',   sprintf( esc_html( GetYourBill_Messaggi_Error["order_anulamento_fattura_error"] ), $getyourbill_invoice, $coder ) ), false, true);
                $order_note->save();
            }
        }else{
            $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Error["order_anulamento_fattura_warning_error"] ) ), false, true);
            $order_note->save();
        }
    }

    function getyourbill_woocommerce_email_order_meta_fields_( $order_obj, $sent_to_admin, $plain_text) {

        $billing_user_type = get_post_meta( $order_obj->get_order_number(), "billing_user_type", true );
        if( empty($billing_user_type) )
            $billing_user_type = get_the_author_meta( "billing_user_type", $order_obj->get_user_id() );

        $docType = $this->get_typeinvoice_by_config($billing_user_type, true);


        if( $docType != "i_not" ){
            $fields_gyb = $this->getyourbill_get_account_fields();
            $fields_gyb = apply_filters( 'getyourbill_invoice_fields_template', $fields_gyb );
            unset($fields_gyb["billing_user_type"]);
            unset($fields_gyb["billing_sdiid"]);
            unset($fields_gyb["billing_getyourbillid"]);
            if( count($fields_gyb) > 0 ){
                echo '<h2>' . esc_html( __( "Dati della fatturazione", 'getyourbill' ) ) . '</h2><table><tbody><tr><td><address>';
                foreach ( $fields_gyb as $key => $field_args ) {
                    $field_ = wc_clean(get_post_meta( $order_obj->get_order_number(), $key, true ));
                    if( !empty( $field_ ) && $field_ != "" ){
                        echo '<strong>' . esc_html( $field_args["label"] ) . ' : </strong>' . esc_html( $field_ ) . '<br>';
                    }
                }
                $field_ = wc_clean(get_post_meta( $order_obj->get_order_number(), "getyourbill_recoverCode", true ));
                if( !empty( $field_ ) && $field_ != "" ){
                    echo '<strong>' . esc_html( GetYourBill_altro["label_recoveri"] ) . ' : </strong>' . esc_html( $field_ ) . '<br>';
                }
                echo '</address></td></tr></tbody></table>';
            }
        }
    }


    /*
        function getyourbill_order_first_status( $order_id ){
            if( self::is_checked_by_config("fautomatiche") ){
                $order = new \WC_Order($order_id);
                $billing_user_type = get_post_meta( $order_id, "billing_user_type", true );
                if( empty($billing_user_type) )
                    $billing_user_type = get_the_author_meta( "billing_user_type", $order->get_user_id() );
                $docType = $this->get_typeinvoice_by_config($billing_user_type, true);
                if( $docType != "i_not" ){
                    $nr_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
                    if( empty($nr_invoice) || wc_clean($nr_invoice) == "" ){
                        $this->getyourbill_prepare_fattura( $order_id );
                        $nr_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
                    }
                }
            }
        }
    */


    function getyourbill_is_request_empty_only_post($order_id){

        $out = false;
        $this->s_typeaccount = get_post_meta( $order_id, "billing_user_type", true );
        if( !array_key_exists($this->s_typeaccount, $this->getyourbillfieldstypeaccounts) ){
            return true;
        }
        $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );
        $request_filds = $this->is_request_by_config_request($data);
        $fields = $this->getyourbillfieldslist;
        $ind=0;
        foreach ( $fields as $key => $field_args ) {
            if( array_key_exists($key, $request_filds) && $request_filds[$key] ){
                if( get_post_meta( $order_id, $key, true) == ""){
                    $ind++;
                }
            }
            if($ind>2){
                $out = true;
            }
        }
        return $out;
    }

    public function getyourbill_payment_anagrafica( $order_id, $billing_pi, $billing_cf, $billing_gyb_ID, $billing_user_type = "", $user_id = 0, $id = 0, $update_user = false, $billing_country = "IT", $pa = "" ){
        $id = (  empty($id) || ( !empty($id) && ($id == "" || $id == "0" ) ) )?0:$id;
        if( $id == 0 ){
            $info = array();
            $api = new \models\APIGetyourbill();

            if( $billing_user_type == "pa" ){
                $info = $api->get_anagrafiche("", "", "", $billing_country, $pa);
            }else if( $billing_user_type == "psoleproprietor" ){ // Privato
                $info = $api->get_anagrafiche("", "", $billing_cf, $billing_country, "");
            }else if( $billing_user_type == "pperson" ){ // libero professionista
                $info = $api->get_anagrafiche("", $billing_pi, "", $billing_country, "");
            }else if( $billing_user_type == "pcompany" ){ // company
                $info = $api->get_anagrafiche("", $billing_pi, "", $billing_country, "");
            }else{
                $info = 0;
            }

            if( is_array($info) && count($info)>0 ){
                if( $info["id"] != "0" ){
                    $id = $info["id"];
                    update_post_meta ( $order_id, 'billing_id_anagrafica', $info["id"] );
                    if( $user_id > 0 && $update_user ){
                        $id_anagrafica_user = get_the_author_meta( "billing_id_anagrafica", $user_id );
                        $id_anagrafica_user = (  empty($id_anagrafica_user) || ( !empty($id_anagrafica_user) && ($id_anagrafica_user == "" || $id_anagrafica_user == "0" ) ) )?0:$id_anagrafica_user;
                        if( $id_anagrafica_user == 0 ){
                            update_user_meta( $user_id, 'billing_id_anagrafica', $info["id"] );
                        }else if( $id_anagrafica_user != $info["id"] ){
                            update_user_meta( $user_id, 'billing_id_anagrafica', $info["id"] );
                        }
                    }
                }
            }
        }
        return $id;
    }


    public static function set_buug_dattura_in_JSON($order_id, $order_note, $message ){
        update_post_meta( $order_id, "getyourbill_send_error", "bug_fattura" );
        update_post_meta( $order_id, "getyourbill_if_error_mess", $message );
        \models\APIGetyourbill::set_error_log( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"] ), $message." order id : ".$order_id." p4" ) );
        $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"], $message ) ) );
        $order_note->save();
        update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
    }

    function getyourbill_payment_complete( $order_id, $force = false ){
        if( '' !== $this->get_request_text( 'ttest' ) || $force || false == get_transient( 'getyourbill_fattura_in_creazione_'.$order_id ) ) {
            $getyourbill_send_ok_f_json = get_post_meta( $order_id, "getyourbill_send_ok_f_json", true );
            if( $getyourbill_send_ok_f_json != "yes" || $force ){
                $order = new \WC_Order($order_id);
                $order_note = wc_get_order( $order_id );
                $user_id = $order->get_user_id();
                $billing_user_type = get_post_meta( $order_id, "billing_user_type", true );
                if( empty($billing_user_type) || ( !empty($billing_user_type) && !array_key_exists($billing_user_type, $this->getyourbillfieldstypeaccounts) ) ){
                    self::set_buug_dattura_in_JSON($order_id, $order_note, GetYourBill_Messaggi_Warning["send_bud_type_account_requered"] );
                    return false;
                }


                // verifico la configurazione
                $docType = $this->get_typeinvoice_by_config($billing_user_type, true);

                $note = "";
                if( $docType != "i_not" ){
                    // prendo il valore del doc type giusto
                    $docType = $this->get_typeinvoice_by_config($billing_user_type);
                    if( '' === $this->get_request_text( 'ttest' ) ){
                        set_transient( 'getyourbill_fattura_in_creazione_'.$order_id , $order_id, MINUTE_IN_SECONDS*GetYourBillCronErrorSendTime );
                        update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                        update_post_meta( $order_id, "getyourbill_send_error", "send_fattura" );
                    }

                    $app = new \models\GetyourbillApp();
                    $api = new \models\APIGetyourbill();
                    $gyb_recovericod = get_post_meta( $order_id, "getyourbill_recoverCode", true );

                    if( GetYourBillDEVMOD && '' !== $this->get_request_text( 'ttest' ) ){
                        $gyb_recovericod = "ttestrecoverCode";
                    }else{
                        if( empty($gyb_recovericod) ){
                            $gyb_recovericod = $app->register_invoice_to_get_your_bill( $order_id );
                        }
                    }
                    if( !is_null($gyb_recovericod) ){

                        if( GetYourBillDEVMOD && '' !== $this->get_request_text( 'ttest' ) ){
                            $recoverCode = "ttestrecoverCode";
                        }else{
                            $recoverCode = get_post_meta( $order_id, "getyourbill_recoverCode", true );
                        }
                        $transactionId = get_post_meta( $order_id, "getyourbill_iniquenumberinvoice", true );

                        //$order_date = get_the_date("Y-m-d", $order_id );
                        $order_date = $this->get_invois_date_by_mode($order_id);
                        $note .= " recoverCode : ".$recoverCode;
                        $anagrafica_user = array();

                        $fields = $this->getyourbillfieldslist;
                        $info_value = array();
                        $data = array( "type" => $billing_user_type, "data" => self::get_info("config_request_".$billing_user_type) );
                        $request_filds = $this->is_request_by_config_request($data);
                        foreach ( $fields as $key => $field_args ) {
                            if(  array_key_exists($key, $request_filds) && $request_filds[$key] ){
                                $info_value[$key] = get_post_meta( $order_id, $key, true );
                            }else{
                                $info_value[$key] = "";
                            }
                        }

                        $billing_cf = isset($info_value["billing_cf"])?$info_value["billing_cf"]:"";
                        $billing_pi = isset($info_value["billing_pi"])?$info_value["billing_pi"]:"";
                        $billing_getyourbillid = isset($info_value["billing_getyourbillid"])?$info_value["billing_getyourbillid"]:"";
                        $billing_update_info_on_checkout = get_post_meta( $order_id, "billing_update_info_on_checkout", true );
                        $id = get_post_meta( $order_id, "billing_id_anagrafica", true );

                        $billing_country = get_post_meta( $order_id, "_billing_country", true );

                        $IPACode = "";

                        if( $billing_user_type == "pa" ){ // PA
                            $IPACode = get_post_meta($order_id, "_billing_ipa", true );
                        }

                        $id = $this->getyourbill_payment_anagrafica( $order_id, $billing_pi, $billing_cf, $billing_getyourbillid, $billing_user_type, $user_id, $id, ( $billing_update_info_on_checkout == "yes" ), $billing_country, $IPACode );

                        // indirizzo di fatturazione
                        $billing_address_1 = get_post_meta( $order_id, "_billing_address_1", true );
                        $billing_city = get_post_meta( $order_id, "_billing_city", true );
                        $billing_postcode = get_post_meta( $order_id, "_billing_postcode", true );
                        $billing_state = get_post_meta( $order_id, "_billing_state", true );
                        $billing_pecaddress = get_post_meta( $order_id, "billing_pecaddress", true );
                        $user_description = get_post_meta( $order_id, "_billing_last_name", true )." ".get_post_meta( $order_id, "_billing_first_name", true );
                        $billing_company = get_post_meta($order_id, "_billing_company", true );

                        if( $billing_user_type == "pperson" || $billing_user_type == "pcompany" ){

                            if( $billing_user_type == "pcompany" ){ // Company
                                $anagrafica_user = array(
                                    "code" => $billing_getyourbillid,
                                    "id" => $id,
                                    "vatNumber" => $billing_pi,
                                    "taxCode" => $billing_cf,
                                    "description" => ($billing_company != "")?$billing_company:$user_description,
                                    "address" => $billing_address_1,
                                    //"city" => $billing_city,
                                    "city" => (trim($billing_city) != "")?$billing_city:$billing_country,
                                    "zipCode" => ($billing_country == 'IT')?$billing_postcode:"00000",
                                    "province" => $billing_state,
                                    "country" => $billing_country,
                                    "pec" => $billing_pecaddress,
                                );
                            }else if( $billing_user_type == "pperson" ){ // Individual
                                $anagrafica_user = array(
                                    "code" => $billing_getyourbillid,
                                    "id" => $id,
                                    "vatNumber" => $billing_pi,
                                    "taxCode" => $billing_cf,
                                    "description" => ($billing_company != "")?$billing_company:$user_description,
                                    "address" => $billing_address_1,
                                    //"city" => $billing_city,
                                    "city" => (trim($billing_city) != "")?$billing_city:$billing_country,
                                    "zipCode" => ($billing_country == 'IT')?$billing_postcode:"00000",
                                    "province" => $billing_state,
                                    "country" => $billing_country,
                                    "pec" => $billing_pecaddress,
                                );
                            }
                        }else if( $billing_user_type == "psoleproprietor" ){ // Privato
                            $anagrafica_user = array(
                                "code" => $billing_getyourbillid,
                                "id" => $id,
                                "vatNumber" => "",
                                "taxCode" => $billing_cf,
                                "description" => ($billing_company != "")?$billing_company:$user_description,
                                "address" => $billing_address_1,
                                //"city" => $billing_city,
                                "city" => (trim($billing_city) != "")?$billing_city:$billing_country,
                                "zipCode" => ($billing_country == 'IT')?$billing_postcode:"00000",
                                "province" => $billing_state,
                                "country" => $billing_country,
                                "pec" => $billing_pecaddress,
                            );
                            if( $billing_country != 'IT' ){
                                $anagrafica_user["vatNumber"] = $billing_cf;
                            }
                        }else if( $billing_user_type == "pa" ){ // PA

                            $anagrafica_user = array(
                                "code" => $billing_getyourbillid,
                                "id" => $id,
                                "vatNumber" => $billing_pi,
                                "IPACode" => $IPACode,
                                "taxCode" => $billing_cf,
                                "description" => ($billing_company != "")?$billing_company:$user_description,
                                "address" => $billing_address_1,
                                //"city" => $billing_city,
                                "city" => (trim($billing_city) != "")?$billing_city:$billing_country,
                                "zipCode" => $billing_postcode,

                                "province" => $billing_state,
                                "country" => $billing_country,
                                "pec" => $billing_pecaddress,
                            );
                        }

                        $billing_sdiid = get_post_meta( $order_id, "billing_sdiid", true );
                        if( !empty($billing_sdiid) && $billing_sdiid != "" ){
                            $anagrafica_user["IntermediaryCode"] = ($billing_country == 'IT' || $billing_country == 'SM')?$billing_sdiid:esc_html(__("XXXXXXX", 'getyourbill') );
                        }

                        $invoice = @$app->prepare_fattura_in_JSON($docType, $order, $anagrafica_user, $transactionId, $recoverCode, $order_date, $note, $order_note, $order_id, $billing_user_type );

                        if( GetYourBillDEVMOD && '' !== $this->get_request_text( 'ttest' ) ){
                            delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                            return false;
                        }

                        if( is_bool($invoice) ){
                            delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                            return false;
                        }

                        $send_ = $api->send_fattura_json($invoice);

                        if( is_array($send_) && array_key_exists("error", $send_) ){
                            update_post_meta( $order_id, "getyourbill_send_error", "bug_fattura" );
                            $error = json_decode($send_["error"]);
                            if( !empty($error->errorMessage) ){
                                update_post_meta( $order_id, "getyourbill_if_error_mess", $error->errorMessage );
					            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                                \models\APIGetyourbill::set_error_log( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"] ), $error->errorMessage." order id : ".$order_id." p1" ) ) ;
                                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"] ), $error->errorMessage ) );
                                $order_note->save();
                            }else{
                                update_post_meta( $order_id, "getyourbill_if_error_mess", wp_json_encode($error) );
                                \models\APIGetyourbill::set_error_log( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"] ), wp_json_encode($error)." order id : ".$order_id." p2" ) );
                                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Error["order_errormess"] ), wp_json_encode($error) ) ) ;
                                $order_note->save();
                            }
                            update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                            delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                            return false;
                        }else{
                            if( $send_ ){
                                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Success["order_send_fattura_ok_notice"] ), $gyb_recovericod ), false, true);
                                $order_note->save();
                                update_post_meta( $order_id, "getyourbill_send_ok_f_json", "yes" );
                                update_post_meta( $order_id, "getyourbill_send_ok_on_gyb", "yes" );

                                // get invoi number_count
                                if( $this->getyourbill_get_invoice_number($order_id) ){
                                    if( $this->getyourbill_set_invoice_pdf($order_id, true) ){
                                        delete_post_meta($order_id, "getyourbill_send_error");
                                        update_post_meta( $order_id, "getyourbill_status_invoice_ok", "processiong" );
                                        $completeDocNumber = get_post_meta( $order_id, "getyourbill_invoice", true );
                                        $invoicePDF = \models\GetyourbillApp::get_invoice_pdf_encode($order);
                                        do_action( 'getyourbill_after_send_to_gyb_invoice_pdf', $order_id, $completeDocNumber, $invoicePDF );
                                    }else{
                                        update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                                        update_post_meta( $order_id, "getyourbill_send_error", "pdf" );
                                    }
                                }else{
                                    update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                                    update_post_meta( $order_id, "getyourbill_send_error", "number" );
                                }
                                delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                                do_action('getyourbill_delete_temp_prod', $order_id);
                                return true;
                            }else{
                                \models\APIGetyourbill::set_error_log(  sprintf( GetYourBill_Messaggi_Error["order_send_invoice_error"], $gyb_recovericod." order id : ".$order_id." p3" ) );
                                $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Error["order_send_invoice_error"],  $gyb_recovericod ) );
                                $order_note->save();
                                update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                                delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                                return false;
                            }
                        }
                        return $send_;
                    }else{
                        \models\APIGetyourbill::set_error_log( sprintf( GetYourBill_Messaggi_Error["order_generazine_RecoverCode_error"], $order_id ) );
                        $order_note->add_order_note( sprintf( GetYourBill_Messaggi_Error["order_generazine_RecoverCode_error"], $order_id ) );
                        $order_note->save();
                        delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                        update_post_meta( $order_id, "getyourbill_send_ok_f_json", "error" );
                        delete_transient('getyourbill_fattura_in_creazione_'.$order_id);
                        return false;
                    }
                }else{
                    delete_post_meta($order_id, "getyourbill_send_ok_f_json");
                    return false;
                }
            }
        }
        return false;
    }


    public function getyourbill_set_invoice_pdf( $order_id = 0, $type = false ){
        $api = new \models\APIGetyourbill();
        $order = new \WC_Order($order_id);
        $mess = "";
        $databool = false;
        if( !is_wp_error( $order ) ) {
            $invoicePDF = \models\GetyourbillApp::get_invoice_pdf_encode($order);
            if( 'invoicepdf_test' === $this->get_request_key( 'gybevent' ) ){
                return $invoicePDF;
            }
            $recoverCode = get_post_meta( $order_id, "getyourbill_recoverCode", true );
            if( !is_null($invoicePDF) ){
                $invoice_nr = get_post_meta( $order_id, "getyourbill_invoice", true );
                $completeDocNumber = get_post_meta( $order_id, "getyourbill_completenumber", true );
                $transactionId = get_post_meta( $order_id, "getyourbill_iniquenumberinvoice", true );
                $docDate = get_the_date("Y-m-d", $order_id );
                $databool = $api->put_invoce_number($docDate, $recoverCode, $transactionId, $completeDocNumber, $invoicePDF);
                $mess = sprintf( esc_html( GetYourBill_Messaggi_Success["order_pdf_aggiornato"] ), $invoice_nr, $recoverCode );
                $order_note = wc_get_order( $order_id );
                $order_note->add_order_note( $mess, false, true);
                $order_note->save();
            }else{
                $mess = sprintf( esc_html( GetYourBill_Messaggi_Error["order_generazine_pdf_error"]), $order_id, $recoverCode );
                $order_note = wc_get_order( $order_id );
                $order_note->add_order_note( $mess, false, true);
                $order_note->save();
            }
        }else{
            $mess = sprintf( esc_html( GetYourBill_Messaggi_Error["order_empty"]), $order_id );
            \models\GetyourbillApp::send_log( $mess );
        }
        return ($type)?$databool:$mess;
    }



    function get_invois_date_by_mode($order_id = 0 ){
        $temp_order = new WC_Order($order_id);
        if( apply_filters( 'getyourbill_switch_data_fatturazione', $temp_order ) ){
            return $temp_order->get_date_paid()->date("Y-m-d");
        }else{
//            return get_the_date("Y-m-d", $order_id);
            return gmdate("Y-m-d");
        }
    }

    public function getyourbill_get_invoice_number( $order_id = 0 ){
        $api = new \models\APIGetyourbill();

        $recoverCode = get_post_meta( $order_id, "getyourbill_recoverCode", true );
        if( !empty($recoverCode) && $recoverCode != "" ){
            $transactionId = get_post_meta( $order_id, "getyourbill_iniquenumberinvoice", true );
            $docDate = $this->get_invois_date_by_mode( $order_id );
            $r = $api->get_invoce_number(  $docDate, $recoverCode, $transactionId);
            if( is_array($r) && array_key_exists("docNumber", $r) ){
                $completeDocNumber = $r["completeDocNumber"];
                $invoice_nr = $r["docNumber"];
                update_post_meta( $order_id, "getyourbill_invoice", $completeDocNumber);
                update_post_meta( $order_id, "getyourbill_invoice_year", gmdate("Y"));
                update_post_meta( $order_id, "getyourbill_completenumber", $completeDocNumber );
                $this->getyourbill_invoice_update_plugin_extra($order_id, $completeDocNumber);
                $order_note = wc_get_order( $order_id );
                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Success["notice_prepare_fattura"] ), $completeDocNumber, $recoverCode ), false, true);
                $order_note->save();
                return true;
            }else{
                $order_note = wc_get_order( $order_id );
                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Error["order_generazine_invoice_number_error"] ), $order_id, $recoverCode ), false, true);
                $order_note->save();
            }
            return false;
        }
        \models\GetyourbillApp::send_log( sprintf( esc_html( GetYourBill_Messaggi_Error["order_recupercode_empty"] ), $order_id ) );
        return false;
    }

    public function getyourbill_get_getyourbillfieldsoptions( $label = "" ){
        return sprintf(esc_html (GetYourBill_Messaggi_Warning["inputs_request"], $label));
    }

    public function getyourbill_edit_option_value( $safe_text, $text ){
        return $this->getyourbillfieldsdesc_senza_testo;
    }




    function getyourbill_is_request_empty($user_id){
        $out = false;
        $post_data = $this->get_post_data_request();
        $this->s_typeaccount = ( isset($post_data["billing_user_type"])?$post_data["billing_user_type"]:$this->get_type_user_account($user_id) );

        if( !array_key_exists($this->s_typeaccount, $this->getyourbillfieldstypeaccounts) ){
            return true;
        }
        $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );
        $request_filds = $this->is_request_by_config_request($data);
        $fields = $this->getyourbillfieldslist;
        $ind=0;
        foreach ( $fields as $key => $field_args ) {
            if( array_key_exists($key, $request_filds) && $request_filds[$key] ){
                $value = (isset($post_data[$key])?$post_data[$key]:get_the_author_meta( $key, $user_id ));
                if( $value == ""){
                    $ind++;
                }
            }
            if($ind>2){
                $out = true;
            }
        }
        return $out;
    }

    public function getyourbill_woocommerce_before_edit_address_form_( ) {
        if( !is_null($this->user_id) ){
            include_once(GetYourBillPATH."template/autocomplete_input.php");
        }
    }

    public function getyourbill_checkout_fields_load_info_by( ) {
        if( !is_null($this->user_id) || true){
            include_once(GetYourBillPATH."template/autocomplete_input.php");
        }
    }

    function getyourbill_sorting_by_type_account($type = ""){
        $out = array();
        foreach ( $this->getyourbillfieldsoptionsBackEnd as $key => $name ) {
            if( array_key_exists($type, $this->getyourbillcontrooutsort) ){
                if( in_array($key, $this->getyourbillcontrooutsort[$type] )){
                    $out[$key] = $name;
                }
            }
        }
        return $out;
    }

    function getyourbill_custom_js_json() {
        $this->request = $this->is_request_by_config_request_all();
        $is_r = "false";
        if( !is_null($this->user_id) ){
            if(  $this->getyourbill_is_request_empty($this->user_id) ){
                $is_r = "true";
            }
        }

        $getyourbill_traslate = GetYourBill_Messaggi_JS;
        foreach ( $getyourbill_traslate as $t => $traslate ) {
            $getyourbill_traslate[$t] = esc_html( $traslate );
        }
        $this->getyourbill_messagges = GetYourBillAjax;

        foreach ( $this->getyourbill_messagges as $t => $traslate ) {
            $this->getyourbill_messagges[$t] = esc_html( $traslate );
        }

        ?>
        <script type="text/javascript">
            var getyourbill_traslate = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $getyourbill_traslate, JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_order_tax = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $this->order_tax, JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_script = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode(
                array(
                    'ajax'  => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'getyourbill_get_campi_by_user' ),
                )
            );
            ?>;
            var getyourbill_messagges = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $this->getyourbill_messagges, JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_all = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $this->request[0], JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_all_desc = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $this->request[1], JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_all_validate = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo wp_json_encode( $this->request[2], JSON_FORCE_OBJECT );
            ?>;
            var getyourbill_is_r = <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON scalar for inline script.
            echo wp_json_encode( $is_r );
            ?>;
        </script>
        <?php
        if( is_admin() && count($this->order_field_precompilati) ){
            $infoorderjs = array();
            foreach ( $this->order_field_precompilati as $key => $field_args ) {
                $infoorderjs[$key] = $field_args["value"];
            }
            ?>
            <script type="text/javascript">
                var getyourbill_userinfo = <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                echo wp_json_encode( $infoorderjs, JSON_FORCE_OBJECT );
                ?>;
            </script>
            <?php
        }
    }

    function getyourbill_custom_footer() {
        include_once(GetYourBillPATH."template/autocomplete_form.php");
    }
    function getyourbill_custom_js_admin() {
        wp_enqueue_script('getyourbill_custom_admin_script', GetYourBillURL . 'assets/js/admin/script.js', array("jquery"), filemtime( GetYourBillPATH . 'assets/js/admin/script.js' ), true);
    }

    public function set_config_request_by_type_account( $key, $field_args = array(), $request = array(), $s_typeaccount = "" ){

        if ( array_key_exists($key, $this->getyourbillfieldslist) ) {

            $is_view_by_config = self::is_view_by_config($key."_".$s_typeaccount);
            $is_request_by_config = false;
            if( array_key_exists("important", $field_args) && $field_args["important"] ){
                $field_args['required_checkout'] = ( $request[$key] === "se" || $request[$key] === true );
                $field_args['required'] = ( $request[$key] === "se" || $request[$key] === true );
                $field_args['view_registre'] = ( $request[$key] === "se" || $request[$key] === true );
                $field_args['description'] = ( $request[$key] === "se" )?"":"";
                $field_args['desc_tip'] = ( $request[$key] === "se" );
                $field_args['view_order'] = ( $field_args['view_order'] || $request[$key] === "se" || $request[$key] === true );
            }else{
                //$field_args['required_checkout'] = $is_view_by_config;
                $field_args['required'] = $is_request_by_config;
                $field_args['view_registre'] = $is_view_by_config;
                $field_args['view_order'] = ( $field_args['view_order'] || $is_request_by_config );
            }

        }

        return $field_args;

    }

    public function get_type_user_account($user_id = null){
        $this->s_typeaccount = self::get_info("accountdefault");
        if( !is_null($user_id) ){
            $this->s_typeaccount = get_the_author_meta( "billing_user_type", $user_id );
            if( !empty($this->s_typeaccount) && $this->s_typeaccount == "" ){
                return self::get_info("accountdefault");
            }
        }else if( !is_null($this->user_id) ){
            $this->s_typeaccount = get_the_author_meta( "billing_user_type", $this->user_id );
        }
        $default_type = ( !empty($this->getyourbillfieldstypeaccounts) ) ? array_key_first( $this->getyourbillfieldstypeaccounts ) : "pperson";
        return ( is_null( $this->s_typeaccount ) || !array_key_exists($this->s_typeaccount, $this->getyourbillfieldstypeaccounts) )?$default_type:$this->s_typeaccount;
    }

    public function getyourbill_ajax_get_customer_details( $data ) {
        if(  array_key_exists("billing", $data) && array_key_exists("id", $data) ){

            $fields = $this->getyourbill_get_account_fields();
            foreach ( $fields as $key => $field_args ) {
                $value = get_the_author_meta( $key, $data["id"] );
                $data["billing"][$key] = (!empty($value))?$value:"";
            }
            $value= get_the_author_meta( "billing_id_anagrafica", $data["id"] );
            $data["billing"]["billing_id_anagrafica"] = (!empty($value))?$value:"";
        }
        return $data;
    }

    public function getyourbill_shop_order_table_head( $defaults ) {
        $defaults['fconfig_statuscolum'] = esc_html( GetYourBill_altro["calumnamestatus"] );
        return $defaults;
    }
    public function getyourbill_shop_order_table_content( $column_name, $order_id ) {

        if ($column_name == 'fconfig_statuscolum') {
            $order = wc_get_order( $order_id );
            $getyourbill_send_ok_f_json = get_post_meta( $order_id, "getyourbill_send_ok_f_json", true );
            if( false == get_transient( 'getyourbill_fattura_in_creazione_'.$order_id) ) {
                $getyourbill_send_ok_receiverResult = get_post_meta( $order_id, "getyourbill_send_ok_receiverResult", true );
                $getyourbill_is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
                $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
                if( !empty($getyourbill_invoice) ){
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                    echo '<strong style="padding-bottom: 5px;display: inline-block;">'.esc_html( GetYourBill_altro["nrinvoice"] )." ".$getyourbill_invoice.'</strong><br>';
                }
                if( $getyourbill_send_ok_f_json == "yes" || $getyourbill_is_send_sdi == "yes" ){
                    $getyourbill_status_invoice_ok = get_post_meta( $order_id, "getyourbill_status_invoice_ok", true );
                    $getyourbill_is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
                    if( $getyourbill_is_send_sdi == "yes" ){
                        $error_sdi = get_post_meta( $order_id, "getyourbill_if_error_sdi", true );
                        $messaggio =  esc_html( GetYourBill_Messaggi_Success["order_colum_status_sdi_ok"] );
                        if( !empty( $error_sdi ) ){
                            $messaggio .=  ". ".esc_html( GetYourBillSdiErrorIntermediaError["impossibleDeliveryDateTime"]["name"] );
                        }
                        $this->get_content_messaggio("column", $messaggio, "completed gyb_completed");
                    }else if( $getyourbill_status_invoice_ok == "error" ){
                        $messaggio =  esc_html( GetYourBill_Messaggi_Error["order_colum_status_errors"] );
                        $this->get_content_messaggio("column", $messaggio, "failed gyb_failed");
                    }else if( $getyourbill_send_ok_f_json == "yes" ){
                        $messaggio =  esc_html( GetYourBill_Messaggi_Success["order_colum_status_send_ok"] );
                        $this->get_content_messaggio("column", $messaggio, "pending gyb_pending");
                    }
                }else{
                    $fields = $this->getyourbill_get_account_fields();
                    $orders_info = array();
                    foreach ( $fields as $key => $field_args ) {
                        $field_args['value'] = get_post_meta( $order_id, $key, true );
                        $orders_info[$key] = $field_args;
                    }
                    $this->s_typeaccount = get_post_meta( $order_id, "billing_user_type", true );
                    $country = get_post_meta( $order_id, "_billing_country", true );
                    if( !$this->validation_global($orders_info, "empty", true, $this->s_typeaccount, $country, $order_id ) ){
                        $messaggio =  esc_html( GetYourBill_Messaggi_Error["order_colum_status_errors_missing_dati"] );
                        $this->get_content_messaggio("column", $messaggio, "failed gyb_failed");
                    }
                }
                if( $getyourbill_send_ok_f_json == "error" ){
                    $messaggio =  esc_html( GetYourBill_Messaggi_Error["order_colum_status_errors"] );
                    $this->get_content_messaggio("column", $messaggio, "failed gyb_failed");
                }


            }else{
                if( $getyourbill_send_ok_f_json == "error" ){
                    $messaggio =  esc_html( GetYourBill_Messaggi_Warning["fattura_in_esecuzioni"] );
                    $this->get_content_messaggio("column", $messaggio, "processing gyb_processing");
                }
            }

        }
    }

    function getyourbill_protected_meta_filter($protected, $meta_key) {
        $fields = $this->getyourbill_get_account_fields();
        foreach ( $fields as $key => $field_args ) {
            if ( $key == $meta_key ) {
                return true;
            }
        }

        $fields = array(
            "getyourbill_iniquenumberinvoice" => "",
            "billing_update_info_on_checkout" => "",
            "getyourbill_invoice" => "",
            "getyourbill_recoverCode" => "",
            "getyourbill_send_ok_f_json" => "",
            "billing_id_anagrafica" => "",
            "getyourbill_if_error_mess" => "",
            "getyourbill_invoice_year" => "",
            "getyourbill_send_ok_on_gyb" => "",
            "getyourbill_status_invoice_ok" => "",
            "getyourbill_completenumber" => "",
            "getyourbill_send_error" => "",
            "getyourbill_auto" => "",
            "getyourbill_status_invoice" => "",
            "getyourbill_send_ok_receiverResult" => "",
            "getyourbill_send_ok_receiverResult" => "",
            "getyourbill_send_ok_f_info" => "",
            "getyourbill_is_send_sdi" => "",
            "getyourbill_if_error_sdi" => "",
        );

        foreach ( $fields as $key => $field_args ) {
            if ( $key == $meta_key ) {
                return true;
            }
        }
        return $protected;
    }
    function mobilefolk_order_item_get_formatted_meta_data($formatted_meta){
        $temp_metas = [];

        return $temp_metas;
    }

    function get_new_invoice_number( ){
        $max = \models\GetyourbillApp::get_min_max_meta_value("max", "getyourbill_invoice");
        return $max+1;
    }


    function get_new_long_int($long_int = 99, $id_order = 0 ){
        $nr_out = 0;
        $query_meta = array(
            'post_type' => 'shop_order',
            'meta_key' => 'getyourbill_iniquenumberinvoice', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_value' => $long_int // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        );
        $time_posts = new WP_Query( $query_meta );
        return $time_posts->post_count == 0?$long_int:$this->get_new_long_int(self::generate_long_int(), $id_order);
    }
    public static function prepara_object_struttura($data = array()){
        $out = array();
        parse_str($data, $out);
        return $out;
    }
    public static function get_name_tax($tax, $taxs = array() ) {
        if( isset( $taxs[$tax] ) ){
            return $taxs[$tax];
        }
        return esc_html__( 'missing', 'getyourbill' );
    }
    public static function get_name_gyb_tax($tax, $taxs = array() ) {
        if( isset( GetYourBillTaxes[$tax] ) ){
            return esc_html( GetYourBillTaxes[$tax]["name"] );
        }
        return esc_html__( 'missing', 'getyourbill' );
    }

    public static function get_list_WC_Tax( ) {
        $alltax_by = array();
        $all_tax_rates = [];
        $tax_classes = WC_Tax::get_tax_classes(); // Retrieve all tax classes.
        if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
            array_unshift( $tax_classes, '' );
        }
        foreach ( $tax_classes as $tax_class ) { // For each tax class, get all rates.
            $all_tax_rates[$tax_class] = WC_Tax::get_rates_for_tax_class( $tax_class );
            $taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
            if( count($all_tax_rates[$tax_class]) >0  ){
                foreach ( $all_tax_rates[$tax_class] as $tax ) { // For each tax class, get all rates.
                    $alltax_by[$tax->tax_rate_id]  = $tax->tax_rate_country." ".$tax->tax_rate_name;
                }
            }
        }
        return [$all_tax_rates, $alltax_by];
    }

    public static function get_list_itemVatCode( $deprecated = false ) {

        $getyourbill_tax_rates = [];
        foreach ( GetYourBillTaxes as $key => $vatcode ) {
            if( $deprecated ){
                if( !isset($vatcode["VatKind"]) ){
                    $getyourbill_tax_rates[] = array( "info" => $vatcode, "code" => $key );
                }else{
                    if( !in_array($vatcode["VatKind"], $getyourbill_tax_rates) ){
                        if( isset(GetYourBillVatKind[$vatcode["VatKind"]])){
                            $vatcode["name"] = GetYourBillVatKind[$vatcode["VatKind"]];
                        }
                        $getyourbill_tax_rates[$vatcode["VatKind"]] = array( "info" => $vatcode, "code" => $key,  "ns" => array() );
                    }
                }
            }else{
                if( !isset($vatcode["deprecated"]) ){
                    if( !isset($vatcode["VatKind"]) ){
                        $getyourbill_tax_rates[] = array( "info" => $vatcode, "code" => $key );
                    }else{
                        if( !in_array($vatcode["VatKind"], $getyourbill_tax_rates) ){
                            if( isset(GetYourBillVatKind[$vatcode["VatKind"]])){
                                $vatcode["name"] = GetYourBillVatKind[$vatcode["VatKind"]];
                            }
                            $getyourbill_tax_rates[$vatcode["VatKind"]] = array( "info" => $vatcode, "code" => $key,  "ns" => array() );
                        }
                    }
                }
            }
        }
        foreach ( GetYourBillTaxes as $key => $vatcode ) { // For each tax class, get all rates.
            if( isset($vatcode["VatKind"]) ){
                if( !isset($vatcode["deprecated"]) ){
                    $vatcode["code"] = $key;
                    $getyourbill_tax_rates[$vatcode["VatKind"]]["ns"][] = $vatcode;
                }
            }
        }
        return $getyourbill_tax_rates;
    }

    public static function load_template_part($template_name, $vars = array() ) {
        $template_path = wp_normalize_path( $template_name . '.php' );
        $plugin_path   = wp_normalize_path( GetYourBillPATH );

        if ( 0 !== strpos( $template_path, $plugin_path ) || ! file_exists( $template_path ) ) {
            return '';
        }

        ob_start();
        foreach ( $vars as $k => $v ){
            set_query_var($k, $v);
        }
        // nosemgrep: audit.php.lang.security.file.inclusion-arg -- Template path is normalized, restricted to GetYourBillPATH, and existence-checked above.
        include_once( $template_path );
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }

    public static function generate_long_int($p = 5){
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $p; $i++) {
            $randomString .= $characters[wp_rand(0, $charactersLength - 1)];
        }
        return time().$randomString;
    }



    function getyourbill_woocommerce_edit_order_fields($order_id, $post)
    {
        $post_data = $this->get_post_data_request();
        if( isset($post_data["getyourbill_invoice"]) ){
            $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
            if( wc_clean($post_data["getyourbill_invoice"]) != "" && $getyourbill_invoice != wc_clean($post_data["getyourbill_invoice"]) ){
                $long_int = $this->get_new_long_int(self::generate_long_int(), $order_id).$order_id;
                update_post_meta( $order_id, "getyourbill_invoice", wc_clean($post_data["getyourbill_invoice"]) );
                update_post_meta( $order_id, "getyourbill_iniquenumberinvoice", $long_int );
                $this->getyourbill_invoice_update_plugin_extra($order_id, wc_clean($post_data["getyourbill_invoice"]));
                $order_note = wc_get_order( $order_id );
                $order_note->add_order_note( sprintf( esc_html( GetYourBill_Messaggi_Success["notice_update_fattura"] ), $getyourbill_invoice, wc_clean($post_data["getyourbill_invoice"])  ) );
                $order_note->save();
            }
        }

        $fields = $this->getyourbill_get_account_fields(true);
        foreach ( $fields as $key => $field_args ) {
            if ( isset($post_data[ $key ]) ) {
                $field = wc_clean( sanitize_text_field(wc_clean($post_data[ $key ]) ));
                update_post_meta( $order_id, $key, $field );
            }else if( $key  != "billing_user_type" ){
                update_post_meta( $order_id, $key, "" );
            }
        }
    }


    public function getyourbill_woocommerce_checkout_process( )
    {
        $fields = $this->getyourbill_get_account_fields(true);
        $this->validation_global($fields, false );
    }

    public static function include_parts( $path )
    {
        $template_path = wp_normalize_path( GetYourBillPATH . ltrim( $path, '/\\' ) . '.php' );
        $plugin_path   = wp_normalize_path( GetYourBillPATH );

        if ( 0 !== strpos( $template_path, $plugin_path ) || ! file_exists( $template_path ) ) {
            return;
        }

        // nosemgrep: audit.php.lang.security.file.inclusion-arg -- Template path is normalized, restricted to GetYourBillPATH, and existence-checked above.
        include( $template_path );
    }

    public function getyourbill_order_buttons( $order ){

        $out__html = "";
        $order_id = $order->get_id();
        $out__html .=  '<div style="display: inline-block;width: 100%;margin-top: 15px;">';

        $invoicegenerator = self::get_info("config_invoicegenerator");
        $out__html .= '<h3 order_gestione_h3 style="display: inline-block;width: 100%;margin-bottom: 15px;">'.esc_html( GetYourBill_altro["order_gestione_h3"] ).'</h3>';
        $getyourbill_is_send_sdi = get_post_meta( $order_id, "getyourbill_is_send_sdi", true );
        $getyourbill_iniquenumberinvoice = get_post_meta( $order_id, "getyourbill_iniquenumberinvoice", true );
        if( !empty($getyourbill_iniquenumberinvoice) ){
            if( $invoicegenerator == "getyourbill_template" ){
                $out__html .= '<a target="_black" href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicepdf_print' ) ).'" class="getyourbill_invoice button" >'.esc_html( GetYourBill_altro["button_label_get_invoice_manualmente"] ).'</a>';
            }else{
                $out__html .= '<a href="'.\models\GetyourbillApp::get_invoice_url($order_id).'" target="_blank" class="getyourbill_invoice button" type="submit"  >'.esc_html( GetYourBill_altro["button_label_view_invoice"] ).'</a>';
            }
        }

        $getyourbill_send_ok_f_json = get_post_meta( $order_id, "getyourbill_send_ok_f_json", true );
        $billing_user_type = get_post_meta( $order_id, "billing_user_type", true );
        if( empty($billing_user_type) ){
            $user_id = $order->get_user_id();
            if( $user_id > 0 ){
                $billing_user_type = get_the_author_meta( "billing_user_type", $user_id );
            }
        }
        $docType = $this->get_typeinvoice_by_config($billing_user_type, true);

        if( $docType == "i_61" && $getyourbill_send_ok_f_json == "yes" ){
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicestatus' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html( GetYourBill_altro["button_label_get_invoice_status"] ).'</a>';
        }


        if( $getyourbill_is_send_sdi !== "yes" ){


            $getyourbill_invoice = get_post_meta( $order_id, "getyourbill_invoice", true );
            $getyourbill_send_ok_on_gyb = get_post_meta( $order_id, "getyourbill_send_ok_on_gyb", true );
            $getyourbill_if_error_mess = get_post_meta( $order_id, "getyourbill_if_error_mess", true );
            $getyourbill_status_invoice_ok = get_post_meta( $order_id, "getyourbill_status_invoice_ok", true );
            $getyourbill_recoverCode = get_post_meta( $order_id, "getyourbill_recoverCode", true );
            $getyourbill_send_error = get_post_meta( $order_id, "getyourbill_send_error", true );

            $out__html .= '<div style="margin-top: 15px;">';

            if( !empty($getyourbill_recoverCode) && $getyourbill_send_error != "bug_fattura" ){
                $last_nr_invois = \models\GetyourbillApp::get_last_order_send_number();
                if( $last_nr_invois == $order_id ){
                    $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoiceannulla' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html( GetYourBill_altro["button_label_annulla_invoice"] ).'</a>';
                }
            }

            if( $getyourbill_send_ok_f_json !== "yes" && $getyourbill_send_error != "bug_fattura"){
                $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'setinvoicepay' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html( GetYourBill_altro["button_label_send_invoice_manualmente"] ).'</a>';
            }

            if( $getyourbill_send_error == "bug_fattura" ){
                $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'errorvalid' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html( GetYourBill_altro["button_label_get_stoperror"] ).'</a>';
            }

            if( GetYourBillDEVMOD ){
                $out__html .= '<br> billing_update_info_on_checkout: '.get_post_meta( $order_id, "billing_update_info_on_checkout", true ).'<br>';
                $out__html .= '<br> getyourbill_auto: '.get_post_meta( $order_id, "getyourbill_auto", true ).'<br>';
                $out__html .= '<br> billing_id_anagrafica: '.get_post_meta( $order_id, "billing_id_anagrafica", true ).'<br><br><br>';
                $out__html .= '<br> getyourbill_send_ok_f_json: '.$getyourbill_send_ok_f_json.'<br>';
                $out__html .= '<br> getyourbill_send_error: '.$getyourbill_send_error.'<br>';
                $out__html .= '<br> getyourbill_status_invoice_ok: '.$getyourbill_status_invoice_ok.'<br>';
                $out__html .= '<br> getyourbill_if_error_mess: '.$getyourbill_if_error_mess.'<br>';
                $out__html .= '<br> getyourbill_is_send_sdi: '.$getyourbill_is_send_sdi.'<br>';
            }

            $out__html .= '<p>&nbsp;</p></div>';


        }




        $out__html .= '<p>&nbsp;</p> ';

        $out__html .= '</div>';

        if( GetYourBillDEVMOD ){
            $out__html .=  '<div style="display: inline-block;width: 100%;">';
            $out__html .= '<br><br><br><br><br><a href="'.esc_url( self::get_gybevent_url( $order_id, 'setinvoicepaytest', array( 'ttest' => 'ttest' ) ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html (__("Fattura json", 'getyourbill') ).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'printbase64' ) ).'" class="getyourbill_invoice button" type="submit"  target="_blank" >'.esc_html(__("Fattura base64 encode", 'getyourbill')).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoiceannulla' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html(__("Annulla fattura", 'getyourbill')).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoiceannullaforce' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html(__("Forza l'anulla fattura", 'getyourbill')).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicestatus' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html(__("Status fattura", 'getyourbill')).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicenumber' ) ).'" class="getyourbill_invoice button" type="submit"  >'.esc_html(__("Number fattura", 'getyourbill')).'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicepdf' ) ).'" class="getyourbill_invoice button" type="submit"  >'.__("PDF fattura", 'getyourbill').'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'invoicepdf_test' ) ).'" class="getyourbill_invoice button" type="submit"  >'.__("PDF fattura test", 'getyourbill').'</a>';
            $out__html .= '<a href="'.esc_url( self::get_gybevent_url( $order_id, 'send_invoice_invoicepdf_test' ) ).'" class="getyourbill_invoice button" type="submit"  >'.__("Send email PDF invoice", 'getyourbill').'</a>';
            $out__html .= '</div>';
        }

        $pactive = \models\GetyourbillApp::get_info("pactive");
        $out__html .= '
			<div id="gyb_popup">
			    <div id="gyb_popupOverlay"></div>
				<div id="gyb_popupContent">
					<p id="getyourbill_autocomlete_content_response"></p>
					<div id="getyourbill_autocomlete" class="getyourbill_autocomlete">
						<div class="getyourbill_autocomlete_content">
							<div class="getyourbill_autocomlete_content_in">
								<p class="form-row form-row form-row-wide" >'.esc_html(__("Recupera automaticamente i dati anagrafici, fai una ricerca:", 'getyourbill')).'</p>
								<p class="form-row form-row form-row-wide" >
									<span class="woocommerce-input-wrapper">
										<input type="text" class="input-text getyourbill_info" mod="pi" placeholder="'.esc_html(__("Partita Iva", 'getyourbill')).'" value="" autocomplete="off">
									</span>
								</p>
								<p class="form-row form-row form-row-wide" >
									<span class="woocommerce-input-wrapper">
										<input type="text" class="input-text getyourbill_info" mod="cf" placeholder="'.esc_html(__("Codice Fiscale", 'getyourbill')).'" value="" autocomplete="off">
									</span>
								</p>
								<p class="form-row form-row form-row-wide" >
									<span class="woocommerce-input-wrapper">
										<input type="text" class="input-text getyourbill_info getyourbillid_icon" mod="bilid" placeholder="'.esc_html(__("Getyourbill ID", 'getyourbill')).'" value="" autocomplete="off">
									</span>
								</p>';
        if( $pactive === "yes" ){
            $out__html .= '
                <p class="form-row form-row form-row-wide" >
                  <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text getyourbill_info" mod="paid" placeholder="'.esc_html(__("Codice ipa del cliente (anagrafica PA)", 'getyourbill')).'" value="" autocomplete="off">
                  </span>
                </p>
                ';
        }
        $out__html .= '
                <button id="getyourbill_autocomlete_cerca" type="button" class="button alt" value="'.esc_html(__("Cerca anagrafica", 'getyourbill')).'" >'.esc_html(__("Cerca anagrafica", 'getyourbill')).'</button>
								<div class="getyourbill_autocomlete_close"><a  class="gybtogglePopup" href="javascript:void(0);">'.esc_html(__("Inserisci i dati manualmente", 'getyourbill')).'</a></div>
							</div>
							<div class="power_by">
								<a href="https://www.getyourbill.com/" target="_blank">Powered by GetYourBill</a>
							</div>
						</div>
					</div>
				</div>
			</div>';
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $out__html;
    }

    public function getyourbill_order_fields( $order ){
        echo '<div id="get_order_fields">';
        $this->prepare_fileds_order_by( $order->get_user_id(), $order->get_id() );
        echo '</div>';
    }
    public function get_woocommerce_wp_text_input($field  ) {
        ob_start();
        woocommerce_wp_text_input( $field );
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }
    public function get_woocommerce_form_field( $field ) {
        ob_start();
        woocommerce_wp_select( $field );
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }

    function prepare_fileds_order_by( $user_id = 0, $order_id = null, $print_or = true ){
        if( is_null($order_id) ){
            return;
        }
        $out__html = '';



        // billing anagrafica
        $billing_id_anagrafica = get_post_meta( $order_id, "billing_id_anagrafica", true );
        $out__html .= '<input id="_billing_billing_id_anagrafica"  name="billing_id_anagrafica" type="hidden" value="'.$billing_id_anagrafica.'" />';

        // se è stato usato la ricerca automatica nel checkout
        if( !empty(get_post_meta( $order_id, "getyourbill_auto", true )) ){
            $out__html .= '<input name="getyourbill_auto" type="hidden" value="auto" />';
        }

        $out__html .= '<div class="edit_address"><p>&nbsp;</p><h3>'.esc_html(__("Dati anagrafici", 'getyourbill')).' '.$this->s_typeaccount.'</h3>';
        $out__html .= '<p>'.esc_html(__("I dati anagrafici saranno aggiornati automaticamente", 'getyourbill')).'</p>';
        foreach ( $this->order_field_precompilati as $key => $field ) {
            if( $key == "billing_user_type"){
                $field['value'] = $this->s_typeaccount;
                $field['type'] = "select";
                $field['id'] = "billing_user_type";
                $field['autocomplete'] = false;
                $out__html .= $this->get_woocommerce_form_field( $field );
                $out__html .= '<p style="color:red">'.esc_html(__("I dati anagrafici non saranno aggiornati automaticamente", 'getyourbill')).'</p>';
            }else{
                $out__html .= $this->get_woocommerce_wp_text_input( $field );
            }
        }
        $out__html .= '</div>';
        $out__html .= '<div class="address"><p>&nbsp;</p><h3>'.esc_html(__("Dati fattura", 'getyourbill')).'</h3>';

        if( !empty($this->s_typeaccount) && $this->s_typeaccount != "nessuno" ){
            $out__html .= '<p><strong>'.esc_html(__("Tipo account", 'getyourbill')).': </strong>'.@$this->getyourbillfieldstypeaccounts[$this->s_typeaccount].' </p>';
        }


        $missing_info = true;
        foreach ( $this->order_field_precompilati as $key => $field ) {
            if( $key == "billing_user_type"){
                continue;
            }
            if( trim($field["value"]) != "" ){
                $missing_info = false;
                $out__html .= '<p '.(( empty($this->s_typeaccount) || $this->s_typeaccount == "nessuno" )?'style="display: none;"':"").' id="view_p'.$field["id"].'" class="field_'.$field["id"].'_view _transaction_id_field"><strong>'.$field["label"].': </strong> '.$field["value"].'</p>';
            }
        }
        if( $missing_info ){
            $out__html .= '<div><h5 style="margin-bottom: 15px;">'.esc_html(__("Dati fattura incompleti", 'getyourbill')).'</h5></div>';
        }
        $out__html .= '</div>';
        $getyourbill_invoice = ( !is_null($order_id) )?get_post_meta( $order_id, "getyourbill_invoice", true ):"";
        $getyourbill_send_ok_f_json = ( !is_null($order_id) )?get_post_meta( $order_id, "getyourbill_send_ok_f_json", true ):"";
        $getyourbill_iniquenumberinvoice = ( !is_null($order_id) )?get_post_meta( $order_id, "getyourbill_iniquenumberinvoice", true ):"";
        $getyourbill_recoverCode = ( !is_null($order_id) )?get_post_meta( $order_id, "getyourbill_recoverCode", true ):"";
        $getyourbill_is_send_sdi = ( !is_null($order_id) )?get_post_meta( $order_id, "getyourbill_is_send_sdi", true ):"";
        $missing_info = true;
        $out__html .= '<div><p>&nbsp;</p><h3 style="margin-bottom: 15px;">'.esc_html(__("Dati fattura", 'getyourbill')).'</h3>';
        $out__html .= '<div class="address">';
        if( !empty($getyourbill_invoice) ){
            $missing_info = false;
            $out__html .= '<p class="none_set"><strong>'.esc_html(__("Numero fattura", 'getyourbill')).': </strong> '.$getyourbill_invoice.'</p>';
        }
        if( !empty($getyourbill_iniquenumberinvoice) ){
            $missing_info = false;
            $out__html .= '<p class="none_set"><strong>'.esc_html(__("Numero univoco", 'getyourbill')).': </strong> '.$getyourbill_iniquenumberinvoice.'</p>';
        }
        if( !empty($getyourbill_recoverCode) ){
            $missing_info = false;
            $out__html .= '<p class="none_set"><strong>'.esc_html( GetYourBill_altro["order_label_recoveri"] ).': </strong> '.$getyourbill_recoverCode.'</p>';
        }
        if( $missing_info ){
            $out__html .= '<div><h5 style="margin-bottom: 15px;">'.esc_html(__("Dati fattura incompleti", 'getyourbill')).'</h5></div>';
        }
        $out__html .= '</div>';
        $out__html .= '<div class="edit_address">
					<p class="form-field _billing_billing_pecaddress_field _transaction_id_field">
					<label for="_billing_billing_pecaddress">'.esc_html(__("Numero fattura", 'getyourbill')).'</label><input type="text" class="short first" style="" name="getyourbill_invoice" value="'.$getyourbill_invoice.'" placeholder="'.esc_html(__("Numero fattura", 'getyourbill')).'"> </p>
					<p>'.esc_html(__("Aggiorna numero fattura", 'getyourbill')).'</p><p>&nbsp;</p></div>';
        $out__html .= '</div>';

        if( $print_or)
		    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
            echo $out__html;
        else
            return $out__html;
    }

    public function is_request_by_config_request_all(){
        $out = $this->getyourbillcontroout;
        unset($out["nessuno"]);
        $out_desc = array();
        $out_validate = array();
        $mess_if_se = array();
        $mess_if_desc = array();
        foreach($out as $key => $type_v) {
            $control_by = self::get_info("config_request_".$key);
            $type = $key;
            if ( !is_null( $control_by ) && $control_by != "oprionals"){
                $mod = explode("__", $control_by);
                if( count($mod) ){
                    foreach($out[$type] as $index => $path) {
                        $is_req = in_array($index, $mod);
                        $is_req_if = in_array($index."_if", $mod);
                        $is_opz = in_array($index."_opz", $mod);
                        if ( $is_opz ) {
                            $out[$type][$index] = "opz";
                            $out_validate[$type][$index] = false;
                        }else if ( $is_req_if && !$is_req ) {
                            $out[$type][$index] = "se";
                            $out_validate[$type][$index] = false;
                            $mess_if_desc[] = $this->getyourbillfieldsoptions[$index];
                            $mess_if_se[] = array("type" => $type, "index" => $index);
                        }else if( $is_req && !$is_req_if){
                            $out[$type][$index] = true;
                            $out_validate[$type][$index] = true;
                            $out_desc[$type][$index] = $this->getyourbillfieldsdesc_senza_testo;
                        }else{
                            $out[$type][$index] = false;
                            $out_validate[$type][$index] = false;
                            $out_desc[$type][$index] = $this->getyourbillfieldsdesc_opzionale;
                        }
                    }
                }
            }else if( $control_by === "oprionals" ){
                foreach($out[$type] as $index => $path) {
                    if( in_array( $index, $this->getyourbillcontrooutsort[$type]) ){
                        $out[$type][$index] = true;
                        $out_validate[$type][$index] = false;
                        $out_desc[$type][$index] = $this->getyourbillfieldsdesc_opzionale;
                    }
                }
            }
            if( $type == "pcompany" || $type == "pperson" || $type == "pa" ){
                $out[$type]["billing_company"] = true;
                $out_validate[$type]["billing_company"] = true;
                $out_desc[$type]["billing_company"] = $this->getyourbillfieldsdesc_senza_testo;
            }else{
                $out[$type]["billing_company"] = false;
                $out_validate[$type]["billing_company"] = false;
                $out_desc[$type]["billing_company"] = $this->getyourbillfieldsdesc_opzionale;
            }
            if( false ){
                $out[$type]["billing_getyourbillid"] = true;
                $out_validate[$type]["billing_getyourbillid"] = true;
                $out_desc[$type]["billing_getyourbillid"] = $this->getyourbillfieldsdesc_senza_testo;
            }else{
                $out[$type]["billing_getyourbillid"] = self::is_view_by_config("billing_getyourbillid_".$type);
                $out_validate[$type]["billing_getyourbillid"] = false;
                $out_desc[$type]["billing_getyourbillid"] = $this->getyourbillfieldsdesc_opzionale;
            }
        }
        $mess_if_desc = array_unique($mess_if_desc);
        foreach($mess_if_se as $type_v) {
            $out_desc[$type_v["type"]][$type_v["index"]] = $this->getyourbill_get_getyourbillfieldsoptions(implode(" ".GetYourBill_altro["order_o"]." ", $mess_if_desc));
        }
        return array($out, $out_desc, $out_validate);
    }

    public function is_request_by_config_request( $data = null ){

        // wc_settings_getyourbill_settings_config_request

        if( is_null($data) ){
            $this->s_typeaccount = $this->get_type_user_account();
            $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );
        }

        $out = $this->getyourbillcontroout;


        $control_by = $data["data"];
        $type = $data["type"];
        if ( !is_null( $control_by ) && $control_by != "oprionals"){

            $mod = explode("__", $control_by);
            if( count($mod) ){
                foreach($out[$type] as $index => $path) {
                    $is_req = in_array($index, $mod);
                    $is_req_if = in_array($index."_if", $mod);
                    if ( $is_req_if && !$is_req ) {
                        $out[$type][$index] = "se";
                    }else if( $is_req && !$is_req_if){
                        $out[$type][$index] = true;
                    }else{
                        $out[$type][$index] = false;
                    }
                }
            }

        }else if ( !is_null( $control_by ) && $control_by == "oprionals"){
            foreach($out[$type] as $index => $path) {
                $out[$type][$index] = false;
            }
        }

        return isset( $out[ $type ] ) ? $out[ $type ] : array();
    }

    public static function get_info( $info = '' ){
        return get_option( "wc_settings_getyourbill_settings_".$info , null );
    }

    public static function set_info( $info = '', $val = '' ) {
        return update_option( "wc_settings_getyourbill_settings_".$info , $val );
    }

    public static function delete_info( $info = ''  ) {
        return delete_option( "wc_settings_getyourbill_settings_".$info );
    }

    public function get_typeinvoice_by_config($account = "", $doctype = null){

        $typeinvoice = self::get_info("config_typeinvoice_".$account);
        if( $account != "psoleproprietor" ){
            $typeinvoice_ = $this->getyourbilltypeinvoice["i_61"]; // forzo la fattura eletronica
            return (!is_null($doctype))?$typeinvoice:$typeinvoice_["doctype"];
        }
        if( array_key_exists($typeinvoice, $this->getyourbilltypeinvoice) ){
            $typeinvoice_ = $this->getyourbilltypeinvoice[$typeinvoice];
            return (!is_null($doctype))?$typeinvoice:$typeinvoice_["doctype"];
        }
        return (!is_null($doctype))?"i_not":"0";
    }

    public static function is_checked_by_name(){
        return self::get_info("config_getyourbillinputsreg");
    }

    public static function is_checked_by_config($key = ""){
        return self::get_info("config_".$key) == "yes";
    }

    public static function is_request_by_config($key = ""){
        return self::get_info("config_".$key) == "yes";
    }

    public static function is_view_by_config($key = ""){
        $r = self::get_info("config_view_".$key);
        return $r == "yes";
    }

    public static function is_pec( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        return filter_var($key, FILTER_VALIDATE_EMAIL);
    }

    public static function is_cf( $key = '' , $pi = false, $type = "psoleproprietor"){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        $country = self::getyourbill_get_country();
        if( $country === 'IT' ){
            if( $type == "pa" )
                return (strlen($key) == 11);
            return (strlen($key) == 16 || strlen($key) == 11 );
        }
        return true;
    }

    public static function is_pi( $key = ''){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        $country = self::getyourbill_get_country();
        if( $country === 'IT' ){
            return (strlen($key) == 11);
        }else{
            return ( wc_clean($key) !== "" );
        }
    }

    public static function is_ipa( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        return (strlen($key) == 6);
    }

    public static function is_pa( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        return (strlen($key) == 6);
    }

    public static function is_gid( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[0-9]+\$/D",wc_clean($key)) )
            return false;
        return (strlen($key) == 8);
    }

    public static function is_cup( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        return (strlen($key) <= 15);
    }

    public static function is_cig( $key = '' ){
        if( wc_clean($key) == "" )
            return false;
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        return (strlen($key) <= 15);
    }

    public static function is_sdi( $key = '' ){
        $country = self::getyourbill_get_country();
        if( !preg_match("/^[A-Za-z0-9]+\$/D", wc_clean($key)) )
            return false;
        if( $country === 'IT' ){
            return (strlen($key) == 7);
        }else{
            return ( wc_clean($key) !== "" );
        }
    }

    function get_content_messaggio($errors, $messaggio, $status = "completed gyb_completed", $dataid = null){
        if( is_object($errors) ){
            $errors->add( 'required-field', $messaggio , array( 'id' => $dataid ) );
        }else if( $errors === "notice"){
            printf( '<div class="%1$s"%2$s><p>%3$s</p></div>', 'notice notice-error', ( ! is_null( $dataid ) ) ? ' data-id="' . esc_attr( $dataid ) . '"' : '', esc_html( $messaggio ) );
        }else if( $errors === "column"){
            echo '<mark class="order-status status-' . esc_attr( $status ) . ' tips" data-tip="' . esc_attr( $messaggio ) . '"><span>' . esc_html( $messaggio ) . '</span></mark>';
        }else if( $errors === "empty"){
        }else if( $errors === "JSON"){
        }else{
            echo '<div class="updated notice error"><p>' . esc_html( $messaggio ) . '</p></div>';
        }
        return array("input" => $dataid , "mess" => $messaggio);
    }

    public function validation_global($fields, $errors, $is_field = false, $s_typeaccount_get = null, $country = null, $order_id = null ){

        $post_data = $this->get_post_data_request();
        $output = true;
        $inputonregiter = self::is_checked_by_name();
        if( is_null($country) ){
            $country = self::getyourbill_get_country();
        }
        $status = "on-hold gyb_hold";
        $erros_out = array();
        $woocommerce_myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
        $register = ( isset($post_data["getyourbill_reg_page_id"]) &&  $post_data["getyourbill_reg_page_id"] == $woocommerce_myaccount_page_id );

        if( !$register ){
            if( isset($post_data["getyourbill_reg_page"]) ){
                $register = true;
            }
        }

        if( $register && $inputonregiter == "nessuno" ){
            if( $errors == "JSON")
                return $erros_out;
            return false;
        }

        $this->s_typeaccount = ( is_null($s_typeaccount_get) )?(( isset($post_data["billing_user_type"]) )?$post_data["billing_user_type"]:self::get_info("accountdefault")):$s_typeaccount_get;
        $is_filtred_first_last = apply_filters( 'getyourbill_registrazione_required_first_last_name', $fields );

        if( $this->s_typeaccount === 'pa' && $country != "IT" ){
            $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_pa_country"] ), $this->getyourbillfieldslist['billing_user_type']["label"] );
            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, "failed gyb_failed", "billing_user_type");
            if( $errors == "JSON")
                return $erros_out;
            return false;
        }



        if( is_null($is_filtred_first_last) || (!is_null($is_filtred_first_last) && $is_filtred_first_last ) ){
            if( $inputonregiter == "inputonregiter" || $inputonregiter == "inputonregiter_namesurname" ){
                if( !array_key_exists($this->s_typeaccount, $this->getyourbillfieldstypeaccounts) ){
                    $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_request"] ), $this->getyourbillfieldslist['billing_user_type']["label"] );
                    $erros_out[] = $this->get_content_messaggio($errors, $messaggio, "failed gyb_failed", "billing_user_type");
                    if( $errors == "JSON")
                        return $erros_out;
                    return false;
                }
            }

            if( $register ){
                if( $inputonregiter == "namesurname" || $inputonregiter == "inputonregiter_namesurname" ){
                    foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                        if( $key == "billing_company" ){
                            if( $this->s_typeaccount == "pcompany" || $this->s_typeaccount == "pperson" || $this->s_typeaccount == "pa" ){
                                $field_data["required"] = true;
                            }else if( $this->s_typeaccount == "psoleproprietor" ){
                                $field_data["required"] = false;
                            }
                        }
                        if( !$field_data["required"] ){
                            continue;
                        }
                        $field = wc_clean( sanitize_text_field(wc_clean( ((isset($post_data[$key]))?$post_data[$key]:"")) ));
                        if( $field == "" ){
                            $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Error["validation_input_request"] ), $field_data["label"] );
                            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status);
                            $output = false;
                        }
                    }
                }else if( $inputonregiter == "inputonregiter" ){
                    foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                        if( $key == "billing_first_name" || $key == "billing_last_name" ){
                            continue;
                        }
                        if( $key == "billing_company" ){
                            if( $this->s_typeaccount == "pcompany" || $this->s_typeaccount == "pperson" || $this->s_typeaccount == "pa"){
                                $field_data["required"] = true;
                            }else if( $this->s_typeaccount == "psoleproprietor" ){
                                $field_data["required"] = false;
                            }
                        }
                        if( !$field_data["required"] ){
                            continue;
                        }
                        $field = wc_clean( sanitize_text_field(wc_clean( ((isset($post_data[$key]))?$post_data[$key]:"")) ));
                        if( $field == "" ){
                            $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Error["validation_input_request"] ), $field_data["label"] );
                            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status);
                            $output = false;
                        }
                    }
                }
            }else{
                foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                    if( $key == "billing_company" ){
                        if( $this->s_typeaccount == "pcompany" || $this->s_typeaccount == "pperson" || $this->s_typeaccount == "pa"){
                            $field_data["required"] = true;
                        }else if( $this->s_typeaccount == "psoleproprietor" ){
                            $field_data["required"] = false;
                        }
                        if( !$field_data["required"] ){
                            continue;
                        }
                        if( $is_field && !is_null($order_id ) ){
                            $field = wc_clean(  get_post_meta( $order_id, "_".$key, true ) );
                        }else{
                            $field = wc_clean( sanitize_text_field(wc_clean( ((isset($post_data[$key]))?$post_data[$key]:"")) ));
                        }

                        if( $field == "" ){
                            $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Error["validation_input_request"] ), $field_data["label"] );
                            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status);
                            $output = false;
                        }
                    }
                }
            }

            if( $register && ( $inputonregiter != "inputonregiter" && $inputonregiter != "inputonregiter_namesurname" ) ){
                if( $errors == "JSON")
                    return $erros_out;
                return false;
            }
        }
        $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );

        $request_filds = $this->is_request_by_config_request($data);
        $request_count = 0;
        if( is_array($request_filds) ){
            foreach ( $request_filds as $key => $val ) {
                if( $val === "se" ){
                    $request_count++;
                }
            }
        }
        $se_request = array();
        if( array_key_exists("billing_user_type", $fields) ){
            unset($fields["billing_user_type"]);
        }


        foreach ( $fields as $key => $field_args ) {
            $field_args_edit = $this->set_config_request_by_type_account($key, $field_args, $request_filds, $this->s_typeaccount);

            if( $register && !$field_args_edit["view_registre"] ){
                continue;
            }
            if( $is_field ){
                $field = wc_clean( sanitize_text_field(wc_clean( ( (array_key_exists("value", $field_args_edit))?$field_args_edit["value"]:"") ) ));
            }else{
                $field = wc_clean( sanitize_text_field(wc_clean( ((isset($post_data[$key]))?$post_data[$key]:"")) ));
            }
            $field = trim($field);
            if(  array_key_exists("important", $field_args_edit) && $field_args_edit["important"] === true ){
                if ( ($request_filds[$key] === true || $field_args_edit["required"] === true) && array_key_exists("mod", $field_args_edit) ) {
                    if( $field != "" && !call_user_func("self::is_".$field_args_edit['mod'], $field, $this->s_typeaccount == "pcompany", $this->s_typeaccount )){
                        $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_not_valid_format"] ), $field_args["label"] );
                        $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                        $output = false;
                    }
                }
                if( $request_filds[$key] === "se" ){
                    $se_request[$key] = $field;
                }else if( $request_filds[$key] === true || $field_args_edit["required"] === true ){
                    if( trim($field) == "" ){
                        $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Error["validation_input_request"] ), $field_args["label"] );
                        $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                        $output = false;
                    }
                }else if( array_key_exists("validatemode", $field_args_edit) && in_array($this->s_typeaccount, $field_args_edit["validatemode"]) ){
                    if( $field != "" && !call_user_func("self::is_".$field_args_edit['mod'], $field, $this->s_typeaccount == "pcompany", $this->s_typeaccount )){
                        $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_not_valid_format"] ), $field_args["label"] );
                        $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                        $output = false;
                    }
                }
            }else if(  array_key_exists("important", $field_args_edit) && $field_args_edit["important"] === false && array_key_exists("validatemode", $field_args_edit) && in_array($this->s_typeaccount, $field_args_edit["validatemode"]) ){

                if( $field != "" && !call_user_func("self::is_".$field_args_edit['mod'], $field, $this->s_typeaccount == "pcompany", $this->s_typeaccount )){
                    $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_not_valid_format"] ), $field_args["label"] );
                    $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                    $output = false;
                }
            }else {
                if( $field_args_edit["required"] === true ){
                    if ( array_key_exists("mod", $field_args_edit) ) {
                        if(  $field != "" && !call_user_func("self::is_".$field_args_edit['mod'], $field, $this->s_typeaccount == "pcompany", $this->s_typeaccount )){
                            $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_not_valid_format"] ), $field_args["label"] );
                            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                            $output = false;
                        }
                    }
                    if( $field == "" ){
                        $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Error["validation_input_request"] ), $field_args["label"] );
                        $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                        $output = false;
                    }
                }
            }
        }
        if( $request_count > 0 && count($se_request) == $request_count ){
            $is_val = true;
            foreach ( $se_request as $k => $f ) {
                if( $f != "" ){
                    $is_val = false;
                }
            }
            if( $is_val ){
                $campi = $this->getyourbillfieldsoptions[self::get_info("config_request_".$this->s_typeaccount)];
                $messaggio =  sprintf( esc_html(GetYourBill_Messaggi_Warning["validation_input_is_reques_compilati"] ), $campi );
                $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
                $output = false;
            }
        }else if( $request_count>0 && count($se_request) != $request_count){
            $messaggio = esc_html(GetYourBill_Messaggi_Error["validation_error_request"]);
            $erros_out[] = $this->get_content_messaggio($errors, $messaggio, $status, $key);
            $output = false;
        }

        //$erros_out[] = $this->get_content_messaggio($errors, "stop ", "200", "cf");


        if( $errors == "JSON" )
            return $erros_out;
        return $output;
    }

    public static function getyourbill_get_country(){
        $post_data = self::get_post_data_request();
        $count =  ( isset($post_data['_billing_country']) && ($post_data['_billing_country']) == "" )?$post_data["_billing_country"]:"";
        return ( !isset($post_data['billing_country']) || ( isset($post_data['billing_country']) && ($post_data['billing_country']) == "" ) )?$count:$post_data["billing_country"];
    }

    public function getyourbill_validate_user_profile_update_errors($errors, $update = null, $user  = null)
    {
        $post_data = $this->get_post_data_request();
        if( isset($post_data["action"]) && $post_data["action"] == "createuser" ){
            return;
        }
        $fields = $this->getyourbill_get_account_fields();
        $this->validation_global($fields, $errors);
    }


    public function getyourbill_register_validation( $username, $email, $errors ){
        $inputonregiter = self::is_checked_by_name();
        if( $inputonregiter != "nessuno" ){
            $fields = $this->getyourbill_get_account_fields();
            $this->validation_global($fields, $errors);
        }
    }

    public function getyourbill_after_checkout_validationa( $fields_wc, $errors ){
        $country = self::getyourbill_get_country();
        $fields = $this->getyourbill_get_account_fields(true);
        $this->validation_global($fields, $errors, false, null, $country);
    }

    public function getyourbill_process_shop_order_meta ( $order_id ){
        $post_data = $this->get_post_data_request();
        $fields = $this->getyourbill_get_account_fields(true);
        foreach ( $fields as $key => $field_args ) {
            if ( isset($post_data[ $key ])   ) {
                $field = wc_clean( sanitize_text_field(wc_clean($post_data[ $key ]) ));
                update_post_meta( $order_id, $key, $field );
            }
        }
        foreach ( $this->data_refer as $key => $field_args ) {
            if ( isset($post_data["autof_".$key ])   ) {
                $field = wc_clean( sanitize_text_field(wc_clean($post_data[ "autof_".$key ]) ));
                update_post_meta( $order_id, $field_args["name"], $field );
                if( isset($post_data["billing_update_info_on_checkout"]) ){
                    if( !is_null($this->user_id) ){
                        if( $key == "id" ){
                            update_user_meta( $this->user_id, $field_args["name"], $field );
                        }
                    }
                }
            }
        }
        if( isset($post_data["billing_update_info_on_checkout"]) ){
            update_post_meta( $order_id, "billing_update_info_on_checkout", "yes" );
        }
        if ( isset($post_data["getyourbill_auto"]) ){
            update_post_meta( $order_id, "getyourbill_auto", "auto" );
        }
    }

    public function getyourbill_print_user_frontend_fields($form ) {
        $post_data = $this->get_post_data_request();
        $inputonregiter = self::is_checked_by_name();
        $register = ( !is_user_logged_in() );
        echo '<input type="hidden" name="getyourbill_inputonregiter" value="' . esc_attr( $inputonregiter ) . '">';
        if( $register && function_exists("get_the_ID") ){
            echo '<input type="hidden" name="getyourbill_reg_page_id" value="' . esc_attr( get_the_ID() ) . '">';
        }else{
            echo '<div id="get_order_fields"><p>&nbsp;</p><h3>' . esc_html( GetYourBill_altro["order_h3"] ) . '</h3>';
        }
        if( $register && $inputonregiter == "nessuno" ){
            return;
        }
        if( $register && $inputonregiter == "namesurname" ){
            foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                woocommerce_form_field( $key, $field_data, (( isset($post_data[ $key ]) )?wc_clean( $post_data[ $key ] ):"") );
            }
        }
        if( $register && ( $inputonregiter != "inputonregiter" && $inputonregiter != "inputonregiter_namesurname" ) ){
            return;
        }
        $fields = $this->getyourbill_get_account_fields();
        if( !$register && !is_null($this->user_id) ){
            include_once(GetYourBillPATH."template/autocomplete_input.php");
        }
        foreach ( $fields as $key => $field_args ) {
            if( $register && !$field_args["view_registre"] ){
                continue;
            }

            if( $field_args["required_checkout"]  && $key !== "billing_user_type"){
                add_filter( 'esc_html', array( $this, 'getyourbill_edit_option_value'), 10, 2 );
            }
            if( $key == "billing_user_type" && $field_args['value'] === "" ){
                $field_args['value'] = self::get_info("accountdefault");
            }
            if( !$register && $field_args['value'] != "" && $key == 'billing_getyourbillid' ){
                $field_args['custom_attributes'] = array( 'disabled' => 'disabled' );
            }
            $field_args['description'] = "";
            woocommerce_form_field( $key, $field_args, $field_args['value'] );
            if( $field_args["required_checkout"] && $key !== "billing_user_type" ){
                remove_filter( 'esc_html', array( $this, 'getyourbill_edit_option_value'), 10, 2 );
            }

            if( $register && $key == "billing_user_type" && ($inputonregiter == "namesurname" || $inputonregiter == "inputonregiter_namesurname") ){
                foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                    if( $inputonregiter == "inputonregiter" && ( $key == "billing_first_name" || $key == "billing_last_name" ) ){
                        continue;
                    }
                    woocommerce_form_field( $key, $field_data, (( isset($post_data[ $key ]) )?wc_clean( $post_data[ $key ] ):"") );
                }
            }else if( $register && $key == "billing_user_type" && $inputonregiter == "inputonregiter" ){
                foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                    if( $key == "billing_first_name" || $key == "billing_last_name" ){
                        continue;
                    }
                    woocommerce_form_field( $key, $field_data, (( isset($post_data[ $key ]) )?wc_clean( $post_data[ $key ] ):"") );
                }
            }

        }
    }



    public function getyourbill_checkout_fields_hidden( $checkout_fields ) {

        if(  !is_null($this->user_id) ){
            $billing_id_anagrafica = get_the_author_meta( "s", $this->user_id );
            if( !empty($billing_id_anagrafica) ){
                echo '<input type="hidden" class="gyb_autof_input" name="autof_id" value="' . esc_attr( $billing_id_anagrafica ) . '">';
            }
        }
    }
    public function getyourbill_checkout_fields_position( $checkout_fields ) {
        $fields = $this->getyourbill_get_account_fields(true);
        $checkout = WC()->checkout();
        $country = self::getyourbill_get_country();
        foreach ( $fields as $key => $field_args ) {
            if( $field_args["required_checkout"] && $key !== "billing_user_type"){
                add_filter( 'esc_html', array( $this, 'getyourbill_edit_option_value'), 10, 2 );
            }
            if( empty($checkout->get_value($key)) && $key == "billing_user_type" && $field_args['value'] === "" ){
                $field_args['value'] = self::get_info("accountdefault");
            }else{
                $field_args['value'] = $checkout->get_value($key);
            }
            $field_args['description'] = "";
            $field = apply_filters( 'woocommerce_form_field_' . $key, $field_args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce filter.
            if( $field_args["required_checkout"] && $key !== "billing_user_type"){
                remove_filter( 'esc_html', array( $this, 'getyourbill_edit_option_value'), 10, 2 );
            }
            if( $key !== "billing_user_type"){
                $field['required'] = false;
            }
            $checkout_fields["billing"][$key] = $field;
            if( $key == "billing_user_type" ){
                if( !is_null( $this->user_id ) ){
                    $checkout_fields["billing"]['billing_update_info_on_checkout'] = array(
                        'type' => 'checkbox',
                        'label' => esc_html(GetYourBill_altro["saveinfoofaccount"] ),
                        'required_checkout' => false,
                        'required' => false,
                        'view_registre' => true,
                        'priority' => 1,
                        'default' => ( $this->getyourbill_is_request_empty($this->user_id) )?"1":"",
                        'class' => array("woocommerce-form__input", "woocommerce-form__input-checkbox", "input-checkbox", "billing_update_info_on_checkout"),
                        'autocomplete' => false,
                    );
                }
            }
            if( $country != "IT" && $key == "billing_cf"){
                $field['required'] = false;
                $field['important'] = false;
            }
        }
        return $checkout_fields;
    }


    public function add_settings_page( $pages ) {
        $pages[ $this->id ] = $this->label;
        return $pages;
    }



    public function get_sections() {

        $sections = array(
            '' => esc_html(GetYourBill_altro["tab_gyb_connessione"]),
            'config' => esc_html(GetYourBill_altro["tab_gyb_config"]),
            'bugs' => esc_html(GetYourBill_altro["tab_gyb_bugs"]),
            'payments' => esc_html(GetYourBill_altro["tab_gyb_payments"]),
            'aliquote' => esc_html(GetYourBill_altro["tab_gyb_aliquote"]),
            'cron' => esc_html(GetYourBill_altro["tab_gyb_cron"]),
            'info' => GetYourBill_altro["tab_gyb_info"],
        );

        return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce filter.
    }



    public function output_sections() {
        global $current_section;

        $sections = $this->get_sections();
        if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
            return;
        }

        echo '<ul class="subsubsub">';
        $array_keys = array_keys( $sections );
        foreach ( $sections as $id => $label ) {
            echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . esc_attr( $current_section == $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
        }
        echo '</ul><br class="clear" />';
    }

    public function registre_method_pre_pauments_gateways(){
        foreach ( $this->getyourbill_pre_payments as $key => $pay ) {
            if( is_null( self::get_info('payments_'.$key) ) ){
                self::set_info('payments_'.$key, $pay);
            }
        }
    }

    public function get_settings( ) {
        global $current_section, $hide_save_button;
        $settings = array();
        if(file_exists(GetYourBillPATH."inc/tabs/".$current_section.".php")){
            require(GetYourBillPATH."inc/tabs/".$current_section.".php");
        }else if(file_exists(GetYourBillPATH."inc/tabs/general.php")){
            require(GetYourBillPATH."inc/tabs/general.php");
        }
        return  $settings;
    }

    public static function print_info($text = "", $error = false){
        echo '<tr valign="top"><td class="info" colspan="2" style="padding: 0px; padding-bottom: 35px;"><p class="description" ' . ( ( $error ) ? "style='color:red'" : "" ) . '>' . wp_kses_post( $text ) . '</p></td></tr>';
    }

    public function output() {
        global $current_section, $hide_save_button;
        if( 'info' === $current_section || 'bugs' === $current_section || 'aliquote' === $current_section ) {
            $hide_save_button = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce settings global.
        }
        $settings = $this->get_settings( $current_section );
        foreach($settings as $input ) {
            if( empty($input['html']) ){
                WC_Admin_Settings::output_fields( array($input) );
            }
            if( !empty($input['errorgyb']) && $input['errorgyb'] != "" ){
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw PDF bytes are intentionally streamed to the browser
                echo self::print_info($input['errorgyb'], true);
            }
            if( !empty($input['descrizione']) && $input['descrizione'] != "" ){
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw PDF bytes are intentionally streamed to the browser.
                echo self::print_info($input['descrizione'] );
            }
            if( !empty($input['html']) ){
                $hide_save_button = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce settings global.
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw PDF bytes are intentionally streamed to the browser.
                echo $input['html'];
            }
            if( !empty($input['sample_html']) ){
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for inline script, safely encoded with wp_json_encode().
                echo $input['sample_html'];
            }
        }
    }

    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        WC_Admin_Settings::save_fields( $settings );
        $live_api_key = $this->get_request_text( 'wc_settings_getyourbill_settings_apikey', INPUT_POST );
        $test_api_key = $this->get_request_text( 'wc_settings_getyourbill_settings_apikey_test', INPUT_POST );
        if( '' !== $live_api_key || '' !== $test_api_key ){
            $api = new \models\APIGetyourbill();


            // live mode


            if( '' !== trim( $live_api_key ) ){
                //  $key = \models\GetyourbillApp::get_info("apikey");
                if( false && $key === trim($live_api_key) ){

                }else{
                    self::delete_info("access_token");
                    self::delete_info("getyourbill_code");
                    self::delete_info("getyourbill_license");
                    self::delete_info("getyourbill_locationId");
                    self::delete_info("getyourbill_supportID");
                    self::set_info("isokkey", "success");
                    if( '' !== trim($live_api_key) && $api->veriffica_access_token(trim($live_api_key), "live") ){
                        \models\GetyourbillApp::set_log( esc_html(GetYourBill_Messaggi_Success["validation_api_key_succes"] ) );
                        self::set_info("isokkey", "success");
                        $info = $api->get_info_getyourbill();
                        $getyourbill_supportID = $api->get_support_key($live_api_key);
                        if( !is_null($info) ){
                            self::set_info("getyourbill_code", $info["code"]);
                            self::set_info("getyourbill_license", $info["license"]);
                            self::set_info("getyourbill_locationId", $info["locationId"]);
                        }
                        self::delete_info("access_token");
                        self::set_info("getyourbill_supportID", (is_null($getyourbill_supportID))?"errore":$getyourbill_supportID);
                    }else{
                        \models\GetyourbillApp::set_log( esc_html(GetYourBill_Messaggi_Warning["missing_api_key"] ) );
                        self::delete_info("access_token");
                        self::set_info("isokkey", "errore");
                    }
                }
            }else{
                self::delete_info("access_token");
                self::delete_info("getyourbill_code");
                self::delete_info("getyourbill_license");
                self::delete_info("getyourbill_locationId");
                self::delete_info("getyourbill_supportID");
                self::set_info("isokkey", "success");
            }

            // test mode
            if( '' !== trim($test_api_key) ){

                //$key = \models\GetyourbillApp::get_info("apikey_test");
                if( false && $key === trim($test_api_key) ){

                }else{
                    self::delete_info("access_token_test");
                    self::delete_info("getyourbill_code_test");
                    self::delete_info("getyourbill_license_test");
                    self::delete_info("getyourbill_locationId_test");
                    self::delete_info("getyourbill_supportID_test");
                    self::set_info("isokkey_test", "success");
                    if( '' !== trim($test_api_key) && $api->veriffica_access_token(trim($test_api_key), "test") ){
                        \models\GetyourbillApp::set_log( esc_html(GetYourBill_Messaggi_Success["validation_api_key_succes_test"] ) );
                        self::set_info("isokkey_test", "success");
                        $info_test = $api->get_info_getyourbill();
                        $getyourbill_supportID = $api->get_support_key($test_api_key);
                        if( !is_null($info_test) ){
                            self::set_info("getyourbill_code_test", $info_test["code"]);
                            self::set_info("getyourbill_license_test", $info_test["license"]);
                            self::set_info("getyourbill_locationId_test", $info_test["locationId"]);
                        }
                        self::set_info("getyourbill_supportID_test", (is_null($getyourbill_supportID))?"errore":$getyourbill_supportID);
                    }else{
                        \models\GetyourbillApp::set_log( esc_html ( GetYourBill_Messaggi_Warning["missing_api_key_test"] ) );
                        self::delete_info("access_token_test");
                        self::set_info("isokkey_test", "errore");
                    }
                }
            }else{
                self::delete_info("access_token_test");
                self::delete_info("getyourbill_code_test");
                self::delete_info("getyourbill_license_test");
                self::delete_info("getyourbill_locationId_test");
                self::delete_info("getyourbill_supportID_test");
                self::set_info("isokkey_test", "success");
            }




        }else if( '' !== $this->get_request_text( "wc_settings_getyourbill_settings_config_bugautosend", INPUT_POST ) ){

        }
    }


    public function getyourbill_get_account_fields($aallfields = false, $user_id = null, $setval = true ) {
        $post_data = $this->get_post_data_request();
        if( is_null($user_id) && is_user_logged_in() ){
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        $fields = $this->getyourbillfieldslist;
        $field_out = array();
        foreach ( $fields as $key => $field_args ) {
            if( !$aallfields ){
                if( isset($field_args["isinputbybglobal"]) && $field_args["isinputbybglobal"] === true ){
                    $field_out[$key] = $field_args;
                    if( $setval ){
                        $field_out[$key]["value"] = (isset($post_data[$key])?$post_data[$key]:((!is_null($user_id))?get_the_author_meta( $key, $user_id ):""));
                    }
                }
            }else{
                $field_out[$key] = $field_args;
                if( $setval ){
                    $field_out[$key]["value"] = (isset($post_data[$key])?$post_data[$key]:((!is_null($user_id))?get_the_author_meta( $key, $user_id ):""));
                }
            }
        }
        return apply_filters( 'getyourbill_account_fields', $field_out );
    }



    public function getyourbill_is_userdata( $key ) {
        return array_key_exists($key, $this->getyourbillfieldslist);
    }


    public function getyourbill_save_account_fields( $customer_id ) {
        $post_data = $this->get_post_data_request();
        $inputonregiter = self::is_checked_by_name();

        $register = ( !is_user_logged_in() );
        $fields = $this->getyourbill_get_account_fields();
        $sanitized_data = array();
        if( $register && ( $inputonregiter == "inputonregiter" || $inputonregiter == "inputonregiter_namesurname" ) ){
            $this->s_typeaccount = ( isset($post_data["billing_user_type"]) && array_key_exists(wc_clean($post_data["billing_user_type"]), $this->getyourbillfieldstypeaccounts) )?wc_clean($post_data["billing_user_type"]):self::get_info("accountdefault");
        }else if( $register && ( $inputonregiter !== "inputonregiter" && $inputonregiter != "inputonregiter_namesurname") ){
            $this->s_typeaccount = self::get_info("accountdefault");
            $post_data["billing_user_type"] = $this->s_typeaccount;
        }else{
            $this->s_typeaccount = ( isset($post_data["billing_user_type"]) && array_key_exists(wc_clean($post_data["billing_user_type"]), $this->getyourbillfieldstypeaccounts) )?wc_clean($post_data["billing_user_type"]):$this->get_type_user_account($customer_id);
        }
        $data = array( "type" => $this->s_typeaccount, "data" => self::get_info("config_request_".$this->s_typeaccount) );
        $request = $this->is_request_by_config_request($data);
        foreach ( $fields as $key => $field_args ) {
            if( isset( $post_data[ $key ] ) ){
                $value = wc_clean( $post_data[ $key ] );
                if( $key == "billing_user_type" ){
                    update_user_meta( $customer_id, $key, $value );
                    $sanitized_data[$key] = $value;
                }else{
                    if( $value != "" || (array_key_exists($key, $request) && ($this->request[$key] === true || $this->request[$key] === "se")) ){
                        update_user_meta( $customer_id, $key, $value );
                        $sanitized_data[$key] = $value;
                    }else{
                        update_user_meta( $customer_id, $key, "" );
                    }
                }
            }
        }

        if( $register ){
            if( $inputonregiter == "namesurname" || $inputonregiter == "inputonregiter_namesurname" ){
                foreach ( $this->getyourbill_namesurnamersociale as $key => $field_data ) {
                    if( isset( $post_data[ $key ] ) ){
                        $value = wc_clean( $post_data[ $key ] );
                        if( array_key_exists("extra_name", $field_data) ){
                            update_user_meta( $customer_id, $field_data["extra_name"], $value );
                        }
                        update_user_meta( $customer_id, $key, $value );
                    }
                }
            }
        }

        if( !is_null($this->user_id) ){
            foreach ( $this->data_refer as $key => $datar ) {
                if( isset( $post_data[ "autof_".$key ] ) ){
                    $value = wc_clean( $post_data[ "autof_".$key ] );
                    update_user_meta( $customer_id, $datar["name"], $value );
                }
            }
        }

        if ( ! empty( $sanitized_data ) ) {
            $sanitized_data['ID'] = $customer_id;
            wp_update_user( $sanitized_data );
        }
    }


    public function getyourbill_user_anagrafica( $billing_pi, $billing_cf, $billing_gyb_ID, $user_id = 0, $country = "IT" ){

        $info = array();
        $api = new \models\APIGetyourbill();
        $info = $api->get_anagrafiche( $billing_gyb_ID, $billing_pi, $billing_cf, $country);

        if( is_array($info) && count($info)>0 ){
            if( $info["id"] != "0" ){
                update_user_meta( $user_id, 'billing_id_anagrafica', $info["id"] );
            }else{
                update_user_meta( $user_id, 'billing_id_anagrafica', "" );
            }
        }
    }



    public function getyourbill_user_admin_fields($user) {

        $fields = $this->getyourbill_get_account_fields();

        ?>
        <h2><?php echo esc_html__( 'Getyourbill informazioni aggiuntive', 'getyourbill' ); ?></h2>
        <table class="form-table" id="getyourbill-additional-information">
            <tbody>

            <?php foreach ( $fields as $key => $field_args ) { ?>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field_args['label'] ); ?></label>
                    </th>
                    <td>
                        <?php
                        $field_args['label'] = false;
                        $field_args['class'][] = "regular-text";
                        ?>
                        <?php woocommerce_form_field( $key, $field_args, get_the_author_meta( $key, $user->ID ) ); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
    }


}
