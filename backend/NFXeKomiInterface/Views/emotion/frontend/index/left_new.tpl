{block name="frontend_index_header_css_screen" append}
<style>
.ekomiWrapperRegister{
	width: 180px; 
	float: right;
}

.ekomiWrapperCategory{
	margin-bottom: 10px;
}
/* SUPPORTING FOR DIFFERENT TEMPLATES */
#eKomiWidget_default > a {
    display: block;
    width: 100%;
}
#eKomiWidget_default > a > img {
    max-width: 100%;
}
</style>
{/block}

{* Trusted shops logo *}
{block name='frontend_index_left_menu' append}
{if $category_batch}
<div class="ekomiWrapperCategory">
	<!-- eKomiWidget START -->
	<div id="eKomiWidget_default"></div>
	<!-- eKomiWidget END -->
	 
	<!-- eKomiLoader START, only needed once per page -->
	<script type="text/javascript">
	                (function(){
	                               eKomiIntegrationConfig = new Array({
	                                               	certId:"{$cert_id}"
	                                               }
	                               );
	                               if(typeof eKomiIntegrationConfig != "undefined"){
		                               	for(var eKomiIntegrationLoop=0;eKomiIntegrationLoop<eKomiIntegrationConfig.length;eKomiIntegrationLoop++) {
		                                               var eKomiIntegrationContainer = document.createElement('script');
		                                               eKomiIntegrationContainer.type = 'text/javascript'; 
		                                               eKomiIntegrationContainer.defer = true;
		                                               eKomiIntegrationContainer.src = (document.location.protocol=='https:'?'https:':'http:') +"//connect.ekomi.de/integration_1386031992/" + eKomiIntegrationConfig[eKomiIntegrationLoop].certId + ".js";
		                                               document.getElementsByTagName("head")[0].appendChild(eKomiIntegrationContainer);
		                                }
	                               }
	                               else{
	                               		if('console' in window) { 
	                               			console.error('connectEkomiIntegration - Cannot read eKomiIntegrationConfig'); 
	                               		}
	                               }
	                })();
	</script>
	
	<!-- eKomiLoader END, only needed once per page -->
</div>
{/if}
{/block}

{block name='frontend_index_content_right' append}
{if $register_batch}
<div class="ekomiWrapperRegister">
    <!-- eKomiWidget START -->
	<div id="eKomiWidget_default"></div>
	<!-- eKomiWidget END -->
	 
	<!-- eKomiLoader START, only needed once per page -->
	<script type="text/javascript">
	                (function(){
	                               eKomiIntegrationConfig = new Array({
	                                               	certId:"{$cert_id}"
	                                               }
	                               );
	                               if(typeof eKomiIntegrationConfig != "undefined"){
		                               	for(var eKomiIntegrationLoop=0;eKomiIntegrationLoop<eKomiIntegrationConfig.length;eKomiIntegrationLoop++) {
		                                               var eKomiIntegrationContainer = document.createElement('script');
		                                               eKomiIntegrationContainer.type = 'text/javascript'; 
		                                               eKomiIntegrationContainer.defer = true;
		                                               eKomiIntegrationContainer.src = (document.location.protocol=='https:'?'https:':'http:') +"//connect.ekomi.de/integration_1386031992/" + eKomiIntegrationConfig[eKomiIntegrationLoop].certId + ".js";
		                                               document.getElementsByTagName("head")[0].appendChild(eKomiIntegrationContainer);
		                                }
	                               }
	                               else{
	                               		if('console' in window) { 
	                               			console.error('connectEkomiIntegration - Cannot read eKomiIntegrationConfig'); 
	                               		}
	                               }
	                })();
	</script>
	
	<!-- eKomiLoader END, only needed once per page -->
</div>
{/if}
{/block}
