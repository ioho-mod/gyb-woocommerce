<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( !defined('GetYourBill') ){
    exit();
}

define( 'GetYourBillPayDef', array(
    "bacs" => array('MP05'),
    "cheque" => array('MP02'),
    "cod" => array('MP01'),
    "paypal" => array('MP08'),
));
define( 'GetYourBillPluginPDF', array(
    'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' => __( 'PDF Invoices & Packing Slips for WooCommerce', 'getyourbill' ),
    'getyourbill_template' => __( 'GetYourBill PDF template personalizzato', 'getyourbill' ),
    //'woocommerce-pdf-invoices/bootstrap.php' => __( 'WooCommerce PDF Invoices', 'getyourbill' )
));
define( 'GetYourBillInputsreg', array(
    'nessuno' => __( "Nessuno (non è richiesto nessun dato in aggiunta all'email)", 'getyourbill' ),
    'namesurname' => __( 'Nome, Cognome, Ragione sociale', 'getyourbill' ),
    'inputonregiter' => __( 'Dati aggiuntivi per la fatturazione', 'getyourbill' ),
    'inputonregiter_namesurname' => __( 'Nome, Cognome, Ragione sociale + Dati aggiuntivi per la fatturazione', 'getyourbill' ),
));
define( 'GetYourBillPluginStatusGenerateInvoice', array(
    'completed' => __( 'Completato', 'getyourbill' ),
    'processing' => __( 'In lavorazione', 'getyourbill' ),
));
define( 'GetYourBillPayments', array(
    'MP01' => __( 'Contanti', 'getyourbill' ),
    'MP02' => __( 'Assegno', 'getyourbill' ),
    'MP03' => __( 'Assegno circolare', 'getyourbill' ),
    'MP04' => __( 'Contanti presso Tesoreria', 'getyourbill' ),
    'MP05' => __( 'Bonifico', 'getyourbill' ),
    'MP06' => __( 'Vaglia bancario', 'getyourbill' ),
    'MP07' => __( 'Bollettino bancario', 'getyourbill' ),
    'MP08' => __( 'Carta di pagamento', 'getyourbill' ),
    'MP09' => __( 'RID', 'getyourbill' ),
    'MP10' => __( 'RID utenze', 'getyourbill' ),
    'MP11' => __( 'RID veloce', 'getyourbill' ),
    'MP12' => __( 'Riba', 'getyourbill' ),
    'MP13' => __( 'MAV', 'getyourbill' ),
    'MP14' => __( 'Quietanza erario stato', 'getyourbill' ),
    'MP15' => __( 'Giroconto su conti di contabilità speciale', 'getyourbill' ),
    'MP16' => __( 'Domiciliazione bancaria', 'getyourbill' ),
    'MP17' => __( 'Domiciliazione postale', 'getyourbill' ),
    'MP18' => __( 'Bollettino di c/c postale', 'getyourbill' ),
    'MP19' => __( 'SEPA Direct Debit', 'getyourbill' ),
    'MP20' => __( 'SEPA Direct Debit CORE', 'getyourbill' ),
    'MP21' => __( 'SEPA Direct Debit B2B', 'getyourbill' ),
    'MP22' => __( 'Trattenuta su somme già riscosse', 'getyourbill'  )
));

