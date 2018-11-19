<?php

namespace ShopwarePlugins\NFXeKomiInterface\Bootstrap;

use Shopware\Models\Mail\Mail;

/**
 * The install class does the basic setup of the plugin. All operations should be implemented in a way
 * that they can also be run on update of the plugin
 *
 * Class Install
 * @package ShopwarePlugins\NFXeKomiInterface\Bootstrap
 */
class EkomiAbstractBootstrap {

    protected $bootstrap = null;
    protected $mailRepository = null;
    protected $ekomi_email = "eKomiReviewTemplate";
    protected $error_email = "eKOMIERRORHANDLE";
    protected $sw4backend_namespace_detail = 'engine/Shopware/Plugins/Community/Backend/NFXeKomiInterface/Views/emotion/frontend/detail/modifications';
    protected $sw4frontend_namespace_detail = 'engine/Shopware/Plugins/Community/Frontend/NFXeKomiInterface/Views/emotion/frontend/detail/modifications';
    protected $sw4backend_namespace_index = 'engine/Shopware/Plugins/Community/Backend/NFXeKomiInterface/Views/emotion/frontend/index/overall';
    protected $sw4frontend_namespace_index = 'engine/Shopware/Plugins/Community/Frontend/NFXeKomiInterface/Views/emotion/frontend/index/overall';
    protected $sw5namespace = 'frontend/detail/tabs/comment';
    protected $emotionItemName = "eKomi Widget";
    protected $version = null;
    protected static $instance = null;

    public function __construct() {
        $this->bootstrap = $this->Plugin();
    }

    public static function getInstance($config) {
        if (!self::$instance) {
            self::$instance = new EkomiAbstractBootstrap($config);
        }
        return self::$instance;
    }

    protected function Plugin() {
        return Shopware()->Plugins()->Backend()->NFXeKomiInterface();
    }

    public function checkShopwareFive() {
        if (method_exists($this->bootstrap, "checkShopwareFive")) {
            if ($this->bootstrap->checkShopwareFive()) {
                return true;
            }
        }
        return false;
    }

    public function checkShopwareFive2() {
        if (method_exists($this->bootstrap, "checkShopwareFive2")) {
            if ($this->bootstrap->checkShopwareFive2()) {
                return true;
            }
        }
        return false;
    }

}
