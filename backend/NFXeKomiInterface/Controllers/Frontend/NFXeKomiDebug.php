<?php

require_once __DIR__ . "/../../includes/func.api.soap.php";
require_once __DIR__ . "/../../includes/func.ekomi.php";
require_once __DIR__ . "/../../includes/func.log.php";

/**
 * nfx eKomi Interface
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2013, nfx:MEDIA
 * @author Nick Fingerhut, info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage nfxeKomiInterface
 */
class Shopware_Controllers_Frontend_NFXeKomiDebug extends Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware {

    private $pConfig;
    private $interface_id;
    private $interface_pw;
    private $emailTemplateType;
    private $emailTemplate;
    private $nfxFEED_URL;
    private $nfxOrdersFEED_URL;
    private $ekomiVersion = "2.0.2";
    private $nfxARTICLE_ID;
    private $nfxNAME;
    private $nfxHEADLINE;
    private $nfxCOMMENT;
    private $nfxPOINTS;
    private $nfxDATUM;
    private $nfxACTIVE;
    private $nfxEMAIL;
    private $nfxFORMATNAME;
    private $ALWAYS_ACTIVE;
    private $ALWAYS_INACTIVE;

    /**
     * @var \Shopware\Models\Mail\Repository
     */
    protected $repository = null;

    /**
     * implements CSRFWhitelistAware
     * @return type
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'index'
        );
    }
    /**
     * constructs the ekomi functionality management class
     */
    function indexAction() {

        $this->View()->loadTemplate("responsive/frontend/debug/index.tpl");

        $this->View()->assign("nfx_ekomi_debug", array());

        try {
            $this->pConfig = $this->Plugin()->Config();
            $this->interface_id = trim($this->pConfig->nfxEKOMI_ID);
            $this->interface_pw = trim($this->pConfig->nfxEKOMI_PASSWORD);
            $this->emailTemplateType = $this->pConfig->email_template;
            define('EKOMI_API_ID', $this->interface_id);
            define('EKOMI_API_KEY', $this->interface_pw);
            define('EKOMI_VERSION', $this->ekomiVersion);

            $this->nfxARTICLE_ID = $this->getID($this->pConfig->nfxARTICLE_ID);
            $this->nfxNAME = $this->getID($this->pConfig->nfxNAME);
            $this->nfxHEADLINE = $this->getID($this->pConfig->nfxHEADLINE);
            $this->nfxCOMMENT = $this->getID($this->pConfig->nfxCOMMENT);
            $this->nfxPOINTS = $this->getID($this->pConfig->nfxPOINTS);
            $this->nfxDATUM = $this->getID($this->pConfig->nfxDATUM);

            $this->nfxFORMATNAME = $this->pConfig->nfxFORMATNAME;
            $this->nfxACTIVE = $this->pConfig->nfxACTIVE;
            $this->nfxEMAIL = $this->pConfig->nfxEMAIL;
            $this->ALWAYS_ACTIVE = $this->pConfig->ALWAYS_ACTIVE;
            $this->ALWAYS_INACTIVE = $this->pConfig->ALWAYS_INACTIVE;

            $this->nfxFEED_URL = "http://api.ekomi.de/get_productfeedback.php?interface_id=" . $this->interface_id . "&interface_pw=" . $this->interface_pw . "&type=csv&product=all";
            $this->nfxOrdersFEED_URL = "http://api.ekomi.de/get_feedback.php?interface_id=" . $this->interface_id . "&interface_pw=" . $this->interface_pw . "&type=csv&product=all";
        } catch (Exception $ex) {
            
        }

        $this->authenticate();

        $phpVersion = phpversion();

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["version"]["php"] = $phpVersion;
        if (function_exists("apache_get_version")) {
            $apacheVersion = apache_get_version();
            $nfx_ekomi_debug["version"]["apache"] = $apacheVersion;
        }
        $nfx_ekomi_debug["plugin_config"]["interface ID"] = $this->interface_id;
        $nfx_ekomi_debug["plugin_config"]["interface PW"] = $this->interface_pw;
        $nfx_ekomi_debug["plugin_config"]["Bestellnummer für Export"] = $this->pConfig->order_id;
        $nfx_ekomi_debug["plugin_config"]["Artikelnummer für Export"] = $this->pConfig->article_id;
        $nfx_ekomi_debug["plugin_config"]["Bestellstatus"] = $this->pConfig->order_status;
        $nfx_ekomi_debug["plugin_config"]["Versand Bewertungsmail"] = $this->pConfig->opt_in;
        $nfx_ekomi_debug["plugin_config"]["Bestellungen ignorieren vor"] = $this->pConfig->oldestTime;

        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);

