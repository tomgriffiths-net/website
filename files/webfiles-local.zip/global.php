<?php
startup::checkGetRequest();

if(!isset($skipAuth)){
    $skipAuth = false;
}

//NEXT_LINE_IS_SETTINGS_FILE
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
            return $_SERVER["REQUEST_URI"];
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
    public static function fullend($scriptLink = false){
        html::end($scriptLink);
        if(is_string($scriptLink)){
            $dontCloseScriptTag = true;
        }
        else{
            $dontCloseScriptTag = false;
        }
        html::end2($dontCloseScriptTag);
    }
    public static function fullhead($headerId = "main", $pageTitle = "", $styleLink = false){
        html::head($styleLink);
        if(is_string($styleLink)){
            $dontCloseStyleTag = true;
        }
        else{
            $dontCloseStyleTag = false;
        }
        html::top($headerId,$pageTitle,$dontCloseStyleTag);
    }
    public static function top($headerId = "main",$pageTitle = "", $dontCloseStyleTag = false){
        $headerData = website_json::readFile($GLOBALS['localDir'] . "\\headers\\" . $headerId . ".json");
        if(!is_array($headerData)){
            $headerData = array("webName"=>"My Website","webNameLink"=>"/","buttons"=>array(0=>array("name"=>"Home","link"=>"/")));
        }

        if($pageTitle === ""){
            $pageTitle = $headerData['webName'];
        }
    
        if(!$dontCloseStyleTag){
            echo '</style>';
        }
    
        echo '
            <title>' . $pageTitle . '</title>
        </head>
        <body>';
        echo '
            <div class="wrapper">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark"><a class="navbar-brand" href="' . $headerData['webNameLink'] . '">' . $headerData['webName'] . '</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent1">
                        <ul class="navbar-nav mr-auto">
                            '; 
                            foreach($headerData['buttons'] as $button){
                                if(isset($button['dropdown'])){
                                    echo '<li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown1" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ' . $button['name'] . '
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown1">';
                                    foreach($button['dropdown'] as $dbutton){
                                        echo '<a class="dropdown-item" href="' . $dbutton['link'] . '">' . $dbutton['name'] . '</a>';
                                    }
                                    echo '</div>
                                    </li>';
                                }
                                else{
                                    echo '<li class="nav-item"><a class="nav-link" href="' . $button['link'] . '">' . $button['name'] . '</a></li>';
                                }
                            }
                            echo '
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="wrapper" id="main-wrapper">
        ';
    }
    public static function start_empty(){
        // Echo page code
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>
                body {font-family:DejaVu Sans Mono, monospace; font-weight:600; color:white; background-color:#242324;}
            </style>
        </head>
        <body>
        ';
    }
    public static function end_empty(){
        // Echo page code
        echo '
        </body>
        </html>';
    }
    public static function head($styleLink = false){
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="' . $GLOBALS['filesUrl'] . '/css/bootstrap-4.4.1.css" rel="stylesheet">
            <link href="' . $GLOBALS['filesUrl'] . '/css/site.css" rel="stylesheet">
            <script src="' . $GLOBALS['filesUrl'] . '/js/jquery-3.7.1.min.js"></script>
            <script src="' . $GLOBALS['filesUrl'] . '/js/popper.min.js"></script>
            <script src="' . $GLOBALS['filesUrl'] . '/js/bootstrap-4.4.1.js"></script>
            <script src="' . $GLOBALS['filesUrl'] . '/js/functions.js"></script>
        ';
        if(is_string($styleLink)){
            echo '<link rel="stylesheet" type="text/css" href="' . $styleLink . '" />';
        }
        else{
            echo "<style>";
        }
    }
    public static function end($scriptLink = false){
        echo '</div>';
        if(is_string($scriptLink)){
            echo '<script src="' . $scriptLink . '"></script>';
        }
        else{
            echo '<script>';
        }
    }
    public static function end2($dontCloseScriptTag = false){
        if($dontCloseScriptTag !== true){
            echo '</script>';
        }
        echo ' 
            </body>
            </html>
        ';
    }
    public static function blankpage($msg){
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    public static function loadurl($url){
        header("Location: " . $url);
        exit;
    }
    public static function encodeInString($data):string{
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }
    public static function decodeInString($data):mixed{
        return json_decode(base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)),true);
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
    public static function createServer(string $ip, int $port, int|false $timeout, &$socketErrorCode, &$socketErrorString):mixed{
        $socket = @stream_socket_server("tcp://$ip:$port", $socketErrorCode, $socketErrorString);
        if($socket === false){
            return false;
        }
        if($timeout !== false){
            if(@stream_set_timeout($socket, $timeout) === false){
                return false;
            }
        }
        return $socket;
    }
    public static function acceptConnection($socketServer, float|false $timeout):mixed{
        if($timeout !== false){
            $timeout = null;
        }
        return @stream_socket_accept($socketServer, $timeout);
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
        if(strlen($id) === 3 && preg_match("/^[0-9]+$/",$id) === 1){
            $valid = true;
        }
        return $valid;
    }
    public static function sendCompanionData($id, string $action, string $payload = ""):array|bool{
        $return = false;
        if(self::validateId($id,false)){

            $socket = @stream_socket_client("tcp://127.0.0.1:25" . $id, $socketErrorCode, $socketErrorString, 1);
            if(!$socket){
                runfunction("cmd::newWindow('php\\php.exe cli.php command \"mcservers start-companion " . $id . "\" no-loop true');");
                return ['state'=>'loading','newoutput'=>''];
            }

            $data['action'] = $action;
            $data['payload'] = $payload;
    
            $data = base64_encode(json_encode($data));
    
            if(fwrite($socket,$data) === false){
                $return = false;
                goto end;
            }
    
            $result = fread($socket,1024);
            if($result === false){
                $return = false;
                goto end;
            }
    
            $result = json_decode(base64_decode($result),true);
            if($result === null){
                $return = false;
                goto end;
            }

            $return = $result;

            end:
            fclose($socket);
            return $return;
        }
        return $return;
    }
}