define( 'GetYourBillSdiErrorIntermediaError', array(
    "intermediaryErrorMessage" => array( "name" => __("L'intermediario ha rifiutato la fattura con il presente messaggio di errore", 'getyourbill' ), "mod" => "error", "status" => "error" ),
    "deliveryDateTime" => array( "name" => __("Data ed ora in cui la fattura è stata recapitata", 'getyourbill' ), "mod" => "result", "status" => "success" ),
    "sdiErrorMessage" => array( "name" => __("Lo SDI ha rifiutato la fattura con il presente messaggio di errore", 'getyourbill' ), "mod" => "error", "status" => "error" ),
    "docSdiErrors" => array( "name" => __("docSdiErrors", 'getyourbill' ), "mod" => "error", "status" => "success" ),
    "impossibleDeliveryDateTime" => array( "name" => __("Impossibile recapitare la fattura, data ed ora a partire dalla quale è disponibile nel cassetto fiscale", 'getyourbill' ), "mod" => "error", "status" => "success" ),
    "receiverResult" => array( "name" => __("receiverResult", 'getyourbill' ), "mod" => "result", "status" => "success" ),
    "receiverResultDescription" => array( "name" => __("Il motivo del rifiuto", 'getyourbill' ), "mod" => "result", "status" => "success" ),
    "isReceiverResultExpired" => array( "name" => __("Notifica di decorrenza termini, il destinatario non ha risposto entro i termini previsti", 'getyourbill' ), "mod" => "error", "status" => "error" ),
    "receiverResultExpiredDescription" => array( "name" => __("Il motivo della decorrenza termini", 'getyourbill' ), "mod" => "result", "status" => "success" ),
    "sdiReceivedDateTime" => array( "name" => __("Attestazione di avvenuta trasmissione con impossibilità di recapito, data ed ora in cui la fattura è stata ricevuta dal sdi", 'getyourbill' ), "mod" => "result", "status" => "success" ),
    "stateNotManagedByIntermediary" => array( "name" => __("stateNotManagedByIntermediary", 'getyourbill' ), "mod" => "error", "status" => "error" ),
));

define( 'GetYourBillDevice', array(
    "code" => __("Codice terminale", 'getyourbill' ),
    "license" => __("Codice licenza", 'getyourbill' ),
    "locationId" => __("ID Esercente", 'getyourbill' ),
));
define( 'GetYourBillAjax',  array(
    "loading" => __("Attendere...", 'getyourbill' ),
    "error_ajax" => __("Si è verificato un errore, riprovare", 'getyourbill' ),
    "error_res" => __("Si è verificato un errore, contattare l'amministratore", 'getyourbill' ),
));

define( 'GetYourBillAccountsSelect', array(
    '' => __( 'Seleziona tipologia', 'getyourbill' ),
    'psoleproprietor' => __( 'Privato', 'getyourbill' ),
    'pperson' => __( 'Ditta Individuale/Libero Professionista', 'getyourbill' ),
    'pcompany' => __( 'Azienda/Società/Organizzazione no profit', 'getyourbill' ),
    'pa' => __( 'Pubblica Amministrazione', 'getyourbill' ),
));
define( 'GetYourBillAccounts', array(
    'psoleproprietor' => __( 'Privato', 'getyourbill' ),
    'pperson' => __( 'Ditta Individuale/Libero Professionista', 'getyourbill' ),
    'pcompany' => __( 'Azienda/Società/Organizzazione no profit', 'getyourbill' ),
    'pa' => __( 'Pubblica Amministrazione', 'getyourbill' ),
));
define( 'GetYourBillOptionsRequestInputsBackEnd', array(
	'oprionals' => __( 'Nessun campo obbligatorio', 'getyourbill' ), //cancellare
	'billing_pi' => __( 'La Partita Iva è obbligatoria', 'getyourbill' ), //cancellare
    'billing_cf' => __( 'Il Codice fiscale è obbligatorio', 'getyourbill' ),
    'billing_pecaddress' => __( 'PEC', 'getyourbill' ),
    'billing_sdiid' => __( 'Codice Destinatario', 'getyourbill' ),
	'billing_pi__billing_cf' => __( 'Partita Iva e Codice fiscale sono obbligatori', 'getyourbill' ), //cancellare
    'billing_pi__billing_cf__billing_pecaddress_if__billing_sdiid_if' => __( 'Partita Iva e Codice fiscale sono richiesti e almeno uno dei due campi PEC o Codice Destinatario', 'getyourbill' ),
	'billing_pi__billing_cf__billing_pecaddress' => __( 'Partita Iva, Codice fiscale e PEC sono obbligatori', 'getyourbill' ), //cancellare
    'billing_pi__billing_pecaddress_if__billing_sdiid_if' => __( 'La Partita Iva è obbligatoria e almeno uno dei due campi PEC o Codice Destinatario', 'getyourbill' ),
	'billing_pi__billing_cf__billing_cf__billing_pecaddress' => __( 'Partita Iva e Codice fiscale sono richiesti e almeno uno dei due campi PEC o Codice Destinatario', 'getyourbill' ), //cancellare
    'billing_cf__billing_ipa__billing_pi_opz__billing_cig_opz__billing_cup_opz' => __( 'Codice fiscale e Codice IPA sono obbligatorio Partita Iva, CIG e GUP sono opzionali', 'getyourbill' ),
));

