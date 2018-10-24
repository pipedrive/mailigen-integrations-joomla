<?php

class MGAPI {
    var $version = "1.8";
    var $errorMessage;
    var $errorCode;

    /**
     * API server adrese
     */
    var $apiUrl;

    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300;

    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;

    /**
     * Lietotaja API atslega
     */
    var $api_key;

    /**
     * Izmantot ssl: false - ne, true - ja
     */
    var $secure = false;

    /**
     * Pieslegties pie Mailigen API
     *
     * @param string $apikey Jusu Mailigen API atslega
     * @param string $secure Izmantot vai neizmantot ssl piesleganos
     */
    function __construct($apikey, $secure = false) {
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailigen.com/" . $this->version . "/?output=php");
        if ( isset($GLOBALS["mg_api_key"]) && $GLOBALS["mg_api_key"]!="" ){
            $this->api_key = $GLOBALS["mg_api_key"];
        } else {
            $this->api_key = $GLOBALS["mg_api_key"] = $apikey;
        }
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val === true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }

	/**
	* Noņemam nost statusu, kas lika kampaņu izsūtīt kaut kad nākotnē
	*
	* @example mgapi_campaignUnschedule.php
	* @example xml-rpc_campaignUnschedule.php
	*
	* @param string $cid Kampaņas, kurai vajag noņemt izsūtīšanas laiku kaut kad nākotnē, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function campaignUnschedule($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignUnschedule", $params);
	}

	/**
	* Iestādam laiku, kad izsūtīt kampaņu
	*
	* @example mgapi_campaignSchedule.php
	* @example xml-rpc_campaignSchedule.php
	*
	* @param string $cid Kampaņas, kurai vajag iestādīt izsūtīšanas laiku, ID
	* @param string $schedule_time Laiks, kad izsūtīt. Laiku jānorāda šādā formātā YYYY-MM-DD HH:II:SS pēc <strong>GMT</strong>
	* @return boolean true ja ir veiksmīgi
	*/
	function campaignSchedule($cid, $schedule_time) {
		$params = array();
		$params["cid"] = $cid;
		$params["schedule_time"] = $schedule_time;
		return $this->callServer("campaignSchedule", $params);
	}

	/**
	* Atjaunojam auto atbildētāja izsūtīšanu
	*
	* @example mgapi_campaignResume.php
	* @example xml-rpc_campaignResume.php
	*
	* @param string $cid Kampaņas, kuru vajag atsākt, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function campaignResume($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignResume", $params);
	}

	/**
	* Apstādinam uz laiku autoatbildētāju
	*
	* @example mgapi_campaignPause.php
	* @example xml-rpc_campaignPause.php
	*
	* @param string $cid Kampaņas, kuru vajag apstādināt, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function campaignPause($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignPause", $params);
	}

	/**
	* Nosūtīt kampaņu nekavējoties
	*
	* @example mgapi_campaignSendNow.php
	* @example xml-rpc_campaignSendNow.php
	*
	* @param string $cid Kampaņas, kuru vajag nosūtīt, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function campaignSendNow($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignSendNow", $params);
	}

	/**
	* Nosūtam testa vēstuli uz norādītajiem epastiem
	*
	* @example mgapi_campaignSendTest.php
	* @example xml-rpc_campaignSendTest.php
	*
	* @param string $cid Kampaņas, kur vēlamies notestēt, ID
	* @param array $test_emails Masīvs, kas satur epastus, uz kuriem nosūtīt vēstuli
	* @param string $send_type Nav obligāts. Ja vēlaties nosūtīt abus formātus, norādiet "html", ja tikai teksta, tad "plain"
	* @return boolean true ja veiksmīgi
	*/
	function campaignSendTest($cid, $test_emails = array(), $send_type = NULL) {
		$params = array();
		$params["cid"] = $cid;
		$params["test_emails"] = $test_emails;
		$params["send_type"] = $send_type;
		return $this->callServer("campaignSendTest", $params);
	}

	/**
	* Atrodam visus lietotāja šablonus
	*
	* @example mgapi_campaignTemplates.php
	* @example xml-rpc_campaignTemplates.php
	*
	* @return array Masīvs, kas satur šablonus
	* @returnf integer id Šablona ID
	* @returnf string name Šablona nosaukums
	* @returnf string layout Šablona izkārtojums - "basic", "left_column", "right_column" vai "postcard"
	* @returnf string preview_image URL adrese līdz priekšskatījuma attēlam
	* @returnf array source Šablona HTML kods
	*/
	function campaignTemplates() {
		$params = array();
		return $this->callServer("campaignTemplates", $params);
	}

	/**
	* Izveidojam jaunu kampaņu
	*
	* @example mgapi_campaignCreate.php
	* @example xml-rpc_campaignCreate.php
	*
	* @param string $type Kampaņas veids: "html", "plain", "auto"
	* @param array $options Masīvs ar kampaņas parametriem
			string/array list_id Saraksta id, to var atrast r lists()
			string subject Vēstules virsraksts
			string from_email Epasts, uz kuru varēs nosūtīt atbildes epastu
			string from_name Vārds, kas parādīsies pie nosūtītāja
			string to_email Merge vērtība, kas parādīsies pie To: lauka (tas nav epasts)
			array tracking Nav obligāts. Statistikas parametru masīvs, tiek izmantotas šādas atslēgas: "opens", "html_clicks" un "text_clicks". Pēc noklusējuma tiek skaitīta atvēršana un HTML klikšķi
			string title Nav obligāts. Kampaņas nosaukums. Pēc noklusējuma tiek izmantots vēstules virsraksts
			array analytics Nav obligāts. Masīvs ar skaitītāju informāciju. Google gadījumā ir šāds pielietojums "google"=>"jūsu_google_analytics_atslēga". "jūsu_google_analytics_atslēga" tiks pievienota visiem linkiem, statistiku varēs apskatīties klienta Google Analytics kontā
			boolean generate_text Nav obligāts. Ja nav norādīts plain teksts, tiks ģenerēts tekst no HTML. Pēc noklusējuma ir false
			boolean auto_footer Nav obligāts. Iekļaut vai neiekļaut automātisko kājeni vēstules saturā. Šis ir pieejams lietotājie ar Pro paku. Pēc noklusējuma ir false
            boolean inline_img Nav obligāts.
            boolean time_match Nav obligāts.
			boolean authenticate Nav obligāts. Ieslēgt epastu autentifikāciju. Šis strādās, ja ir pievienoti un aktivizēti autentificēti domēni sistēmā. Pēc noklusējuma ir false
			string sender Nav obligāts. Epasta adrese. Tiek izmantots, lai norādītu citu sūtītāja informāciju. Ir pieejams lietotājiem ar Pro paku.
			integer/array segment_id Nav obligāts. Satur segmenta ID, kuriem izsūtīt kampaņu
			boolean inline_img Nav obligāts. Izmantot vai nē inline bildes. Šis ir pieejams ar atbilstošu addonu. Pēc noklusējuma ir false
			string ln Nav obligāts. Nosaka, kādā valodā būs kājene un galvene. Iespējamās vērtības: cn, dk, en, ee, fi, fr, de, it, jp, lv, lt, no, pl, pt, ru, es, se

	* @param array $content Masīvs, kas satur vēstules saturu. Struktūra:
			"html" HTML saturs
			"plain" saturs plain vēstulei
			"url" Adrese, no kuras importēt HTML tekstu. Ja netiek norādīts plain teksts, tad vajag ieslēgt generate_text, lai tiktu ģenerēts plain teksta vēstules saturs. Ja tiek norādīta šī vērtība, tad tiek pārrakstītas augstāk minētās vērtības
			"archive" Ar Base64 kodēts arhīva fails. Ja tiek norādīta šī vērtība, tad tiek pārrakstītas augstāk minētās vērtības
			"archive_type" Nav obligāts. Pieļaujamie arhīva formāti: zip, tar.gz, tar.bz2, tar, tgz, tbz . Ja nav norādīts, tad pēc noklusējuma tiks izmantots zip
			integer template_id Nav obligāts. Lietotāja šablona id, nu kura tiks ģenerēts HTML saturs

	* @param array $type_opts Nav obligāts -

			Autoatbildētāja kampaņa, šis masīvs satur šādu informāciju:
			string offset-units Kāda vērtība no "day", "week", "month", "year". Obligāti jānorāda
			string offset-time Vērtība, kas ir lielāka par 0. Obligāti jānorāda
			string offset-dir Viena vērtība no "before" vai "after". Pēc noklusējuma "after"
			string event Nav obligāts. Izsūtīt pēc "signup" (parakstīšanās, pēc noklusējuma), "date" (datuma) vai "annual" (ikgadējs)
			string event-datemerge Nav obligāts. Merge lauks, kurš tiek ņemts vērā, kad izsūtīt. Šis ir nepieciešams, ja event ir norādīt "date" vai "annual"

	*
	* @return string Atgriež jaunās kampaņas ID
	*/
	function campaignCreate($type, $options, $content, $type_opts = NULL) {
		$params = array();
		$params["type"] = $type;
		$params["options"] = $options;
		$params["content"] = $content;
		$params["type_opts"] = $type_opts;
		return $this->callServer("campaignCreate", $params);
	}

