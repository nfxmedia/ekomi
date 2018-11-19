<?php

require_once __DIR__ . '/Components/CSRFWhitelistAware.php';
define("EKOMI_FRONTPAGE_RESPONSE_TIMEOUT", 3);
use ShopwarePlugins\NFXeKomiInterface\NFXeKomiManager,
    ShopwarePlugins\NFXeKomiInterface\Bootstrap\Install,
    ShopwarePlugins\NFXeKomiInterface\Bootstrap\Uninstall;

/**
 * nfx eKomi Interface
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2016, nfx:MEDIA
 * @author Nick Fingerhut, info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage nfxeKomiInterface
 * 
 */
class Shopware_Plugins_Backend_NFXeKomiInterface_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    /**
     * Returns true if it's Shopware 5
     *
     */
    private function checkShopwareResponsiveTemplate() {
        if (method_exists($this, "assertMinimumVersion")) {
            if ($this->assertMinimumVersion('5') && Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                return true;
            }
        }
        return false;
    }

    public function checkShopwareFive() {
        if (method_exists($this, "assertMinimumVersion")) {
            if ($this->assertMinimumVersion('5')) {
                return true;
            }
        }
        return false;
    }

    public function checkShopwareFive2() {
        if (method_exists($this, "assertMinimumVersion")) {
            if ($this->assertMinimumVersion('5.2')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns capabilities supported by the plugin
     *
     */
    public function getCapabilities() {
        return array('install' => true, 'update' => true, 'enable' => true);
    }

    /**
     * Registers plugin namespace
     * @return void
     */
    public function afterInit() {
        if ($this->checkShopwareFive()) {
            $this->get('Loader')->registerNamespace(
                    'ShopwarePlugins\\NFXeKomiInterface', __DIR__ . '/'
            );
        } else {
            $this->Application()->Loader()->registerNamespace(
                    'ShopwarePlugins\\NFXeKomiInterface', __DIR__ . '/'
            );
        }
    }

    /**
     * Install the plugin
     * - creates events
     * - creates emotion components
     * - creates custom email template
     * - creates menu
     * - creates config form
     * - creates database table
     * - created translation snippets
     * - creates database
     * @return <type>
     */
    public function install() {
        $uninstall = new Uninstall();
        $uninstall->run();
        $install = new Install();
        return $install->run();
    }

    /**
     * Updates the plugin
     * @return bool
     */
    public function update($version) {
        $install = new Install();
        return $install->run("update");
    }

    /**
     * Uninstalls the plugin by deleting the
     *
     * - database
     * - snippets
     * - email template
     *
     */
    public function uninstall() {
        $uninstall = new Uninstall();
        return $uninstall->run();
    }
    
    /** Get Shopware version
     *
     * @param <type> $version
     * @return <type>
     */
    public function assertVersionGreaterThenLocal($version) {
        if ($this->assertMinimumVersion($version)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @return string
     */
    public function onGetBackendController() {
        $this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . 'Controllers/Backend/NFXeKomiInterface.php';
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @return string
     */
    public function onGetFrontendController() {
        $this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . 'Controllers/Frontend/NFXeKomiStatistics.php';
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @return string
     */
    public function onGetDebugController() {
        $this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . 'Controllers/Frontend/NFXeKomiDebug.php';
    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @return string
     */
    public function onGetBackendStatisticsController() {
        $this->Application()->Snippets()->addConfigDir($this->Path() . 'Snippets/');
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . 'Controllers/Backend/NFXeKomiStatistics.php';
    }

    /**
     * Called for the cron event, and imports items/orders to ekomi and receives and stores the corresponding reviews.
     */
    public function oneKomiCronRun(Shopware_Components_Cron_CronJob $job) {
        try {
            $nfxeKomiManager = new NFXeKomiManager();
            $nfxeKomiManager->manage();
        } catch (Exception $ex) {
            //do nothing, just for being sure that cron is not disabled
        }
    }

    /**
     * Called for the cron event, and sends emails with review links to the customers that aren't in the blacklist.
     */
    public function oneKomiCronRunEmail(Shopware_Components_Cron_CronJob $job) {
        try {
            $nfxeKomiManager = new NFXeKomiManager();
            $nfxeKomiManager->sendEmails();
        } catch (Exception $ex) {
            //do nothing, just for being sure that cron is not disabled
        }
    }

    /**
     * Called after frontend's detail page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchDetail(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();

        $config = $this->Config();
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || $request->getControllerName() != 'detail') {
            return;
        }

        /*
         * @Todo pull to separate function
         */
        if (!$this->checkShopwareResponsiveTemplate()) {
            $version = substr(Shopware()->Config()->Version, 0, 3);
            if ($version >= 4.2) {
                $config->detail_widget = false;
                $config->disable_price = false;
                $config->home_widget = false;
            }
        } else {
            $config->detail_widget = false;
            $config->disable_price = false;
            $config->home_widget = false;
        }

        if (!$config->overide_ratings && !$config->detail_widget) {
            return;
        }

        if ($this->checkShopwareResponsiveTemplate()) {
            $view->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $view->addTemplateDir($this->Path() . 'Views/emotion/');
            if ($config->overide_ratings) {
                $view->extendsTemplate('frontend/index/indexEkomi.tpl');
                $view->extendsTemplate('frontend/detail/modifications.tpl');
            } else if ($config->detail_widget) {
                $view->extendsTemplate('frontend/detail/rich_snippets.tpl');
            }
        }

        $view->assign('ekomi_icon_url', $config->ekomi_icon_url);

        if ($config->minReviews > 0 && $config->minRatings > 0) {

            $sql = "SELECT
                        count(*) as cnt
                    FROM `s_articles_vote` sv
                    WHERE articleID = ? AND active = 1";
            $reviewsCount = Shopware()->Db()->fetchOne($sql, array($request->sArticle));

            $sql = "SELECT
                        COUNT(*) as cnt
                    FROM nfx_vote_rating
                    WHERE article_vote_id IN (SELECT id FROM s_articles_vote WHERE articleID=? AND active=1)
                    GROUP BY article_vote_id HAVING COUNT(*) >= ?
                    ORDER BY COUNT(*) DESC";
            $ratingsCount = Shopware()->Db()->fetchOne($sql, array($request->sArticle, $config->minRatings));

            if ($reviewsCount >= $config->minReviews && $ratingsCount >= $config->minRatings) {
                $sql = "SELECT
                            sv.name, sv.headline, sv.comment, DATE_FORMAT(sv.datum,  '%d.%m.%Y') as datum, sv.points,
                            (SELECT SUM(rating_value) FROM nfx_vote_rating WHERE article_vote_id=sv.id) as positive,
                            (SELECT COUNT(*) FROM nfx_vote_rating WHERE article_vote_id=sv.id) as total
                        FROM
                            `s_articles_vote` sv
                        WHERE
                            points >= 4 AND active = 1 AND articleID = ?
                        ORDER BY points desc, positive desc limit 1";
                
                $bestReview = Shopware()->Db()->fetchAll($sql, array($request->sArticle));
                $view->assign('bestVote', $bestReview[0]);

                $sql = "SELECT
                            sv.name, sv.headline, sv.comment, DATE_FORMAT(sv.datum,  '%d.%m.%Y') as datum, sv.points,
                            (SELECT SUM(rating_value) FROM nfx_vote_rating WHERE article_vote_id=sv.id) as positive,
                            (SELECT COUNT(*) FROM nfx_vote_rating WHERE article_vote_id=sv.id) as total
                        FROM
                            `s_articles_vote` sv
                        WHERE
                            points <= 3 AND active = 1 AND articleID = ?
                        ORDER BY points, positive desc limit 1";
                $worstReview = Shopware()->Db()->fetchAll($sql, array($request->sArticle));
                $view->assign('worstVote', $worstReview[0]);
                $view->assign('showVsRatingArea', 't');
            }
        }
        
        try {
            if($config->review_rating) {
                if($view->hasTemplate()) {
                    $tplVars = $view->Template()->getTemplateVars();
                    $comments = $tplVars['sArticle']['sVoteComments'];
                } else {
                    $article_id = $request->getParam('sArticle');
                    if($article_id) {
                        try {
                            $tplVars['sArticle'] = Shopware()->Modules()->Articles()->sGetArticlebyId($article_id);
                            $comments = $tplVars['sArticle']['sVoteComments'];
                        } catch(Exception $e) {
                           //
                        }
                    }
                }

                foreach($comments as $key => $comment) {
                    $id = $comment['id'];
                    $sql = "SELECT COUNT(*) as total, SUM(rating_value) as positive FROM nfx_vote_rating WHERE article_vote_id = ?";
                    $result = Shopware()->Db()->fetchRow($sql, array($id));
                    if($result['total']) {
                        $comments[$key]['rating']['positive'] = $result['positive'];
                        $comments[$key]['rating']['total'] = $result['total'];
                    }
                }
                $tplVars['sArticle']['sVoteComments'] = $comments;
                $view->assign('sArticle', $tplVars['sArticle']);
            }

            if ($config->detail_widget) {
                if($view->hasTemplate()) {
                    $tplVars = $view->Template()->getTemplateVars();
                } else {
                    $article_id = $request->getParam('sArticle');
                    if($article_id) {
                        try {
                            $tplVars['sArticle'] = Shopware()->Modules()->Articles()->sGetArticlebyId($article_id);
                        } catch(Exception $e) {
                            //
                        }
                    }
                }

                $tplVars['sArticle']['nfxVoteComments'] = $tplVars['sArticle']['sVoteComments'];
                $tplVars['sArticle']['sVoteComments'] = false;
                $view->assign('sArticle', $tplVars['sArticle']);
            }
        } catch(Exception $e) {
            //
        }

        $view->assign('disable_price_config', $config->disable_price);   
    }

    /**
     * Called before frontend's detail page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPreDispatchDetail(Enlight_Event_EventArgs $args) {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $config = $this->Config();

        if ($response->isException() || $request->getModuleName() != 'frontend' || $request->getControllerName() != 'detail') {
            return;
        }

        /*
         * @Todo pull to separate function
         */
        if (!$this->checkShopwareResponsiveTemplate()) {
            $version = substr(Shopware()->Config()->Version, 0, 3);
            if ($version >= 4.2) {
                $config->detail_widget = false;
                $config->disable_price = false;
                $config->home_widget = false;
            }
        } else {
            $config->detail_widget = false;
            $config->disable_price = false;
            $config->home_widget = false;
        }

        $args->getSubject()->View()->assign('ekomi_logo', $config->ekomi_logo);
        $args->getSubject()->View()->assign('rating_filter', $config->rating_filter);
        $args->getSubject()->View()->assign('manual_ratings', $config->manual_ratings);

        $args->getSubject()->View()->assign('detail_widget', $config->detail_widget);

        $args->getSubject()->View()->assign('best_worst_reviews', $config->best_worst_reviews);
        $args->getSubject()->View()->assign('review_rating', $config->review_rating);

        $article_vote_id = intval($request->getPost("article_vote_id"));
        if ($article_vote_id > 1) {
            $user_id = $this->getCustomUserId($request);
            $value = 0;

            if (strtolower($request->getPost("answer")) == "yes" || strtolower($request->getPost("answer")) == "ja") {
                $value = 1;
            }

            $sql = "SELECT count(*) as cnt FROM nfx_vote_rating WHERE article_vote_id = ? AND user_id = ?";
            $result = Shopware()->Db()->fetchOne($sql, array($article_vote_id, $user_id));
            if ($result == "0") {
                Shopware()->Db()->query("INSERT INTO nfx_vote_rating(article_vote_id,user_id,rating_value) VALUES(?,?,?)", array($article_vote_id, $user_id, $value));
                $args->getSubject()->View()->assign('voteRatingThanks', $article_vote_id);
            }
            if ($request->isXmlHttpRequest()) {
                die("Rating saved. Thank You!");
            }
        }
    }

    /**
     * Called after frontend's checkout page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchCheckout(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();
        $request = $args->getSubject()->Request();

        if ($this->checkShopwareResponsiveTemplate()) {
            $view->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $view->addTemplateDir($this->Path() . 'Views/emotion/');
            $view->extendsTemplate('frontend/checkout/ekomi_confirm.tpl');
        }
        if ($request->opt_in) {
            $session = Shopware()->Session();
            $orderNumber = $session['sOrderVariables']["sOrderNumber"];
            if (!$orderNumber) {
                $orderNumber = $view->sOrderNumber;
            }
            if($orderNumber){
                Shopware()->Session()->offsetUnset("nfxEkomiSessionID");
                $this->saveOrderOptIn($orderNumber);
            } else {
                Shopware()->Session()->nfxEkomiSessionID = Shopware()->SessionID();
            }
        } else {
            $config = $this->Config();
            if ($config->opt_in === "1") {
                $view->assign('opt_in_checked', true);
            } elseif ($config->opt_in === "2") {
                $view->assign('no_opt_in', true);
            }
        }
    }

    /**
     * marks the order as opt in
     *
     * @param $orderNumber 
     */
    private function saveOrderOptIn($orderNumber) {
        $ip = $_SERVER["REMOTE_ADDR"];

        Shopware()->Db()->query("INSERT INTO nfx_ekomi_opt_in_orders(ordernumber,ip) VALUES(?,?)", array($orderNumber, $ip));
    }
    /**
     * identify the order number
     * @param Enlight_Event_EventArgs $args
     */
    public function onAfterSaveOrder(Enlight_Event_EventArgs $args) {
        if(Shopware()->Session()->nfxEkomiSessionID){
            $orderNumber = $args->getReturn();
            if($orderNumber){
                Shopware()->Session()->offsetUnset("nfxEkomiSessionID");
                $this->saveOrderOptIn($orderNumber);
            }
        }
    }

    /**
     * Called after frontend's index page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchIndex(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();

        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || ($request->getControllerName() != 'index' && $request->getControllerName() != 'listing' && $request->getControllerName() != 'register')) {
            return false;
        }

        $config = $this->Config();
        $interfaceId = $config->nfxEKOMI_ID;

        /*
         * @Todo pull to separate function
         */
        if (!$this->checkShopwareResponsiveTemplate()) {
            $version = substr(Shopware()->Config()->Version, 0, 3);
            if ($version >= 4.2) {
                $config->detail_widget = false;
                $config->disable_price = false;
                $config->home_widget = false;
            }
        } else {
            $config->detail_widget = false;
            $config->disable_price = false;
            $config->home_widget = false;
        }

        // Save Ekomi Avg count in Log file and update once per day
        $filename = __DIR__ . "/Logs/ekomi_rating.txt";
        $handle = fopen($filename, "r");
        $content = fread($handle, filesize($filename));
        fclose($handle);

        $update_ekomi_count = 0;
        if($content) {
            $ekomi_count = explode("\n", $content);
            if($ekomi_count[1] && date("Y-m-d") > $ekomi_count[1]) {
                $update_ekomi_count = 1;
            }
            $ekomi_count = $ekomi_count[0];
        }

        if((!isset($ekomi_count[0]) && !$ekomi_count[0]) || $update_ekomi_count) {
            // timeout on slow response
            $context = stream_context_create(array('http'=> array(
                'timeout' => EKOMI_FRONTPAGE_RESPONSE_TIMEOUT,
                'ignore_errors' => true,
            )));
        
            $ekomi_count = file_get_contents("http://www.ekomi.de/widgets/$interfaceId/bewertung.txt", false, $context);
            $fp = fopen(__DIR__. '/Logs/ekomi_rating.txt', 'w');
            fwrite($fp, $ekomi_count. "\n");
            fwrite($fp, date("Y-m-d"));
            fclose($fp);
        }
        $content = htmlspecialchars($ekomi_count);
        // Save Ekomi Avg count in Log file and update once per day

        $parts = explode("|", $content);
        $maxRate = 5;
        $averageRate = $parts[0];
        $reviewsNum = $parts[1];

        $nfxeKomiManager = new NFXeKomiManager();
        $certificateId = $nfxeKomiManager->getSnapshot();
        $view->assign('cert_id', $certificateId);
        if ($this->checkShopwareResponsiveTemplate()) {
            $view->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            $view->addTemplateDir($this->Path() . 'Views/emotion/');
            if ($request->getControllerName() != 'index') {
                $view->extendsTemplate('frontend/index/left_new.tpl');
            } else {
                $view->assign('home_widget', $config->home_widget);
                $view->extendsTemplate('frontend/index/overall.tpl');
            }
        }

        $shopName = Shopware()->Config()->get("sShopname");
        $view->assign('shopName', $shopName);
        if ($request->getControllerName() == 'register') {
            $view->assign('register_batch', $config->register_batch);
        } else if ($request->getControllerName() == 'listing') {
            $view->assign('category_batch', $config->category_batch);
        }
        // $view->assign('widget_code', $config->widget_code);
        if (!$this->checkShopwareResponsiveTemplate()) {
            $view->assign('maxRate', $maxRate);
            $view->assign('averageRate', $averageRate);
            $view->assign('reviewsNum', $reviewsNum);
        }
    }

    /**
     * Called after frontend's index page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontend(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();

        $request = $args->getSubject()->Request();

        if ($request->getControllerName() == 'checkout') {
            return;
        }
        if ($this->checkShopwareResponsiveTemplate()) {
            $config = $this->Config();
            $nfxeKomiManager = new NFXeKomiManager();
            $certificateId = $nfxeKomiManager->getSnapshot();
            $view->assign('cert_id', $certificateId);
            $view->addTemplateDir($this->Path() . 'Views/responsive/');
            $view->assign('ekomi_badge', $config->ekomi_badge);
            $view->assign('controllerAction', $request->getActionName());
        }
    }

    /**
     * Called after frontend's index page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchWidgets(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();
        $nfxeKomiManager = new NFXeKomiManager();
        $certificateId = $nfxeKomiManager->getSnapshot();
        $view->assign('cert_id', $certificateId);
    }

    /**
     * Called when event is fired for the GetArticlesVotes registered hook
     *
     * @param Enlight_Hook_HookArgs $args
     */
    public function afterGetArticlesVotes(Enlight_Hook_HookArgs $args) {
        $result = $args->getReturn();
        $request = Shopware()->Front()->Request();

        $vote_ids = array();

        foreach ($result as $v) {
            $vote_ids[] = $v["id"];
        }

        if (count($vote_ids)) {
            $sql = "SELECT
                        article_vote_id, SUM(rating_value) as positive, COUNT(*) as total
                    FROM
                        nfx_vote_rating
                    WHERE
                        article_vote_id IN (" . join(',', $vote_ids) . ")
                    GROUP BY article_vote_id";
            $ratings = Shopware()->Db()->fetchAssoc($sql);

            $user_votes = Shopware()->Db()->fetchAssoc("SELECT article_vote_id, user_id FROM nfx_vote_rating WHERE user_id = ?", array($this->getCustomUserId($request)));

            foreach ($result as $articleKey => $articleValue) {
                if (isset($ratings[$articleValue["id"]])) {
                    $result[$articleKey]["rating"] = $ratings[$articleValue["id"]];
                }
                if (isset($user_votes[$articleValue["id"]])) {
                    $result[$articleKey]["hide_buttons"] = "t";
                }
            }

            $args->setReturn($result);
        }
    }

    /**
     * Returns customers user id
     *
     * @param $request
     * @return string
     */
    private function getCustomUserId($request) {
        return md5($request->getClientIp() . $request->getHeader('USER_AGENT'));
    }

    /**
     * Called after backend's index page event was fired
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function postDispatchBackendIndex(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();
        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('backend/nf_xe_komi_interface/index.js');
    }

    /**
     * add additional attribute on backend
     * @param Enlight_Event_EventArgs $args
     * @return type
     */
    public function onLoadArticle(Enlight_Event_EventArgs $args) {
        $request = $args->getSubject()->Request();
        $view = $args->getSubject()->View();

        if ($request->getActionName() != "load") {
            return;
        }

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('backend/nf_xe_komi_interface/model/attribute.js');
    }

    /**
     * add additional attribute when the articles are filetered
     * @param Enlight_Event_EventArgs $args
     * @return type
     */
    public function onFilterSql(Enlight_Event_EventArgs $args) {
        $sql = $args->getReturn();
        return str_replace('attr20,', 'attr20,nfx_ekomiintervaldays,', $sql);
    }

    /**
     * calculate statistics
     * @param type $params
     * @return type
     */
    public function getStatistics($params) {
        $nfxeKomiManager = new NFXeKomiManager();
        $statistics = $nfxeKomiManager->getStatistics($params);
        return $statistics;
    }

    /**
     * get cronjobs status
     * @return type
     */
    public function getCronjobs() {
        $nfxeKomiManager = new NFXeKomiManager();
        $statistics = $nfxeKomiManager->getCronjobs();
        return $statistics;
    }

    /**
     * get the host name of the site
     * @return type
     */
    public function getHost() {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveDefault();
        $host = $shop->getHost();
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = ($host) ? $host : $_SERVER['HTTP_HOST'];
        }
        return $host;
    }

    /**
     * Provide the file collection for js files
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addJsFiles(Enlight_Event_EventArgs $args) {
        $jsFiles = array(__DIR__ . '/Views/responsive/frontend/_public/src/js/ekomi_interface.js');
        return new Doctrine\Common\Collections\ArrayCollection($jsFiles);
    }

    /**
     * Provide the file collection for less
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addLessFiles(Enlight_Event_EventArgs $args) {
        $less = new \Shopware\Components\Theme\LessDefinition(
                //configuration
                array(),
                //less files to compile
                array(
            __DIR__ . '/Views/responsive/frontend/_public/src/less/ekomi_interface.less'
                ),
                //import directory
                __DIR__
        );

        return new Doctrine\Common\Collections\ArrayCollection(array($less));
    }

    /**
     * Reads Plugins Meta Information
     * @return string
     */
    public function getInfo() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return array(
                'version' => $info['currentVersion'],
                'author' => $info['author'],
                'copyright' => $info['copyright'],
                'label' => $this->getLabel(),
                'source' => $info['source'],
                'description' => $info['description'],
                'license' => $info['license'],
                'support' => $info['support'],
                'link' => $info['link'],
                'changes' => $info['changelog'],
                'revision' => '1'
            );
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the current version of the plugin.
     *
     * @return string|void
     * @throws Exception
     */
    public function getVersion() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Get (nice) name for plugin manager list
     *
     * @return string
     */
    public function getLabel() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['label']["de"];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

}

?>
