<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/*
 * Il filtro mi permette di verificare se l'ordine è stato pagato e di conseguenza stampare la data di pagamento in fattura
 * */
add_filter('getyourbill_switch_data_fatturazione', 'getyourbill_switch_data_fatturazione_aux', 10, 1);
function getyourbill_switch_data_fatturazione_aux($order){
    if ($order->is_paid()){
        return true;
    }
    return false;
}
