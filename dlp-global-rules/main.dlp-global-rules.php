<?php
/**
 * This file implement classes to extends some behaviours
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */

/**
 * This class implement the method to display a tab in objects
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */
class TriggerActionTab implements iApplicationUIExtension
{
    public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
    {
        
    }

    public function EnumAllowedActions(DBObjectSet $oSet)
    {
        return array();
    }

    public function EnumUsedAttributes($oObject)
    {
        return array();
    }

    public function OnFormSubmit($oObject, $sFormPrefix = '')
    {
    }

    public function OnFormCancel($sTempId)
    {
    }

    public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
    {
        if (!$bEditMode) {
            $iId = $oObject->GetKey();
            $sClassName = get_class($oObject);
            $aTriggers = DBObjectSearch::FromOQL("SELECT ActionRuleTrigger AS art WHERE art.obj_id = $iId AND art.class_name='$sClassName'");
            $oTriggersSet = new DBObjectSet($aTriggers);
            if ($oTriggersSet->Count() > 0) {
                $oPage->SetCurrentTab(Dict::S('UI:ActionRuleTab') . ' (' . $oTriggersSet->Count() . ')');
                $oPage->p(MetaModel::GetClassIcon('ActionRuleTrigger', true).'&nbsp;' . MetaModel::GetName('ActionRuleTrigger'));
                $oBlock = new DisplayBlock($aTriggers, 'list', false);
                $oBlock->Display($oPage, 'trigger_ActionRuleTrigger', array('menu' => false));
            }
        }
    }

    public function GetIcon($oObject)
    {
        return '';
    }

    public function GetHilightClass($oObject)
    {
        return HILIGHT_CLASS_NONE;
    }
}

    
/**
 * This class implement the trigger action
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */
class ActionRuleActions implements iApplicationObjectExtension
{
    private static $_aInsertedObject = [];
    // Not used
    public function OnIsModified($oObject){}
    // Not used
    public function OnCheckToWrite($oObject){}
    // Not used
    public function OnCheckToDelete($oObject){}
    // Not used
    public function OnDBDelete($oObject, $oChange = null){}
    // Not used
    public function OnDBUpdate($oObject, $oChange = null)
    {
        if (isset(self::$_aInsertedObject[get_class($oObject)])
            && in_array($oObject->GetKey(), self::$_aInsertedObject[get_class($oObject)])
        ) {
            // Do not trigger any update here
            // it comes from an insert
            // Maybe later we could make this configurable
        } else {
            // not the same object
            $this->_triggerActions($oObject, "update");
        }
    }

    /**
     * Triggered on insert
     */
    public function OnDBInsert($oObject, $oChange = null)
    {
        // Make a reference of inserted object
        if (!isset(self::$_aInsertedObject[get_class($oObject)])) {
            self::$_aInsertedObject[get_class($oObject)] = [];
        }
        self::$_aInsertedObject[get_class($oObject)][] = $oObject->GetKey();
        // then, trigger actions for create
        $this->_triggerActions($oObject, "create");
    }

    /**
     * This function will search into action rules and trigger them
     * 
     * @return void
     */
    private function _triggerActions($oObj, string $sTriggeredAction)
    {
        if (is_object($oObj)) {
            $oSetActionRule = new DBObjectSet(
                DBObjectSearch::FromOQL(
                    "SELECT ActionRule WHERE trigger_type=:trigger_type AND status='enabled' AND target_class=:target_class"
                ), [], ['trigger_type' => $sTriggeredAction, 'target_class' => get_class($oObj)]
            );
            while ($oActionRule = $oSetActionRule->Fetch()) {
                ActionRuleHelper::init($oActionRule->GetKey());
                if (ActionRuleHelper::checkAll() && ActionRuleHelper::checkConditionToApply($oObj)) {
                    // insert a new trigger : ActionRuleTrigger
                    $oActionRuleTrigger = MetaModel::NewObject('ActionRuleTrigger');
                    $oActionRuleTrigger->Set('obj_id', $oObj->GetKey());
                    $oActionRuleTrigger->Set('class_name', get_class($oObj));
                    $oActionRuleTrigger->Set('actionrule_id', $oActionRule->GetKey());
                    $oActionRuleTrigger->Set('date', date('Y-m-d H:i:s'));
                    $oActionRuleTrigger->Set('values_applied', $oActionRule->Get('values_to_apply'));
                    $oActionRuleTrigger->DBInsert();
                    // Exec Jobs
                    ActionRuleHelper::execAll($oObj);
                }
            }
        }
    }
}