<?php
/**
 * This script will test data in the rule
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */

// Include iTop necessary files
if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
require_once __DIR__ . '/../../approot.inc.php';
require_once APPROOT . '/application/application.inc.php';
require_once APPROOT . '/application/webpage.class.inc.php';
require_once APPROOT . '/application/ajaxwebpage.class.inc.php';
require_once APPROOT . 'core/mutex.class.inc.php';
require_once APPROOT . '/application/loginwebpage.class.inc.php';
require_once APPROOT . '/application/startup.inc.php';
// Include Helper classes and functions
require_once APPROOT . '/env-production/dlp-global-rules/includes/DlpGlobalRulesHelper.php';

class DlpGlobalRulesTester
{
    public static function test($sAction)
    {
        $sHtml = '';
        switch($sAction) {
        case 'tester': // This is for testing datas
            if (isset($_GET['oid'])) {
                // we should check for rights before, even if it is harmless
                if (DlpGlobalRulesHelper::init($_GET['oid'])) {
                    $sHtml .= DlpGlobalRulesHelper::checkAll(true);
                } else {
                    $sHtml .= '<tr><td class="table-danger">NOK</td><td></td>ActionRule object not found</tr>';
                }
                // echo html code and exit;
                echo "<html><head>
                <link rel='stylesheet' href='" . utils::GetAbsoluteUrlAppRoot() . "env-production/dlp-global-rules/css/bootstrap.min.css'>
                </head><body><div class='container-fluid'><table class='table'>";
                echo $sHtml;
                echo "</table></div></html>";
                exit;        
            } else {
                throw new Exception('No oid');
            }
            break;
        default:
            throw new Exception('Invalid action');
            break;
        }
    }
}

DlpGlobalRulesTester::test((isset($_GET['action']) ? $_GET['action'] : ''));
