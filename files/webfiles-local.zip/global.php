<?php
startup::checkGetRequest();

//NEXT_LINE_IS_SETTINGS_FILE
$settingsFile = "D:\\Projects\\PHP-CLI\\mywebsite\\localdata\\settings.json";
$globalSettings = startup::loadGlobalSettings($settingsFile);

startup::setTimeZone();
$uri = startup::setupUri();
startup::startSession();
$useruid = startup::setupUseruid();

if(substr($uri,1,5) !== "login"){
    $loginRequired = true;
}
if(isset($loginRequired)){
    if($loginRequired === true){
        startup::ensureLogin($useruid);
    }
}

if(substr($uri,1,5) === "admin"){
    $adminRequired = true;
}
if(isset($adminRequired)){
    if($adminRequired === true){
        startup::ensureAdmin($useruid);
    }
}

if(communicator::getName() === false){
    communicator::setName("PHP-CLI_Website");
}
if(communicator::getPasswordEncoded() === false){
    communicator::setPassword("password");
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
        if(is_file($settingsFile)){
            return json::readFile($settingsFile);
        }
        else{
            echo "File not found";
            exit;
        }
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
        if($useruid === false){
            mklog('general','User prompted for login at ' . $GLOBALS['uri'],true);
            html::loadurl('/login/');
        }
    }
    public static function ensureAdmin($useruid){
        if($useruid !== "admin"){
            html::loadurl('/');
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
    //Set correct time zone
    date_default_timezone_set("Europe/London");
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

        json::writeFile($globalSettings['localdir'] . "\\settings.json",$globalSettings,true);

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
        $headerData = json::readFile($GLOBALS['localDir'] . "\\headers\\" . $headerId . ".json",true,array("webName"=>"My Website","webNameLink"=>"/","buttons"=>array(0=>array("name"=>"Home","link"=>"/"))));
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
class users{
    public static function uidExists($username){
        $username = preg_replace('/[^a-z0-9_]/', '_', strtolower($username));
        return is_file($GLOBALS['globalSettings']['localdir'] . '\\users\\' . $username . '\\login-info.json');
    }
    public static function usersName($useruid):string{
        $return = "";
        if(self::uidExists($useruid)){
            $return = $useruid;
            $name = json::readFile($GLOBALS['globalSettings']['localdir'] . "\\users\\" . $useruid . "\\login-info.json")['name'];
            if(!empty($name)){
                $return = $name;
            }
        }
        return $return;
    }
    public static function loginUser($username, $pwd){
        if(self::uidExists($username)){
            $username = preg_replace('/[^a-z0-9_]/', '_', strtolower($username));
            $userFile = $GLOBALS['localDir'] . "\\users\\" . $username . "\\login-info.json";
            $userData = json::readFile($userFile);
    
            if(password_verify($pwd, $userData['password'])){
                session_start();
                $_SESSION["useruid"] = $userData['useruid'];
                if($userData['useruid'] === "admin"){
                    html::loadurl('/admin/');
                }
                else{
                    html::loadurl('/');
                }
            }
            else{
                html::loadurl('/login/?error=wrongpassword');
            }
        }
        else{
            html::loadurl('/login/?error=userdoesnotexist');
        }
    }
}
class communicator{
    // Settings
    public static function setName(string $name):bool{
        return setSetting('communicator/name', $name, true);
    }
    public static function getName():string|bool{
        global $globalSettings;
        if(isset($globalSettings['communicator']['name'])){
            return $globalSettings['communicator']['name'];
        }
        return false;
    }
    public static function setPassword(string $password):bool{
        return setSetting('communicator/password',base64_encode($password),true);
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
class communicator_client{
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
        $socket = communicator::connect($ip,$port,false,$socketError,$socketErrorString);
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

        $data['name'] = communicator::getName();
        $data['password'] = communicator::getPasswordEncoded();

        $data = base64_encode(json_encode($data));

        if(!communicator::send($socket,$data)){
            $return["error"] = "Error sending data";
            goto end;
        }

        $result = communicator::receive($socket);
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
        communicator::close($socket);
        return $return;
    }
}
class data_types{
    public static function string_to_float(string $string):float{
        //Check if string is a number
        if(is_numeric($string)){
            //Return value
            $return = $string;
        }
        else{
            //Return 0
            $return = 0;
        }
        //Convert return to float
        return floatval($return);
    }
    public static function string_to_integer(string $string):int{
        //Check if string is a number
        if(is_numeric($string)){
            //Return value
            $return = $string;
        }
        else{
            //Return 0
            $return = 0;
        }
        //Convert return to integer
        return intval($return);
    }
    public static function string_to_boolean(string $string):bool{
        //Assume that the value is false
        $return = false;
        //Check if string is "true"
        if($string === "true"){
            $return = true;
        }
        return $return;
    }
    public static function boolean_to_string(bool $boolean):string{
        //Assume that string is representing false
        $return = "false";
        //Check if boolean is true
        if($boolean === true){
            //Return "true"
            $return = "true";
        }
        return $return;
    }
    public static function convert_string(string $value):int|float|bool|string{
        $return = $value;
        if(is_numeric($value)){
            //Check if the string contains a point
            if(strpos($value,'.')){
                //Convert string to float
                $return = self::string_to_float($value);
            }
            else{
                //Convert string to integer
                $return = self::string_to_integer($value);
            }
        }
        elseif($value === "true" || $value === "false"){
            //convert string to boolean
            $return = self::string_to_boolean($value);
        }
        return $return;
    }
    public static function convert_to_string(mixed $value):string{
        $return = $value;
        if($value === true || $value === false){
            $return = self::boolean_to_string($value);
        }
        return $return;
    }
    public static function xmlStringToArray(string $xml):array{
        $xml1 = simplexml_load_string($xml);
        return json_decode(json_encode($xml1),true);
    }
}
class files{
    public static function globRecursive(string $base, string $pattern, $flags = 0):array{
        $flags = $flags & ~GLOB_NOCHECK;
        
        if (substr($base, -1) !== DIRECTORY_SEPARATOR) {
            $base .= DIRECTORY_SEPARATOR;
        }
    
        $files = glob($base.$pattern, $flags);
        if (!is_array($files)) {
            $files = [];
        }
    
        $dirs = glob($base.'*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK);
        if (!is_array($dirs)) {
            return $files;
        }
        
        foreach ($dirs as $dir) {
            $dirFiles = self::globRecursive($dir, $pattern, $flags);
            $files = array_merge($files, $dirFiles);
        }
    
        return $files;
    }
    public static function ensureFolder(string $dir):bool{
        if(is_dir($dir)){
            return true;
        }
        elseif(is_file($dir)){
            return false;
        }
        else{
            return self::mkFolder($dir);
        }
    }
    public static function mkFolder(string $path):bool{
        return mkdir($path,0777,true);
    }
    public static function mkFile(string $path, $data, $fopenMode = "w"):bool|int{
        $dir = self::getFileDir($path);
        if(!is_dir($dir)){
            self::mkFolder($dir);
        }
        $stream = fopen($path,$fopenMode);
        $return = fwrite($stream,$data);
        fclose($stream);
        return $return;
    }
    public static function getFileDir(string $path):string{
        $path = str_replace("/","\\",$path);
        $pos = strripos($path,"\\");
        $dir = substr($path,0,$pos);
        return $dir;
    }
    public static function getFileName(string $path):string{
        $path = str_replace("/","\\",$path);
        $pos = strripos($path,"\\");
        $file = substr($path,$pos+1);
        return $file;
    }
    public static function copyFile(string $pathFrom, string $pathTo):bool{
        $success = false;
        $dir = self::getFileDir($pathTo);
        if(!is_file($pathFrom)){
            goto end;
        }
        if(!is_dir($dir)){
            self::mkFolder($dir);
        }

        $success = copy($pathFrom,$pathTo);

        end:
        return $success;
    }
    public static function validatePath(string $path, bool $addquotes = false):string{
        $path = str_replace("/","\\",$path);
        if(strpos($path," ") && $addquotes){
            $path = '"' . $path . '"';
        }
        return $path;
    }
    public static function getFileExtension(string $fileName):string{
        $ext = "";
        $pos = strripos($fileName,".");
        if($pos !== false){
            $ext = substr($fileName,$pos+1);
        }
        return $ext;
    }
}
class json{
    public static function addToFile($path,$entryKey,$entryValue,$addToTop=true){
        $existing = self::readFile($path);
        if($addToTop === true){
            $new[$entryKey] = $entryValue;
        }
        foreach($existing as $key => $value){
            $new[$key] = $value;
        }
        if($addToTop === false){
            $new[$entryKey] = $entryValue;
        }
        self::writeFile($path,$new,true);
    }
    public static function readFile($path,$createIfNonexistant=true,$expectedValues=array()){
        //Chech if file exists
        $existing = false;

        $existing = is_file($path);

        if($existing){
            //Check if file can be read
            $json = file_get_contents($path);
            if($json === false){
                //Error is file cannot be read
                mklog("warning","Failed to read from file: ". $path);
            }
            //If file can be read, return array of json values
            else{
                return json_decode($json,true);
            }
        }
        else{
            if($createIfNonexistant){
                mklog("general","Attempt made to read from nonexistant file: " . $path . ", creating file");
                txtrw::mktxt($path,json_encode($expectedValues,JSON_PRETTY_PRINT));
                return $expectedValues;
            }
            else{
                mklog("warning","Attempt made to read from nonexistant file: " . $path);
            }
        }
    }
    public static function writeFile($path,$array,$overwrite=false){
        //Write file with json text as contents
        txtrw::mktxt($path,json_encode($array,JSON_PRETTY_PRINT),$overwrite);
    }
}
class math{
    public static function getClosest($closeNumber,$numberArray){
        $closest = null;
        foreach ($numberArray as $item) {
           if ($closest === null || abs($closeNumber - $closest) > abs($item - $closeNumber)) {
              $closest = $item;
           }
        }
        return $closest;
    }
    public static function tension_smooth_pulley(float $A_mass_kg, float $B_mass_kg, float $gravity = 9.81):float{
        $accel = self::acceleration_smooth_pulley($A_mass_kg,$B_mass_kg,$gravity);
        $ma = $B_mass_kg * $accel;
        $bg = $B_mass_kg * $gravity;
        $t = 0;
        if($A_mass_kg > $B_mass_kg){
            $t = $ma + $bg;
        }
        elseif($B_mass_kg > $A_mass_kg){
            $t = $bg - $ma;
        }

        return (float) $t;

    }
    public static function acceleration_smooth_pulley(float $A_mass_kg, float $B_mass_kg, float $gravity = 9.81):float{
        $A = $A_mass_kg * $gravity;
        $B = $B_mass_kg * $gravity;
        if($A > $B){
            $Fsum = $A - $B;
        }
        elseif($B > $A){
            $Fsum = $B - $A;
        }
        else{
            return (float) 0;
        }
        $accelCoeff = $A_mass_kg + $B_mass_kg;
        $accel = $Fsum / $accelCoeff;

        return (float) $accel;

    }
}
class network{
    public static function ping($ip,$port,$timeout=0.2):bool{
        //Ping ip and port
        $conectionStream = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        //Return true on response, false on failure
        if($conectionStream !== false) {
            return true;
        }
        else{
            return false;
        }
    }
}
class time{
    public static function stamp(){
        return floor(microtime(true));
    }
    public static function millistamp(){
        return floor(microtime(true)*1000);
    }
}
class txtrw{
    public static function mktxt($file,$content,$overwrite = false){
        //Check if file allready exists
        if(is_file($file)){
            //Mark file as not writeable
            mklog("general","Text file: " . $file . " allready exists");
            $writeFile = false;
        }
        else{
            //Mark file as writeable
            $writeFile = true;
        }
    
        //Write if overwite is true
        if($overwrite === true){
            mklog("general","Overwriting text file: " . $file);
            $writeFile = true;
        }
    
        if($writeFile){
            //Seperate directory from file name
            $file = str_replace('/',"\\",$file);
            $dir = substr($file,0,strripos($file,"\\"));
            //Check if directory does not exist
            if($dir != "" && $dir != " "){
                if(!is_dir($dir)){
                    mklog("general","File creation attempt in nonexistant directory: " . $dir . ", creating directory");
                    //Create required directory
                    if(mkdir($dir,0777,true) == false){
                        mklog("warning","Unable to create directory: " . $dir);
                    }
                }
            }
            //Open file in write mode
            $f = fopen($file,"w");
            //Log error if file cannot be accessed
            if($f === false){
                mklog("warning","Unable to access file: ". $file);
            }
            //Write file contents
            if(fwrite($f,$content) === false){
                //Log if file cannot be written to
                mklog("warning","Unable to write to file: ". $file);
            }
            //Close file
            fclose($f);
        }
    }
    public static function readtxt($file){
        //Check if file exists
        if(!is_file($file)){
            //Create file if it does not exist
            mklog("general","Attempt made to read from file that does not exist, creating file with no contents");
            self::mktxt($file,"");
        }
        else{
            //Return file contents if it exists
            $filecontents = file_get_contents($file);
            if($filecontents === false){
                mklog("error","Unable to read from file: " . $file);
            }
            else{
                return $filecontents;
            }
        }
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
                communicator_client::runfunction("cmd::newWindow('php\\php.exe cli.php command \"mcservers start-companion " . $id . "\" no-loop true');");
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