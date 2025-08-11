<?php
use Illuminate\Database\Capsule\Manager as Capsule; 
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
global $whmcs;
$activity = $whmcs->get_req_var('activity');
 
   if (isset($activity)) {

        switch (trim($activity)) {

            case 'domainadd':
                    $domain = $whmcs->get_req_var('domain');
                    $description = $params['clientsdetails']['companyname'];
                    $transport = trim($params['configoption9']);
                    $domain_quota_size = $params['configoption1'];
                    $default_quota_of_new_user = trim($params['configoption4']);

                    $domainCount = Capsule::table('mod_ireddomainname')->where('sid',$params["serviceid"])->count();
                   
                    $total_domain_limit = $params['configoption20'];
                   
                    if($domainCount >= $total_domain_limit ){
                        echo  $LANG['max_limit']." ".$total_domain_limit.".";
                        exit();
                    }

                    # For Mail Directory - class.iredmail.settings.php
                    $iredmail->MAILDIR_APPEND_TIMESTAMP = $params["configoption13"];
                    $iredmail->MAILDIR_HASHED = $params["configoption14"];
                    $iredmail->MAILDIR_PREPEND_DOMAIN = $params["configoption15"];


                    if ($params['configoption1'] == 'Business') {
                        
                        $domain_quota_size_value = $iredmail->toMegabyteSize(trim($params['configoption3']), $params['configoption2']);
                    
                    if ($default_quota_of_new_user > $domain_quota_size_value) {

                        echo  "Error: Default Quota size(" . $default_quota_of_new_user . " MB) should be less then 'Account Size' (" . $domain_quota_size_value . " MB)";
                        exit();
                    }
                    
                    $iredmail->DOMAIN_QUOTA_MB = $domain_quota_size_value;
                    $created = date("Y-m-d H:i:s");

                    $settings = 'default_language:en_US;';
                    $max_quota = $params['configoption6'];

                    $default_quota_of_new_user = ($default_quota_of_new_user > $max_quota ) ? $max_quota : $default_quota_of_new_user;

                    $settings .= 'default_user_quota:' . $default_quota_of_new_user . ';';

                 
                    $account_limit = ($params['configoption1'] == 'Business') ? $params['configoptions']['Account Limit'] : $params['configoption5'];
                   
                    $max_alias = $params['configoption7'];
                 
                    $max_mailing = $params['configoption8'];
                   
                    $iredmail->MAX_ACCOUNT_LIMIT = $params['configoption5'];

                    $account_limit = ($account_limit == 0) ? '-1' : $account_limit;
                    $max_alias = ($max_alias == 0) ? '-1' : $max_alias;
                    $max_mailing = ($max_mailing == 0) ? '-1' : $max_mailing;

                    $domain_data = array(
                        "domain" => $domain,
                        "description" => $description,
                        "maxquota" => '',
                        "mailboxes" => '',
                        "aliases" => $max_alias,
                        "maillists" => $max_mailing,
                        "transport" => $transport,
                        "created" => $created,
                        "active" => 1,
                        "settings" => $settings,
                    );


                    if ($iredmail->createdomain($domain_data) == 'error') {
                        echo  "Error: Domain '" . $domain . " ".$LANG['domain_exist'];
                        exit();
                    }else{
                        $domaindataArry = [
                                "domain" => $domain,
                                "sid" => $params["serviceid"],
                                "created_at" => date('Y-m-d H:i:s'),
                                "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        Capsule::table('mod_ireddomainname')->insert($domaindataArry);
                        logModuleCall("iredmail", "CreateAccount", $domain_data, "success");
                        echo  "success";
                    }
                    exit();
                }
                if ($params['configoption1'] == 'Unlimited') {
                    
                    $domain_quota_size_value = $iredmail->toMegabyteSize(trim($params['configoption3']), $params['configoption2']);
                    $iredmail->DOMAIN_QUOTA_MB = $domain_quota_size_value;
                    $created = date("Y-m-d H:i:s");

                    $settings = 'default_language:en_US;';
                    $default_quota_of_new_user = trim($params['configoption4']);
                    $max_quota = $params['configoption6'];
                    $default_quota_of_new_user = ($default_quota_of_new_user > $max_quota ) ? $max_quota : $default_quota_of_new_user;

                    $settings .= 'default_user_quota:' . $default_quota_of_new_user . ';';

                    $account_limit =  $params['configoption5'];
                    $max_alias =   $params['configoption7'];
                    $max_mailing =  $params['configoption8'];
                    $iredmail->MAX_ACCOUNT_LIMIT =  $params['configoption5'];

                    $account_limit = ($account_limit == 0) ? '-1' : $account_limit;
                    $max_alias = ($max_alias == 0) ? '-1' : $max_alias;
                    $max_mailing = ($max_mailing == 0) ? '-1' : $max_mailing;

                    $settings .= 'max_user_quota:' . $max_quota . ';';  

                    $domainCount = Capsule::table('mod_ireddomainname')->where('sid',$params["serviceid"])->count();
                   
                    $total_domain_limit = $params['configoption20'];
                    
                    if($domainCount >= $total_domain_limit ){
                        echo  $LANG['max_limit']." ".$total_domain_limit.".";
                        exit();
                    }

                    $domain_data = array(
                        "domain" => $domain,
                        "description" => $description,
                        "maxquota" => $domain_quota_size_value,
                        "mailboxes" => $account_limit,
                        "aliases" => $max_alias,
                        "maillists" => $max_mailing,
                        "transport" => $transport,
                        "created" => $created,
                        "active" => 1,
                        "settings" => $settings,
                    );
                  
                    if ($iredmail->createdomain($domain_data) == 'error') {
                        echo  "Error: Domain '" . $domain . " ".$LANG['domain_exist'];
                        exit();
                    }else{
                        $domaindataArry = [
                                "domain" => $domain,
                                "sid" => $params["serviceid"],
                                "created_at" => date('Y-m-d H:i:s'),
                                "updated_at" => date('Y-m-d H:i:s'),
                        ];
                        Capsule::table('mod_ireddomainname')->insert($domaindataArry);
                        logModuleCall("iredmail", "CreateAccount Unlimited", $domain_data, "success");
                        echo  "success";
                    }
                }    
                
                exit();
            break;
            case 'domaindelete':

                $domain = $whmcs->get_req_var('domain');//$_POST["domain"];

                $iredmail->delete("domain", array(
                    "domain" => $domain
                ));

                $iredmail->delete("mailbox", array(
                    "domain" => $domain
                ));
                
                if (!empty($iredmail->error)) {
                    logModuleCall("iredmail", "delete domain", $domain, $iredmail->error);
                    echo  $iredmail->error;
                    exit();
                }else{
                    $serviceId = $params["serviceid"];
                    Capsule::table('mod_ireddomainname')->where('sid',$serviceId)->where('domain',$domain)->delete();
                    Capsule::table('mod_iredmailUsers')->where('serviceid',$serviceId)->where('domain',$domain)->delete();
                    echo "success";
                    exit();
                }

            break;

            case 'addemail':
                 
                $isadmin = $params['configoption19'];

                if($isadmin == 'on'){
                    $isadmin = 1;
                }else{
                    $isadmin = 0;
                }    
                $password = $whmcs->get_req_var('password');//$_POST["password"];
                $mail =  $whmcs->get_req_var('mail');//$_POST["mail"];

                $domain = trim($whmcs->get_req_var('domain'));//trim($_POST['domain']);
                if (empty($domain)) {
                    echo $LANG['domain_error'];
                    exit();
                }

                $displayname = $whmcs->get_req_var('displayname');//$_POST["displayname"];
                if(empty($displayname)){
                    $displayname = $mail;
                }
                $password = $iredmail->iredMail_generateSecretHash($params['configoption11'], trim($password));

                if ($params['configoption1'] == 'Business') {
        
                    $quotatype = $_POST["servicetype"];
                    $quotaSize = explode("_", $quotatype)[0];
                    $quotatype = explode("_", $quotatype)[1];

                    // $quotaSize = $iredmail->convertdatatoMB($quotatype);
                    
                    
                    // if($emailAccountArry[$quotatype] > 0){
                    if($quotatype > 0){

                        $dataArr = [
                            'password' => $password,
                            'mail' => $mail,
                            'domain' => $domain,
                            'quota' => $quotaSize,
                            'displayname' => $displayname,
                            'isadmin' => $isadmin,
                        ];

                        $createmail = $iredmail->createmailaccount($dataArr, $SBD);

                        if ($createmail['type'] == 'error') {
                            echo  $createmail['msg'];

                        }
                    
                        if($createmail['type'] == 'success'){
                            $mailaddress = $mail . '@' . $domain;
                            $emaildataArry = [
                                'serviceid' => $params["serviceid"] ,
                                'type' => $quotatype,
                                'domain' => $domain,
                                'mail' => $mailaddress ,

                            ];
                           Capsule::table('mod_iredmailUsers')->insert($emaildataArry); 
                           echo 'success';
                        }
                    }else{
                        echo 'You have reached quota size limit for this service type ('.$quotasize.')' ;
                    }    
                    exit();
                }

                if ($params['configoption1'] == 'Unlimited') {
                    $additional_quotaSize = $params['configoptions']['additional_quota']; 
                    $additional_quota = $iredmail->toMegabyteSize(trim($additional_quotaSize),'GB');

                    $domain_quota_size_value = $iredmail->toMegabyteSize(trim($params['configoption3']), $params['configoption2']);
                    $available_quota = $domain_quota_size_value + $additional_quota;
                    $serviceid = $params["serviceid"];
                    $geteEmaildata = $iredmail->getMailAccountslist($serviceid);
                   
                    $usedQuotaInMB = '';
                    foreach ($geteEmaildata as $key => $val) {
                        $quotaUsed = filter_var($val['quota'], FILTER_SANITIZE_NUMBER_INT); 
                        $unit = preg_replace('/[^a-zA-Z]+/', "", $val['quota']);
                        $usedQuotaInMB += $iredmail->toMegabyteSize(trim($quotaUsed), $unit);
                    
                    }
                   
                    $quotaSize = $_POST["quotasize"];
                    $leftQuota = $available_quota - $usedQuotaInMB;
                    $newquota = $usedQuotaInMB + $quotaSize;
                    if($available_quota < $newquota){
                        echo "Error: Your Domain has been reached Max limit (".$params['configoption3']." ".$params['configoption2']."). your available quota is: ".$leftQuota." MB";
                        exit();
                    }

                    if($quotaSize > $params['configoption6'] ){
                        echo "Your Email Max limit is :".$params['configoption6']." MB";
                        exit();
                    }
                  
                    $dataArr = [
                        'password' => $password,
                        'mail' => $mail,
                        'domain' => $domain,
                        'quota' => $quotaSize,
                        'displayname' => $displayname,
                        'isadmin' => $isadmin,
                    ];

                    $createmail = $iredmail->createmailaccount($dataArr, $SBD);

                    if ($createmail['type'] == 'error') {
                        echo $createmail['msg'];
                    }
                    $quotatype = 'custom';
                
                    if($createmail['type'] == 'success'){
                        $mailaddress = $mail . '@' . $domain;
                        $emaildataArry = [
                            'serviceid' => $params["serviceid"] ,
                            'type' => $quotatype,
                            'domain' => $domain,
                            'mail' => $mailaddress ,

                        ];
                       Capsule::table('mod_iredmailUsers')->insert($emaildataArry); 
                       echo 'success';
                    }
                }
                 
            exit();
            break;
            case 'getemail':
                $serviceid = $params["serviceid"];
                $data = $iredmail->getMailAccountslist($serviceid);
                echo json_encode($data);
            exit();
            break;    
            case 'get_domain_list':
                $serviceid = $params["serviceid"];
                #get default domain
                $getDomains = Capsule::table('mod_ireddomainname')->where('sid',$params['serviceid'])->get();
                $domainData = [];
                foreach ($getDomains as $key => $value) {
                    $domain_name = $value->domain;
                    $domainData[$value->domain]['emailcount'] = Capsule::table('mod_iredmailUsers')->where('domain', $value->domain)->where('serviceid', $params['serviceid'])->count();
                    $domainData[$value->domain]['domainalias'] = $iredmail->getAliasDomain($domain_name);
                }
               
                echo json_encode($domainData);
            exit();
            break; 
            case 'del_alias_domain':
               
                $targetDomain = $whmcs->get_req_var('targetDomain'); 
                $domain = $whmcs->get_req_var('domain'); 
                $resp = $iredmail->deleteDomainAlias($domain);
                if($resp == 'success'){
                    $status = 'success';
                }else{
                     $status = $resp;
                }
               
                echo $status;
            exit();
            break; 
            case 'getforwardingmail':
                $mailaddress = $whmcs->get_req_var('mail'); 
                $domain = $whmcs->get_req_var('domain'); 
                $data = $iredmail->getForwordingemail($mailaddress,$domain) ;
                echo json_encode($data);
            exit();
            break; 
            case 'set_forwarding':

                $mailaddress = $whmcs->get_req_var('mailaddress'); 
                $domain_name = $whmcs->get_req_var('domain_name'); 
                $mailforwarding = $whmcs->get_req_var('mailforwarding'); 
              
                $forwadingMail = trim($mailforwarding);
                $forwardingArr = explode(",", $forwadingMail);
              
                $iredmail->deleteForwardingemail($mailaddress);
                
                foreach ($forwardingArr as $key => $value) {
        
                        $forwardmailarr = [
                            "address"=> $mailaddress,
                            "forwarding"=> $value,
                            "domain"=> $domain_name,
                            "dest_domain"=> $domain_name,
                            "is_maillist"=>"0",
                            "is_list"=> "0",
                            "is_forwarding"=> "1",
                            "is_alias"=> "0",
                            "active"=> "1",
                        ];
                    $response = $iredmail->setForwardingemail($forwardmailarr); 

                    if ($response != 'success') {
                        $status = $response;
                    } else {
                        $status = "success";
                    }
                }
                echo  $status;
                exit();
            break;
             case 'set_alias_domain':
                $domain_name = $whmcs->get_req_var('domain_alias_dname'); 
                $targetdomain = $whmcs->get_req_var('targetdomain'); 
                $targetDomainName = trim($targetdomain);
                $targetDomainNameArr = explode(",", $targetDomainName);
              
                $resp = $iredmail->deleteDomainAlias($domain_name);
                foreach ($targetDomainNameArr as $key => $val) {
                 
                    $pattern = '/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]/i';
                  $validDname = preg_match($pattern, $val);
                  
                   if($validDname){
                        $data = [
                            "alias_domain"=> $domain_name,
                            "target_domain"=> $targetdomain,
                            "active"=> "1",
                        ];
                        $response = $iredmail->setAliasDomain($data); 
                        if ($response != 'success') {
                            $status = $response;
                        } else {
                            $status = "success";
                        }
                   } 
                }
              
                echo  $status;
                exit();
            break;  
            case 'get_alias_domain':
                $domain_name = $whmcs->get_req_var('domain'); 
               
                $resp = $iredmail->getAliasDomain($domain_name);
                echo json_encode($resp);
                exit();
            break;  
            case 'get_mail_alias':
                $mailAddress = $whmcs->get_req_var('mailaddress'); 
                $data = $iredmail->getMailAlias($mailAddress);
                
                echo $data;
                exit();
            break;  
            case 'get_domain_catch':
                $domain = $whmcs->get_req_var('domain'); 
 
                $dataCatch = $iredmail->getMailAccountslistCatch($domain);
                if (empty($dataCatch)) {
                    echo "none";
                }else{
                    echo json_encode($dataCatch);
                }
              
                exit();
            break; 
            case 'add_domain_catch':
                $domain = $whmcs->get_req_var('catch_domain_selected'); 
                $catchallmail = $whmcs->get_req_var('catchallmail'); 
                 
                if($catchallmail == 'none'){
                    $status = $iredmail->domainCatchDelete($domain);
                }else{
                    $data = [
                            "address"=> $domain,
                            "forwarding"=> $catchallmail,
                            "domain"=> $domain,
                            "dest_domain"=> $domain,
                        ];
                    $status = $iredmail->doaminCatchAdd($data,$domain) ;
                }
                    
                
                echo $status;
                exit();
            break; 
            case 'set_alias_mail':
                $domain_name = $whmcs->get_req_var('mail_alias_dname'); 
                $mail_name = $whmcs->get_req_var('mailaddress_alias'); 
                $targetmail = $whmcs->get_req_var('mailalias'); 
                $enableAlias = $whmcs->get_req_var('enable'); 
                if($enableAlias == 'on')
                    $isActive = 1;
                else
                    $isActive = 0;

                $targetMailNameArr = explode(",", preg_replace('/\s+/', '', $targetmail));
                $aliasMailing = '';
                foreach ($targetMailNameArr as $key => $val) {
                   if(filter_var($val, FILTER_VALIDATE_EMAIL)) {
                       $aliasMailing .= $val.',';
                    } 
                }
                $aliasMailing = rtrim($aliasMailing, ',');
                $data = [
                            "address"=> $aliasMailing,
                            "forwarding"=> $mail_name,
                            "domain"=> $domain_name,
                            "dest_domain"=> $domain_name,
                            "is_list"=> 0,
                            "active"=> $isActive,
                        ];
                         
                        $response = $iredmail->insertMailAlias($data,$mail_name); 
                        if ($response != 'success') {
                            $status = $response;
                        } else {
                            $status = "success";
                        } 
               
                echo  $status;
                exit();
            break;   
            case 'enable_disablePop':
                $status = $whmcs->get_req_var('pop_status');//$_POST['email_status'];
                $mailuser = $whmcs->get_req_var('email_user');//$_POST['email_user'];
               
                $data = $iredmail->setAccountStatusPop($status,$mailuser);
                 if (!empty($iredmail->error)) {
                    echo  $iredmail->error;
                    exit();
                } else {
                    echo "success";
                }
              
            exit();
            break;  

            case 'enable_disable_imap':
               
                $status = $whmcs->get_req_var('imap_status');//$_POST['email_status'];
                $mailuser = $whmcs->get_req_var('email_user');//$_POST['email_user'];
                $data = $iredmail->setAccountStatusImap($status,$mailuser);
                 if (!empty($iredmail->error)) {
                    echo  $iredmail->error;
                    exit();
                } else {
                    echo "success";
                }
               
            exit();
            break;   

            case 'accountenable_disable':
                
                $status = $whmcs->get_req_var('email_status');//$_POST['email_status'];
                $mailuser = $whmcs->get_req_var('email_user');//$_POST['email_user'];
                $data = $iredmail->setAccountStatus($status,$mailuser);
                 if (!empty($iredmail->error)) {
                    echo  $iredmail->error;
                    exit();
                } else {
                    echo "success";
                }
                 
                exit();
            break;    
            case 'changepassword':
                
                $password = $whmcs->get_req_var('password');//$_POST["password"];
                $mailaccount = $whmcs->get_req_var('mailaddress');//$_POST["mailaddress"];
 
                $password = $iredmail->iredMail_generateSecretHash($params['configoption11'], trim($password));

                $iredmail->changepassword($password, $mailaccount);
                if (!empty($iredmail->error)) {
                   echo $iredmail->error;
                   exit();
                } else {
                    echo "success";
                }
                 
                exit();
            break;  
            case 'maildelete':
                $mailaddress = $whmcs->get_req_var('mail');//$_POST["mail"];
                $domain = $whmcs->get_req_var('domain');//$_POST["domain"];
                $where = array("domain" => $domain, 'username' => $mailaddress);
                
                $iredmail->delete("mailbox", $where);
                if (!empty($iredmail->error)) {
                    logModuleCall("iredmail", "delete mail account", $mailaddress, $iredmail->error);
                }else{
                   Capsule::table('mod_iredmailUsers')->where('serviceid',$params["serviceid"])->where('mail',$mailaddress)->where('domain',$domain)->delete(); 
                }
                echo 'success';
               
            exit();
            break; 
            case 'overridequotasize':
                 
                $invoiceId = $whmcs->get_req_var('invoiceid');//$_POST['invoiceid'];
                $mailAddress = $whmcs->get_req_var('mailaddress');//$_POST['mailaddress'];
                $domainName = $whmcs->get_req_var('domainname');//$_POST['domainname'];
                $quotaChange = $whmcs->get_req_var('quotachange');//$_POST['quotachange'];

                $unitPrice = $upgradePriceArray['price'];
                $quotaPrice = $unitPrice * $quotaChange;

                #get invoice item id
                
                $getInvoiceitem = Capsule::table('tblinvoiceitems')->where('invoiceid',$invoiceId)->first();
                $invoiceItemId = $getInvoiceitem->id;// die;
                $invoiceItemId = 33;
                #update invoice
                $command = 'UpdateInvoice';
                $postData = array(
                    'invoiceid' => $invoiceId,
                    'status' => 'Unpaid',
                    'itemdescription' => array($invoiceItemId  => 'Upgrade quota size '. $quotaChange.' GB of mail ( '.$mailAddress.' )'),
                    'itemamount' => array($invoiceItemId => $quotaPrice),
                    'itemtaxed' => array($invoiceItemId => false),
                );
                $adminUsername = ''; // Optional for WHMCS 7.2 and later
               
                $results = localAPI($command, $postData, $adminUsername);
                if($results['result'] == 'success'){
                    $InvoiceId = $results['invoiceid'];
                    $response = [
                        'code' => 'success',
                        'msg' => ''.$LANG['UpgradeSent'].'  <a href="viewinvoice.php?id='.$InvoiceId.'" target="_blank">  ( #'.$InvoiceId.' )</a>'
                    ];

                    $data = [
                        'quota' => $quotaChange, //in Gb
                    ];

                    Capsule::table('mod_iredupgradequota')->where('invoiceid',$invoiceId)->update($data);    
                }else{
                   
                    $response = [
                        'code' => 'error',
                        'msg' => $results['error']
                    ];
                }
                echo json_encode($response);

            exit();
            break;
            case 'upgradequotasize':
          
                $mailaddress = $whmcs->get_req_var('mailaddress');//$_POST["mailaddress"];
                $domain = $whmcs->get_req_var('domainname');//$_POST["domainname"];
                $change_quota = $whmcs->get_req_var('quotachange');//$_POST["quotachange"];
                $unitPrice = $upgradePriceArray['price'];
                $quotaPrice = $unitPrice * $change_quota;
                $currentDate = date("Y-m-d");
                $time = strtotime($currentDate);
                $dueDate = date("Y-m-d", strtotime("+1 month", $time));//die;

                $count = Capsule::table('mod_iredupgradequota')->where('domain',$domain)->where('mail', $mailaddress)->where('status', 'Unpaid')->count();

                if($count == 0){
                    #create invoice
                    $command = 'CreateInvoice';
                    $postData = array(
                        'userid' => $clientId,
                        'status' => 'Unpaid',
                        'sendinvoice' => '1',
                        'paymentmethod' => 'banktransfer',
                        'taxrate' => '0',
                        'date' => $currentDate,
                        'duedate' =>  $dueDate,
                        'itemdescription1' => 'Upgrade quota size '. $change_quota.' GB of mail ( '.$mailaddress.' )' ,
                        'itemamount1' => $quotaPrice,
                        'itemtaxed1' => '0',
                        'autoapplycredit' => '0',
                    );
 
                    $adminUsername = ''; // Optional for WHMCS 7.2 and later

                    $results = localAPI($command, $postData, $adminUsername);
                    
                    if($results['result'] == 'success'){
                        $InvoiceId = $results['invoiceid'];
                        $status = $results['status'];
                        $data = [
                            'serviceid' => $serviceId,
                            'invoiceid' => $InvoiceId,
                            'mail' => $mailaddress,
                            'domain' => $domain,
                            'quota' => $change_quota, //in Gb
                            'mailtype' => $mailType,  
                            'status' => $status
                        ];

                        Capsule::table('mod_iredupgradequota')->insert($data);
                        
                        $response = [
                            'code' => 'success',
                            'msg' => ''.$LANG['UpgradeSent'].'  <a href="viewinvoice.php?id='.$InvoiceId.'" target="_blank">  ( #'.$InvoiceId.' )</a>'
                        ];
                    }else{
                       
                        $response = [
                            'code' => 'error',
                            'msg' => $results['error']
                        ];
                    }
                }else{
                    $getUpgradeData = Capsule::table('mod_iredupgradequota')->where('mail', $mailaddress)->where('status','Unpaid')->first();
                    $quotaSize = $getUpgradeData->quota;
                    $invoiceid = $getUpgradeData->invoiceid;
                     
                    if($quotaSize == $change_quota){
                        $msg_quota =  str_replace("(%qSize%)",$quotaSize,$LANG['UpgradeProgess']).' <br><br><a  href="viewinvoice.php?id='.$invoiceid.'" target="_blank" style="color: #fff;background-color: #264d73;border-color: #234669;font-size: 14px;padding: 4px 10px;line-height: 1.5;border-radius: .25rem;">'.$LANG['Upgradechangequota'].'</a>';
                    }else{
                        $msg_quota =  str_replace("(%qSize%)",$quotaSize,$LANG['UpgradeProgess']).' <br><a  href="viewinvoice.php?id='.$invoiceid.'" target="_blank" style="color: #fff;background-color: #264d73;border-color: #234669;font-size: 14px;padding: 4px 10px;line-height: 1.5;border-radius: .25rem;">'.$LANG['Upgradechangequota'].'</a> Or <button  onclick="overidequota(this,'.$invoiceid.');return false;" type="button" class="btn btn-success" style="font-size: 14px;padding: 4px;">Override quota ('.$change_quota.'GB)';
                    }
                    $response = [
                        'code' => 'error',
                        'msg' => $msg_quota
                    ];
                 
                }
                echo json_encode($response);
                
                exit();
            break;  
        }
    }

?>