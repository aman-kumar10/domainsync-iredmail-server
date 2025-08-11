<?php

namespace WHMCS\Module\Addon\DomainSync\Admin;

use Exception;
use WHMCS\Module\Addon\DomainSync\Helper;
use WHMCS\Database\Capsule;

require "../includes/customfieldfunctions.php";

use Smarty;

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

            if (isset($_POST['data_action']) && $_POST['data_action'] === "getCustomfields") {
                $customFields = getCustomFields('product', $_POST['product_id'], "");

                $html = '';
                    foreach ($customFields as $field) {
                        $html .= '
                        <div class="form-group">
                            <label class="col-lg-6 col-sm-6 control-label">' . htmlspecialchars($field['name']) . '</label>
                            <div class="col-lg-6 col-sm-6">
                                <input type="text" 
                                    name="customfield[' . intval($field['id']) . ']" 
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

            // Get products
            $products = Capsule::table("tblproducts")->where("servertype", "iredmail")->get();
            $this->tplVar["products"] = $products;

            // $data = getCustomFields('product',26,"");
            // echo "<pre>"; print_r($data); die;

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
