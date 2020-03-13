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
     * @return bool True if ok, false in other cases
     */
    public static function checkObject() : bool
    {
        if (MetaModel::IsValidClass(self::getTargetClass())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function will check if the values to apply is well syntaxed
     * 
     * @return bool True if ok, false in other cases
     */
    public static function checkValueToApply(): bool
    {
        $aValues = self::getActionRuleObject()->parseValuesToApply(self::getActionRuleObject());
        if ($aValues !== false) {
            self::setValuesToApply($aValues);
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function will test all values
     * 
     * @return bool True in case of success, false in other cases with at least 1 error
     */
    public static function checkValues(): bool
    {
        foreach (self::getValuesToApply() as $sK => $sValue) {
            if ($sK === 'stimuli') {
                // check if the stimuli $sValue can be applied
                $aStimuli = MetaModel::EnumStimuli(self::getTargetClass());
                if (!isset($aStimuli[$sValue])) {
                    return false;
                }
            } else {
                // check if the col exist and the value can be
                if (!MetaModel::IsValidAttCode(self::getTargetClass(), $sK)) {
                    return false;
                }
            }
        }
        // default, when all is OK
        return true;
    }

    /**
     * This function will test every params of ActionRule object
     * 
     * @return bool True is OK. False in other cases
     */
    public static function checkAll(): bool
    {
        if (!ActionRuleHelper::checkObject()
            || !ActionRuleHelper::checkValueToApply()
            || ActionRuleHelper::checkValues() === false
            || !ActionRuleHelper::checkCondition()
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * This function will try to execute OQL query to check the syntax of condition
     * 
     * @return bool True if OK, false in other cases
     */
    public static function checkCondition(): bool
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
            return false;
        }
        // nothing to do, so it is ok by default
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
        // Check if condition is
        foreach (self::getValuesToApply() as $sK => $sValue) {
            if ($sK === 'stimuli') {
                // check if the stimuli $sValue can be applied
                $aStimuli = MetaModel::EnumStimuli(self::getTargetClass());
                if (isset($aStimuli[$sValue])) {
                    $oObject->ApplyStimulus($sValue);
                } else {
                    break;
                }
            } else {
                // check if the col exist and the value can be
                if (MetaModel::IsValidAttCode(self::getTargetClass(), $sK)) {
                    $oObject->Set($sK, $sValue);
                } else {
                break;
                }
            }
        }
        // This could be a problem because it will trigger 'normal' action rules
        $oObject->DBUpdate();
    }
}