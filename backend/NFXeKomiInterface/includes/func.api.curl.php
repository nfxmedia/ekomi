<?php
/*~ func.api.curl.php
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

	$api          = 'http://api.ekomi.de/v2/getSettings?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,EKOMI_RESPONSE_TIMEOUT);
	curl_setopt($ch, CURLOPT_URL, $api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$get_settings  = curl_exec($ch);
	curl_close($ch);

	$settings     = unserialize( $get_settings );

	check_settings($settings);

	if(trim(EKOMI_MAIL_SENDER)!='') {
		$settings['mail_from_email'] = EKOMI_MAIL_SENDER;
	}

	return $settings;

}


function send_order( $customer, $product_ids ) {

	$api          = 'http://api.ekomi.de/v2/putOrder?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&order_id='.urlencode($customer['order_id']).'&product_ids='.urlencode($product_ids);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,EKOMI_RESPONSE_TIMEOUT);
	curl_setopt($ch, CURLOPT_URL, $api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$send_order  = curl_exec($ch);
	curl_close($ch);
	$ret         = unserialize( $send_order );

	return $ret;

}

function send_product( $product ) {
	$api          = 'http://api.ekomi.de/v2/putProduct?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&product_id='.urlencode($product['product_id']).'&product_name='.urlencode($product['product_name']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,EKOMI_RESPONSE_TIMEOUT);
	curl_setopt($ch, CURLOPT_URL, $api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$send_product = curl_exec($ch);
	curl_close($ch);
	$ret          = unserialize( $send_product );

	return $ret;

}

function send_ping( $type ) {

	$api          = 'http://api.ekomi.de/v2/putPing?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&type='.$type;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,EKOMI_RESPONSE_TIMEOUT);
	curl_setopt($ch, CURLOPT_URL, $api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$send_ping    = curl_exec($ch);
	curl_close($ch);
	$ret          = unserialize( $send_ping );

	return true;

}


function send_log( $type, $errno, $errstr ) {

	$api          = 'http://api.ekomi.de/v2/putLog?auth='.EKOMI_API_ID.'|'.EKOMI_API_KEY.'&version='.EKOMI_VERSION.'&level='.$type.'&errnum='.$errno.'&errstr='.urlencode($errstr);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,EKOMI_RESPONSE_TIMEOUT);
	curl_setopt($ch, CURLOPT_URL, $api);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$send_log      = curl_exec($ch);
	curl_close($ch);
	$ret          = unserialize( $send_log );

return true;

}
?>