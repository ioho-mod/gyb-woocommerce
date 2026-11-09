<?php // Static HTML/JS guide template. 
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
?>
<script>
    jQuery( document ).ready(function($) {
        if( jQuery(".indici").length ){
            jQuery(".indici a").click(function(event){
                event.preventDefault();
                $('html,body').animate({ scrollTop: $($(this).attr("href")).offset().top-100 }, 'slow');
            });
            jQuery("a.infomazioni").click(function(event){
                event.preventDefault();
                $('html,body').animate({ scrollTop: $($(this).attr("href")).offset().top-100 }, 'slow');
            });
        }
    });
</script>

<h1>INDICE</h1>

<div class="indici">
    <a href="#_INTRODUZIONE"><strong>INTRODUZIONE</strong></a><br><br>

    <a href="#_ATTIVAZIONE_E_CONFIGURAZIONE"><strong>ATTIVAZIONE E CONFIGURAZIONE</strong></a>
    <ul>
        <li><a href="#_Connessione_al_servizio"><strong>Connessione al servizio</strong></a></li>

        <li><a href="#_Configurazione"><strong>Configurazione</strong></a></li>

        <li><a href="#_Storico_dei_warning"><strong>Storico dei warning</strong></a></li>

        <li><a href="#_Metodi_di_pagamento"><strong>Metodi di pagamento</strong></a></li>

        <li><a href="#_Aliquote"><strong>Aliquote</strong></a></li>

        <li><a href="#_Crontab"><strong>Crontab</strong></a></li>

        <li><a href="#_Guida"><strong>Guida</strong></a></li>
    </ul>

    <a href="#_AUTOMATISMI_E_CONFIGURAZIONE"><strong>AUTOMATISMI E CONIFGURAZIONE DEL CRONTAB</strong></a>
    <ul>
        <li><a href="#_Automatismi_messi_a"><strong>Automatismi messi a disposizione dal plug-in GetYourBill</strong></a></li>

        <li><a href="#_Configurazione_del_Crontab"><strong>Configurazione del Crontab</strong></a></li>
    </ul>
    <a href="#_CONTROLLO_E_GESTIONE"><strong>CONTROLLO E GESTIONE DELL’ATTIVITÀ DI FATTURAZIONE</strong></a>
    <ul>
        <li><a href="#_Controllo_della_fatturazione"><strong>Controllo della fatturazione nella sezione “Ordini”</strong></a></li>

        <li><a href="#_Controllo_e_gestione_1"><strong>Controllo e gestione della fatturazione nella scheda dell’ordine</strong></a></li>
    </ul>

    <a href="#_CONTROLLO_E_GESTIONE_2"><strong>CONTROLLO E GESTIONE DEI DATI AGGIUNTIVI PER LA FATTURAZIONE</strong></a><br><br>
    <a href="#_FRONT_END_RICERCA"><strong>FRONT END: RICERCA E AUTOCOMPILAZIONE DEI DATI DI FATTURAZIONE</strong></a>
</div>

<h1 id="_INTRODUZIONE">INTRODUZIONE</h1>
<h3>GETYOURBILL</h3>
<p>GetYourBill è una piattaforma che permette l’invio delle fatture elettroniche in modo totalmente automatico, ricevendo i dati per le fatture da una fonte esterna (sia esso un registratore di cassa o un sito web) e interfacciandosi con un intermediario, deputato all’invio delle fatture all’Agenzia delle Entrate.</p>

<h3>PLUG-IN GETYOURBILL</h3>
<p>Il plug-in GetYourBill si integra con il plug-in WooCommerce (a sua volta un plug-in del CMS WordPress) e si interfaccia con la piattaforma GetYourBill, permettendo di configurare l’e-commerce per rispondere a tutte le necessità relative al nuovo sistema di invio delle fatture all’Agenzia delle Entrate. In particolare il plug-in, una volta installato, attivato e configurato, permette di:</p>

<p>- Richiedere all’acquirente i dati personali/aziendali non previsti da WooCommerce ma necessari alla corretta compilazione della fattura elettronica (Partita Iva, Codice Fiscale, Pec e Codice Destinatario). Aggiunge quindi i relativi campi nelle interfacce di registrazione e/o checkout;</p>

<p>- Inviare automaticamente le fatture all’intermediario che si occuperà dell’invio al Sistema di Interscambio dell’Agenzia delle Entrate (SDI);</p>

<p>- Controllare lo stato delle fatture (dal primo invio fino alla corretta ricezione presso lo SDI) direttamente nel back end di WooCommerce;</p>

<p>- Agevolare gli utenti del tuo e-commerce, velocizzando le fasi di registrazione e checkout grazie ad uno strumento di ricerca e compilazione automatica dei dati aziendali;</p>

<p>- Importare in WooCommerce le aliquote più comuni per il mercato italiano, con un semplice click.</p>

