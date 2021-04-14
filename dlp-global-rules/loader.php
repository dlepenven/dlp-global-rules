<?php
/**
 * Simple file loader
 */
// load every thing from includes dir
foreach (glob(dirname(__FILE__).'/includes/*.php') as $filename) {
    include_once $filename;
}