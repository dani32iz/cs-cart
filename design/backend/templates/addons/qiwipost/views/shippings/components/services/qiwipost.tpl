<div>{__("shippings_qiwipost.salerequest")}</div>
<fieldset>

<div class="control-group">
    <label class="control-label" for="qp_calc_key">{__("shippings_qiwipost.calc_key")}</label>
    <div class="controls">
        <input id="qp_calc_key" type="text" name="shipping_data[service_params][calc_key]" size="30" value="{$shipping.service_params.calc_key}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="qp_dims">{__("shippings_qiwipost.dims")}</label>
    <div class="controls">
        <input id="qp_dims" type="text" name="shipping_data[service_params][dims]" size="30" value="{$shipping.service_params.dims}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="qp_nds">{__("shippings_qiwipost.nds")}</label>
    <div class="controls">
        <input id="qp_nds" type="checkbox" name="shipping_data[service_params][nds]" value="1"{if $shipping.service_params.nds==1} checked{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="qp_tens">{__("shippings_qiwipost.tens")}</label>
    <div class="controls">
        <input id="qp_tens" type="checkbox" name="shipping_data[service_params][tens]" value="1"{if $shipping.service_params.tens==1} checked{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="qp_pack">{__("shippings_qiwipost.pack")}</label>
    <div class="controls">
        <input id="qp_pack" type="checkbox" name="shipping_data[service_params][pack]" value="1"{if $shipping.service_params.pack==1} checked{/if} />
    </div>
</div>

</fieldset>