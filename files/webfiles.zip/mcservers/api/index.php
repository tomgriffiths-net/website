<?php
$loginRequired = true;
require '../../../localfiles/global.php';

$f = false;
if(isset($_GET["function"])){
    $f = $_GET["function"];
}
if($f === false){
    echo "No function specified";
    exit;
}

$id = false;
if(isset($_GET["id"])){
    if(website_mcservers::validateId($_GET['id'])){
        $id = $_GET['id'];
    }
}
if(!isset($_GET['noid'])){
    if($id === false){
        echo "No server id specified";
        exit;
    }
}

if($f === "start_server"){
    website_mcservers::sendCompanionData($id,"start");
}
elseif($f === "stop_server"){
    website_mcservers::sendCompanionData($id,"stop");
}
elseif($f === "backup_server"){
    website_mcservers::sendCompanionData($id,"backup");
}
elseif($f === "kill_server_yesiamsure"){
    website_mcservers::sendCompanionData($id,"kill");
}
elseif($f === "sendCommand"){
    website_mcservers::sendCompanionData($id,"sendCommand",html::decodeInString($_GET['command']));
}
elseif($f === "serverStats"){
    $stats = website_mcservers::sendCompanionData($id,"getStats");
    
    $stats['newoutput'] = str_replace("MCServer Monitor/","", $stats['newoutput']);

    echo json_encode($stats);
}
elseif($f === "serverInfo"){
    echo json_encode(runfunction('mcservers::serverInfo("' . $id . '");'));
}
elseif($f === "delete_server"){

    if(isset($_GET["confirm"])){
        runfunction('mcservers::deleteServer("' . $id . '",true);');
        header("Location: /mcservers/list/");
    }
    else{
        html::head();
        html::top("admin_mcservers","Delete server");
        echo '
            <h2>Are you sure you want to delete server ' . $id . '?<br>This cannot be undone!</h2>
            <br>
            <a href="' . $_SERVER['REQUEST_URI'] . '&confirm=1">
                <button onclick="document.getElementById(\'loaddiv\').innerHTML = \'<h3>Deleting Server...</h3>\';" class="account-form-submit" style="background-color:red;border-color:red">
                    YES!
                </button>
            </a>
            <br>
            <div id="loaddiv"></div>
            <br>
            <br>
            <a href="/mcservers/list/"><button class="account-form-submit">NO!</button></a>
        ';

        html::end();
        html::end2();
    }
}
elseif($f === "create_server"){
    $datastring = arrayToEvalString(html::decodeInString($_GET['specialData']));
    if(runfunction('mcservers::createServer(' . $datastring . ');')){
        $serverId = end(runfunction('mcservers::allServers();'));
        html::loadurl("/mcservers/list/?range=" . $serverId . "-" . $serverId);
    }
    else{
        html::loadurl("/mcservers/manager/failed-create/");
    }
}
elseif($f === "publicServer"){
    runfunction('mcservers::addSubdomainToServer("' . $id . '");');
}
elseif($f === "privateServer"){
    runfunction('mcservers::deleteSubdomainForServer("' . $id . '");');
}
elseif($f === "manager_page_home"){
    echo '
        <div id="homepage_mainServerStats">
            <div id="homepage_mainServerUsageBars">
                <div class="usageBar">
                    <div class="usageBarPercent" id="servercpuusage"></div>
                    <div class="usageBarText">
                        <a id="servercputext">CPU</a>
                    </div>
                </div>
                <div class="usageBar">
                    <div class="usageBarPercent" id="servermemoryusage"></div>
                    <div class="usageBarText">
                        <a id="servermemorytext">Memory</a>
                    </div>
                </div>
            </div>
            <div id="homepage_mainStateButtonDiv">
                <button onclick="changeState(); this.blur()" style="background-color:orange; font-size:1.3em;" id="statebutton">Loading...</button>
            </div>
        </div>
        <div id="homepage_extraButtons">
            <button disabled id="homepage_backupButton" onclick="backupServer(); this.blur();">Backup</button>
            <button disabled id="homepage_killButton" onclick="killServer(); this.blur();">Kill</button>
            <input id="homepage_commandInput" type="text"></input>
            <button disabled id="homepage_commandButton" onclick="sendCommand(); this.blur();">Send Command</button>
        </div>
        <div id="homepage_eventLog">
            <pre id="homepage_eventLogText"></pre>
        </div>
    ';
}
elseif($f === "manager_page_files"){
    $dir = getServerDir($id);
    echo '<form action="/mcservers/api/?function=manager_files_submit&id=' . $id . '" method="post">';
    $files = glob($dir . '*.*');
    $fileSettings = array();
    foreach($files as $file){
        $file = str_replace("/","\\",$file);
        $fileType = substr($file,strripos($file,".")+1);
        $fileName = substr($file,strripos($file,"\\")+1);
        $noEditFiles = array();
        $editTypes = array('yaml','yml','json','properties','bat','txt','toml','console_history');
        $showFile = false;
        foreach($editTypes as $editType){if($editType === $fileType){$showFile = true;}}
        foreach($noEditFiles as $noEditFile){if($noEditFile === $fileName){$showFile = false;}}
        if($showFile){
            $fileNameID = str_replace(".",":_dot_:",$fileName);
            $fileNameID = str_replace("\\",":_slash_:",$fileNameID);
            echo '
            <div class="dropdown" style="margin-bottom:5px;">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" style="background-color:white;">' . $fileName . '<span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a>
                        <textarea style="height:400px;width:700px;" type="text" name="' . $fileNameID . '">' . txtrw::readtxt($dir . $fileName) . '</textarea>
                    </li></a>
                </ul>
            </div>';
        }
    }
    echo '<button class="account-form-submit" type="submit" name="submit">Apply</button>
    </form>';
}
elseif($f === "manager_files_submit"){
    $dir = getServerDir($id);
    foreach($_POST as $fileName => $fileValue){
        if($fileName !== "submit"){
            $fileName = str_replace(":_dot_:",".",$fileName);
            $fileName = str_replace(":_slash_:","\\",$fileName);
            txtrw::mktxt($dir . $fileName,$fileValue,true);
        }
    }
    html::loadurl('/mcservers/manager/?setPage=files&id=' . $id);
}
elseif($f === "manager_page_runtime"){
    $serverInfo = runfunction('mcservers::serverInfo("' . $id . '");');
    if($serverInfo['capabilities']['hasPropertiesFile'] === true){
        echo '<button style="height:30px;" onclick="setContentPage(\'runtime_serverproperties\');">server.properties</button>';
    }
}
elseif($f === "manager_page_runtime_serverproperties"){
    $propertiesSpec = runfunction('mcservers::serverPropertiesFileInfo()');
    $serverProperties = runfunction('mcservers::parseServerPropertiesFile("' . $id . '");');
    echo '<form action="/mcservers/api/?function=manager_runtime_serverproperties_submit&id=' . $id . '" method="post">';
    foreach($serverProperties as $propertyName => $propertyValue){
        echo '
            <div style="width:100%; height:50px; overflow:hidden; margin-bottom:5px; border-color:grey; border-radius:5px; border-width:1px; border-style:solid; padding:5px; display:flex; align-items:center;">
                <div style="width:270px;">
                    <a style="float:right">' . $propertyName . ':</a>
                </div>
                ';
                if(isset($propertiesSpec[$propertyName])){
                    $values = $propertiesSpec[$propertyName]["values"];
                    if(count($values) === 1){
                        if($values[0] === "integer"){
                            echo '<input class="account-form-input" type="number" ';
                            if(isset($propertiesSpec[$propertyName]["int_min"])){
                                echo 'min="' . $propertiesSpec[$propertyName]["int_min"] . '" ';
                            }
                            if(isset($propertiesSpec[$propertyName]["int_max"])){
                                echo 'max="' . $propertiesSpec[$propertyName]["int_max"] . '" ';
                            }
                            echo 'name="' . $propertyName . '" value="' . $propertyValue . '">';
                        }
                        else{
                            echo '<input class="account-form-input" type="text" name="' . $propertyName . '" value="' . $propertyValue . '">';
                        }
                    }
                    else{
                        echo '<select name="' . $propertyName . '" class="account-form-input">';
                            foreach($values as $value){
                                echo '<option value="' . $value . '" '; if($value == $propertyValue){echo 'selected ';} echo '>' . $value . '</option>';
                            }
                        echo '</select>';
                    }
                    echo '
                        <div style="width:210px;">
                            <a>Default: ' . $propertiesSpec[$propertyName]["default"] . '</a>
                        </div>
                        <img style="height:30px; margin-left:5px; cursor:pointer; width:auto; filter:invert(100%);" src="' . $filesUrl . '/img/info-icon.svg" title="' . $propertiesSpec[$propertyName]["description"] . '">
                    ';
                }
                else{
                    echo '<input class="account-form-input" type="text" name="' . $propertyName . '" value="' . $propertyValue . '">';
                }
                echo '
            </div>
        ';
    }
    echo '
        <button class="account-form-submit" type="submit" name="submit" onclick="this.innerHTML=\'Loading...\'">Apply</button>
        </form>
    ';
}
elseif($f === "manager_runtime_serverproperties_submit"){
    $expectedProperties = runfunction('mcservers::serverPropertiesFileInfo()');
    $newProperties = array();
    foreach($_POST as $potentialPropertyName => $potentialPropertyValue){
        if(isset($expectedProperties[$potentialPropertyName])){
            $newProperties[$potentialPropertyName] = $potentialPropertyValue;
        }
    }
    if(runfunction('mcservers::modifyServerPropertiesFile("' . $id . '",' . arrayToEvalString($newProperties) . ');')){
        html::loadurl('/mcservers/manager/?setPage=runtime_serverproperties&settingsApplied=true&id=' . $id);
    }
    else{
        echo "Failed.";
    }
}
elseif($f === "manager_page_mods"){
    echo '
        <button style="height:30px;" onclick="setModrinthContentType(\'mod\'); setContentPage(\'mods_addnew\');">Add Mod</button>
        <br>
    ';
    echo listContent(runfunction('mcservers::listContents("' . $id . '","mod")'));
}
elseif($f === "manager_page_datapacks"){
    echo '
        <button style="height:30px;" onclick="setModrinthContentType(\'datapack\'); setContentPage(\'datapacks_addnew\');">Add Datapack</button>
        <br>
    ';
    echo listContent(runfunction('mcservers::listContents("' . $id . '","datapack")'));
}
elseif($f === "manager_page_plugins"){
    echo '
        <button style="height:30px;" onclick="setModrinthContentType(\'plugin\'); setContentPage(\'plugins_addnew\');">Add Plugin</button>
        <br>
    ';
    echo listContent(runfunction('mcservers::listContents("' . $id . '","plugin")'));
}
elseif($f === "manager_page_resourcepacks"){
    echo '
        <button style="height:30px;" onclick="setModrinthContentType(\'resourcepack\'); setContentPage(\'resourcepacks_addnew\');">Add Resourcepack</button>
        <br>
    ';
    echo listContent(runfunction('mcservers::listContents("' . $id . '","resourcepack")'));
}
elseif($f === "manager_page_mods_addnew"){
    echo modrinthInitialHtml();
}
elseif($f === "manager_page_datapacks_addnew"){
    echo modrinthInitialHtml();
}
elseif($f === "manager_page_plugins_addnew"){
    echo modrinthInitialHtml();
}
elseif($f === "manager_page_resourcepacks_addnew"){
    echo modrinthInitialHtml();
}
elseif($f === "manager_page_actions"){
    echo '
        <button onclick="window.location.href=\'/mcservers/api/?function=delete_server&id=' . $id . '\';" class="btn btn-danger">Delete Server</button>
    ';
}
elseif($f === "modrinthContentSearch"){
    $serverInfo = runfunction('mcservers::serverInfo("' . $id . '");');

    $comString = 'modrinth_api::search("' . $_GET['query'] . '", ';
    if($_GET['type'] === "resourcepack"){
        $comString .= '"minecraft"';
    }
    elseif($_GET['type'] === "datapack"){
        $comString .= '"datapack"';
    }
    else{
        $comString .= '"' . $serverInfo['version']['type'] . '"';
    }
    $comString .= ', "' . $serverInfo['version']['version'] . '", "' . $_GET['type'] . '", ';
    if($_GET['type'] === "resourcepack" || $_GET['type'] === "datapack"){
        $comString .= 'false';
    }
    else{
        $comString .= 'true';
    }
    $comString .= ');';
    $results = runfunction($comString);
    
    $resultsTotal = count($results['results']);
    $currentResult = 1;
    if($resultsTotal === 0){
        echo 'No Compatible Results';
    }
    else{
        foreach($results['results'] as $result){
            echo '
                <div style="width:100%; height:100px; display:flex; border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; padding:5px;">
                    <img style="height:100%; border-radius:5px; aspect-ratio:1" src="' . $result['icon_url'] . '">
                    <div>
                        <div style="margin-left:6px; width:400px; height:60px; overflow:hidden;">
                            <p style="position:relative; top:-5px; font-size:1.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . $result['title'] . '</p>
                            <p style="position:relative; top:-18px; left:1px; color:grey; font-size:0.7rem; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; text-overflow:ellipsis;">' . $result['description'] . '</p>
                        </div>
                        <div style="display:flex; margin-left:6px; width:450px; margin-top:10px; height:calc(100% - 70px);">
                            <div style="height:100%; display:flex;">
                                <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/download.png">
                                <p style="position:relative; top:-3px; left:3px;">' . number_format($result['downloads']) . '</p>
                            </div>
                            <div style="margin-left:30px; height:100%; display:flex;">
                                <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/user.png">
                                <p style="position:relative; top:-3px; left:4px;">' . $result['author'] . '</p>
                            </div>
                            <div style="margin-left:30px; height:100%; display:flex;">
                                <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/back-clock.png">
                                <p style="position:relative; top:-3px; left:4px;">';
                                    $givenDatetime = new DateTime($result['date_modified']);
                                    // Get the current datetime as a DateTime object
                                    $currentDatetime = new DateTime();
                                    // Calculate the difference between the two DateTime objects
                                    $interval = $currentDatetime->diff($givenDatetime);
                                    // Check and format the difference
                                    if ($interval->y > 0) {
                                        // If difference is in years
                                        echo $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
                                    } elseif ($interval->m > 0) {
                                        // If difference is in months
                                        echo $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
                                    } elseif ($interval->d >= 7) {
                                        // If difference is in weeks
                                        $weeks = floor($interval->d / 7);
                                        echo $weeks . ' week' . ($weeks > 1 ? 's' : '');
                                    } else {
                                        // If difference is in days
                                        echo $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
                                    }
                                echo '</p>
                            </div>
                        </div>
                    </div>
                    <div onclick="modrinthContentLoadResultVersions(\'' . $result['project_id'] . '\',\'' . html::encodeInString($result['title']) . '\');" style="height:100%; flex:1; cursor:pointer; border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; padding:5px; text-align:center;">
                        <img style="height:calc(100% - 40px);" src="' . $filesUrl . '/img/plus.png">
                        <p style="font-size:18px; margin-top:5px;">Add Content</p>
                    </div>
                </div>
            ';
            if($currentResult < $resultsTotal){
                echo '<div style="height:5px;"></div>';
            }
            $currentResult++;
        }
    }
}
elseif($f === "modrinthContentLoadResultVersions"){
    $serverInfo = runfunction('mcservers::serverInfo("' . $id . '");');

    $comString = 'modrinth_api::listProjectVersions("' . $_GET['projectId'] . '", "' . $serverInfo['version']['version'] . '", ';
    if($_GET['type'] === "resourcepack"){
        $comString .= '"minecraft"';
    }
    elseif($_GET['type'] === "datapack"){
        $comString .= '"datapack"';
    }
    else{
        $comString .= '"' . $serverInfo['version']['type'] . '"';
    }
    $comString .= ');';
    $results = runfunction($comString);
    
    if(!empty($results)){
        $resultsTotal = count($results);
        $currentResult = 1;
        if($resultsTotal === 0){
            echo 'No Compatible Results';
        }
        else{
            foreach($results as $result){
                echo '
                    <div style="width:100%; height:100px; display:flex; border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; padding:5px;">
                        <div>
                            <div style="margin-left:6px; width:400px; height:60px; overflow:hidden;">
                                <p style="position:relative; top:-5px; font-size:1.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . $result['name'] . '</p>
                                <p style="position:relative; top:-18px; left:1px; color:grey; font-size:0.7rem">' . $result['version_number'] . '</p>
                            </div>
                            <div style="display:flex; margin-left:6px; width:500px; margin-top:10px; height:calc(100% - 70px);">
                                <div style="height:100%; display:flex;">
                                    <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/download.png">
                                    <p style="position:relative; top:-3px; left:3px;">' . number_format($result['downloads']) . '</p>
                                </div>
                                <div style="margin-left:40px; height:100%; display:flex;">
                                    <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/file-version.png">
                                    <p style="position:relative; top:-3px; left:3px;">' . $result['version_type'] . '</p>
                                </div>
                                <div style="margin-left:40px; height:100%; display:flex;">
                                    <img style="height:100%; aspect-ratio:1;" src="' . $filesUrl . '/img/back-clock.png">
                                    <p style="position:relative; top:-3px; left:4px;">';
                                        $givenDatetime = new DateTime($result['date_published']);
                                        // Get the current datetime as a DateTime object
                                        $currentDatetime = new DateTime();
                                        // Calculate the difference between the two DateTime objects
                                        $interval = $currentDatetime->diff($givenDatetime);
                                        // Check and format the difference
                                        if ($interval->y > 0) {
                                            // If difference is in years
                                            echo $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
                                        } elseif ($interval->m > 0) {
                                            // If difference is in months
                                            echo $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
                                        } elseif ($interval->d >= 7) {
                                            // If difference is in weeks
                                            $weeks = floor($interval->d / 7);
                                            echo $weeks . ' week' . ($weeks > 1 ? 's' : '');
                                        } else {
                                            // If difference is in days
                                            echo $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
                                        }
                                    echo '</p>
                                </div>
                            </div>
                        </div>
                        <div onclick="modrinthContentApply(\'' . $_GET['projectId'] . '\',\'' . $result['id'] . '\');" style="height:100%; flex:1; cursor:pointer; border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; padding:5px; text-align:center;">
                            <img style="height:calc(100% - 40px);" src="' . $filesUrl . '/img/plus.png">
                            <p style="font-size:18px; margin-top:5px;">Add Version</p>
                        </div>
                    </div>
                ';
                if($currentResult < $resultsTotal){
                    echo '<div style="height:5px;"></div>';
                }
                $currentResult++;
            }
        }
    }
    else{
        echo 'No Compatible Results';
    }
}
elseif($f === "modrinthContentApply"){
    $result = runfunction('mcservers::addModrinthContentToServer("' . $id . '", "' . $_GET['projectId'] . '", "' . $_GET['projectVersion'] . '", "' . $_GET['type'] . '");');
    if(is_string($result)){
        echo '<pre style="color:black">' . $result . '</pre>';
    }
    else{
        echo "Failed.";
    }
}
else{
    echo "Function not found.";
}

