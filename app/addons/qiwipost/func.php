<?php
/* **********************************************************
* Модуль доставки QIWI Post version 1.0					    *
* For CS-Cart                  								*
* @author Zoya Schegolihina zoya (at) qiwipost (dot) ru		*
* ******************************************************** */

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;
use Tygh\Languages\Languages;

function fn_qiwipost_install()
{
	db_query( 'CREATE TABLE qiwipost_cash (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `q` text CHARACTER SET utf8,
		  `res` longtext CHARACTER SET utf8,
		  `lastupd` int(11) DEFAULT NULL,
		  `type` varchar(8) CHARACTER SET utf8 NOT NULL,
		  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8' );

    $objects = fn_qiwipost_schema();

    foreach ($objects as $object) {

        $service = fn_qiwipost_get_shipping_service($object['module']);

        if (!empty($service)) {
            continue;
        }

        $setting = array(
            'edition_type' => 'ROOT',
            'name' => $object['name'],
            'section_id' => 7,
            'section_tab_id' => 0,
            'type' => 'C',
            'value' => 'Y',
            'position' => 90,
            'is_global' => 'Y'
        );

        $object_id = db_query("INSERT INTO ?:settings_objects ?e", $setting);

        foreach (Languages::getAll() as $lang_code => $lang_data) {

            $setting_desc = array(
                'object_id' => $object_id,
                'object_type' => 'O',
                'lang_code' => $lang_code,
                'value' => $object['ru'],
                'tooltip' => ''
            );

            db_query("INSERT INTO ?:settings_descriptions ?e", $setting_desc);
        }

        $service = array(
            'status' => $object['status'],
            'module' => $object['module'],
            'code' => $object['code'],
            'sp_file' => $object['sp_file'],
            'description' => $object['description'],
        );

        $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

        foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
            db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
        }
    }
}

function fn_qiwipost_uninstall()
{	db_query( 'DROP TABLE qiwipost_cash');

    $objects = fn_qiwipost_schema();

    foreach ($objects as $object) {
        $object_ids = db_get_fields('SELECT object_id FROM ?:settings_objects WHERE name = ?s', $object['name']);

        if (!empty($object_ids)) {
            foreach ($object_ids as $object_id) {
                db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $object_id);
                db_query('DELETE FROM ?:settings_descriptions WHERE object_id = ?i', $object_id);
            }
        }

        $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', $object['module']);

        if (!empty($service_ids)) {
            db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
            db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
        }
    }
}

function fn_qiwipost_schema()
{
    return array(
        'qiwipost' => array(
            'name' => 'qiwipost_enabled',
            'ru' => 'Включить QIWI Post',
            'status' => 'A',
            'module' => 'qiwipost',
            'code' => 'QIWI Post',
            'sp_file' => '',
            'description' => 'Терминалы QIWI Post'
        )
    );
}

function fn_qiwipost_get_shipping_service($module)
{
    $service = db_get_row('SELECT * FROM ?:shipping_services WHERE `module` = ?s', $module);

    return $service;
}

function fn_qiwipost_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{	if (!empty($cart['shippings_extra']['data'])) {

        if (!empty($cart['shippings_extra']['data'])) {
            foreach($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach($shippings as $shipping_id => $shippings_extra) {

                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];
                        if ($module == 'qiwipost' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;

                            if (!empty($shippings_extra['selectedterminal'])) {
                                $product_groups[$group_key]['shippings'][$shipping_id]['qiwipost_id'] = $shippings_extra['qiwipost_id'];
                                $product_groups[$group_key]['shippings'][$shipping_id]['qiwipost_addr'] = $shippings_extra['qiwipost_addr'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];
                    if ($module == 'qiwipost' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }

    }

	return;
}

function fn_qiwipost_cash_param( $type )
{	$data = array(
		'normal' 		=> 60*60*24*30,
		'terminals' 	=> 60*60*24
	);
	return isset( $data[ $type ] ) ? $data[ $type ] : $data[ 'normal' ];}

function fn_qiwipost_Query( $url, $post=array(), $type='normal' )
{	static $query_cash;

	if ( !isset( $query_cash ) || !is_array( $query_cash ) )
	{		$query_cash = array();	}

	$cash = '';
	$q = $url.( count( $post ) > 0 ? json_encode( $post ) : '' );

	if ( !isset( $query_cash[ $q ] ) )
	{		db_query( 'DELETE FROM qiwipost_cash WHERE lastupd<?s && type=?s', time()-fn_qiwipost_cash_param( $type ), $type );

		$res = db_get_row( 'SELECT res FROM qiwipost_cash WHERE `q` = ?s ORDER BY lastupd DESC LIMIT 1', $q );
		if ( !empty( $res ) )
		{
			$cash = $res['res'];
		}

		if ( empty( $cash ) )
		{
			$ch = curl_init( $url );

			curl_setopt( $ch, CURLOPT_POST, ( count( $post ) > 0 ? 1 : 0 ) );
			if ( count( $post ) > 0 )
			{
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
			}
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$cash = curl_exec( $ch );

			if ( !empty( $cash ) )
			{
				db_query( 'INSERT INTO qiwipost_cash ?e', array( 'q'=>$q, 'res'=>$cash, 'lastupd'=>time(), 'type'=>$type ) );
			}
		}
		$query_cash[ $q ] = $cash;
	}
	else
	{		$cash = $query_cash[ $q ];	}

	return $cash;
}

function fn_qiwipost_ApiQuery( $post, $get, $out )
{
	$xml = fn_qiwipost_PostQuery( 'https://api.qiwipost.ru/?'.$get, $post );

	return $out == 'parse' ? simplexml_load_string( $xml ) : $xml;
}

function fn_qiwipost_Terminals()
{	$data = array();
	$xml = fn_qiwipost_Query( 'http://api.qiwipost.ru?do=listmachines_xml', array(), 'terminals' );	$qpdata = simplexml_load_string( $xml );

	if ( count( $qpdata ) > 0 && isset( $qpdata->machine ) )
	{
		foreach ( $qpdata->machine as $row )
		{			$data[ (string)$row->name ] = array( 'string'=>(string)$row->name.' '.(string)$row->town.' '.(string)$row->street.' '.(string)$row->buildingnumber, 'array'=>$row );
		}
	}

	return $data;
}

function fn_qiwipost_getregions()
{	$res = fn_qiwipost_Query( 'http://wt.qiwipost.ru/cscartstates', array(), 'normal' );
	$res = json_decode( $res );
	return is_object( $res ) ? $res : array();}

?>