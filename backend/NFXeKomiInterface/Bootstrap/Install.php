<?php

namespace ShopwarePlugins\NFXeKomiInterface\Bootstrap;

use ShopwarePlugins\NFXeKomiInterface\Bootstrap\EkomiAbstractBootstrap,
    Shopware\Models\Mail\Mail;

/**
 * The install class does the basic setup of the plugin. All operations should be implemented in a way
 * that they can also be run on update of the plugin
 *
 * Class Install
 * @package ShopwarePlugins\NFXeKomiInterface\Bootstrap
 */
class Install extends EkomiAbstractBootstrap {

    public function __construct() {
        $this->bootstrap = $this->Plugin();
    }

    public function run($type = "install") {
        $this->createEmail();
        if ($this->bootstrap->checkShopwareFive() && $type == "install") {
            $this->createEmotionComponents();
        }
        $this->createConfiguration();
        $this->createMenu();
        if($type == "install"){
            $this->addSnippets();
        }
        $this->createDatabase();
        $this->registerEvents();
        $this->registerCronJobs();
        $this->addIntervalDaysAttribute();
        $this->createErrorHandleEmail();

        return array('success' => true, 'invalidateCache' => array('backend', 'frontend', 'proxy'));
    }

    /**
     * Creates custom email template that will be used for sending review links to customers
     * in case if the "shopware" based email option is selected in the config.
     */
    private function createEmail() {
        $config = $this->bootstrap->Config();
        if ($config->email_ekomi_name != "") {
            $this->ekomi_email = $config->email_ekomi_name;
        }

        $mailRepository = $this->getMailRepository();
        $mailModel = $mailRepository->findOneBy(array(
            'name' => $this->ekomi_email
        ));
        if ($mailModel) {
            return;
        }

        $params = array();
        $params["name"] = $this->ekomi_email; //"eKomiReviewTemplate";
        $params["fromName"] = "{config name=shopName}";
        $params["fromMail"] = "{config name=mail}";
        $params["subject"] = "Bitte bewerten Sie den {config name=shopName}";
        $params["content"] = 'Guten Tag {$salutation} {$vorname} {$nachname},

Sie haben k&uuml;rzlich bei uns eingekauft - vielen Dank.

Da wir stets um einen besseren Service bem&uuml;ht sind, w&uuml;rde es uns sehr freuen, wenn sie sich wenige Sekunden Zeit nehmen um uns zu bewerten.

Bitte klicken Sie auf folgenden Link, um uns bei dem unabh&auml;ngigen Dienstleister eKomi zu bewerten: 

{$ekomilink}

Damit helfen Sie nicht nur uns, sondern auch unseren Neukunden, die richtige Entscheidung zu treffen, indem Ihre Bewertung auf unserer Internetseite ver&ouml;ffentlicht wird. 

Vielen Dank f&uuml;r Ihre Unterst&uuml;tzung. 

Ihr {config name=shopName} Team


{config name=address}';

        $params["contentHtml"] = '<div style="font-family:arial; font-size:12px;">
<p>
Guten Tag {$salutation} {$vorname} {$nachname},<br>
<br>
Sie haben k&uuml;rzlich bei uns eingekauft - vielen Dank.<br>
<br>
Da wir stets um einen besseren Service bem&uuml;ht sind, w&uuml;rde es uns sehr freuen, wenn sie sich wenige Sekunden Zeit nehmen um uns zu bewerten.<br>
<br>
Bitte klicken Sie auf folgenden Link, um uns bei dem unabh&auml;ngigen Dienstleister eKomi zu bewerten:<br> 
<br>
{$ekomilink}<br>
<br>
Damit helfen Sie nicht nur uns, sondern auch unseren Neukunden, die richtige Entscheidung zu treffen, indem Ihre Bewertung auf unserer Internetseite ver&ouml;ffentlicht wird. 
<br>
Vielen Dank f&uuml;r Ihre Unterst&uuml;tzung. <br>
<br>
Ihr {config name=shopName} Team<br>
<br>
{config name=address}
</p>
</div>';
        $params["isHtml"] = true;
        $params["attachment"] = "";
        $params["type"] = "";
        $params["context"] = "";
        $params["contextPath"] = "";
        $params["parentId"] = 0;
        $params["index"] = 0;
        $params["depth"] = 0;
        $params["expanded"] = false;
        $params["expandable"] = true;
        $params["checked"] = null;
        $params["leaf"] = false;
        $params["cls"] = "";
        $params["iconCls"] = "";
        $params["icon"] = "";
        $params["root"] = false;
        $params["isLast"] = false;
        $params["isFirst"] = false;
        $params["allowDrop"] = true;
        $params["allowDrag"] = true;
        $params["loaded"] = false;
        $params["loading"] = false;
        $params["href"] = "";
        $params["hrefTarget"] = "";
        $params["qtip"] = "";
        $params["qtitle"] = "";

        $params["children"] = null;
        $mail = new Mail();
        $params['attribute'] = "";
        $mail->fromArray($params);
        Shopware()->Models()->persist($mail);
        Shopware()->Models()->flush();

        $enShop = $this->getEnglishShopId();
        if (!empty($enShop)) {
            $enShop = $enShop[0]["id"];
            $data = array();
            $data["fromName"] = "{config name=shopName}";
            $data["fromMail"] = "{config name=mail}";
            $data["subject"] = "Please rate {config name=shopName}";
            $data["content"] = 'Good Day {$salutation} {$vorname} {$nachname},

You recently shopped with us - thank you very much.

As we are trying to provide better service, we would be very grateful if you take a few seconds to rate us.

Please click on the link below in order to assess us in the independent service eKomi: 

{$ekomilink}

Your published review will help not only our old but new customers as well to make the right decision.

Thank You for Your Support. 

Your {config name=shopName} Team


{config name=address}';

            $data["contentHtml"] = '<div style="font-family:arial; font-size:12px;">
<p>
Good Day {$salutation} {$vorname} {$nachname},<br>
<br>
You recently shopped with us - thank you very much.<br>
<br>
As we are trying to provide better service, we would be very grateful if you take a few seconds to rate us.<br>
<br>
Please click on the link below in order to assess us in the independent service eKomi:<br> 
<br>
{$ekomilink}<br>
<br>
Your published review will help not only our old but new customers as well to make the right decision. 
<br>
Thank You for Your Support.  <br>
<br>
Your {config name=shopName} Team<br>
<br>
{config name=address}
</p>
</div>';
            $translationReader = new \Shopware_Components_Translation();
            $translationReader->write($enShop, 'config_mails', $mail->getId(), $data);
        }

        return $mail->getId();
    }

