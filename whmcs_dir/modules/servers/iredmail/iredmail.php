<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/* * ****************************************************************
 *  WGS Ired Mail WHMCS Provisioning Module By whmcsglobalservices.com
 *  Copyright whmcsglobalservices, All Rights Reserved
 *
 *  WHMCS Version: v6,v7,v8
 *  Version: 1.0.8
 *  Update Date: 10 May, 2024
 *
 *  By WHMCSGLOBALSERVICES    https://whmcsglobalservices.com
 *  Contact                   info@whmcsglobalservices.com
 *
 *  This module is made under license issued by whmcsglobalservices.com
 *  and used under all terms and conditions of license.    Ownership of
 *  module can not be changed.     Title and copy of    module  is  not
 *  available to any other person.
 *
 *  @owner <whmcsglobalservices.com>
 *  @author <WHMCSGLOBALSERVICES>
 * ********************************************************** */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
// die('dff');
function iredmail_MetaData()
{
    return array(
        'DisplayName' => 'iRed Mail',
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
    );
}

function iredmail_ConfigOptions($var)
{
    $getAllCurrencies = Capsule::table('tblcurrencies')->get();
    $custom_price = '';
    $count = 1;
    $c_price = '';

    if (!Capsule::Schema()->hasTable('mod_iredmailUsers')) {
        try {
            Capsule::schema()->create(
                    'mod_iredmailUsers', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('serviceid');
                $table->string('type');
               // $table->integer('primary');
                $table->string('domain');
                $table->string('mail');
                $table->timestamp('created_at')->default(Capsule::raw("CURRENT_TIMESTAMP"));
                $table->timestamp('updated_at')->default(Capsule::raw("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"));
            }
            );
        } catch (\Exception $e) {
            echo "Unable to create my_table: {$e->getMessage()}";
        }
    }

    if (!Capsule::Schema()->hasTable('mod_iredupgradequota')) {
        try {
            Capsule::schema()->create(
                    'mod_iredupgradequota', function ($table) {
                $table->increments('id');
                $table->integer('serviceid');
                $table->string('invoiceid');
                $table->string('mail');
                $table->string('domain');
                $table->string('quota');
                $table->string('mailtype');
                $table->string('status')->nullable();
            }
            );
        } catch (\Exception $e) {
            echo "Unable to create my_table: {$e->getMessage()}";
        }
    }


    if (!Capsule::Schema()->hasTable('mod_ireddomainname'))
    {
        try {
            Capsule::schema()->create('mod_ireddomainname', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('sid');
                $table->string('domain');
                $table->string('domain_type');
                $table->string('domain_quota');
                $table->string('domain_used_quota');
                $table->string('domain_account_limit');
                $table->string('domain_email_limit');
                $table->timestamps();
            });
        } catch (\Exception $e) {
            echo "Unable to create my_table: {$e->getMessage()}";
        }
    }

    if (!Capsule::Schema()->hasTable('mod_iredmailcurrencies_price')) {
        try {
            Capsule::schema()->create(
                    'mod_iredmailcurrencies_price', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->string('pid');
                $table->string('code');
                $table->string('price')->nullable();
                $table->timestamps();
            }
            );
        } catch (\Exception $e) {
            echo "Unable to create my_table: {$e->getMessage()}";
        }
    }

    $productid = (int) $_REQUEST["id"];
    $product = Capsule::table('tblproducts')->find($productid);

    foreach ($getAllCurrencies as $key => $val) {
        if (Capsule::table('mod_iredmailcurrencies_price')->where('pid', $productid)->where('code', $val->code)->count() != 0) {
            $c_price = Capsule::table('mod_iredmailcurrencies_price')->where('pid', $productid)->where('code', $val->code)->first();
        }

        if ($count % 2 != 0) {
            $custom_price .= '<tr><td class="fieldlabel" width="20%">Upgrade Quota price Per GB</td><td class="fieldarea"><input type="number"  min="0" name="customprice[' . $val->code . ']" class="form-control input-inline input-300" value="' . $c_price->price . '"> Price in ' . $val->code;
        } else {
            $custom_price .= ' </td><td class="fieldlabel" width="20%">Upgrade Quota price Per GB</td><td class="fieldarea"><input type="number"  min="0" name="customprice[' . $val->code . ']" class="form-control input-inline input-300"value="' . $c_price->price . '"> Price in ' . $val->code . ' <div id="currency_price"></div></td></tr>';
        }

        $count++;
    }

    // if (empty($product->configoption1)) {
    //     $selectedPlan = "Business,Unlimited";
    // } else {
    //     $selectedPlan = $product->configoption1;
    // }

    if ($_REQUEST['action'] == 'save') {
        $planType = "Unlimited";

        if ($_REQUEST['packageconfigoption'][1] == 'Business') {
            $planType = "Business";
        }

        if (count( (array) $_REQUEST['customfieldname']) == 0) {
            createEmailTemplate();
           iredmail_createCustomfields($productid, $planType);
            create_configurableOption($productid, $planType);
        }
    }

    $configarray = array(
        "Quota Access" => array(
            "FriendlyName" => "Manage Quota by<font color='#FF0000;'>*</font>",
            "Type" => "dropdown",
            "Options" => "Business,Unlimited",
            "Description" => "",
            "Default" => "Business"
        ),
        "customquotastorage" => array(
            "FriendlyName" => "Domain Quota Size",
            "Type" => "dropdown",
            "Options" => "MB,GB,TB",
            "Description" => "",
            "Default" => "MB"
        ),
        "customquotavalue" => array(
            "FriendlyName" => "Account Size<font color='#FF0000;'>*</font>",
            "Type" => "text",
            "Size" => "20",
            "Default" => "0",
            "Description" => "Domain quota size"
        ),
        "defaultquotaofnewuser" => array(
            "FriendlyName" => "Default Quota of new User<font color='#FF0000;'>*</font>",
            "Type" => "text",
            "Size" => "20",
            "Default" => "0",
            "Description" => "MB",
        ),
        "Users" => array(
            "FriendlyName" => "Account Limit",
            "Type" => "text",
            "Size" => "20",
            "Description" => "",
            "Default" => "10",
            "Description" => "( '0' means unlimited)",
        ),
        "max_quota_of_mail_user" => array(
            "FriendlyName" => "Max Quota  of  Mail User",
            "Type" => "text",
            "Size" => "20",
            "Default" => "0",
            "Description" => "MB",
        ),
        "max_alias_of_mail_user" => array(
            "FriendlyName" => "Max Alias  of Mail User",
            "Type" => "text",
            "Size" => "20",
            "Default" => "0",
            "Description" => "",
        ),
        "max_mailing_list" => array(
            "FriendlyName" => "Max Mailing List",
            "Type" => "text",
            "Size" => "20",
            "Default" => "0",
            "Description" => "<tr style='background-color:#FAF6D4;color:#545353;'><td colspan='100%'><span style='font-weight:bold;'>Settings for mail Accounts</span><br><small><font color='red' size='2px'>( Attention!! Below settings must be same as iRed Mail Server. Otherwise module will not work properly )</font></small></td></tr>",
        ),
        "defaultmtatransport" => array(
            "FriendlyName" => "MTA Transport<font color='#FF0000;'>*</font>",
            "Type" => "text",
            "Size" => "20",
            "Default" => "",
            "Description" => "Default MTA transport"
        ),
        "storagebasedirectory" => array(
            "FriendlyName" => "SBD<font color='#FF0000;'>*</font>",
            "Type" => "text",
            "Size" => "20",
            "Default" => "",
            "Description" => "Storage Base Directory"
        ),
        "defaultpasswordscheme" => array(
            "FriendlyName" => "Password Scheme",
            "Type" => "dropdown",
            "Options" => "MD5,SSHA512",
            "Default" => "SSHA512",
            "Description" => "Default Password Scheme",
        ),
        "passwordprefixscheme" => array(
            "FriendlyName" => "Password Prefix",
            "Type" => "dropdown",
            "Options" => "True,False",
            "Default" => "True",
            "Description" => ""
        ),
        "appendtimestamp" => array(
            "FriendlyName" => "Append Timestamp",
            "Type" => "dropdown",
            "Options" => "True,False",
            "Default" => "True",
            "Description" => "Append timestamp to Mail Directory"
        ),
        "hashed" => array(
            "FriendlyName" => "Hashed",
            "Type" => "dropdown",
            "Options" => "True,False",
            "Default" => "True",
            "Description" => "Hashed Mail Directory"
        ),
        "prependdomain" => array(
            "FriendlyName" => "Prepend Domain",
            "Type" => "dropdown",
            "Options" => "True,False",
            "Default" => "True",
            "Description" => "Prepend domain to Mail Directory"
        ),
        "webmailurl" => array(
            "FriendlyName" => "Web Mail URL<font color='#FF0000;'>*</font>",
            "Type" => "text",
            "Size" => "20",
            "Deafult" => "",
            "Description" => "For e.g. http(s)://example.com<script>$( document ).ready(function(){
            $('input, select').each(function( ){
                if($(this).attr('name') == 'packageconfigoption[3]'){
                    $(this).attr('id', 'custom_quota');
                }
                if($(this).attr('name') == 'packageconfigoption[2]'){
                    $(this).attr('id', 'quota_storage');
                }
              
                if($(this).attr('name') == 'packageconfigoption[4]'){
                    $(this).attr('id', 'default_quota');
                }
                if($(this).attr('name') == 'packageconfigoption[5]'){
                    $(this).attr('id', 'admin_user');
                }
                if($(this).attr('name') == 'packageconfigoption[1]'){
                    $(this).attr('id', 'manage_quota');
                }
            });

            if($('#quota_size').val() == 'Unlimited'){
                $('#custom_quota').prop('disabled', true);
            }else{
                $('#custom_quota').prop('disabled', false);
            }
            $('select').change(function(){if($(this).val() == 'Unlimited'){
                $('#custom_quota').prop('disabled', true);
                $('#custom_quota').val('');
               
            }if($(this).val() == 'Custom'){
                $('#custom_quota').prop('disabled', false);
            }});

            var mb = $('#quota_storage').val();

            if($('#manage_quota').val() == 'Business'){
                    $('#custom_quota').prop('disabled', true);
                    $('#quota_size').prop('disabled', true);
                    $('#default_quota').prop('disabled', false);
                    $('#admin_user').prop('disabled', true);
                    $('input[name=\'packageconfigoption[4]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[6]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[7]\']').prop('disabled', false);
            }
           

            $('select').change(function(){

                if($(this).val() == 'Business'){

                    $('input[name=\'packageconfigoption[3]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[4]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[5]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[6]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[7]\']').prop('disabled', true);
                    $('input[name=\'packageconfigoption[8]\']').prop('disabled', true);

                    $(document).ready(function(){
                  
                    });
                }if($(this).val() == 'Unlimited'){
                    $('input[name=\'packageconfigoption[3]\']').prop('disabled', false);
                    $('input[name=\'packageconfigoption[4]\']').prop('disabled', false);
                    $('input[name=\'packageconfigoption[5]\']').prop('disabled', false);
                    $('input[name=\'packageconfigoption[6]\']').prop('disabled', false);
                    $('input[name=\'packageconfigoption[7]\']').prop('disabled', false);
                    $('input[name=\'packageconfigoption[8]\']').prop('disabled', false);

                }
            });

             " . $defaultvalue . "
            });

            </script><tr style='background-color:#FAF6D4;color:#545353;'><td colspan='100%'><span style='font-weight:bold;'>Module Licensekey</small></td></tr>"
        ), 
        "licensekey" => array(
            "FriendlyName" => "Module Licensekey",
            "Type" => "text",
            "Size" => "30",
            "Deafult" => "",
            "Description" => ""
        ),
        "web_admin_url" => array(
            "FriendlyName" => "Web Admin Url",
            "Type" => "text",
            "Size" => "30",
            "Deafult" => "",
            "Description" => ""
        ),
        "enable_admin" => array(
            "FriendlyName" => "Enable Admin",
            "Type" => "yesno", 
            "Size" => "25",
            "Description" => "Enable web admin for users<tr style='background-color:#FAF6D4;color:#545353;'><td colspan='100%'><span style='font-weight:bold;'>Domain settings</small></td></tr>",
        ),
        "total_domain_limit" => array(
            "FriendlyName" => "Number of domains",
            "Type" => "text", 
            "Size" => "30",
            "Description" => "Number of domains per domain",
        ),
         "domain_per_hour" => array(
            "FriendlyName" => "Per domain per hour out going",
            "Type" => "text", 
            "Size" => "30",
            "Description" => " ",
        ),
         "email_per_hour" => array(
            "FriendlyName" => "Per email per hour out going",
            "Type" => "text", 
            "Size" => "30",
            "Description" => "<div id='currency_price'><tr style='background-color:#FAF6D4;color:#545353;'><td colspan='100%'><span style='font-weight:bold;'>Upgrade Price settings</small></td></tr>".$custom_price."</div> ",
        ),
        
    );

    return $configarray;
}

