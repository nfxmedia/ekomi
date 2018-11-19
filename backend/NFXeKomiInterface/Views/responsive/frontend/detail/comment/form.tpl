{extends file='parent:frontend/detail/comment/form.tpl'}
{namespace name="frontend/detail/comment"}

{* Display notice if the shop owner needs to unlock a comment before it will be listed *}
{block name='frontend_detail_comment_post_notice'}
    {if $manual_ratings}
        {if {config name=VoteUnlock}}
                {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='nfxDetailCommentTextReview'}Bewertungen werden nach &Uuml;berpr&uuml;fung freigeschaltet.{/s}"}
        {/if}
    {/if}
{/block}


{* Review Rating *}
{block name='frontend_detail_comment_input_rating'}
    <div class="field--select review--field">
        <span class="arrow"></span>
        <select name="sVoteStars">
            <option value="10">{s name="nfxEkomiRate5"}5 Sterne{/s}</option>
            <option value="8">{s name="nfxEkomiRate4"}4 Sterne{/s}</option>
            <option value="6">{s name="nfxEkomiRate3"}3 Sterne{/s}</option>
            <option value="4">{s name="nfxEkomiRate2"}2 Sterne{/s}</option>
            <option value="2">{s name="nfxEkomiRate1"}1 Stern{/s}</option>
        </select>
    </div>
{/block}