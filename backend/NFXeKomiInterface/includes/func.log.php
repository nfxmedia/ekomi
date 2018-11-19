<?php

/* ~ func.log.php
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
  '-------------------------------------------------------------------------- */

use Shopware\Models\Mail\Mail,
    Shopware\Models\Mail\Attachment;

function write_log($errno, $errstr, $log_external = false, $type = 'INFO') {

    if (EKOMI_LOG_ENABLED) {
        $handle_log = fopen(EKOMI_LOG_FILE, 'a'); // w = open file for writing
        fwrite($handle_log, '[' . date('Y-m-d H:i:s') . '] ' . $errno . ': ' . $errstr . "\n");
        fclose($handle_log);
    }

    if ($log_external) {
        send_log($type, $errno, $errstr);
    }
}

function handle_error($errstr, $exit = false) {

    write_log(500, 'ERROR: ' . $errstr, true, 'ERROR');
    $mailContext["content"] = 'ERROR ON ' . EKOMI_API_ID . '<br>' . $errstr . '<br>' . serialize(json_encode(debug_backtrace()));
    $mailContext["subject"] = 'ERROR ON ' . EKOMI_API_ID;
    $mail = Shopware()->TemplateMail()->createMail('eKOMIERRORHANDLE', $mailContext);
    $mail->clearRecipients();
    $emailAddress = 'TODO@add_your.address';
    $mail->addTo($emailAddress);
    $mail->send();
    if (!$mail->send()) {
        write_log(500, 'ERROR: sending errormail failed', false, 'ERROR');
    }

    if ($exit) {
        exit('An error occured. See log for further information.');
    }
}

function handle_mysql_error($file, $line, $sql) {

    handle_error('MySQL Error in ' . $file . ' on line ' . $line . ': ' . $sql . ' - ERR: ' . mysql_error(), true);
}

?>