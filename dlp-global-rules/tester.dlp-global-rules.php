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
require_once APPROOT . '/env-production/dlp-global-rules/helper.dlp-global-rules.php';

if (isset($_GET['action'])) {
    $sAction = $_GET['action'];
} else {
    // default action when called without parameter
    throw new Exception('Ne actions');
}

$sHtml = '';
switch($sAction) {
case 'tester': // This is for testing datas
    if (isset($_GET['oid'])) {
        if (ActionRuleHelper::init($_GET['oid'])) {
            if (ActionRuleHelper::checkObject()) {
                $sHtml .= "<tr><td class='table-success'>OK</td><td>The class " . ActionRuleHelper::getTargetClass() . " is valid</td></tr>";
                $sClass = ActionRuleHelper::getTargetClass();
                $oClass = new $sClass;
                if (!ActionRuleHelper::checkValueToApply()) {
                    // Syntax error
                    $sHtml .= "<tr><td class='table-danger'>NOK</td><td>There is a syntax error in values</td></tr>";
                } else {
                    $sHtml .= "<tr><td class='table-success'>OK</td><td>The syntax of values is valid</td></tr>";
                    if (!ActionRuleHelper::checkCondition()) {
                        // condition is not good
                        $sHtml .= "<tr><td class='table-danger'>NOK</td><td>There is a syntax error in condition</td></tr>";
                    } else {
                        $sHtml .= "<tr><td class='table-success'>OK</td><td>The syntax of condition is valid</td></tr>";
                        foreach (ActionRuleHelper::getValuesToApply() as $sK => $sValue) {
                            if ($sK === 'stimuli') {
                                // check if the stimuli $sValue can be applied
                                $aStimuli = MetaModel::EnumStimuli($sClass);
                                if (!isset($aStimuli[$sValue])) {
                                    $sHtml .= "<tr><td class='table-danger'>NOK</td><td>The transition " . $sValue . " is not valid for the object " . $sClass . "</td></tr>";
                                } else {
                                    $sHtml .= "<tr><td class='table-success'>OK</td><td>The transition " . $sValue . " is valid for the object " . $sClass . "</td></tr>";
                                }
                            } else {
                                // check if the col exist and the value can be
                                if (!MetaModel::IsValidAttCode($sClass, $sK)) {
                                    $sHtml .= "<tr><td class='table-danger'>NOK</td><td>The attribute " . $sK . " is not valid for the object " . $sClass . "</td></tr>";
                                } else {
                                    $sHtml .= "<tr><td class='table-success'>OK</td><td>The attribute " . $sK . " is valid for the object " . $sClass . "</td></tr>";
                                    // check if the value is OK to apply
                                    // How to check this?
                                }
                            }
                        }
                    }
                }
            } else {
                $sHtml .= "<tr><td class='table-danger'>NOK</td><td>The class " . $sClass . " is not valid</td></tr>";
            }
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