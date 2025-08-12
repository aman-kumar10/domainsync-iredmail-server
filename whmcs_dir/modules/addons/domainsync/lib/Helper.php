<?php

namespace WHMCS\Module\Addon\DomainSync;

use Exception;
use WHMCS\Database\Capsule;

class Helper
{

    // 
    function checkAvlDomain($domain) {
        try {
            $tld = ".".$domain;
            $exist = Capsule::table('tbldomainpricing')->where("extension", $tld)->first();

            if ($exist) {
                return ['status' => 'success', 'message' => 'Domain is Available.'];
            } else {
                return ['status' => 'error', 'message' => 'Domain does not exists.'];
            }
        } catch (Exception $e) {
            logActivity("Error checking domain availability: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while checking the domain.'];
        }
    }

    function domainFormSubmit($uid, $pid, $domain, $submittedCustomFields){
        try {
            $payment = Capsule::table("tblclients")
                ->where("id", $uid)
                ->value("defaultgateway");

            $productFields = Capsule::table('tblcustomfields')
                ->where('type', 'product')
                ->where('relid', $pid)
                ->pluck('id')
                ->toArray();

            $customFieldsMapped = [];
            foreach ($productFields as $fieldId) {
                if (isset($submittedCustomFields[$fieldId])) {
                    $customFieldsMapped[$fieldId] = $submittedCustomFields[$fieldId];
                }
            }

            $command = 'AddOrder';
            $postData = [
                'clientid'      => $uid,
                'pid'           => [$pid],
                'domain'        => [$domain],
                'customfields'  => [
                    0 => base64_encode(serialize($customFieldsMapped))
                ],
                'paymentmethod' => !empty($payment) ? $payment : "banktransfer",
            ];

            $results = localAPI($command, $postData);

            if ($results['result'] === 'success') {
                Capsule::table("mod_ireddomainname")->insert([
                    'domain'     => $domain,
                    'sid'        => $results['serviceids'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                return [
                    'status'  => 'success',
                    'message' => 'Domain registered successfully. ServiceId: #<a href="/admin/clientsservices.php?userid=' . $uid . '&id=' . $results['serviceids'] . '">' . $results['serviceids'] . '</a>'
                ];

            } else {
                return [
                    'status'  => 'error',
                    'message' => $results['message']
                ];
            }
        } catch (Exception $e) {
            logActivity("Error submitting domain order: " . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'An error occurred while submitting the domain.'
            ];
        }
    }


    

}