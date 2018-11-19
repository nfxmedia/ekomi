<?php
/*~ func.api.get.php
.---------------------------------------------------------------------------.
|  Software: eKomi                                                          |
|   Version: 2.0.2                                                          |
|   Updated: 2010-10-19                                                     |
|   Contact: +49 30 2000 444 999 | support@ekomi.de                         |
|      Info: http://ekomi.de                                                |
|   Support: http://ekomi.de                                                |
| ------------------------------------------------------------------------- |
|    Author: Simon Becker | eKomi                                           |
| Copyright: (c) 2008-2010, eKomi Ltd. All Rights Reserved.                 |
'--------------------------------------------------------------------------*/

function get_settings() {
	// timeout on slow response
    $context = stream_context_create(array('http'=> array(
        'timeout' => EKOMI_RESPONSE_TIMEOUT,
        'ignore_errors' => true,
    )));

	$api          = 'http://api.ekomi.de/v2/getSettings?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION;
	$get_settings = file_get_contents($api, false, $context);
	$settings     = unserialize( $get_settings );

	check_settings($settings);

	if(trim(EKOMI_MAIL_SENDER)!='') {
		$settings['mail_from_email'] = EKOMI_MAIL_SENDER;
	}

	return $settings;

}



function send_order( $customer ) {
	// timeout on slow response
    $context = stream_context_create(array('http'=> array(
        'timeout' => EKOMI_RESPONSE_TIMEOUT,
        'ignore_errors' => true,
    )));

	$api          = 'http://api.ekomi.de/v2/putOrder?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&order_id='.urlencode($customer['order_id']).'&product_ids='.urlencode($product_ids);
	$send_order   = file_get_contents($api, false, $context);
	$ret          = unserialize( $send_order );

	return $ret;

}



function send_product( $product ) {
	// timeout on slow response
    $context = stream_context_create(array('http'=> array(
        'timeout' => EKOMI_RESPONSE_TIMEOUT,
        'ignore_errors' => true,
    )));

	$api          = 'http://api.ekomi.de/v2/putProduct?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&product_id='.urlencode($product['product_id']).'&product_name='.urlencode($product['product_name']);
	$send_product = file_get_contents($api, false, $context);
	$ret          = unserialize( $send_product );

	return $ret;

}



function send_ping( $type ) {
	// timeout on slow response
    $context = stream_context_create(array('http'=> array(
        'timeout' => EKOMI_RESPONSE_TIMEOUT,
        'ignore_errors' => true,
    )));

	$api          = 'http://api.ekomi.de/v2/putPing?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&type='.$type;
	$send_ping    = file_get_contents($api, false, $context);
	$ret          = unserialize( $send_ping );

	return true;

}



function send_log( $type, $errno, $errstr ) {
	// timeout on slow response
    $context = stream_context_create(array('http'=> array(
        'timeout' => EKOMI_RESPONSE_TIMEOUT,
        'ignore_errors' => true,
    )));

	$api          = 'http://api.ekomi.de/v2/putLog?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&level='.$type.'&errnum='.$errno.'&errstr='.urlencode($errstr);
	$send_log     = file_get_contents($api, false, $context);
	$ret          = unserialize( $send_log );

	return true;

}
?>