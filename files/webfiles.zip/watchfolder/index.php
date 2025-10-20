<?php
require '../../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');
if(!isset($packages['watchfolder'])){
    html::loadurl("/");
}

html::fullhead("watchfolder", "Folder watchers");



html::fullend();