	/**
	* Atjaunojam kampaņas, kura vēl nav nosūtīta, parametrus
	*
	*
	*  Uzmanību:<br/><ul>
	*        <li>Ja Jūs izmantojat list_id, visi iepriekšējie saraksti tiks izdzēsti.</li>
	*        <li>Ja Jūs izmantojat template_id, tiks pārrakstīts HTML saturs ar šablona saturu</li>
	*
	* @example mgapi_campaignUpdate.php
	* @example xml-rpc_campaignUpdate.php
	*
	* @param string $cid Kampaņas, kuru vajag labot, ID
	* @param string $name Parametra nosaukums (skatīties pie campaignCreate() options lauku ). Iespējamie parametri: subject, from_email, utt. Papildus parametri ir content. Gadījumā, ja vajag mainīt "type_opts", kā "name" vajag norādīt, piemēram, "auto".
	* @param mixed  $value Iespējamās vērtības parametram ( skatīties campaignCreate() options lauku )
	* @return boolean true, ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function campaignUpdate($cid, $name, $value) {
		$params = array();
		$params["cid"] = $cid;
		$params["name"] = $name;
		$params["value"] = $value;
		return $this->callServer("campaignUpdate", $params);
	}

	/**
	* Kopējam kampaņu
	*
	* @example mgapi_campaignReplicate.php
	* @example xml-rpc_campaignReplicate.php
	*
	* @param string $cid Kampaņas, kuru vajag kopēt, ID
	* @return string Atgriežam jaunās kampaņas ID
	*/
	function campaignReplicate($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignReplicate", $params);
	}

	/**
	* Tiek dzēsta neatgriezensiki kampaņa. Esiet uzmanīgi!
	*
	* @example mgapi_campaignDelete.php
	* @example xml-rpc_campaignDelete.php
	*
	* @param string $cid Kampaņas, kuru vajag dzēst, ID
	* @return boolean true ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function campaignDelete($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignDelete", $params);
	}

	/**
	* Atgriežam kampaņu sarakstu. Var pielietot filtru, lai detalizēt atlasītu
	*
	* @example mgapi_campaigns.php
	* @example xml-rpc_campaigns.php
	*
	* @param array $filters Nav obligāts. Masīvs ar parametriem:
			string  campaign_id Nav obligāts, kampaņas id
			string  list_id Nav obligāts, saraksta id. To var atrast ar lists()
			string  status Nav obligāts. Var atrast kampaņu pēc statusa: sent, draft, paused, sending
			string  type Nav obligāts. Kampaņas tips: plain, html
			string  from_name Nav obligāts. Atlasa kampānu pēc nosūtītāja vārda
			string  from_email Nav obligāts. Atlasa kampaņas pēc "Reply-to" epasta
			string  title Nav obligāts. Atlasa pēc kampaņas nosaukuma
			string  subject Nav obligāts. Atlasa pēc vēstules virsraksta ("Subject")
			string  sendtime_start Nav obligāts. Atlasa vēstules, kas izsūtītas pēc šī datuma/laika. Formāts - YYYY-MM-DD HH:mm:ss (24hr)
			string  sendtime_end Nav obligāts. Atlasa vēstules, kas izsūtītas pirms šī datuma/laika. Formāts - YYYY-MM-DD HH:mm:ss (24hr)
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 1000
	* @return array Agriež masīvu ar kampaņu sarakstu
	* @returnf string id Kampaņas id. To izmanto pārējām funkcijām
	* @returnf integer web_id Kampaņas id, kas tiek izmanots web versijā
	* @returnf string title Kampaņas virsraksts
	* @returnf string type Kampaņas tips (html,plain,auto)
	* @returnf date create_time Kampaņas izveidošanas datums
	* @returnf date send_time Kampānas nosūtīšanas datums
	* @returnf integer emails_sent Epastu skaits, uz kuriem nosūtīta kampaņa
	* @returnf string status Kampaņas statuss (sent, draft, paused, sending)
	* @returnf string from_name Vārds, kas parādās From laukā
	* @returnf string from_email E-pasts, uz kuru saņēmējs var nosūtīt atbildi
	* @returnf string subject Vēstules virsraksts
	* @returnf boolean to_email  Personalizēt "To:" lauku
	* @returnf string archive_url Arhīva saite uz kampaņu
	* @returnf boolean analytics Integrēt vai neitegrēt Google Analytics
	* @returnf string analytcs_tag  Google Analytics nosaukums kampaņai
	* @returnf boolean track_clicks_text Skaitīt vai neskaitīt klikšķus plain vēstulē
	* @returnf boolean track_clicks_html Skaitīt vai neskaitīt klikšķus HTML vēstulē
	* @returnf boolean track_opens Skaitīt vai neskaitīt atvēršanu
	*/
	function campaigns($filters = array(), $start = 0, $limit = 25) {
		$params = array();
		$params["filters"] = $filters;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaigns", $params);
	}

	/**
	* Given a list and a campaign, get all the relevant campaign statistics (opens, bounces, clicks, etc.)
	*
	* @example mgapi_campaignStats.php
	* @example xml-rpc_campaignStats.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @return array Masīvs, kas satur kampaņas statistiku
	* @returnf integer hard_bounces Nepiegādāto/nepareizo epastu skaits
	* @returnf integer soft_bounces Pagaidu nepiegādāto
	* @returnf integer blocked_bounces Bloķēto skaits
	* @returnf integer temporary_bounces Īslaicīgi atgriezto skaits
	* @returnf integer generic_bounces Nepareizo epastu skaits
	* @returnf integer unsubscribes Epastu skaits, kas atrakstījās no kampaņas
	* @returnf integer forwards Skaits, cik reizes vēstule ir pārsūtīta
	* @returnf integer opens Skaits, cik reizes atvērts
	* @returnf date last_open Datums, kad pēdējo reizi atvērts
	* @returnf integer unique_opens Unikālo atvēršanu skait
	* @returnf integer clicks Skaits, cik daudz ir spiests uz linkiem
	* @returnf integer unique_clicks Unikālie klikšķi uz saitēm
	* @returnf date last_click Datums, kad pēdējo reizi spiests uz linkiem
	* @returnf integer users_who_clicked Lietotāju skaits, kas spieduši uz saitēm
	* @returnf integer emails_sent Kopējais skaits, cik vēstules ir izsūtītas
	*/
	function campaignStats($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignStats", $params);
	}

	/**
	* Atrodam kampaņas visus linkus
	*
	* @example mgapi_campaignClickStats.php
	* @example xml-rpc_campaignClickStats.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @return struct urls Saišu masīvs
    * @returnf string id
    * @returnf string url
	* @returnf integer clicks Kopējais klikšķu skaits
	* @returnf integer unique Unikālo klikšķu skaits
	*/
	function campaignClickStats($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignClickStats", $params);
	}

	/**
	* Atrodam kampaņas detalizētu linku statistiku
	*
	* @example mgapi_campaignClickStatsDetails.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
    * @param array $filters Nav obligāts. Masīvs ar parametriem:
			string  linkId Nav obligāts, saites id. To var atrast ar campaignClickStats()
			string  link Nav obligāts, saite. To var atrast ar campaignClickStats()
	* @return struct
    * @returnf string email
    * @returnf string link
    * @returnf date date
	* @returnf string device
    * @returnf string browser
	*/
	function campaignClickStatsDetails($cid, $filters = [], $start = 0, $limit = 25) {
		$params = array();
		$params["cid"] = $cid;
        $params["filters"] = $filters;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignClickStatsDetails", $params);
	}

