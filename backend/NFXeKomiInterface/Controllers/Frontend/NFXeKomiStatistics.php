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
class Shopware_Controllers_Frontend_NFXeKomiStatistics extends Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware {
    
    /**
     * implements CSRFWhitelistAware
     * @return type
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'statistics'
        );
    }
    /**
     * display the statistics from outside the shop
     */
    public function statisticsAction() {
        $key = $this->Request()->getParam("key");
        if($this->isKeyValid($key)) {
            //cronjobs
            $statistics = $this->Plugin()->getCronjobs();
            $text = "<table>";
            $text .= "<tr>";
            $text .= "<td colspan='3'>Cronjobs:</td>";
            $text .= "</tr>";
            $text .= "<tr style='background-color:#444;color:white;text-align:center;font-weight:bold'>";
            $text .= "<td>Name</td><td>Aktiv</td><td>Letzte Ausf&uuml;hrung</td>";
            $text .= "</tr>";
            $i = 0;
            foreach($statistics as $row){
                $bgcolor = ($i++ % 2)? "#ddd":"grey";
                $text .= "<tr style='background-color:".$bgcolor.";'>";
                $text .= "<td>".$row["name"]."</td><td style='text-align:center'>".$row["active"]."</td><td>".$row["date"]."</td>";
                $text .= "</tr>";
            }
            $text .= "</table>";
            //statistics
            $statistics = $this->Plugin()->getStatistics($this->Request()->getParams());
            $text .= "<table>";
            $text .= "<tr>";
            $text .= "<td colspan='4'>Statistiken:</td>";
            $text .= "</tr>";
            $text .= "<tr style='background-color:#444;color:white;text-align:center;font-weight:bold'>";
            $text .= "<td>Datum</td><td>E-Mail versandet</td><td>Bewertungslinks genieriert</td><td>neue Produktbewertungen</td>";
            $text .= "</tr>";
            $i = 0;
            foreach($statistics as $row){
                $bgcolor = ($i++ % 2)? "#ddd":"grey";
                $total = ($row["date"] == "SUMME") ? "font-weight:bold;color:blue": "";
                $text .= "<tr style='background-color:".$bgcolor.";".$total."'>";
                $text .= "<td>".$row["date"]."</td><td style='text-align:right'>".$row["nr_emails"]."</td><td style='text-align:right'>".$row["nr_links"]."</td><td style='text-align:right'>".$row["nr_reviews"]."</td>";
                $text .= "</tr>";
            }
            $text .= "</table>";
            echo $text; 
            exit;
        }
        else {
            print_r("wrong key.");exit;
        }
    }
    
    /**
     * check if the key parameter is valid
     * @param type $key
     * @return boolean
     */
    private function isKeyValid($key) {
        $config = Shopware() -> plugins() -> Backend() -> NFXeKomiInterface() -> Config();
        if($key == $config->monitoring_key && $config->monitoring_key) {
            return true;
        }
        return false;
    }
    
    /**
     * NFXeKomiInterface plugin
     * @return type
     */
    private function Plugin(){
        return Shopware()->Plugins()->Backend()->NFXeKomiInterface();
    }
}