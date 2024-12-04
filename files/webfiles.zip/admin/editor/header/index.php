<?php
require '../../../../localfiles/global.php';

$id = false;
if(isset($_GET['id'])){
    if(preg_match("/^[a-zA-Z0-9-_.]+$/", $_GET['id']) === 1){
        if(is_file($localDir . "\\headers\\" . $_GET['id'] . ".json")){
            $id = $_GET["id"];
        }
        else{
            echo "ID not found!<br>";
        }
    }
    else{
        echo "Invalid ID!<br>";
    }
}

function get2arr(string $get,string $separator = ".."):string{
    $keys = explode($separator,$_GET[$get]);
    $varName = '$headerData["buttons"]';
    foreach($keys as $key){
        $varName .= '["' . $key . '"]';
    }
    return $varName;
}

if(isset($_GET['submit']) && $id !== false){
    if(isset($_GET['action'])){
        $action = $_GET['action'];
        $headerData = json::readFile($localDir . "\\headers\\" . $id . ".json");
        if($action === 'delete'){
            $varName = get2arr("button");
            eval('unset(' . $varName . ');');
        }
        elseif($action === 'adddropdown' || $action === 'addbutton'){
            $defaultData = array("name"=>"NewButton","link"=>"/");
            if(strpos($_GET['after'],"..") === false){
                if($action === 'adddropdown'){
                    $defaultData = array("name"=>"New Dropdown","dropdown"=>array(0=>array("name"=>"New Button","link"=>"/")));
                }
                $lastButton = intval($_GET['after']);
                $newHeaderData = $headerData;
                $newHeaderData["buttons"] = array();
                if($lastButton === count($headerData['buttons'])-1){
                    $newHeaderData['buttons'] = $headerData['buttons'];
                    $newHeaderData['buttons'][] = $defaultData;
                }
                else{
                    $i = 0;
                    foreach($headerData['buttons'] as $buttonData){
                        if($i === $lastButton+1){
                            $newHeaderData['buttons'][$i] = $defaultData;
                            $i++;
                            $newHeaderData['buttons'][$i] = $buttonData;
                        }
                        else{
                            $newHeaderData['buttons'][$i] = $buttonData;
                        }
                        $i++;
                    }
                }
                $headerData = $newHeaderData;
            }
            else{
                $lastButton = intval(substr($_GET['after'],0,strpos($_GET['after'],"..")));
                $lastButton2 = intval(substr($_GET['after'],strripos($_GET['after'],"..")+2));
                $newHeaderData = $headerData;
                $newHeaderData["buttons"][$lastButton]['dropdown'] = array();
                if($lastButton2 === count($headerData['buttons'][$lastButton]['dropdown'])-1){
                    $newHeaderData["buttons"][$lastButton]['dropdown'] = $headerData['buttons'][$lastButton]['dropdown'];
                    $newHeaderData["buttons"][$lastButton]['dropdown'][] = $defaultData;
                }
                else{
                    $i = 0;
                    foreach($headerData["buttons"][$lastButton]['dropdown'] as $buttonData){
                        if($i === $lastButton2+1){
                            $newHeaderData["buttons"][$lastButton]['dropdown'][$i] = $defaultData;
                            $i++;
                            $newHeaderData["buttons"][$lastButton]['dropdown'][$i] = $buttonData;
                        }
                        else{
                            $newHeaderData["buttons"][$lastButton]['dropdown'][$i] = $buttonData;
                        }
                        $i++;
                    }
                }
                $headerData = $newHeaderData;
            }
        }
    }
    else{
        $headerData['webName'] = $_POST['headerContent/webName'];
        $headerData['webNameLink'] = $_POST['headerContent/webNameLink'];
        foreach($_POST as $key => $value){
            if(substr($key,0,14) === 'headerContent/' && $key !== 'headerContent/webName' && $key !== 'headerContent/webNameLink'){
                $keys = explode("/",substr($key,14));
                $varName = '$headerData["buttons"]';
                foreach($keys as $key2){
                    $varName .= '["' . $key2 . '"]';
                }
                eval($varName . ' = "' . $value . '";');
            }
        }
    }
    json::writeFile($localDir . "\\headers\\" . $id . ".json",$headerData,true);
    header('Location: ?id=' . $id);
    exit;
}

