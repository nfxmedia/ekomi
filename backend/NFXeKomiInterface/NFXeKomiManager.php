<?php

namespace ShopwarePlugins\NFXeKomiInterface;
define("EKOMI_RESPONSE_TIMEOUT", 5);

require_once "includes/func.api.soap.php";
require_once "includes/func.ekomi.php";
require_once "includes/func.log.php";

/**
 * nfx eKomi Interface
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2013, nfx:MEDIA
 * @author Nick Fingerhut, info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage nfxeKomiInterface
 */
class NFXeKomiManager {

    const LOGFILE = "/Logs/logfile<>.log";
    const DEBUGFILE = "/Logs/debug<>.txt";
    const DEBUG_SILENT = TRUE;

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
    private $DEBUG_MODE;
    private $ALWAYS_ACTIVE;
    private $ALWAYS_INACTIVE;

    /**
     * @var \Shopware\Models\Mail\Repository
     */
    protected $repository = null;

    /**
     * constructs the ekomi functionality management class
     */
    function __construct() {
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
                        
            $this->removeOldFiles(30);
        } catch (\Exception $ex) {
            $this->logError($ex, __METHOD__);
        }
    }

    /**
     * An action that internally calls the "manage" methods
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function manageAction() {
        $this->manage();
    }

    /**
     * initiates the products and orders exporting functionality
     * And after that part is ready initiates the product and order reviews retrieving and locally storing functionality
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function manage() {
        $this->initializeEmailTemplate();
        $this->manageProducts();
        $this->manageOrders();

        if ($this->pConfig->product_review) {
            $this->loadProductReviews();
        }
    }

    /**
     * initializes the email template according to the email setting in shopware and eKomi
     *
     */
    private function initializeEmailTemplate() {
        $this->emailTemplate = array();
        $this->emailTemplate["from_address"] = $this->pConfig->email_sender_address;
        $this->emailTemplate["from_name"] = $this->pConfig->email_sender_name;
        
        if ($this->emailTemplateType == "ekomi") {
            $settings = get_settings();
            $this->emailTemplate["subject"] = $settings["mail_subject"];
            $this->emailTemplate["content_html"] = $settings["mail_html"];
            $this->emailTemplate["content_plain"] = $settings["mail_plain"];
        }

        $this->emailTemplate["delay"] = $settings["mail_delay"];
    }

    /**
     * exports products to eKomi and marks them as exported
     */
    private function manageProducts() {
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
                ORDER BY 1 LIMIT 0, 1000;";
        $products = Shopware()->db()->fetchAll($sql);
        $sentCount = 0;
        
        foreach ($products as $product) {
            try {
                if (!empty($product['product_id'])) {
                    $ret = send_product($product);
                    $doneAt = $ret["done_at"];
                    $sql = "INSERT INTO `nfx_ekomi_products` (articleID, product_id, sent_time) VALUES (?, ?, ?);";
                    Shopware()->db()->query($sql, array($product['article_id'], $product['product_id'], $doneAt));
                    $sentCount++;
                }
            } catch (\Exception $ex) {
                $this->logError($ex, __METHOD__);
            }
        }

        $this->logMessage("[" . date("Y-m-d H:i:s") . "]: OK: " . $sentCount . " products were exported to eKomi\r\n", self::LOGFILE, "a+", false);
    }

    /**
     * exports products to eKomi and marks them as exported by saving the review link returned by eKomi.
     */
    private function manageOrders() {
        $maxTime = time() - $this->pConfig->max_order_dates * 24 * 60 * 60;
        $oldestTime = strtotime($this->pConfig["oldestTime"]);
        if (!$oldestTime) {
            $oldestTime = 0;
        }

        $optInCheck = "";

        if ($this->pConfig["opt_in"] !== "2") {
            $optInCheck = " INNER JOIN `nfx_ekomi_opt_in_orders` AS eoo ON CONVERT(eoo.`ordernumber`USING utf8) = CONVERT(od.`ordernumber` USING utf8) ";
        }
        $sentCount = 0;
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
        
        $review_email_mode = $this->pConfig->review_email_mode;
        $b_order_status = $this->pConfig->review_email_before_order_status;
        $a_order_status = $this->pConfig->review_email_after_order_status;
        $order_max_days = $this->pConfig->max_days_for_order_change;
        

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

            $shippingCheck = " INNER JOIN (SELECT orderID, MIN(change_date) AS shipping_date FROM s_order_history WHERE order_status_id = $orderStatus AND IFNULL(previous_order_status_id,0) <> $orderStatus GROUP BY orderID) shipping_orders_hist 
                ON o.id = shipping_orders_hist.orderid AND 
                DATEDIFF(shipping_orders_hist.shipping_date, o.ordertime) <= 7 AND o.ordertime > '2015-01-13' ";

            $additionalFilter = " AND o.ordertime > '2015-01-13' ";
        }

        if($review_email_mode == 0) {

            $review_mode = " LEFT JOIN (SELECT orderID, MAX(change_date) AS c_date FROM s_order_history GROUP BY orderID) AS t1 ON o.id = t1.orderID ";
            $review_mode_check = " c_orderdate < FROM_UNIXTIME($maxTime) AND ";
            $review_mode_order_check = " ";
            $review_mode_0_order_status = " AND o.status = $orderStatus ";


        } else if($review_email_mode == 1) {
            $review_mode = "   INNER JOIN s_order_history oh1
              ON oh1.orderID = o.id
                INNER JOIN s_order_history oh2
              ON oh2.orderID = o.id
            ";
            $review_mode_check = " ";
            $review_mode_order_check = " 
            AND (
                (
                    oh1.order_status_id = ".$a_order_status."
                    AND oh1.previous_order_status_id = ".$b_order_status."
                    AND ABS(TIMESTAMPDIFF(DAY, o.ordertime, oh1.change_date)) <= ".$order_max_days."
                    AND oh2.order_status_id = ".$a_order_status."
                    AND oh2.previous_order_status_id = ".$b_order_status."
                    AND ABS(TIMESTAMPDIFF(DAY, o.ordertime, oh2.change_date)) <= ".$order_max_days."
                ) 
                OR 
                (
                    oh1.order_status_id = ".$a_order_status." AND oh2.previous_order_status_id =  ".$b_order_status." AND ABS(TIMESTAMPDIFF(DAY, oh1.change_date, oh2.change_date)) <= ".$order_max_days." AND oh1.orderID  = oh2.orderID  AND oh1.id  > oh2.id AND oh1.order_status_id != oh1.previous_order_status_id  AND oh2.order_status_id != oh2.previous_order_status_id

                ) 
            )";
            $review_mode_0_order_status = "";
        }

        $sql = "SELECT * FROM (
                    SELECT od.`orderID`, 
                            od.`ordernumber`, 
                            ad.`$selector` AS product_id, 
                            od.`articleID`,
                            u.`email`, 
                            o.`ordertime` AS c_orderdate
                    FROM s_order AS o
                    INNER JOIN s_order_details AS od ON o.id = od.`orderID` AND od.modus = 0 $review_mode_0_order_status  
                    INNER JOIN s_articles_details ad ON od.articleId = ad.articleId AND od.articleordernumber = ad.ordernumber
                    $shippingCheck
                    INNER JOIN s_user AS u ON o.`userID` = u.id 
                    $germanClientsCheck
                    LEFT JOIN `nfx_ekomi_orders` AS eo ON od.`orderID` = eo.`order_id` 
                    $review_mode
                    $productsJoin
                    $optInCheck
                    WHERE eo.`order_id` IS NULL $additionalFilter
                    $review_mode_order_check
            ) AS t WHERE  $review_mode_check c_orderdate > FROM_UNIXTIME($oldestTime)";

        $tempOrders = Shopware()->db()->fetchAll($sql);
        //---grouping orders
        
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
            $order["email"] = $tempOrder["email"];

            $orders[$tempOrder["orderID"]] = $order;
        }
        $sql = "SELECT email FROM nfx_ekomi_blacklist;";
        $blacklist = Shopware()->db()->fetchAll($sql);

        foreach ($orders as $order) {
            try {
                $isInBlack = false;

                foreach ($blacklist as $black) {
                    if (fnmatch($black["email"], $order["email"])) {
                        $isInBlack = true;
                        break;
                    }
                }

                if ($isInBlack) {
                    continue;
                }

                unset($order["email"]);
                $customer = array('order_id' => $order[$this->pConfig->order_id]);
                $ret = send_order($customer, $order["product_id"]);

                $orderId = $order["orderID"];
                $productId = $order["product_id"];
                $ekomiLink = $ret["link"];
                $doneAt = $ret["done_at"];

                $isSent = 0;

                if ($ret["known_since"]) {
                    $isSent = 1;
                }

                $sql = "INSERT INTO `nfx_ekomi_orders` (order_id, product_id, ekomi_link, sent_time, is_sent) VALUES (? , ?, ?, '$doneAt',  ?);";
                Shopware()->db()->query($sql, array($orderId, $productId, $ekomiLink, $isSent));
                $sentCount++;
            } catch (\Exception $ex) {
                $this->logError($ex, __METHOD__);
            }
        }

        $this->logMessage("[" . date("Y-m-d H:i:s") . "]: OK: " . $sentCount . " orders were exported to eKomi\r\n", self::LOGFILE, "a+", false);
    }

     /**
     * prepares the emails with review links to the customers that are not in the blacklist
     * and internally passes to the "sendEmail" method for sending.
     */
    public function sendEmails() {
        $sentCount = 0;

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
                  ) $whereClause;";
        $orders = Shopware()->db()->fetchAll($sql);

        $sql = "SELECT email FROM nfx_ekomi_blacklist;";
        $blacklist = Shopware()->db()->fetchAll($sql);

        foreach ($orders as $order) {
            try {
                $isInBlack = false;
                foreach ($blacklist as $black) {
                    if (fnmatch($black["email"], $order["email"])) {
                        $isInBlack = true;
                        break;
                    }
                }

                if ($isInBlack) {
                    continue;
                }

                $orderData = array();
                $orderData["email"] = $order["email"];
                $orderData["firstname"] = $order["firstname"];
                $orderData["lastname"] = $order["lastname"];
                $orderData["link"] = $order["ekomi_link"];
                $orderData["salutation"] = "";
                if (strtolower($order["salutation"]) == "mr") {
                    $orderData["salutation"] = "Herr";
                } elseif (strtolower($order["salutation"]) == 'ms' || strtolower($order["salutation"]) == 'mrs') {
                    $orderData["salutation"] = "Frau";
                }

                $this->sendEmail($orderData);

                $orderId = $order["orderID"];
                $time = time();
                $sql = "UPDATE `nfx_ekomi_orders` SET is_sent = 1, `email_sent_time` = $time WHERE order_id = $orderId;";
                Shopware()->db()->query($sql);
                $sentCount++;
            } catch (\Exception $ex) {
                $this->logError($ex, __METHOD__);
            }
        }

        $this->logMessage("[" . date("Y-m-d H:i:s") . "]: OK: " . $sentCount . " emails were sent to customers\r\n", self::LOGFILE, "a+", false);
    }

    /**
     * send an email to the customer by using shopware email sending mechansim
     *
     * @param Array with order data  $orderData
     */
    private function sendEmail($orderData) {
        $contentHTML = $this->emailTemplate["content_html"];
        $content = $this->emailTemplate["content_plain"];

        $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail');
        $emailName = $this->pConfig->email_ekomi_name;

        $query = $this->repository->getValidateNameQuery($emailName);
        $mailModel = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if ($this->emailTemplateType == "ekomi") {
            $mailModel->setFromMail($this->pConfig->email_sender_address);
            $mailModel->setFromName($this->pConfig->email_sender_name);
        } else {
            if (!$this->emailTemplate["subject"]) {
                $this->emailTemplate["subject"] = $mailModel->getSubject();
                $this->emailTemplate["content_html"] = $mailModel->getContentHtml();
                $this->emailTemplate["content_plain"] = $mailModel->getContent();
                $content = $mailModel->getContent();
                $contentHTML = $mailModel->getContentHtml();
            }
        }

        $mailModel->setSubject($this->emailTemplate["subject"]);

        if ($this->emailTemplateType == "ekomi") {
            $content = utf8_encode($content);
            $contentHTML = utf8_encode($contentHTML);

            $content = str_replace("{vorname}", $orderData["firstname"], $content);
            $content = str_replace("{nachname}", $orderData["lastname"], $content);
            $content = str_replace("{ekomilink}", $orderData["link"], $content);
            $content = str_replace("{salutation}", $orderData["salutation"], $content);
            $mailModel->setContent($content);

            $contentHTML = str_replace("{vorname}", $orderData["firstname"], $contentHTML);
            $contentHTML = str_replace("{nachname}", $orderData["lastname"], $contentHTML);
            $contentHTML = str_replace("{ekomilink}", $orderData["link"], $contentHTML);
            $contentHTML = str_replace("{salutation}", $orderData["salutation"], $contentHTML);
            $mailModel->setContentHtml($contentHTML);

            $mailEncoding = "UTF-8";
            $mail = new \Enlight_Components_Mail($mailEncoding);
            $mail = Shopware()->TemplateMail()->loadValues($mail, $mailModel);
            $mail->addTo($orderData["email"]);
            $mail->send();
        } else {
            $this->sendShopwareTypeMail($orderData);
        }

        $this->logMessage("[" . date("Y-m-d H:i:s") . "]: OK: An email was sent to " . $orderData["email"] . " via " . $this->emailTemplateType . " setting \r\n", self::LOGFILE, "a+", false);
    }

    /**
     * Sending E-Mail with Shopware methods using createMail function
     */
    private function sendShopwareTypeMail($orderData) {
        $emailAddress = $orderData["email"];
        $mailContext = array();
        $mailContext["salutation"] = $orderData["salutation"];
        $mailContext["vorname"] = $orderData["firstname"];
        $mailContext["nachname"] = $orderData["lastname"];
        $mailContext["ekomilink"] = $orderData["link"];
        $mail = Shopware()->TemplateMail()->createMail($this->pConfig->email_ekomi_name, $mailContext);
        $mail->clearRecipients();

        $mail->addTo($emailAddress);
        $mail->send();
    }

    /**
     * Gets a snapshot from eKomi for the Widget to be implemented. This helps to reduce admin works
     */
    public function getSnapshot() {
        $certificateId = 0;
        $auth = EKOMI_API_ID . '|' . EKOMI_API_KEY;
        $sql = sprintf("SELECT * FROM `nfx_widget_code`;");
        $result = Shopware()->DB()->fetchAll($sql);
        if (count($result) > 0) {
            if ($auth == $result[0]['auth']) {
                $certificateId = $result[0]['certificate_id'];
            } else {
                $id = $result[0]['id'];
                $snapshot = get_snapshot();
                $certificateId = $snapshot['info']['ekomi_certificate_id'];
                $sql = "UPDATE `nfx_widget_code` SET `auth` = '$auth', `certificate_id`='$certificateId' WHERE `id` = $id;";
                Shopware()->DB()->query($sql);
            }
        } else {
            $snapshot = get_snapshot();
            $certificateId = $snapshot['ekomi_certificate_id'];
            if (!$certificateId) {
                $certificateId = $snapshot['info']['ekomi_certificate_id'];
            }
            $sql = "INSERT INTO `nfx_widget_code` (`id`,`auth`,`certificate_id`) VALUES ('', '$auth', '$certificateId');";
            Shopware()->DB()->query($sql);
        }
        return $certificateId;
    }

    /**
     * Loads the product reviews from eKomi and stores in the local database
     */
    private function loadProductReviews() {
        try {
            $count_votes = 0;
            $sum_votes = 0;
            $sql = "SELECT UNIX_TIMESTAMP(MAX(`time`)) FROM `nfx_ekomi_product_reviews`;";
            $maxTime = Shopware()->db()->fetchOne($sql);
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
                                //the article was not matched
                                throw new \Exception("The article $product_id was not matched");
                            }
                        } catch (\Exception $ex) {
                            $this->logError($ex, __METHOD__);
                        }
                    }
                }
                fclose($file_handle);
                $this->logMessage("[" . date("Y-m-d H:i:s") . "]: OK: " . $count_votes . " ratings were added - sum of ratings = " . $sum_votes . "\r\n", self::LOGFILE, "a+", false);
            } else {
                throw new \Exception("ERROR opening " . $this->nfxFEED_URL);
            }
        } catch (\Exception $ex) {
            $this->logError($ex, __METHOD__);
        }
    }

    /**
     * logs errors if there are some
     */
    private function logError($ex, $fct) {
        $msg = "[" . date("Y-m-d H:i:s") . "]: ERROR: \r\n";
        $msg .= "           Error Source: " . $fct . "\r\n";
        $msg .= "           Caught Exception: " . $ex->getMessage() . "\r\n";

        $this->logMessage($msg, self::LOGFILE, "a+", false);
        if ($this->DEBUG_MODE == true)
            $this->logMessage($msg, self::DEBUGFILE, "a+", true);
    }

    /**
     * Logs the error message and stores it inside of the file system.
     */
    private function logMessage($msg, $f, $opt, $silent) {
        try {
            $filename = dirname(__FILE__) . str_replace("<>", date('Ymd', strtotime('Last Monday', time())), $f);
            if ($fh = @fopen($filename, $opt)) {
                fputs($fh, $msg);
                fclose($fh);
            }

            @fclose($fh);
        } catch (\Exception $ex) {
            
        }
        if (!$silent){
            if(php_sapi_name() !== 'cli'){
                echo $msg . "<br>";
            }
        }
    }

    /**
     * Delete old files
     *
     * @param <type> $folder
     * @param <type> $days
     */
    private function removeOldFiles($days) {
        $folder = realpath(dirname(__FILE__)) . "/Logs";
        if ($handle = opendir($folder)) {
            $now = date("Y-m-d");
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $filename = $folder . DIRECTORY_SEPARATOR . $file;

                    $filedate = date('Y-m-d', filemtime($filename));
                    $diff = (strtotime($now) - strtotime($filedate)) / (60 * 60 * 24); //it will count no. of days

                    if ($diff > $days) {
                        unlink($filename);
                    }
                }
            }
            closedir($handle);
        }
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

    /**
     * calculate statistics
     * @param type $params
     * @return type
     */
    public function getStatistics($params) {
        //$from_date = ($params["from_date"]) ? substr($params["from_date"], 0, 10) : date("Y-m-d", strtotime("-1 week"));
        $from_date = ($params["from_date"]) ? substr($params["from_date"], 0, 10) : "1900-01-01";
        $to_date = ($params["to_date"]) ? substr($params["to_date"], 0, 10) : date("Y-m-d", time());
        $to_date = date("Y-m-d", strtotime("+1 day", strtotime($to_date)));
        $type = ($params["type"]) ? $params["type"] : "M"; //montly, daily

        $format = '%Y-%m' . (($type == "M") ? "" : "-%d");
        $sql_1 = "SELECT DATE_FORMAT(  FROM_UNIXTIME(`email_sent_time`) ,  '" . $format . "' ) AS date
                        , COUNT(*) AS nr_emails
                    FROM nfx_ekomi_orders
                    WHERE is_sent = 1
                        AND FROM_UNIXTIME(`email_sent_time`) >= '" . $from_date . "'
                        AND FROM_UNIXTIME(`email_sent_time`) < '" . $to_date . "'
                    GROUP BY DATE_FORMAT(  FROM_UNIXTIME(`email_sent_time`) ,  '" . $format . "' )";

        $sql_2 = "SELECT DATE_FORMAT(  FROM_UNIXTIME(`sent_time`) ,  '" . $format . "' ) AS date
                    , COUNT(*) AS nr_links
                FROM nfx_ekomi_orders
                WHERE `ekomi_link` IS NOT NULL
                    AND FROM_UNIXTIME(`sent_time`) >= '" . $from_date . "'
                    AND FROM_UNIXTIME(`sent_time`) < '" . $to_date . "'
                GROUP BY DATE_FORMAT(  FROM_UNIXTIME(`sent_time`) ,  '" . $format . "' )";

        $sql_3 = "SELECT DATE_FORMAT(  `time` ,  '" . $format . "' ) AS date
                    , COUNT(*) AS nr_reviews
                FROM nfx_ekomi_product_reviews
                WHERE `time` >= '" . $from_date . "'
                    AND `time` < '" . $to_date . "'
                GROUP BY DATE_FORMAT(  `time` ,  '" . $format . "' )";
        $sql_4 = "SELECT A.date, A.nr_emails, B.nr_links FROM ($sql_1) A LEFT OUTER JOIN ($sql_2) B ON A.date = B.date
                    UNION
                  SELECT B.date, A.nr_emails, B.nr_links FROM ($sql_1) A RIGHT OUTER JOIN ($sql_2) B ON A.date = B.date";
        $sql = "SELECT C.date, C.nr_emails, C.nr_links, D.nr_reviews FROM ($sql_4) C LEFT OUTER JOIN ($sql_3) D ON C.date = D.date
                    UNION
                  SELECT D.date, C.nr_emails, C.nr_links, D.nr_reviews FROM ($sql_4) C RIGHT OUTER JOIN ($sql_3) D ON C.date = D.date
                ORDER BY 1 DESC";
        $rows = Shopware()->Db()->fetchAll($sql);

        $sql = "SELECT 'SUMME' AS date
                        , SUM(CASE WHEN is_sent = 1 THEN 1 ELSE 0 END) AS nr_emails
                        , SUM(CASE WHEN `ekomi_link` IS NOT NULL THEN 1 ELSE 0 END) AS nr_links
                    FROM nfx_ekomi_orders";

        //get totals
        $data = Shopware()->Db()->fetchAll($sql);
        if ($data) {

            $sql = "SELECT COUNT(*) AS nr_reviews
                    FROM nfx_ekomi_product_reviews";
            $data[0]["nr_reviews"] = Shopware()->Db()->fetchOne($sql);
            foreach ($rows as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * get cronjobs status
     * @return type
     */
    public function getCronjobs() {
        $sql = "SELECT
                    `s_crontab`.`name`,
                    `s_crontab`.`active`,
                    `s_crontab`.`end` AS date
                FROM
                    `s_crontab`
                WHERE `s_crontab`.`name` = 'NFXeKomiSchnittstelleImportBewertungen'
                    OR `s_crontab`.`name` = 'NFXeKomiSchnittstelleVersandBewertungsmails' ";

        $result = Shopware()->Db()->fetchAll($sql);

        return $result;
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

?>