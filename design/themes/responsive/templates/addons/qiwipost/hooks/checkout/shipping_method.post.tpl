{if $shipping.module == "qiwipost"}
<div class="">
    <span class="">{$qiwipost_title1}</span>
    <span class="qiwipost_gray" id="qiwipost_display_terminal">{$qiwipost_title2}</span>
</div>
<div>
    <a href="javascript:void(0);" onclick="QiwipostWidget.mapClick({ town: '{$qiwipost_city}', onlytown: 0, towntype: 1, mobile: '', mobileinput: 0, mobileconfirm: 0, calc: 0, callback: myQiwipostCallback });">{$qiwipost_title3}</a>
</div>
<input type="hidden" name="qiwipost_id" id="qiwipost_id" value="">
<input type="hidden" name="qiwipost_addr" id="qiwipost_addr" value="">
{/if}