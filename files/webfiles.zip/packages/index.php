<?php
require '../../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');

html::fullhead("packages","Packages");

echo '<pre style="color:white;">' . json_encode($packages, JSON_PRETTY_PRINT) . '</pre>';

html::fullend();

?>