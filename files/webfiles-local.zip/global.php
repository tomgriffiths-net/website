<?php
startup::checkGetRequest();

if(!isset($skipAuth)){
    $skipAuth = false;
}

$settingsFile = "D:\\Projects\\PHP-CLI\\mywebsite\\localdata\\settings.json";
$globalSettings = startup::loadGlobalSettings($settingsFile);

startup::setTimeZone();
$uri = startup::setupUri();
startup::startSession();
$useruid = startup::setupUseruid();

if(!$skipAuth){
    startup::ensureLogin($useruid);
}

if(!website_communicator::getName() || !website_communicator::getPasswordEncoded()){
    echo "Communicator credentials error";
    exit;
}

//Set commonly used settings to variables
$localDir = $globalSettings["localdir"];
$filesUrl = $globalSettings["filesurl"];

if($globalSettings['verbose-logging'] === true){
    startup::logPing();
}

class startup{
    public static function checkGetRequest(){
        if(implode($_GET) !== ""){
            if(preg_match("/^[a-zA-Z0-9 ._-]+$/", implode($_GET)) == false){
                mklog("bad-ping",$_SERVER["REQUEST_URI"],false);
                exit;
            }
        }
    }
    public static function loadGlobalSettings($settingsFile){
        if(!is_file($settingsFile)){
            echo "Settings file not found";
            exit;
        }
        
        $json = website_json::readFile($settingsFile);

        if(!is_array($json)){
            echo "Failed to read settings file";
            exit;
        }

        return $json;
    }
    public static function setTimeZone(){
        if(date_default_timezone_set("Europe/London") == false){
            mklog("error","Failed to set default time zone",false);
        }
    }
    public static function setupUri(){
        if(isset($_SERVER["HTTP_HOST"]) && isset($_SERVER["REQUEST_URI"])){
            return html::normaliseUrl($_SERVER["REQUEST_URI"]);
        }
        else{
            exit;
        }
    }
    public static function startSession(){
        if(session_start() == false){
            mklog("error","Failed to start a session",false);
        }
    }
    public static function setupUseruid(){
        //Check if user is logged in
        if(isset($_SESSION["useruid"])){
            //Set userid and set loggedIn to true
            return $_SESSION["useruid"];
        }
        return false;
    }
    public static function ensureLogin($useruid){
        if($useruid !== "admin"){
            html::loadurl('/login');
        }
    }
    public static function logPing(){
        $uri = $_SERVER["REQUEST_URI"];
        if(isset($_SERVER['REMOTE_ADDR'])){
            if(
                $uri !== "social/create-post/video/index.php"
            )
            mklog("ping","Request=" . $_SERVER["REMOTE_ADDR"] . "," . $uri,true);
        }
    }
}
function mklog($type,$message,$verbose=true){
    global $globalSettings;
    $microtime = floor(microtime(true)*1000);
    //Set current time and date with miliseconds
    $time = date("Y-m-d_H:i:s:") . substr($microtime, 10, 3);
    //Check if logsdir is available

    $line = $time . ": " . $type . ": " . $message . "\n";

    if(isset($globalSettings["tempdir"])){
        $logsDir = $globalSettings["tempdir"] . "\\logs\\";
        if(!is_dir($logsDir)){
            mkdir($logsDir,0777, true);
        }
        if(!isset($GLOBALS['uri'])){
            echo $line;
        }
        $logFile = fopen($logsDir . $microtime . "-" .  $type . ".txt","a");
        fwrite($logFile, $line);
        fclose($logFile);
    }
    else{
        echo $line;
        echo "Unable to save log " . $microtime . "-" . $type;
        exit;
    }

    //Display error if in development mode, exit to root page if not
    if($type === "error"){
        if($globalSettings["devmode"]){
            echo $message;
        }
        else{
            //Check if main page is allready the current page
            if($GLOBALS["uri"] === "/"){
                //Exit redirect loop if error is within main page request
                echo "Error with main page";
            }
            else{
                //Redirect to main page if current page is not main page
                header("Location: /");
            }
        }
        exit;
    }
}
function setSetting(string $settingName, mixed $settingValue, bool $overwrite=false):bool{
    global $globalSettings;

    $settingCodeString = settingEvalString($settingName);
    if($settingCodeString === false){
        return false;
    }

    $settingIsset = eval('return isset($globalSettings' . $settingCodeString . ');');
    $writeSetting = false;
    if($settingIsset){
        if($overwrite){
            $writeSetting = true;
        }
    }
    else{
        $writeSetting = true;
    }

    $successful = false;

    if($writeSetting){

        $evalErr = false;

        eval('
            try{
                unset($globalSettings' . $settingCodeString . ');
                $globalSettings' . $settingCodeString . ' = $settingValue;
            }
            catch(\Error){
                mklog("warning","Unable to set setting in non-array",false);
                $evalErr = true;
            }
        ');

        if($evalErr){
            goto end;
        }

        website_json::writeFile($globalSettings['localdir'] . "\\settings.json",$globalSettings,true);

        $successful = true;
    }

    end:
    return $successful;
}
function settingEvalString(string $settingName):string|false{
    $invalidChars = array("'",';',':','\\','(',')');
    foreach($invalidChars as $invalidChar){
        if(strpos($settingName,$invalidChar) !== false){
            mklog('warning','$settingName contained an invalid character: ' . $invalidChar,false);
            return false;
        }
    }

    $settingNames = array();
    if(strpos($settingName,"/") !== false){
        $settingNames = explode("/",$settingName);
    }
    else{
        $settingNames[0] = $settingName;
    }

    $settingCodeString = '';
    foreach($settingNames as $settingNamePart){
        $settingCodeString .= "['" . $settingNamePart . "']";
    }

    return $settingCodeString;
}
function runfunction(string $function):mixed{
    return website_communicator_client::runfunction($function);
}
function runcommand(string $command):bool{
    return website_communicator_client::runcommand($command);
}
class html{
    public static function fullend(string|false $scriptLink=false){
        html::end($scriptLink);
        if(is_string($scriptLink)){
            $dontCloseScriptTag = true;
        }
        else{
            $dontCloseScriptTag = false;
        }
        html::end2($dontCloseScriptTag);
    }
    public static function fullhead(string $headerId="main", string $pageTitle="", string|false $styleLink=false){
        html::head($styleLink);
        if(is_string($styleLink)){
            $dontCloseStyleTag = true;
        }
        else{
            $dontCloseStyleTag = false;
        }
        html::top($headerId,$pageTitle,$dontCloseStyleTag);
    }
    public static function top(string $headerId="main", string $pageTitle="", bool $dontCloseStyleTag=false){
        $headerData = self::getNavButtons($headerId);
        if(!is_array($headerData)){
            $headerData = [
                "webName" => "My Website",
                "webNameLink" => "/",
                "buttons" => [
                    [
                        "name" => "Home",
                        "link" => "/"
                    ]
                ]
            ];
        }

        if(empty($pageTitle)){
            $pageTitle = $headerData['webName'];
        }

        if(!$dontCloseStyleTag){
            echo '</style>';
        }
    
        echo '
            <title>' . $pageTitle . '</title>
        </head>
        <body data-bs-theme="dark">';
        echo '
            <div class="wrapper">
                <nav class="navbar navbar-expand-lg bg-body-tertiary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="' . $headerData['webNameLink'] . '">' . $headerData['webName'] . '</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            ';
                            foreach($headerData['buttons'] as $button){
                                if(isset($button['dropdown'])){
                                    echo '
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle' . (html::isThisCurrentLinkDropdown($button['dropdown']) ? " active" : "") . '" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">' . $button['name'] . '</a>
                                            <ul class="dropdown-menu">';
                                                foreach($button['dropdown'] as $dbutton){
                                                    echo '<li><a class="dropdown-item" href="' . $dbutton['link'] . '">' . $dbutton['name'] . '</a></li>';
                                                }
                                            echo '
                                            </ul>
                                        </li>
                                    ';
                                }
                                else{
                                    echo '<li class="nav-item"><a class="nav-link' . (html::isThisCurrentLink($button['link']) ? " active" : "") . '" href="' . $button['link'] . '">' . $button['name'] . '</a></li>';
                                }
                            }
                            echo '
                        </ul>
                        ';
                        if(!html::isThisCurrentLink("/search")){
                            echo '
                                <form class="d-flex" role="search" action="/search/" method="get">
                                    <input class="form-control me-2" type="search" name="q" style="min-width:300px;" placeholder="Search" aria-label="Search"/>
                                    <button class="btn btn-outline-success" type="submit">Search</button>
                                </form>
                            ';
                        }
                        echo '
                        </div>
                    </div>
                </nav>
            </div>
            <div class="wrapper" id="main-wrapper">
        ';
    }
    public static function start_empty(){
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <style>
                    body {font-family:DejaVu Sans Mono, monospace; font-weight:600; color:white; background-color:#242324;}
                </style>
            </head>
            <body>
        ';
    }
    public static function end_empty(){
        echo '
            </body>
            </html>
        ';
    }
    public static function head(string|false $styleLink=false){
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
            <link href="' . $GLOBALS['filesUrl'] . '/site.css" rel="stylesheet">
            <script src="' . $GLOBALS['filesUrl'] . '/functions.js"></script>
        ';
        if(is_string($styleLink)){
            echo '<link rel="stylesheet" type="text/css" href="' . $styleLink . '">';
        }
        else{
            echo "<style>";
        }
    }
    public static function end(string|false $scriptLink=false){
        echo '</div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        ';
        if(is_string($scriptLink)){
            echo '<script src="' . $scriptLink . '"></script>';
        }
        else{
            echo '<script>';
        }
    }
    public static function end2(bool $dontCloseScriptTag=false){
        if($dontCloseScriptTag !== true){
            echo '</script>';
        }
        echo ' 
            </body>
            </html>
        ';
    }
    public static function blankpage(string $msg){
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="shortcut icon" type="image/png" href="' . $GLOBALS['filesUrl'] . 'img/icon.png">
            <style>
                body {font-family:DejaVu Sans Mono, monospace; font-weight:600; color:white; background-color:#242324;}
            </style>
        </head>
        <body>
            <center><h1>' . $msg . '</h1></center>
        </body>
        </html>
        ';
    }
    public static function loadurl(string $url){
        header("Location: " . $url);
        exit;
    }
    public static function encodeInString(mixed $data):string{
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }
    public static function decodeInString(mixed $data):mixed{
        return json_decode(base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)),true);
    }
    public static function getNavButtons(string $headerId):array|false{
        if(!preg_match("/^[a-zA-Z0-9-_.]+$/", $headerId)){
            return false;
        }

        if(!is_file($GLOBALS['localDir'] . "\\headers\\" . $headerId . ".json")){
            return false;
        }

        $data = website_json::readFile($GLOBALS['localDir'] . "\\headers\\" . $headerId . ".json");
        if(!is_array($data) || !isset($data['webName']) || !is_string($data['webName']) || !isset($data['webNameLink']) || !is_string($data['webNameLink'])){
            return false;
        }

        if(!isset($data['buttons']) || !is_array($data['buttons']) || !array_is_list($data['buttons'])){
            return false;
        }

        foreach($data['buttons'] as $button){
            if(!isset($button['name']) || !is_string($button['name'])){
                return false;
            }

            if(isset($button['link'])){
                if(!is_string($button['link'])){
                    return false;
                }
            }
            elseif(isset($button['dropdown'])){
                if(!is_array($button['dropdown']) || !array_is_list($button['dropdown'])){
                    return false;
                }

                foreach($button['dropdown'] as $dropdownItem){
                    if(!isset($dropdownItem['name']) || !is_string($dropdownItem['name']) || !isset($dropdownItem['link']) || !is_string($dropdownItem['link'])){
                        return false;
                    }
                }
            }
            else{
                return false;
            }
        }

        return $data;
    }
    public static function isThisCurrentLink(string $link):bool{
        global $uri;

        return (strtolower(self::normaliseUrl($uri)) === strtolower(self::normaliseUrl($link)));
    }
    public static function isThisCurrentLinkDropdown(array $dropdown):bool{
        foreach($dropdown as $item){
            if(isset($item['link']) && is_string($item['link'])){
                if(self::isThisCurrentLink($item['link'])){
                    return true;
                }
            }
        }

        return false;
    }
    public static function normaliseUrl(string $url):string{
        $path = parse_url($url, PHP_URL_PATH);
        // Normalize:
        $path = rtrim($path, '/');           // Remove trailing slash
        $path = '/' . ltrim($path, '/');     // Ensure leading slash
        $path = preg_replace('#/+#', '/', $path); // Remove duplicate slashes

        return $path;
    }
}

class website_communicator{
    // Settings
    public static function getName():string|bool{
        global $globalSettings;
        if(isset($globalSettings['communicator']['name'])){
            return $globalSettings['communicator']['name'];
        }
        return false;
    }
    public static function getPasswordEncoded():string|bool{
        global $globalSettings;
        if(isset($globalSettings['communicator']['password'])){
            return $globalSettings['communicator']['password'];
        }
        return false;
    }
    public static function verifyPassword(string $password):bool{
        return ($password === self::getPasswordEncoded());
    }
    // Data
    public static function send($stream, string $data):bool{
        $dataLength = strlen($data);
        if(strlen($dataLength) <= 20){
            if(fwrite($stream,$dataLength,20) !== false){
                if(fread($stream,2) === "OK"){
                    if(fwrite($stream,$data,$dataLength) !== false){
                        if(fread($stream,2) === "OK"){
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    public static function receive($stream):string|bool{
        $responseLength = fread($stream,20);
        if($responseLength !== false){
            fwrite($stream,"OK",2);
            $responseLength = intval($responseLength);
            if($responseLength > 0){
                $response = "";
                while(strlen($response) < $responseLength){
                    $read = fread($stream,8192);
                    if($read !== false){
                        $response .= $read;
                    }
                    else{
                        break;
                    }
                }
                
                fwrite($stream,"OK",2);
                return $response;
                
            }
        }
        return false;
    }
    // Actions
    public static function close($stream):bool{
        return @fclose($stream);
    }
    public static function connect(string $ip, int $port, float|false $timeout, &$socketErrorCode, &$socketErrorString):mixed{
        if($timeout === false){
            $timeout = null;
        }
        return @stream_socket_client("tcp://$ip:$port", $socketErrorCode, $socketErrorString, $timeout);
    }
}
class website_communicator_client{
    public static function runfunction(string $function):mixed{
        $result = self::run('127.0.0.1', 8080, array("type"=>"function_string","payload"=>$function));
        if($result["success"]){
            return $result["result"];
        }
        return false;
    }
    public static function runcommand(string $command):bool{
        $result = self::run('127.0.0.1', 8080, array("type"=>"command","payload"=>$command));
        return $result["success"];
    }
    public static function run(string $ip, int $port, array $data):array{
        $socket = website_communicator::connect($ip,$port,false,$socketError,$socketErrorString);
        if($socket !== false){
            return self::execute($socket,$data);
        }
        return array("success"=>false,"error"=>"Unable to connect to " . $ip . ":" . $port);
    }
    private static function execute($socket, array $data):array{
        $return = array("success"=>false);

        if(!isset($data['type'])){
            $return["error"] = "Type not set";
            goto end;
        }
        if(!in_array($data['type'],array("function_string","command","stop"))){
            $return["error"] = "Type not recognised";
            goto end;
        }

        if(!isset($data['payload'])){
            $return["error"] = "Payload not set";
            goto end;
        }

        $data['name'] = website_communicator::getName();
        $data['password'] = website_communicator::getPasswordEncoded();

        $data = base64_encode(json_encode($data));

        if(!website_communicator::send($socket,$data)){
            $return["error"] = "Error sending data";
            goto end;
        }

        $result = website_communicator::receive($socket);
        if($result === false){
            $return["error"] = "Error receiving data";
            goto end;
        }

        $result = json_decode(base64_decode($result),true);
        if($result === null){
            $return["error"] = "Empty response";
            goto end;
        }

        $return["success"] = true;
        $return["result"] = $result;

        end:
        website_communicator::close($socket);
        return $return;
    }
}
class website_json{
    public static function readFile(string $path):mixed{
        if(is_file($path)){
            $json = @file_get_contents($path);
            if($json === false){
                mklog("warning","Failed to read from file: ". $path);
                return false;
            }
            $json = json_decode($json,true);
            if($json === null){
                mklog("warning","Failed to decode json from file: ". $path);
                return false;
            }
            return $json;
        }
        return false;
    }
    public static function writeFile(string $path, mixed $value, bool $overwrite=false):bool{
        $json = json_encode($value,JSON_PRETTY_PRINT);
        if($json === false){
            return false;
        }

        $writeFile = !is_file($path);
    
        if($overwrite === true){
            $writeFile = true;
        }
    
        if($writeFile){
            $dir = dirname($path);
            if($dir !== ""){
                if(is_file($dir)){
                    return false;
                }
                if(!is_dir($dir)){
                    if(!mkdir($dir,0777,true)){
                        return false;
                    }
                }
            }

            return (bool) @file_put_contents($path, $json);
        }
        return false;
    }
}
class website_mcservers{
    public static function validateId($id):bool{
        $valid = false;
        if(strlen($id) === 3 && preg_match("/^[0-9]+$/",$id)){
            $valid = true;
        }
        return $valid;
    }
    public static function sendCompanionData(string $id, string $action, string $payload = ""):array|bool{
        if(!self::validateId($id, true)){
            return false;
        }

        $result = self::manage($id, $action, $payload);

        if(!is_array($result) || !isset($result['success']) || !$result['success']){
            return false;
        }

        if($action === "getStats"){
            return isset($result['stats']) ? $result['stats'] : false;
        }

        return true;
    }
    public static function manage(string $id, string $action, mixed $extra=null):array|false{
        if(!self::validateId($id, true)){
            return false;
        }

        return website_communicator_client::runfunction('mcservers::manager_run("' . $id . '", unserialize(base64_decode("' . base64_encode(serialize($action)) . '")), unserialize(base64_decode("' . base64_encode(serialize($extra)) . '")))');
    }
}
class website_website{
    public static function sendCommand(string $action, array $sites=[], array $servers=[]):array|false{
        foreach(['sites','servers'] as $thing){
            if(!array_is_list($$thing)){
                return false;
            }
            foreach($$thing as $eeee){
                if(!is_int($eeee)){
                    return false;
                }
            }
        }

        $action = base64_encode(serialize($action));
        $sites = base64_encode(serialize($sites));
        $servers = base64_encode(serialize($servers));

        $response = website_communicator_client::runfunction('website::hoster_run(unserialize(base64_decode(\''.$action.'\')), unserialize(base64_decode(\''.$sites.'\')), unserialize(base64_decode(\''.$servers.'\')))');

        if(!is_array($response)){
            return false;
        }

        return $response;
    }
    public static function sendCommandBool(string $command, array $sites=[], array $servers=[]):bool{
        $response = self::sendCommand($command, $sites, $servers);
        if(!is_array($response)){
            return false;
        }

        if(!$response['success']){
            if(!isset($response['error']) || empty($response['error'])){
                return false;
            }

            return false;
        }

        return true;
    }
}