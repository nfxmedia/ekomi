{block name='frontend_checkout_confirm_agb_checkbox' append}
	{if !$no_opt_in}
		<div style="clear: both"></div>
		<div class="ekomi_confirm_checkbox" >
			<input class="ekomi_confirm_checkbox_input" type="checkbox" name="opt_in" id="opt_in" {if $opt_in_checked}checked{/if}/>
			<label class="chklabel modal_open ekomi_confirm_checkbox_label" for="opt_in">
				{s name="nfxEkomiConfirmCheckboxText"}Ich möchte den Einkauf später bewerten und von eKomi nach der Lieferung einmalig per E-Mail an die Abgabe einer Bewertung erinnert werden. Meine Einwilligung kann ich jederzeit widerrufen.{/s}
			</label>
		</div>
	{/if}
{/block}