<p>- <strong>Attenzione: per il corretto funzionamento del plugin è necessario che ad ogni prodotto presente nell’eshop venga assegnato un codice SKU univoco.</strong></p>

<p><strong style="color: #e6007e;">IMPORTANTE:</strong> il plug-in GetYourBill è in via di ottimizzazione per la nuova modalità di Woocommerce: HPOS (High Performance Order Storage). In attesa del rilascio di una nuova versione del plug-in adattata a HPOS, <strong>è necessario settare il proprio plugin WooCommerce nella modalità tradizionale (“Legacy version”)</strong>.</p>


<h3>INSTALLAZIONE DEL PLUG-IN</h3>
<p>Scarica il file compresso in formato .zip sul tuo computer. Accedi alla sezione “Plug-in” di WordPress, clicca sul pulsante in alto “Aggiungi nuovo”.</p>

<p>Nella pagina che si apre dove sono elencati i principali plug-in per WordPress, premi il bottone “Carica plugin”. Comparirà un bottone che permetterà di scegliere il file scaricato in precedenza e di caricarlo. Selezionarlo e premere il pulsante “Installa ora”. Se correttamente attivato, il plug-in è pronto per essere utilizzato e configurato.</p>


<h1 id="_ATTIVAZIONE_E_CONFIGURAZIONE">ATTIVAZIONE E CONFIGURAZIONE</h1>

<p>Tutti i comandi per l’attivazione e configurazione del plug-in GetYourBill si trovano all’interno delle Impostazioni di WooCommerce. L’installazione del plug-in aggiunge un tab di nome “GetYourBill” nella sezione “Impostazioni” di WooCommerce. Cliccando su questo tab, comparirà la lista dei pannelli attraverso cui è possibile</p>
<ul>
    <li>attivare le funzionalità e configurare le impostazioni del plug-in;</li>
    <li>configurare i metodi di pagamento e le aliquote;</li>
    <li>consultare e inviare le notifiche riguardanti il funzionamento tecnico del plug-in (warning)</li>
</ul>
<img src="attivazione.png"/>

<p>In questo capitolo della guida, illustreremo separatamente ogni pannello.</p>
<h2 id="_Connessione_al_servizio">Connessione al servizio</h2>
<p>Il primo pannello è “Connessione al servizio”. Attraverso di esso è possibile inserire e verificare l’Api Key fornita da GetYourBill, necessaria per attivare la comunicazione tra l’e-commerce e la piattaforma GetYourBill.</p>

<p>Inserisci l’Api Key di GetYourBill per collegare l’e-commerce ai servizi GetYourBill.</p>

<p>Una volta inserita l’Api Key, nella sezione verranno riportati tutti i dettagli della licenza: Codice terminale, Codice della licenza, ID esercente e il Support ID, necessario quando si contatta l’assistenza.</p>

<img src="api.PNG"/>

<h2 id="_Configurazione">Configurazione</h2>
<p>Il pannello “Configurazione” presenta al suo interno le seguenti opzioni:</p>

<h3>Attiva/Disattiva plug-in</h3>
<p>Permette di attivare o disattivare il plug-in (utile per operazioni di debug o in caso di conflitto con altri plug-in)</p>
<img src="adplugin.png"/>

<h3>Fatture automatiche</h3>
<img src="fautomatiche.png"/>
<p>Permette di abilitare la generazione e l’invio automatico delle fatture, al momento del pagamento di un ordine. Se questa funzione non viene abilitata, le fatture dovranno essere inviate manualmente a GetYourBill, accedendo alla scheda che WooCommerce crea per ogni singolo ordine.</p>

<p><strong>IMPORTANTE</strong>: Uno dei principali vantaggi che il plug-in GetYourBill offre agli utenti WooCommerce è la possibilità di automatizzare al massimo le fasi del processo di fatturazione, dalla generazione della fattura fino alla corretta ricezione della fattura elettronica da parte dello SDI. Per beneficiare di questi automatismi, è necessario:</p>
<ul>
    <li>mantenere attivata la funzione “Fatture automatiche”.</li>
    <li>Configurare un crontab nel tuo sito.</li>
</ul>
<p>Trovi tutte le informazioni sugli automatismi di GetYourBill e sulla configurazione del Crontab nel capitolo <a class="infomazioni" href="#_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</a> della presente guida</p>

<h3>Colonna “Fatturazione”</h3>
<img src="colonnafatturazione.png"/>
<p>Permette di visualizzare una colonna “Fatturazione” nella pagina degli ordini. Si consiglia vivamente di mantenere abilitata questa opzione, per avere sempre sott’occhio lo stato delle fatture e rilevare se si sono verificati degli errori lungo il processo che va dalla generazione della fattura fino alla corretta ricezione da parte dello SDI.</p>

