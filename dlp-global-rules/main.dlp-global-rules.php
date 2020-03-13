<?php
/**
 * This is the task for background
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */

class ActionRuleActions implements iApplicationObjectExtension {
    // Not used
    public function OnIsModified($oObject){}
    // Not used
    public function OnCheckToWrite($oObject){}
    // Not used
    public function OnCheckToDelete($oObject){}
    // Not used
    public function OnDBDelete($oObject, $oChange = null){}

    /**
     * Triggered on update
     */
    public function OnDBUpdate($oObject, $oChange = null)
    {
        // not enable FTM
        //$this->_triggerActions($oObject, "update");
    }

    /**
     * Triggered on insert
     */
    public function OnDBInsert($oObject, $oChange = null)
    {
        $this->_triggerActions($oObject, "create");
    }

    /**
     * This function will search into action rules and trigger them
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
                    ActionRuleHelper::execAll($oObj);
                }
            }
        }
    }
}