	/**
	* Atrodam šīs kampaņas epastu domēnu statistiku
	*
	* @example mgapi_campaignEmailDomainPerformance.php
	* @example xml-rpc_campaignEmailDomainPerformance.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @return array Masīvs ar epasta domēniem
	* @returnf string domain Domēna vārds
	* @returnf integer total_sent Kopā nosūtīto epastu skaits kampaņai (visi epasti)
	* @returnf integer emails Uz šo domēnu nosūtīto epstu skaits
	* @returnf integer bounces Neaizgājušo epastu skaits
	* @returnf integer opens Unikālo atvēršanu skaits
	* @returnf integer clicks Unikālo klikšķu skaits
	* @returnf integer unsubs Skaits, cik atrakstījušies
	* @returnf integer delivered Piegādāto vēstuļu skaits
	* @returnf integer emails_pct Skaits, cik epastu procentuāli ir ar šo domēnu
	* @returnf integer bounces_pct Skaits, cik procentuāli no kopēja skaita nav piegādāts ar šo domēnu
	* @returnf integer opens_pct Skaits, cik procentuāli ir atvērts ar šo domēnu
	* @returnf integer clicks_pct Skaits, cik procentuāli no šī domēna ir spieduši
	* @returnf integer unsubs_pct Procentuāli, cik daudz no šī domēna ir atrakstījušies
	*/
	function campaignEmailDomainPerformance($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignEmailDomainPerformance", $params);
	}

    /**
	* Atrodam neeksistējošos/nepareizos epastus (hard bounces)
	*
	* @example mgapi_campaignHardBounces.php
	* @example xml-rpc_campaignHardBounces.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignHardBounces($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignHardBounces", $params);
    }

    /**
	* Atrodam pagaidu atgrieztos epastus (soft bounces)
	*
	* @example mgapi_campaignSoftBounces.php
	* @example xml-rpc_campaignSoftBounces.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignSoftBounces($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignSoftBounces", $params);
    }

    /**
	* Atrodam atgrieztos epastus (blocked bounces)
	*
	* @example mgapi_campaignBlockedBounces.php
	* @example xml-rpc_campaignBlockedBounces.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignBlockedBounces($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignBlockedBounces", $params);
    }

    /**
	* Atrodam atgrieztos epastus (temporary bounces)
	*
	* @example mgapi_campaignTemporaryBounces.php
	* @example xml-rpc_campaignTemporaryBounces.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignTemporaryBounces($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignTemporaryBounces", $params);
    }

    /**
	* Atrodam atgrieztos epastus (generic bounces)
	*
	* @example mgapi_campaignGenericBounces.php
	* @example xml-rpc_campaignGenericBounces.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignGenericBounces($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignGenericBounces", $params);
    }

    /**
	* Atrodam visus e-pastus, kas ir atrakstījušies no šīs kampaņas
	*
	* @example mgapi_campaignUnsubscribes.php
	* @example xml-rpc_campaignUnsubscribes.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignUnsubscribes($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignUnsubscribes", $params);
    }

	/**
	* Atgriež valstu sarakstu, no kurām ir atvērtas vēstules un cik daudz
	*
	* @example mgapi_campaignGeoOpens.php
	* @example xml-rpc_campaignGeoOpens.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @return array countries Masīvs ar valstu sarakstu
	* @returnf string code Valsts kods ISO3166 formātā, satur 2 simbolus
	* @returnf string name Valsts nosaukums
	* @returnf int opens Skaits, cik daudz atvērts
	*/
	function campaignGeoOpens($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("campaignGeoOpens", $params);
	}

    /**
	* Atgriež detaļas par atvērto vēstuļu skaitu no konkrētas valsts
	*
	* @example mgapi_campaignGeoOpensByCountry.php
	* @example xml-rpc_campaignGeoOpensByCountry.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param string $code Valsts kods ISO3166 formātā, satur 2 simbolus
	* @return array country Masīvs ar detaļām par vienu valsti
	* @returnf string code Valsts kods ISO3166 formātā, satur 2 simbolus
	* @returnf string name Valsts nosaukums
	* @returnf int opens Skaits, cik daudz atvērts
	*/
	function campaignGeoOpensByCountry($cid, $code) {
		$params = array();
		$params["cid"] = $cid;
        $params["code"] = $code;
		return $this->callServer("campaignGeoOpensByCountry", $params);
	}

