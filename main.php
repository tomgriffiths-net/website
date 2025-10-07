<?php
class website{
    public static function init():void{
        $defaultSettings = [
            'hosterPort' => 7000,
            'hosterIP'   => '127.0.0.1',
            'autoStartSites'  => [],
            'autoStartHoster' => true,

            'communicatorPort' => 8080,
            'communicatorIP'   => '127.0.0.1'
        ];

        foreach($defaultSettings as $settingName => $settingValue){
            settings::set($settingName, $settingValue, false);
        }

        if(settings::read('autoStartHoster') && !is_string($GLOBALS['arguments']['command'])){
            $hostPort = settings::read('hosterPort');
            $hostIp = settings::read('hosterIP');

            if(is_int($hostPort) && is_string($hostIp)){
                if(!network::ping($hostIp, $hostPort, 2)){
                    mklog(1, 'Communicator is not running, starting automatically');
                    cmd::newWindow('php\\php cli.php command "timetest website::hostSites();"');
                }
            }
            else{
                mklog(2, 'Failed to read hosterPort or hosterIP setting');
            }
        }
    }
    public static function command($line):void{
        $lines = explode(" ",$line);
        if($lines[0] === "start" || $lines[0] === "stop"){
            if(isset($lines[1])){
                $siteId = intval($lines[1]);
                if(settings::isset('sites/' . $siteId)){
                    if($lines[0] === "start"){
                        self::startSite($siteId);
                    }
                    elseif($lines[0] === "stop"){
                        self::stopSite($siteId);
                    }
                }
                else{
                    echo "Unknown site id!\n";
                }
            }
            else{
                echo "No site id provided!\n";
            }
        }
        elseif($lines[0] === "list"){
            $columnTitles = array("Site ID"=>7,"Name"=>15,"Path"=>50,"Servers"=>30);
            $rowsData = array();
            $i = 0;
            $sites = self::listSites();
            if(is_array($sites)){
                foreach($sites as $siteId => $siteData){
                    $rowsData[$i][] = $siteId;
                    $rowsData[$i][] = $siteData['name'];
                    $rowsData[$i][] = $siteData['path'];
                    $str = "";
                    foreach($siteData['servers'] as $server){
                        $str .= $server['type'];
                        if($server['type'] === "apache"){
                            $str .= "(" . $server['apacheIndex'] . ")";
                        }
                        else{
                            $str .= "(" . $server['mysqlIndex'] . ")";
                        }
                        $str .= ", ";
                    }
                    $str = substr($str,0,-2);
                    $rowsData[$i][] = $str;
                    $i++;
                }
                echo commandline_list::table($columnTitles,$rowsData);
            }
            else{
                echo "Failed to read sites list\n";
            }
        }
        elseif($lines[0] === "create"){
            if(is_dir("mywebsite")){
                mklog(2, "Unable to create default site as is has allready been created");
                return;
            }

            passwordCreation:
            echo "Please enter a password for the website:\n";
            $password = user_input::await();
            echo "Repeat:\n";
            $password2 = user_input::await();
            if($password !== $password2){
                echo "Passwords do not match!\n";
                goto passwordCreation;
            }

            $siteid = self::newSite(false,false,false,false,false,false,true,"MySite",$password);

            if(!is_int($siteid)){
                mklog(2,'Failed to get valid site id');
                return;
            }

            echo "Site ID: " . $siteid . "\n";
        }
        elseif($lines[0] === "update"){
            if(isset($lines[1])){
                $siteId = intval($lines[1]);
                if(settings::isset('sites/' . $siteId)){
                    self::updateSite($siteId);
                }
                else{
                    echo "Unknown site id!\n";
                }
            }
            else{
                echo "No site id provided!\n";
            }
        }
        else{
            echo "Unknown command!\n";
        }
    }
    //Actions
    public static function newSite(string|false $apacheRoot=false, string|false $directory=false, string|false $filesurl=false, string|false $filesdir=false, string|false $logsdir=false, string|false $tempdir=false, bool $communicator=false, string $name="MySite", string $password="1234"):int|false{
        $siteId = 1;
        while(settings::isset("sites/" . $siteId)){
            $siteId ++;
        }
        
        $cwd = getcwd();
        $dir = $cwd . "\\mywebsite";
        $copyfiles = true;
        if(is_string($directory)){
            $dir = $directory;
        }

        if(is_dir($dir)){
            $copyfiles = false;
        }
        else{
            files::ensureFolder($dir);
        }
        $dir = realpath($dir);

        if(!is_string($filesdir)){
            $filesdir = $dir . '\\website\\files';
        }
        $apacheNumber = apachemgr::newServer($dir . "/website", $name, $apacheRoot);
        if(!is_int($apacheNumber)){
            mklog(2, 'Failed to register apache server');
            return false;
        }

        if($copyfiles){
            cmd::run('robocopy packages\\website\\files\\webfiles.zip "' . $dir . '\\website" /e /v');
            cmd::run('robocopy packages\\website\\files\\webfiles-local.zip "' . $dir . '\\localfiles" /e /v');
            cmd::run('robocopy packages\\website\\files\\webfiles-data.zip "' . $dir . '\\localdata" /e /v');
            cmd::run('robocopy packages\\website\\files\\webfiles-files.zip "' . $filesdir . '" /e /v');

            if(!is_dir($dir . '\\website') || !is_dir($dir . '\\localfiles') || !is_dir($dir . '\\localdata') || !is_dir($filesdir)){
                mklog(2, 'Failed to copy some site files');
                return false;
            }
        }

        $websettings['debug'] = true;
        $websettings['filesurl'] = '/files';
        if(is_string($filesurl)){
            $websettings['filesurl'] = $filesurl;
        }
        $websettings['localdir'] = $dir . '\\localdata';
        $websettings['version'] = 1;
        $websettings['site-index'] = $siteId;
        $logsdirFinal = $cwd . '\\logs\\websites\\' . $siteId;
        if(is_string($logsdir)){
            $logsdirFinal = $logsdir;
        }
        $websettings['verbose-logging'] = false;
        $websettings['filesdir'] = $filesdir;
        $websettings['tempdir'] = $cwd . '\\temp\\websites\\' . $siteId;
        if(is_string($tempdir)){
            $websettings['tempdir'] = $tempdir;
        }
        $websettings['php-exec-path'] = $cwd . '\\php\\php.exe';
        $websettings['cli-root'] = $cwd;
        $websettings['communicator'] = ['name'=>'PHP-CLI_Website', 'password'=>communicator::getPasswordEncoded(), 'port'=>settings::read('communicatorPort'), 'ip'=>settings::read('communicatorIP')];
        $websettings['password'] = password_hash($password, PASSWORD_DEFAULT);

        if(!json::writeFile($dir . '/localdata/settings.json', $websettings, true)){
            mklog(2, 'Failed to save site settings into website folder');
            return false;
        }

        //Hard code location to settings file into website global stuff
        if(!apachemgr::setConfDirective($dir.'/localfiles/global.php', '$settingsFile = ', '"'.str_replace("\\","\\\\", $websettings['localdir'].'\\settings.json').'";')){
            mklog(2, 'Failed to save settings file location to website config');
            return false;
        }

        //Set php ini extension dir to absolute path as httpd is not in same cwd as php-cli
        if(!apachemgr::setConfDirective('php/php.ini', 'extension_dir =', '"'.str_replace("\\","\\\\", getcwd().'\\php\\ext').'"')){
            mklog(2, 'Failed to make php extensions dir an absolute path');
            return false;
        }

        if(!settings::set('sites/' . $siteId,
            array(
                'name'=>$name,
                'path'=>$dir,
                'filesdir'=>$filesdir,
                'logsdir'=>$logsdirFinal,
                'tempdir'=>$websettings['tempdir'],
                "servers"=>array(
                    array(
                        "type"=>"apache",
                        "apacheIndex"=>$apacheNumber
                    )
                ),
                "logsCollector"=>true,
                "communicator"=>$communicator
            )
        )){
            mklog(2, 'Failed to save site settings');
            return false;
        }

        mklog(1, 'Created site ' . $siteId);

        return $siteId;
    }
    public static function startSite(int $siteId):bool{
        return self::sendCommandBool('startSite', [$siteId]);
    }
    public static function stopSite(int $siteId):bool{
        return self::sendCommandBool('stopSite', [$siteId]);
    }
    public static function updateSite(int $siteId):bool{

        $settings = settings::read('sites/' . $siteId);
        if(!is_array($settings)){
            mklog(2, 'Could not find site ' . $siteId);
            return false;
        }

        if(self::isCommunicatorOn()){
            echo "\n  If any other packages have been updated, communicator will have to be restarted.\n\n";
        }

        mklog(1, 'Copying site files for site ' . $siteId);
        cmd::run('robocopy packages\\website\\files\\webfiles.zip "' . $settings['path'] . '\\website" /e /v /mir');
        cmd::run('robocopy packages\\website\\files\\webfiles-local.zip "' . $settings['path'] . '\\localfiles" /e /v /mir');
        cmd::run('robocopy packages\\website\\files\\webfiles-files.zip "' . $settings['filesdir'] . '" /e /v');

        //Hard code location to settings file into website global stuff
        if(!apachemgr::setConfDirective($settings['path'].'/localfiles/global.php', '$settingsFile = ', '"'.str_replace("\\","\\\\", $settings['path'].'\\localdata\\settings.json').'";')){
            mklog(2, 'Failed to save settings file location to website config');
            return false;
        }

        return true;
    }
    public static function removeSite(int $siteId):bool{
        self::stopSite($siteId);
        return settings::unset('sites/' . $siteId);
    }
    //Hoster
    public static function hostSites():void{
        exec('title Websites host');
        cli_formatter::clear();
        echo "Website hoster process...\n";

        $hostPort = settings::read('hosterPort');
        $hostIp = settings::read('hosterIP');

        if(!is_int($hostPort) || !is_string($hostIp)){
            mklog(2,'Failed to read hosterPort or hosterIP setting');
            return;
        }

        $socketServer = communicator::createServer($hostIp, $hostPort, 5, $socketErrorCode, $socketErrorString);
        if(!$socketServer){
            mklog(2, 'Failed to set up communications: ' . $socketErrorString);
        }

        $apacheProcs = [];
        $logCollectors = [];
        $mysqls = [];
        $break = false;

        $autoStarts = settings::read('autoStartSites');
        if(is_array($autoStarts)){
            foreach($autoStarts as $siteId){
                if(is_int($siteId)){
                    $siteData = settings::read('sites/' . $siteId);
                    if(is_array($siteData)){
                        $autoStartResponse = [];
                        if(!self::hoster_startSite($siteId, $siteData, $autoStartResponse)){
                            mklog(2, 'Failed to automatically start site ' . $siteId);
                        }
                        if(isset($autoStartResponse['error']) && !empty($autoStartResponse['error'])){
                            mklog(2, 'Autostarting site ' . $siteId . ' gave an error: ' . $autoStartResponse['error']);
                        }
                    }
                }
            }
        }

        while(true){
            $clientSocket = communicator::acceptConnection($socketServer, 5);
            if($clientSocket){
                $response = [
                    'success' => false,
                    'error' => '',
                    'data' => [],
                    //'servers' => []
                ];
                $message = communicator::receiveData($clientSocket);
                if(!is_array($message) || !isset($message['action']) || !is_string($message['action']) || !isset($message['sites']) || !is_array($message['sites']) || !array_is_list($message['sites']) || !isset($message['servers']) || !is_array($message['servers']) || !array_is_list($message['servers'])){
                    mklog(1, 'Received unexpected message, ignoring' . ($message === false ? ' (likely a ping)' : ''));
                    communicator::close($clientSocket);
                    continue;
                }

                $sites = [];
                foreach($message['sites'] as $siteId){
                    if(!is_int($siteId)){
                        mklog(2, 'Received a non integer site number');
                        $response['error'] = 'Found a non integer site number';
                        goto respond;
                    }

                    $siteData = settings::read('sites/' . $siteId);
                    if(!is_array($siteData)){
                        mklog(2, 'Could not find site ' . $siteId);
                        $response['error'] = 'Could not find site ' . $siteId;
                        goto respond;
                    }

                    $sites[$siteId] = $siteData;
                }

                $serversList = [];
                if(!empty($message['servers'])){
                    if(empty($sites)){
                        mklog(2, 'No sites specified for servers');
                        $response['error'] = 'No sites specified for servers';
                        goto respond;
                    }

                    foreach($message['servers'] as $server){
                        if(!is_int($server)){
                            mklog(2, 'Received a non integer server number');
                            $response['error'] = 'Found a non integer server number';
                            goto respond;
                        }

                        if($server < 0){
                            foreach($sites as $siteId => $siteData){
                                if(!$siteData['logsCollector']){
                                    mklog(2, 'Site ' . $siteId . ' does not have logsCollector enabled');
                                    $response['error'] = 'Site ' . $siteId . ' does not have logsCollector enabled';
                                    goto respond;
                                }
                            }
                        }
                        else{
                            foreach($sites as $siteId => $siteData){
                                if(!isset($siteData['servers'][$server])){
                                    mklog(2, 'Could not find server ' . $server . ' in site ' . $siteId);
                                    $response['error'] = 'Could not find server ' . $server . ' in site ' . $siteId;
                                    goto respond;
                                }
                            }
                        }

                        $serversList[] = $server;
                    }
                }

                if($message['action'] === "startSite"){
                    foreach($sites as $siteId => $siteData){
                        if(!self::hoster_startSite($siteId, $siteData, $response)){
                            goto respond;
                        }
                    }
                }
                elseif($message['action'] === "stopSite"){
                    foreach($sites as $siteId => $siteData){
                        foreach($siteData['servers'] as $server){
                            if(!self::hoster_stopServer($server, $response, $apacheProcs, $mysqls)){
                                goto respond;
                            }
                        }
                        if($siteData['logsCollector']){
                            self::hoster_disableLogCollector($siteId, $logCollectors);
                        }
                    }
                }
                elseif($message['action'] === "exit"){
                    foreach($apacheProcs as $apacheId => $apacheProc){
                        if(!self::hoster_stopApacheServer($apacheId, $response, $apacheProcs)){
                            goto respond;
                        }
                    }

                    foreach($mysqls as $mysqlId => $mysqlRunning){
                        if(!self::hoster_stopMysqlServer($mysqlId, $response, $mysqls)){
                            goto respond;
                        }
                    }

                    foreach($logCollectors as $siteId => $logCollector){
                        self::hoster_disableLogCollector($siteId, $logCollectors);
                    }

                    $break = true;
                }
                elseif($message['action'] === "getRunningServers"){
                    $response['success'] = true;
                    $response['data']['apache'] = [];
                    $response['data']['mysql'] = [];
                    $response['data']['logCollectors'] = [];

                    foreach($apacheProcs as $apacheIndex => $process){
                        $response['data']['apache'][] = $apacheIndex;
                    }
                    foreach($mysqls as $mysqlIndex => $mysqlRunning){
                        $response['data']['mysql'][] = $mysqlIndex;
                    }
                    foreach($logCollectors as $siteId => $logCollector){
                        $response['data']['logCollectors'][] = $siteId;
                    }
                }
                elseif($message['action'] === "startServer"){
                    foreach($sites as $siteId => $siteData){
                        foreach($serversList as $server){
                            if($server < 0){
                                if(self::hoster_enableLogCollector($siteId, $siteData, $logCollectors)){
                                    $response['success'] = true;
                                }
                                else{
                                    $response['error'] = 'LogCollector is already running for site ' . $siteId;
                                    goto respond;
                                }
                            }
                            else{
                                if(!self::hoster_startServer($siteData['servers'][$server], $response, $apacheProcs, $mysqls)){
                                    goto respond;
                                }
                            }
                        }
                    }
                }
                elseif($message['action'] === "stopServer"){
                    foreach($sites as $siteId => $siteData){
                        foreach($serversList as $server){
                            if($server < 0){
                                if(self::hoster_disableLogCollector($siteId, $logCollectors)){
                                    $response['success'] = true;
                                }
                                else{
                                    $response['error'] = 'LogCollector was not running for site ' . $siteId;
                                    goto respond;
                                }
                            }
                            else{
                                if(!self::hoster_stopServer($siteData['servers'][$server], $response, $apacheProcs, $mysqls)){
                                    goto respond;
                                }
                            }
                        }
                    }
                }
                else{
                    mklog(2,'Received an unknown command');
                    $response['error'] = 'Unknown command';
                }

                respond:

                if(!communicator::sendData($clientSocket, $response)){
                    mklog(2, 'Failed to send response');
                }

                communicator::close($clientSocket);
            }

            foreach($apacheProcs as $apacheIndex => $process){
                $procinfo = proc_get_status($process);
                if(!$procinfo['running']){
                    mklog(2, 'Process for apache server ' . $apacheIndex . ' has exited unexpectedly with code ' . $procinfo['exitcode']);
                    unset($apacheProcs[$apacheIndex]);
                }
            }

            self::hoster_logCollectors($logCollectors);

            if($break){
                break;
            }
        }

        mklog(1, 'Closing website hoster');

        communicator::close($socketServer);

        return;
    }
    private static function hoster_startApacheServer(int $apacheId, array &$response, array &$apacheProcs):bool{
        if(isset($apacheProcs[$apacheId])){
            $response['success'] = true;
            return true;
        }

        mklog(1, 'Starting apache server ' . $apacheId);
        if(apachemgr::start($apacheId)){
            $response['success'] = true;
            $apacheProcs[$apacheId] = apachemgr::getServerProc($apacheId);
            mklog(1, 'Started apache server ' . $apacheId);
            return true;
        }
        else{
            mklog(2, 'Failed to start apache server ' . $apacheId);
            $response['error'] = 'Failed to start apache server ' . $apacheId;
            return false;
        }
    }
    private static function hoster_startMysqlServer(int $mysqlId, array &$response, array &$mysqls):bool{
        if(isset($mysqls[$mysqlId])){
            $response['success'] = true;
            return true;
        }

        mklog(1, 'Starting mysql server ' . $mysqlId);
        if(mysql::start($mysqlId)){
            $mysqls[$mysqlId] = true;
            $response['success'] = true;
            mklog(1, 'Started mysql server ' . $mysqlId);
            return true;
        }
        else{
            mklog(2, 'Failed to start mysql server ' . $mysqlId);
            $response['error'] = 'Failed to start mysql server ' . $mysqlId;
            return false;
        }
    }
    private static function hoster_stopApacheServer(int $apacheId, array &$response, array &$apacheProcs):bool{
        if(!isset($apacheProcs[$apacheId])){
            $response['success'] = true;
            return true;
        }

        mklog(1, 'Stopping apache server ' . $apacheId);
        $exit = apachemgr::stop($apacheId);
        if($exit === false){
            mklog(2, 'Failed to stop apache server ' . $apacheId);
            $response['error'] = 'Failed to stop apache server ' . $apacheId;
            return false;
        }
        else{
            $response['success'] = true;
            if($exit !== 0){
                mklog(2, 'Apache server ' . $apacheId . ' exited with a non-zero code of ' . $exit);
                $response['error'] = 'Apache server ' . $apacheId . ' has been stopped in its tracks';
            }
            unset($apacheProcs[$apacheId]);
            mklog(1, 'Stopped apache server ' . $apacheId);
            return true;
        }
    }
    private static function hoster_stopMysqlServer(int $mysqlId, array &$response, array &$mysqls):bool{
        if(!isset($mysqls[$mysqlId])){
            $response['success'] = true;
            return true;
        }

        if(mysql::stop($mysqlId)){
            $response['success'] = true;
            mklog(1, 'Stopped mysql server ' . $mysqlId);
            unset($mysqls[$mysqlId]);
            return true;
        }
        else{
            mklog(2, 'Failed to stop mysql server ' . $mysqlId);
            $response['error'] = 'Failed to stop mysql server ' . $mysqlId;
            return false;
        }
    }
    private static function hoster_logCollectors(array $logCollectors):void{
        foreach($logCollectors as $siteId => $logCollector){
            if((time() - $logCollector['lasttime']) < 60){
                continue;
            }

            if(!files::ensureFolder($logCollector['logsdir'])){
                mklog(2, 'Failed to create directory ' . $logCollector['logsdir']);
                continue;
            }
            if(!files::ensureFolder($logCollector['tempdir'])){
                mklog(2, 'Failed to create directory ' . $logCollector['tempdir']);
                continue;
            }

            $templogs = glob($logCollector['tempdir'] . "\\*.txt");
            if(!is_array($templogs)){
                mklog(2, 'Failed to get list of files from ' . $logCollector['tempdir']);
                continue;
            }

            foreach($templogs as $templog){
                $logtype = substr($templog,strripos($templog,"-")+1);
                $logtype = substr($logtype,0,strpos($logtype,"."));

                $typeFolder = $logCollector['logsdir'] . "\\" . $logtype;
                if(!files::ensureFolder($typeFolder)){
                    mklog(2, 'Failed to create directory ' . $typeFolder);
                    continue;
                }

                $templogContents = file_get_contents($templog);
                if(!is_string($templogContents)){
                    mklog(2, 'Failed to read file ' . $templog);
                    continue;
                }

                $logfile = $typeFolder . "\\" . date('Y-m') . ".txt";
                if(!file_put_contents($logfile, $templogContents, FILE_APPEND)){
                    mklog(2, 'Failed to add log to main log file ' . $logfile);
                    continue;
                }

                if(!unlink($templog)){
                    mklog(2, 'Failed to remove old temporary log ' . $templog);
                }
            }

            $logCollectors[$siteId]['lasttime'] = time();
        }
    }
    private static function hoster_startServer(array $serverData, array &$response, array &$apacheProcs, array &$mysqls):bool{
        if(!isset($serverData['type']) || !is_string($serverData['type'])){
            return false;
        }
        
        if($serverData['type'] === "apache"){
            if(!self::hoster_startApacheServer($serverData['apacheIndex'], $response, $apacheProcs)){
                return false;
            }
        }
        elseif($serverData['type'] === "mysql"){
            if(!self::hoster_startMysqlServer($serverData['mysqlIndex'], $response, $mysqls)){
                return false;
            }
        }
        else{
            mklog(2, 'Unknown server type: ' . $serverData['type']);
        }

        return true;
    }
    private static function hoster_stopServer(array $serverData, array &$response, array &$apacheProcs, array &$mysqls):bool{
        if(!isset($serverData['type']) || !is_string($serverData['type'])){
            return false;
        }
        
        if($serverData['type'] === "apache"){
            if(!self::hoster_stopApacheServer($serverData['apacheIndex'], $response, $apacheProcs)){
                return false;
            }
        }
        elseif($serverData['type'] === "mysql"){
            if(!self::hoster_stopMysqlServer($serverData['mysqlIndex'], $response, $mysqls)){
                return false;
            }
        }
        else{
            mklog(2, 'Unknown server type: ' . $serverData['type']);
        }

        return true;
    }
    private static function hoster_enableLogCollector(int $siteId, array $siteData, array &$logCollectors):bool{
        if(isset($logCollectors[$siteId])){
            return false;
        }

        $logCollectors[$siteId] = [
            'logsdir' => $siteData['logsdir'],
            'tempdir' => $siteData['tempdir'],
            'lasttime' => 0
        ];
        mklog(1, 'Enabled logs collector for site ' . $siteId);

        return true;
    }
    private static function hoster_disableLogCollector(int $siteId, &$logCollectors):bool{
        if(isset($logCollectors[$siteId])){
            unset($logCollectors[$siteId]);
            mklog(1, 'Disabled logs collector for site ' . $siteId);
            return true;
        }
        return false;
    }
    private static function hoster_serverChanged(int $siteId, int $serverNumber, array &$response):void{
        if(!isset($response['servers'][$siteId])){
            $response['servers'][$siteId] = [];
        }

        if(!in_array($serverNumber, $response['servers'][$siteId])){
            $response['servers'][$siteId][] = $serverNumber;
        }
    }
    private static function hoster_startSite(int $siteId, array $siteData, array &$response):bool{
        if(!isset($siteData['servers']) || !is_array($siteData['servers']) || empty($siteData['servers'])){
            $response['error'] = 'The specified site has no servers';
            return false;
        }
        
        $return = true;

        if(isset($siteData['communicator']) && $siteData['communicator']){
            if(!self::isCommunicatorOn()){
                mklog(1, 'Communicator is not running, starting automatically');
                cmd::newWindow('php\php cli.php command "communicator begin"');
            }
        }

        foreach($siteData['servers'] as $server){
            if(!self::hoster_startServer($server, $response, $apacheProcs, $mysqls)){
                $return = false;
            }
        }

        if($siteData['logsCollector']){
            if(!self::hoster_enableLogCollector($siteId, $siteData, $logCollectors)){
                $response['error'] = "Failed to start logs collector for site " . $siteId;
                $return = false;
            }
        }

        return $return;
    }
    public static function sendCommand(string $command, array $sites=[], array $servers=[]):array|false{
        foreach(['sites','servers'] as $thing){
            if(!array_is_list($$thing)){
                mklog(2, 'Non list site or server list passed');
                return false;
            }
            foreach($$thing as $eeee){
                if(!is_int($eeee)){
                    mklog(2, 'Non integer site or server id passed');
                    return false;
                }
            }
        }

        $hosterPort = settings::read('hosterPort');
        $hosterIp = settings::read('hosterIP');
        if(!is_int($hosterPort) || !is_string($hosterIp)){
            mklog(2, 'Failed to get hoster connection info');
            return false;
        }

        $stream = communicator::connect($hosterIp, $hosterPort, 5, $errorCode, $errorString);
        if(!$stream){
            mklog(2, 'Failed to connect to site hoster');
            return false;
        }

        if(!communicator::sendData($stream, ['action'=>$command, 'sites'=>$sites, 'servers'=>$servers])){
            mklog(2, 'Failed to send data to site hoster');
            return false;
        }

        $response = communicator::receiveData($stream);
        if(!is_array($response)){
            mklog(2, 'Did not receive expected data');
            return false;
        }

        if(!isset($response['success']) || !is_bool($response['success'])){
            mklog(2, 'Received unexpected data');
            return false;
        }

        return $response;
    }
    public static function sendCommandBool(string $command, array $sites=[], array $servers=[]):bool{
        $response = self::sendCommand($command, $sites, $servers);
        if(!is_array($response)){
            mklog(2, 'Failed to send command to site hoster');
            return false;
        }

        if(!$response['success']){
            if(!isset($response['error']) || empty($response['error'])){
                mklog(2, 'Generic failure');
                return false;
            }

            mklog(2, 'Failed with error: ' . $response['error']);
            return false;
        }

        return true;
    }
    //Queries
    public static function isCommunicatorOn():bool{
        $communicatorIp = settings::read('communicatorIP');
        $communicatorPort = settings::read('communicatorPort');
        if(is_string($communicatorIp) && is_int($communicatorPort)){
            return network::ping($communicatorIp, $communicatorPort, 1);
        }
        else{
            mklog(2, 'Failed to get communicator connection info');
            return false;
        }
    }
    public static function listSites():array|false{
        return settings::read('sites');
    }
    public static function numberOfSites():int|false{
        $sites = settings::read('sites');
        if(!is_array($sites)){
            return false;
        }
        return count($sites);
    }
    //Web helpers
    public static function web_getRunningSites():array|false{
        $sites = settings::read('sites');
        if(!is_array($sites)){
            return false;
        }

        if(empty($sites)){
            return [];
        }

        $result = self::sendCommand('getRunningServers');
        if(!is_array($result) || !isset($result['data']) || !isset($result['data']['apache']) || !isset($result['data']['mysql']) || !isset($result['data']['logCollectors'])){
            return false;
        }

        $runningServers = [];
        $siteStates = [];
        foreach($sites as $siteId => $siteData){
            if(!isset($siteData['servers']) || !is_array($siteData['servers'])){
                continue;
            }

            if($siteData['logsCollector']){
                if(in_array($siteId, $result['data']['logCollectors'])){
                    $runningServers[$siteId][] = -1;
                    self::web_getRunningSites_siteThingOnOff($siteId, true, $siteStates);
                }
                else{
                    self::web_getRunningSites_siteThingOnOff($siteId, false, $siteStates);
                }
            }

            foreach($siteData['servers'] as $serverNumber => $server){
                foreach(['apache','mysql'] as $thing){
                    if($server['type'] === $thing){
                        if(in_array($server[$thing.'Index'], $result['data'][$thing])){
                            $runningServers[$siteId][] = $serverNumber;
                            self::web_getRunningSites_siteThingOnOff($siteId, true, $siteStates);
                        }
                        else{
                            self::web_getRunningSites_siteThingOnOff($siteId, false, $siteStates);
                        }
                    }
                }
            }
        }

        return [
            'servers'=>$runningServers,
            'sites'=>$siteStates
        ];
    }
    private static function web_getRunningSites_siteThingOnOff(int $siteId, bool $on, array &$siteStates):void{
        if(!isset($siteStates[$siteId])){
            $siteStates[$siteId] = ($on ? "on" : "off");
        }
        else{
            if($siteStates[$siteId] === "off" && $on || $siteStates[$siteId] === "on" && !$on){
                $siteStates[$siteId] = "onish";
            }
        }
    }
}