function listContent($contents):string{
    $return = "";
    if(is_array($contents)){
        foreach($contents as $fileName => $fileData){
            $return .= '<a>' . $fileName . '</a><br>';
        }
    }
    return $return;
}
function modrinthInitialHtml():string{
    return '
        <div style="width:700px; height:600px; background-color:white; border-radius:5px; padding:5px;">
            <div style="display:flex; height:40px;">
                <input id="modrinthContentSearchBar" onkeyup="modrinthContentSearchDebounced();" style="width:625px; height:100%" class="form-control mr-sm-2" type="text" placeholder="Search Modrinth">
                <div id="modrinthContentSearchButton" onclick="modrinthContentSearch();" style="position:relative; width:60px; height:100%; border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; cursor:pointer;">
                    <img id="modrinthContentSearchButtonImage" style="position:relative; top:50%; left:50%; transform:translate(-50%, -50%); height:30px; filter:invert(100%);" src="' . $GLOBALS['filesUrl'] . '/img/search.png">
                </div>
            </div>
            <div id="modrinthContentSearchResults" style="overflow-y:scroll; overflow-x:hidden; color:black; position:relative; top:5px; width:100%; height:calc(100% - 45px); border-color:lightgrey; border-radius:5px; border-width:1px; border-style:solid; padding:5px;">

            </div>
        </div>
    ';
}
function arrayToEvalString(array $data):string{
    $string = 'array(';
    foreach($data as $key => $value){
        if(is_int($key)){
            $keytext = $key;
        }
        else{
            $keytext = '"' . $key . '"';
        }
        
        if(is_array($value)){
            $valuetext = arrayToEvalString($value);
        }
        else{
            if(is_int($value) || is_float($value)){$valuetext = $value;}
            elseif(is_bool($value)){
                $valuetext = data_types::boolean_to_string($value);
            }
            else{
                $valuetext = '"' . $value . '"';
            }
        }
        $string .= $keytext . '=>' . $valuetext . ',';
    }
    $string .= ')';
    return $string;
}
function getServerDir($id){
    if(website_mcservers::validateId($id)){
        return runfunction('mcservers::serverDir()') . '\\' . $id . '\\';
    }
    return false;
}