<?php
/**
 * This script have some helpder classes and functions
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */

/**
 * This class have some tester methods etc...
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */
class ActionRuleHelper
{

    private static $_oActionRuleObject;
    private static $_aValuesToApply = [];

    /**
     * The function will set the action rule object
     * 
     * @param ActionRule $oActionRule The object to set
     * 
     * @return void
     */
    public static function setActionRuleObject(ActionRule $oActionRule): void
    {
        self::$_oActionRuleObject = $oActionRule;
    }

    /**
     * This function will return the action rule object
     * 
     * @return ActionRule the action rule object
     */
    public static function getActionRuleObject(): ActionRule
    {
        return self::$_oActionRuleObject;
    }

    /**
     * This function will set the values to apply
     * 
     * @param array $aValuesToApply The values
     * 
     * @return void
     */
    public static function setValuesToApply(array $aValuesToApply): void
    {
        self::$_aValuesToApply = $aValuesToApply;
    }

    /**
     * This function will get the values to apply
     * 
     * @return array Values to apply
     */
    public static function getValuesToApply(): array
    {
        return self::$_aValuesToApply;
    }

    /**
     * This class will return the targeted class
     * 
     * @return string The class name
     */
    public static function getTargetClass(): string
    {
        return self::getActionRuleObject()->Get('target_class');
    }

    /**
     * Will init the class
     * 
     * @param int $iOid The id of ObjectRule 
     * 
     * @return bool True if found, false in other cases
     */
    public static function init(int $iOid) : bool
    {
        $oRule = MetaModel::GetObject('ActionRule',  $iOid);
        if (!is_null($oRule)) {
            self::setActionRuleObject($oRule);
            return true;
        } else {
            return false;
        }
    }

    /** 
     * This function will check if target ckass is fine 
     * 
     * @param string $sHtml Html string to fill to make an html display
     * 
     * @return bool True if ok, false in other cases
     */
    public static function checkObject(string &$sHtml) : bool
    {
        if (MetaModel::IsValidClass(self::getTargetClass())) {
            $sHtml .= self::_makeHtmlTableRow(true, "The class " . self::getTargetClass() . " is valid");
            return true;
        } else {
            $sHtml .= self::_makeHtmlTableRow(false, "The class " . self::getTargetClass() . " is not valid");
            return false;
        }
    }

    /**
     * This function will check if the values to apply is well syntaxed
     * 
     * @return bool True if ok, false in other cases
     */
    public static function checkValueToApply(string &$sHtml): bool
    {
        $aValues = self::getActionRuleObject()->parseValuesToApply(self::getActionRuleObject());
        if ($aValues === false) {
            $sHtml .= self::_makeHtmlTableRow(false, "There is a syntax error in values");
            return false;
        } else {
            self::setValuesToApply($aValues);
            $sHtml .= self::_makeHtmlTableRow(true, "The syntax of values is valid");
            return true;
        }
    }

