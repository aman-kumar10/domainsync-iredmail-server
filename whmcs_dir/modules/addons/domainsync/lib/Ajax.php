<?php

require_once __DIR__ . '/../../../../init.php';

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\DomainSync\Helper;

header('Content-Type: application/json');

global $whmcs;

// Check domain
if ($whmcs->get_req_var('data_action') === 'checkAvlDomain') {
    
   $helper = new Helper;

   $domain = $whmcs->get_req_var('domain_value');
   $availability = $helper->checkAvlDomain($domain);
   
   echo json_encode([
       'success' => $availability['status'],
       'message' => $availability['message']
   ]);
   exit;

}
