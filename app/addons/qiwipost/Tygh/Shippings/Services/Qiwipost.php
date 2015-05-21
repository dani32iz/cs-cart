<?php
/* **********************************************************
* Модуль доставки QIWI Post version 1.0					    *
* For CS-Cart                  								*
* @author Zoya Schegolihina zoya (at) qiwipost (dot) ru		*
* ******************************************************** */

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;

class Qiwipost implements IService
{
    private $_allow_multithreading = false;

    private $_error_stack = array();

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        return array();
    }

    public function processErrors($response)
    {

        if (!empty($this->_error_stack)) {
            $error = '';
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    public function getRequestData()
    {
        $request_data = array();

        return $request_data;
    }

    public function getSimpleRates()
    {
        $response = $this->getRequestData();

        return $response;
    }

    public function processResponse($response)
    {
		if ( isset( $_REQUEST['qiwipost_id'] ) )
		{
			$_SESSION['cart']['shippings_extra']['data'][ $this->_shipping_info['keys']['group_key'] ][ $this->_shipping_info['keys']['shipping_id'] ]['qiwipost_id'] = $_REQUEST['qiwipost_id'];
			$_SESSION['cart']['shippings_extra']['data'][ $this->_shipping_info['keys']['group_key'] ][ $this->_shipping_info['keys']['shipping_id'] ]['qiwipost_addr'] = $_REQUEST['qiwipost_addr'];
			$_SESSION['cart']['qiwipostselectedterminal_id'] = $_REQUEST['qiwipost_id'];
			$_SESSION['cart']['qiwipostselectedterminal_addr'] = $_REQUEST['qiwipost_addr'];
		}

    	$return = array(
			'cost' => false,
			'error' => '',
			'delivery_time' => ''
		);

		$qiwipost_city = '';
		$qiwipost_regions = fn_qiwipost_getregions();

		$state = isset( $this->_shipping_info['package_info']['location']['state'] ) ? $this->_shipping_info['package_info']['location']['state'] : ( isset( $_SESSION['cart']['user_data']['s_state'] ) ? $_SESSION['cart']['user_data']['s_state'] : '' );
		if ( isset( $qiwipost_regions->{$state} ) )
		{
			$qiwipost_city = $qiwipost_regions->{$state};
		}

		$terminals = fn_qiwipost_Terminals();
		$terminals_num = 0;
		foreach ( $terminals as $k=>$row )
		{			if ( (string)$row[ 'array' ]->town == $qiwipost_city || (string)$row[ 'array' ]->citygroup == $qiwipost_city )
			{				$terminals_num++;
				break;			}		}

		if ( empty( $qiwipost_city ) || $terminals_num == 0 )
		{			return $return;		}

    	$dims = array();
    	$w = 0;
    	if ( !isset( $this->_shipping_info['service_params']['dims'] ) )
		{			$this->_shipping_info['service_params']['dims'] = 1;		}
		if ( !isset( $this->_shipping_info['service_params']['pack'] ) )
		{
			$this->_shipping_info['service_params']['pack'] = 0;
		}
		if ( !isset( $this->_shipping_info['service_params']['nds'] ) )
		{
			$this->_shipping_info['service_params']['nds'] = 0;
		}
		if ( !isset( $this->_shipping_info['service_params']['tens'] ) )
		{
			$this->_shipping_info['service_params']['tens'] = 0;
		}

    	foreach ( $this->_shipping_info['package_info']['packages'] as $row )
    	{    		if ( isset( $row['shipping_params'] ) )
    		{    			for ( $i=1; $i<=(int)$row['amount']; $i++ )
    			{
    				$dims[] = array(
    					0 => (int)$row['shipping_params']['box_length']*(float)$this->_shipping_info['service_params']['dims'],
    					1 => (int)$row['shipping_params']['box_width']*(float)$this->_shipping_info['service_params']['dims'],
    					2 => (int)$row['shipping_params']['box_height']*(float)$this->_shipping_info['service_params']['dims']
    				);
    			}    		}
    		$w += $row['weight']*(int)$row['amount'];    	}

    	if ( $w > $this->_shipping_info['service_params']['calc_key'] )
    	{    		$return['error'] = __('qiwipost_weighterror');    	}
    	$post = array();

		if ( $this->_shipping_info['service_params']['pack'] == 1 )
		{
	    	$post = array(
	    		'dimensions' => json_encode( $dims )
	    	);
	  	}

    	$res = json_decode( fn_qiwipost_Query( 'http://wt.qiwipost.ru/calc?type=json&key='.$this->_shipping_info['service_params']['calc_key'].'&cscartcity='.$this->_shipping_info['package_info']['location']['state'].'&nds='.( $this->_shipping_info['service_params']['nds'] == 1 ? '1' : '0' ).'&tens='.( $this->_shipping_info['service_params']['tens'] == 1 ? '1' : '0' ), $post, 'normal' ) );

    	if ( $res && ( !isset( $res->error ) || empty( $res->error ) ) )
    	{    		$return = array(
	            'cost' => $res->price,
	            'error' => false,
	            'delivery_time' => $res->deliverytime
	        );    	}
    	else
    	{
	        $return['error'] = isset( $res->error ) ? $res->error : 'Unknown error';
	    }

        return $return;
    }
}
