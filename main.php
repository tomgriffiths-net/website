<?php
class website{
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
            self::listSites();
        }
        elseif($lines[0] === "create"){
            self::createDefaultSite();
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
    public static function newSite($httpdPath,$directory=false,$filesurl=false,$filesdir=false,$logsdir=false,$tempdir=false,$communicator=false,$name="MySite",$password="1234"):int{
        if(!is_admin::check()){
            mklog('warning','You need administrator permissions to do that',false);
        }
        
        $i = 1;
        redo:
        if(settings::isset('sites/' . $i)){
            $i++;
            goto redo;
        }
        else{
            $cwd = getcwd();
            $dir = $cwd . "\\mywebsite";
            $copyfiles = true;
            if(is_string($directory)){
                $dir = $directory;
            }

            if(is_dir($dir)){
                $copyfiles = false;
            }

            if(!is_string($filesdir)){
                $filesdir = $dir . '\\website\\files';
            }
            $apacheNumber = apache::new_server($httpdPath,$dir . "/website",$name);
            if(is_int($apacheNumber)){
                if($copyfiles){
                    cmd::run('robocopy packages\\website\\files\\webfiles.zip "' . $dir . '\\website" /e /v');
                    cmd::run('robocopy packages\\website\\files\\webfiles-local.zip "' . $dir . '\\localfiles" /e /v');
                    cmd::run('robocopy packages\\website\\files\\webfiles-data.zip "' . $dir . '\\localdata" /e /v');
                    cmd::run('robocopy packages\\website\\files\\webfiles-files.zip "' . $filesdir . '" /e /v');
                }
                $websettings['debug'] = true;
                $websettings['filesurl'] = '/files';
                if(is_string($filesurl)){
                    $websettings['filesurl'] = $filesurl;
                }
                $websettings['localdir'] = $dir . '\\localdata';
                $websettings['version'] = 1;
                $websettings['site-index'] = $i;
                $logsdirFinal = $cwd . '\\logs\\website';
                if(is_string($logsdir)){
                    $logsdirFinal = $logsdir;
                }
                $websettings['verbose-logging'] = false;
                $websettings['filesdir'] = $filesdir;
                $websettings['tempdir'] = $cwd . '\\temp\\website';
                if(is_string($tempdir)){
                    $websettings['tempdir'] = $tempdir;
                }
                $websettings['php-exec-path'] = $cwd . '\\php\\php.exe';
                $websettings['cli-root'] = $cwd;
                $websettings['communicator'] = ['name'=>'PHP-CLI_Website', 'password'=>communicator::getPasswordEncoded()];
                $websettings['password'] = password_hash($password, PASSWORD_DEFAULT);
                json::writeFile($dir . '/localdata/settings.json',$websettings,true);

                $fileLines = file($dir . '/localfiles/global.php');
                foreach($fileLines as $ln => $line){
                    if(trim($line) === '//NEXT_LINE_IS_SETTINGS_FILE'){
                        $ln++;
                        $fileLines[$ln] = '$settingsFile = "' . str_replace("\\","\\\\",$websettings['localdir']) . '\\settings.json";' . "\n";
                        break;
                    }
                }
                file_put_contents($dir . '/localfiles/global.php',$fileLines);

                if(!apache::ensurePhpExtension($apacheNumber,array("bz2","curl","fileinfo","gettext","mbstring","exif","mysqli","openssl","pdo_mysql","pdo_sqlite","sockets"))){
                    echo "Unable to enable required extensions in php.ini file!\n";
                }

                settings::set('sites/' . $i,
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
                );
                return $i;
            }
            else{
                mklog('warning','Failed to register apache server',false);
            }
        }
        return 0;
    }
    public static function listSites(){
        $columnTitles = array("Site ID"=>7,"Name"=>15,"Path"=>50,"Servers"=>30);
        $rowsData = array();
        $i = 0;
        $sites = settings::read('sites');
        if(!is_array($sites)){
            $sites = array();
        }
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
    public static function numberOfSites():int|false{
        $sites = settings::read('sites');
        if(!is_array($sites)){
            return false;
        }
        return count($sites);
    }
    public static function removeSite(int $siteId){
        $settings = settings::read('sites/' . $siteId);
        settings::unset('sites/' . $siteId);
        foreach($settings['servers'] as $server){
            if($server['type'] === "apache"){
                apache::delete_server($server['apacheIndex']);
            }
        }
    }
    public static function startSite(int $siteId){
        $settings = settings::read('sites/' . $siteId);
        if($settings['communicator']){
            mklog('general','Starting communicator',false);
            if(communicator_client::runfunction('time::stamp();') !== false){
                echo "Communicator is allready running.\n";
            }
            else{
                cmd::newWindow('php\php cli.php command "communicator begin"');
            }
        }
        if($settings['logsCollector']){
            mklog('general','Starting logs collector',false);
            cmd::newWindow('php\php packages\website\\files\logsCollector.php "' . $settings['tempdir'] . '\\logs" "' . $settings['logsdir'] . '" "' . $siteId . '" & exit',true);
        }
        foreach($settings['servers'] as $server){
            if($server['type'] === "apache"){
                apache::start($server['apacheIndex']);
            }
            elseif($server['type'] === "mysql"){
                mysql::start($server['mysqlIndex']);
            }
        }
    }
    public static function stopSite(int $siteId){
        $settings = settings::read('sites/' . $siteId);
        foreach(array_reverse($settings['servers']) as $server){
            if($server['type'] === "apache"){
                apache::stop($server['apacheIndex']);
            }
            elseif($server['type'] === "mysql"){
                mysql::stop($server['mysqlIndex']);
            }
        }
        //if($settings['communicator']){
        //    mklog('general','Stopping communicator',false);
        //    @communicator::send("__EXIT_SOCKET_SERVER");
        //}
        if($settings['logsCollector']){
            mklog('general','Stopping logs collector',false);
            character_sender::sendString("","Logs Collector " . $siteId,false,true);
        }
    }
    public static function updateSite(int $siteId){
        if(settings::isset("sites/" . $siteId)){
            $communicatorRunning = network::ping('localhost',8080);
            if($communicatorRunning){
                echo "\n  If any other packages have been updated then communicator will have to be restarted.\n\n";
            }
            
            $settings = settings::read('sites/' . $siteId);
            $dir = $settings['path'];
            $filesdir = $settings['filesdir'];

            mklog('general','Copying site files for site ' . $siteId,false);
            cmd::run('robocopy packages\\website\\files\\webfiles.zip "' . $dir . '\\website" /e /v /mir');
            cmd::run('robocopy packages\\website\\files\\webfiles-local.zip "' . $dir . '\\localfiles" /e /v /mir');
            cmd::run('robocopy packages\\website\\files\\webfiles-files.zip "' . $filesdir . '" /e /v');

            $fileLines = file($dir . '/localfiles/global.php');
            foreach($fileLines as $ln => $line){
                if(trim($line) === '//NEXT_LINE_IS_SETTINGS_FILE'){
                    $ln++;
                    $fileLines[$ln] = '$settingsFile = "' . str_replace("\\","\\\\",$dir) . '\\\\localdata\\\\settings.json";' . "\n";
                    break;
                }
            }
            file_put_contents($dir . '/localfiles/global.php',$fileLines);
            return true;
        }
        return false;
    }
    public static function createDefaultSite(bool $silent = false){
        $httpdPath = "C:\\xampp\\apache\\bin\\httpd.exe";
        if(!is_file("C:\\xampp\\uninstall.exe") && !is_file($httpdPath)){
            if(!is_dir("mywebsite")){
                e_xampp_installer::install(array(),false,$silent);
                if(!is_file($httpdPath)){
                    echo "Unable to find apache executable(httpd.exe), please enter its path path below:\n";
                    $newHttpdPath = trim(user_input::await());
                    if(files::getFileName($newHttpdPath) !== "httpd.exe" || !is_file($newHttpdPath)){
                        echo "The file given does not have the expected name or does not exist.\n";
                        echo "Are you sure you want to continue with " . files::getFileName($newHttpdPath) . "?";
                        if(user_input::yesNo()){
                            $httpdPath = $newHttpdPath;
                        }
                        else{
                            return;
                        };
                    }
                    else{
                        $httpdPath = $newHttpdPath;
                    }
                }
                //DO STUFF
                passwordCreation:
                echo "Please enter a password for the website:\n";
                $password = user_input::await();
                echo "Repeat:\n";
                $password2 = user_input::await();
                if($password !== $password2){
                    echo "Passwords do not match!\n";
                    goto passwordCreation;
                }
                $siteid = self::newSite($httpdPath,false,false,false,false,false,true,"MySite",$password);
                if(!is_int($siteid)){
                    mklog('warning','Failed to get valid site id',false);
                    return;
                }
                echo "Site ID: " . $siteid . "\n";
            }
            else{
                mklog("general","Unable to create default site as is has allready been created",false);
            }
        }
        else{
            mklog("general","Unable to create default site as XAMPP is allready installed",false);
        }
    }
}