<?php
require '../../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');

html::fullhead("pkgmgr","Packages");

echo '<span>';

ksort($packages);

foreach($packages as $packageId => $packageVersion){
    echo $packageId . " v" . $packageVersion . "<br>";
}

echo '</span>';

html::fullend();

?>