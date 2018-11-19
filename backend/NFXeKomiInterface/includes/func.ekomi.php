<?php
/*~ func.ekomi.php
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

function get_customers_to_send( $delay ) {

    // calc timestamp
    $timestamp = time() - $delay * 24 * 60 * 60;
    $return_customers = array();
    
    // check storage method = db
    if( EKOMI_STORAGE == 'db' ) {
    
        // build query and select customers ready to send
        $sql = "SELECT `order_id`, `first_name`, `last_name`, `email`, `product_ids`, `product_names`  FROM `" . EKOMI_DB_TABLE . "` WHERE `time_read` < $timestamp AND `time_sent` IS NULL LIMIT 100";

        $get_customers = mysql_query($sql) OR handle_mysql_error(__FILE__, __LINE__, $sql); 
        unset($sql);
    
        // loop thru
        while( $customer = mysql_fetch_assoc( $get_customers ) ) {
    
            // write to array
            $return_customers[] = $customer;
    
        }
    
    }
    // check storage method = file
    elseif( EKOMI_STORAGE == 'file' ) {

        // read customers from file
        $get_customers = file( EKOMI_CUSTOMERS_FILE );

        // loop thru
        foreach( $get_customers AS $customer ) {

            // split row into fields
            $customer = explode(';', $customer);

            // build array
            $return_customers[] = array(
              'order_id'      => $customer[0],
              'email'         => $customer[1],
              'first_name'    => $customer[2],
              'last_name'     => $customer[3],
              'product_ids'   => $customer[4],
              'product_names' => $customer[5]
            );
        }
    }
    // handle other storage method
    else {
    
    // handle error and exit
    handle_error('unknown storage type: "'.EKOMI_STORAGE.'"', true);

    }

    if(empty($return_customers) || count($return_customers)) {
        write_log(101, 'no mails to send', false);
    }
    
    // return customers
    return $return_customers;
}





function set_sent( $customer ) {

    write_log(100, 'Mail successfully sent: '. $customer['email'], false);

    // check storage method = db
    if( EKOMI_STORAGE == 'db' ) {
    
        // build query and select customers ready to send
        $sql = "UPDATE `" . EKOMI_DB_TABLE . "` SET time_sent = ".time() ." WHERE `order_id` = '" . $customer['order_id'] . "'";
        mysql_query($sql) OR handle_mysql_error(__FILE__, __LINE__, $sql);
        unset($sql);

    }
    // check storage method = file
    elseif( EKOMI_STORAGE == 'file' ) {

        // read customers from file
        $get_current_customers = file( EKOMI_CUSTOMERS_FILE );

        // loop thru
        foreach( $get_current_customers AS $current_customer ) {

            // split row into fields
            $current_customer = explode(';', $current_customer);

            $current_customers[] = $current_customer[0];
        }

        $key = array_search( $customer['order_id'], $current_customers );

        $handle_old_customers = fopen( EKOMI_OLD_CUSTOMERS_FILE, 'a' ); // w = open file for writing
        fwrite($handle_old_customers, trim($get_current_customers[$key]) . ';' .time() . ';0'."\n" );
        fclose($handle_old_customers);

        unset ($get_current_customers[$key]);

        $handle_customers = fopen( EKOMI_CUSTOMERS_FILE, 'w' ); // w = clear file and open for writing
        fwrite($handle_customers, implode("\n", $get_current_customers) );
        fclose($handle_customers);

    }
    // handle other storage method
    else {
    
    // handle error and exit
    handle_error('unknown storage type: "'.EKOMI_STORAGE.'"', true);

    }
    
    return true;
}


function manage_products( $customer ) {

    $products = unserialize($customer['product_names']);

	if(is_array($products)) {
    
		foreach( $products AS $product_id => $product_name) {

			send_product( array( 'product_id' => $product_id, 'product_name' => $product_name ) );
			$product_ids[]=$product_id;
			
		}
		
		return implode(',',$product_ids);
		
	}
	
	return '';
	
}

function send_mail( $customer, $link, $mail_settings ) {



    $search        = array( '{vorname}', '{nachname}', '{ekomilink}' );
    $replace_html  = array( $customer['first_name'], $customer['last_name'], '<a href="'.$link.'" target="_blank">'.$link.'</a>');
    $replace_plain = array( $customer['first_name'], $customer['last_name'], $link);

    // HTML E-Mail
    $mail_html  = str_replace($search, $replace_html, $mail_settings['mail_html']);
    $mail_plain = str_replace($search, $replace_plain, $mail_settings['mail_plain']);
   
   
    // send mail
    $mail           = new PHPMailer();
    
    // set smtp settings if smtp mode is enabled
    if(EKOMI_SMTP_MODE == 1){
    
        $mail->Host     = EKOMI_SMTP_SERVER . ':' . EKOMI_SMTP_PORT;
        $mail->Mailer   = "smtp";
        $mail->SMTPAuth = true;
        $mail->Username = EKOMI_SMTP_USER;
        $mail->Password = EKOMI_SMTP_PASS;

    }

    $mail->From     = $mail_settings['mail_from_email'];
    $mail->FromName = $mail_settings['mail_from_name'];
    $mail->Subject  = $mail_settings['mail_subject'];
    $mail->Body     = $mail_html;
    $mail->AltBody  = $mail_plain;
    $mail->AddAddress( $customer['email'] );
    
    if($mail->Send()) {
        return true;
    }
    else {
        handle_error('sending mail failed' . $mail->ErrorInfo, true);
    }

}




function check_api_type() {
	if ( class_exists( 'SoapClient' ) ) {
		return 'soap';
	}
	if ( function_exists( 'curl_init' ) ) {
	    return 'curl';
	}
	if ( ini_get( 'allow_url_fopen' ) == '1' ) {
	    return 'get';
	}

	handle_error('no api type available', true);

}

function check_settings($settings) {
	if(!is_array($settings))                            handle_error('func.api.'.check_api_type().'.php: get_settings did not return an array', true);
	if(count($settings)==0)                             handle_error('func.api.'.check_api_type().'.php: get_settings returned empty array', true);
	if(!array_key_exists('mail_subject', $settings))    handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_subject', true);
	if(!array_key_exists('mail_plain', $settings))      handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_plain', true);
	if(!array_key_exists('mail_html', $settings))       handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_html', true);
	if(!array_key_exists('mail_delay', $settings))      handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_delay', true);
	if(!array_key_exists('mail_from_name', $settings))  handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_from_name', true);
	if(!array_key_exists('mail_from_email', $settings)) handle_error('func.api.'.check_api_type().'.php: get_settings did not return mail_from_name', true);
	if(empty($settings['mail_subject']))                handle_error('func.api.'.check_api_type().'.php: get_settings returned empty mail_subject', true);
	if(empty($settings['mail_plain']))                  handle_error('func.api.'.check_api_type().'.php: get_settings returned empty mail_plain', true);
	if(empty($settings['mail_html']))                   handle_error('func.api.'.check_api_type().'.php: get_settings returned empty mail_html', true);
	if(empty($settings['mail_from_name']))              handle_error('func.api.'.check_api_type().'.php: get_settings returned empty mail_from_name', true);
	if(empty($settings['mail_from_email']))             handle_error('func.api.'.check_api_type().'.php: get_settings returned empty mail_from_name', true);
	if($settings['mail_delay']==0)                      log('func.api.'.check_api_type().'.php: get_settings returned 0 days mail_delay', true);

	return true;

}



function startup() {


    // startup db
    if(EKOMI_STORAGE == 'db') {

        mysql_connect(EKOMI_DB_SERVER, EKOMI_DB_USER, EKOMI_DB_PASS) OR handle_mysql_error(__FILE__, __LINE__, $sql);
        mysql_select_db(EKOMI_DB_NAME) OR handle_mysql_error(__FILE__, __LINE__, $sql);

    }
    // check storage permissions
    elseif(EKOMI_STORAGE == 'file') {
	
        if(!is_writable(EKOMI_CUSTOMERS_FILE)) {
		
            handle_error(EKOMI_CUSTOMERS_FILE . ' is not writable', true);
			
        }
        if(!is_writable(EKOMI_OLD_CUSTOMERS_FILE)) {
		
            handle_error(EKOMI_CUSTOMERS_FILE . ' is not writable', true);
			
        }
    
    }

    // check log storage permissions
    if(EKOMI_LOG_ENABLED && !is_writable(EKOMI_LOG_FILE)) {
	
        handle_error(EKOMI_LOG_FILE . ' is not writable', true);
		
    }

    return true;
}

?>