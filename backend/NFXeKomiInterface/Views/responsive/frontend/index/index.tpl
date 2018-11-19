{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader || !(({$Controller|lower} == "checkout" && {$controllerAction|lower} != "cart") || ({$Controller|lower} == "register" && $sTarget != "account"))}
        {if $ekomi_badge}
            <div class="ekomi-header-seal">
                <!-- eKomiWidget START -->
                <div id="eKomiSeal_default"></div>
                <!-- eKomiWidget END -->

                <!-- eKomiLoader START, only needed once per page -->
                <script type="text/javascript">
                    (function () {
                        eKomiIntegrationConfig = new Array({
                            certId: "{$cert_id}"
                        }
                        );
                        if (typeof eKomiIntegrationConfig != "undefined") {
                            for (var eKomiIntegrationLoop = 0; eKomiIntegrationLoop < eKomiIntegrationConfig.length; eKomiIntegrationLoop++) {
                                var eKomiIntegrationContainer = document.createElement('script');
                                eKomiIntegrationContainer.type = 'text/javascript';
                                eKomiIntegrationContainer.defer = true;
                                eKomiIntegrationContainer.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + "//connect.ekomi.de/integration_1386031992/" + eKomiIntegrationConfig[eKomiIntegrationLoop].certId + ".js";
                                document.getElementsByTagName("head")[0].appendChild(eKomiIntegrationContainer);
                            }
                        }
                        else {
                            if ('console' in window) {
                                console.error('connectEkomiIntegration - Cannot read eKomiIntegrationConfig');
                            }
                        }
                    })();
                </script>

                <!-- eKomiLoader END, only needed once per page -->
            </div>
        {/if}
        {$smarty.block.parent}
    {/if}
{/block}