    /**
     * Creates custom email template that will be used for sending error reports
     */
    private function createErrorHandleEmail() {
        $mailRepository = $this->getMailRepository();
        $mailModel = $mailRepository->findOneBy(array(
            'name' => $this->error_email
        ));
        if ($mailModel) {
            return;
        }

        $params = array();
        $params["name"] = $this->error_email;
        $params["fromName"] = "Error-Report";
        $params["fromMail"] = "error@feedback-ekomi.com";
        $params["subject"] = '{$subject}';
        $params["content"] = "";
        $params["contentHtml"] = '<p>{$content}</p>';
        $params["isHtml"] = true;
        $params["attachment"] = "";
        $params["type"] = "";
        $params["context"] = "";
        $params["contextPath"] = "";
        $params["parentId"] = 0;
        $params["index"] = 0;
        $params["depth"] = 0;
        $params["expanded"] = false;
        $params["expandable"] = true;
        $params["checked"] = null;
        $params["leaf"] = false;
        $params["cls"] = "";
        $params["iconCls"] = "";
        $params["icon"] = "";
        $params["root"] = false;
        $params["isLast"] = false;
        $params["isFirst"] = false;
        $params["allowDrop"] = true;
        $params["allowDrag"] = true;
        $params["loaded"] = false;
        $params["loading"] = false;
        $params["href"] = "";
        $params["hrefTarget"] = "";
        $params["qtip"] = "";
        $params["qtitle"] = "";

        $params["children"] = null;
        $mail = new Mail();
        $params['attribute'] = "";
        $mail->fromArray($params);
        Shopware()->Models()->persist($mail);
        Shopware()->Models()->flush();
    }

    /**
     * Creates emotion components
     * @return booelan
     */
    private function createEmotionComponents() {
        $componentConfig = array('name' => $this->emotionItemName,
            'template' => 'component_nfx_ekomi_widget',
            'xtype' => 'emotion-components-html-element',
            'cls' => 'nfx-ekomi-widget');
        $component = $this->bootstrap->createEmotionComponent($componentConfig);
        return true;
    }