<h3>Plug-in per la generazione delle fatture in PDF</h3>
<img src="pdf.png"/>
<p>Il plug-in GetYourBill è già predisposto per dialogare con WooCommerce PDF Invoices & Packing Slips, il più diffuso plug-in per la generazione della fattura di cortesia in formato PDF. Se vuoi utilizzare questo plug-in semplicemente installalo nel tuo e-commerce, e lascia la configurazione di default mostrata nell’immagine. Se vuoi utilizzare un altro plugin, contatta l’assistenza di GetYourBill.</p>

<h3>Stato in cui generare la fattura</h3>
<img src="statofattura.png"/>

<p>Permette di scegliere lo stato dell’ordine in corrispondenza del quale sarà generata la fattura, qualora sia abilitata la funzione “Fatture automatiche” vista sopra.</p>

<p>APPROFONDIMENTO: Il plug-in GetYourBill prevede che la generazione automatica della fattura avvenga dopo il pagamento. WooCommerce prevede due soli stati dell’ordine successivi al pagamento: “In Lavorazione” e “Completato”. La tendina permette dunque di scegliere in quale di questi due stati dell’ordine sarà generata la fattura. Se non viene cambiata l’opzione, il plug-in GetYourBill di default prevede la generazione automatica della fattura quando l’ordine viene impostato (manualmente o automaticamente) su “In lavorazione”. <br> <strong>Attenzione:</strong> In base al funzionamento di Woocommerce, esiste un caso in cui l’ordine raggiunge direttamente lo stato “Completato” senza prima passare dallo stato “In lavorazione”. Ciò avviene quando l’ordine riguarda prodotti immateriali (virtuali/scaricabili secondo la definizione di WooCommerce) con pagamento immediato (ad es. con carta di credito o PayPal). In questo caso, WooCommerce non passa dallo stato “In lavorazione” perché presuppone che il pagamento immediato dell’ordine coincida con il suo completamento. Si osservi che in questo caso, se la generazione automatica della fattura è collegata allo stato “In lavorazione”, non vi sarà alcuna generazione automatica della fattura.
    L’esercente deve dunque scegliere lo stato in cui generare la fattura automatica in base al modo in cui si succedono gli stati dell’ordine nel proprio e-commerce, e valutando l’opzione più adatta alle proprie esigenze. In generale, se i prodotti venduti sono abbonamenti o prodotti digitali, bisognerà impostare la generazione della fattura quando l’ordine passa allo stato “completato”; nel caso di prodotti che prevedano una successiva spedizione, converrà impostare la generazione della fattura quando l’ordine passa allo stato “In lavorazione”.</p>

<p>Si sottolinea infine che, qualora non sia attivata la funzione “Fatture automatiche”, non vi sarà generazione automatica della fattura, indipendentemente da come è settata la presente configurazione.</p>
<h3>Invio automatico dei warning</h3>
<img src="warning.png"/>

<p>Le notifiche di errore (warning) sono informazioni che il plug-in archivia per agevolare il servizio di assistenza. I warning vengono visualizzati e archiviati nel pannello “Storico dei warning”, che comprende anche un pulsante per inviare all’assistenza l’intera lista dei warning. La presente opzione di configurazione permette di automatizzare il processo, abilitando un sistema di invio automatico dei warning al servizio di assistenza, senza intervento manuale da parte dell’utente. Lasciare attiva questa opzione aiuta a migliorare il servizio e a identificare e correggere più tempestivamente gli errori che possono verificarsi.</p>

<p>Affinché l’invio automatico dei warning funzioni correttamente, è richiesta anche un’ulteriore condizione: la configurazione di un Crontab. Cfr. A questo proposito il capitolo <a class="infomazioni" href="#_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</a> della presente guida.</p>

<h3 id="_CONFIGURAZIONE_DEI_DATI_AGGIUNTIVI_PER_LA_FATTURAZIONE">CONFIGURAZIONE DEI DATI AGGIUNTIVI PER LA FATTURAZIONE</h3>
<p>Di seguito vengono presentate alcune opzioni di configurazione che riguardano i campi che dovrà/potrà compilare l’utente, in fase di check-out. In particolare, il plugin GetYourBill permette di configurare la richiesta dei “dati aggiuntivi per la fatturazione”, ossia quei dati non previsti da Woocommerce, ma necessari alla corretta redazione delle fatture.</p>

<h3>Stile del selettore per il tipo di utente</h3>
<img src="utente.png"/>
<p>I dati necessari alla fatturazione dipendono dalla tipologia di utente. Ad esempio dove un utente privato sarà tenuto solo all’inserimento del codice fiscale in aggiunta ai tradizionali dati anagrafici, un’azienda o una ditta individuale dovranno inserire un maggior numero di dati.</p>

<p>Quindi, all’inizio del modulo di inserimento dei dati di fatturazione, all’utente sarà richiesto di specificare la tipologia a cui appartiene. Le tipologie possibili sono: Privato, Ditta Individuale/Libero Professionista e Azienda/Società.</p>

