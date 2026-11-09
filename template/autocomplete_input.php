<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( !defined('GetYourBill') ){
    exit();
}
global $GetYourBill;

?>
<p id="getyourbill_autocomlete_content_response_front"></p>
<div id="getyourbill_autocomlete" class="getyourbill_autocomlete" style="display: none;">
    <div class="getyourbill_overlay"></div>
    <div class="getyourbill_autocomlete_content">
        <span class="dashicons dashicons-dismiss" id="getyourbill_autocomlete_icon_close"></span>
        <div class="getyourbill_autocomlete_content_in">
            <p id="getyourbill_autocomlete_content_response"></p>
            <p class="form-row form-row form-row-wide" ><?php echo esc_html(__("Recupera automaticamente i tuoi dati anagrafici. Se presenti nei nostri archivi, precompileremo i campi.", 'getyourbill')); ?></p>


            <p class="form-row form-row form-row-wide select_billing_user_type" >
                <label class=""><?php echo esc_html(GetYourBill_altro["tipo_account"]); ?></label>
                <span class="woocommerce-input-wrapper">
					<?php
                    $fields_popup = $GetYourBill->WC_Settings_GetyourbillTabs->getyourbillfieldstypeaccounts;
                    $pactive = self::get_info("pactive");
                    if( $pactive !== "yes" && isset($fields_popup["pa"]) ){
                        unset($fields_popup["pa"]);
                    }
                    ?>
					<select id="popgybfields">
						<?php
                        foreach ( $fields_popup as $value => $label ) {
                            ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php
                        }
                        ?>
					</select>
 				</span>
            </p>

            <p class="form-row form-row form-row-wide gyb_hidden" mod="pcompany" >
                <label class=""><?php echo esc_html(__("Partita Iva (Ricerca Azienda)", 'getyourbill')); ?></label>
                <span class="woocommerce-input-wrapper">
					<input type="text" class="input-text getyourbill_info " mod="pi" placeholder="<?php echo esc_attr( __( "Partita Iva", 'getyourbill' ) ); ?>" value="" autocomplete="off">
				</span>
            </p>
            <p class="form-row form-row form-row-wide gyb_hidden" mod="psoleproprietor" >
                <label class=""><?php echo esc_html(__("Codice Fiscale (Ricerca Privato)", 'getyourbill')); ?></label>
                <span class="woocommerce-input-wrapper">
					<input type="text" class="input-text getyourbill_info" mod="cf" placeholder="<?php echo esc_attr( __( "Codice Fiscale", 'getyourbill' ) ); ?>" value="" autocomplete="off">
				</span>
            </p>
            <p class="form-row form-row form-row-wide gyb_hidden" mod="gybcode" >
                <label class=""><?php echo esc_html(__("Getyourbill ID (Ricerca Cliente GetYourBill)", 'getyourbill')); ?></label>
                <span class="woocommerce-input-wrapper">
					<input type="text" class="input-text getyourbill_info getyourbillid_icon" mod="bilid" placeholder="<?php echo esc_attr( __( "Getyourbill ID", 'getyourbill' ) ); ?>" value="" autocomplete="off">
				</span>
            </p>
            <?php
            $pactive = \models\GetyourbillApp::get_info("pactive");
            if( $pactive === "yes" ){
                ?>
                <p class="form-row form-row form-row-wide gyb_hidden" mod="pa" >
                    <label class=""><?php echo esc_html(__("Codice Univoco PA (Ricerca Publica Amministrazione)", 'getyourbill')); ?></label>
                    <span class="woocommerce-input-wrapper">
	 					<input type="text" class="input-text getyourbill_info " mod="paid" placeholder="<?php echo esc_attr( __( "Codice Univoco PA", 'getyourbill' ) ); ?>" value="" autocomplete="off">
	 				</span>
                </p>
                <?php
            }
            ?>

            <button id="getyourbill_autocomlete_cerca" type="button" class="button alt" value="<?php echo esc_attr( __( "AVANTI", 'getyourbill' ) ); ?>" ><?php echo esc_html( __( "AVANTI", 'getyourbill' ) ); ?></button>
            <!-- <div class="getyourbill_autocomlete_close"><a id="getyourbill_autocomlete_close" href="javascript:void(0);"><?php echo esc_html(__("Inserisci i dati manualmente", 'getyourbill')); ?></a></div> -->
        </div>
        <div class="power_by">
            <a href="https://www.getyourbill.com/" target="_blank">Powered by GetYourBill</a>
        </div>
    </div>
</div>
<a id="getyourbill_view_autocomlete" class="getyourbill_view_autocomlete " href="javascript:void(0);"><?php echo esc_html(__("Cerca anagrafica", 'getyourbill')); ?></a>
