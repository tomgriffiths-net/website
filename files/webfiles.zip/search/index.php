<?php
require '../../localfiles/global.php';

if(isset($_GET['eventSearch'])){
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    if(!isset($_GET['q'])){
        sendEvent("exit");
        exit;
    }
    $q = strtolower(trim($_GET['q']));
    $sentUrls = [];//used in sendCard
    if(empty($q)){
        sendCard("Empty query", "Please enter a non empty query");
        sendEvent("exit");
        exit;
    }

    $packages = runfunction('pkgmgr::getLoadedPackages()');

    //Search pages
    foreach([
        '/admin/navbars/list/' => 'Edit Navbars',
        '/apachemgr/' => 'Apache server manager',
        '/conductor_server/' => 'Conductor Server',
        '/hyper_v/' => 'Hyper-V manager',
        '/mcservers/list/' => 'Minecraft servers list',
        '/mcservers/manager/create/' => 'Create Minecraft server',
        '/pkgmgr/' => 'PHP-CLI packages',
        '/watchfolder/' => 'Watchfolder',
        '/website/' => 'Websites manager'
    ] as $link => $name){
        $basename = substr($link, 1, strpos($link, "/", 1) -1);
        if(strpos(strtolower($name), $q) !== false || strpos($basename, $q) !== false){
            sendCard($name, "Web page for " . $basename, $link);
        }
    }

    if(isset($packages['apachemgr'])){
        $servers = runfunction('apachemgr::listServers()');
        if(is_array($servers)){
            foreach($servers as $serverId => $serverData){
                if($q == $serverId){
                    showApacheServer($serverId, $serverData);
                }

                if(isset($serverData['name'])){
                    if(strpos(strtolower($serverData['name']), $q) !== false){
                        showApacheServer($serverId, $serverData);
                    }
                }
            }
        }
    }
    if(isset($packages['conductor_server'])){
        foreach(['successful jobs', 'failed jobs', 'processing jobs', 'pending jobs'] as $thing){
            if(strpos($thing, $q) !== false){
                sendCard(ucfirst($thing), ucfirst($thing) . " in conductor server.", "/conductor_server/", false, true);
            }
        }
    }
    if(isset($packages['mcservers'])){
        $serverIds = runfunction('mcservers::allServers()');

        if(is_array($serverIds)){
            foreach($serverIds as $serverId){
                if(strpos($q, $serverId) !== false){
                    bringToTop($serverIds, $serverId);
                }
            }

            foreach($serverIds as $serverId){
                $serverData = runfunction('mcservers::serverInfo("' . $serverId . '");');

                if(!is_array($serverData)){
                    if(strpos($q, $serverId) !== false){
                        sendCard("Minecraft server " . $serverId, "Unable to get extra data", "/mcservers/manager/?id=" . $serverId);
                    }
                    continue;
                }

                if(strpos($q, $serverId) !== false){
                    showMCServer($serverId, $serverData);
                    continue;
                }

                if(isset($serverData['name']) && strpos(strtolower($serverData['name']), $q) !== false){
                    showMCServer($serverId, $serverData);
                    continue;
                }

                if(isset($serverData['version']['version']) && strpos($serverData['version']['version'], $q) !== false){
                    showMCServer($serverId, $serverData);
                    continue;
                }

                if(isset($serverData['version']['type']) && strpos($serverData['version']['type'], $q) !== false){
                    showMCServer($serverId, $serverData);
                    continue;
                }
            }
        }
    }
    if(isset($packages['watchfolder'])){
        $watchers = runfunction('watchfolder::getWatcherSettings()');

        if(is_array($watchers)){
            foreach($watchers as $watcherName => $watcherData){
                if(!is_string($watcherName) || !is_array($watcherData)){
                    continue;
                }

                if(strpos($watcherName, $q) !== false){
                    showWatchfolder($watcherName, $watcherData);
                    continue;
                }

                if(isInArrayValue('directory', $watcherData, $q)){
                    showWatchfolder($watcherName, $watcherData);
                    continue;
                }

                //Needs to be at end for break to work effectively
                if(isset($watcherData['fileTypes']) && is_array($watcherData['fileTypes'])){
                    if(in_array($q, $watcherData['fileTypes'], true)){
                        showWatchfolder($watcherName, $watcherData);
                        break;
                    }
                }
            }
        }
    }
    if(isset($packages['website'])){
        $sites = runfunction('website::listSites()');

        if(is_array($sites)){
            foreach($sites as $siteId => $siteData){
                if(!is_array($siteData)){
                    continue;
                }
                
                if(isInArrayValue('name', $siteData, $q)){
                    $name = "Website " . $siteId;
                    if(isset($siteData['name']) && is_string($siteData['name'])){
                        $name = $siteData['name'];
                    }
                    sendCard($name, "Website " . $siteId, "/website/", false, true);
                }
            }
        }
    }

    //Hyper-v lookup is slow so is at the end
    if(isset($packages['hyper_v'])){
        $vms = runfunction('hyper_v::listVms()');
        if(is_array($vms)){
            foreach($vms as $vm){
                if(isset($vm['name']) && is_string($vm['name'])){
                    if(strpos(strtolower($vm['name']), $q) !== false){
                        $text = "Virtual Machine";

                        foreach(['state', 'status'] as $thing){
                            if(isset($vm[$thing]) && is_string($vm[$thing])){
                                $text .= ", " . $vm[$thing];
                            }
                        }

                        sendCard($vm['name'], $text, "/hyper_v/", false, true);
                    }
                }
            }
        }
    }

    //Search pkgmgr packages
    foreach($packages as $packageId => $packageVersion){
        if(strpos($packageId, $q) !== false){
            $packageInfo = runfunction('pkgmgr::getPackageInfo("' . $packageId . '", false)');
            if(is_array($packageInfo)){
                sendCard($packageInfo['name'], rtrim($packageId . ", v" . $packageVersion . ", by " . $packageInfo['author'] . ", " . $packageInfo['description'], ", "), "/pkgmgr/", false, true);
            }
            else{
                sendCard($packageId, "Version " . $packageVersion . ", unable to get additional info.", "/pkgmgr/", false, true);
            }
        }
    }

    if(empty($sentUrls)){
        sendCard("No results found", "No results found for query \"" . $q . "\"");
    }

    sendEvent("exit");
    exit;
}
function isInArrayValue(string $key, array $data, string $q):bool{
    if(isset($data[$key]) && is_string($data[$key])){
        if(strpos($data[$key], $q) !== false){
            return true;
        }
    }
    return false;
}
function showMCServer(string $id, array $data):void{
    $name = "Minecraft server " . $id;
    $text = "";
    $url = "/mcservers/manager/?id=" . $id;
    
    if(isset($data['name']) && is_string($data['name'])){
        $name = $data['name'];
    }

    if(isset($data['version']['type']) && is_string($data['version']['type'])){
        $text .= ucfirst($data['version']['type']) . " server, ";
    }

    if(isset($data['version']['version']) && is_string($data['version']['version'])){
        $text .= "Minecraft " . ucfirst($data['version']['version']) . ", ";
    }

    if(isset($data['run']['max_ram_mb']) && is_int($data['run']['max_ram_mb'])){
        $text .= ucfirst($data['run']['max_ram_mb']) . "MB Ram, ";
    }

    foreach(["Mods", "Plugins"] as $thing){
        if(isset($data['capabilities']['allow' . $thing]) && $data['capabilities']['allow' . $thing]){
            $text .= $thing . " enabled, ";
        }
    }

    $text = substr($text, 0, -2);

    sendCard($name, $text, $url);
}
function showApacheServer(string $id, array $data):void{
    $name = "Apache server " . $id;
    $text = "";

    if(isset($data['name']) && is_string($data['name'])){
        $name = $data['name'];
        $text = "Apache server " . $id;
    }

    sendCard($name, $text, "/apachemgr/", false, true);
}
function showWatchfolder(string $id, array $data):void{
    $text = "";

    if(isset($data['active']) && $data['active']){
        $text .= "Active";
    }
    else{
        $text .= "Inactive";
    }
    if(isset($data['directory']) && is_string($data['directory'])){
        $text .= ", Scanning directory " . $data['directory'];
    }
    if(isset($data['interval']) && is_int($data['interval'])){
        $text .= ", Every " . $data['interval'] . " seconds";
    }
    if(isset($data['fileTypes']) && is_array($data['fileTypes'])){
        $text .= ", For file type" . ((count($data['fileTypes']) > 1) ? "s" : "") . " ";
        foreach($data['fileTypes'] as $type){
            if(is_string($type)){
                $text .= $type . ", ";
            }
        }
        $text = substr($text, 0, -2);
    }

    sendCard($id, $text, "/watchfolder/", false, true);
}
function bringToTop(array &$array, mixed $value):void{
    $key = array_search($value, $array);
    if($key !== false){
        unset($array[$key]);
        array_unshift($array, $value);
    }
}
function sendCard(string $title, string $text, string $url="", bool $useButton=false, bool $ignoreSentUrls=false):void{
    global $sentUrls;

    if(!$ignoreSentUrls && in_array($url, $sentUrls)){
        return;
    }

    $sentUrls[] = $url;

    $urlEmpty = empty($url);
    $html = '<div class="card mb-3">';
    
    if(!$urlEmpty){
        $html .= '<div class="card-header">' . substr($url, 1, strpos($url, "/", 1) -1) . '</div>';
    }
    
    $html .= '<div class="card-body">';

    if(!$useButton && !$urlEmpty){
        $html .= '<a href="' . $url . '">';
    }

    $html .= '<h5 class="card-title">' . $title . '</h5>';

    if(!$useButton && !$urlEmpty){
        $html .= '</a>';
    }

    $html .= '<p class="card-text">' . $text . '</p>';

    if(!$urlEmpty && $useButton){
        $html .= '<a href="' . $url . '" class="btn btn-primary">Go there</a>';
    }

    $html .= '</div></div>';

    sendEvent(html::encodeInString($html));
}
function sendEvent(string $data):void{
    echo "data: " . $data . "\n\n";
    ob_flush();
    flush();
}

html::fullhead("search", "Search");

?>

<div class="container my-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <form class="d-flex" method="get">
                <input class="form-control form-control-md me-2" type="search" name="q" placeholder="Search" aria-label="Search"<?php echo (isset($_GET['q']) ? ' value="' . $_GET['q'] . '"' : '');?>>
                <button class="btn btn-success btn-md" type="submit">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container my-4">
    <div class="row justify-content-center">
        <div id="loaderContainer"></div>
        <div class="col-md-10 col-lg-8" id="results"></div>
    </div>
</div>

<?php

if(isset($_GET['q'])){
    echo '
        <script>
            const events = new EventSource("?eventSearch=1&q=' . preg_replace('/[^a-zA-Z0-9 _.-]/', '', $_GET['q']) . '");
            const results = document.getElementById("results");
            const loaderContainer = document.getElementById("loaderContainer");

            loaderContainer.innerHTML = \'<div class="mb-3"><div class="loadingAnimation"></div></div>\';

            events.onmessage = (event) => {
                if(event.data == "exit"){
                    events.close();
                    loaderContainer.innerHTML = "";
                    return;
                }
                results.innerHTML += decodeInString(event.data);
            };

        </script>
    ';
}

html::fullend();