{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_agb' append}
	{if !$no_opt_in}
		<li class="block-group row--tos">
		    <div class="block column--checkbox">
		        <input type="checkbox" id="opt_in" name="opt_in" {if $opt_in_checked}checked="checked"{/if} />
		    </div>
		    <div class="block column--label">
		        <label for="opt_in">
		            {s name='nfxEkomiCheckoutOptin'}Ich möchte den Einkauf später bewerten und von eKomi nach der Lieferung einmalig per E-Mail an die Abgabe einer Bewertung erinnert werden. Meine Einwilligung kann ich jederzeit widerrufen.{/s}
		        </label>
		    </div>
		</li>
    {/if}
{/block}
