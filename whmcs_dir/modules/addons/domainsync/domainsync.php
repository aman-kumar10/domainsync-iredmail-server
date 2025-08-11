<?php

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\DomainSync\Admin\AdminDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


function domainsync_config()
{
    return [
        'name' => 'Domain Sync',
        'description' => 'Domain Sync Addon Module',
        'author' => 'WHMCS GLOBAL SERVICES',
        'language' => 'english',
        'version' => '1.0',
        // 'fields' => [
            
        // ]
    ];
}


function domainsync_activate()
{
    try {
        // 
        return [
            'status' => 'success',
            'description' => 'Domain Sync addon module has been activated.',
        ];
    } catch (\Exception $e) {
        return [
            'status' => "error",
            'description' => 'Unable to activate the Domain Sync addon module: ' . $e->getMessage(),
        ];
    }
}


function domainsync_deactivate()
{
    try {
        // 
        return [
            'status' => 'success',
            'description' => 'Your Domain Sync addon module has been deactivated',
        ];
    } catch (\Exception $e) {
        return [
            "status" => "error",
            "description" => "Unable to deactivate the Domain Sync addon module: {$e->getMessage()}",
        ];
    }
}


function domainsync_output($vars)
{
    // 
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'dashboard';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    return $response;
}