<p>L’opzione “Stile del selettore per il tipo di utente” permette semplicemente di scegliere il tipo di selettore che sarà presentato all’utente. La scelta è tra i pulsanti di opzione (radio button) o tendina.</p>

<h3>Seleziona il tipo di utente di default</h3>
<img src="utentedefault.png"/>
<p>Permette di scegliere quale tipologia di utente viene preimpostata (Nessuno, Privato, Libero Professionista/Ditta Individuale o Azienda/Società). Il plug-in GetYouBill prevede di default l’opzione “Nessuno” rendendo obbligatoria la scelta da parte dell’utente.</p>

<h3>Configurazioni specifiche in caso di utente “Privato”</h3>
<p>Alcune configurazioni possono essere differenziate a seconda della tipologia a cui appartiene l’utente (ossia se questi dichiara di essere Privato, Libero Professionista/Ditta Individuale o Azienda/Società).
    Quelle che seguono sono le opzioni di configurazione specifiche nel caso in cui l’utente dichiari di essere “Privato”</p>

<h3>Il tipo di documento che si intende generare</h3>
<img src="tipodocgenerare.png"/>
<p>Permette di scegliere se generare la fattura elettronica anche per gli utenti privati, o se gestire in altro modo (manualmente) il documento da consegnare.</p>

<h3>Campi obbligatori</h3>
<img src="campiobbligatori.png"/>
<p>Permette di scegliere se rendere o meno obbligatorio l’inserimento del codice fiscale quando la tipologia di utente è Privato</p>

<h3>GetYourBill ID</h3>
<img src="gybID.png"/>
<p>Permette di scegliere se visualizzare il campo per l’inserimento dell’ID GetYourBill.</p>

<h3>Configurazioni specifiche in caso di utente “Ditta Individuale/Libero Professionista”</h3>
<h3>Campi obbligatori</h3>
<img src="campiobbligatori.png"/>
<p>Permette di scegliere la combinazione dei campi obbligatori per la tipologia di utente Ditta Individuale/Libero Professionista</p>

<h3>GetYourBill ID</h3>
<img src="gybID.png"/>

<p>Permette di scegliere se visualizzare il campo per l’inserimento dell’ID GetYourBill.</p>

<h3>Configurazioni specifiche in caso di utente “Azienda/Società”</h3>

<h3>Campi obbligatori</h3>
<img src="campiobbligatori.png"/>
<p>Permette di scegliere la combinazione dei campi obbligatori per la tipologia di utente Azienda/Società</p>

<h3>GetYourBill ID</h3>
<img src="gybID.png"/>

<p>Permette di scegliere se visualizzare il campo per l’inserimento dell’ID GetYourBill.</p>

<h3>ULTERIORI CONFIGURAZIONI DEI DATI RICHIESTI ALL’UTENTE</h3>
<p>Il Plugin GetYourBill offre altre due configurazioni che incidono sulle modalità con cui il tuo e-commerce chiede dati personali all’utente:</p>

<h3>Telefono</h3>
<img src="telefono.png"/>

<p>WooCommerce, di default, impone all’utente di inserire obbligatoriamente un numero di telefono. Questa opzione permette di scegliere se rendere facoltativo o lasciare obbligatorio questo campo.</p>

<h3>Dati aggiuntivi richiesti nella registrazione</h3>
<img src="richistiregistazione.png"/>

<p>Questa opzione riguarda i dati richiesti all’utente in fase di registrazione al sito. Di default WooCommerce chiede solo l’indirizzo email. Questa opzione ti permette di scegliere se chiedere all’utente ulteriori dati in fase di registrazione, scegliendo tra 4 possibili combinazioni:</p>
<ul>
    <li>Nessuno (non è richiesto nessun dato in aggiunta all’email)</li>
    <li>Nome, Cognome, Ragione sociale</li>
    <li>Dati aggiuntivi per la fatturazione</li>
    <li>Nome, Cognome, Ragione sociale + Dati aggiuntivi per la fatturazione</li>
</ul>

<p>Come visto sopra, per “Dati aggiuntivi per la fatturazione” si intendono i dati che il plug-in GetYourBill permette di richiedere all’utente al fine di una corretta fatturazione. Tali dati cambiano a seconda della tipologia di utente, e i relativi campi saranno presentati secondo le configurazioni decise nella sezione <a class="infomazioni" href="#_CONFIGURAZIONE_DEI_DATI_AGGIUNTIVI_PER_LA_FATTURAZIONE">CONFIGURAZIONE DEI DATI AGGIUNTIVI PER LA FATTURAZIONE</a> di questo pannello.</p>

