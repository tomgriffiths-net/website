<?php
//Set window title
exec('title Logs Collector ' . $argv[3]);

//Set required directories
$tmpLogsDir = $argv[1];
$logsDir = $argv[2];
//Set date and time
date_default_timezone_set("Europe/London");

echo $tmpLogsDir . "\n";
echo $logsDir . "\n";


if(!is_dir($tmpLogsDir)){
    if(!mkdir($tmpLogsDir,0777,true)){
        echo 'Unable to create temperary logs directory';
        sleep(10);
        exit;
    }
}
if(!is_dir($logsDir)){
    if(!mkdir($logsDir,0777,true)){
        echo 'Unable to create logs directory';
        sleep(10);
        exit;
    }
}

start:
//Get list of files in pingtracking directory
$files = array();
$files = glob($tmpLogsDir . "\\*.txt");
//Add each file to the pingtracking log
foreach($files as $file){
    $type = substr($file,strripos($file,"-")+1);
    $type = substr($type,0,strpos($type,"."));
    if(!is_dir($logsDir . "\\" . $type)){
        mkdir($logsDir . "\\" . $type,0777,true);
    }
    //Get contents from current file in list and append it to pingtracking.txt
    $file1 = fopen($logsDir . "\\" . $type . "\\" . date('Y-m') . ".txt", 'a');
    fwrite($file1, file_get_contents($file));
    fclose($file1);
    //Echo date and file name in console to show that the code is working
    echo date("Y-m-d_H:i:s:_") . $file . "\n";
    //Delete current file from pingtracking directory after it has been appended
    unlink($file);
}
//Wait 10 seconds
sleep(10);
//Restart the script
goto start;