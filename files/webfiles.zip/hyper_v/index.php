<?php
require '../../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');
if(!isset($packages['watchfolder'])){
    html::loadurl("/");
}

html::fullhead("hyper_v", "Virtual Machines");



html::fullend();