function iredmail_CreateAccount($params)
{

    $licenceDetails = iredmail_license_checkLicense($params['configoption17']);
    if ($licenceDetails['status'] != "Active") {
        return "Invaild Licence Key, please enter vaild licence key";
    }
    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
        require_once __DIR__ . '/URLify.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file';
    }

    if (file_exists(__DIR__ . '/URLify.php')) {
        require_once __DIR__ . '/URLify.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "cURLify.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "URLify.php" is required, Please make sure you have uploaded iredmail URLify file';
    }
    //$isadmin = $params['configoption19'];
    
    if ($params['configoption1'] == 'Unlimited') {
        $hostname = $params['customfields']['ip_address'];
        $username = $params['customfields']['username'];
        $db_password = $params['customfields']['password'];
        $db_name = $params['customfields']['db_name'];
        $port = $params['customfields']['port'];
        if (empty($hostname) || empty($username) || empty($db_password) || empty($db_name)|| empty($port)) {
            return 'Error: (Empty Fields) You can not leave required fields empty. Please fill carefully custom field (database) all the values.';
        }
        if (empty($params['configoption4'])) {
            return 'Error: (Default Quota of new User) You can not leave required fields empty. Please fill carefully all the values.';
        }
        if (empty($params['configoption3'])) {
            return 'Error: (Account size of domain) You can not leave required fields empty. Please fill carefully all the values.';
        } 
        if (empty($params['configoption6'])) {
            return 'Error: (Max Quota of Mail User) You can not leave required fields empty. Please fill carefully all the values.';
        }
        if (empty($params['domain'])) {
            return 'Error: (Domain) You can not leave required fields empty. Please enter domain name.';
        }
    
        $iredmail = new Iredmail($hostname, $username, $db_password, $port, "domain", $db_name, $error = NULL);
    } else {
        if (empty($params['configoption9']) || empty($params['configoption10'])) {
            return 'Error: (Empty Fields) You can not leave required fields empty. Please fill carefully all the values.';
        }

        // if (empty($params['configoption4'])) {
        //     return 'Error: (Default Quota of new User) You can not leave required fields empty. Please fill carefully all the values.';
        // }

        $iredmail = new Iredmail($params['serverhostname'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "domain", $params['serveraccesshash'], $error = NULL);
    }

    if (!empty($iredmail->error)) {
        return $iredmail->error;
    }
    # Connection With Class * Ends


    $packageid = $params["packageid"];
    $usedaccounts = Capsule::table('mod_iredmailUsers')->where('serviceid', $params["serviceid"])->count();
    //$dname = Capsule::table('mod_ireddomainname')->where('sid', $params['packageid'])->get();

    $domain = empty(trim($params['domain'])) ? $params['customfields']['Domain'] : trim($params['domain']);
	
	if(!$domain){
    	return "Domain can not be null";
    }

    // $data = Capsule::select("SELECT `id` FROM `tbladmins` LIMIT 1");
    $command = "updateclientproduct";
    // $adminuser = $data[0]->id;
    $values["serviceid"] = $params["serviceid"];
    $values["domain"] = $domain;
    localAPI($command, $values);

    $description = $params['clientsdetails']['companyname'];
    $transport = trim($params['configoption9']);
    $domain_quota_size = $params['configoption1'];
    $default_quota_of_new_user = trim($params['configoption4']);


    # For Mail Directory - class.iredmail.settings.php
    $iredmail->MAILDIR_APPEND_TIMESTAMP = $params["configoption13"];
    $iredmail->MAILDIR_HASHED = $params["configoption14"];
    $iredmail->MAILDIR_PREPEND_DOMAIN = $params["configoption15"];


    if ($params['configoption1'] == 'Business')
    {
        $domain_quota_size_value = $iredmail->toMegabyteSize(trim($params['configoption3']), $params['configoption2']);

        if ($default_quota_of_new_user > $domain_quota_size_value) {
            return "Error: Default Quota size(" . $default_quota_of_new_user . " MB) should be less then 'Account Size' (" . $domain_quota_size_value . " MB)";
        }

        $iredmail->DOMAIN_QUOTA_MB = $domain_quota_size_value;
        $created = date("Y-m-d H:i:s");

        $settings = 'default_language:en_US;';
        $max_quota = $params['configoption6'];

        $default_quota_of_new_user = ($default_quota_of_new_user > $max_quota ) ? $max_quota : $default_quota_of_new_user;

        $settings .= 'default_user_quota:' . $default_quota_of_new_user . ';';

        $account_limit = ($params['configoption1'] == 'Business') ? $params['configoptions']['Account Limit'] : $params['configoption5'];
        // $max_alias = ($params['configoption1'] == 'Business') ? $params['configoptions']['max_alias_of_mail_user'] : $params['configoption7'];
        $max_alias = $params['configoption7'];
        // $max_mailing = ($params['configoption1'] == 'Business') ? $params['configoptions']['max_mailing_list'] : $params['configoption8'];
        $max_mailing = $params['configoption8'];
        //$iredmail->MAX_ACCOUNT_LIMIT = ($params['configoption1'] == 'Business') ? $params['configoptions']['Account Limit'] : $params['configoption5'];
        $iredmail->MAX_ACCOUNT_LIMIT = $params['configoption5'];

        $account_limit = ($account_limit == 0) ? '-1' : $account_limit;
        $max_alias = ($max_alias == 0) ? '-1' : $max_alias;
        $max_mailing = ($max_mailing == 0) ? '-1' : $max_mailing;

        $settings .= 'max_user_quota:' . $max_quota . ';';

        $domain_data = array(
            "domain" => $domain,
            "description" => $description,
            "maxquota" => 0,
            "mailboxes" => 0,
            "aliases" => $max_alias,
            "maillists" => $max_mailing,
            "transport" => $transport,
            "created" => $created,
            "active" => 1,
            "settings" => $settings,
        );

        if ($iredmail->createdomain($domain_data) == 'error') {
            return "Error: Domain '" . $domain . "' already exists. Please try another one";
        } else {
            $domaindataArry = [
                    "domain" => $domain,
                    "sid" => $params["serviceid"],
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
            ];
            Capsule::table('mod_ireddomainname')->insert($domaindataArry);
            logModuleCall("iredmail", "CreateAccount", $domain_data, "success");
            return "success";
        }
    }

    #plan unlimited
    if ($params['configoption1'] == 'Unlimited') {
        $additional_quotaSize = $params['configoptions']['additional_quota'];
        $additional_quota = $iredmail->toMegabyteSize(trim($additional_quotaSize),'GB');

        $domain_quota_size_value = $iredmail->toMegabyteSize(trim($params['configoption3']), $params['configoption2']);
        $domain_quota_size_value = $domain_quota_size_value + $additional_quota;
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
            return "Error: Domain '" . $domain . "' already exists. Please try another one";
        } else {

            $domaindataArry = [
                    "domain" => $domain,
                    "sid" => $params["serviceid"],
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
            ];
            Capsule::table('mod_ireddomainname')->insert($domaindataArry);
            logModuleCall("iredmail", "CreateAccount unlimited", $domain_data, "success");
            return "success";
        }
    }

}

