{if $shipping.module == "qiwipost"}
<script src="http://wt.qiwipost.ru/selectterminal?div=qiwipost_widget&town=москва&dropdown=1&combobox=0&emptydropvalue=0"></script>
<script type="text/javascript">
    function myQiwipostCallback( t ){
        $('#qiwipost_display_terminal').html( t.name+' '+t.addr );
        $('#qiwipost_id').val( t.name );
        $('#qiwipost_addr').val( t.addr );
    }
</script>

<style src="http://wt.qiwipost.ru/css/gray/jquery-ui-1.10.4.custom.min.css"></style>

<span id="qiwipost_widget" style="display: none;"></span>

<div class=""><span class="">{$qiwipost_title1}</span> <span class="qiwipost_gray" id="qiwipost_display_terminal">{$qiwipost_title2}</span></div>
<div><a href="javascript:void(0);" onclick="QiwipostWidget.mapClick({ town: '{$qiwipost_city}', onlytown: 0, towntype: 1, mobile: '', mobileinput: 0, mobileconfirm: 0, calc: 0, callback: myQiwipostCallback });">{$qiwipost_title3}</a></div><input type="hidden" name="qiwipost_id" id="qiwipost_id" value=""><input type="hidden" name="qiwipost_addr" id="qiwipost_addr" value="">
{/if}