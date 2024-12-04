<?php
require '../../../../../localfiles/global.php';

if(!isset($_GET['type'])){
    echo "Type not set";
    exit;
}
$_POST['version/type'] = $_GET['type'];

$basicInfo = array("name","version/type","version/version");
foreach($basicInfo as $infoItem){
    if(!isset($_POST[$infoItem])){
        echo "Server data is incomplete";
        exit;
    }
}

$serverData = array();
foreach($_POST as $postName => $postData){
    $invalidChars = array("'",';',':','\\','(',')');
    foreach($invalidChars as $invalidChar){
        if(strpos($postName,$invalidChar) !== false){
            goto endofvarcreate;
        }
    }
    if($postData === ""){
        $postData = "(UnsetVariable)";
    }
    $varNames = array();
    if(strpos($postName,"/") !== false){
        $varNames = explode("/",$postName);
    }
    else{
        $varNames[0] = $postName;
    }

    $codeString = '';
    foreach($varNames as $varNamePart){
        $codeString .= "['" . $varNamePart . "']";
    }

    eval('$serverData' . $codeString . ' = $postData;');
    endofvarcreate:
}

$serverData = arrayStringConvert($serverData);

echo '<pre style="color:black">' . json_encode($serverData,JSON_PRETTY_PRINT) . '</pre>';

html::loadurl('/admin/mcservers/api/?function=create_server&noid=true&specialData=' . html::encodeInString($serverData));


function arrayStringConvert(array $input):array{
    $output = array();
    foreach($input as $key => $value){
        if(is_array($value)){
            $tmp = arrayStringConvert($value);
            if($tmp !== array()){
                $output[$key] = $tmp;
            }
        }
        else{
            if($value !== "(UnsetVariable)"){
                if($key === "version" || $key === "special_version"){
                    $output[$key] = $value;
                }
                else{
                    $output[$key] = data_types::convert_string($value);
                }
            }
        }
    }
    return $output;
}