function iredmail_SuspendAccount($params) { 

    $licenceDetails = iredmail_license_checkLicense($params['configoption17']);
    if ($licenceDetails['status'] != "Active") {
        return "Invaild Licence Key, please enter vaild licence key";
    }
    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file';
    }

    
    if ($params['configoption1'] == 'Unlimited') {
        $hostname = $params['customfields']['ip_address'];
        $username = $params['customfields']['username'];
        $db_password = $params['customfields']['password'];
        $db_name = $params['customfields']['db_name'];
        $port = $params['customfields']['port'];
        $iredmail = new Iredmail($hostname, $username, $db_password, $port, "domain", $db_name, $error = NULL);
    } else {
        #business plan
        $iredmail = new Iredmail($params['serverhostname'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "domain", $params['serveraccesshash'], $error = NULL);
    }

    if (!empty($iredmail->error)) {
        return $iredmail->error;
    }

    $sid = $params['serviceid'];
    $listDomain = Capsule::table('mod_ireddomainname')->where('sid',$sid)->get();

    foreach ($listDomain as $key => $val) {
        $domain = $val->domain;
        $iredmail->suspenddomain($domain);
        if (!empty($iredmail->error)) {
             logModuleCall("iredmail", "SuspendDomainAccount", $domain, $iredmail->error);
            return $iredmail->error;
        }
        logModuleCall("iredmail", "SuspendDomainAccount", $domain, "success");
    }

    return "success";
}

