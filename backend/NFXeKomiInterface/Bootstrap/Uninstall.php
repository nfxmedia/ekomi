<?php

namespace ShopwarePlugins\NFXeKomiInterface\Bootstrap;

use ShopwarePlugins\NFXeKomiInterface\Bootstrap\EkomiAbstractBootstrap;

/**
 * Uninstaller of the plugin.
 *
 * Class Uninstall
 * @package ShopwarePlugins\NFXeKomiInterface\Bootstrap;
 */
class Uninstall extends EkomiAbstractBootstrap
{

    public function __construct()
    {
        $this->bootstrap = $this->Plugin();
    }

    public function run()
    {
        $this->clearSnippets();
        $this->clearEmail();
        $this->removeEmotionComponents();
        //$this->removeDatabase();//do not remove the data anymore
        //$this->removeIntervalDaysAttribute();//do not remove the data anymore
        return array('success' => true, 'invalidateCache' => array('backend', 'frontend', 'proxy'));
    }

    /**
     * deletes the snippets created during the installation
     */
    private function clearSnippets() {
        // clear SW4 snippets

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4frontend_namespace_detail . "' AND name LIKE 'nfx%'";
        Shopware()->Db()->query($sql);

        $sql = "DELETE FROM s_core_snippets WHERE namespace = '" . $this->sw4backend_namespace_detail . "' AND name LIKE 'nfx%'";
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
     * deletes the email template created during the installation
     */
    private function clearEmail() {
        try {
            $config = $this->bootstrap->Config();
            
            $query = $this->getMailRepository()->getValidateNameQuery($this->ekomi_email);
            $mailModel = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            if(isset($mailModel)) {
                Shopware()->Models()->remove($mailModel);
                Shopware()->Models()->flush();
            }

            $query = $this->getMailRepository()->getValidateNameQuery($this->error_email);
            $mailModel = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
            if(isset($mailModel)) {
                Shopware()->Models()->remove($mailModel);
                Shopware()->Models()->flush();
            }
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * drops the database tables that are created during the installation
     */
    private function removeDatabase() {
        $sql = "DROP TABLE IF EXISTS `nfx_ekomi_orders`;";
        Shopware()->db()->query($sql);
        $sql = "DROP TABLE IF EXISTS `nfx_ekomi_products`;";
        Shopware()->db()->query($sql);
        $sql = "DROP TABLE IF EXISTS `nfx_ekomi_product_reviews`;";
        Shopware()->db()->query($sql);
        $sql = "DROP TABLE IF EXISTS `nfx_ekomi_order_reviews`;";
        Shopware()->db()->query($sql);
        $sql = "DROP TABLE IF EXISTS nfx_vote_rating";
        Shopware()->Db()->query($sql);
        $sql = "DROP TABLE IF EXISTS nfx_ekomi_blacklist";
        Shopware()->Db()->query($sql);
        $sql = "DROP TABLE IF EXISTS nfx_ekomi_details";
        Shopware()->Db()->query($sql);

        $delete_sql = "DELETE FROM `s_core_engine_elements` WHERE `name` = 'nfxEkomiintervaldays';";
        Shopware()->Db()->query($delete_sql);
    }

    /**
     * remove the additional attribute
     */
    private function removeIntervalDaysAttribute() {
        $check_column_sql = "SHOW COLUMNS FROM `s_articles_attributes` LIKE 'nfx_ekomiintervaldays';";

        $result = Shopware()->Db()->fetchAll($check_column_sql);
        if($result){
            Shopware()->Models()->removeAttribute('s_articles_attributes', 'nfx', 'ekomiintervaldays');
            Shopware()->Models()->generateAttributeModels(array('s_articles_attributes'));
        }
        $delete_sql = "DELETE FROM `s_core_engine_elements` WHERE `name` = 'nfxEkomiintervaldays';";
        Shopware()->Db()->query($delete_sql);
    }

    /**
     * Removes created emotion components
     * @return boolean
     */
    private function removeEmotionComponents() {
        $this->removeEmotionElement($this->emotionItemName);
        return true;
    }

    /*
     * Removes emotion components from db
     * @return booelan
     */

    private function removeEmotionElement($emotionItemName) {
        $sql = "SELECT `id` FROM `s_library_component` WHERE `name`=?;";
        $lastInsertedId = Shopware()->Db()->fetchRow($sql, array($emotionItemName));
        $removeById = $lastInsertedId['id'];

        if ($removeById > 0) {
            $sql = "DELETE FROM `s_emotion_element_value`
                    WHERE `componentID` = $removeById;";
            Shopware()->Db()->query($sql);

            $sql = "DELETE FROM `s_emotion_element`
                    WHERE `componentID` = $removeById;";
            Shopware()->Db()->query($sql);

            $sql = "DELETE FROM `s_library_component_field`
                    WHERE `componentID` = $removeById;";
            Shopware()->Db()->query($sql);

            $sql = "DELETE FROM `s_library_component`
                    WHERE `id` = $removeById;";
            Shopware()->Db()->query($sql);
        }

        return true;
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