<p>N.B. Si fa notare che la richiesta di molti dati nella prima registrazione al sito non è di solito consigliabile: rallenta l’iscrizione, aumenta il rischio di dati errati e vanifica il vantaggio della funzione di ricerca anagrafica offerta dal plugin GetYourBill (cfr. Il capitolo <a class="infomazioni" href="#_FRONT_END_RICERCA">FRONT END: RICERCA E AUTOCOMPILAZIONE DEI DATI DI FATTURAZIONE</a> nella presente Guida)</p>

<h3>Salva le modifiche</h3>
<p>Premi sul pulsante “Salva le modifiche” per rendere effettive le tue configurazioni</p>

<h2 id="_Storico_dei_warning">Storico dei warning</h2>
<p>Le notifiche di errore (warning) sono informazioni che il plug-in archivia per agevolare il servizio di assistenza. I warning vengono visualizzati e archiviati nel pannello “Storico dei warning”, all’interno del riquadro bianco.</p>
<p>Condividendo la lista dei warning con l’assistenza, faciliterai la tempestiva identificazione e soluzione di eventuali problemi.</p>

<p>In basso, un tasto “invia” permette di inviare lo storico dei warning all’assistenza.</p>

<p>L’invio dei warning può essere automatizzato. Cfr. A questo proposito il capitolo <a class="infomazioni" href="#_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</a></p>

<img src="warninglist.png"/>
<h2 id="_Metodi_di_pagamento">Metodi di pagamento</h2>
<p>WooCommerce prevede nella sua installazione standard alcuni metodi di pagamento già configurabili e attivabili (ad es. PayPal, Bonifico, Pagamento alla consegna, pagamenti con assegno).</p>

<p>Esistono molti altri metodi di pagamento supportati da WooCommerce e attivabili con l’installazione di plug-in di terze parti (ad es. Stripe, 2Checkout, ecc).
    In questo pannello è possibile associare ad ogni metodo di pagamento previsto in WooCommerce il relativo tipo di pagamento previsto nel sistema GetYourBill, utile ad un corretto invio delle informazioni della fattura elettronica</p>

<p><strong>IMPORTANTE:</strong> Le terze parti che gestiscono i metodi di pagamento potrebbero modificare i metodi stessi e i loro codici, facendo saltare le corrispondenze impostate in questo pannello di configurazione. In caso di aggiornamento del vostro plug-in che gestisce i metodi di pagamento, si consiglia di aggiornare anche la sincronizzazione con GetYourBill, onde evitare questa casistica. È sufficiente cliccare sul pulsante “Salva le modifiche” in calce alla pagina di configurazione del plug-in GetYourBill.</p>

<img src="pagamenti.png"/>
<h2 id="_Aliquote">Aliquote</h2>
<p>Questo pannello ospita un utile strumento di gestione delle aliquote messo a disposizione dal plug-in GetYourBill. Esso permette di impostare in WooCommerce le aliquote di cui hai bisogno, senza doverle inserire manualmente. Con la nuova fatturazione elettronica, in caso di esenzione iva (aliquota a 0%), è necessario indicare la “natura” dell’esenzione iva (N1, N2, N3 …). Lo strumento permette di associare in automatico la natura corretta per l’aliquota.</p>
<p>Per inserire le aliquote basta selezionare le voci desiderate e premere in basso il tasto “Importa le aliquote in WooComemerce”.</p>

<img src="aliquote.png"/>
<p><strong>ATTENZIONE</strong>: per il corretto funzionamento dell’invio delle fatture, è necessario che le aliquote corrispondano esattamente a quelle previste da GetYourBill. E’ vivamente consigliato l’utilizzo di questo strumento, evitando l’inserimento manuale, per non incorrere in errori. Se hai bisogno di un’aliquota specifica non presente nella lista delle aliquote selezionabili, contatta GetYourBill per trovare una soluzione alle tue necessità.</p>

<h2 id="_Crontab"">Crontab</h2>
<img src="crontab.png"/>

<p>In questo pannello si trova il link da utilizzare nella configurazione del Crontab.</p>
<p>Cfr. a questo proposito il capitolo <a class="infomazioni" href="#_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</a></p>

<h2 id="_Guida">Guida</h2>
<p>In questo pannello è contenuto il presente manuale.</p>

<h1 id="_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</h1>

<h2 id="_Automatismi_messi_a">Automatismi messi a disposizione dal plug-in GetYourBill</h2>
<p>Uno dei principali vantaggi che il plug-in GetYourBill offre agli utenti WooCommerce è la possibilità di automatizzare al massimo le fasi del processo di fatturazione, dalla generazione della fattura fino all’invio all’intermediario che provvederà alla corretta ricezione della fattura elettronica da parte dello SDI.</p>

