{block name="frontend_index_content"}
<div itemscope itemtype="http://data-vocabulary.org/Product">
    {$smarty.block.parent}
</div>
{/block}

{block name='frontend_detail_index_name'}
<span itemprop="name">{$smarty.block.parent}</span>
{/block}

{block name='frontend_detail_data_price_configurator'}
	{if $sArticle.minPriceOfVariants}
		<strong>
			<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}{/se}</span>
			{$sArticle.minPriceOfVariants|currency}&nbsp;{s name="Star" namespace="frontend/listing/box_article"}*{/s}
		</strong>
	{else}
		<strong {if $sArticle.priceStartingFrom && $sView} class="starting_price"{/if} itemprop="offerDetails" itemscope itemtype="http://data-vocabulary.org/Offer"> 
		{if $sArticle.priceStartingFrom && !$sArticle.sConfigurator && $sView} 
			<span id="DetailDataInfoFrom">{se name="DetailDataInfoFrom"}ab{/se}</span> 
			{$sArticle.priceStartingFrom|currency} {s name="Star" namespace="frontend/listing/box_article"}*{/s}
		{else}
			{*}<span itemprop="currency">{"0"|currency:use_shortname|truncate:3:"":true}</span>{*} 
			<span {if !$disable_price_config}itemprop="price"{/if}>{$sArticle.price|currency:use_shortname}</span> 
			{s name="Star" namespace="frontend/listing/box_article"}*{/s}
		{/if} 
		</strong>
	{/if}
{/block}

{block name='frontend_detail_data_block_prices_start' append}
{if !$disable_price_config}
  <div itemprop="offerDetails" itemscope itemtype="http://data-vocabulary.org/Offer">
      <meta itemprop="price" content="{$sArticle.price|currency:use_shortname}"/>
  </div>
{/if}
{/block}

{block name='frontend_detail_data_ordernumber' append}
<meta itemprop="identifier" content="sku:{$sArticle.ordernumber}"/>
{/block}

{block name='frontend_detail_index_supplier' append}
<meta itemprop="brand" content="{$sArticle.supplierName}"/>
{/block}

{block name='frontend_detail_description_text'}
<div itemprop="description">
    {$smarty.block.parent}
</div>
{/block}

{block name="frontend_detail_index_tabs_rating" prepend}
    {if $sArticle.sVoteAverange eq NULL }
        {$sArticle.sVoteAverange = $sArticle.sVoteAverage}
        {$sArticle.sVoteAverange.averange = $sArticle.sVoteAverage.average}
    {/if}
    {if $sArticle.sVoteAverange.count}
    <div class="overview_rating" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
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
    </div>
    {/if}
    
{/block}

{block name='frontend_detail_comment_post' prepend}
  {* Display comments *}
  {if $sArticle.nfxVoteComments}
    {foreach name=comment from=$sArticle.nfxVoteComments item=vote}
      <div class="comment_block{if $smarty.foreach.comment.last} last{/if}{if $vote.answer} no_border{/if} page1 filter{$vote.points} pagef1" itemscope itemtype="http://data-vocabulary.org/Review">
		<meta itemprop="itemreviewed" content="{$sArticle.articleName}"/>
        <div class="left_container">
        {* Author *}
          <strong class="author">
            {se name="DetailCommentInfoFrom" namespace="frontend/detail/comment"}{/se} <span class="name" itemprop="reviewer">{$vote.name}</span>
          </strong>

        {* Date *}
        {block name='frontend_detail_comment_date'}
          <span class="date">
            <span itemprop="dtreviewed" datetime="{$vote.datum}">{$vote.datum}</span>
          </span>
        {/block}
        
        {* Star rating *}
          <meta itemprop="rating" content="{$vote.points}"/>
          <meta itemprop="best" content="5"/>
          <meta itemprop="worst" content="0.5"/>
          <div class="star star{$vote.points*2}"></div>
        </div>
        <div class="right_container">
        {block name='frontend_detail_comment_text'}
          {* Headline *}
          {block name='frontend_detail_comment_headline'}
            <h3><span itemprop="summary">{$vote.headline}</span></h3>
          {/block}

          {* Comment text *}
          <p>
            <span itemprop="description">{$vote.comment|nl2br}</span>
          </p>
        {/block}
        </div>



        <div class="clear">&nbsp;</div>

      </div>

            {block name="frontend_detail_answer_block"}
                {if $vote.answer}
                <div class="comment_block answer">
                    <div class="left_container">
                        <strong class="author">
                            {se name="DetailCommentInfoFrom" namespace="frontend/detail/comment"}{/se} {se name="DetailCommentInfoFromAdmin"}Admin{/se}
                        </strong>
                        <span class="date">
                            {$vote.answer_date}
                        </span>
                    </div>
                    <div class="right_container">
                        {$vote.answer}
                    </div>
                    <div class="clear"></div>
                </div>
                {/if}
            {/block}

    {/foreach}

    <div class="space">&nbsp;</div>

  {/if}
{/block}