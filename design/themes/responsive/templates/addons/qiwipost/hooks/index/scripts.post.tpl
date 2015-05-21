{if $runtime.controller == "checkout" && $runtime.mode == "checkout"}
{script src="http://wt.qiwipost.ru/selectterminal?div=qiwipost_widget&town=санкт-петербург&dropdown=1&combobox=1&emptydropvalue=1"}
<script type="text/javascript">
    function myQiwipostCallback( t ){
        //alert( 'Выбран терминал '+t.name+' '+t.addr );
        //fn_calculate_total_shipping_cost();
        $('#qiwipost_display_terminal').html( t.name+' '+t.addr );
        $('#qiwipost_id').val( t.name );
        $('#qiwipost_addr').val( t.addr );
    }
</script>
{/if}