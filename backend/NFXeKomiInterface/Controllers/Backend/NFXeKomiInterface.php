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
class Shopware_Controllers_Backend_NFXeKomiInterface extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
    /**
     * implements CSRFWhitelistAware
     * @return type
     */
    public function getWhitelistedCSRFActions()
    {
        return array(
            'list',
            'create',
            'delete'
        );
    }
    /**
     * returns the list of emails in the blacklist
     */
    public function listAction(){
		$sql = "SELECT id, email FROM `nfx_ekomi_blacklist`;";

		$emails = Shopware() -> Db() -> fetchAll($sql);

		$count = count($emails);

		$this -> View() -> assign(array('success' => true, 'data' => $emails, 'total' => $count));
    }

	/**
     * adds an email into the blacklist
     */	
	public function createAction(){
		$email = $this -> Request() -> getParam('email');
    	$sql = "INSERT INTO `nfx_ekomi_blacklist` (email) VALUES ('$email');";
		Shopware() -> Db() -> query($sql);
    }
	
	/**
     * deletes the email from the blacklist
     */
	public function deleteAction(){
		$id = $this -> Request() -> getParam('id');
		$sql = "DELETE FROM `nfx_ekomi_blacklist` WHERE id = '$id';";
		Shopware() -> Db() -> query($sql);
    }
}