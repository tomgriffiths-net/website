<?php
require '../localfiles/global.php';

$packages = runfunction('$GLOBALS[\'packages\']');

html::fullhead("main","Home", 'style.css');

echo '<div style="width:100%; height:fit-content; padding:10px; background-color:#404040; display:flex;">';
echo '
    <div class="topsquare" onclick="window.location.href=\'/packages/\';">
        <span class="topsqtitle">' . count($packages) . '</span>
        <br>
        <span>Packages installed</span>
        <br>
        <span style="color:#777;">(pkgmgr)</span>
    </div>
';

if(isset($packages['mcservers'])){
    $mcservers = runfunction('mcservers::allServers()');
    if(is_array($mcservers)){
        echo '
            <div class="topsquare" onclick="window.location.href=\'/mcservers/list/\';">
                <span class="topsqtitle">' . count($mcservers) . '</span>
                <br>
                <span>Minecraft servers</span>
                <br>
                <span style="color:#777;">(mcservers)</span>
            </div>
        ';
    }
}
if(isset($packages['conductor_server'])){
    $jobs = runfunction('conductor_server::numberOfJobs()');
    if(is_int($jobs)){
        echo '
            <div class="topsquare" onclick="window.location.href=\'/conductor_server/\';">
                <span class="topsqtitle">' . $jobs . '</span>
                <br>
                <span>Queued jobs</span>
                <br>
                <span style="color:#777;">(conductor_server)</span>
            </div>
        ';
    }
}
if(isset($packages['hyper_v'])){
    $vms = runfunction('hyper_v::listVms()');
    if(is_array($vms)){
        echo '
            <div class="topsquare" onclick="window.location.href=\'/hyper_v/\';">
                <span class="topsqtitle">' . count($vms) . '</span>
                <br>
                <span>Virtual machines</span>
                <br>
                <span style="color:#777;">(hyper_v)</span>
            </div>
        ';
    }
}
if(isset($packages['website'])){
    $sites = runfunction('website::numberOfSites()');
    if(is_int($sites)){
        echo '
            <div class="topsquare" onclick="window.location.href=\'/website/\';">
                <span class="topsqtitle">' . $sites . '</span>
                <br>
                <span>Websites</span>
                <br>
                <span style="color:#777;">(website)</span>
            </div>
        ';
    }
}

echo '</div>';

html::fullend();