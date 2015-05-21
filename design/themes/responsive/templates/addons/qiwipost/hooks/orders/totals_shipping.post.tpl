{foreach from=$order_info.shipping item="shipping_method"}
	{if $shipping_method.data.qiwipost_id}
        <p class="">
            {$shipping_method.data.qiwipost_id} {$shipping_method.data.qiwipost_addr}
        </p>
    {/if}
{/foreach}