function iredmail_UnsuspendAccount($params)
{
    $licenceDetails = iredmail_license_checkLicense($params['configoption17']);
    if ($licenceDetails['status'] != "Active") {
        return "Invaild Licence Key, please enter vaild licence key";
    }
    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file';
    }

    if ($params['configoption1'] == 'Unlimited') {
        $hostname = $params['customfields']['ip_address'];
        $username = $params['customfields']['username'];
        $db_password = $params['customfields']['password'];
        $db_name = $params['customfields']['db_name'];
        $port = $params['customfields']['port'];
        $iredmail = new Iredmail($hostname, $username, $db_password, $port, "domain", $db_name, $error = NULL);
    } else {
        #business plan
        $iredmail = new Iredmail($params['serverhostname'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "domain", $params['serveraccesshash'], $error = NULL);
    }

    if (!empty($iredmail->error)) {
        return $iredmail->error;
    }

    $sid = $params['serviceid'];
    $listDomain = Capsule::table('mod_ireddomainname')->where('sid',$sid)->get();

    foreach ($listDomain as $key => $val) {
        $domain = $val->domain;
        $iredmail->unsuspenddomain($domain);
        if (!empty($iredmail->error)) {
            logModuleCall("iredmail", "UnSuspendDomainAccount", $domain, $iredmail->error);
            return $iredmail->error;
        }
        logModuleCall("iredmail", "UnSuspendDomainAccount", $domain, "success");
    }

    return "success";
}

//include lang
function iredmail_getLang($params)
{
    global $CONFIG;
    if (!empty($_SESSION['Language']))
        $language = strtolower($_SESSION['Language']);
    else if (strtolower($params['clientsdetails']['language']) != '')
        $language = strtolower($params['clientsdetails']['language']);
    else
        $language = $CONFIG['Language'];

    $langfilename = dirname(__FILE__) . '/lang/' . $language . '.php';

    if (file_exists($langfilename))
        require_once($langfilename);
    else
        require_once(dirname(__FILE__) . '/lang/english.php');

    if (isset($lang)) {
        return $lang;
    }
}

