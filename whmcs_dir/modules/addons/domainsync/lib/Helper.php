<?php

namespace WHMCS\Module\Addon\DomainSync;

use Exception;
use WHMCS\Database\Capsule;

class Helper
{

    // 
    function checkAvlDomain($domain) {
        try {
            $exist = Capsule::table("tbldomains")->where("domain", $domain)->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Domain already exists.'];
            } else {
                return ['status' => 'success', 'message' => 'Domain is Available.'];
            }
        } catch (Exception $e) {
            logActivity("Error checking domain availability: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while checking the domain.'];
        }
    }
    

}