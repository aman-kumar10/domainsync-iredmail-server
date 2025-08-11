<?php

use Illuminate\Database\Capsule\Manager as Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/URLify.php';


#new function
add_hook('AdminProductConfigFieldsSave', 1, function($vars) {
 
    $pid = $vars['pid'];
    
    $count = Capsule::table('tblproducts')->where('id', $pid)->where('servertype','iredmail')->count();
    if($count != 0){
      
        if(count( (array) $_REQUEST['customprice']) !== 0){
            foreach ($_REQUEST['customprice'] as $key => $val) {

                if(Capsule::table('mod_iredmailcurrencies_price')->where('pid',$pid)->where('code',$key)->count() == 0){
                    $price_Arry = [
                        'pid' => $pid,
                        'code' => $key,
                        'price' => $val,
                    ];
                    Capsule::table('mod_iredmailcurrencies_price')->insert($price_Arry);
                }else{
                    $price_Arry = [
                        'code' => $key,
                        'price' => $val,
                    ];
                    Capsule::table('mod_iredmailcurrencies_price')->where('pid',$pid)->update($price_Arry);
                }
            }
        }
       
    }
});


# quota upgrade ired only for bussiness mail
add_hook('InvoicePaid', 1, function($vars) {
       
    $invoiceId = $vars['invoiceid'];
    $count = Capsule::table('mod_iredupgradequota')->where('invoiceid', $invoiceId)->where('status','Unpaid')->count();
    
    if($count != 0){

        if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
            require_once __DIR__ . '/class.iredmail.api.php';
            require_once __DIR__ . '/URLify.php';
        }
        $getData = Capsule::table('mod_iredupgradequota')->where('invoiceid', $invoiceId)->first();
        $serviceID = $getData->serviceid;  
        $quota = $getData->quota;
        $mail = $getData->mail;
        $domain = $getData->domain;
        $mailType = $getData->mailtype;
       
        $getUserId = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
        $userId = $getUserId->userid;
       
        $invoiceId = $vars['invoiceid'];
        $command = 'GetClientsProducts';
        $postData = array(
            'clientid' => $userId,
            'serviceid' => $serviceID,
        );
        $adminUsername = ''; // Optional for WHMCS 7.2 and later

        $results = localAPI($command, $postData, $adminUsername);
         
            $serverId = $results['products']['product'][0]['serverid'];
            
            $getServerDetail = Capsule::table('tblservers')->where('id',$serverId)->where('type', 'iredmail')->first();
            $serverName = $getServerDetail->ipaddress;
            $dbUserName = $getServerDetail->username;
            $encryptedDbPassword = $getServerDetail->password;
            $dbName = $getServerDetail->accesshash;
            $dbPort = $getServerDetail->port;

            $command = 'DecryptPassword';
            $postData = array(
                'password2' => $encryptedDbPassword ,
            );
            $adminUsername = ''; // Optional for WHMCS 7.2 and later

            $results = localAPI($command, $postData, $adminUsername);
            $dbPassword = $results['password'];

        #quota GB to MB convert
        $quotaInMB = 1024 *  $quota;
        $iredmail = new Iredmail($serverName, $dbUserName,  $dbPassword , $dbPort, "domain", $dbName, $error = NULL);    
        $result =  $iredmail->select("mailbox", "quota", array(
            "username" => $mail,
            "domain" => $domain
        ));
        $data = mysqli_fetch_assoc($result);
        $existingQuota = $data['quota'];  
         
        $quotaUpgrade =  $quotaInMB +  $existingQuota;

        $iredmail->update("mailbox", array(
            "quota" => $quotaUpgrade
                ), array(
            "username" => $mail,
            "domain" => $domain
        ));
        $updateData = [
                        'status' => 'Paid'
                    ];
        Capsule::table('mod_iredupgradequota')->where('invoiceid', $invoiceId)->update($updateData);
    }
     
});


add_hook('ClientAreaHeadOutput', 1, function($vars) {
    //($buttonOrText -> any php variable array or string used in javascript)

    $pid = $vars['pid'];

    $domain_admin_email = Capsule::table('tblcustomfields')->where('relid', $pid)
            ->where('fieldname', '=', 'name_for_domain_admin_email|name for domain administrator amail address')
            ->first();
    $id = $domain_admin_email->id;

    $name = '';
    if (empty($vars['clientsdetails']['firstname']) && !empty($vars['clientsdetails']['lastname'])) {
        $name = URLify::filter($vars['clientsdetails']['lastname']);
    }
    if (empty($vars['clientsdetails']['lastname']) && !empty($vars['clientsdetails']['firstname'])) {
        $name = URLify::filter($vars['clientsdetails']['firstname']);
    }
    if (!empty($vars['clientsdetails']['lastname']) && !empty($vars['clientsdetails']['firstname'])) {
        $name = URLify::filter($vars['clientsdetails']['firstname']) . '.' . URLify::filter($vars['clientsdetails']['lastname']);
    }

    $name = strtolower($name);

    $script = <<< javascript
<script>
jQuery(document).ready(function(){
    var Name = "$name";
   
    var customfie_id  = "#customfield"+$id;
    $(customfie_id).val(Name);
});
</script>
javascript;

    return $script;
});