function iredmail_TerminateAccount($params)
{
    $licenceDetails = iredmail_license_checkLicense($params['configoption17']);
    if ($licenceDetails['status'] != "Active") {
        return "Invaild Licence Key, please enter vaild licence key";
    }
    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file';
    }

  
    if ($params['configoption1'] == 'Unlimited') {
        $hostname = $params['customfields']['ip_address'];
        $username = $params['customfields']['username'];
        $db_password = $params['customfields']['password'];
        $db_name = $params['customfields']['db_name'];
        $port = $params['customfields']['port'];
        $iredmail = new Iredmail($hostname, $username, $db_password, $port, "domain", $db_name, $error = NULL);
    } else {
        #business plan
        $iredmail = new Iredmail($params['serverhostname'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "domain", $params['serveraccesshash'], $error = NULL);
    }

    if (!empty($iredmail->error)) {
        return $iredmail->error;
    }

    $sid = $params['serviceid'];
    $listDomain = Capsule::table('mod_ireddomainname')->where('sid',$sid)->get();

    foreach ($listDomain as $key => $val) {
        $domain = $val->domain;

        $iredmail->terminateaccount($domain);
        if (!empty($iredmail->error)) {
            logModuleCall("iredmail", "TerminateDomainAccount", $domain, $iredmail->error);
            return $iredmail->error;
        }
        logModuleCall("iredmail", "TerminateDomainAccount", $domain, "success");
    }

    Capsule::table('mod_iredmailUsers')->where('serviceid', $params['serviceid'])->delete();
    Capsule::table('mod_ireddomainname')->where('sid', $params['serviceid'])->delete();

    return "success";
}


