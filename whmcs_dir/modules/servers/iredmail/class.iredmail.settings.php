<?php

/* * ****************************************************************
 *  WGS Ired Mail Server API
 *  Copyright whmcsglobalservices, All Rights Reserved
 * 
 *  WHMCS Version: v6,v7,v8
 *  Version: 1.0.6
 *  Update Date: 22 DEC, 2020
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
require_once 'Md5Crypt.php';

class IredmailSettings extends Md5Crypt {

    public $MAILDIR_HASHED;
    public $MAILDIR_PREPEND_DOMAIN;
    public $MAILDIR_APPEND_TIMESTAMP;

    public function __construct($param) {
        
    }

    public function generate_mail_directory_path($mail) {
        # Generate path for mailbox
        if (empty($mail)) {
            return;
        }
        # Get user/domain part from mail address
        $username_domain = explode("@", $mail, 2);

        $username = str_split($username_domain[0]);
        $usernamelength = strlen($username_domain[0]);
        $domain = $username_domain[1];
        # Get current timestamp
        $timestamp = '';
        if ($this->MAILDIR_APPEND_TIMESTAMP == "True") {
            $timestamp = "-" . date("Y.m.d.H.m.s");
        }
        if ($this->MAILDIR_HASHED == "True") {
            if ($usernamelength > 3) {
                $maildir = $username[0] . '/' . $username[1] . '/' . $username[2] . '/' . $username_domain[0] . $timestamp . '/';
            } elseif ($usernamelength == 2) {
                $maildir = $username[0] . '/' . $username[1] . '/' . $username[1] . '/' . $username_domain[0] . $timestamp . '/';
            } else {
                $maildir = $username[0] . '/' . $username[0] . '/' . $username[0] . '/' . $username_domain[0] . $timestamp . '/';
            }
            $mailMessageStore = $maildir;
        } else {
            $mailMessageStore = $username_domain[0] . $timestamp . '/';
        }
        //if ($this->MAILDIR_PREPEND_DOMAIN == "True") {
        $mailMessageStore = $domain . '/' . $mailMessageStore;
        //}
        return strtolower($mailMessageStore);
    }

    public function storageBaseDirectory($storagebasedirectory) {
        $tempStorageBaseDirectory = strtolower($storagebasedirectory);
        $splitedSBD = explode("/", $tempStorageBaseDirectory);
        $storageNode = array_pop($splitedSBD);
        $SBD = implode("/", $splitedSBD);
        return array("storagenode" => $storageNode, "storagebasedirectory" => $SBD);
    }

}
