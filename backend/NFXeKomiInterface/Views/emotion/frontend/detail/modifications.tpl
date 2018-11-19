{block name="frontend_detail_index_tabs_rating"}
<style type="text/css">
	{*------------------------------------------------------------------*}
</style>
<script>
function sendVoteRating(elm) {
var $form = $(elm.form);
var posting = $.post(window.location.href, $form.serialize() + "&answer="+ $(elm).attr("value"));
posting.done(function(data) {
$form.html("<div class=\"thanksArea\">{s name="nfxVoteThankYou"}{/s}</div>");
});
}
</script>
<div id="comments">
    {* Response save comment *}
    {if $sAction == "ratingAction"}
    {block name='frontend_detail_comment_error_messages'}
    <div>
        {if $sErrorFlag}
        <div class="error bold center">
            {se name="nfxDetailCommentInfoFillOutFields"}{/se}
        </div>
        {else}
        {if {config name="OptinVote"} && !{$smarty.get.sConfirmation}}
        <div class="success bold center">
            {se name="nfxDetailCommentInfoSuccessOptin"}{/se}
        </div>
        {else}
        <div class="success bold center">
            {se name="nfxDetailCommentInfoSuccess"}{/se}
        </div>
        {/if}
        {/if}
    </div>
    {/block}
    {/if}

    <h2>{s name="nfxDetailCommentHeader"}{/s} "{$sArticle.articleName}"</h2>
    {if $sArticle.nfxVoteComments}
        {assign var='comments' value=$sArticle.nfxVoteComments}
    {else}
        {assign var='comments' value=$sArticle.sVoteComments}
    {/if}
    {if $sArticle.sVoteAverange eq NULL }
        {$sArticle.sVoteAverange = $sArticle.sVoteAverage}
        {$sArticle.sVoteAverange.averange = $sArticle.sVoteAverage.average}
    {/if}
    <div class="overview_rating" {if $sArticle.sVoteAverange.count && $detail_widget}itemscope itemtype="http://data-vocabulary.org/Review-aggregate"{/if}>
        {if $sArticle.sVoteAverange.count && $detail_widget}    
            <meta itemprop="itemreviewed" content="{$sArticle.articleName}"/>
            <meta itemprop="rating" content="{$sArticle.sVoteAverange.averange / 2}" />
            <meta itemprop="count" content="{$sArticle.sVoteAverange.count}"/>
            <meta itemprop="best" content="5"/>
            <meta itemprop="worst" content="0.5"/>
            <meta itemprop="title" content="{strip}{if $sBreadcrumb}{foreach from=$sBreadcrumb|array_reverse item=breadcrumb}{$breadcrumb.name} | {/foreach}{/if}{config name=sShopname}{/strip}"/>
            <meta itemprop="type" content="product"/>
            <meta itemprop="url" content="{$sArticle.linkDetails|rewrite:$sArticle.articleName}"/>
            <meta itemprop="description" content="{$sArticle.description_long|strip_tags}"/>
            <meta itemprop="app_id" content="{$sArticle.ordernumber}"/>
            <meta itemprop="image" content="{$sArticle.image.src.3}"/>
        {/if}
            <strong>{se name="nfxDetailCommentInfoAverageRate"}{/se}</strong>
        {if $sArticle.sVoteAverange.count}    
            <div class="star star{$sArticle.sVoteAverange.averange}">
                Star Rating
            </div>
        {/if}
        {*--------------------------------------------------------------------------------------*}
        {if $rating_filter}
            {assign var="count" value=$sArticle.sVoteAverange.count}
            {assign var="count10" value=0}
            {assign var="count8" value=0}
            {assign var="count6" value=0}
            {assign var="count4" value=0}
            {assign var="count2" value=0}
            {assign var="count2" value=0}
    
            {foreach name=comment from=$comments item=vote}
            {if $vote.points == 5}
            {$count10 = $count10 + 1}
            {/if}
            {if $vote.points == 4}
            {$count8 = $count8 + 1}
            {/if}
            {if $vote.points == 3}
            {$count6 = $count6 + 1}
            {/if}
            {if $vote.points == 2}
            {$count4 = $count4 + 1}
            {/if}
            {if $vote.points == 1}
            {$count2 = $count2 + 1}
            {/if}
            {/foreach}
    
            <div class="left_overview_container" style="margin-top:6px">
                {if $count10 != 0}
                <a href="#" id="hstar5"> {/if} <span class="star star10 star_list">Star Rating</span> <span class="chart"><span class="chart-inner" style="margin-left:8px;margin-top:9px;width:{$count10*100/$count}%"></span></span> <span class="count" style="text-decoration: underline;margin-top:6px;">{$count10} </span> {if $count10 != 0} </a>
                {/if}
    
                {if $count8 != 0}
                <a href="#" id="hstar4"> {/if} <span class="star star8r star_list">Star Rating</span> <span class="chart"><span class="chart-inner" style="margin-left:8px;margin-top:9px;width:{$count8*100/$count}%"></span></span> <span class="count" style="text-decoration: underline;margin-top:6px;">{$count8} </span> {if $count8 != 0} </a>
                {/if}
    
                {if $count6 != 0}
                <a href="#" id="hstar3"> {/if} <span class="star star6r star_list">Star Rating</span> <span class="chart"><span class="chart-inner" style="margin-left:8px;margin-top:9px;width:{$count6*100/$count}%"></span></span> <span class="count" style="text-decoration: underline;margin-top:6px;">{$count6} </span> {if $count6 != 0} </a>
                {/if}
    
                {if $count4 != 0}
                <a href="#" id="hstar2"> {/if} <span class="star star4r star_list">Star Rating</span> <span class="chart"><span class="chart-inner" style="margin-left:8px;margin-top:9px;width:{$count4*100/$count}%"></span></span> <span class="count" style="text-decoration: underline;margin-top:6px;">{$count4} </span> </a>
                {if $count4 != 0}
                </a>
                {/if}
    
                {if $count2 != 0}
                <a href="#" id="hstar1"> {/if} <span class="star star2r star_list">Star Rating</span> <span class="chart"><span class="chart-inner" style="margin-left:8px;margin-top:9px;width:{$count2*100/$count}%"></span></span> <span class="count" style="text-decoration: underline;margin-top:6px;">{$count2} </span> </a>
                {if $count2 != 0}
                </a>
                {/if}
            </div>
        {/if}
        
        {if $ekomi_logo}
            <a href="{$ekomi_icon_url}" target="_blank" title="eKomi">
            <div class="center_overview_container">
                <div class="guarantee"></div>
            </div> </a>
            <div class="clear">
                &nbsp;
            </div>
        {/if}
        {*-------------------------------------------------------------------------------*}
       
        <div class="clear">
            &nbsp;
        </div>
    </div>

    <div class="doublespace">
        &nbsp;
    </div>

    {if $showVsRatingArea eq "t" && $best_worst_reviews}
    <div class="vsRatingArea">
        <div class="vsRatingAreaHeading">
            <div>
                {s name="nfxRatingAreaHeadingPositive"}{/s}
            </div>
            <div>
                {s name="nfxRatingAreaHeadingNegative"}{/s}
            </div>
        </div>
        <div class="vsRatingAreaContent">
            <div class="reviewBlock1">
                <div class="stat">
                    {s name="nfxHelpfulReviewsCountVS1"}{/s}
                </div>
                <div class="author">
                	<div class="star star{$bestVote.points*2}" style="float:left;margin-top:3px;"></div>
                	<div style="float:right;">
                		{s name="nfxFrom"}{/s} <span class="name">{$bestVote.name}</span> <b>{$bestVote.datum}</b>
                	</div>
                </div>
                
                <div class="text" style="padding-left: 10px;">
                    {$bestVote.comment|nl2br}
                </div>
            </div>
            <div class="reviewBlock2">
                <div class="stat">
                    {s name="nfxHelpfulReviewsCountVS2"}{/s}
                </div>
                <div class="author">
                		<div class="star star{$worstVote.points*2}" style="float:left;margin-top:3px;"></div>
                		<div style="float:right;">
                    	{s name="nfxFrom"}{/s} <span class="name">{$worstVote.name}</span> <b>{$worstVote.datum}</b>
                    </div>
                </div>
                <div class="text">
                    {$worstVote.comment|nl2br}
                </div>
            </div>
        </div>
    </div>
    {/if}
    {* Display comments *}
    
    {if $comments}
    <div class="rating-filter">
        <span>Anzeige von <span></span>-Sterne Rezensionen</span><a href="#" id="hstar0">Filter aufheben</a>
    </div>
    {foreach name=comment from=$comments item=vote}
    <div class="comment_block{if $smarty.foreach.comment.last} last{/if}{if $vote.answer} no_border{/if} page1 filter{$vote.points} pagef1" {if $detail_widget}itemscope itemtype="http://data-vocabulary.org/Review"{/if}>
        {if  $detail_widget}
            <meta itemprop="itemreviewed" content="{$sArticle.articleName}"/>
        {/if}
        <div class="left_container cloud_bg">
            {* Author *}
            {block name='frontend_detail_comment_author'}
                <strong class="author"> {se name="nfxFrom"}{/se}
                    <span {if $detail_widget}itemprop="reviewer"{/if}>
                        {$vote.name}
                    </span>
                </strong>
            {/block}

            {* Date *}
            {block name='frontend_detail_comment_date'}
                <span class="date">
                    <span {if $detail_widget}itemprop="dtreviewed"{/if} datetime="{$vote.datum}">{$vote.datum|substr:0:4}-{$vote.datum|substr:5:2}-{$vote.datum|substr:8:2}</span>
                </span>
            {/block}
            {* Star rating *}
            {block name="frontend_detail_comment_star_rating"}
                {if $detail_widget}
                    <meta itemprop="rating" content="{$vote.points}"/>
                    <meta itemprop="best" content="5"/>
                    <meta itemprop="worst" content="0.5"/>
                {/if}
                <div class="star star{$vote.points*2}"></div>
            {/block}
        </div>

        <div class="right_container">
            {block name="frontend_detail_comment_text"}
            {* Headline *}
            <div style="background-color: #e9e9e9;border-radius: 5px;padding: 5px;">
	            {block name='frontend_detail_comment_headline'}
	            <h3 style="color:#e1540f;">
                        <span {if $detail_widget}itemprop="summary"{/if}>{$vote.headline}</span>
                    </h3>
	            {/block}
	
	            {* Comment text *}
	            <p>
	            	<div class="open_quotes" style="float:left;"></div>
                        <span {if $detail_widget}itemprop="description"{/if}>{$vote.comment|nl2br}<b style="font-size: 33px; color:#e1540f;">&bdquo;</b></span>
	            </p>
            </div>
            {/block}
        </div>

        <div class="clear">
            &nbsp;
        </div>

    </div>

    {block name="frontend_detail_answer_block"}
    {if $vote.answer}
    <div class="comment_block answer">
        <div class="left_container">
            <strong class="author"> {se name="nfxDetailCommentInfoFrom"}{/se} {se name="nfxDetailCommentInfoFromAdmin"}Admin{/se} </strong>
            <span class="date"> {$vote.answer_date} </span>
        </div>
        <div class="right_container">
            {$vote.answer}
        </div>
        <div class="clear"></div>
    </div>
    {/if}
    {/block}

    {/foreach}

    <div class="space">
        &nbsp;
    </div>

    {/if}

    {block name='frontend_detail_comment_post'}
    {if $manual_ratings}
    {* Display notice if the shop owner needs to unlock a comment before it will'be listed *}
    {if {config name=VoteUnlock}}
    <div class="notice">
        <div class="center">
            <strong>{s name="nfxDetailCommentTextReview"}{/s}</strong>
        </div>
    </div>
    {/if}

    {* Write comment *}
    <h2 class="headingbox_dark"> {se name="nfxDetailCommentHeaderWriteReview"}{/se} </h2>
    <form method="post" action="{url action='rating' sArticle=$sArticle.articleID sCategory=$sArticle.categoryID}">
        <div>
            <a name="tabbox"></a>

            <fieldset>
                {* Name *}
                {block name='frontend_detail_comment_input_name'}
                <div>
                    <label for="sVoteName">{se name="nfxDetailCommentLabelName"}{/se}*: </label>
                    <input name="sVoteName" type="text" id="sVoteName" value="{$sFormData.sVoteName|escape}" class="text {if $sErrorFlag.sVoteName}instyle_error{/if}" />
                    <div class="clear">
                        &nbsp;
                    </div>
                </div>
                {/block}

                {* E-Mail address *}
                {if {config name=OptinVote} == true}
                {block name='frontend_detail_comment_input_mail'}
                <div>
                    <label for="sVoteMail">{se name="nfxDetailCommentLabelMail"}{/se}*: </label>
                    <input name="sVoteMail" type="text" id="sVoteMail" value="{$sFormData.sVoteMail|escape}" class="text {if $sErrorFlag.sVoteMail}instyle_error{/if}" />
                    <div class="clear">
                        &nbsp;
                    </div>
                </div>
                {/block}
                {/if}

                {* Comment summary*}
                {block name='frontend_detail_comment_input_summary'}
                <div>
                    <label for="sVoteSummary">{se name="nfxDetailCommentLabelSummary"}{/se}*:</label>
                    <input name="sVoteSummary" type="text" value="{$sFormData.sVoteSummary|escape}" id="sVoteSummary" class="text {if $sErrorFlag.sVoteSummary}instyle_error{/if}" />
                    <div class="clear">
                        &nbsp;
                    </div>
                </div>
                {/block}

                {* Star Rating *}
                {block name='frontend_detail_comment_input_rating'}
                <div>
                    <label for="sVoteStars">{se name="nfxDetailCommentLabelRating"}{/se}*:</label>
                    <select name="sVoteStars" class="normal" id="sVoteStars">
                        <option value="10">{s name="nfxRate10"}{/s}</option>
                        <option value="9">{s name="nfxRate9"}{/s}</option>
                        <option value="8">{s name="nfxRate8"}{/s}</option>
                        <option value="7">{s name="nfxRate7"}{/s}</option>
                        <option value="6">{s name="nfxRate6"}{/s}</option>
                        <option value="5">{s name="nfxRate5"}{/s}</option>
                        <option value="4">{s name="nfxRate4"}{/s}</option>
                        <option value="3">{s name="nfxRate3"}{/s}</option>
                        <option value="2">{s name="nfxRate2"}{/s}</option>
                        <option value="1">{s name="nfxRate1"}{/s}</option>
                    </select>
                    <div class="clear">
                        &nbsp;
                    </div>
                </div>
                {/block}

                {* Comment text *}
                {block name='frontend_detail_comment_input_text'}
                <div>
                    <label for="sVoteComment">{se name="nfxDetailCommentLabelText"}{/se}</label>
                    <textarea name="sVoteComment" id="sVoteComment" cols="3" rows="2" class="text {if $sErrorFlag.sVoteComment}instyle_error{/if}">{$sFormData.sVoteComment|escape}</textarea>
                    <div class="clear">
                        &nbsp;
                    </div>
                </div>
                {/block}

                {* Captcha *}
                {block name='frontend_detail_comment_input_captcha'}
                <div class="captcha">
                    <img src="{url controller='captcha' rand=$rand}" alt="" />
                    <div class="code">
                        <label>{se name="nfxDetailCommentLabelCaptcha"}{/se}</label>
                        <input type="text" name="sCaptcha"  class="text {if $sErrorFlag.sCaptcha}instyle_error{/if}" />
                        <input type="hidden" name="sRand"  value="{$rand}" />
                        <div class="clear">
                            &nbsp;
                        </div>
                    </div>
                </div>
                {/block}
                <div class="clear">
                    &nbsp;
                </div>
                <p>
                    {se name="nfxDetailCommentInfoFields"}{/se}
                </p>
            </fieldset>

            <div class="buttons">
                <input class="button-right large" type="submit" name="Submit" value="{s name="nfxDetailCommentActionSave"}{/s}"/>

                <div class="clear">
                    &nbsp;
                </div>
            </div>
        </div>
    </form>
    {/if}
    {/block}
</div>
{/block}

{block name="frontend_detail_comment_text" append}
<div class="ratingArea">
    {if $vote.rating.positive > 0}{s name="nfxHelpfulReviewsCount"}{/s}{/if}
</div>
{if $voteRatingThanks eq $vote.id}
<div class="thanksArea">
    {s name="nfxVoteThankYou"}{/s}
</div>
{/if}
{if $review_rating && !$vote.hide_buttons}
<form method="post" class="voteArea" onsubmit="return false;">
    {s name="nfxHelpfulReviewQuestion"}{/s}
    <input type="hidden" name="article_vote_id" value="{$vote.id}">
    <input type="submit" name="answer" value="{s name="nfxYes"}Ja{/s}" class="voteButton_yes" onclick="sendVoteRating(this)">
    <input type="submit" name="answer" value="{s name="nfxNo"}Nein{/s}" class="voteButton_no" onclick="sendVoteRating(this)">
</form>
{/if}
{/block}

{block name="frontend_index_content"}
<div {if $detail_widget}itemscope itemtype="http://data-vocabulary.org/Product"{/if}>
    {$smarty.block.parent}
</div>
{/block}

{block name='frontend_detail_index_name'}
    <span {if $detail_widget}itemprop="name"{/if}>
        {$smarty.block.parent}
    </span>
{/block}

{block name='frontend_detail_data_price_configurator'}
	{if $sArticle.minPriceOfVariants}
		<strong>
			<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}{/se}</span>
			{$sArticle.minPriceOfVariants|currency}&nbsp;{s name="Star" namespace="frontend/listing/box_article"}*{/s}
		</strong>
	{else}
		<strong {if $sArticle.priceStartingFrom && $sView} class="starting_price"{/if} {if $detail_widget}itemprop="offerDetails" itemscope itemtype="http://data-vocabulary.org/Offer"{/if}> 
		{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView} 
			<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}ab{/se}</span> 
			{$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}*{/s}
		{else}
			<span {if !$disable_price_config && $detail_widget}itemprop="price"{/if}>{$sArticle.price|currency:use_shortname}</span> 
			{s name="Star" namespace="frontend/listing/box_article"}*{/s}
		{/if} 
		</strong>
	{/if}
{/block}

{block name='frontend_detail_data_ordernumber' append}
    {if $detail_widget}
    <meta itemprop="identifier" content="sku:{$sArticle.ordernumber}"/>
    {/if}
{/block}

{block name='frontend_detail_index_supplier' append}
    {if $detail_widget}
    <meta itemprop="brand" content="{$sArticle.supplierName}"/>
    {/if}
{/block}

{block name='frontend_detail_description_text'}
    <div {if $detail_widget}itemprop="description"{/if}>
        {$smarty.block.parent}
    </div>
{/block}

{block name='frontend_detail_image_main'}
    {if $sArticle.image.res.relations}
        <div id="img{$sArticle.image.res.relations}" style="display:none">
            <a href="{$sArticle.image.src.5}"
            title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}"
            {if {config name=sUSEZOOMPLUS}}class="cloud-zoom-gallery"{/if}
            rel="lightbox"> <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" /> </a>
        </div>
    {/if}

    <a id="zoom1" href="{$sArticle.image.src.5}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" {if {config name=sUSEZOOMPLUS}}class="cloud-zoom"{/if} rel="lightbox[{$sArticle.ordernumber}]"> <img {if $detail_widget}itemprop="image"{/if} src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" /> </a>
{/block}