function iredmail_ClientArea($params)
{
    $planselected  = $params['configoption1'];
    global $whmcs, $CONFIG;

    $licenceDetails = iredmail_license_checkLicense($params['configoption17']);
    if ($licenceDetails['status'] != "Active") {
        return "Invaild Licence Key, please enter vaild licence key";
    }
    $systemurl = (empty($CONFIG['SystemSSLURL'])) ? $CONFIG['SystemURL'] : $CONFIG['SystemSSLURL'];
    $url = $systemurl . '/modules/servers/iredmail/';

    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
    } else {
        logModuleCall('iredmail', 'Create Account', 'Error', 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file');
        return 'File "class.iredmail.api.php" is required, Please make sure you have uploaded iredmail class file';
    }

    if (empty($params['configoption9'])  || empty($params['configoption16'])) {
        return 'Error: (Empty Fields) You can not leave required fields empty. Please fill carefully all the values.';
    }

    if ($params['configoption1'] == 'Unlimited') {
        $hostname = $params['customfields']['ip_address'];
        $username = $params['customfields']['username'];
        $db_password = $params['customfields']['password'];
        $db_name = $params['customfields']['db_name'];
        $port = $params['customfields']['port'];
        $web_mail_url = $params['customfields']['web_mail_url'];
        $web_admin_url = $params['customfields']['web_admin_url'];
        $SBD = $params['customfields']['SBD'];

        $iredmail = new Iredmail($hostname, $username, $db_password, $port, "domain", $db_name, $error = NULL);

    } else {
        $iredmail = new Iredmail($params['serverhostname'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "mail", $params['serveraccesshash'], $error = NULL);

        $web_mail_url = trim($params['configoption16']);
        $web_admin_url = trim($params['configoption18']);
        $SBD = $params['customfields']['SBD'];
        if (empty($SBD)) {
            $SBD = trim($params['configoption10']);
        }
    }
   
    $domain = empty(trim($params['domain'])) ? $params['customfields']['Domain'] : trim($params['domain']);

	// $test = $iredmail->getDomains($domain);
	// $test2 =  $iredmail->getMailAccounts($domain, "", "");
// echo"<pre>";
// print_r($test);
// print_r($test2);
// die;

    if (empty($domain)) {
        return 'Error: (Empty Fields) You can not leave empty Domain filed.';
    }
    
    $result = $iredmail->select("domain", "mailboxes,maxquota", array("domain" => $domain));
    $data = mysqli_fetch_assoc($result);

    $LANG = iredmail_getLang($params);
    
    #client Id
    $clientId = $params['clientsdetails']['client_id'];

    #service Id
    $serviceId = $params['serviceid'];
    $pid = $params['pid'];

    #get clientcurrency with price
    $clientCurrencyId = $params['clientsdetails']['currency'];  
    $getcurrencyCode =Capsule::table('tblcurrencies')->where('id',$clientCurrencyId)->first();
    $currencyCode = $getcurrencyCode->code;
    $currencyprefix = $getcurrencyCode->prefix;
    $currencysuffix = $getcurrencyCode->suffix;
    $getPriceUnit = Capsule::table('mod_iredmailcurrencies_price')->where('pid',$pid)->where('code',$currencyCode)->first();
    $getPriceUnit = $getPriceUnit->price;
    $upgradePriceArray = [
        'prefix' => $currencyprefix,
        'suffix' => $currencysuffix, 
        'price' => $getPriceUnit
    ];

    if ($params['configoption1'] == 'Business') {
        if ($result->num_rows > 0) {
            $iredmail->MAX_ACCOUNT_LIMIT = $data['mailboxes'];
            $iredmail->DOMAIN_QUOTA_MB = $data['maxquota'];
            $iredmail->DOMAIN_QUOTA_TYPE = "UserDefined";
        }
    }

    # For Mail Directory - class.iredmail.settings.php
    $iredmail->MAILDIR_APPEND_TIMESTAMP = $params["configoption5"];
    $iredmail->MAILDIR_HASHED = $params["configoption15"];
    $iredmail->MAILDIR_PREPEND_DOMAIN = $params["configoption16"];

    #get default domain
    $getDomains = Capsule::table('mod_ireddomainname')->where('sid',$params['serviceid'])->get();

    $domainData = [];
    foreach ($getDomains as $key => $value) {
        $domainData[$value->domain]['emailcount'] = Capsule::table('mod_iredmailUsers')->where('domain', $value->domain)->where('serviceid', $params['serviceid'])->count();

        $domainData[$value->domain]['domainalias'] = $iredmail->getAliasDomain($value->domain);
    }
    $mailType = $params['configoption1'];
    $emailAccountArry = [];
    if (isset($params['configoptions']) && count( (array) $params['configoptions']) > 0) {
        foreach ($params['configoptions'] as $key => $value) {
            #get mail account details
            $getMailCount = Capsule::table('mod_iredmailUsers')->where('type', $key)->where('serviceid', $params['serviceid'])->count();
            $emailAccountArry[$key] = $value - $getMailCount;
        }
    }

    if (isset($_POST["ajaxcall"]) && isset($_POST["activity"])) {
        include 'ajax.php';
    }

    $vars = array(
        "domain" => $domain,
        "webmail" => $web_mail_url,
        "web_admin" => $web_admin_url,
        "is_admin" =>  $params['configoption19'],
        "url" => $url,
        "iredmailtype" => 'Business',
        "totalUsers" => $iredmail->MAX_ACCOUNT_LIMIT,
        "DEFAULT_QUOTA_MB" => $params["configoption5"],
        "domainlist" =>  $domainData,
        "emailaccountslimit" => $emailAccountArry,
        "upgrade_price" =>  $upgradePriceArray,
        "lang" =>  $LANG,
        "planselected" => $planselected,
        "max_quota_size" => $params['configoption6'],
        "min_quota_size" => $params['configoption4'],
    );

	
// echo"<pre>";
// print_r($vars);
// print_r($test);
// print_r($test2);

// die;

    return array(
        "templatefile" => "iredmail",
        "vars" => $vars,
    );
}


function iredmail_TestConnection(array $params)
{
    if (file_exists(__DIR__ . '/class.iredmail.api.php')) {
        require_once __DIR__ . '/class.iredmail.api.php';
        require_once __DIR__ . '/URLify.php';
    }

    $iredmail = new Iredmail($params['serverip'], $params['serverusername'], $params['serverpassword'], $params['serverport'], "domain", $params['serveraccesshash'], $error = NULL);
	

    if (!empty($iredmail->error)) {
        $errorMsg = $iredmail->error;
    } else {
        $success = true;
        $errorMsg = '';
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

function iredmail_createCustomfields($pid, $type) {

    if ($type == 'Business') {

        $customfieldarray = [
            'domain' => [
                'type' => 'product',
                'fieldname' => 'domain|Domain',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Enter Domain name to email accounts',
                'required' => 'on',
                'showorder' => 'on'
            ],
            'name_for_domain_admin_email' => [
                'type' => 'product',
                'fieldname' => 'name_for_domain_admin_email|name for domain administrator email address',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Enter name , before @domain for creating administrator email account.',
                'required' => 'on',
                'showorder' => 'on'
            ],
            'number_of_domain' => [
                'type' => 'product',
                'fieldname' => 'number_of_domain|Number of domain',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
             'domain_per_hour_outgoing' => [
                'type' => 'product',
                'fieldname' => 'domain_per_hour_outgoing|Per Domain per hour outgoing',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
             'email_per_hour_outgoing' => [
                'type' => 'product',
                'fieldname' => 'email_per_hour_outgoing|Per Email per hour outgoing',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',

                'sortorder' => '0'
            ],
            'SBD' => [
                'type' => 'product',
                'fieldname' => 'SBD|Storage Base Directory',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',

                'sortorder' => '0'
            ], 
        ];

        foreach ($customfieldarray as $key => $customfieldval) {
            $fieldname = explode('|', $customfieldval['fieldname']);

            if (Capsule::table('tblcustomfields')->where('type', $customfieldval['type'])->where('relid', $customfieldval['relid'])->where('fieldname', 'like', '%' . $fieldname[0] . '%')->count() == 0) {
                Capsule::table('tblcustomfields')->insert($customfieldarray[$key]);
            }
        }
    }

    if ($type == 'Unlimited') {
        $customfieldarray = [
            'ip_address' => [
                'type' => 'product',
                'fieldname' => 'ip_address|IP Address',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Enter IP Address or Hostname',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
            'username' => [
                'type' => 'product',
                'fieldname' => 'username|Database Username',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Enter username',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
            'password' => [
                'type' => 'product',
                'fieldname' => 'password|Database Password',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
            'db_name' => [
                'type' => 'product',
                'fieldname' => 'db_name|Database Name',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ],
            'port' => [
                'type' => 'product',
                'fieldname' => 'port|Enter port',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',

                'sortorder' => '0'
            ],
            'SBD' => [
                'type' => 'product',
                'fieldname' => 'SBD|Storage Base Directory',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',

                'sortorder' => '0'
            ],    
            'web_mail_url' => [
                'type' => 'product',
                'fieldname' => 'web_mail_url|Enter Web Mail URL',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ], 
            'web_admin_url' => [
                'type' => 'product',
                'fieldname' => 'web_admin_url|Enter Web Admin Url',
                'relid' => $pid,
                'fieldtype' => 'text',
                'description' => 'Only for admin',
                'adminonly' => 'on',
                'sortorder' => '0'
            ], 
        ];

        foreach ($customfieldarray as $key => $customfieldval) {

            $fieldname = explode('|', $customfieldval['fieldname']);
       
            if (Capsule::table('tblcustomfields')->where('type', $customfieldval['type'])->where('relid', $customfieldval['relid'])->where('fieldname', 'like', '%' . $fieldname[0] . '%')->count() == 0) {
                Capsule::table('tblcustomfields')->insert($customfieldarray[$key]);
            }
        }
    }
}

function create_configurableOption($pid, $type){

    $groupname = 'Iredmail-' . $pid;
    $addconfigurabledescription = "Iredmail user account mail limit";
    $result = Capsule::table('tblproductconfiggroups')->where('name', $groupname)->first();
    $groupid = $result->id;

    if (!$groupid) {
        $groupid = Capsule::table('tblproductconfiggroups')
                ->insertGetId(
                [
                    "name" => $groupname,
                    "description" => $addconfigurabledescription
                ]
        );
    }

    if (Capsule::table('tblproductconfiglinks')->where('gid', $groupid)->where('pid', $pid)->count() == 0) {
        $groupconfiglink = capsule::table('tblproductconfiglinks')->insert(
                [
                    "gid" => $groupid,
                    "pid" => $pid
                ]
        );
    }

    if($type == 'Business'){
        $configoptionsarray = [
            '1gb' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => '1gb|1 GB Mail Accounts',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '10',
                    'order' => '1',
                ],
                'suboptions' => ['1gb|1 GB Mail Accounts'],
            ],
            '2gb' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => '2gb|2 GB Mail Accounts',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '10',
                    'order' => '2',
                ],
                'suboptions' => ['2gb|2 GB Mail Accounts'],
            ],
            '5gb' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => '5gb|5 GB Mail Accounts',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '10',
                    'order' => '2',
                ],
                'suboptions' => ['5gb|5 GB Mail Accounts'],
            ],
            '10gb' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => '10gb|10 GB Mail Accounts',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '10',
                    'order' => '2',
                ],
                'suboptions' => ['10gb|10 GB Mail Accounts'],
            ],
            '30gb' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => '30gb|30 GB Mail Accounts',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '10',
                    'order' => '2',
                ],
                'suboptions' => ['30gb|30 GB Mail Accounts'],
            ]
        ];
    } elseif ($type == 'Unlimited') {
        $configoptionsarray = [
            'additional_quota' => [
                'configoption' => [
                    'gid' => $groupid,
                    'optionname' => 'additional_quota|Additional Quota for domain in GB',
                    'optiontype' => '4',
                    'qtyminimum' => '0',
                    'qtymaximum' => '50',
                    'order' => '1',
                ],
                'suboptions' => ['additional_quota|Additional Quota for domain in GB'],
            ],   
        ];
    }

    foreach ($configoptionsarray as $key => $configoptionvalue) {

        $checkOptionId = capsule::table('tblproductconfigoptions')->where('gid', $groupid)->where('optionname', 'like', '%' . $key . '%')->first();
        $configId = $checkOptionId->id;

        if (count( (array) $checkOptionId) == 0) {
            $configId = Capsule::table('tblproductconfigoptions')->insertGetId($configoptionvalue['configoption']);
        }

        $subOptions = $configoptionvalue['suboptions'];
        if (Capsule::table('tblproductconfigoptionssub')->where('configid', $configId)->where('optionname', 'like', '%' . $subOptions. '%')->count() == 0) {
            $optionArr = [
                'configid' => $configId,
                'optionname' => $subOptions,
                'hidden' => '0'
            ];

            $subOptionId = Capsule::table('tblproductconfigoptionssub')->insertGetId($optionArr);
            insertPriceForOptions($subOptionId);
        }

    }
}


