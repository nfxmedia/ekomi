<?php
/*~ config.inc.php
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


/***************************
*  api settings            *
***************************/



/***************************
* storage settings         *
***************************/

// where to store the data. values: 'db' or 'file'
// 'file' is deprecated and will be removed soon
define( 'EKOMI_STORAGE',                 'db');



/***************************
* database settings        *
***************************/

// only needed when EKOMI_STORAGE=db
define( 'EKOMI_DB_SERVER',               '' );
define( 'EKOMI_DB_USER',                 '' );
define( 'EKOMI_DB_PASS',                 '' );
define( 'EKOMI_DB_NAME',                 '' );
define( 'EKOMI_DB_TABLE',                EKOMI_API_ID . '_ekomi_customers' );



/***************************
* file storage settings    *
***************************/
// only needed when EKOMI_STORAGE=file
// storage type 'file' is deprecated and will be removed soon
define('EKOMI_CUSTOMERS_FILE',         './orders/customers.txt');        // storage file
define('EKOMI_OLD_CUSTOMERS_FILE',     './orders/customers_old.txt');    // storage file



/***************************
* log settings             *
***************************/

define('EKOMI_LOG_ENABLED',            '1'); // enable logging?
define('EKOMI_LOG_FILE',               './log/log.log'); // log file



/***************************
* e-mail settings          *
***************************/

// sender address. Leave empty to use setting from ekomi customer area
define( 'EKOMI_MAIL_SENDER',             '' );

// smtp-mode. 0=use php's mail() || 1=use smtp
define( 'EKOMI_SMTP_MODE',               '0' );

// smtp-login details. only used when EKOMI_SMTP_MODE = 1
define( 'EKOMI_SMTP_SERVER',             '' );
define( 'EKOMI_SMTP_PORT',               '' );
define( 'EKOMI_SMTP_USER',               '' );
define( 'EKOMI_SMTP_PASS',               '' );


define( 'EKOMI_VERSION',                 '2.0.2' );

?>