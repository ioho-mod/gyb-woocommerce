<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside a class method, so these variables are local.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$getyourbill_tax_rates = WC_Settings_GetyourbillTabs::get_list_itemVatCode();
	$get_list_WC_Tax = WC_Settings_GetyourbillTabs::get_list_WC_Tax();

?>

<h2><?php echo esc_html(__( 'Gestione delle imposte per la fatturazione elettronica', 'getyourbill' ) ); ?></h2>

<div><p><?php echo esc_html(__( 'Per inviare correttamente le fatture, ad ogni imposta creata in WooCommerce devi associare la voce corrispondente nel sistema GetYourBill.', 'getyourbill' ) ); ?></p></div>

<div id="gyb_selects" style="display:none">

	<select name="gyb_tax">
		<option value="-1"><?php echo esc_html(__( 'Seleziona una tassa', 'getyourbill' ) ); ?></option>
		<?php
			foreach ( $getyourbill_tax_rates as $vatcode ) { // For each tax class, get all rates.
				if( count($vatcode) >0  ){

					if( !isset($vatcode["ns"]) ){
				?>
					  <option value="<?php echo esc_attr($vatcode['code'] ); ?>"><?php echo esc_html($vatcode['info']['name'] ); ?></option>
				<?php
					}else{
						if( count($vatcode["ns"]) === 1 ){
							?>
							  <option value="<?php echo esc_attr($vatcode['code'] ); ?>"><?php echo esc_html($vatcode['info']['name'] ); ?></option>
							<?php
						}else{
					?>
						<optgroup label="<?php echo esc_attr($vatcode['info']['name'] ); ?>">
							<?php
								foreach ( $vatcode["ns"] as $ns ) { // For each tax class, get all rates.
								?>
								  <option value="<?php echo esc_attr($ns['code'] ); ?>"><?php echo esc_html($ns['name'] ); ?></option>
								<?php
								}
							}
						?>
						</optgroup>
				<?php
					}
				}
			?>
		<?php
			}
		?>
	</select>
</div>

<table class="form-table gyb-form-table">
	<tbody>
		<tr valign="top">
			<td class=" " colspan="2">
				<table class="  widefat gyb-table" cellspacing="0">
						<thead>
							<tr>
								 <th class="space" style="width: 1%;"></th>
								 <th class="status"><?php echo esc_html( __( 'Imposta', 'getyourbill' ) ); ?></th>
								 <th class="description"><?php echo esc_html( __( 'Imposta', 'getyourbill' ) ); ?> GetYourBill</th>
								 <th class="action"></th>
							</tr>
						</thead>
						<tbody id="gyb_tbogy"  class="gyb_tbogy">
						</tbody>
					</table>
				</td>
			</tr>
	</tbody>
</table>