    /**
     * creates the configuration form of the plugin
     */
    public function createConfiguration() {
        $index = 0;
        $positions = array();
        $form = $this->bootstrap->Form();

        $form->setElement('checkbox', 'product_interval_days', array('label' => 'individueller Bewertungszeitraum für Artikel', 'value' => 0, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['product_interval_days'] = $index++;
        $form->setElement('text', 'nfxEKOMI_ID', array('label' => 'Interface ID', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxEKOMI_ID'] = $index++;
        $form->setElement('text', 'nfxEKOMI_PASSWORD', array('label' => 'Passwort', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxEKOMI_PASSWORD'] = $index++;
        $form->setElement('select', 'order_id', array('label' => 'Bestellnummer für Export', 'value' => "orderID", 'store' => array(array("orderID", "interne ID"), array("ordernumber", 'offizielle Bestellnummer')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['order_id'] = $index++;
        $form->setElement('select', 'article_id', array('label' => 'Artikelnummer für Export', 'required' => true, 'value' => "articleID", 'store' => array(array("articleID", "interne ID"), array("ordernumber", "Artikelnummer"), array("suppliernumber", 'Herstellernummer')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['article_id'] = $index++;
        $form->setElement('text', 'email_sender_name', array('label' => 'Name des E-Mail Versenders', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['email_sender_name'] = $index++;
        $form->setElement('text', 'email_sender_address', array('label' => 'E-Mail-Adresse des Versenders', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['email_sender_address'] = $index++;
        $form->setElement('text', 'email_ekomi_name', array('label' => 'Shopware Template Name', 'value' => "eKomiReviewTemplate", 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['email_ekomi_name'] = $index++;
        $form->setElement('combo', 'email_template', array('label' => 'E-Mail Template', 'value' => 'ekomi', 'store' => array(array("ekomi", "eKomi"), array("shopware", "Shopware")), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['email_template'] = $index++;
        $form->setElement('checkbox', 'overide_ratings', array('label' => 'Optimierte Darstellung der Bewertungen', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['overide_ratings'] = $index++;
        $form->setElement('checkbox', 'product_review', array('label' => 'eKomi Produktbewertungen', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['product_review'] = $index++;
        $form->setElement('checkbox', 'register_batch', array('label' => 'eKomi-Widget auf Registrierungsseite', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['register_batch'] = $index++;
        $form->setElement('checkbox', 'category_batch', array('label' => 'eKomi-Widget auf Kategorie-Ansicht', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['category_batch'] = $index++;
        $form->setElement('checkbox', 'review_rating', array('label' => 'Bewertung der Rezension', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['review_rating'] = $index++;
        $form->setElement('checkbox', 'best_worst_reviews', array('label' => 'Darstellung beste vs. schlechteste Bewertung', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['best_worst_reviews'] = $index++;
        $form->setElement('checkbox', 'manual_ratings', array('label' => 'Produktbewertungen im Shop erlauben', 'value' => 0, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['manual_ratings'] = $index++;
        $form->setElement('checkbox', 'rating_filter', array('label' => 'Filterfunktion der Bewertungen', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['rating_filter'] = $index++;
        $form->setElement('checkbox', 'ekomi_logo', array('label' => 'eKomi-Logo bei Produktbewertungen', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['ekomi_logo'] = $index++;
        $form->setElement('text', 'ekomi_icon_url', array('label' => 'eKomi Bewertungs-URL', 'value' => "", 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['ekomi_icon_url'] = $index++;

        $sql = "SELECT id, description FROM s_core_states WHERE `group` = 'state' ORDER BY `position`;";
        $orderStatusRes = Shopware()->Db()->fetchAll($sql);

        $deliveryStatuses = array();
        foreach ($orderStatusRes as $orderStatus) {
            $deliveryStatuses[] = array($orderStatus["id"], $orderStatus["description"]);
        }

        $form->setElement('combo', 'order_status', array('label' => 'Bestellstatus', 'value' => '7', 'store' => $deliveryStatuses, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['order_status'] = $index++;
        $form->setElement('combo', 'max_order_dates', array('label' => 'Frühste Bestellung (Tage vor Import)', 'value' => '7', 'store' => array(
                array("3", "3"),
                array("7", "7"),
                array("14", "14"),
                array("28", "28"),
                array("30", "30"),
                array("60", "60"),
                array("90", "90")
            ), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['max_order_dates'] = $index++;

        //v2.3.2
        $form->setElement('combo', 'review_email_mode', array('label' => 'Senden Sie die Bewertungs-E-Mail an den Benutzer, wenn sich der Bestellstatus geändert hat oder die Bestellung maximal Tage dauert', 'value' => '0', 'store' => array(
                array("0", "Nach frühester Bestellung (Tage vor dem Import)"),
                array("1", "von vorher nach dem Bestellstatus "),
            ), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['review_email_mode'] = $index++;

        $form->setElement('combo', 'review_email_before_order_status', array('label' => 'Bestellstatus (Beginn) für die Überprüfungs-E-Mail', 'value' => '0', 'store' => $deliveryStatuses, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['review_email_before_order_status'] = $index++;

        $form->setElement('combo', 'review_email_after_order_status', array('label' => 'Bestellstatus (Ziel) für die Überprüfungs-E-Mail', 'value' => '7', 'store' => $deliveryStatuses, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['review_email_after_order_status'] = $index++;

        $form->setElement('text', 'max_days_for_order_change', array('label' => 'Max. Anzahl der Tage für Auftragsänderung', 'value' => '3', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['max_days_for_order_change'] = $index++;
        //v2.3.2



        $form->setElement('combo', 'opt_in', array('label' => 'Versand Bewertungsmail', 'value' => '0', 'store' => array(
                array("0", "Opt-In (Empfehlung)"),
                array("1", "Opt-In vorausgewählt (nicht empfohlen)"),
                array("2", "ohne Zustimmung (nicht empfohlen)")
            ), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['opt_in'] = $index++;

        $form->setElement('select', 'nfxARTICLE_ID', array('label' => 'Daten f&uuml;r Feld article_id', 'value' => 3, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxARTICLE_ID'] = $index++;
        $form->setElement('select', 'nfxNAME', array('label' => 'Daten f&uuml;r Feld name', 'value' => 2, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxNAME'] = $index++;
        $form->setElement('select', 'nfxHEADLINE', array('label' => 'Daten f&uuml;r Feld headline', 'value' => 0, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxHEADLINE'] = $index++;
        $form->setElement('select', 'nfxCOMMENT', array('label' => 'Daten f&uuml;r Feld comment', 'value' => 5, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxCOMMENT'] = $index++;
        $form->setElement('select', 'nfxPOINTS', array('label' => 'Daten f&uuml;r Feld points', 'value' => 4, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxPOINTS'] = $index++;
        $form->setElement('select', 'nfxDATUM', array('label' => 'Daten f&uuml;r Feld datum', 'value' => 1, 'store' => array(array(0, '--- keine Daten importieren ---'), array(1, 'Datum'), array(2, 'Name'), array(3, 'ArticleID'), array(4, 'Rating'), array(5, 'Feedback')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxDATUM'] = $index++;
        $form->setElement('text', 'nfxACTIVE', array('label' => 'Daten f&uuml;r Feld active (konstanter Wert)', 'value' => '1', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxACTIVE'] = $index++;
        $form->setElement('text', 'nfxEMAIL', array('label' => 'Daten f&uuml;r Feld E-Mail (konstanter Wert)', 'value' => 'eKomi', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxEMAIL'] = $index++;
        $form->setElement('text', 'ALWAYS_ACTIVE', array('label' => 'Immer als aktive Bewertungen importieren f&uuml;r diese Artikel', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['ALWAYS_ACTIVE'] = $index++;
        $form->setElement('text', 'ALWAYS_INACTIVE', array('label' => 'Immer als inaktive Bewertungen importieren f&uuml;r diese Artikel', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['ALWAYS_INACTIVE'] = $index++;

        $formattedDate = date("Y-m-d H:i:s");
        $form->setElement('datetime', 'oldestTime', array('label' => 'Bestellungen ignorieren vor', 'required' => true, 'value' => $formattedDate, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['oldestTime'] = $index++;

        $form->setElement('select', 'nfxFORMATNAME', array('label' => 'Name Format', 'value' => 1, 'store' => array(array(-1, "--- keinen Namen importieren ---"), array(0, 'Vorname Nachname'), array(1, 'Vorname N.'), array(2, 'V. Nachname'), array(3, 'V. N.'), array(4, 'Vorname'), array(5, 'Nachname Vorname'), array(6, 'Nachname V.'), array(7, 'N. Vorname'), array(8, 'N. V.'), array(9, 'Nachname')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['nfxFORMATNAME'] = $index++;

        $parent = $this->bootstrap->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);

        $form->setElement('number', 'minReviews', array('label' => 'Mindestanzahl der Rezensionen für Vergleich', 'value' => 5, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['minReviews'] = $index++;
        $form->setElement('number', 'minRatings', array('label' => 'Mindestanzahl der Bewertungen für Vergleich', 'value' => 4, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['minRatings'] = $index++;

        //$form -> setElement('checkbox', 'enable_monitoring', array('label' => 'Fernwartung ermöglichen', 'value' => '1'));
        //$positions['enable_monitoring] = $index++;
        $monitoringKey = md5($this->bootstrap->getHost());
        $form->setElement('text', 'monitoring_key', array('label' => 'Fernwartungs-Key', 'value' => $monitoringKey, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['monitoring_key'] = $index++;
        $form->setElement('text', 'debug_token', array('label' => 'Debug-Token', 'value' => '', 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['debug_token'] = $index++;
        //$form->setElement('checkbox', 'ENABLE_DEBUG', array('label' => 'Debug Mode', 'value' => false));
        //$positions['ENABLE_DEBUG] = $index++;

        $form->setElement('button', 'shopware_4.x', array('label' => '<strong>Einstellungen für RichSnippets – Shopware v 4.2 und älter</strong>', 'handler' => ""));
        $positions['shopware_4.x'] = $index++;

        $form->setElement('checkbox', 'disable_price', array('label' => 'Preisausgabe im Rich-Snippet deaktivieren (Grundpreis-Problem)', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['disable_price'] = $index++;
        $form->setElement('checkbox', 'home_widget', array('label' => 'Rich Snippet für Startseite (Shopbewertung)', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['home_widget'] = $index++;
        $form->setElement('checkbox', 'detail_widget', array('label' => 'Rich Snippets für Detailseite (Produktbewertung)', 'value' => 1, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['detail_widget'] = $index++;

        $form->setElement('button', 'shopware_5.x', array('label' => '<strong>nur Responsive Shopware Template (ab Shopware 5)</strong>', 'handler' => ""));
        $positions['shopware_5.x'] = $index++;

        $form->setElement('checkbox', 'ekomi_badge', array('label' => 'eKomi Badge im Header anzeigen', 'value' => 0, 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));
        $positions['ekomi_badge'] = $index++;


        $elements = $form->getElements();
        foreach ($elements as $element) {
            if (isset($positions[$element->getName()])) {
                $element->setPosition($positions[$element->getName()]);
            } else {
                $element->setPosition(999); //this should be better deleted
            }
        }
        
        //contains all translations
        Shopware()->Db()->query("DELETE FROM s_core_config_element_translations WHERE element_id IN (SELECT id FROM s_core_config_elements WHERE form_id = ?)"
                , array($form->getId()));
        
        $translations = array(
            'en_GB' => array(
                'product_interval_days' => 'individual review period for article',
                'nfxEKOMI_ID' => 'Interface ID',
                'nfxEKOMI_PASSWORD' => 'password',
                'order_id' => 'Export order',
                'email_sender_name' => "e-mail sender's name",
                'email_sender_address' => "Sender's email address",
                'email_ekomi_name' => 'Shopware Template Name',
                'email_template' => 'E-Mail Template',
                'disable_price' => 'deactivation of pricing in rich snippets (basic price issue)',
                'overide_ratings' => 'Optimized display of ratings',
                'product_review' => 'eKomi Product Reviews',
                'register_batch' => 'eKomi widget on registration page',
                'category_batch' => 'eKomi widget for Category View',
                'detail_widget' => 'Rich Snippets for details page (Product Review)',
                'home_widget' => 'Rich Snippet for Home Page (Shop review)',
                'review_rating' => 'Review Rating',
                'best_worst_reviews' => 'best results and worst rating',
                'manual_ratings' => 'Allow Reviews in Store',
                'rating_filter' => 'Filter function of reviews',
                'ekomi_icon_url' => 'eKomi Review URL',
                'order_status' => 'Order Tracking',
                'max_order_dates' => 'The Earliest Order (days before import)',
                'review_email_mode' => 'Send the review email to the user if the order status has changed or the order takes a maximum of days',
                'review_email_before_order_status' => 'Order status (start date) for the review e-mail',
                'review_email_after_order_status' => 'Order status (destination) for the review e-mail',
                'max_days_for_order_change' => 'Max. Number of days for order change',
                'nfxARTICLE_ID' => 'Article_id data for field',
                'nfxNAME' => 'Name data for field',
                'nfxHEADLINE' => 'Data for headline field',
                'nfxCOMMENT' => 'Data for comment field',
                'nfxPOINTS' => 'Data for points field',
                'nfxDATUM' => 'Data for date field',
                'nfxACTIVE' => 'Data for active field (constant value)',
                'nfxEMAIL' => 'Data for E-Mail field (constant value)',
                'ALWAYS_ACTIVE' => 'Always import active ratings for these articles',
                'ALWAYS_INACTIVE' => 'Always import inactive ratings for these articles',
                'oldestTime' => 'Before ignoring orders',
                'nfxFORMATNAME' => 'Name Format',
                'minReviews' => 'Minimum number of reviews for the comparison',
                'minRatings' => 'Minimum number of ratings for comparison',
                'monitoring_key' => 'Remote maintenance Key',
                'ENABLE_DEBUG' => 'Debug Mode'
            ),
        );
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        //iterate the languages
        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            //not found? continue with next language
            if ($localeModel === null) {
                continue;
            }
            //iterate all snippets of the current language
            foreach ($snippets as $element => $snippet) {

                //get the form element by name
                $elementModel = $form->getElement($element);

                //not found? continue with next snippet
                if ($elementModel === null) {
                    continue;
                }

                //create new translation model
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);

                //add the translation to the form element
                $elementModel->addTranslation($translationModel);
            }
        }

        //$form->save();
    }

    /**
     * registers events
     *
     */
    private function registerEvents() {
        // Subscribe the needed event for js merge and compression
        $this->bootstrap->subscribeEvent('Theme_Compiler_Collect_Plugin_Javascript', 'addJsFiles');
        // Subscribe the needed event for less merge and compression
        $this->bootstrap->subscribeEvent('Theme_Compiler_Collect_Plugin_Less', 'addLessFiles');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_NFXeKomiInterface', 'onGetBackendController');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_NFXeKomiStatistics', 'onGetBackendStatisticsController');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_NFXeKomiStatistics', 'onGetFrontendController');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Detail', 'onPostDispatchDetail');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PreDispatch_Frontend_Detail', 'onPreDispatchDetail');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Index', 'onPostDispatchIndex');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Register', 'onPostDispatchIndex');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Listing', 'onPostDispatchIndex');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Checkout', 'onPostDispatchCheckout', 10000);
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend', 'onPostDispatchFrontend');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Campaign_Widgets', 'onPostDispatchWidgets');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Widgets_Emotion', 'onPostDispatchWidgets');
        $this->bootstrap->subscribeEvent('sArticles::sGetArticlesVotes::after', 'afterGetArticlesVotes');
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Index', 'postDispatchBackendIndex');
        // additional attribute
        $this->bootstrap->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Article', 'onLoadArticle');
        $this->bootstrap->subscribeEvent('Shopware_Modules_Articles_sGetArticlesByCategory_FilterSql', 'onFilterSql');
        $this->bootstrap->subscribeEvent('Shopware_Modules_Articles_GetArticleById_FilterSQL', 'onFilterSql');
        $this->bootstrap->subscribeEvent('Shopware_Modules_Articles_sGetProductByOrdernumber_FilterSql', 'onFilterSql');
        $this->bootstrap->subscribeEvent('Shopware_Modules_Articles_GetPromotionById_FilterSql', 'onFilterSql');
        $this->bootstrap->subscribeEvent('sExport::sCreateSql::after', 'onFilterSql');
        if ($this->bootstrap->checkShopwareFive()) {
            $this->bootstrap->registerController('Frontend', 'NFXeKomiDebug', 'onGetDebugController');
        } else {
            $this->bootstrap->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_NFXeKomiDebug', 'onGetDebugController');
        }
        $this->bootstrap->subscribeEvent(
                'sOrder::sSaveOrder::after', 'onAfterSaveOrder'
        );
    }

    /**
     * registers the cron jobs
     * - for exporting items and orders to ekomi
     * - for sending emails with review links to customers
     */
    private function registerCronJobs() {
        try {
            $sql = "SELECT id FROM s_crontab WHERE name = 'NFXeKomiSchnittstelleImportBewertungen'";
            $exists = Shopware()->Db()->fetchOne($sql);
            if (!$exists) {
                $event = $this->bootstrap->subscribeEvent('Shopware_CronJob_NFXeKomiSchnittstelleImportBewertungen', 'oneKomiCronRun');
                if ($this->checkShopwareFive()) {
                    $this->bootstrap->createCronJob("NFXeKomiSchnittstelleImportBewertungen", "NFXeKomiSchnittstelleImportBewertungen", 86400, true);
                } else {
                    $this->bootstrap->subscribeCron("NFXeKomiSchnittstelleImportBewertungen", "NFXeKomiSchnittstelleImportBewertungen", 86400, true);
                }
            }
        } catch (\Exception $ex) {
            
        }

        try {
            $sql = "SELECT id FROM s_crontab WHERE name = 'NFXeKomiSchnittstelleVersandBewertungsmails'";
            $exists = Shopware()->Db()->fetchOne($sql);
            if (!$exists) {
                $event = $this->bootstrap->subscribeEvent('Shopware_CronJob_NFXeKomiSchnittstelleVersandBewertungsmails', 'oneKomiCronRunEmail');
                if ($this->checkShopwareFive()) {
                    $this->bootstrap->createCronJob("NFXeKomiSchnittstelleVersandBewertungsmails", "NFXeKomiSchnittstelleVersandBewertungsmails", 86400, true);
                } else {
                    $this->bootstrap->subscribeCron("NFXeKomiSchnittstelleVersandBewertungsmails", "NFXeKomiSchnittstelleVersandBewertungsmails", 86400, true);
                }
            }
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * Create the backend menu items for the black list and eKomi backend options iframe
     */
    private function createMenu() {
        $parent = $this->bootstrap->Menu()->findOneBy(array('label' => 'Marketing'));
        $eKomi = $this->bootstrap->Menu()->findOneBy(array('label' => 'eKomi'));
        if (!$eKomi) {
            $eKomi = $this->bootstrap->createMenuItem(array('label' => 'eKomi', 'class' => 'sprite-credit-cards', 'active' => 1, 'parent' => $parent, 'style' => 'background-position: 5px 5px;'));
            if (!$this->checkShopwareFive2()) {
                $this->bootstrap->Menu()->addItem($eKomi);
            }

            $item = $this->bootstrap->createMenuItem(array('label' => 'eKomi Backend', 'onclick' => 'createiFrameDemo()', 'class' => 'sprite-credit-cards', 'active' => 1, 'parent' => $eKomi, 'style' => 'background-position: 5px 5px;'));
            if (!$this->checkShopwareFive2()) {
                $this->bootstrap->Menu()->addItem($item);
            }

            $item = $this->bootstrap->createMenuItem(array('label' => 'Blacklist', 'controller' => 'NFXeKomiInterface', 'action' => 'Index', 'class' => 'sprite-credit-cards', 'active' => 1, 'parent' => $eKomi, 'style' => 'background-position: 5px 5px;'));
            if (!$this->checkShopwareFive2()) {
                $this->bootstrap->Menu()->addItem($item);
            }
        }
        $item = $this->bootstrap->Menu()->findOneBy(array('label' => 'Statistik'));
        if (!$item) {
            $item = $this->bootstrap->createMenuItem(array('label' => 'Statistik', 'controller' => 'NFXeKomiStatistics', 'action' => 'Index', 'class' => 'sprite-credit-cards', 'active' => 1, 'parent' => $eKomi, 'style' => 'background-position: 5px 5px;'));
            if (!$this->checkShopwareFive2()) {
                $this->bootstrap->Menu()->addItem($item);
            }
        }
        if (!$this->checkShopwareFive2()) {
            $this->bootstrap->Menu()->save();
        }
    }

    /**
     * deletes the snippets created during the installation
     */
    private function clearSnippets() {
        // clear SW4 snippets

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4frontend_namespace_detail . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4backend_namespace_detail . "' AND name LIKE 'nfx%' OR name = 'DetailDataInfoFrom'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4frontend_namespace_index . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4backend_namespace_index . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);

        // clear SW5 snippets
        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw5namespace . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw5namespace . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);
    }

    /**
     * Creates snipptes, that can be included in the frontend templates
     */
    private function addSnippets() {
        $this->clearSnippets();
        $snippets = $this->addSnippetNamespaces($this->sw4backend_namespace_detail);
        $snippets = $this->addSnippetNamespaces($this->sw5namespace);
    }

    /**
     * Creates snipptes, that can be included in the frontend templates
     */
    private function addSnippetNamespaces($namespace) {
        $snippets = array();
        $snippets[] = array($namespace, '1', 'nfxHelpfulReviewQuestion', 'War diese Rezension für Sie hilfreich?');
        $snippets[] = array($namespace, '2', 'nfxHelpfulReviewQuestion', 'Was this review helpful to you?');
        $snippets[] = array($namespace, '1', 'nfxHelpfulReviewsCount', '<b>{$vote.rating.positive}</b> von <b>{$vote.rating.total} Kunden</b> fanden diese Bewertung hilfreich');
        $snippets[] = array($namespace, '2', 'nfxHelpfulReviewsCount', '<b>{$vote.rating.positive}</b> from <b>{$vote.rating.total} customers</b> found this review helpful');
        $snippets[] = array($namespace, '1', 'nfxVoteThankYou', 'Vielen Dank! Das Feedback für diese Bewertung wurde erfolgreich übermittelt.');
        $snippets[] = array($namespace, '2', 'nfxVoteThankYou', 'Thank You. Your Feedback very important for us.');
        $snippets[] = array($namespace, '1', 'nfxRatingAreaHeadingPositive', 'Die hilfreichste positive Bewertung');
        $snippets[] = array($namespace, '2', 'nfxRatingAreaHeadingPositive', 'The most helpful positive review');
        $snippets[] = array($namespace, '1', 'nfxRatingAreaHeadingNegative', 'Die hilfreichste negative Bewertung');
        $snippets[] = array($namespace, '2', 'nfxRatingAreaHeadingNegative', 'The most helpful negative review');
        $snippets[] = array($namespace, '1', 'nfxHelpfulReviewsCountVS1', '<b>{$bestVote.positive}</b> von <b>{$bestVote.total} Kunden</b> fanden diese bewertung hilfreich');
        $snippets[] = array($namespace, '2', 'nfxHelpfulReviewsCountVS1', '<b>{$bestVote.positive}</b> von <b>{$bestVote.total} Customers</b> found this review helpful');
        $snippets[] = array($namespace, '1', 'nfxHelpfulReviewsCountVS2', '<b>{$worstVote.positive}</b> von <b>{$worstVote.total} Kunden</b> fanden diese bewertung hilfreich');
        $snippets[] = array($namespace, '2', 'nfxHelpfulReviewsCountVS2', '<b>{$worstVote.positive}</b> von <b>{$worstVote.total} Customers</b> found this review helpful');
        $snippets[] = array($namespace, '1', 'nfxFrom', 'von');
        $snippets[] = array($namespace, '2', 'nfxFrom', 'from');
        if ($namespace === $this->sw4backend_namespace_detail) {
            $snippets[] = array($namespace, '1', 'DetailDataInfoFrom', 'ab');
            $snippets[] = array($namespace, '2', 'DetailDataInfoFrom', 'From');
        }

        $sql = 'SELECT \'' . $namespace . '\' as namespace, localeID, concat(\'nfx\',name) as name, value FROM `s_core_snippets` where namespace = "frontend/detail/comment" GROUP BY name, localeID';
        $copy = Shopware()->Db()->fetchAll($sql);
        foreach ($copy as $snippet) {
            $snippets[] = array($snippet['namespace'], $snippet['localeID'], $snippet['name'], $snippet['value']);
        }

        $shops = Shopware()->Db()->fetchAll("SELECT id FROM `s_core_shops` WHERE `active`=1;");
        foreach ($snippets as $key => $value) {
            foreach ($shops as $shop_id) {
                $sql = "INSERT s_core_snippets SET namespace    = \"" . $value[0] . "\",
                                                shopID      =   " . $shop_id['id'] . ",
                                                localeID    =   " . $value[1] . ",
                                                name        =   \"" . $value[2] . "\",
                                                value       = \"" . addslashes($value[3]) . "\"
                ";
                try {
                    Shopware()->Db()->query($sql);
                } catch (\Exception $ex) {
                    
                }
            }
        }

        return true;
    }

    /**
     * Creates database tables
     * - nfx_ekomi_orders - for storing orders exported to eKomi
     * - nfx_ekomi_products - for storing products exported to eKomi
     * - nfx_ekomi_product_reviews - for storing product review imported to eKomi
     * - nfx_ekomi_order_reviews - for storing orders reviews imported to eKomi
     * - nfx_vote_rating - for storing improved vote ratings
     * - nfx_ekomi_blacklist - for storing blacklisted emails
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    private function createDatabase() {
        $sqls = array();
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_orders` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `order_id` INT,
                  `product_id` VARCHAR(100),
                  `ekomi_link` TEXT,
                  `is_sent` BOOL DEFAULT 0,
                  sent_time INT (11),
                  email_sent_time INT (11),
                  PRIMARY KEY (`id`, `order_id`),
                  KEY `order_id_index` (`order_id`)
                ) ;";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_products` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `articleID` INT,
                  `product_id` VARCHAR(100),
                  sent_time INT (11),
                  PRIMARY KEY (`id`),
                  KEY `product_id_index` (`product_id`)
                ) ;";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_product_reviews`( `id` INT NOT NULL AUTO_INCREMENT, `product_id` varchar(200), `order_id` varchar(200), `rating` INT, `review` TEXT, `time` TIMESTAMP, PRIMARY KEY (`id`) ); ";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_order_reviews`( `id` INT NOT NULL AUTO_INCREMENT, `order_id` varchar(200), `rating` varchar(200), `review` TEXT, `time` TIMESTAMP, PRIMARY KEY (`id`) ); ";
        $sqls[] = "CREATE TABLE IF NOT EXISTS nfx_vote_rating(
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `article_vote_id`   INT(10) UNSIGNED NOT NULL,
                `user_id`   VARCHAR(32) NOT NULL,
                `rating_value`  TINYINT(1) NOT NULL DEFAULT 0,
                `rating_date`   TIMESTAMP NOT NULL DEFAULT NOW(),
                PRIMARY KEY (`id`),
                INDEX(`article_vote_id`),
                INDEX(`user_id`)
            ) CHARACTER SET utf8";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_widget_code` (
                    `id` INT(10) NOT NULL AUTO_INCREMENT,
                    `auth` VARCHAR(200) DEFAULT NULL,
                    `certificate_id` VARCHAR(200) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) CHARACTER SET utf8;";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_blacklist`( `id` INT NOT NULL AUTO_INCREMENT, `email` VARCHAR(255), PRIMARY KEY (`id`) );";
        $sqls[] = "CREATE TABLE IF NOT EXISTS `nfx_ekomi_opt_in_orders` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `ordernumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		  `ip` varchar(255) DEFAULT NULL,
		  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
                        KEY(`ordernumber`)
		) DEFAULT CHARSET=utf8";        
        $exists = Shopware()->Db()->fetchOne("SHOW TABLES LIKE 'nfx_ekomi_products'");
        if($exists){
            $exists = Shopware()->Db()->fetchOne("SHOW COLUMNS FROM nfx_ekomi_products WHERE Field LIKE 'articleID';");
            if (!$exists) {
                $config = $this->bootstrap->Config();
                $sqls[] = "CREATE TABLE nfx_ekomi_products_tmp SELECT MIN(`id`) id FROM `nfx_ekomi_products` GROUP BY `product_id`";
                $sqls[] = "DELETE FROM `nfx_ekomi_products` WHERE `id` NOT IN (SELECT `id` FROM `nfx_ekomi_products_tmp`)";
                $sqls[] = "DROP TABLE `nfx_ekomi_products_tmp`";
                $sqls[] = "ALTER TABLE `nfx_ekomi_products` CHANGE `product_id` `articleID` INT;";
                $sqls[] = "ALTER TABLE `nfx_ekomi_products` ADD COLUMN `product_id` VARCHAR(100) AFTER `articleID`;";
                $sqls[] = "UPDATE `nfx_ekomi_products` e JOIN `s_articles_details` d ON e.articleID = d.articleID AND d.kind = 1 SET e.product_id = d.`" . $config->article_id . "`";
            }
        }
        $exists = Shopware()->Db()->fetchOne("SHOW TABLES LIKE 'nfx_ekomi_opt_in_orders'");
        if($exists){
            $exists = Shopware()->Db()->fetchOne("SHOW INDEX FROM nfx_ekomi_opt_in_orders WHERE Key_name = 'ordernumber'");
            if(!$exists){
                $sqls[] = "CREATE INDEX ordernumber ON nfx_ekomi_opt_in_orders(ordernumber);";
            }
        }
        $row = Shopware()->Db()->fetchRow("SHOW TABLE STATUS WHERE `Name` = 'nfx_ekomi_opt_in_orders'");
        if($row){
            if($row["Engine"] != "InnoDB"){
                $sqls[] = "ALTER TABLE nfx_ekomi_opt_in_orders ENGINE=InnoDB;";
            }
        }
        $row = Shopware()->Db()->fetchRow("SHOW TABLE STATUS WHERE `Name` = 'nfx_vote_rating'");
        if($row){
            if($row["Engine"] != "InnoDB"){
                $sqls[] = "ALTER TABLE nfx_vote_rating ENGINE=InnoDB;";
            }
        }
        $row = Shopware()->Db()->fetchRow("SHOW TABLE STATUS WHERE `Name` = 'nfx_widget_code'");
        if($row){
            if($row["Engine"] != "InnoDB"){
                $sqls[] = "ALTER TABLE nfx_widget_code ENGINE=InnoDB;";
            }
        }
        foreach ($sqls as $sql) {
           try{
               Shopware()->Db()->query($sql);
           } catch (Exception $ex) {

           }
        }
    }

    /**
     * create additional attribute
     */
    private function addIntervalDaysAttribute() {
        $check_column_sql = "SHOW COLUMNS FROM `s_articles_attributes` LIKE 'nfx_ekomiintervaldays';";

        $result = Shopware()->Db()->fetchAll($check_column_sql);

        if (empty($result)) {
            if($this->bootstrap->assertVersionGreaterThenLocal("5.5")){
                $service = Shopware()->Container()->get('shopware_attribute.crud_service');
                $service->update('s_articles_attributes', 'nfx_ekomiintervaldays', 'TEXT');
            } else {
                Shopware()->Models()->addAttribute(
                        's_articles_attributes', 'nfx', 'ekomiintervaldays', 'TEXT', true
                );
            }
            Shopware()->Models()->generateAttributeModels(array('s_articles_attributes'));
        }
        $select_sql = "SELECT COUNT(*) as `count` FROM `s_core_engine_elements` WHERE `name` = 'nfxEkomiintervaldays';";
        $count = Shopware()->Db()->fetchRow($select_sql);
        if ($count['count'] == '0') {
            $instert_sql = "INSERT INTO `s_core_engine_elements` (`groupID`, `domname`, `default`, `type`, `store`, `label`, `required`, `position`, `name`, `variantable`, `help`, `translatable`)
                                VALUES (0, '', '', 'number',  '', 'individueller Bewertungszeitraum für Artikel', 0, 51, 'nfxEkomiintervaldays', 0, '', 0); ";
            Shopware()->Db()->query($instert_sql);
        }
    }

    function getEnglishShopId() {
        $sql = "SELECT 
                `s_core_shops`.`id` 
                FROM
                `s_core_shops` 
                INNER JOIN `s_core_locales` 
                  ON `s_core_shops`.`locale_id` = `s_core_locales`.`id` 
                WHERE `s_core_locales`.`locale` = 'en_GB'";
        return Shopware()->Db()->fetchAll($sql);
    }

    /**
     * Returns repository of the Mail object
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    private function getMailRepository() {
        if ($this->mailRepository === null) {
            $this->mailRepository = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail');
        }
        return $this->mailRepository;
    }

}
