{block name="frontend_detail-amp_buybox"}
	<div class="sw-product--buybox">
		{block name="frontend_detail-amp_buy_laststock"}
			{if !$sArticle.isAvailable && ($sArticle.isSelectionSpecified || !$sArticle.sConfigurator)}
				{include file="frontend/_includes/messages.tpl" type="error" content="{s name='DetailBuyInfoNotAvailable' namespace='frontend/detail/buy'}{/s}"}
			{/if}
		{/block}

		{block name="frontend_detail-amp_buy"}
			<form method="POST" action="{url controller=checkout action=addArticle}" class="sw-buybox--form">
			</form>
		{/block}
	</div>
{/block}
