{foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}
    {if $shipping.module == 'qiwipost' && $shipping.data.qiwipost_id}
        <p class="strong">
            {$shipping.data.qiwipost_id} {$shipping.data.qiwipost_addr}
        </p>
    {/if}
{/foreach}