    /**
     * This function will test all values
     * 
     * @param bool   $bHtml If true, return an html string at the end of the function
     * @param string $sHtml The current html string
     * 
     * @return bool True in case of success, false in other cases with at least 1 error
     */
    public static function checkValues(bool $bHtml, string &$sHtml): bool
    {   
        // Get stimulis list
        $aStimuli = MetaModel::EnumStimuli(self::getTargetClass());
        $aLinkedClasses = MetaModel::GetLinkedSets(self::getTargetClass());
        // loop on all values
        foreach (self::getValuesToApply() as $aTable) {
            switch ($aTable['type']) {
            case 'stimuli':
                // check if the stimuli $sValue can be applied
                if (!isset($aStimuli[$aTable['value']])) {
                    $sHtml .= self::_makeHtmlTableRow(false, "The transition " . $aTable['value'] . " is not valid for the object " . self::getTargetClass());
                    if ($bHtml === false) {
                        return false;
                    }
                } else {
                    $sHtml .= self::_makeHtmlTableRow(true, "The transition " . $aTable['value'] . " is valid for the object " . self::getTargetClass());
                }
                break;
            case 'value':
                // check if the col exist and the value can be
                if (!MetaModel::IsValidAttCode(self::getTargetClass(), $aTable['col'])) {
                    $sHtml .= self::_makeHtmlTableRow(false, "The attribute " . $aTable['col'] . " is not valid for the object " . self::getTargetClass());
                    if ($bHtml === false) {
                        return false;
                    }
                } else {
                    $sHtml .= self::_makeHtmlTableRow(true, "The attribute " . $aTable['col'] . " is valid for the object " . self::getTargetClass());              
                }
                break;
            case 'link':
                if (MetaModel::IsValidAttCode(self::getTargetClass(), $aTable['col'])) {
                    $oAtt = MetaModel::GetAttributeDef(self::getTargetClass(), $aTable['col']);
                    // check if linkset, also, is direct linkset, we should have one value only
                    if (!$oAtt->IsLinkset() 
                        || (!$oAtt->IsIndirect() && count($aTable['value']) !== 1)
                    ) {
                        $sHtml .= self::_makeHtmlTableRow(false, "The attribute " . $aTable['col'] . " is not a valid linkset for the object " . self::getTargetClass());
                        if ($bHtml === false) {
                            return false;
                        }
                    } else {
                        $sHtml .= self::_makeHtmlTableRow(true, "The attribute " . $aTable['col'] . " is a valid linkset for the object " . self::getTargetClass());
                        $bExtKey = false;
                        $sLinkClass = $oAtt->GetLinkedClass();
                        foreach ($aTable['value'] as $sLinkValue) {
                            $aLinkValue = explode('/', $sLinkValue);
                            if (count($aLinkValue) === 2) {
                                if (MetaModel::IsValidAttCode($sLinkClass, $aLinkValue[0])
                                    || (!$oAtt->IsIndirect() && $aLinkValue[0] === 'id')
                                ) {
                                    $sHtml .= self::_makeHtmlTableRow(true, "The value " . $sLinkValue . " is valid for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                                    // Depends on link type, we check the ext key
                                    if ($oAtt->IsIndirect()) { // n:n link
                                        if ($aLinkValue[0] === $oAtt->GetExtKeyToRemote()) {
                                            $sHtml .= self::_makeHtmlTableRow(true, "The external key " . $sLinkValue . " is valid for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                                            $bExtKey = true;
                                        }
                                    } else { // 1:n link
                                        if ($aLinkValue[0] === 'id') {
                                            $sHtml .= self::_makeHtmlTableRow(true, "The external key " . $sLinkValue . " is valid for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                                            $bExtKey = true;
                                        }
                                    }
                                } else {
                                    $sHtml .= self::_makeHtmlTableRow(false, "The value " . $sLinkValue . " is not a valid for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                                    if ($bHtml === false) {
                                        return false;
                                    }
                                }
                            } else {
                                $sHtml .= self::_makeHtmlTableRow(false, "The value " . $sLinkValue . " is not valid for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                                if ($bHtml === false) {
                                    return false;
                                }
                            }
                        }
                    }
                    if ($bExtKey === false) {
                        $sHtml .= self::_makeHtmlTableRow(false, "No ext_key found for linkset " . $aTable['col'] . " for the object " . self::getTargetClass());
                        if ($bHtml === false) {
                            return false;
                        }
                    }                   
                } else {
                    $sHtml .= self::_makeHtmlTableRow(false, "The attribute " . $aTable['col'] . " is not a valid linkset for the object " . self::getTargetClass());
                    if ($bHtml === false) {
                        return false;
                    }
                }
                break;
            default:
                if ($bHtml === false) {
                    return false;
                }
                break;
            }
        }
        // Default behaviour : Every thing is ok, or html is enabled
        return true;
    }

    /**
     * This function will test every params of ActionRule object
     * 
     * @param bool $bHtml If set to true, then return html code instead of boolean
     * 
     * @return mixed True is OK. False in other cases, html code if bHtml is set to true
     */
    public static function checkAll(bool $bHtml = false)
    {
        $sHtml = '';
        $bCheckObj = ActionRuleHelper::checkObject($sHtml);
        if ($bCheckObj === true) {
            $bCheckValueToApply = ActionRuleHelper::checkValueToApply($sHtml);
            $bCheckValue = ActionRuleHelper::checkValues($bHtml, $sHtml);
            $bCheckCondition = ActionRuleHelper::checkCondition($sHtml);
            if ($bHtml === true) {
                return $sHtml;
            } else {
                if ($bCheckValueToApply === true  && $bCheckValue === true && $bCheckCondition === true) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            if ($bHtml === true) {
                return $sHtml;
            } else {
                return false;
            }
        }
    }

    /**
     * This function will try to execute OQL query to check the syntax of condition
     * 
     * @param string $sHtml The current html string
     * 
     * @return bool True if OK, false in other cases
     */
    public static function checkCondition(string &$sHtml): bool
    {
        if (self::getActionRuleObject()->Get('condition') != ''
            && !is_null(self::getActionRuleObject()->Get('condition'))
        ) {
            $sWhere = " WHERE " . self::getActionRuleObject()->Get('condition');
        } else {
            $sWhere = "";
        }
        try {
            // try to exec query, if not good, it will trigger exception
            new DBObjectSet(DBObjectSearch::FromOQL("SELECT " . self::getTargetClass() . $sWhere));
        } catch(OQLException $e) {
            $sHtml .= self::_makeHtmlTableRow(false, "There is a syntax error in condition"); 
            return false;
        }
        // nothing to do, so it is ok by default
        $sHtml .= self::_makeHtmlTableRow(true, "The syntax of condition is valid");
        return true;        
    }
    

    /**
     * This function will apply the condition to the object
     * 
     * @param stdClass $oObject Current object
     * 
     * @return bool true is no problems, false in other cases
     */
    public static function checkConditionToApply($oObject): bool
    {
        if (self::getActionRuleObject()->Get('condition') != '' && !is_null(self::getActionRuleObject()->Get('condition'))) {
            $sWhere = " (" . self::getActionRuleObject()->Get('condition') . ") AND ";
        }
        // @TODO : the id column name is hard coded here. Find the way to not.
        $sWhere .= " id=" . $oObject->GetKey();
        // try to exec query, if not good, it will trigger exception
        $oSetObject = new DBObjectSet(DBObjectSearch::FromOQL("SELECT " . self::getTargetClass() . " WHERE " . $sWhere));
        if ($oSetObject->Count() === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function will try to exec all actions. All action will be executed in given order
     * 
     * @param stdClass $oObject Current object
     * 
     * @return bool true is no problems, false in other cases
     */
    public static function execAll($oObject): void
    {
        // at this steps, values has been checked already
        // Check if condition is
        foreach (self::getValuesToApply() as $sK => $aTable) {
            switch($aTable['type']) {
            case 'stimuli':
                $oObject->ApplyStimulus($aTable['value']);
                break;
            case 'value':
                $oObject->Set($aTable['col'], $aTable['value']);
                break;
            case 'link':
                $oAtt = MetaModel::GetAttributeDef(self::getTargetClass(), $aTable['col']);
                $sLinkClass = $oAtt->GetLinkedClass();
                if ($oAtt->IsIndirect()) {
                    $oLinkedSet = new $sLinkClass;
                    $oSet = $oObject->Get($aTable['col']);
                }
                foreach ($aTable['value'] as $sLinkValue) {
                    $aLinkValue = explode('/', $sLinkValue);
                    if ($oAtt->IsIndirect()) {
                        $oLinkedSet->Set($aLinkValue[0], $aLinkValue[1]);
                    } else {
                        // only one value here
                        // get link object, set new value for
                        $oLnkObj = MetaModel::GetObject($sLinkClass, $aLinkValue[1], false); // false => not sure it exists
                        if (is_object($oLnkObj)) {
                            $oLnkObj->Set($oAtt->GetExtKeyToMe(), $oObject->GetKey());
                            $oLnkObj->DBUpdate();
                        }
                    }
                }
                if ($oAtt->IsIndirect()) {
                    $oSet->AddObject($oLinkedSet);
                    $oObject->Set($aTable['col'], $oSet);
                }
                break;
            }
        }
        // Update values
        $oObject->DBUpdate();
    }

    /**
     * This function will make html table row
     * 
     * @param bool   $bValid Tells if the test is valid
     * @param string $sMsg   The message to display
     * 
     * @return string The html message
     */
    private static function _makeHtmlTableRow(bool $bValid, string $sMsg) : string
    {
        if ($bValid === true) {
            $sCell1 = "<td class='table-success'>OK</td>";
        } else {
            $sCell1 = "<td class='table-danger'>NOK</td>";
        }

        return "<tr>" . $sCell1 . "<td>" . $sMsg . "</td>" . "</tr>";
    }
}