    /**
	* Atrodam pārsūtīšanas statistiku
	*
	* @example mgapi_campaignForwardStats.php
	* @example xml-rpc_campaignForwardStats.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 1000. Maksimālā vērtība ir 15000
	* @return array Epastu saraksts
	*/
    function campaignForwardStats($cid, $start = 0, $limit = 1000) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignForwardStats", $params);
    }

	/**
	* Atgriež kampaņas atmesto vēstuļu tekstus, kuras nav vecākas par 30 dienām
	*
	* @example mgapi_campaignBounceMessages.php
	* @example xml-rpc_campaignBounceMessages.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 50
	* @return array bounces Masīvs, kas satur atsviesto epastu saturu
	* @returnf string date Laiks, kad vēstule saņemta
	* @returnf string email Epasta arese, uz kuru neizdevās nosūtīt
	* @returnf string message Atsviestēs vēstules saturs
	*/
	function campaignBounceMessages($cid, $start = 0, $limit = 25) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignBounceMessages", $params);
	}

	/**
	* Atgriež epastu sarakstu, kas atvēruši kampaņu
	*
	* @example mgapi_campaignOpened.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 50
	* @return struct Masīvs, kas satur datus
	* @returnf integer total Kopējais skaits
	* @returnf array data Saraksts ar datiem
			struct data
				string email Epasta adrese
				integer count Cik reizes atvēra
	*/
	function campaignOpened($cid, $start = 0, $limit = 25) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignOpened", $params);
	}

	/**
	* Atgriež epastu sarakstu, kas nav atvēruši kampaņu
	*
	* @example mgapi_campaignNotOpened.php
	*
	* @param string $cid Kampaņas id. To var atrast ar campaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 50
	* @return struct Masīvs, kas satur datus
	* @returnf integer total Kopējais skaits
	* @returnf array data Epastu saraksts
			string email Epasta adrese
	*/
	function campaignNotOpened($cid, $start = 0, $limit = 25) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("campaignNotOpened", $params);
	}

	/**
	* Izveidojam jaunu sarakstu
	*
	* @example mgapi_listCreate.php
	* @example xml-rpc_listCreate.php
	*
	* @param string $title Saraksta nosaukums
	* @param array $options Masīvs ar kampaņas parametriem
			string permission_reminder Atgādinājums lietotājiem, kā tie nokļuva sarakstā
			string notify_to Epasta adrese, uz kuru sūtīt paziņojumus
			bool subscription_notify Sūtīt paziņojumus par to, ka ir jauns lietotājs pierakstījies
			bool unsubscription_notify Sūtīt paziņojumus par to, ka ir jauns lietotājs atrakstījies
			bool has_email_type_option Ļaut izvēlēties epasta formātu
            string public_title

	*
	* @return string Atgriež jaunā saraksta ID
	*/
	function listCreate($title, $options = NULL) {
		$params = array();
		$params["title"] = $title;
		$params["options"] = $options;
		return $this->callServer("listCreate", $params);
	}

	/**
	* Atjaunojam saraksta parametrus
	*
	* @example mgapi_listUpdate.php
	* @example xml-rpc_listUpdate.php
	*
	* @param string $id Saraksta, kuru vajag labot, ID
	* @param string $name Parametra nosaukums (skatīties pie listCreate() options lauku ). Iespējamie parametri: title, permission_reminder, notify_to, utt.
	* @return boolean true, ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function listUpdate($id, $name, $value) {
		$params = array();
		$params["id"] = $id;
		$params["name"] = $name;
		$params["value"] = $value;
		return $this->callServer("listUpdate", $params);
	}

	/**
	* Tiek dzēsts neatgriezensiki saraksts. Esiet uzmanīgi!
	*
	* @example mgapi_listDelete.php
	* @example xml-rpc_listDelete.php
	*
	* @param string $id Saraksta, kuru vajag labot, ID
	* @return boolean true ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function listDelete($id) {
		$params = array();
		$params["id"] = $id;
		return $this->callServer("listDelete", $params);
	}

	/**
	* Atrodam visus sarakstus
	*
	* @example mgapi_lists.php
	* @example xml-rpc_lists.php
	*
	* @return array Masīvs ar sarakstiem
	* @returnf string id Saraksta id. Šī vērtība tiek izmantota cītās funkcijās, kas strādā ar sarakstiem.
	* @returnf integer web_id Saraksta id, kas tiek izmantots web administrācijas lapā
	* @returnf string name Saraksta nosaukums
	* @returnf date date_created Saraksta izveidošanas datums.
	* @returnf integer member_count Lietotāju skaits sarakstā
	* @returnf integer unsubscribe_count Lietotāju skaits, cik atrakstījušies no saraksta
	* @returnf string default_from_name Noklusējuma vērtība From Name priekš kampaņām, kas izmanto šo sarakstu
	* @returnf string default_from_email Noklusējuma vērtība From Email priekš kampaņām, kas izmanto šo sarakstu
	* @returnf string default_subject Noklusējuma vērtība Subject priekš kampaņām, kas izmanto šo sarakstu
	* @returnf string default_language Noklusēja valoda saraksta formām
	*/
	function lists($start = 0, $limit = 1000) {
		$params = array();
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("lists", $params);
	}

	/**
	* Atrodam merge tagus sarakstam
	*
	* @example mgapi_listMergeVarUpdate.php
	* @example xml-rpc_listMergeVars.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @return array Merge tagu saraksts
	* @returnf string name Merge taga nosaukums
	* @returnf bool req Vai šis lauks ir obligāti aizpildāms (true) vai nē (false)
	* @returnf string field_type Merge tada datu tips. Ir pieļaujamas šādas vērtības: email, text, number, date, address, phone, website, image
	* @returnf bool show Norāda, vai rādīt šo lauku lietotāju sarakstā.
	* @returnf string order Kārtas numurs
	* @returnf string default Vērtība pēc noklusējuma
	* @returnf string tag Merge tags, kas tiek izmantots formās, listSubscribe() un listUpdateMember()
	*/
	function listMergeVars($id) {
		$params = array();
		$params["id"] = $id;
		return $this->callServer("listMergeVars", $params);
	}

	/**
	* Pievienojam jaunu merge tagu sarakstam
	*
	* @example mgapi_listMergeVarUpdate.php
	* @example xml-rpc_listMergeVarAdd.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $tag Merge tags, kuru vajag pievienot, piemēram, FNAME
	* @param string $name Garāks nosaukum, kas tiks rādīts lietotājiem
	* @param array $options Nav obligāts. Dažādi parametri merge tagam.
			string field_type Nav obligāts. Kāda vērtība no: text, number, date, address, phone, website, image. Pēc noklusējuma ir text
			boolean req Nav obligāts. Norāda, vai lauks ir obligāti aizpildāms. Pēc noklusējuma, false
			boolean show Nav obligāts. Norāda, vai rādīt šo lauku lietotāju sarakstā. Pēc noklusējuma, true
			string default_value Nav obligāts. Vērtība pēc noklusējuma

	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listMergeVarAdd($id, $tag, $name, $options = array()) {
		$params = array();
		$params["id"] = $id;
		$params["tag"] = $tag;
		$params["name"] = $name;
		$params["options"] = $options;
		return $this->callServer("listMergeVarAdd", $params);
	}

	/**
	* Atjaunojam merge taga parametrus sarakstā. Merge taga tipu nevar nomainīt
	*
	* @example mgapi_listMergeVarUpdate.php
	* @example xml-rpc_listMergeVarUpdate.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $tag Merge tags, kuru vajag atjaunot
	* @param array $options Parametri merge taga atjaunošanai. Pareizus parametrus skatīties pie metodes listMergeVarAdd()
	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listMergeVarUpdate($id, $tag, $options) {
		$params = array();
		$params["id"] = $id;
		$params["tag"] = $tag;
		$params["options"] = $options;
		return $this->callServer("listMergeVarUpdate", $params);
	}

	/**
	* Tiek izdzēsts merge tags no saraksta un vērtība visiem saraksta lietotājiem. Dati tie izdzēsti neatgriezeniski
	*
	* @example mgapi_listMergeVarDel.php
	* @example xml-rpc_listMergeVarDel.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $tag Merge tags, kuru vajag izdzēst
	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listMergeVarDel($id, $tag) {
		$params = array();
		$params["id"] = $id;
		$params["tag"] = $tag;
		return $this->callServer("listMergeVarDel", $params);
	}

    /**
	* Pievienojam sarakstam jaunu lietotaju
	*
	* @example mgapi_listSubscribe.php
	* @example xml-rpc_listSubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $email_address Epasta adrese, ko japievieno sarakstam
	* @param array $merge_vars Masivs, kas satur MERGE lauku vertibas (FNAME, LNAME, etc.) Maksimalais izmers 255
	* @param string $email_type Nav obligats. Epasta tips: html vai plain. Pec noklusejuma html
	* @param boolean $double_optin Vai sutit apstiprinajuma vestuli. Pec noklusejuma true
	* @param boolean $update_existing Vai atjaunot eksistejoos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
	* @param boolean $send_welcome - Nav obligats. Sutit vai nesutit paldies vestuli. Pec noklusejuma false

	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listSubscribe($id, $email_address, $merge_vars, $email_type = 'html', $double_optin = true, $update_existing = false, $send_welcome = false) {
		$params = array();
		$params["id"] = $id;
		$params["email_address"] = $email_address;
		$params["merge_vars"] = $merge_vars;
		$params["email_type"] = $email_type;
		$params["double_optin"] = $double_optin;
		$params["update_existing"] = $update_existing;
		$params["send_welcome"] = $send_welcome;
		return $this->callServer("listSubscribe", $params);
	}

    /**
	* Pievienojam sarakstam jaunu lietotaju
	*
	* @example mgapi_listSubscribe.php
	* @example xml-rpc_listSubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $phone Tālrunis, ko japievieno sarakstam
	* @param array $merge_vars Masivs, kas satur MERGE lauku vertibas (FNAME, LNAME, etc.) Maksimalais izmers 255
	* @param boolean $update_existing Vai atjaunot eksistejoos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)

	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listSubscribeSMS($id, $phone, $merge_vars, $update_existing = false) {
		$params = array();
		$params["id"] = $id;
		$params["phone"] = $phone;
		$params["merge_vars"] = $merge_vars;
		$params["update_existing"] = $update_existing;
		return $this->callServer("listSubscribeSMS", $params);
	}

	/**
	* Iznemam no saraksta epasta adresi
	*
	* @example mgapi_listUnsubscribe.php
	* @example xml-rpc_listUnsubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $email_address Epasta adrese vai "id", ko var atrast ar "listMemberInfo" metodi
	* @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
	* @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
	* @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listUnsubscribe($id, $email_address, $delete_member = false, $send_goodbye = true, $send_notify = true) {
		$params = array();
		$params["id"] = $id;
		$params["email_address"] = $email_address;
		$params["delete_member"] = $delete_member;
		$params["send_goodbye"] = $send_goodbye;
		$params["send_notify"] = $send_notify;
		return $this->callServer("listUnsubscribe", $params);
	}

	/**
	* Iznemam no saraksta epasta adresi
	*
	* @example mgapi_listUnsubscribe.php
	* @example xml-rpc_listUnsubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $phone Phone vai "id", ko var atrast ar "listMemberInfo" metodi
	* @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
	* @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listUnsubscribeSMS($id, $phone, $delete_member = false, $send_notify = true) {
		$params = array();
		$params["id"] = $id;
		$params["phone"] = $phone;
		$params["delete_member"] = $delete_member;
		$params["send_notify"] = $send_notify;
		return $this->callServer("listUnsubscribeSMS", $params);
	}

	/**
	* Labo epasta adresi, MERGE laukus saraksta lietotajam
	*
	* @example mgapi_listUpdateMember.php
	* @example xml-rpc_listUpdateMember.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $email_address Epasta adrese vai "id", ko var atrast ar "listMemberInfo" metodi
	* @param array $merge_vars Masivs ar  MERGE laukiem. MERGE laukus var apskatities pie metodes "listSubscribe"
	* @param string $email_type Epasta tips: "html" vai "plain". Nav obligats
	* @return boolean true ja ir izdevies, false ja nav izdevies
	*/
	function listUpdateMember($id, $email_address, $merge_vars, $email_type = '') {
		$params = array();
		$params["id"] = $id;
		$params["email_address"] = $email_address;
		$params["merge_vars"] = $merge_vars;
		$params["email_type"] = $email_type;
		return $this->callServer("listUpdateMember", $params);
	}

	/**
	* Pievienojam sarakstam vairakus epastus
	*
	* @example mgapi_listBatchSubscribe.php
	* @example xml-rpc_listBatchSubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar ada atslegam: "EMAIL" epasta adresei, "EMAIL_TYPE" epasta tips (html vai plain)
	* @param boolean $double_optin Vai sutit apstiprinajuma vestuli. Pec noklusejuma true
	* @param boolean $update_existing Vai atjaunot eksistejoos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
	* @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
	* @returnf integer success_count Skaits, cik izdevas
	* @returnf integer error_count Skaits, cik neizdevas
	* @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
	*/
	function listBatchSubscribe($id, $batch, $double_optin = true, $update_existing = false) {
		$params = array();
		$params["id"] = $id;
		$params["batch"] = $batch;
		$params["double_optin"] = $double_optin;
		$params["update_existing"] = $update_existing;
		return $this->callServer("listBatchSubscribe", $params);
	}

	/**
	* Pievienojam sarakstam vairakus epastus
	*
	* @example mgapi_listBatchSubscribe.php
	* @example xml-rpc_listBatchSubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param array $batch Masivs, kas satur epastu datus. Epasta dati ir masivs ar ada atslegam: "SMS" epasta adresei
	* @param boolean $update_existing Vai atjaunot eksistejoos epastus. Pec noklusejuma false (atgriezis kludas pazinojumu)
	* @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
	* @returnf integer success_count Skaits, cik izdevas
	* @returnf integer error_count Skaits, cik neizdevas
	* @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "phone"
	*/
	function listBatchSubscribeSMS($id, $batch, $update_existing = false) {
		$params = array();
		$params["id"] = $id;
		$params["batch"] = $batch;
		$params["double_optin"] = $double_optin;
		$params["update_existing"] = $update_existing;
		return $this->callServer("listBatchSubscribeSMS", $params);
	}

	/**
	* Iznemam no saraksta vairakus epastus
	*
	* @example mgapi_listBatchUnsubscribe.php
	* @example xml-rpc_listBatchUnsubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param array $emails Epastu masivs
	* @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
	* @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
	* @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
	* @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
	* @returnf integer success_count Skaits, cik izdevas
	* @returnf integer error_count Skaits, cik neizdevas
	* @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
	*/
	function listBatchUnsubscribe($id, $emails, $delete_member = false, $send_goodbye = true, $send_notify = false) {
		$params = array();
		$params["id"] = $id;
		$params["emails"] = $emails;
		$params["delete_member"] = $delete_member;
		$params["send_goodbye"] = $send_goodbye;
		$params["send_notify"] = $send_notify;
		return $this->callServer("listBatchUnsubscribe", $params);
	}

	/**
	* Iznemam no saraksta vairakus epastus
	*
	* @example mgapi_listBatchUnsubscribe.php
	* @example xml-rpc_listBatchUnsubscribe.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param array $phones Tālruņu masivs
	* @param boolean $delete_member Dzest vai nedzest lietotaju no saraksta. Pec noklusejuma false
	* @param boolean $send_goodbye Nosutit vai nesutit pazinojumu epasta lietotajam. Pec noklusejuma true
	* @param boolean $send_notify Nosutit vai nesutit pazinojumu uz epastu, kas noradits saraksta opcijas. Pec noklusejuma false
	* @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
	* @returnf integer success_count Skaits, cik izdevas
	* @returnf integer error_count Skaits, cik neizdevas
	* @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
	*/
	function listBatchUnsubscribeSMS($id, $phones, $delete_member = false, $send_notify = false) {
		$params = array();
		$params["id"] = $id;
		$params["phones"] = $phones;
		$params["delete_member"] = $delete_member;
		$params["send_notify"] = $send_notify;
		return $this->callServer("listBatchUnsubscribeSMS", $params);
	}

	/**
	* Atrodam epasta info sarkaksta
	*
	* @example mgapi_listMembers.php
	* @example xml-rpc_listMembers.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $status Epasta statuss (subscribed, unsubscribed, inactive, bounced), pec noklusejuma subscribed
	* @param integer $start Nav obligats. Nepiecieams lielam sarakstam. Lapas numurs, no kuras sakt. Pirmajai lapai atbilst numurs 0
	* @param integer $limit Nav obligats. Nepiecieams lielam sarakstam. Skaits, cik daudz atgriezt epastus. Pec noklusejuma 100, maksimalais ir 15000
	* @return array Masivs ar lietotaju sarakstu
	* @returnf string email Lietotaja epasts
	* @returnf date timestamp Peivienoanas datums
	*/
	function listMembers($id, $status = 'subscribed', $start = 0, $limit = 100) {
		$params = array();
		$params["id"] = $id;
		$params["status"] = $status;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("listMembers", $params);
	}

    function listMembersByMerge($id, $merge_var, $merge_value = '', $status = 'subscribed', $start = 0, $limit = 100) {
        $params = array();
		$params["id"] = $id;
		$params["merge_var"] = $merge_var;
		$params["merge_value"] = $merge_value;
		$params["status"] = $status;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("listMembersByMerge", $params);
    }

	/**
	* Atrodam epasta info sarkaksta
	*
	* @example mgapi_listMemberInfo.php
	* @example xml-rpc_listMemberInfo.php
	*
	* @param string $id Saraksta ID. Saraksta ID var atrast ar lists() metodi
	* @param string $email_address Epasta adrese vai epasta ID saraksta
	* @return array Masivs, kas satur epasta informaciju
	* @returnf string id Unikals epasta id
	* @returnf string email Epasta adrese
	* @returnf string email_type Epasta tips: html vai plain
	* @returnf array merges Masivs ar papildus laukiem
	* @returnf string status Epasta status: inactive, subscribed, unsubscribed, bounced
	* @returnf string ip_opt IP adrese, no kuras tika apstiprinats epasts
	* @returnf string ip_signup IP adrese, no kuras tika aizpildita forma
	* @returnf date timestamp Laiks, kad tika pievienots sarakstam
	*/
	function listMemberInfo($id, $email_address) {
		$params = array();
		$params["id"] = $id;
		$params["email_address"] = $email_address;
		return $this->callServer("listMemberInfo", $params);
	}

	/**
	* List growth history by specific time intervals
	*
	* @example mgapi_listGrowthHistory.php
	* @example xml-rpc_listGrowthHistory.php
	*
	* @param string $id List ID. List ID can be found using lists() method
	* @param string $split_by Optional. Statistics divided by periods of time: month, week, day. Default is month
	* @param string $start_date Optional. Filter statistics from given start date. Date should be in YYYY-MM-DD format. Note: end date should be provided together with start date.
	* @param string $end_date Optional. Filter statistics till given end date. Date should be in YYYY-MM-DD format. Note: start date should be provided together with end date.
	* @param integer $start Optional. Page number starting from which selection will be made. Default value is 0.
	* @param integer $limit Optional. Number of results returned in one page. Default value is 25. Maximum allowable value is 100.
	* @return array Array of statistical information split by given time intervals.
	* @returnf string month Year and month returned in YYYY-MM format, if $split_by parameter is set to month
	* @returnf string week Year, month and week returned in YYYY-MM \WW format, if $split_by parameter is set to week. Week number of year (01..53). Weeks starting from Monday. E.g. 2015-09 W36 - september, the 36th week in the year 2015
	* @returnf string day Year, month and day returned in YYYY-MM-DD format, if $split_by parameter is set to day
	* @returnf integer existing Number of emails at the beginning of given period of time
	* @returnf integer imports Number of how many emails of new recipients have been added during the current period of time
	*/
	function listGrowthHistory($id, $split_by = 'month', $start_date = null, $end_date = null, $start = 0, $limit = 25) {
		$params = array();
		$params["id"] = $id;
		$params["split_by"] = $split_by;
		$params["start_date"] = $start_date;
		$params["end_date"] = $end_date;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("listGrowthHistory", $params);
	}

	/**
	* Atrodam visus saraksta segmentus
	*
	* @example mgapi_listSegments.php
	* @example xml-rpc_listSegments.php
	*
	* @return array Masīvs ar saraksta segmentiem
	* @returnf string id Saraksta segmenta id.
	* @returnf integer web_id Saraksta segmenta id, kas tiek izmantots web administrācijas lapā
	* @returnf string name Saraksta segmenta nosaukums
	* @returnf date date_created Saraksta izveidošanas datums.
	* @returnf integer member_count Lietotāju skaits sarakstā
	*/
	function listSegments($id) {
		$params = array();
		$params["id"] = $id;
		return $this->callServer("listSegments", $params);
	}

	/**
	* Izveidojam jaunu segmentu
	*
	* @example mgapi_listSegmentCreate.php
	*
	* @param string $list Saraksta ID
	* @param string $title Segmenta nosaukums
	* @param string $match Sakritības tips
	* @param array $filter Masīvs ar nosacījumu masīviem
	* @param boolean $auto_update Segmenta automātiska atjaunošana
			string field Merge lauks
			string condition Nosacījumi: is, not, isany, contains, notcontain, starts, ends, greater, less
			string value Vērtība, priekš condition

	*
	* @return string Atgriež jaunā segmenta ID
	*/
	function listSegmentCreate($list, $title, $match, $filter, $auto_update = false) {
		$params = array();
		$params["list"] = $list;
		$params["title"] = $title;
		$params["match"] = $match;
		$params["filter"] = $filter;
		$params["auto_update"] = $auto_update;
		return $this->callServer("listSegmentCreate", $params);
	}

	/**
	* Atjaunojam segmenta parametrus
	*
	* @example mgapi_listSegmentUpdate.php
	*
	* @param string $sid Segmenta, kuru vajag labot, ID
	* @param string $name Parametra nosaukums (skatīties pie listSegmentCreate() options lauku ). Iespējamie parametri: title, match, filter
	* @return boolean true, ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function listSegmentUpdate($sid, $name, $value) {
		$params = array();
		$params["sid"] = $sid;
		$params["name"] = $name;
		$params["value"] = $value;
		return $this->callServer("listSegmentUpdate", $params);
	}

	/**
	* Tiek dzēsts neatgriezensiki segments. Esiet uzmanīgi!
	*
	* @example mgapi_listSegmentDelete.php
	*
	* @param string $sid Segmenta, kuru vajag dzēst, ID
	* @return boolean true ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function listSegmentDelete($sid) {
		$params = array();
		$params["sid"] = $sid;
		return $this->callServer("listSegmentDelete", $params);
	}

	/**
	* Atrodam epastus
	*
	* @example mgapi_listSegmentMembers.php
	* @example xml-rpc_listSegmentMembers.php
	*
	* @param string $id Segmenta ID. Saraksta ID var atrast ar listSegments() metodi
	* @param string $status Epasta statuss (subscribed, unsubscribed, inactive, bounced), pec noklusejuma subscribed
	* @param integer $start Nav obligats. Nepiecieams lielam sarakstam. Lapas numurs, no kuras sakt. Pirmajai lapai atbilst numurs 0
	* @param integer $limit Nav obligats. Nepiecieams lielam sarakstam. Skaits, cik daudz atgriezt epastus. Pec noklusejuma 100, maksimalais ir 15000
	* @return array Masivs ar lietotaju sarakstu
	* @returnf string email Lietotaja epasts
	* @returnf string id Lietotaja ID
	* @returnf string list Saraksta ID
	* @returnf date timestamp Peivienoanas datums
	*/
	function listSegmentMembers($id, $status = 'subscribed', $start = 0, $limit = 100) {
		$params = array();
		$params["id"] = $id;
		$params["status"] = $status;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("listSegmentMembers", $params);
	}

	/**
	* Noņemam nost statusu, kas lika SMS kampaņu izsūtīt kaut kad nākotnē
	*
	* @example mgapi_smsCampaignUnschedule.php
	*
	* @param string $cid SMS kampaņa, kurai vajag noņemt izsūtīšanas laiku kaut kad nākotnē, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function smsCampaignUnschedule($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignUnschedule", $params);
	}

	/**
	* Iestādam laiku, kad izsūtīt SMS kampaņu
	*
	* @example mgapi_smsCampaignSchedule.php
	*
	* @param string $cid SMS kampaņa, kurai vajag iestādīt izsūtīšanas laiku, ID
	* @param string $schedule_time Laiks, kad izsūtīt. Laiku jānorāda šādā formātā YYYY-MM-DD HH:II:SS pēc <strong>GMT</strong>
	* @return boolean true ja ir veiksmīgi
	*/
	function smsCampaignSchedule($cid, $schedule_time) {
		$params = array();
		$params["cid"] = $cid;
		$params["schedule_time"] = $schedule_time;
		return $this->callServer("smsCampaignSchedule", $params);
	}

	/**
	* Nosūtīt SMS kampaņu nekavējoties
	*
	* @example mgapi_smsCampaignSendNow.php
	*
	* @param string $cid SMS kampaņa, kuru vajag nosūtīt, ID
	* @return boolean true ja ir veiksmīgi
	*/
	function smsCampaignSendNow($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignSendNow", $params);
	}

	/**
	* Atrodam visus lietotāja SMS šablonus
	*
	* @example mgapi_smsCampaignTemplates.php
	*
	* @return array Masīvs, kas satur SMS šablonus
	* @returnf integer id Šablona ID
	* @returnf string source Šablona teksts
	*/
	function smsCampaignTemplates() {
		$params = array();
		return $this->callServer("smsCampaignTemplates", $params);
	}

	/**
	* Izveidojam jaunu SMS kampaņu
	*
	* @example mgapi_smsCampaignCreate.php
	*
	* @param array $options Masīvs ar SMS kampaņas parametriem
			string sender Vārds, no kā tiks nosūtīta SMS. To nepieciešams piereģistrēt ar funkciju smsSenderIdRegister()
			struct recipients
				string list_id Saraksta id, to var atrast ar lists()
				integer segment_id Nav obligāts. Segmenta ID, to var atrast ar segments()
				string merge SMS lauka nosaukums, piemēram, MERGE10, SMS
			array tracking Nav obligāts. Statistikas parametru masīvs, tiek izmantotas šādas atslēgas: "clicks".
			string title Nav obligāts. Kampaņas nosaukums.
			array analytics Nav obligāts. Masīvs ar skaitītāju informāciju. Google gadījumā ir šāds pielietojums "google"=>"jūsu_google_analytics_atslēga". "jūsu_google_analytics_atslēga" tiks pievienota visiem linkiem, statistiku varēs apskatīties klienta Google Analytics kontā
			boolean unicode Nav obligāts. Nosaka, vai izsūtīt kampaņu unikodā. Lai speciālie simboli un burit rādītos SMS kampaņa, šim ir jābūt true. Pēc noklusējuma ir false
			boolean concatenate Nav obligāts. Nosaka, vai izsūtīt vairākas īsziņas, ja teksts ir par garu. Pēc noklusējuma ir false

	* @param array $content Masīvs, kas satur vēstules saturu. Struktūra:
			text saturs Nav obligāts, ja ir norādīts template_id. SMS kampaņas saturs
			integer template_id Nav obligāts. Lietotāja SMS šablona id, nu kura tiks paņemts SMS saturs. Var atrast ar smsCampaignTemplates()

	*
	* @return string Atgriež jaunās SMS kampaņas ID
	*/
	function smsCampaignCreate($options, $content) {
		$params = array();
		$params["options"] = $options;
		$params["content"] = $content;
		return $this->callServer("smsCampaignCreate", $params);
	}

	/**
	* Atjaunojam kampaņas, kura vēl nav nosūtīta, parametrus
	*
	*
	*  Uzmanību:<br/><ul>
	*        <li>Ja Jūs izmantojat list_id, visi iepriekšējie saraksti tiks izdzēsti.</li>
	*        <li>Ja Jūs izmantojat template_id, tiks pārrakstīts saturs ar šablona saturu</li>
	*
	* @example mgapi_smsCampaignUpdate.php
	*
	* @param string $cid Kampaņas, kuru vajag labot, ID
	* @param string $name Parametra nosaukums (skatīties pie smsCampaignCreate() options lauku ). Iespējamie parametri: sender, recipients, utt. Papildus parametri ir content.
	* @param mixed  $value Iespējamās vērtības parametram ( skatīties campaignCreate() options lauku )
	* @return boolean true, ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function smsCampaignUpdate($cid, $name, $value) {
		$params = array();
		$params["cid"] = $cid;
		$params["name"] = $name;
		$params["value"] = $value;
		return $this->callServer("smsCampaignUpdate", $params);
	}

	/**
	* Kopējam kampaņu
	*
	* @example mgapi_smsCampaignReplicate.php
	*
	* @param string $cid SMS kampaņa, kuru vajag kopēt, ID
	* @return string Atgriežam jaunās SMS kampaņas ID
	*/
	function smsCampaignReplicate($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignReplicate", $params);
	}

	/**
	* Tiek dzēsta neatgriezensiki SMS kampaņa. Esiet uzmanīgi!
	*
	* @example mgapi_smsCampaignDelete.php
	*
	* @param string $cid SMS kampaņa, kuru vajag dzēst, ID
	* @return boolean true ja ir veiksmīgi, pretējā gadījumā atgriež kļūdas paziņojumu
	*/
	function smsCampaignDelete($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignDelete", $params);
	}

	/**
	* Atgriežam SMS kampaņu sarakstu. Var pielietot filtru, lai detalizēt atlasītu
	*
	* @example mgapi_smsCampaigns.php
	*
	* @param array $filters Nav obligāts. Masīvs ar parametriem:
			string  campaign_id Nav obligāts, kampaņas id
			string  recipients Nav obligāts, saraksta id. To var atrast ar lists()
			string  status Nav obligāts. Var atrast kampaņu pēc statusa: sent, draft, sending
			string  sender Nav obligāts. Atlasa kampānu pēc sūtītāja vārda
			string  title Nav obligāts. Atlasa pēc kampaņas nosaukuma
			string  sendtime_start Nav obligāts. Atlasa vēstules, kas izsūtītas pēc šī datuma/laika. Formāts - YYYY-MM-DD HH:mm:ss (24hr)
			string  sendtime_end Nav obligāts. Atlasa vēstules, kas izsūtītas pirms šī datuma/laika. Formāts - YYYY-MM-DD HH:mm:ss (24hr)
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 1000
	* @return array Agriež masīvu ar SMS kampaņu sarakstu
	* @returnf string id SMS kampaņas id. To izmanto pārējām funkcijām
	* @returnf integer web_id SMS kampaņas id, kas tiek izmanots web versijā
	* @returnf string title SMS kampaņas virsraksts
	* @returnf date create_time SMS kampaņas izveidošanas datums
	* @returnf date send_time SMS kampānas nosūtīšanas datums
	* @returnf integer sms_sent Nosūtīto SMS skaits
	* @returnf string status Kampaņas statuss (sent, draft, paused, sending)
	* @returnf string sender SMS sūtītāja vārds
	* @returnf boolean analytics Integrēt vai neitegrēt Google Analytics
	* @returnf string analytcs_tag  Google Analytics nosaukums kampaņai
	* @returnf boolean track_clicks Skaitīt vai neskaitīt klikšķus
	* @returnf boolean unicode Izmantot vai neizmantot unikodu
	* @returnf boolean concatenate Sadalīt vai nesadalīt vairākās īsziņās garāku īsziņu
	*/
	function smsCampaigns($filters = array(), $start = 0, $limit = 25) {
		$params = array();
		$params["filters"] = $filters;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("smsCampaigns", $params);
	}

	/**
	* Atgriež SMS kampaņas statistiku
	*
	* @example mgapi_smsCampaignStats.php
	*
	* @param string $cid SMS kampaņas id. To var atrast ar smsCampaigns()
	* @return array Masīvs, kas satur SMS kampaņas statistiku
	* @returnf integer delivered Piegādāto SMS skaits
	* @returnf integer sent Nosūtīto SMS skaits. Vēl nav saņemts gala apstiprinājums par veiksmi vai neveiksmi
	* @returnf integer queued SMS skats, kas stāv vēl izsūtīšanas rindā
	* @returnf integer undelivered Nepiegādāto SMS skaits
	* @returnf integer error Nepiegādāto SMS skaits, kuriem ir bijusi kāda tehniska kļūda piegādes procesā
	* @returnf integer other SMS ar citu piegādes statusu
	* @returnf integer clicks Skaits, cik daudz ir spiests uz linkiem
	* @returnf integer unique_clicks Unikālie klikšķi uz saitēm
	* @returnf date last_click Datums, kad pēdējo reizi spiests uz linkiem
	* @returnf integer users_who_clicked Lietotāju skaits, kas spieduši uz saitēm
	* @returnf integer sms_sent Kopējais skaits, cik vēstules ir izsūtītas
	*/
	function smsCampaignStats($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignStats", $params);
	}

	/**
	* Atrodam SMS kampaņas visus linkus
	*
	* @example mgapi_smsCampaignClickStats.php
	*
	* @param string $cid SMS kampaņas id. To var atrast ar smsCampaigns()
	* @return struct urls Saišu masīvs, kur atslēga ir saite
	* @returnf integer clicks Kopējais klikšķu skaits
	* @returnf integer unique Unikālo klikšķu skaits
	*/
	function smsCampaignClickStats($cid) {
		$params = array();
		$params["cid"] = $cid;
		return $this->callServer("smsCampaignClickStats", $params);
	}

	/**
	* Atgriež SMS kampaņas nepiegādāto īsziņu statusus
	*
	* @example mgapi_smsCampaignBounces.php
	*
	* @param string $cid SMS kampaņas id. To var atrast ar smsCampaigns()
	* @param integer $start Nav obligāts. Lapa, no kuras izvadīt datus. Pēc noklusējuma ir 0, kas atbilst pirmajai lapai
	* @param integer $limit Nav obligāts. Rezultātu skaits lapā. Pēc noklusējuma 25. Maksimālā vērtība ir 50
	* @return array bounces Masīvs, kas satur nepiegādātās SMS
	* @returnf string phone Tālruņa numurs, uz kuru neizdevās nosūtīt
	* @returnf string reason Iemesls, kāpēc netika piegādāts
	*/
	function smsCampaignBounces($cid, $start = 0, $limit = 25) {
		$params = array();
		$params["cid"] = $cid;
		$params["start"] = $start;
		$params["limit"] = $limit;
		return $this->callServer("smsCampaignBounces", $params);
	}

	/**
	* Nosūtam pieprasījumu reģistrēt SMS sūtītāja vārdu
	*
	* @example mgapi_smsSenderIdRegister.php
	*
	* @param string $sender Vēlamais SMS sūtītāja vārds
	* @param string $phone Rezerves mobilā tālr. numurs
	* @param string $company Uzņēmuma nosaukums
	* @param string $fullname Kontaktpersonas vārds, uzvārds
	* @param string $companyposition Pozīcija uzņēmumā
	* @param string $comments Papildus komentāri
	* @returnf boolean Vai ir pieņemts izskatīšanai
	*/
	function smsSenderIdRegister($sender, $phone, $company, $fullname, $companyposition, $comments = '') {
		$params = array();
		$params["sender"] = $sender;
		$params["phone"] = $phone;
		$params["company"] = $company;
		$params["fullname"] = $fullname;
		$params["companyposition"] = $companyposition;
		$params["comments"] = $comments;
		return $this->callServer("smsSenderIdRegister", $params);
	}

    /**
    * Pievienojam sarakstam jaunu lietotaju
    *
    * @example mgapi_suppressedListSubscribe.php
    *
    * @param string $email_address Epasta adrese, ko japievieno sarakstam

    * @return boolean true ja ir izdevies, false ja nav izdevies
    */
    function suppressedListSubscribe($email_address) {
        $params = array();
        $params["email_address"] = $email_address;
        return $this->callServer("suppressedListSubscribe", $params);
    }

    /**
    * Iznemam no saraksta epasta adresi
    *
    * @example mgapi_suppressedListUnsubscribe.php
    *
    * @param string $email_address Epasta adrese
    * @return boolean true ja ir izdevies, false ja nav izdevies
    */
    function suppressedListUnsubscribe($email_address) {
        $params = array();
        $params["email_address"] = $email_address;
        return $this->callServer("suppressedListUnsubscribe", $params);
    }

    /**
    * Pievienojam sarakstam vairakus epastus
    *
    * @example mgapi_suppressedListBatchSubscribe.php
    *
    * @param array $batch Masivs, kas satur epastu datus.
    * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
    * @returnf integer success_count Skaits, cik izdevas
    * @returnf integer error_count Skaits, cik neizdevas
    * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
    */
    function suppressedListBatchSubscribe($batch) {
        $params = array();
        $params["batch"] = $batch;
        return $this->callServer("suppressedListBatchSubscribe", $params);
    }

    /**
    * Iznemam no saraksta vairakus epastus
    *
    * @example mgapi_suppressedListBatchUnsubscribe.php
    *
    * @param array $emails Epastu masivs
    * @return struct Masivs, kas satur skaitu, cik izevies iznemt un kludu pazinojumus
    * @returnf integer success_count Skaits, cik izdevas
    * @returnf integer error_count Skaits, cik neizdevas
    * @returnf array errors Masivs ar kludas pazinojumiem. Satur "code", "message", un "email"
    */
    function suppressedListBatchUnsubscribe($emails) {
        $params = array();
        $params["emails"] = $emails;
        return $this->callServer("suppressedListBatchUnsubscribe", $params);
    }

    /**
    * Atrodam epasta info sarkaksta
    *
    * @example mgapi_suppressedListMembers.php
    *
    * @param integer $start Nav obligats. Nepiecieams lielam sarakstam. Lapas numurs, no kuras sakt. Pirmajai lapai atbilst numurs 0
    * @param integer $limit Nav obligats. Nepiecieams lielam sarakstam. Skaits, cik daudz atgriezt epastus. Pec noklusejuma 100, maksimalais ir 15000
    * @return array Masivs ar lietotaju sarakstu
    * @returnf string email Lietotaja epasts
    * @returnf date timestamp Pievienošanas datums
    */
    function suppressedListMembers($start = 0, $limit = 100) {
        $params = array();
        $params["start"] = $start;
        $params["limit"] = $limit;
        return $this->callServer("suppressedListMembers", $params);
    }

	/**
	* Atgriež dažādu informaciju par lietotaju kontu
	*
	* @example mgapi_getAccountDetails.php
	* @example xml-rpc_getAccountDetails.php
	*
	* @return array Masivs, kas satur da˛adu informaciju par is API atlsegas lietotaja kontu
	* @returnf string user_id Lietotaja unikalais ID, tas tiek izmantots buvejot da˛adas saites
	* @returnf string username Lietotaja lietotajvards
	* @returnf bool is_trial Vai lietotajs ir trial
	* @returnf int emails_left Skaits, cik daudz epastus var nosutit
	* @returnf datetime first_payment Pirma maksajuma datums
	* @returnf datetime last_payment Pedeja maksajuma datums
	* @returnf int times_logged_in Skaits, cik daudz reizes lietotajs caur web ir ielogojies
	* @returnf datetime last_login Datums, kad pedejo reizi bija ielogojies caur web
	* @returnf array contact Masivs, kas satur kontkatinformaciju: Vards, uzvards, epasts, uznemuma nosaukums, adrese, majas lapas adrese, telefons, fakss
	* @returnf array orders Masivs, kas satur informaciju par samaksatajiem rekiniem: rekina numurs, plans, cena, valuta, izrakstianas datums, pakas deriguma termin
	*/
	function getAccountDetails() {
		$params = array();
		return $this->callServer("getAccountDetails", $params);
	}

	/**
	* Atrodam visu sarakstu ID, kuros ir šis epasts
	*
	* @example mgapi_listsForEmail.php
	* @example xml-rpc_listsForEmail.php
	*
	* @param string $email_address epasta adrese
	* @return array an array Masivs, kas satur sarakstu ID
	*/
	function listsForEmail($email_address) {
		$params = array();
		$params["email_address"] = $email_address;
		return $this->callServer("listsForEmail", $params);
	}

	/**
	* Atrodam visas API atslegas
	*
	* @example mgapi_apikeys.php
	* @example xml-rpc_apikeys.php
	*
	* @param string $username lietotaja vards
	* @param string $password lietotaja parole
	* @param boolean $expired nav obligats - radit vai neradit atslegas, kuras vairs nav derigas. Pec noklusejuma ir false
	* @return array API atslegu masivs, kas satur:
	* @returnf string apikey o atslegu var izmantot, lai pieslegtos API
	* @returnf string created_at Datums, kad atslega ir izveidota
	* @returnf string expired_at Datums, kad ta tika atzimeta, ka neaktiva
	*/
	function apikeys($username, $password, $expired = false) {
		$params = array();
		$params["username"] = $username;
		$params["password"] = $password;
		$params["expired"] = $expired;
		return $this->callServer("apikeys", $params);
	}

	/**
	* Izveidojam jaunu API atslegu
	*
	* @example mgapi_apikeyAdd.php
	* @example xml-rpc_apikeyAdd.php
	*
	* @param string $username lietotaja vards
	* @param string $password lietotaja parole
	* @return string atgrie˛ jaunu API atslegu
	*/
	function apikeyAdd($username, $password) {
		$params = array();
		$params["username"] = $username;
		$params["password"] = $password;
		return $this->callServer("apikeyAdd", $params);
	}

	/**
	* Atzimejam ka neaktivu API atslegu
	*
	* @example mgapi_apikeyExpire.php
	* @example xml-rpc_apikeyExpire.php
	*
	* @param string $username lietotaja vards
	* @param string $password lietotaja parole
	* @return boolean true, ja izdevas nomainit statusu
	*/
	function apikeyExpire($username, $password) {
		$params = array();
		$params["username"] = $username;
		$params["password"] = $password;
		return $this->callServer("apikeyExpire", $params);
	}

	/**
	* Atrodam API atslegu
	*
	* @example mgapi_login.php
	* @example xml-rpc_login.php
	*
	* @param string $username lietotaja vards
	* @param string $password lietotaja parole
	* @return string tiek atgriezta API atslega, ja tadas vel nav, tad tiek izveidota
	*/
	function login($username, $password) {
		$params = array();
		$params["username"] = $username;
		$params["password"] = $password;
		return $this->callServer("login", $params);
	}

	/**
	* "ping" - vienkar veids, ka parbaudit, vai viss ir kartiba. Ja ir kadas problemas, tiks atgriezts par to pazinojums.
	*
	* @example mgapi_ping.php
	* @example xml-rpc_ping.php
	*
	* @return string tiek atgriezts teksts "Everything's Ok!", ja viss ir kartiba, ja nav, tad atgrie˛ kludas pazinojumu
	*/
	function ping() {
		$params = array();
		return $this->callServer("ping", $params);
	}

    function webhooks() {
		$params = array();
		return $this->callServer("webhooks", $params);
	}

    function webhookCreate($title, $options = NULL) {
		$params = array();
		$params["title"] = $title;
		$params["options"] = $options;
		return $this->callServer("webhookCreate", $params);
	}

    function webhookUpdate($wid, $name, $value) {
		$params = array();
		$params["wid"] = $wid;
		$params["name"] = $name;
		$params["value"] = $value;
		return $this->callServer("webhookUpdate", $params);
	}

    function webhookDelete($wid) {
		$params = array();
		$params["wid"] = $wid;
		return $this->callServer("webhookDelete", $params);
	}

    function webhookReplicate($wid) {
		$params = array();
		$params["wid"] = $wid;
		return $this->callServer("webhookReplicate", $params);
	}

	/**
	* Piesledzas pie servera uz izsauc nepiecieamo metodi un atgrie˛ rezultatu
	* o funkciju nav nepiecieams izsaukt manuali
	*/
	function callServer($method, $params) {
		$host = $this->apiUrl["host"];
		$params["apikey"] = $this->api_key;

		$this->errorMessage = "";
		$this->errorCode = "";
		$post_vars = $this->httpBuildQuery($params);

		$payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
		$payload .= "Host: " . $host . "\r\n";
		$payload .= "User-Agent: MGAPI/" . $this->version ."\r\n";
		$payload .= "Content-type: application/x-www-form-urlencoded\r\n";
		$payload .= "Content-length: " . strlen($post_vars) . "\r\n";
		$payload .= "Connection: close \r\n\r\n";
		$payload .= $post_vars;

		ob_start();
		if ($this->secure){
			$sock = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
		} else {
			$sock = fsockopen($host, 80, $errno, $errstr, 30);
		}
		if(!$sock) {
			$this->errorMessage = "Could not connect (ERR $errno: $errstr)";
			$this->errorCode = "-99";
			ob_end_clean();
			return false;
		}

		$response = "";
		fwrite($sock, $payload);
		stream_set_timeout($sock, $this->timeout);
		$info = stream_get_meta_data($sock);
		while ((!feof($sock)) && (!$info["timed_out"])) {
			$response .= fread($sock, $this->chunkSize);
			$info = stream_get_meta_data($sock);
		}
		if ($info["timed_out"]) {
			$this->errorMessage = "Could not read response (timed out)";
			$this->errorCode = -98;
		}
		fclose($sock);
		ob_end_clean();
		if ($info["timed_out"]) return false;

		list($throw, $response) = explode("\r\n\r\n", $response, 2);

		if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);

		$serial = unserialize($response);
		if($response && $serial === false) {
			$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
		} else {
			$response = $serial;
		}
		if(is_array($response) && isset($response["error"])) {
			$this->errorMessage = $response["error"];
			$this->errorCode = $response["code"];
			return false;
		}

		return $response;
	}

	/**
	* Definejam funkciju, kas aizstaj http_build_query sistemam, kuras tas nav
	*/
	function httpBuildQuery($params, $key = NULL) {
		if(!function_exists('http_build_query')) {
			$ret = array();

			foreach((array) $params as $name => $val) {
				$name = urlencode($name);
				if($key !== null) {
					$name = $key . "[" . $name . "]";
				}

				if(is_array($val) || is_object($val)) {
						$ret[] = $this->httpBuildQuery($val, $name);
				} elseif($val !== null) {
					$ret[] = $name . "=" . urlencode($val);
				}
			}

			return implode("&", $ret);
		} else {
			return http_build_query((array)$params, $key);
		}
	}
}

?>
