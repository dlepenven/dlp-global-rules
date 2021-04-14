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
class DlpGlobalRulesUIExtension implements iApplicationUIExtension
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
        if (!$bEditMode
            && MetaModel::GetModuleSetting('dlp-global-rules', 'show_tab_on_object', true)
        ) {
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