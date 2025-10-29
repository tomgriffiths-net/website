<?php
require '../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');

html::fullhead("main","Home", 'style.css');

echo '<div class="container" style="margin-top:30px;"><div class="row g-4 justify-content-center">';

$statCards = [
    "count(apachemgr::listServers())"      => "Apache servers",
    "conductor_server::numberOfJobs()"     => "Pending jobs",
    "count(hyper_v::listVms())"            => "Virtual machines",
    "count(mcservers::allServers())"       => "Minecraft servers",
    "watchfolder::getActiveWatcherCount()" => "Active watchers",
    "website::numberOfSites()"             => "Websites",
    "pkgmgr-" . count($packages)           => "Packages installed"
];

foreach($statCards as $function => $label){
    if(strpos($function, ":")){
        $offset = strpos(substr($function,0,7), "(");
        if(is_int($offset)){$offset++;}

        $package = substr($function, $offset, strpos($function, ":") - $offset);
        if(!isset($packages[$package])){
            continue;
        }
        $value = (int) runfunction($function);
    }
    else{
        $dashPos = strpos($function, "-");

        $package = substr($function, 0, $dashPos);
        $value = (int) substr($function, $dashPos +1);
    }

    echo '
        <div class="col-12 col-md-4 col-lg-2" onclick="window.location.href=\'/' . $package . '/\'">
            <div class="card-stat">
                <div class="number">' . $value . '</div>
                <div class="label">' . $label . '</div>
                <div class="small opacity-50">(' . $package . ')</div>
            </div>
        </div>
    ';
}

echo '</div></div>';

html::fullend();