define( 'GetYourBillOptionsRequestInputs', array(
    'oprionals' => __( 'Nessun campo obbligatorio', 'getyourbill' ),
    'billing_pi' => __( 'La Partita Iva è obbligatoria', 'getyourbill' ),
    'billing_cf' => __( 'Il Codice fiscale è obbligatorio', 'getyourbill' ),
    'billing_pecaddress' => __( 'PEC', 'getyourbill' ),
    'billing_sdiid' => __( 'Codice Destinatario', 'getyourbill' ),
    'billing_pi__billing_cf' => __( 'Partita Iva e Codice fiscale sono obbligatori', 'getyourbill' ),
    'billing_pi__billing_cf__billing_pecaddress_if__billing_sdiid_if' => __( 'PEC o Codice Destinatario', 'getyourbill' ),
    'billing_pi__billing_cf__billing_pecaddress' => __( 'Partita Iva, Codice fiscale e PEC sono obbligatori', 'getyourbill' ),
    'billing_pi__billing_pecaddress_if__billing_sdiid_if' => __( 'PEC o Codice Destinatario', 'getyourbill' ),
    'billing_pi__billing_cf__billing_cf__billing_pecaddress' => __( 'Codice fiscale o PEC o Codice Destinatario', 'getyourbill' ),
    'billing_cf__billing_ipa__billing_pi_opz__billing_cig_opz__billing_cup_opz' => __( 'Codice fiscale è obbligatorio Partita Iva, CIG e GUP sono opzionali', 'getyourbill' ),
));

define( 'GetYourBillOptionsRequestInputsSorByAccount', array(
    "psoleproprietor" => array("oprionals", "billing_cf"),
    "pperson" => array("billing_pi__billing_cf","billing_pi__billing_cf__billing_pecaddress_if__billing_sdiid_if", "billing_pi__billing_cf__billing_pecaddress"),
    "pcompany" => array("billing_pi","billing_pi__billing_cf","billing_pi__billing_cf__billing_pecaddress_if__billing_sdiid_if","billing_pi__billing_pecaddress_if__billing_sdiid_if" ),
    "pa" => array( "billing_cf__billing_ipa__billing_pi_opz__billing_cig_opz__billing_cup_opz"),
));


define( 'GetYourBillInvoice', array(
    "i_not" => array( "name" => __("Nessuna fatturazione", 'getyourbill' ), "doctype" => "0"),
    "i_61" => array( "name" => __("Fattura elettronica", 'getyourbill' ), "doctype" => "61"),
));