function iredmail_configOption_assign_quota($id) {
    # Configurable Option
    $userMinimumLimit = 1;
    $userMaximumLimit = 0;

    $addconfigrablegroupname = "Ired Mail Quota:" . $id;
    $addconfigurabledescription = "Iredmail user quota";
    $addconfigurableoptionname = "Account Size";

    $configurableoptionlinkresult = Capsule::table('tblproductconfiggroups')->where('name', $addconfigrablegroupname)->get();
    if (!$configurableoptionlinkresult) {
        $configurablegroup_id = Capsule::table('tblproductconfiggroups')
                ->insertGetId(
                [
                    "name" => $addconfigrablegroupname,
                    "description" => $addconfigurabledescription
                ]
        );

        Capsule::table('tblproductconfiglinks')->insert(
                [
                    "gid" => $configurablegroup_id,
                    "pid" => $id
                ]
        );

        $configid = Capsule::table('tblproductconfigoptions')
                ->insertGetId(
                [
                    "gid" => $configurablegroup_id,
                    "optionname" => $addconfigurableoptionname,
                    "optiontype" => "4",
                    "qtyminimum" => $userMinimumLimit,
                    "qtymaximum" => $userMaximumLimit,
                    "order" => "",
                    "hidden" => ""
                ]
        );

        $tblpricing_rel_id = Capsule::table('tblproductconfigoptionssub')
                ->insertGetId(
                [
                    "configid" => $configid,
                    "optionname" => "MB",
                    "sortorder" => "",
                    "hidden" => ""
                ]
        );

        $datas = Capsule::table('tblcurrencies')->orderBy('code', 'DESC')->get();


        foreach ($datas as $data) {
            $curr_id = $data->id;
            $curr_code = $data->code;
            $currenciesarray[$curr_id] = $curr_code;
        }

        foreach ($currenciesarray as $curr_id => $currency) {
            Capsule::table('tblpricing')->insert(
                    [
                        'type' => 'configoptions',
                        'currency' => $curr_id,
                        'relid' => $tblpricing_rel_id,
                        'msetupfee' => '',
                        'qsetupfee' => '',
                        'annually' => '',
                        'biennially' => '',
                        'triennially' => ''
                    ]
            );
        }
    }
}