html::head();
echo '
    .account-form-input{
        width:200px;
    }
    img{
        position:relative;
        width:25px;
        height:auto;
        left:210px;
        bottom:38px;
    }
    .input2{
        position:relative;
        bottom:33px;
    }
    .input3{
        position:relative;
        bottom:66px;
    }
    .img2{
        left:230px;
        bottom:71px;
    }
    .button{
        position:relative;
        bottom:33px;
        left:5px;
    }
    .button2{
        left:25px;
        bottom:66px;
    }
';
html::top("admin");

if($id !== false){
    $headerData = json::readFile($localDir . "\\headers\\" . $id . ".json");
    echo '<form action="?id=' . $id . '&submit=true" method="post">
    <input class="account-form-input" type="text" name="headerContent/webName" placeholder="Website Name" value="' . $headerData['webName'] . '">
    <input class="account-form-input" type="text" name="headerContent/webNameLink" placeholder="Website Name Link" value="' . $headerData['webNameLink'] . '">
    <br>';
    $i2 = 0;
    foreach($headerData['buttons'] as $buttonId => $buttonData){
        $extraSpace = false;
        echo '
            <input class="account-form-input" type="text" name="headerContent/' . $buttonId . '/name" placeholder="Name" value="' . $buttonData['name'] . '">
            <a'; if($i2 > 0){echo ' href="?id=' . $id . '&submit=true&action=delete&button=' . $buttonId . '"';} echo '>
                <img'; if($i2 < 1){echo ' style="opacity:0%;"';} echo ' src="' . $filesUrl . '/img/red-trash-can-icon.png">
            </a>
            ';
        if($i2 > 0){echo '';}
        if(isset($buttonData['dropdown'])){
            $i = 0;
            foreach($buttonData['dropdown'] as $dbuttonId => $dbuttonData){
                echo '
                    <input style="position:relative; left:20px;" class="account-form-input input2" type="text" name="headerContent/' . $buttonId . '/dropdown' . '/' . $dbuttonId . '/name" placeholder="Name" value="' . $dbuttonData['name'] . '">

                    <a'; if($i > 0){echo ' href="?id=' . $id . '&submit=true&action=delete&button=' . $buttonId . '..dropdown..' . $dbuttonId . '"';} echo '>
                        <img'; if($i < 1){echo ' style="opacity:0%;"';} echo ' class="img2" src="' . $filesUrl . '/img/red-trash-can-icon.png">
                    </a>

                    <input style="position:relative; left:20px;" class="account-form-input input3" type="text" name="headerContent/' . $buttonId . '/dropdown' . '/' . $dbuttonId . '/link" placeholder="Link" value="' . $dbuttonData['link'] . '">
                    <a href="?id=' . $id . '&submit=true&action=addbutton&after=' . $buttonId . '..dropdown..' . $dbuttonId . '">
                        <button type="button" class="button2 button">Add Button</button>
                    </a>
                    <br>
                ';
                $i++;
            }
            $extraSpace = true;
        }
        else{
            echo '
            <input class="account-form-input input2" type="text" name="headerContent/' . $buttonId . '/link" placeholder="Link" value="' . $buttonData['link'] . '">
            ';
        }
        echo '
        <a href="?id=' . $id . '&submit=true&action=addbutton&after=' . $buttonId . '">
            <button class="button" type="button">Add Button</button>
        </a>
        <a href="?id=' . $id . '&submit=true&action=adddropdown&after=' . $buttonId . '">
            <button class="button" type="button">Add Dropdown</button>
        </a>
        ';
        $i2++;
    }

    echo '
        <br>
        <button class="account-form-submit" type="submit" name="submit">Save Text</button>
    </form>
    ';
}
else{
    foreach(glob($GLOBALS['localDir'] . "/headers/*.json") as $header){
        $header = files::getFileName($header);
        $header = substr($header,0,strripos($header,"."));
        echo '<a class="link" href="?id=' . $header . '">' . $header . '</a><br>';
    }
}

html::end();
echo '
        
';
html::end2();