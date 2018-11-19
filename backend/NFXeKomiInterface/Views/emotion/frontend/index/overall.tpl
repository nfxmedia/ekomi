{block name='frontend_index_body_inline' append}
    {if $home_widget}
        <div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
            <meta itemprop="itemreviewed" content="{$shopName}"/>
            <span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
            <meta itemprop="average" content="{$averageRate}"/>
            <meta itemprop="best" content="{$maxRate}"/>  
            </span>
            <meta itemprop="votes" content="{$reviewsNum}"/> 
        </div>
    {/if}
{/block}