function iredmail_license_checkLicense($licensekey, $localkey = "")
{
    $whmcsurl = "http://members.whmcsglobalservices.com/"; #enter your own whmcs url here
    $licensing_secret_key = 'Leased-@ireadmail@2017'; #you can enter your own secret key here
    $check_token = time() . md5(mt_rand(1000000000, 1e+010) . $licensekey);
    $checkdate = date("Ymd");
    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
    $localkeydays = 10;
    $allowcheckfaildays = 5;
    $localkeyvalid = false;
   
    $lkey = Capsule::table('tblconfiguration')->where('setting', 'iredmail_localkey')->get(); 
    if ($lkey) {
        $localkey = $lkey[0]->value;
    }
    if ($localkey) {
        $localkey = str_replace("\n", "", $localkey);
        $localdata = substr($localkey, 0, strlen($localkey) - 32);
        $md5hash = substr($localkey, strlen($localkey) - 32);
        if ($md5hash == md5($localdata . $licensing_secret_key)) {
            $localdata = strrev($localdata);
            $md5hash = substr($localdata, 0, 32);
            $localdata = substr($localdata, 32);
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults['checkdate'];
            if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                if ($localexpiry < $originalcheckdate) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",", $results['validdomain']);
                    if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }

                    $validips = explode(",", $results['validip']);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }

                    if ($results['validdirectory'] != dirname(__FILE__)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }

    if (!$localkeyvalid) {
        $postfields['licensekey'] = $licensekey;
        $postfields['domain'] = $_SERVER['SERVER_NAME'];
        $postfields['ip'] = $usersip;
        $postfields['dir'] = dirname(__FILE__);
        if ($check_token) {
            $postfields['check_token'] = $check_token;
        }

        if (function_exists("curl_exec")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl . "modules/servers/licensing/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
            if ($fp) {
                $querystring = "";
                foreach ($postfields as $k => $v) {
                    $querystring .= "{$k}=" . urlencode($v) . "&";
                }
                $header = "POST " . $whmcsurl . "modules/servers/licensing/verify.php HTTP/1.0\r\n";
                $header .= "Host: " . $whmcsurl . "\r\n";
                $header .= "Content-type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-length: " . @strlen(@$querystring) . "\r\n";
                $header .= "Connection: close\r\n\r\n";
                $header .= $querystring;
                $data = "";
                @stream_set_timeout(@$fp, 20);
                @fputs(@$fp, @$header);
                $status = @socket_get_status(@$fp);

                while (!feof(@$fp) && $status) {
                    $data .= @fgets(@$fp, 1024);
                    $status = @socket_get_status(@$fp);
                }
                @fclose(@$fp);
            }
        }

        if (!$data) {
            $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ( $localkeydays + $allowcheckfaildays ), date("Y")));
            if ($localexpiry < $originalcheckdate) {
                $results = $localkeyresults;
            } else {
                $results['status'] = "Invalid";
                $results['description'] = "Remote Check Failed";
                return $results;
            }
        }

        preg_match_all("/<(.*?)>([^<]+)<\\/\\1>/i", $data, $matches);
        $results = array();
        foreach ($matches[1] as $k => $v) {
            $results[$v] = $matches[2][$k];
        }

        if ($results['md5hash'] && $results['md5hash'] != md5($licensing_secret_key . $check_token)) {
            $results['status'] = "Invalid";
            $results['description'] = "MD5 Checksum Verification Failed";
            return $results;
        }

        if ($results['status'] == "Active") {
            $results['checkdate'] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $results['localkey'] = $data_encoded;
            /*  for local key start */
            if (Capsule::table('tblconfiguration')->where('setting', 'iredmail_localkey')->count() == 0) {
                Capsule::table('tblconfiguration')->insert(
                        [
                            'setting' => 'iredmail_localkey',
                            'value' => $results['localkey']
                        ]
                );
            } else {
                Capsule::table('tblconfiguration')
                        ->where('setting', 'iredmail_localkey')
                        ->update(
                                [
                                    'value' => $results['localkey']
                                ]
                );
            }
            /*  for local key end */
        }
        $results['remotecheck'] = true;
    }

    unset($postfields);
    unset($data);
    unset($matches);
    unset($whmcsurl);
    unset($licensing_secret_key);
    unset($checkdate);
    unset($usersip);
    unset($localkeydays);
    unset($allowcheckfaildays);
    unset($md5hash);
    return $results;
}

function insertPriceForOptions($subOptionId)
{
    $price_data = Capsule::table('tblcurrencies')->get();
    foreach ($price_data as $priceval) {
        $curr_id = $priceval->id;
        if (Capsule::table('tblpricing')->where('type', 'configoptions')->where('currency', $curr_id)->where('relid', $subOptionId)->count() == 0) {

            Capsule::table('tblpricing')->insert(
                    [
                        'type' => 'configoptions',
                        'currency' => $curr_id,
                        'relid' => $subOptionId,
                        'msetupfee' => '',
                        'qsetupfee' => '',
                        'annually' => '',
                        'biennially' => '',
                        'triennially' => ''
                    ]
            );
        }
    }
}

function createEmailTemplate(){
    
    $emailarray = array(
        'emailarr' => array(
            'name' => 'Iredmail Usage Limit Reached',
            'subject' =>  'Iredmail Data Usage Reached',
            'type' => 'product',
            'message' => '<p><strong>Dear {$client_name},</strong></p>
                <p>This is a notice to inform you that your mail ({$mailaddress}) {$quota_percentage} data quota has been used till now {$date}. Your max quota size limit is : {$max_quota}.You can upgrade quota size from client area!</p>
                <p>Thank you</p>'
        ),
        'emailarr' => array(
            'name' => 'Iredmail domain Usage Limit Reached',
            'subject' =>  'Iredmail Domain Data Usage Reached',
            'type' => 'product',
            'message' => '<p><strong>Dear {$client_name},</strong></p>
                        <p>This is a notice to inform you that you have been used {$quota_percentage} data quota till now {$date}. Your account quota size limit is : {$max_quota}.</p>
                        <p>Here is the list of data usage!</p><br />
                        <table class="table">
                          <thead>
                            <tr>
                              <th scope="col">Mail Address</th>
                              <th scope="col">Max quota</th>
                              <th scope="col">Used Quota</th>
                              <th scope="col">messages</th>
                            </tr>
                          </thead><tbody>{$custom_list}</tbody></table>
                        <p>Thank you</p>'
        )
    );

    foreach ($emailarray as $emailval) {
        $name = $emailval['name'];
        $count = Capsule::table('tblemailtemplates')->where('type', 'product')->where('name', $name)->count();

        if ($count == 0) {
            Capsule::table('tblemailtemplates')->insert($emailval);
        }
    }
}

function update_customfield($pid, $type)
{
    if ($type == 'Business') {
        $planData = [
             'domain' => [
                'required' => '',
                'showorder' => '',
                'adminonly' => 'on'
            ],
            'name_for_domain_admin_email' => [
                'required' => '',
                'showorder' => '',
                'adminonly' => 'on'
            ],
        ];
    }

    if ($type == 'Unlimited') {
        $planData = [
            'domain' => [
                'required' => 'on',
                'showorder' => 'on',
                'adminonly' => ''
            ],
            'name_for_domain_admin_email' => [
                'required' => 'on',
                'showorder' => 'on',
                'adminonly' => ''
            ],
        ];
    }
 
    foreach ( $planData as $key => $customfieldval) {
        Capsule::table('tblcustomfields')->where('relid',$pid)->where('fieldname', 'like', '%' . $key . '%')->update($customfieldval);
    }
}
