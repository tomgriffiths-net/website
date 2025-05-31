<?php
require '../../../../localfiles/global.php';

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

html::loadurl('/mcservers/api/?function=create_server&noid=true&specialData=' . html::encodeInString($serverData));


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
                    $output[$key] = convert_string($value);
                }
            }
        }
    }
    return $output;
}
function convert_string(string $value):int|float|bool|string{
    $return = $value;
    if(is_numeric($value)){
        //Check if the string contains a point
        if(strpos($value,'.')){
            //Convert string to float
            $return = floatval($value);
        }
        else{
            //Convert string to integer
            $return = intval($value);
        }
    }
    elseif($value === "true" || $value === "false"){
        //convert string to boolean
        $return = ($value === "true");
    }
    return $return;
}