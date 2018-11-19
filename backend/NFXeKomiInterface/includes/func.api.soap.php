<?php
/*~ func.api.soap.php
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
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);
	$api          = 'http://api.ekomi.de/v2/wsdl';

	$client       = new SoapClient($api, array('exceptions' => 0));
	$get_settings = $client->getSettings(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION);
	$settings     = unserialize( utf8_decode( $get_settings ) );

	check_settings($settings);

	if(trim(EKOMI_MAIL_SENDER)!='') {
		$settings['mail_from_email'] = EKOMI_MAIL_SENDER;
	}
	ini_set('default_socket_timeout', $old);
	return $settings;

}



function send_order( $customer, $product_ids ) {
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);
	$api          = 'http://api.ekomi.de/v2/wsdl';

	$client       = new SoapClient($api, array('exceptions' => 0));
	$send_order   = $client->putOrder(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION, utf8_encode($customer['order_id']), utf8_encode($product_ids));
	$ret          = unserialize( utf8_decode( $send_order ) );
	ini_set('default_socket_timeout', $old);
	return $ret;

}


function get_snapshot() {
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);

    $api          = 'http://api.ekomi.de/v2/wsdl';

    $client       = new SoapClient($api, array('exceptions' => 0));
    $get_snapshot   = $client->getSnapshot(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION);
    $ret          = unserialize( utf8_decode( $get_snapshot ) );
    ini_set('default_socket_timeout', $old);
    return $ret;

}


function send_product( $product ) {
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);
	$api          = 'http://api.ekomi.de/v2/wsdl';

	$client       = new SoapClient($api, array('exceptions' => 0));
	$send_product = $client->putProduct(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION, utf8_encode($product['product_id']), utf8_encode($product['product_name']));

	$ret          = unserialize( utf8_decode( $send_product ) );
	ini_set('default_socket_timeout', $old);
	return $ret;

}



function send_ping( $type ) {
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);
	$api          = 'http://api.ekomi.de/v2/wsdl';

	$client       = new SoapClient($api, array('exceptions' => 0));
	$send_ping    = $client->putPing(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION, $type);
	$ret          = unserialize( utf8_decode( $send_ping  )  );
	ini_set('default_socket_timeout', $old);
	return true;

}



function send_log( $type, $errno, $errstr ) {
	$old = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', EKOMI_RESPONSE_TIMEOUT);
	$api          = 'http://api.ekomi.de/v2/wsdl';

	$client       = new SoapClient($api, array('exceptions' => 0));
	$send_log = $client->putLog(EKOMI_API_ID.'|'.EKOMI_API_KEY, EKOMI_VERSION, $type, $errno, utf8_encode($errstr));
	$ret          = unserialize( utf8_decode( $send_log  )  );
	ini_set('default_socket_timeout', $old);
	return true;

}
?>