<?php
require '../../localfiles/global.php';

if(isset($_GET['getRunnings'])){
    $runningSites = website_website::sendCommand("getStatuses");
    
    if(!is_array($runningSites)){
        echo json_encode(['servers'=>[],'sites'=>[]]);
    }

    echo json_encode($runningSites['data']);

    exit;
}
elseif(isset($_GET['command'])){
    if($_GET['command'] === "startSite" || $_GET['command'] === "stopSite"){
        if(!isset($_GET['site'])){
            exit;
        }
        $result = website_website::sendCommand($_GET['command'], [intval($_GET['site'])]);
    }
    elseif($_GET['command'] === "startServer" || $_GET['command'] === "stopServer"){
        if(!isset($_GET['site']) || !isset($_GET['server'])){
            exit;
        }
        $result = website_website::sendCommand($_GET['command'], [intval($_GET['site'])], [intval($_GET['server'])]);
    }
    else{
        exit;
    }

    if(!is_array($result)){
        exit;
    }

    if($result['success']){
        echo "OK";
        exit;
    }

    if(!empty($result['error'])){
        echo "weberr: " . $result['error'];
    }

    exit;
}

html::fullhead("website","Websites", "style.css");

$sites = runfunction('website::listSites();');

foreach($sites as $siteId => $siteData){
    echo '
        <div class="site" id="site_' . $siteId . '">
            <div class="name">
                <span>' . $siteData['name'] . '</span><span style="font-size:18px; position:relative; top:-2px;">(' . $siteId . ')</span>
                <img class="stop-icon" onclick="command(\'stopSite\',\'' . $siteId . '\');" src="' . $filesUrl . '/img/stop-circle.svg">
                <img class="start-icon" onclick="command(\'startSite\',\'' . $siteId . '\');" src="' . $filesUrl . '/img/play-circle.svg">
            </div>
            <div class="servers">
                ';
                $newSiteServers = $siteData['servers'];
                if($siteData['logsCollector']){
                    $newSiteServers["-1"] = ['type'=>'logCollector'];
                }

                $count = count($newSiteServers);
                $counter = 0;
                foreach($newSiteServers as $serverNumber => $server){
                    $counter++;
                    $type = $server['type'];

                    echo '
                        <div id="server_' . $siteId . '_' . $serverNumber . '">
                            <span>' . ucfirst($type) . ($server['type'] !== "logCollector" ? ' server ' . $server[$type . "Index"] : '') . '</span>
                            <img class="stop-icon" onclick="command(\'stopServer\',\'' . $siteId . '\',\'' . $serverNumber . '\');" src="' . $filesUrl . '/img/stop-circle.svg">
                            <img class="start-icon" onclick="command(\'startServer\',\'' . $siteId . '\',\'' . $serverNumber . '\');" src="' . $filesUrl . '/img/play-circle.svg">
                        </div>
                    ';

                    if($counter !== $count){
                        echo '<div style="background-color:grey; width:100%; height:1px"></div>';
                    }
                }

                echo '
            </div>
        </div>
    ';
}

echo '
    <br>
    <h6>Communicator is running for this page to be visible.</h6>
';

html::fullend('script.js');

?>