        $this->manageProducts();
        $this->manageOrders();
        $this->sendEmails();
        $this->getOrders();
        $this->loadProductReviews();
    }

    private function authenticate() {
        $username = $this->Request()->getParam('user', '');
        $password = $this->Request()->getParam('pass', '');
        if (empty($this->pConfig->debug_token) || $username !== "nfx_ekomi_support_team" || $password !== $this->pConfig->debug_token) {
            $this->noAuth();
        }
    }

    private function noAuth() {
        header("HTTP/1.1 401 Unauthorized");
        header('Content-type: application/json');
        $response = \Zend_Json::encode(array('success' => false, 'message' => 'Invalid or missing auth'));
        echo $response;
        exit;
    }

    private function manageProducts() {
        $returnData = array();
        try {
            $limit = $this->Request()->getParam('manage_products_limit', 1000);
            $offset = $this->Request()->getParam('manage_products_offset', 0);
            $selector = "articleID";
            if ($this->pConfig->article_id) {
                $selector = $this->pConfig->article_id;
            }
            if($selector == "articleID"){
                $productsJoin = " LEFT JOIN `nfx_ekomi_products` ON s_articles_details.`$selector` = nfx_ekomi_products.`product_id` ";
            } else {
                $productsJoin = " LEFT JOIN `nfx_ekomi_products` ON CONVERT(s_articles_details.`$selector` USING utf8) = CONVERT(nfx_ekomi_products.`product_id` USING utf8) ";
            }
            $sql = "SELECT DISTINCT s_articles_details.`$selector` AS product_id, 
        		s_articles.id as `article_id`,	
                        s_articles.`name` AS product_name 
                    FROM `s_articles` 
                    INNER JOIN `s_articles_details` ON s_articles.id = `s_articles_details`.`articleID`
                    $productsJoin 
                    WHERE nfx_ekomi_products.`product_id` IS NULL
                    ORDER BY 1 LIMIT $offset, $limit;";
            $products = Shopware()->db()->fetchAll($sql);
            $returnData['sql'] = $sql;
            $returnData['products'] = $products;
        } catch (Exception $ex) {
            $returnData['errorMessages'][] = $ex->getMessage();
        }

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["manage_products"]["returnData"] = $returnData;
        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);
    }

    private function manageOrders() {
        $returnData = array();
        try {
            $limit = $this->Request()->getParam('manage_orders_limit', 1000);
            $offset = $this->Request()->getParam('manage_orders_offset', 0);
            $maxTime = time() - $this->pConfig->max_order_dates * 24 * 60 * 60;
            $oldestTime = strtotime($this->pConfig["oldestTime"]);
            if (!$oldestTime) {
                $oldestTime = 0;
            }

            $optInCheck = "";

            if ($this->pConfig["opt_in"] !== "2") {
                $optInCheck = " INNER JOIN `nfx_ekomi_opt_in_orders` AS eoo ON CONVERT(eoo.`ordernumber`USING utf8) = CONVERT(od.`ordernumber` USING utf8) ";
            }
            $orderStatus = $this->pConfig->order_status;
            $selector = "articleID";
            if ($this->pConfig->article_id) {
                $selector = $this->pConfig->article_id;
            }
            if($selector == "articleID"){
                $productsJoin = " INNER JOIN `nfx_ekomi_products` AS ep ON ad.`$selector` = ep.`product_id` ";
            } else {
                $productsJoin = " INNER JOIN `nfx_ekomi_products` AS ep ON CONVERT(ad.`$selector` USING utf8) = CONVERT(ep.`product_id` USING utf8) ";
            }
            $germanClientsCheck = " ";
            $shippingCheck = " ";
            $additionalFilter = " ";
            if (strpos($this->Plugin()->getHost(), "ABC") !== false) {
                if ($this->Plugin()->checkShopwareFive2()) {
                    $germanClientsCheck = " INNER JOIN `s_user_addresses`  AS ub
                                            ON u.id = ub.user_id AND u.default_billing_address_id = ub.id 
                                        INNER JOIN `s_core_countries` cc
                                            ON ub.country_id = cc.id AND cc.countryiso = 'DE' ";
                } else {
                    $germanClientsCheck = " INNER JOIN `s_user_billingaddress`  AS ub
                                            ON u.id = ub.userID 
                                        INNER JOIN `s_core_countries` cc
                                            ON ub.countryID = cc.id AND cc.countryiso = 'DE' ";
                }
                $shippingCheck = " INNER JOIN (SELECT orderID,
                                                    MIN(change_date) AS shipping_date
                                            FROM s_order_history 
                                            WHERE order_status_id = $orderStatus
                                            AND IFNULL(previous_order_status_id,0) <> $orderStatus
                                            GROUP BY orderID) shipping_orders_hist 
                                                            ON o.id = shipping_orders_hist.orderid 
                                                                 AND DATEDIFF(shipping_orders_hist.shipping_date, o.ordertime) <= 7 
                                                                 AND o.ordertime > '2015-01-13' ";
                $additionalFilter = " AND o.ordertime > '2015-01-13' ";
            }
            $sql = "SELECT * FROM (
                            SELECT od.`orderID`, 
                                    od.`ordernumber`, 
                                    ad.`$selector` AS product_id, 
                                    od.`articleID`, 
                                    u.`email`, 
                                    IF(c_date,c_date,o.`ordertime`) AS c_orderdate 
                            FROM s_order AS o
                            INNER JOIN s_order_details AS od ON o.id = od.`orderID` AND od.modus = 0 AND o.status = $orderStatus  
                            INNER JOIN s_articles_details ad ON od.articleId = ad.articleId AND od.articleordernumber = ad.ordernumber
                            $shippingCheck
                            INNER JOIN s_user AS u ON o.`userID` = u.id 
                            $germanClientsCheck
                            LEFT JOIN `nfx_ekomi_orders` AS eo ON od.`orderID` = eo.`order_id` 
                            LEFT JOIN (SELECT orderID, MAX(change_date) AS c_date FROM s_order_history GROUP BY orderID) AS t1 ON o.id = t1.orderID 
                            $productsJoin 
                            $optInCheck
                            WHERE eo.`order_id` IS NULL $additionalFilter
                    ) AS t 
                    WHERE c_orderdate < FROM_UNIXTIME($maxTime) AND c_orderdate > FROM_UNIXTIME($oldestTime) 
                    LIMIT $offset, $limit;";
            $tempOrders = Shopware()->db()->fetchAll($sql);

            $returnData['sql'] = $sql;
            $returnData['tempOrders'] = $tempOrders;
            $orders = array();

            foreach ($tempOrders as $tempOrder) {
                $product_id = $tempOrder["product_id"];
                if (isset($orders[$tempOrder["orderID"]])) {
                    $orders[$tempOrder["orderID"]]["product_id"] .= ",$product_id";
                    continue;
                }

                $order = array();
                $order["orderID"] = $tempOrder["orderID"];
                $order["ordernumber"] = $tempOrder["ordernumber"];
                $order["product_id"] = $product_id;

                $orders[$tempOrder["orderID"]] = $order;
            }
            $returnData['orders'] = $orders;

            $sql = "SELECT email FROM nfx_ekomi_blacklist;";
            $blacklist = Shopware()->db()->fetchAll($sql);
            $returnData['blacklist'] = $blacklist;
        } catch (Exception $ex) {
            $returnData['errorMessages'][] = $ex->getMessage();
        }

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["manage_orders"]["returnData"] = $returnData;
        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);
    }

    private function sendEmails() {
        $returnData = array();
        try {
            $limit = $this->Request()->getParam('send_emails_limit', 1000);
            $offset = $this->Request()->getParam('send_emails_offset', 0);
            $this->initializeEmailTemplate();
            $this->emailTemplate["delay"] = 0;
            $timeSent = time() - $this->emailTemplate["delay"] * 24 * 60 * 60;
            $selectAttr = ', customDelay';
            $innerJoin = 'INNER JOIN (SELECT `s_order_details`.`orderID` AS orderId, 
                                            MAX(s_articles_attributes.nfx_ekomiintervaldays) AS customDelay 
                                    FROM `s_order_details` 
                                    INNER JOIN s_articles_details ON s_articles_details.`articleID` = s_order_details.`articleID` 
                                    INNER JOIN `s_articles_attributes` ON s_articles_details.`id` = s_articles_attributes.`articleDetailsID` 
                                    GROUP BY orderId) delay_table ON `delay_table`.`orderID` = `s_order_details`.`orderID`';
            $whereClause = "WHERE nfx_ekomi_orders.sent_time < IF(customDelay,UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL customDelay DAY)), $timeSent)";
            if ($this->Plugin()->checkShopwareFive2()) {
                $billingAddressJoin = " INNER JOIN s_user_addresses AS ub ON s_user.`id` = ub.`user_id` AND s_user.default_billing_address_id = ub.id ";
            } else {
                $billingAddressJoin = " INNER JOIN s_user_billingaddress AS ub ON s_user.`id` = ub.`userID` ";
            }
            $sql = "SELECT DISTINCT 
                      s_order_details.`orderID`,
                      s_user.`email`,
                      nfx_ekomi_orders.`ekomi_link`,
                      ub.`salutation`,
                      ub.`firstname`,
                      ub.`lastname` 
                      $selectAttr 
                    FROM
                      (
                        (
                          (
                            (
                              s_order 
                              INNER JOIN s_order_details 
                                ON s_order.id = s_order_details.`orderID`
                              $innerJoin
                            ) 
                            INNER JOIN s_user 
                              ON s_order.`userID` = s_user.id
                          ) 
                          INNER JOIN `nfx_ekomi_orders` 
                            ON s_order_details.`orderID` = `nfx_ekomi_orders`.`order_id` 
                            AND nfx_ekomi_orders.is_sent = 0
                        ) 
                        $billingAddressJoin
                      ) $whereClause LIMIT $offset, $limit;";
            $orders = Shopware()->db()->fetchAll($sql);
            $returnData['sql'] = $sql;
            $returnData['orders'] = $orders;

            $sql = "SELECT email FROM nfx_ekomi_blacklist;";
            $blacklist = Shopware()->db()->fetchAll($sql);
            $returnData['blacklist'] = $blacklist;
        } catch (Exception $ex) {
            $returnData['errorMessages'][] = $ex->getMessage();
        }

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["send_emails"]["returnData"] = $returnData;
        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);
    }

    private function getOrders() {
        $returnData = array();
        try {
            $limit = $this->Request()->getParam('get_orders_limit', 1000);
            $offset = $this->Request()->getParam('get_orders_offset', 0);
            $sql = "SELECT * FROM s_order ORDER BY `id` DESC LIMIT $offset, $limit;";
            $orders = Shopware()->Db()->fetchAll($sql);
            $returnData['orders'] = $orders;
        } catch (Exception $ex) {
            $returnData['errorMessages'][] = $ex->getMessage();
        }

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["get_orders"]["returnData"] = $returnData;
        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);
    }

    private function loadProductReviews() {
        $returnData = array();
        try {
            $count_votes = 0;
            $sum_votes = 0;
            $sql = "SELECT UNIX_TIMESTAMP(MAX(`time`)) FROM `nfx_ekomi_product_reviews`;";
            $maxTime = Shopware()->db()->fetchOne($sql);
            $returnData['maxTime'] = $maxTime;
            $sql_customer = $this->getNameFormat();
            //get the list with forced active status
            $tmpList = explode(",", $this->ALWAYS_ACTIVE);
            $activeStatusArr = array();
            foreach($tmpList as $tmpNr){
                if(trim($tmpNr)){
                    $sql = "SELECT articleID FROM s_articles_details WHERE ordernumber = ?";
                    $tmpId = Shopware()->Db()->fetchOne($sql, array(trim($tmpNr)));
                    if($tmpId){
                        if(!in_array($tmpId, $activeStatusArr)){
                            $activeStatusArr[] = $tmpId;
                        }
                    }
                }
            }
            //get the list with forced inactive status
            $tmpList = explode(",", $this->ALWAYS_INACTIVE);
            $inactiveStatusArr = array();
            foreach($tmpList as $tmpNr){
                if(trim($tmpNr)){
                    $sql = "SELECT articleID FROM s_articles_details WHERE ordernumber = ?";
                    $tmpId = Shopware()->Db()->fetchOne($sql, array(trim($tmpNr)));
                    if($tmpId){
                        if(!in_array($tmpId, $inactiveStatusArr)){
                            $inactiveStatusArr[] = $tmpId;
                        }
                    }
                }
            }

            if (($file_handle = fopen($this->nfxFEED_URL, "r")) !== FALSE) {
                while (($line_of_text = fgetcsv($file_handle)) !== FALSE) {
                    // get value from csv
                    $unixtime = ($this->nfxDATUM) ? $line_of_text[$this->nfxDATUM - 1] : '';
                    $order_id = ($this->nfxNAME) ? str_replace("'", "''", $line_of_text[$this->nfxNAME - 1]) : '';
                    $product_id = ($this->nfxARTICLE_ID) ? $line_of_text[$this->nfxARTICLE_ID - 1] : '';
                    $rating = ($this->nfxPOINTS) ? intval($line_of_text[$this->nfxPOINTS - 1]) : '';
                    $headline = ($this->nfxHEADLINE) ? str_replace("'", "''", $line_of_text[$this->nfxHEADLINE - 1]) : '';
                    $review = ($this->nfxCOMMENT) ? str_replace("'", "''", $line_of_text[$this->nfxCOMMENT - 1]) : '';                    
                    //skip \n
                    $review = str_replace("\\n", "\n", $review);
                    $headline = str_replace("\\n", "\n", $headline);
                    if ($unixtime > $maxTime) {
                        try {
                            if($this->Plugin()->checkShopwareFive2()){
                                $sqlBillingAddress = "SELECT $sql_customer AS name
                                                        FROM s_order o
                                                        JOIN s_user
                                                                ON o.userID = s_user.id
                                                        JOIN s_user_addresses u 
                                                                ON s_user.id = u.user_id AND s_user.default_billing_address_id = u.id ";
                            } else {
                                $sqlBillingAddress = "SELECT $sql_customer AS name
                                                        FROM s_order o
                                                        JOIN s_user_billingaddress u 
                                                                ON o.userID = u.id ";
                            }
                            $orderid = array("id", "ordernumber");
                            if ($this->pConfig->order_id == "ordernumber") {
                                $orderid = array("ordernumber", "id");
                            }
                            //get customer name from s_order.id = $order_id
                            $sql = $sqlBillingAddress . " WHERE o." . $orderid[0] . " = ?";
                            $customer_name = Shopware()->Db()->fetchOne($sql, array($order_id));
                            if (!$customer_name) {
                                //get customer name from s_order.ordernumber = $order_id
                                $sql = $sqlBillingAddress . " WHERE o." . $orderid[1] . " = ?";
                                $customer_name = Shopware()->Db()->fetchOne($sql, array($order_id));
                            }
                            $returnData['customer_name'][] = $customer_name;
                            $articleID = false;
                            if ($this->pConfig->article_id == "articleID") {
                                $sql = "SELECT articleID
	                                    FROM s_articles_details
	                                    WHERE STRCMP( articleID,  ? ) =0";
                                $articleID = Shopware()->Db()->fetchOne($sql, array($product_id));
                            } else if ($this->pConfig->article_id == "ordernumber") {
                                $sql = "SELECT articleID
                                    FROM s_articles_details
                                    WHERE CONVERT(ordernumber USING utf8) = CONVERT(? USING utf8)";
                                $articleID = Shopware()->Db()->fetchOne($sql, array($product_id));
                            } else if ($this->pConfig->article_id == "suppliernumber") {
                                $sql = "SELECT articleID
                                    FROM s_articles_details
                                    WHERE CONVERT(suppliernumber USING utf8) = CONVERT(? USING utf8)";
                                $articleID = Shopware()->Db()->fetchOne($sql, array($product_id));
                            }
                            $returnData['articleID'][] = $articleID;
                            if ($articleID) {
                                $sql = "INSERT INTO `nfx_ekomi_product_reviews` (product_id, order_id, rating, review, `time`) "
                                        . "VALUES (?, ?, ?, ?, FROM_UNIXTIME(?));";

                                Shopware()->db()->query($sql, array($articleID, $order_id, $rating, $review, $unixtime));

                                $sql = "SELECT id FROM s_articles_vote "
                                        . "WHERE articleID = ? AND name = ? AND points = ? AND datum = FROM_UNIXTIME(?);";
                                $exists = Shopware()->Db()->fetchOne($sql, array($articleID, $customer_name, $rating, $unixtime));

                                if (!$exists) {
                                    $active = $this->nfxACTIVE;
                                    if(in_array($articleID, $activeStatusArr)){
                                        $active = 1;
                                    }
                                    if(in_array($articleID, $inactiveStatusArr)){
                                        $active = 0;
                                    }
                                    $sql = "INSERT INTO s_articles_vote SET headline = ?, comment = ?, articleID = ?, 
                                                                                    name = ?, email = ?, points = ?, 
                                                                                     active = ?, datum = FROM_UNIXTIME(?);";
                                    Shopware()->db()->query($sql, array($headline, $review, $articleID, $customer_name, $this->nfxEMAIL, $rating, $active, $unixtime));
                                    $count_votes++;
                                    $sum_votes += $rating;
                                }
                            } else {
                                $returnData['errorMessages'][] = "The article $product_id was not matched";
                            }
                        } catch (Exception $ex) {
                            $returnData['errorMessages'][] = $ex->getMessage();
                        }
                    }
                }
                fclose($file_handle);
            } else {
                $returnData['errorMessages'][] = "ERROR opening " . $this->nfxFEED_URL;
            }
        } catch (Exception $ex) {
            $returnData['errorMessages'][] = $ex->getMessage();
        }

        $assign = $this->View()->getAssign();
        $nfx_ekomi_debug = $assign["nfx_ekomi_debug"];
        $nfx_ekomi_debug["load_product_reviews"]["returnData"] = $returnData;
        $this->View()->assign("nfx_ekomi_debug", $nfx_ekomi_debug);
    }

    /**
     * get id column from config: "Column N" returns "N"
     * @return string
     */
    private function getID($text) {
        $values = array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback'));
        $return = "";
        foreach ($values as $arr) {
            if (($text === $arr[0]) || ($text === $arr[1])) {
                $return = $arr[0];
                break;
            }
        }
        return $return;
    }

    private function initializeEmailTemplate() {
        $this->emailTemplate = array();
        $this->emailTemplate["from_address"] = $this->pConfig->email_sender_address;
        $this->emailTemplate["from_name"] = $this->pConfig->email_sender_name;
    }

    /**
     * Format customer's name as it is set in config; Firstname Lastname, Firstname L. etc
     * @return string
     */
    private function getNameFormat() {
        //CONCAT(u.firstname,' ', CASE IFNULL(u.lastname,'') WHEN '' THEN '' ELSE CONCAT(LEFT(u.lastname,1), '.') END)
        $names = "";
        $values = array(array(0, 'Vorname Nachname'), array(1, 'Vorname N.'), array(2, 'V. Nachname'), array(3, 'V. N.'), array(4, 'Vorname'), array(5, 'Nachname Vorname'), array(6, 'Nachname V.'), array(7, 'N. Vorname'), array(8, 'N. V.'), array(9, 'Nachname'));
        foreach ($values as $arr) {
            if (($arr[0] === $this->nfxFORMATNAME) || ($arr[1] === $this->nfxFORMATNAME)) {
                $names = $arr[1];
            }
        }
        if (!$names) {
            return "";
        }
        $names = explode(" ", $names);
        for ($i = 0; $i < count($names); $i++) {

            $names[$i] = str_replace("Vorname", "u.firstname", $names[$i]);
            $names[$i] = str_replace("V.", "CASE IFNULL(u.firstname,'') WHEN '' THEN '' ELSE CONCAT(LEFT(u.firstname,1), '.') END", $names[$i]);
            $names[$i] = str_replace("Nachname", "u.lastname", $names[$i]);
            $names[$i] = str_replace("N.", "CASE IFNULL(u.lastname,'') WHEN '' THEN '' ELSE CONCAT(LEFT(u.lastname,1), '.') END", $names[$i]);
        }
        if (count($names) > 1) {
            $return = join(",' ',", $names);
            $return = "CONCAT($return)";
        } else {
            $return = $names[0];
        }
        return $return;
    }

    /**
     * get an instance of the plugin
     * @return type
     */
    private function Plugin() {
        return Shopware()->Plugins()->Backend()->NFXeKomiInterface();
    }

}
