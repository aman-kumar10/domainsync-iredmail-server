<?php

namespace WHMCS\Module\Addon\DomainSync\Admin;

use Exception;
use WHMCS\Module\Addon\DomainSync\Helper;
use WHMCS\Database\Capsule;

require "../includes/customfieldfunctions.php";

use Smarty;

global $whmcs;

class Controller
{
    private $params;


    private $tplVar = [];

    private $tplFileName;

    public $smarty;

    private $lang = [];

    /**
     * Constructor initializes parameters, paths, and language
     */
    public function __construct($params)
    {
        global $CONFIG;
        global $customadminpath;
        $this->params = $params;

        $module = $params['module'];

        $this->tplVar['adminpath']     = $customadminpath;
        $this->tplVar['rootURL']     = $CONFIG['SystemURL'];
        $this->tplVar['urlPath']     = $CONFIG['SystemURL'] . "/modules/addons/{$module}/";
        $this->tplVar['tplDIR']      = ROOTDIR . "/modules/addons/{$module}/templates/";
        $this->tplVar['header']      = ROOTDIR . "/modules/addons/{$module}/templates/header.tpl";
        $this->tplVar['modals']      = ROOTDIR . "/modules/addons/{$module}/templates/modals.tpl";
        $this->tplVar['moduleLink']  = $params['modulelink'];

        $adminLang = $_SESSION['adminlang'] ?? 'english';
        $langFile  = __DIR__ . "/../../lang/{$adminLang}.php";

        if (!file_exists($langFile)) {
            $langFile = __DIR__ . "/../../lang/english.php";
        }

        global $_ADDONLANG;
        include($langFile);
        $this->lang = $_ADDONLANG;
    }

    /**
     * Dashboard tab handler
     */
    public function dashboard()
    {
        try {
            global $whmcs;
            $helper = new Helper;

            // get custom fields
            if (isset($_POST['data_action']) && $_POST['data_action'] === "getCustomfields") {
                $customFields = getCustomFields('product', $_POST['product_id'], "");

                $html = '';
                    foreach ($customFields as $field) {

                        $fieldName = "customfield[".$field['id']."]";

                        $html .= '
                        <div class="form-group">
                            <label class="col-lg-6 col-sm-6 control-label">' . htmlspecialchars($field['name']) . '</label>
                            <div class="col-lg-6 col-sm-6">
                                <input type="text" 
                                    name="'.$fieldName.'" 
                                    data-text_id=' . $field['textid']. ' 
                                    class="form-control required-field" 
                                    placeholder="' . htmlspecialchars($field['description']) . '" 
                                    value="' . htmlspecialchars($field['value']) . '">
                            </div>
                        </div>';
                    }

                    echo json_encode([
                        'success' => true,
                        'html' => $html
                    ]);
                    exit;
            }

            if ($whmcs->get_req_var('domain_frm') === 'domainSubFrm') {

                $helper = new Helper;

                $uid = $whmcs->get_req_var('userid');
                $pid = $whmcs->get_req_var('selectedProduct');

                $customFields = $whmcs->get_req_var('customfield');

                $domain = '';
                foreach ($customFields as $value) {
                    if (preg_match('/^(?!\-)([A-Za-z0-9\-]{1,63}\.)+[A-Za-z]{2,}$/', trim($value))) {
                        $domain = trim($value);
                        break;
                    }
                }

                $formResponse = $helper->domainFormSubmit($uid, $pid, $domain, $customFields);
                
                if ($formResponse['status'] === 'success') {
                    $this->tplVar["alert_msg"] = '<div class="alert alert-success">' . $formResponse['message'] . '</div>';
                } else {
                    $this->tplVar["alert_msg"] = '<div class="alert alert-danger">' . $formResponse['message'] . '</div>';
                }
            }


            // Get products
            $products = Capsule::table("tblproducts")->where("servertype", "iredmail")->get();
            $this->tplVar["products"] = $products;

            $this->tplFileName = $this->tplVar['tab'] = __FUNCTION__;
            $this->output();
        } catch(Exception $e) {
            logActivity("Error in module Dashboard. Error: ". $e->getMessage());
        }
    }

    /**
     * Loads the assigned Smarty template
     */
    public function output()
    {
        try {
            $smarty = new Smarty();
    
            $smarty->assign('tplVar', $this->tplVar);
            $smarty->assign('LANG', $this->lang);
    
            $smarty->display($this->tplVar['tplDIR'] . $this->tplFileName . '.tpl');

        } catch(Exception $e) {
            logActivity("Error in module output. Error: ". $e->getMessage());
        }
    }
}
