 {extends file='parent:frontend/detail/tabs/comment.tpl'}

{block name="frontend_detail_tabs_rating_title" append}
    <div class="product--ekomi-average-rating">
        <h3>
            {s name='nfxDetailCommentInfoAverageRate'}Durchschnittliche Kundenbewertung:{/s}
             <span class="product--rating">
                {* Average calculation *}
                {$average = $sArticle.sVoteAverage.average / 2}
                {* Stars *}
                    {if $sArticle.sVoteAverage.average != 0}
                        {for $value=1 to 5}
                            {$cls = 'icon--star'}

                            {if $value > $average}
                                {$diff=$value - $average}

                                {if $diff > 0 && $diff <= 0.5}
                                    {$cls = 'icon--star-half'}
                                {else}
                                    {$cls = 'icon--star-empty'}
                                {/if}
                            {/if}

                            <i class="{$cls}"></i>
                        {/for}
                    {/if}
                    {if $sArticle.sVoteAverange eq NULL }
                        {$sArticle.sVoteAverange = $sArticle.sVoteAverage}
                    {/if}
                    {s name='nfxDetailCommentInfoRating'}(aus {$sArticle.sVoteAverange.count} Kundenbewertungen){/s}
            </span>
        <h3>
    </div>
    <div class="product--ekomi-box">
        {if $rating_filter}
            <div class="product--ekomi-ratings">
                {foreach from=$sArticle.sVoteAverage.pointCount item=point}
                    {if $point.points > 0}
                        <a class="product--rating f_filter" data-vote-filter="filter_{$point.points}" data-vote-num="{$point.points}" href="javascript:void(0);" >
                            <span class="product--ekomi-star-cont">
                                {for $value=1 to $point.points}
                                    <i class="icon--star"></i>
                                {/for}
                            </span>
                            <span class="product--ekomi-percent-cont">
                                <span class="product--ekomi-percent" style="width:{($point.total/$sArticle.sVoteAverange.count*100)|round:"2"}%;"></span>
                            </span>
                            <span>
                                {$point.total}
                            </span>
                        </a>
                    {/if}
                {/foreach}
            </div>
        {/if}
        
        
        {if $ekomi_logo}
            <div class="product--ekomi-widget">
                <a href="{$ekomi_icon_url}" class="product--ekomi-widget-link" target="_blank"></a>
            </div>
        {/if}
    </div>

    {if $showVsRatingArea eq "t" && $best_worst_reviews && $bestVote.total >= 1}
        <div class="ekomi--vs-rating-area">
            <div class="ekomi--vs-rating-area-heading">
                <div class="ekomi--vs-rating-area-heading-title">
                    {s name="nfxRatingAreaHeadingPositive"}{/s}
                </div>
                <div class="ekomi--vs-rating-area-heading-title">
                    {s name="nfxRatingAreaHeadingNegative"}{/s}
                </div>
            </div>
            <div class="ekomi--vs-rating-area-content">
                <div class="ekomi--vs-rating-area-review-block1 ekomi--vs-rating-area-review-block">
                {if $bestVote.total >= 1}
                    <div class="ekomi--vs-rating-area-review-stat">
                        {s name="nfxHelpfulReviewsCountVS1"}{/s}
                    </div>
                    <div class="ekomi--vs-rating-area-review-author">
                        {for $bestVoteVal=1 to $bestVote.points}
                            <i class="icon--star"></i>
                        {/for}    
                        <div class="ekomi--vs-rating-area-review-author-from">
                                {s name="nfxFrom"}{/s} <span class="name">{$bestVote.name}</span> <b>{$bestVote.datum}</b>
                        </div>
                    </div>

                    <div class="ekomi--vs-rating-area-review-text" style="padding-left: 10px;">
                        {$bestVote.comment|nl2br}
                    </div>
                {/if}
                </div>
                <div class="ekomi--vs-rating-area-review-block2 ekomi--vs-rating-area-review-block">
                    {if $worstVote}
                    <div class="ekomi--vs-rating-area-review-stat">
                        {s name="nfxHelpfulReviewsCountVS2"}{/s}
                    </div>
                    <div class="ekomi--vs-rating-area-review-author">
                        {for $worstVoteVal=1 to $worstVote.points}
                            <i class="icon--star"></i>
                        {/for} 
                        <div class="ekomi--vs-rating-area-review-author-from">
                            {s name="nfxFrom"}{/s} <span class="name">{$worstVote.name}</span> <b>{$worstVote.datum}</b>
                        </div>
                    </div>
                    <div class="ekomi--vs-rating-area-review-text">
                        {$worstVote.comment|nl2br}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}

    {* Display comments *}
    {if $sArticle.sVoteComments}
        <div class="ekomi-rating-filter hidden">
            {s name='nfxCancelFilterText'}<span>Anzeige von <span class="ekomi-rating-filter-result"></span>-Sterne Rezensionen</span><a href="javascript:void(0);" id="f_cancelFilter">Filter aufheben</a>{/s}
        </div>
    {/if}
    
{/block}

{block name="frontend_detail_comment_block"}
     <input type="hidden" class="ekomi-filter filter_{$vote.points}" value="filter_{$vote.points}">
    {$smarty.block.parent}
    <div class="ekomi--comments-vote-area-cont">
        <div class="alert is--success is--rounded thanks-text hidden">
            <div class="alert--icon">
            <i class="icon--element icon--check"></i>
            </div>
            <div class="alert--content">
            {s name='nfxVoteThankYou'}{/s}
            </div>
        </div>
        {if $vote.rating.positive > 0}
            {s name="nfxHelpfulReviewsCount"}<b>{$vote.rating.positive}</b> von <b>{$vote.rating.total} Kunden</b> fanden diese Bewertung hilfreich{/s}
        {/if}
        {if $review_rating && !$vote.hide_buttons}
            <form class="ekomi--comments-vote-area" method="post">
                {s name='nfxHelpfulReviewQuestion'}War diese Rezension f�r Sie hilfreich?{/s}
                <input type="hidden" value="{$vote.id}" name="article_vote_id">
                <span class="thanks-text hidden">{s name='nfxVoteThankYou'}Vielen Dank! Das Feedback f�r diese Bewertung wurde erfolgreich �bermittelt.{/s}</span>
                <a class="ekomi--comments-vote-area-button ekomi--comments-vote-area-button-yes" href="javascript:void(0);" data-answer="yes"><i class="icon--thumbsup"></i>{s name='nfxHelpfulReviewAnswerYes'}Ja{/s}</a>
                <a class="ekomi--comments-vote-area-button ekomi--comments-vote-area-button-no" href="javascript:void(0);" data-answer="no"><i class="icon--thumbsdown"></i>{s name='nfxHelpfulReviewAnswerNo'}Nein{/s}</a>
            </form>
        {/if}
    </div>
{/block} 