define( 'GetYourBillRefer', array(
    "pec" => array( "name" => "billing_pecaddress", "mod" => "", "autocorezione" => true, "sp" => true, "hidden" => false),
    "code" => array( "name" => "billing_getyourbillid", "mod" => "", "autocorezione" => true, "sp" => true, "hidden" => false),
    "intermediaryCode" => array( "name" => "", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => false),
    "vatNumber" => array( "name" => "billing_pi", "mod" => "", "autocorezione" => true, "sp" => true, "hidden" => false),
    "ipaCode" => array( "name" => "billing_ipa", "mod" => "", "autocorezione" => true, "sp" => true, "hidden" => false),
    "taxCode" => array( "name" => "billing_cf", "mod" => "", "autocorezione" => true, "sp" => true, "hidden" => false),
    "description" => array( "name" => "billing_company", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => false),
    "address" => array( "name" => "billing_address_1", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => false),
    "city" => array( "name" => "billing_city", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => false),
    "zipCode" => array( "name" => "billing_postcode", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => false),
    "province" => array( "name" => "billing_state", "mod" => "strtoupper", "autocorezione" => true, "sp" => false, "hidden" => false),
    "country" => array( "name" => "billing_country", "mod" => "strtoupper", "autocorezione" => true, "sp" => false, "hidden" => false),
    "id" => array( "name" => "billing_id_anagrafica", "mod" => "", "autocorezione" => true, "sp" => false, "hidden" => true),
));

define( 'GetYourBillControout', array(
    "nessuno" => array(
        "billing_pi" => false,
        "billing_cf" => false,
        "billing_pecaddress" => false,
        "billing_sdiid" => false,
        "billing_ipa" => false,
        "billing_cig" => false,
        "billing_cup" => false,
    ),
    "pperson" => array(
        "billing_pi" => false,
        "billing_cf" => false,
        "billing_pecaddress" => false,
        "billing_sdiid" => false,
        "billing_ipa" => false,
        "billing_cig" => false,
        "billing_cup" => false,
    ),
    "pcompany" => array(
        "billing_pi" => false,
        "billing_cf" => false,
        "billing_pecaddress" => false,
        "billing_sdiid" => false,
        "billing_ipa" => false,
        "billing_cig" => false,
        "billing_cup" => false,
    ),
    "psoleproprietor" => array(
        "billing_pi" => false,
        "billing_cf" => false,
        "billing_pecaddress" => false,
        "billing_sdiid" => false,
        "billing_ipa" => false,
        "billing_cig" => false,
        "billing_cup" => false,
    ),
    "pa" => array(
        "billing_pi" => false,
        "billing_cf" => false,
        "billing_pecaddress" => false,
        "billing_sdiid" => false,
        "billing_ipa" => false,
        "billing_cig" => false,
        "billing_cup" => false,
    )
));