<p>In particolare il plug-in può:</p>
<ul>
    <li>Generare e inviare automaticamente le fatture a GetYourBill all’avvenuto pagamento dell’ordine</li>
    <li>Verificare automaticamente se il tentativo di invio è andato a buon fine e, in caso contrario, ripetere automaticamente il tentativo fino al corretto invio (un invio della fattura può fallire a causa di un temporaneo problema di connessione)</li>
    <li>Aggiornare automaticamente gli stati fattura, ossia tutte le notifiche riguardanti lo stato in cui si trova la fattura, la presenza di eventuali errori ecc. Il plug-in rende consultabili gli stati fattura direttamente nel backend dell’e-commerce (più precisamente, nella colonna “Fatturazione” della sezione “Ordini” - se abilitata - e all’interno della scheda di ogni singolo ordine)</li>
    <li>Inviare automaticamente al servizio di assistenza le notifiche (warning) riguardanti eventuali errori (ciò aiuta il servizio a correggere tempestivamente tali errori).</li>
</ul>

<h3>CONDIZIONI PER ABILITARE GLI AUTOMATISMI</h3>
<p>Per beneficiare degli automatismi sopra elencati, sono necessarie tre condizioni:</p>
<ul>
    <li>Dev’essere attivata la funzione “Fatture automatiche” (il controllo di questa funzione si trova nel pannello “Configurazione” del plug-in GetYourBill);</li>
    <li>Dev’essere attivata la funzione “Invio automatico dei warning” (il controllo di questa funzione si trova nel pannello “Configurazione” del plug-in GetYourBill);</li>
    <li>Dev’essere configurato un Crontab.</li>
</ul>
<p>Se non vengono modificate, le prime due condizioni  risultano già abilitate all’attivazione del plugin. La configurazione del Crontab è un’attività molto semplice: nella sezione successiva trovi le istruzioni per il suo espletamento.</p>

<h2 id="_Configurazione_del_Crontab">Configurazione del Crontab</h2>
<p>Il Crontab è un sistema di schedulazione che permette l’esecuzione ricorsiva di uno script all’interno del sito, con un intervallo di tempo definito. La configurazione del Crontab non è obbligatoria, ma è indispensabile per rendere operativi gli automatismi messi a disposizione dal plug-in GetYourBill.</p>

<p>Se vuoi conoscere meglio cos’è un crontab, leggi <a target="_black" href="https://it.wikipedia.org/wiki/Crontab">qui</a>.</p>

<p>Il crontab è configurabile direttamente sul server, tramite il pannello Hosting (ad es. cPanel o Plesk), utilizzando il link riportato nel pannello “Crontab”.</p>

<p>Nel caso tu non abbia l’accesso alla gestione del server, o voglia comunque espletare la procedura di configurazione nel modo più semplice, puoi affidarti ad un servizio esterno, come ad esempio Cron-job.org.</p>

<p>Di seguito, le istruzioni puntuali per configurare il crontab attraverso Cron-job.org</p>

<ul>
    <li>Accedi a <a target="_black" href="https://cron-job.org">https://cron-job.org</a>.</li>
    <li>Crea un account gratuito cliccando su “Sign in” e seguendo le istruzioni.</li>
    <li>Una volta creato l’account, fai log in nel sito. Quindi clicca su Members -> Cronjobs</li>
    <li>Nella sezione “Cronjobs” troverai un pulsante “Create cronjob”</li>
    <li>Cliccando “Create cronjob”, si aprirà il modulo per configurare il tuo cronjob. Compila i campi in questo modo:</li>
</ul>
<table>
    <tr>
        <td>
            <img src="cronjob.png"/>
        </td>
    </tr>
    <tr>
        <td>
            <p>1) Inserisci un nome a scelta per lo specifico cronjob.</p>

            <p>2) Inserisci il link dello script che verrà eseguito. Il link che devi inserire si trova nel pannello <a class="infomazioni" href="#_Crontab">CRONTAB</a></p>

            <p>3) Inserisci l’intervallo di tempo tra un’esecuzione e l’altra dello script. Il nostro consiglio è di impostarlo a 5 minuti.</p>

            <p>4) Seleziona quando vuoi venire avvisato via e-mail in caso di errore nell’esecuzione. Il nostro consiglio è di lasciare l’opzione di default già selezionata, per evitare un eccessivo invio di e-mail.</p>

            <p>Al termine della configurazione premi il pulsante “Create cronjob”: il crontab è configurato e attivato.</p>

        </td>
    </tr>
</table>

<h1 id="_CONTROLLO_E_GESTIONE">CONTROLLO E GESTIONE DELL’ATTIVITÀ DI FATTURAZIONE</h1>

<h2 id="_Controllo_della_fatturazione">Controllo della fatturazione nella sezione “Ordini”</h2>
<p>Come visto sopra (capitolo “ATTIVAZIONE E CONFIGURAZIONE”) è possibile abilitare una colonna “Fatturazione” all’interno della sezione “ORDINI” di WooCommerce. In essa viene riportato lo stato della fattura relativa ad ogni ordine.</p>

<img src="stati.PNG"/>

