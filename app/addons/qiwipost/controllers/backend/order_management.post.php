<?php
/* **********************************************************
* Модуль доставки QIWI Post version 1.0					    *
* For CS-Cart                  								*
* @author Zoya Schegolihina zoya (at) qiwipost (dot) ru		*
* ******************************************************** */

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & $_SESSION['cart'];

if ( isset( $cart['shipping'] ) )
{
	foreach ( $cart['shipping'] as $k=>$v )
	{
		if ( $v['module'] == 'qiwipost' )
		{
			$qiwipost_city = '';
			$qiwipost_regions = fn_qiwipost_getregions();

			$state = isset( $cart['user_data']['s_state'] ) ? $cart['user_data']['s_state'] : ( isset( $cart['product_groups'][0]['package_info']['location']['state'] ) ? $cart['product_groups'][0]['package_info']['location']['state'] : '' );
			if ( !empty( $state ) && isset( $qiwipost_regions->$state ) )
			{
				$qiwipost_city = $qiwipost_regions->$state;
			}
			Registry::get('view')->assign( 'qiwipost_city', $qiwipost_city );
			Registry::get('view')->assign( 'qiwipost_title1', __('qiwipost_title4') );
			Registry::get('view')->assign( 'qiwipost_title2', __('qiwipost_title2') );
			Registry::get('view')->assign( 'qiwipost_title3', __('qiwipost_title3') );
		}
		break;
	}
}

?>