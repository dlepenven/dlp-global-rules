<?php
/**
 * Extension definition
 * 
 * @author    David LE PENVEN <dlepenven@msn.com>
 * @copyright 2020 David LE PENVEN
 * @license   AGPL http://opensource.org/licenses/AGPL-3.0
 */

SetupWebPage::AddModule(
    __FILE__,
    'dlp-global-rules/2.0.0',
    array(
        'label' => 'Create rules on objects to change values and apply stimulus',
        'category' => 'business',
        'dependencies' => array(
            // Add needed dependencies to have it in last in loading order
            'itop-config-mgmt/2.4.0',
        ),
        'mandatory' => false,
        'visible' => true,
        'datamodel' => array(
            'model.dlp-global-rules.php',
            'loader.php'
        ),
        'webservice' => array(),
        'data.struct' => array(),
        'data.sample' => array(),
        'doc.manual_setup' => '',
        'doc.more_information' => '',
        'settings' => array(
            'show_tab_on_object' => true,
            'value_separator' => '=',
            'type_separator' => ':',
            'link_value_separator' => '|',
            'itop_portal_modules' => [
                'itop-portal-base',
                'itop-portal',
            ]
        ),
    )
);