<p>Nella colonna “Fatturazione” lo stato della fattura è riportato in modo sintetico, riconducendo tutte le possibili casistiche a 5 stati fondamentali:</p>
<ul>
    <li><span class="">Nessuna etichetta presente</span>. La fattura non è stata generata, oppure deve ancora cominciare il processo di invio a GetYourBill</li>
    <li><span class="gyb_processing_color">Inviata a GetYourBill</span>. La fattura è stata correttamente inviata a GetYourBill, ma non è ancora stato eseguito l’invio allo SDI.</li>
    <li><span class="gyb_completed_color">Inviata</span>. La fattura è stata correttamente inviata allo SDI ed è stata recapitata all’acquirente.</li>
    <li><span class="gyb_completed_color">Accettato dallo SDI. Impossibile recapitare la fattura</span>. La fattura è stata invita allo SDI ma non recapitata correttamente all’intermediario dell’acquirente. La fattura è comunque disponibile nel cassetto fiscale del cliente. Dal punto di vista dell’esercente gli obblighi fiscali sono assolti.</li>
    <li><span class="gyb_failed_color">Sono presenti degli errori</span>. La fattura non è stata inviata correttamente. Per conoscere l’errore è necessario entrare nella pagina di dettaglio dell’ordine (cliccando sulla riga corrispondente)</li>
</ul>
<p>Per avere una descrizione più puntuale dello stato della fattura – in particolare per conoscere l’errore specifico quando la colonna indica “Sono presenti degli errori”– è necessario entrare nella scheda del singolo ordine (come spiegato in dettaglio nella sezione successiva).</p>

<p><strong>IMPORTANTE</strong>: affinché gli stati fattura indicati nella colonna Fatturazione risultino sempre aggiornati, così da rappresentare in tempo reale l’effettivo stato delle fatture, è necessario che gli automatismi del plug-in GetYourBill siano correttamente attivati. Cfr. A questo proposito il capitolo <a class="infomazioni" href="#_AUTOMATISMI_E_CONFIGURAZIONE">AUTOMATISMI E CONFIGURAZIONE DEL CRONTAB</a></p>

<h2 id="_Controllo_e_gestione_1">Controllo e gestione della fatturazione nella scheda dell’ordine</h2>
<p>Quando si entra nella pagina di dettaglio di un singolo ordine, sarà presente sul lato destro il pannello “Note Ordine”. Qui vengono elencati tutti gli eventi relativi all’ordine, compresi eventuali errori. Se quindi nella sezione “Ordini” la colonna “Fatturazione” restituiva lo stato “Sono presenti degli errori”, qui viene indicato lo specifico errore che si è verificato.</p>

<img src="noteorder.png"/>

<p>Alcuni stati della fattura (i più importanti, come la segnalazione di errori o la notifica di corretta ricezione) vengono visualizzati anche in alto, prima del dettaglio dell’ordine.</p>

<img src="modificaordine.PNG"/>

<h3>Sezione “Gestione della fatturazione”</h3>
<p>Sempre all’interno della scheda dell’ordine, il plug-in aggiunge a sinistra una sezione intitolata “Gestione della fatturazione”. La sezione si trova subito sotto ai dati principali dell’ordine (voce “Generale”) e presenta alcuni pulsanti che permettono funzioni di controllo e gestione della fattura. I pulsanti che vengono visualizzati in questa sezione possono cambiare a seconda del modo in cui è stato configurato il plug-in e dello stato in cui si trova la fattura.</p>

<img src="dettagliordine.png"/>

<p>Ecco in dettaglio i pulsanti che, a seconda dei casi, potrai visualizzare in “Gestione della fatturazione”:</p>
<ul>
    <li><strong>Visualizza lo stato della fattura</strong>: ti permette di visualizzare lo stato aggiornato della fattura. Se non hai configurato il Crontab nel tuo sito, questo pulsante è l’unico modo di cui disponi per essere certo di visualizzare lo stato più recente della fattura.</li>
    <li><strong>Visualizza la fattura:</strong> ti permette di visualizzare il pdf della fattura.</li>
    <li><strong>Annulla la fattura</strong>: permette l’annullamento della fattura a condizione che sia l’ultima generata (per evitare il rischio di creare dei buchi nella sequenza delle fatture) e che non sia già stata inviata allo SDI. In questo secondo caso per porre rimedio ad eventuali errori nella fattura, è necessario procedere all’emissione di una Nota di Credito. Tramite il plug-in non è possibile creare automaticamente la Nota di Credito ma è necessario accedere al pannello GetYourBill. Per ulteriori indicazioni su come realizzare la nota di credito consultare la guida al servizio GetYourBill. Per informazioni su come deve essere compilata la Nota di Credito, consulta il tuo commercialista.</li>
    <li><strong>Invia manualmente la fattura: </strong>ti permette di inviare la fattura a GetYourBill. Se nella configurazione del plug-in, non hai attivato l’opzione “Fatture automatiche”, questo pulsante è l’unico modo di cui disponi per inviare le fatture a GetYourBill.</li>
    <li><strong>Re-invia la fattura:</strong> se visualizzi questo pulsante, significa che è già stato tentato l’invio di una fattura a GetYourBill, ma GetYourBill non l’ha accettata in quanto la fattura contiene errori. Dopo aver corretto gli errori segnalati negli stati fattura, devi cliccare su questo pulsante per effettuare un nuovo invio.</li>
