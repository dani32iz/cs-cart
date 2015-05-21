<?php
/* **********************************************************
* Модуль доставки QIWI Post version 1.0					    *
* For CS-Cart                  								*
* @author Zoya Schegolihina zoya (at) qiwipost (dot) ru		*
* ******************************************************** */

if ( !defined('AREA') ) { die('Access denied'); }

fn_register_hooks(
    'calculate_cart_taxes_pre'
);