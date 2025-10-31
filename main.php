<?php
class website{
    private static $apacheProcs = [];
    private static $logCollectors = [];
    private static $mysqls = [];
    public static function init():void{
        $defaultSettings = [
            'autoStartSites'  => [],
            'communicatorPort' => 8080,
            'communicatorIP'   => '127.0.0.1'
        ];

        foreach($defaultSettings as $settingName => $settingValue){
            settings::set($settingName, $settingValue, false);
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


        echo "\n  Communicator may have to be restarted if it contains outdated code.\n\n";

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
    public static function hoster_startAutostarts():bool{
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if(!isset($backtrace[2]['class']) || $backtrace[2]['class'] !== "communicator_server"){
            mklog(1, 'You cannot call the hoster_startAutostarts outside of communicator_server');
            return false;
        }

        $autoStarts = settings::read('autoStartSites');
        if(!is_array($autoStarts)){
            mklog(2, 'Failed to read autostart settings');
            return false;
        }

        foreach($autoStarts as $siteId){
            if(is_int($siteId)){
                $siteData = settings::read('sites/' . $siteId);
                if(is_array($siteData)){
                    $autoStartResponse = [];
                    if(!self::hoster_startSite($siteId, $siteData, $autoStartResponse, self::$apacheProcs, self::$mysqls, self::$logCollectors)){
                        mklog(2, 'Failed to automatically start site ' . $siteId);
                    }
                    if(isset($autoStartResponse['error']) && !empty($autoStartResponse['error'])){
                        mklog(2, 'Autostarting site ' . $siteId . ' gave an error: ' . $autoStartResponse['error']);
                    }
                }
            }
        }

        return true;
    }
    public static function hoster_checkStuff():void{
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if(!isset($backtrace[2]['class']) || $backtrace[2]['class'] !== "communicator_server"){
            mklog(1, 'You cannot call the hoster_checkStuff outside of communicator_server');
            return;
        }

        foreach(self::$apacheProcs as $apacheIndex => $process){
            $procinfo = proc_get_status($process);
            if(!$procinfo['running']){
                mklog(2, 'Process for apache server ' . $apacheIndex . ' has exited unexpectedly with code ' . $procinfo['exitcode']);
                unset(self::$apacheProcs[$apacheIndex]);
            }
        }

        self::hoster_logCollectors(self::$logCollectors);
    }
    public static function hoster_run(string $action, array $sites, array $servers):array{
        $response = [
            'success' => false,
            'error' => '',
            'data' => [],
        ];

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if(!isset($backtrace[2]['class']) || $backtrace[2]['class'] !== "communicator_server"){
            mklog(1, 'You cannot call the hoster_run outside of communicator_server');
            $response['error'] = 'You cannot call the hoster_run outside of communicator_server';
            return $response;
        }

        if(!array_is_list($sites) || !array_is_list($servers)){
            mklog(1, 'Received unexpected message');
            return $response;
        }

        $sitesList = [];
        foreach($sites as $siteId){
            if(!is_int($siteId)){
                mklog(2, 'Received a non integer site number');
                $response['error'] = 'Found a non integer site number';
                return $response;
            }

            $siteData = settings::read('sites/' . $siteId);
            if(!is_array($siteData)){
                mklog(2, 'Could not find site ' . $siteId);
                $response['error'] = 'Could not find site ' . $siteId;
                return $response;
            }

            $sitesList[$siteId] = $siteData;
        }

        $serversList = [];
        if(!empty($servers)){
            if(empty($sitesList)){
                mklog(2, 'No sites specified for servers');
                $response['error'] = 'No sites specified for servers';
                return $response;
            }

            foreach($servers as $server){
                if(!is_int($server)){
                    mklog(2, 'Received a non integer server number');
                    $response['error'] = 'Found a non integer server number';
                    return $response;
                }

                if($server < 0){
                    foreach($sitesList as $siteId => $siteData){
                        if(!$siteData['logsCollector']){
                            mklog(2, 'Site ' . $siteId . ' does not have logsCollector enabled');
                            $response['error'] = 'Site ' . $siteId . ' does not have logsCollector enabled';
                            return $response;
                        }
                    }
                }
                else{
                    foreach($sitesList as $siteId => $siteData){
                        if(!isset($siteData['servers'][$server])){
                            mklog(2, 'Could not find server ' . $server . ' in site ' . $siteId);
                            $response['error'] = 'Could not find server ' . $server . ' in site ' . $siteId;
                            return $response;
                        }
                    }
                }

                $serversList[] = $server;
            }
        }

        if($action === "startSite"){
            foreach($sitesList as $siteId => $siteData){
                if(!self::hoster_startSite($siteId, $siteData, $response, self::$apacheProcs, self::$mysqls, self::$logCollectors)){
                    return $response;
                }
            }
        }
        elseif($action === "stopSite"){
            foreach($sitesList as $siteId => $siteData){
                foreach($siteData['servers'] as $server){
                    if(!self::hoster_stopServer($server, $response, self::$apacheProcs, self::$mysqls)){
                        return $response;
                    }
                }
                if($siteData['logsCollector']){
                    self::hoster_disableLogCollector($siteId, self::$logCollectors);
                }
            }
        }
        elseif($action === "exit"){
            foreach(self::$apacheProcs as $apacheId => $apacheProc){
                if(!self::hoster_stopApacheServer($apacheId, $response, self::$apacheProcs)){
                    return $response;
                }
            }

            foreach(self::$mysqls as $mysqlId => $mysqlRunning){
                if(!self::hoster_stopMysqlServer($mysqlId, $response, self::$mysqls)){
                    return $response;
                }
            }

            foreach(self::$logCollectors as $siteId => $logCollector){
                self::hoster_disableLogCollector($siteId, self::$logCollectors);
            }
        }
        elseif($action === "getRunningServers" || $action === "getStatuses"){
            $response['success'] = true;
            $response['data']['apache'] = [];
            $response['data']['mysql'] = [];
            $response['data']['logCollectors'] = [];

            foreach(self::$apacheProcs as $apacheIndex => $process){
                $response['data']['apache'][] = $apacheIndex;
            }
            foreach(self::$mysqls as $mysqlIndex => $mysqlRunning){
                $response['data']['mysql'][] = $mysqlIndex;
            }
            foreach(self::$logCollectors as $siteId => $logCollector){
                $response['data']['logCollectors'][] = $siteId;
            }

            if($action === "getStatuses"){
                $statuses = self::hoster_turnRunningServersIntoSiteStates($response);
                if(!is_array($statuses)){
                    $response['success'] = false;
                    $response['error'] = "Failed to turn running data into statuses";
                    $response['data'] = [];
                    return $response;
                }

                $response['data'] = $statuses;
            }
        }
        elseif($action === "startServer"){
            foreach($sitesList as $siteId => $siteData){
                foreach($serversList as $server){
                    if($server < 0){
                        if(self::hoster_enableLogCollector($siteId, $siteData, self::$logCollectors)){
                            $response['success'] = true;
                        }
                        else{
                            $response['error'] = 'LogCollector is already running for site ' . $siteId;
                            return $response;
                        }
                    }
                    else{
                        if(!self::hoster_startServer($siteData['servers'][$server], $response, self::$apacheProcs, self::$mysqls)){
                            return $response;
                        }
                    }
                }
            }
        }
        elseif($action === "stopServer"){
            foreach($sitesList as $siteId => $siteData){
                foreach($serversList as $server){
                    if($server < 0){
                        if(self::hoster_disableLogCollector($siteId, self::$logCollectors)){
                            $response['success'] = true;
                        }
                        else{
                            $response['error'] = 'LogCollector was not running for site ' . $siteId;
                            return $response;
                        }
                    }
                    else{
                        if(!self::hoster_stopServer($siteData['servers'][$server], $response, self::$apacheProcs, self::$mysqls)){
                            return $response;
                        }
                    }
                }
            }
        }
        else{
            mklog(2,'Received an unknown command');
            $response['error'] = 'Unknown command';
        }

        return $response;
    }
    private static function hoster_turnRunningServersIntoSiteStates(array $result):array|false{
        $sites = settings::read('sites');
        if(!is_array($sites)){
            return false;
        }
        if(empty($sites)){
            return [];
        }

        if(!isset($result['data']) || !isset($result['data']['apache']) || !isset($result['data']['mysql']) || !isset($result['data']['logCollectors'])){
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
                    self::hoster_siteThingOnOff($siteId, true, $siteStates);
                }
                else{
                    self::hoster_siteThingOnOff($siteId, false, $siteStates);
                }
            }

            foreach($siteData['servers'] as $serverNumber => $server){
                foreach(['apache','mysql'] as $thing){
                    if($server['type'] === $thing){
                        if(in_array($server[$thing.'Index'], $result['data'][$thing])){
                            $runningServers[$siteId][] = $serverNumber;
                            self::hoster_siteThingOnOff($siteId, true, $siteStates);
                        }
                        else{
                            self::hoster_siteThingOnOff($siteId, false, $siteStates);
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
    private static function hoster_siteThingOnOff(int $siteId, bool $on, array &$siteStates):void{
        if(!isset($siteStates[$siteId])){
            $siteStates[$siteId] = ($on ? "on" : "off");
        }
        else{
            if($siteStates[$siteId] === "off" && $on || $siteStates[$siteId] === "on" && !$on){
                $siteStates[$siteId] = "onish";
            }
        }
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
    private static function hoster_startSite(int $siteId, array $siteData, array &$response, array &$apacheProcs, array &$mysqls, array &$logCollectors):bool{
        if(!isset($siteData['servers']) || !is_array($siteData['servers']) || empty($siteData['servers'])){
            $response['error'] = 'The specified site has no servers';
            return false;
        }
        
        $return = true;

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
    public static function sendCommand(string $action, array $sites=[], array $servers=[]):array|false{
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

        $communicatorIp = settings::read('communicatorIP');
        $communicatorPort = settings::read('communicatorPort');
        if(!is_string($communicatorIp) || !is_int($communicatorPort)){
            mklog(2, 'Failed to get hoster connection info');
            return false;
        }

        $action = base64_encode(serialize($action));
        $sites = base64_encode(serialize($sites));
        $servers = base64_encode(serialize($servers));

        $response = communicator_client::runfunction('website::hoster_run(unserialize(base64_decode(\''.$action.'\')), unserialize(base64_decode(\''.$sites.'\')), unserialize(base64_decode(\''.$servers.'\')))', $communicatorIp, $communicatorPort);

        if(!is_array($response)){
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
    public static function listSites():array|false{
        return settings::read('sites');
    }
    public static function numberOfSites():int|false{
        $sites = self::listSites();
        if(!is_array($sites)){
            return false;
        }
        return count($sites);
    }
    //Communicator things
    public static function communicatorServerThingsToDo():array{
        return [
            [
                "type" => "startup",
                "function" => 'website::hoster_startAutostarts()'
            ],
            [
                "type" => "repeat",
                "interval" => 5,
                "function" => 'website::hoster_checkStuff()'
            ],
            [
                "type" => "shutdown",
                "function" => 'website::hoster_run("exit", [], [])'
            ],
        ];
    }
}