</ul>

<h1 id="_CONTROLLO_E_GESTIONE_2">CONTROLLO E GESTIONE DEI DATI AGGIUNTIVI PER LA FATTURAZIONE</h1>

<p>Come già visto nei primi due capitoli, il plug-in GetYourBill permette di richiedere e inserire nell’ordine del tuo e-commerce tutti i dati necessari alla corretta compilazione della fattura elettronica. Dunque consente di acquisire e gestire alcuni dati in più rispetto a quelli normalmente gestiti da WooCommerce. Questi dati sono: Ragione sociale, Partita Iva, Codice Fiscale, Pec e Codice Destinatario, a cui si aggiunge eventualmente l’ID GetYourBill.</p>

<p>Questi “dati aggiuntivi” vengono poi visualizzati nell’Area riservata dell’utente, di modo che l’utente stesso possa consultarli e modificarli accedendo alla propria sezione “Dettagli account”. </p>

<p>Nel back end dell’e-commerce, questi “Dati aggiuntivi” sono visualizzabili dall’amministratore del sito nella scheda dell’ordine, in calce alla colonna “Fatturazione”, di cui costituiscono il completamento.</p>

<p>Nella scheda dell’ordine, i “Dati aggiuntivi” vengono gestiti esattamente come gli altri dati previsti da WooCommerce. Ovvero possono essere modificati e compilati manualmente, tanto in fase di modifica da backend di un ordine esistente, quanto in fase di creazione di un nuovo ordine da backend (pulsante “Aggiungi ordine”).</p>

<img src="ordineadmin.png"/>

<h1 id="_FRONT_END_RICERCA">FRONT END: RICERCA E AUTOCOMPILAZIONE DEI DATI DI FATTURAZIONE</h1>

<p>Il plug-in GetYourBill agisce nel back end del tuo sito e ti offre le funzionalità necessarie per gli adempimenti fiscali relativi alla fatturazione elettronica. Esiste però un’importante funzionalità che viene messa a disposizione degli utenti che acquistano sul tuo sito e-commerce: si tratta della Ricerca e Autocompilazione dei dati di fatturazione</p>

<p>L’utente anzitutto effettua la registrazione minima al sito (tramite l’inserimento dell’e-mail e dei dati che eventualmente avrai deciso di chiedergli). Poi, al momento del checkout o nella modifica dei dati personali, il primo step richiesto sarà quello di inserire una di queste 3 informazioni:</p>
<ul>
    <li>Partita Iva</li>
    <li>Codice Fiscale</li>
    <li>Codice GetYourBill</li>
</ul>
<p>Inserito uno di questi dati, l’utente potrà cliccare il tasto di ricerca, che avvierà l’immediata ricerca dei suoi dati di fatturazione all’interno degli archivi GetYourBill. Qualora i dati vengano reperiti da GetYourBill, all’utente apparirà il form precompilato con tutti i dati anagrafici necessari per la corretta emissione della fattura elettronica.</p>

<img src="popup.PNG"/>

<p>Questa funzionalità consente un vantaggio tanto all’utente quanto all’esercente:</p>
<ul>
    <li>dal lato utente permette di semplificare e velocizzare la procedura di checkout</li>
    <li>dal lato esercente minimizza gli errori nelle anagrafiche inserite</li>
</ul>

<p>In fase di checkout, WooCommerce prevede che vengano sempre richiesti all’utente i dati di fatturazione, e presenta i relativi campi già precompilati con i dati forniti in fase di registrazione. Ovviamente WooCommerce permette all’acquirente di effettuare la modifica di questi campi, per fatturare ad un diverso soggetto.</p>

<p>Nel caso i dati dell’utente registrato al sito siano già stati correttamente compilati, in fase di checkout non verrà più presentato il modulo di ricerca ma quello di default per l’inserimento dei dati.</p>

<p>Se l’utente intende acquistare con i propri dati aziendali, dovrà solo confermare.</p>

<p>Nel caso voglia fatturare ad altro soggetto, un bottone in testa al modulo permetterà di eseguire nuovamente una ricerca e, in caso di corretto reperimento dei dati, precompilerà il modulo con i dati del nuovo soggetto.</p>

<p>N.B. I dati di default dell’utente registrato sono quelli inseriti nella sezione “il mio account” di WordPress.
    I dati inseriti in fase di checkout vengono memorizzati in WooCommerce: vengono associati al singolo ordine e non sovrascrivono i dati di default dell’utente che acquista.</p>
