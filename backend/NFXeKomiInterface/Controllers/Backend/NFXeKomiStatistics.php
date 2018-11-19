<?php

/**
 * nfx eKomi Interface
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2013, nfx:MEDIA
 * @author Nick Fingerhut, info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage nfxeKomiInterface
 */
class Shopware_Controllers_Backend_NFXeKomiStatistics extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware 
{
    
    /**
     * implements CSRFWhitelistAware
     * @return type
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'getStatistics',
            'getCronjobs'
        );
    }
    
    /**
     * get statistics
     * -> Mails send (month/total) / E-Mail versandet (Monat/Summe)
     * -> Rating links generated (month/total) / Bewertungslinks genieriert (Monat/Summe)
     * -> new product reviews (month / total) / neue Produktbewertungen (Monat / Summe).
     */
    public function getStatisticsAction() {
        $params = $this->Request()->getParams();
        
        $statistics = $this->Plugin()->getStatistics($params);
        $count = count($statistics);

        $this -> View() -> assign(array('success' => true, 'data' => $statistics, 'total' => $count));
    }
    
    /**
     * get cronjobs
     * -> Cronjob aktiv / inaktiv
     * -> Latest Cronjob Call
     */
    public function getCronjobsAction() {
        $statistics = $this->Plugin()->getCronjobs();
        $count = count($statistics);

        $this -> View() -> assign(array('success' => true, 'data' => $statistics, 'total' => $count));
    }
    
    /**
     * NFXeKomiInterface plugin
     * @return type
     */
    private function Plugin(){
        return Shopware()->Plugins()->Backend()->NFXeKomiInterface();
    }
}