define( 'GetYourBillTaxesEstera', array(
    "iva0" => array("name" => __( "Iva 0% Extra Italia", 'getyourbill' ), "iva" => 0, "standart" => false),
));
define( 'GetYourBillTaxes', array(
    "ITA04" => array("name" => __( "Iva 4%", 'getyourbill' ), "iva" => 4, "country" => "IT", "standart" => false),
    "ITA10" => array("name" => __( "Iva 10%", 'getyourbill' ), "iva" => 10, "country" => "IT", "standart" => false),
    "ITA22" => array("name" => __( "Iva 22%", 'getyourbill' ), "iva" => 22, "country" => "IT", "standart" => true),
    /*"ESEPM" => array("name" => "Prestazione medica esente iva ex art.10 n.18 DPR 633/1972", "iva" => 0, "country" => "IT", "standart" => false),
    "RCEDI" => array("name" => __( "Esente iva ex art.17, comma 6 DPR 633/72", 'getyourbill' ), "iva" => 0, "country" => "IT", "standart" => false),
    "NIA72" => array("name" => __( "Non imponibile ex art.72, DPR 633/72", 'getyourbill' ), "iva" => 0, "country" => "IT", "standart" => false),
    "PADCE" => array("name" => __( "Esente iva ex art.5.3.1, Privilegi degli Agenti Diplomatici e Consolari Esteri in Italia", 'getyourbill' ), "iva" => 0, "country" => "IT", "standart" => false),
    "NIA41" => array("name" => __( "Non imponibile ex art.41, comma 1 DL 331/93", 'getyourbill' ), "iva" => 0, "country" => "IT", "standart" => false),
    "ESEAE" => array("name" => __( "Art. 74 del DPR 633/72 - IVA assolta dall'editore", 'getyourbill' ), "iva" => 0, "country" => "IT", "standart" => false),
    "ESIVA" => array("name" => "Esente iva", "iva" => 0, "country" => "IT", "standart" => false),*/
    "ESN1" => array("name" => __( "Escluse ex art. 15", 'getyourbill' ), "VatKind" => "N1"),
    "ESN2" => array("name" => __( "Non soggette", 'getyourbill' ), "VatKind" => "N2", "deprecated" => true),
    "ESN2.1" => array("name" => __( "2.1 Non soggette ad IVA ai sensi degli artt. da 7 a 7-septies del DPR 633/72", 'getyourbill' ), "VatKind" => "N2"),
    "ESN2.2" => array("name" => __( "2.2 Non soggette – altri casi", 'getyourbill' ), "VatKind" => "N2"),
    "ESN3" => array("name" => __( "Non imponibili", 'getyourbill' ), "VatKind" => "N3", "deprecated" => true),
    "ESN3.1" => array("name" => __( "3.1 Non Imponibili - esportazioni", 'getyourbill' ), "VatKind" => "N3"),
    "ESN3.2" => array("name" => __( "3.2 Non Imponibili - cessioni intracomunitarie", 'getyourbill' ), "VatKind" => "N3"),
    "ESN3.3" => array("name" => __( "3.3 Non Imponibili - cessioni verso San Marino", 'getyourbill' ), "VatKind" => "N3"),
    "ESN3.4" => array("name" => __( "3.4 Non Imponibili - operazioni assimilate alle cessioni all'esportazione", 'getyourbill' ), "VatKind" => "N3"),
    "ESN3.5" => array("name" => __( "3.5 Non Imponibili - a seguito di dichiarazioni d'intento", 'getyourbill' ), "VatKind" => "N3"),
    "ESN3.6" => array("name" => __( "3.6 Non Imponibili - altre operazioni che non concorrono alla formazione del plafond", 'getyourbill' ), "VatKind" => "N3"),
    "ESN4" => array("name" => __( "Esenti", 'getyourbill' ), "VatKind" => "N4"),
    "ESN5" => array("name" => __( "Regime del margine / IVA non esposta in fattura", 'getyourbill' ), "VatKind" => "N5"),
    "ESN6" => array("name" => __( "Inversione contabile", 'getyourbill' ), "VatKind" => "N6", "deprecated" => true),
    "ESN6.1" => array("name" => __( "6.1 Inversione contabile - cessione di rottami e altri materiali di recupero", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.2" => array("name" => __( "6.2 Inversione contabile - cessione di oro e argento puro", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.3" => array("name" => __( "6.3 Inversione contabile - subappalto nel settore edile", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.4" => array("name" => __( "6.4 Inversione contabile - cessione di fabbricati", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.5" => array("name" => __( "6.5 Inversione contabile - cessione di telefoni cellulari", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.6" => array("name" => __( "6.6 Inversione contabile - cessione di prodotti elettronici", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.7" => array("name" => __( "6.7 Inversione contabile - prestazioni comparto edile e settori connessi", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.8" => array("name" => __( "6.8 Inversione contabile - operazioni settore energetico", 'getyourbill' ), "VatKind" => "N6"),
    "ESN6.9" => array("name" => __( "6.9 Inversione contabile - altri casi", 'getyourbill' ), "VatKind" => "N6"),
    "ESN7" => array("name" => __( "IVA assolta in altro stato UE", 'getyourbill' ), "VatKind" => "N7")
));
define( 'GetYourBillVatKind', array(
    "N1" => "N1: Escluse ex art. 15",
    "N2" => "N2: Non soggette",
    "N3" => "N3: Non imponibili",
    "N4" => "N4: Esenti",
    "N5" => "N5: Regime del margine / IVA non esposta in fattura",
    "N6" => "N6: Inversione contabile",
    "N7" => "N7: IVA assolta in altro stato UE",
));
