<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/* * ****************************************************************
 *  WGS Ired Mail Server API
 *  Copyright whmcsglobalservices, All Rights Reserved
 *
 *  WHMCS Version: v6,v7,v8
 *  Version: 1.0.7
 *  Update Date: 10 oct, 2021
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
require_once 'class.iredmail.settings.php';

class Iredmail extends IredmailSettings {

    private $host;
    private $user;
    private $password;
    private $port;
    private $database;
    protected $link;
    protected $table;
    public $mode;
    public $error;
    public $DOMAIN_QUOTA_TYPE;
    public $MAX_ACCOUNT_LIMIT;
    public $DEFAULT_QUOTA_MB;
    public $MAX_QUOTA_MB;
    public $DOMAIN_QUOTA_MB;

    public function __construct()
    {
        $params = func_get_args();
        $this->host = $params[0];
        $this->user = $params[1];
        $this->password = $params[2];
        $this->port = $params[3];
        $this->mode = $params[4];
        $this->database = $params[5];


        if ($this->mode == "domain") {
            $this->table = "domain";
        } elseif ($this->mode == "mail") {
            $this->table = "mailbox";
        } elseif ($this->mode == "throttle") {
            $this->table = "policies";
        }

        $this->link = mysqli_connect($this->host, $this->user, $this->password, $this->database, $this->port);
         
        if (mysqli_connect_errno()) {
            $this->error = 'There is no connection with server. Please contact to administrator';
            return $this->error;
        }

        $this->checkDatabase();
    }

    public function __destruct()
    {
        mysqli_close($this->link);
    }

    /*
     *
     * Manage Domain Account Functions
     *
     */

    public function createdomain($data)
    {
        $res = mysqli_query($this->link, "SELECT * FROM domain WHERE domain = '" . $data['domain'] . "'");
        $count = mysqli_num_rows($res);
        if ($count >= 1) {
            return "error";
        } else {
            $this->insert("domain", $data);
        }
    }

	
	public function getDomains($domain){
     $result = mysqli_query($this->link, "SELECT * FROM domain WHERE domain = '" . $domain . "'");
        $count = mysqli_fetch_assoc($result);
    
    	return $count;
    }

    public function suspenddomain($domain)
    {
        $this->update("domain", array(
            "active" => "0"
                ), array(
            "domain" => $domain
        ));
    }

    public function unsuspenddomain($domain)
    {
        $this->update("domain", array(
            "active" => "1"
                ), array(
            "domain" => $domain
        ));
    }

    public function terminateaccount($domain)
    {
        $this->delete("domain", array(
            "domain" => $domain
        ));

        $this->delete("domain_admins", array(
            "domain" => $domain
        ));

        $this->delete("mailbox", array(
            "domain" => $domain
        ));

        $this->delete("forwardings", array(
            "domain" => $domain
        ));

        $this->delete("used_quota", array(
            "domain" => $domain
        ));

            //        $this->delete("alias", array(
            //            "domain" => $domain
            //        ));

        $this->delete("alias_domain", array(
            "target_domain" => $domain
        ));

        $result = $this->select("alias_domain", "*", array(
            "target_domain" => $domain
        ));

        $alias_domains = array();

        while ($data = mysqli_fetch_assoc($result)) {
            $alias_domains[] = $data["alias_domain"];
        }

        foreach ($alias_domains as $domain) {
            $this->delete("recipient_bcc_domain", array(
                "domain" => $domain
            ));
            $this->delete("recipient_bcc_user", array(
                "domain" => $domain
            ));
            $this->delete("sender_bcc_domain", array(
                "domain" => $domain
            ));
            $this->delete("sender_bcc_user", array(
                "domain" => $domain
            ));
        }

    }

    public function checkForwardingexist($mail, $forwordingMail, $domain)
    {
        $query = "SELECT count(*) as `existdata` FROM `forwardings` WHERE `address`='" . $mail."'AND `forwarding` = '".$forwordingMail."'";
        $result = mysqli_query($this->link, $query);
        $data = mysqli_fetch_array($result);
        return  $data['existdata'];
    }
    public function setForwardingemail($forwardmailarr)
    {
        $result = $this->insert("forwardings", $forwardmailarr);

        if (!empty($result)) {
            $this->error = mysqli_error($this->link);
        } else {
            return 'success';
        }
    }
    public function deleteForwardingemail($mailaddress)
    {
        $result = $this->delete("forwardings", array(
                "address" => $mailaddress
            ));
        if (!$result) {
            $this->error = mysqli_error($this->link);
        } else {
            return 'success';
        }
    }

    public function setAliasDomain($data)
    {
        $result = $this->insert("alias_domain", $data);
        if (!empty($result)) {
            $this->error = mysqli_error($this->link);
        } else {
            return 'success';
        }
    }

    public function getAliasDomain($domain)
    {
        $query = "SELECT * FROM `alias_domain` WHERE `alias_domain`='" . $domain."'";
        $result = mysqli_query($this->link, $query);

        if (!$result) {
            $this->error = mysqli_error($this->link);
        } else {
            $domainalias_mail = [];
            while ($data = mysqli_fetch_assoc($result)) {
                $domainalias_mail[] = $data;
            }
            return $domainalias_mail;
        }
    }

    public function is_valid_domain_name($domain_name)
    {
        if (filter_var(gethostbyname($domain_name), FILTER_VALIDATE_IP)) {
            return (preg_match('/^www./', $domain_name)) ? FALSE : TRUE;
        }
        return FALSE;
    }
    public function deleteDomainAlias($domain)
    {
        $result = $this->delete("alias_domain", array(
                "alias_domain" => $domain
            ));
        if (!empty($result)) {
            $this->error = mysqli_error($this->link);
        } else {
            return 'success';
        }
    }

    public function insertMailAlias($data, $where)
    {
        $resultCheck = $this->select("forwardings", "address,forwarding", array("forwarding" => $where));
        $num_rows = mysqli_num_rows($resultCheck);

        if ($num_rows > 0) {
            $result = $this->update("forwardings", $data, array(
                "forwarding" => $where
            ));

            if (!empty($result)) {
                $this->error = mysqli_error($this->link);
            } else {
                return 'success';
            }
        } else {
            $result = $this->insert("forwardings", $data);
            if (!empty($result)) {
                $this->error = mysqli_error($this->link);
            } else {
                return 'success';
            }
        }
    }

    public function getMailAlias($mailAddress)
    {
        $resultCheck = $this->select("forwardings", "address", array("forwarding" => $mailAddress));
        $data = mysqli_fetch_assoc($resultCheck);
        return $data['address'];
    }

    public function getForwordingemail($mailaddress, $domain)
    {
        $query = "SELECT `forwarding` FROM `forwardings` WHERE `address`='" . $mailaddress."'AND `domain` = '".$domain."'";
        $result = mysqli_query($this->link, $query);
        if (!$result) {
            $this->error = mysqli_error($this->link);
        } else {
            $forwarding_mail = [];
            while ($data = mysqli_fetch_assoc($result)) {
                $forwarding_mail[] = $data;
            }
            return $forwarding_mail;
        }
    }

    public function checkDomainAvailability($value, $type)
    {
        switch ($type) {
            case 'domain':
                $query = "SELECT count(*) as `availbility` FROM `domain` WHERE `domain`='" . $value . "'";
                $result = mysqli_query($this->link, $query);
                if (!$result) {
                    $this->error = mysqli_error($this->link);
                } else {
                    $data = mysqli_fetch_assoc($result);
                    return $data['availbility'];
                }
                break;
            case 'email':
                $query = "SELECT count(*) as `availbility` FROM `mailbox` WHERE `username`='" . $value . "@shineemail.com" . "'";
                $result = mysqli_query($this->link, $query);
                if (!$result) {
                    $this->error = mysqli_error($this->link);
                } else {
                    $data = mysqli_fetch_assoc($result);
                    return $data['availbility'];
                }
                break;
        }
    }

    public function setcatchall($domain, $params)
    {
        $previous_catch_email = trim($params["previous_catch_email"]);
        $new_catch_email = trim($params["new_catch_email"]);
        $modified = date("Y-m-d H:m:s");

        if (empty($new_catch_email)) {
            $result = $this->select("alias", "goto", array("address" => $domain, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);
            $num_rows = mysqli_num_rows($result);

            if ($num_rows > 0) {
                $this->delete("alias", array(
                    "address" => $domain,
                    "domain" => $domain
                ));
            }

            $this->error = "You did not enter value for email address";
        } elseif ($previous_catch_email == $new_catch_email) {
            $this->error = $new_catch_email . " email address is already added!";
        } elseif (empty($previous_catch_email)) {
            $result = $this->select("alias", "goto", array("address" => $domain, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);
            $num_rows = mysqli_num_rows($result);

            if ($num_rows == 0) {
                $this->insert("alias", array(
                    "address" => $domain,
                    "goto" => $new_catch_email,
                    "name" => "",
                    "moderators" => "",
                    "accesspolicy" => "",
                    "domain" => $domain,
                    "created" => $modified,
                    "modified" => "0000-00-00 00:00:00",
                    "expired" => "9999-12-31 00:00:00",
                    "active" => 1,
                    "islist" => 0
                ));
            }
        } else {
            $result = $this->select("alias", "goto", array("address" => $domain, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);
            $num_rows = mysqli_num_rows($result);

            if ($num_rows > 0) {
                $goto = explode(",", $data["goto"]);

                $previous_catch_email_array = explode(",", $previous_catch_email);
                foreach ($previous_catch_email_array as $each_previous_email_address) {
                    $key = array_search($each_previous_email_address, $goto);
                    unset($goto[$key]);
                }


                $new_catch_email_array = explode(",", $new_catch_email);
                foreach ($new_catch_email_array as $each_new_catch_email) {
                    if (!in_array($each_new_catch_email, $goto)) {
                        array_push($goto, $each_new_catch_email);
                    }
                }
                $new_catch_emails = implode(",", $goto);
                $this->update("alias", array(
                    "goto" => $new_catch_emails,
                    "modified" => $modified), array(
                    "address" => $domain,
                    "domain" => $domain
                ));
            }
        }
    }

    public function username_alrady_exiest($domain, $username)
    {
        $userResult = $this->select("mailbox", "quota", array("domain" => $domain, 'username' => $username));
        if ($userResult->num_rows > 0) {
            $response = array('type' => 'error', 'msg' => 'Mail Address already in use please try another mail address! ');
        } else {
            $response = true;
        }
        return $response;
    }


    public function mailUserQuota($quota = 0, $domain = null)
    {
        $usedQuota = 0;
        $response = true;

        $usedQuotaResult = $this->select("mailbox", "quota", array("domain" => $domain));

        if ($usedQuotaResult->num_rows > 0) {
            if ($usedQuotaResult->num_rows >= $this->MAX_ACCOUNT_LIMIT) {
                return array('type' => 'error', 'msg' => 'Max user limit exceed:  Your can\'t create new user');
            }

            while ($usedQuotaData = mysqli_fetch_assoc($usedQuotaResult)) {
                $usedQuota += (int) $usedQuotaData["quota"];
            }

            if ($usedQuota >= $this->DOMAIN_QUOTA_MB) {
                $response = array('type' => 'error', 'msg' => 'Your don\'t sufficent Quota for new user');
            } elseif ($quota > ($this->DOMAIN_QUOTA_MB - $usedQuota)) {
                $response = array('type' => 'error', 'msg' => 'Your have only ' . ($this->DOMAIN_QUOTA_MB - $usedQuota) . ' MB Quota left');
            } else {
                return true;
            }
        } else {
            if ($this->DOMAIN_QUOTA_MB) { // if domain_quota_set as '0' means unlimited
                if ($quota > $this->DOMAIN_QUOTA_MB) {
                    $response = array('type' => 'error', 'msg' => 'User Quota can\'t be grater then domain quota');
                } else {
                    $response = true;
                }
            } else {
                $response = true;
            }
        }
        return $response;
    }

    public function createmailaccount($params, $SBD)
    {
        foreach ($params as $key => $value) {
            $$key = trim($value);
        }
        $domain = $params['domain'];
        $mail = $params['mail'];
        $quota = $params['quota'];
        $password = $params['password'];

        if (isset($params['isadmin'])) {
            $isadmin = $params['isadmin'];
        } else {
            if ($params['configoption19'] == 'on') {
                $isadmin = 1;
            } else {
                $isadmin = 0;
            }
        }

        if (empty($domain)) {
            return array(
                'type' => "error",
                'msg' => "No domain found"
            );
        }

        $mailaddress = $mail . '@' . $domain;
        $response = $this->username_alrady_exiest($domain, $mailaddress);

        if (isset($response['type'])) {
            return $response;
        }

        $userquota = true;

        $encrypted_password = $password;

        $SBD = $this->storageBaseDirectory($SBD);

        $storagenode = $SBD['storagenode'];

        $storagebasedirectory = $SBD['storagebasedirectory'];
        $maildir = $this->generate_mail_directory_path($mailaddress);

        $created = date("Y-m-d H:m:s");

        if ($userquota == true) {
            $mailAddArray = array(
                "username" => $mailaddress,
                "password" => $encrypted_password,
                "name" => $displayname,
                "language" => "en_US",
                "storagebasedirectory" => $storagebasedirectory,
                "storagenode" => $storagenode,
                "maildir" => $maildir,
                "quota" => $quota,
                "domain" => $domain,
                "transport" => null,
                "department" => null,
                "rank" => 'normal',
                "employeeid" => null,
                "isadmin" => $isadmin,
                "isglobaladmin" => "0",
                //"lastlogindate" => "1970-01-01 01:01:01",
                //"lastloginipv4" => "0",
                //"lastloginprotocol" => null,
                "disclaimer" => null,
                "passwordlastchange" => $created,
                "created" => $created,
                "modified" => "1970-01-01 01:01:01",
                "expired" => "9999-12-31 00:00:00",
                "enablelmtp" => "1",
                "settings" => null,
                "active" => 1,
            );

            $this->insert("mailbox", $mailAddArray);

            $forwardmailarr = [
                "address" => $mailaddress,
                "forwarding" => $mailaddress,
                "domain" => $domain,
                "dest_domain" => $domain,
                "is_maillist" => "0",
                "is_list" => "0",
                "is_forwarding" => "1",
                "is_alias" => "0",
                "active" => "1",
            ];

            $this->insert("forwardings", $forwardmailarr);

            if ($isadmin == 1) {
                $this->insert("domain_admins", array(
                    "username" => $mailaddress,
                    "domain" => $domain,
                    "created" => $created,
                    "modified" => "0000-00-00 00:00:00",
                    "expired" => "9999-12-31 00:00:00",
                    "active" => "1",
                ));
            }

            return array(
                'type' => "success",
            );
        } else {

            return array(
                'type' => "error",
                'msg' => "You cannot create more users under domain " . $domain . ", No domain quota left"
            );
        }
    }

    public function throttleEmailAccount($mail, $outboundMessageLimit, $inboundMessageLimit)
    {
        # Limit Out Bound Mails
        $outboundName = "throttle_outbound_" . $mail;
        $outboundDescription = "outbound_" . $mail;
        $this->insert("policies", array(
            "Name" => $outboundName,
            "Priority" => "30",
            "Description" => $outboundDescription,
            "Disabled" => "0"
        ));
        $outbound_id = mysqli_insert_id($this->link);
        $this->insert("policy_members", array(# policy_members
            "PolicyID" => $outbound_id,
            "Source" => $mail,
            "Destination" => "!%internal_ips,!%internal_domains",
//            "Comment" => "",
            "Disabled" => 0
        ));
        $this->insert("quotas", array(
            "PolicyID" => $outbound_id,
            "Name" => "outbound_" . $mail,
            "Track" => "Sender:user@domain",
            "Period" => 86400,
            "Verdict" => "reject",
            "Data" => "Quota exceeded (" . $outboundMessageLimit . " messages in 86400 seconds)",
//            "Comment" => "",
            "Disabled" => 0
        ));
        $outbound_quota_limit_id = mysqli_insert_id($this->link);
        $this->insert("quotas_limits", array(
            "QuotasID" => $outbound_quota_limit_id,
            "Type" => "MessageCount",
            "CounterLimit" => $outboundMessageLimit,
//            "Comment" => "",
            "Disabled" => 0
        ));

        # Limit In Bound Mails
        $inboundName = "throttle_inbound_" . $mail;
        $inboundDescription = "inbound_" . $mail;
        $this->insert("policies", array(
            "Name" => $inboundName,
            "Priority" => "30",
            "Description" => $inboundDescription,
            "Disabled" => "0"
        ));
        $inbound_id = mysqli_insert_id($this->link);
        $this->insert("policy_members", array(# policy_members
            "PolicyID" => $inbound_id,
            "Source" => "!%internal_ips,!%internal_domains",
            "Destination" => $mail,
//            "Comment" => "",
            "Disabled" => 0
        ));
        $this->insert("quotas", array(
            "PolicyID" => $inbound_id,
            "Name" => "inbound_" . $mail,
            "Track" => "Recipient:user@domain",
            "Period" => 86400,
            "Verdict" => "reject",
            "Data" => "Quota exceeded (" . $inboundMessageLimit . " messages in 86400 seconds)",
//            "Comment" => "",
            "Disabled" => 0
        ));
        $inbound_quota_limit_id = mysqli_insert_id($this->link);
        $this->insert("quotas_limits", array(
            "QuotasID" => $inbound_quota_limit_id,
            "Type" => "MessageCount",
            "CounterLimit" => $inboundMessageLimit,
//            "Comment" => "",
            "Disabled" => 0
        ));
    }

    public function deleteThrottleData($webmail)
    {
        $outbound_id_result = $this->select("quotas", "ID,PolicyID", array("Name" => "outbound_" . $webmail));
        $outbound_id_data = mysqli_fetch_assoc($outbound_id_result);

        $quota_limit_outbound_id_result = $this->select("quotas_limits", "ID", array("QuotasID" => $outbound_id_data["ID"]));
        $quota_limit_outbound_id_data = mysqli_fetch_array($quota_limit_outbound_id_result);
        $this->delete("quotas_tracking", array("QuotasLimitsID" => $quota_limit_outbound_id_data["ID"]));
        $this->delete("quotas_limits", array("ID" => $quota_limit_outbound_id_data["ID"]));
        $this->delete("quotas", array("ID" => $outbound_id_data["ID"]));
        $this->delete("policy_members", array("PolicyID" => $outbound_id_data["PolicyID"]));
        $this->delete("policies", array("ID" => $outbound_id_data["PolicyID"]));

        $inbound_id_result = $this->select("quotas", "ID,PolicyID", array("Name" => "inbound_" . $webmail));
        $inbound_id_data = mysqli_fetch_assoc($inbound_id_result);

        $quota_limit_inbound_id_result = $this->select("quotas_limits", "ID", array("QuotasID" => $inbound_id_data["ID"]));
        $quota_limit_inbound_id_data = mysqli_fetch_assoc($quota_limit_inbound_id_result);
        $this->delete("quotas_tracking", array("QuotasLimitsID" => $quota_limit_inbound_id_data["ID"]));
        $this->delete("quotas_limits", array("ID" => $quota_limit_inbound_id_data["ID"]));
        $this->delete("quotas", array("ID" => $inbound_id_data["ID"]));
        $this->delete("policy_members", array("PolicyID" => $inbound_id_data["PolicyID"]));
        $this->delete("policies", array("ID" => $inbound_id_data["PolicyID"]));
    }

    public function getMailAccounts($domain, $type, $serviceid)
    {
        $mailAccounts = array();
        $result = $this->select("mailbox", "username,name,quota,active", array("domain" => $domain));
        $webmails = array();

        while ($data = mysqli_fetch_assoc($result)) {
            $pdata = '';
            $primaryData = Capsule::table('mod_iredmailUsers')->where('mail', $data["username"])->get();
            if ($primaryData) {
                $pdata = $primaryData[0]->primary;
            }

            $result2 = $this->select("used_quota", "bytes,messages", array("username" => $data["username"], "domain" => $domain));
            $data2 = mysqli_fetch_assoc($result2);
            $data["emails"] = empty($data2["messages"]) ? 0 : $data2["messages"];
            $data["used"] = empty($data2["bytes"]) ? 0 : $this->toByteSize($data2["bytes"]);
            $data["primary"] = $pdata;
            # Convert quota
            $bytesizequota = $data["quota"] * 1024 * 1024;
            $data["quota"] = $this->toByteSize($bytesizequota);
            $mailAccounts[] = $data;
        }
        return $mailAccounts;
    }
    #for unlimited data cron
    public function getMailDataUsage($sid,$mail,$domain){
        $result = $this->select("mailbox", "username,name,quota,active", array("domain" => $domain,"username" => $mail));
        $webmails = array();

        while ($data = mysqli_fetch_assoc($result)) {
             
            $mailTotalQuota = $data['quota'];
            $mailUsername = $data['username'];
            $result2 = $this->select("used_quota", "bytes,messages", array("username" => $mailUsername, "domain" => $domain));
            $data2 = mysqli_fetch_assoc($result2);
            if (!array_key_exists("bytes",$data2)){
                $data2['bytes'] = 0;
                $data2['messages'] = 0;
            }

            $data2['mail'] = $mailUsername;
            $data2['sid'] = $sid;
            $data2['mailquota'] = $mailTotalQuota;

        }
        return $data2;
    }


    public function getMailAccountslist($serviceid)
    {
        $mailAccounts = array();

        $result = $this->select("mailbox", "username,name,quota,active,domain,enableimap,enablepop3");
        $webmails = array();

        while ($data = mysqli_fetch_assoc($result)) {
            $pdata = '';

            $primaryData = Capsule::table('mod_iredmailUsers')->where('serviceid', $serviceid)->get();
            foreach ($primaryData as $key => $existdata) {
                if ($existdata->mail ==  $data["username"]) {
                    $data["domain"] = $data["domain"];
                    $result2 = $this->select("used_quota", "bytes,messages", array("username" => $data["username"]));
                    $data2 = mysqli_fetch_assoc($result2);
                    $data["emails"]  = empty($data2["messages"]) ? 0 : $data2["messages"];
                    $data["used"]= empty($data2["bytes"]) ? 0 : $this->toByteSize($data2["bytes"]);
                    $bytesizequota = $data["quota"] * 1024 * 1024;
                    $data["quota"]  = $this->toByteSize($bytesizequota);
                   
                    $mailAccounts[] = $data;
                }
            }
        }

        return $mailAccounts;
    }

    public function getMailAccountslistCatch($domain)
    {
        $mailAccounts = array();
        $result = $this->select("mailbox", "username,name,quota,active,domain", array("domain" => $domain));
        $selectedMail = $this->getdoaminCatch($domain);
        if ($selectedMail != "notexist") {
            $mailAccounts["selectedmail"] = $selectedMail;
        }

        while ($data = mysqli_fetch_assoc($result)) {
                    $data["domain"] = $data["domain"];
                    $data["username"] = $data["username"];

                    $mailAccounts["list"][] = $data;
        }

        return $mailAccounts;
    }
    public function doaminCatchAdd($data, $domain)
    {
        $resultCheck = $this->select("forwardings", "address", array("address" => $domain));
        $num_rows = mysqli_num_rows($resultCheck);
        
        if ($num_rows > 0) {
            $result = $this->update("forwardings", $data, array("address" => $domain));
             
            if (!empty($result)) {
                $this->error = mysqli_error($this->link);
            } else {
                return 'success';
            }
        } else {
            $result = $this->insert("forwardings", $data);
            if (!empty($result)) {
                $this->error = mysqli_error($this->link);
            } else {
                return 'success';
            }
        }
    }
    public function domainCatchDelete($domain)
    {
        $result = $this->delete("forwardings", array("address" => $domain));
        if (!empty($result)) {
            $this->error = mysqli_error($this->link);
        } else {
            return 'success';
        }
    }
    public function getdoaminCatch($domain)
    {
        $resultCheck = $this->select("forwardings", "*", array("address" => $domain));
        $num_rows = mysqli_num_rows($resultCheck);
         
        if ($num_rows > 0) {
            $data = mysqli_fetch_assoc($resultCheck);
            return $data['forwarding'];
        }

        return "notexist";
    }


    public function changepassword($password, $account)
    {
        $modified = date("Y-m-d H:m:s");

        $this->update("mailbox", array(
            "password" => trim($password),
            "modified" => $modified
                ), array(
            "username" => $account
        ));
    }

    public function getForwardEmails($domain)
    {
        $forwardEmailArray = array();
        $result = $this->select("alias", "goto,address", array("domain" => $domain));
        while ($data = mysqli_fetch_assoc($result)) {
            $emails = explode(",", $data["goto"]);
            $key = array_search($data["address"], $emails);
            unset($emails[$key]);
            $forwardemails = implode(",", $emails);
            $forwardEmailArray[$data["address"]] = $forwardemails;
        }
        return $forwardEmailArray;
    }

    public function setForwardEmail($domain, $params)
    {
        $mailaddress = $params["mail_account"];
        $previous_forward_email = trim($params["previous_forward_email"]);
        $new_forward_email = trim($params["new_forward_email"]);

        if ($mailaddress == $new_forward_email) {
            $this->error = "You cannot use account mail address. Try some other email address!";
        } elseif (empty($new_forward_email)) {
            $result = $this->select("alias", "goto", array("address" => $mailaddress, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);
            $num_rows = mysqli_num_rows($result);
            if ($num_rows > 0) {
                $goto = explode(",", $data["goto"]);

                $previous_forward_email_array = explode(",", $previous_forward_email);
                foreach ($previous_forward_email_array as $each_previous_forward_email) {
                    $key = array_search($each_previous_forward_email, $goto);
                    unset($goto[$key]);
                }

                $forwardemail_empty = implode(",", $goto);
                $modified = date("Y-m-d H:m:s");
                $this->update("alias", array(
                    "goto" => $forwardemail_empty,
                    "modified" => $modified), array(
                    "address" => $mailaddress,
                    "domain" => $domain
                ));
            }
            $this->error = "You did not enter value for email address";
        } elseif ($previous_forward_email == $new_forward_email) {
            $this->error = $new_forward_email . " email address is already added!";
        } elseif (empty($previous_forward_email)) {
            $result = $this->select("alias", "goto", array("address" => $mailaddress, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);

            $goto = explode(",", $data["goto"]);
            if (!in_array($new_forward_email, $goto)) {
                array_push($goto, $new_forward_email);
                $forwardemail_new = implode(",", $goto);
                $modified = date("Y-m-d H:m:s");
                $this->update("alias", array(
                    "goto" => $forwardemail_new,
                    "modified" => $modified), array(
                    "address" => $mailaddress,
                    "domain" => $domain
                ));
            }
        } else {
            $result = $this->select("alias", "goto", array("address" => $mailaddress, "domain" => $domain));
            $data = mysqli_fetch_assoc($result);

            $goto = explode(",", $data["goto"]);

            $previous_forward_email_array = explode(",", $previous_forward_email);
            foreach ($previous_forward_email_array as $each_previous_forward_email) {
                $key = array_search($each_previous_forward_email, $goto);
                unset($goto[$key]);
            }

            $new_forward_email_array = explode(",", $new_forward_email);
            foreach ($new_forward_email_array as $each_new_forward_email) {
                if (!in_array($each_new_forward_email, $goto)) {
                    array_push($goto, $each_new_forward_email);
                }
            }
            $forwardemail_new = implode(",", $goto);
            $modified = date("Y-m-d H:m:s");
            $this->update("alias", array(
                "goto" => $forwardemail_new,
                "modified" => $modified), array(
                "address" => $mailaddress,
                "domain" => $domain
            ));
        }
    }


    public function suspendwebmailaccount($mail)
    {
        $this->update("mailbox", array("active" => 0), array("username" => $mail));
    }

    public function unsuspendwebmailaccount($mail)
    {
        $this->update("mailbox", array("active" => 1), array("username" => $mail));
    }

    public function teminatewebmailaccount($mail)
    {
        $this->delete("mailbox", array("username" => $mail));
        $this->delete("used_quota", array("username" => $mail));
    }

    public function setAccountStatus($status, $account)
    {
        $this->update("mailbox", array("active" => $status), array("username" => $account));
    }

    public function setAccountStatusPop($status, $mail)
    {
        $this->update("mailbox", array("enablepop3" => $status,"enablepop3secured" => $status,"enablepop3tls" => $status), array("username" => $mail));
    }

    public function setAccountStatusImap($status, $mail)
    {
        $this->update("mailbox", array("enableimap" => $status,"enableimapsecured" => $status,"enableimaptls" => $status), array("username" => $mail));
    }

    public function deleteAccount($params)
    {
        $account = $params["del_account_val"];
        $this->delete("mailbox", array("username" => $account));
        $this->delete("used_quota", array("username" => $account));
    }

    /*
     *
     * MySQL Operations
     *
     */

    public function insert($table, $table_values)
    {
        $columns = array();
        $values = array();
        foreach ($table_values as $column => $value) {
            $columns[] = '`' . $column . '`';
            $values[] = "'" . $value . "'";
        }

        $query = "INSERT INTO `" . $table . "`(" . implode(',', $columns) . ") VALUES(" . implode(',', $values) . ")";
        $result = mysqli_query($this->link, $query);
       
        if (!$result) {
            $this->error = mysqli_error($this->link);
        }
    }

    public function select($table, $column, $where = NULL)
    {
        $query = "SELECT " . $column . " FROM `" . $table . "`";
        $where_condition = '';
        if ($where) {
            $where_array = array();
            $query .= ' WHERE ';
            foreach ($where as $col => $val) {
                $where_array[] = '`' . $col . '`="' . $val . '"';
            }
            $where_condition = implode(" AND ", $where_array);
        }

        $result = mysqli_query($this->link, $query . $where_condition);
        if (!$result) {
            $this->error = mysqli_error($this->link);
        } else {
            return $result;
        }
    }

    public function update($table, $column, $where)
    {
        $columns = array();
        foreach ($column as $col => $val) {
            $columns[] = "`" . $col . "`='" . $val . "'";
        }

        $column_str = implode(",", $columns);
        $where_condition = '';
        if ($where) {
            $where_array = array();
            $where_condition = ' WHERE ';
            foreach ($where as $col => $val) {
                $where_array[] = "`" . $col . "`='" . $val . "'";
            }
            $where_condition .= implode(" AND ", $where_array);
        }

        $query = "UPDATE " . $table . " SET " . $column_str . $where_condition;

        $result = mysqli_query($this->link, $query);
        if (!$result) {
            $this->error = mysqli_error($this->link);
        }
    }

    public function delete($table, $where = NULL)
    {
        $where_condition = '';
        if ($where) {
            $where_array = array();
            $where_condition = ' WHERE ';
            foreach ($where as $col => $val) {
                $where_array[] = "`" . $col . "`='" . $val . "'";
            }
            $where_condition .= implode(" AND ", $where_array);
        }

        $query = "DELETE FROM " . $table . $where_condition;
        $result = mysqli_query($this->link, $query);
        if (!$result) {
            $this->error = mysqli_error($this->link);
        }
    }

    /*
     *
     * Database Selection
     *
     */

    public function checkDatabase()
    {
        $query = "DESCRIBE `" . $this->table . "`";

        $result = mysqli_query($this->link, $query);

        if (!$result) {
            $this->error = mysqli_error($this->link);
        } else {
            $data = mysqli_fetch_assoc($result);
            if (count( (array) $data) == 0) {
                $this->error = 'An unknown error occured!';
            }
        }
    }

    /*
     *
     * Custom Functions
     *
     */

    public function toMegabyteSize($value, $type)
    {
        $gigabyte = 1 * 1024;
        $terabyte = $gigabyte * 1024;
        if(empty($value)){
            $value = 0; 
        }
       
        switch ($type) {
            case 'GB':
                $result = $value * $gigabyte;
                break;
            case 'TB':
                $result = $value * $terabyte;
                break;
            case 'MB':
                $result = $value;
                break;
        }
        return $result;
    }

    public function toByteSize($bytes, $precision = 2)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' KB';
        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' MB';
        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' GB';
        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $precision) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }


    public function convertdatatoMB($quotatype)
    {
        $Getunit = substr($quotatype, -2);

        $Getquota =  preg_replace('/[^0-9]/', '', $quotatype);
        if ($Getunit == 'mb') {
            $quota = $Getquota;
        }
        if ($Getunit == 'gb') {
            $quota = $Getquota * 1024;
        }
        return $quota ;
    }

    private function iredMail_generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function iredMail_generateSecretHash($hashType, $password)
    {
        $salt = $this->iredMail_generateRandomString(8);
        $hashed_password = ($hashType == 'SSHA512') ? hash('sha512', $password . $salt, true) : hash('md5', $password . $salt, true);
        $password_to_db = '{' . $hashType . '}' . base64_encode($hashed_password . $salt);
        return $password_to_db;
    }
}
