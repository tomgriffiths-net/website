<?php
require '../../../../localfiles/global.php';

if(!isset($_GET['action'])){
    exit;
}
$action = $_GET['action'];

$id = ensureId();

$data = html::getNavButtons($id);
if(!is_array($data)){
    echo "<p>Failed to read file.</p>";
    exit;
}
$save = true;
$render = true;

if($action === "getCards"){
    $save = false;
}
elseif($action === "addButton"){
    $data["buttons"][] = ["name"=>"", "link"=>""];
}
elseif($action === "addDropdown"){
    $data["buttons"][] = ["name"=>"", "dropdown"=>[["name"=>"", "link"=>""]]];
}
else{
    $iden = ensureIden();
    
    if($action === "saveText"){
        if(!isset($_GET['value'])){
            exit;
        }

        $text = html::decodeInString($_GET['value']);
        if(!is_string($text)){
            exit;
        }

        if($iden["isWebName"]){
            if(isset($_GET['isLink'])){
                $data["webNameLink"] = $text;
            }
            else{
                $data["webName"] = $text;
            }
        }
        else{
            $name = (isset($_GET['isLink']) ? "link" : "name");

            if($iden["isDrop"]){
                $data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"]][$name] = $text;
            }
            else{
                $data["buttons"][$iden["button"]][$name] = $text;
            }
        }

        $render = false;
    }
    elseif($action === "moveUp" || $action === "moveDown"){
        $mod = ($action === "moveUp" ? -1 : 1);
        if($iden["isDrop"]){
            if(isset($data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"]]) && isset($data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"] + $mod])){
                $thing = $data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"]];
                $thingMod = $data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"] + $mod];
                
                $data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"]] = $thingMod;
                $data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"] + $mod] = $thing;
            }
        }
        else{
            if(isset($data["buttons"][$iden["button"]]) && isset($data["buttons"][$iden["button"] + $mod])){
                $thing = $data["buttons"][$iden["button"]];
                $thingMod = $data["buttons"][$iden["button"] + $mod];

                $data["buttons"][$iden["button"]] = $thingMod;
                $data["buttons"][$iden["button"] + $mod] = $thing;
            }
        }
    }
    elseif($action === "delete"){
        if($iden["isDrop"]){
            unset($data["buttons"][$iden["button"]]["dropdown"][$iden["buttonItem"]]);
            $data["buttons"][$iden["button"]]["dropdown"] = array_values($data["buttons"][$iden["button"]]["dropdown"]);
        }
        else{
            unset($data["buttons"][$iden["button"]]);
            $data["buttons"] = array_values($data["buttons"]);
        }
    }
    elseif($action === "addDropdownItem"){
        $data["buttons"][$iden["button"]]["dropdown"][] = ["name"=>"", "link"=>""];
    }
}

if($save){
    if(!website_json::writeFile($GLOBALS['localDir'] . "\\headers\\" . $id . ".json", $data, true)){
        echo "<p>Failed to save file.</p>";
    }
}

if($render){
    echo renderCards($data);

    echo '
        <div>
            <button onclick="doAction(\'addButton\');" class="btn btn-outline-secondary me-2">Add Button</button>
            <button onclick="doAction(\'addDropdown\');" class="btn btn-outline-secondary">Add Dropdown</button>
        </div>
    ';
}

exit;

function ensureId():string{
    if(isset($_GET['id'])){
        if(preg_match("/^[a-zA-Z0-9-_.]+$/", $_GET['id'])){
            if(is_file($GLOBALS['localDir'] . "\\headers\\" . $_GET['id'] . ".json")){
                return $_GET['id'];
            }
        }
    }

    exit;
}
function ensureIden():array{
    if(!isset($_GET['iden'])){
        exit;
    }

    $return = [
        "isDrop" => false,
        "isWebName" => false,
        "button" => 0,
        "buttonItem" => 0
    ];

    if(preg_match('/^b-\d+$/', $_GET['iden'])){ //b-x
        $return["button"] = intval(substr($_GET['iden'], 2));
        return $return;
    }
    if(preg_match('/^b-\d+-\d+$/', $_GET['iden'])){ //b-x-x
        $rightDash = strripos($_GET['iden'], "-");

        $return["isDrop"] = true;
        $return["button"] = intval(substr($_GET['iden'], 2, $rightDash));
        $return["buttonItem"] = intval(substr($_GET['iden'], $rightDash +1));

        return $return;
    }
    if($_GET['iden'] === "webName"){
        $return["isWebName"] = true;
        return $return;
    }

    exit;
}

function renderCards(array $data):string{
    $string = nameCard($data['webName'], $data['webNameLink'], "webName");

    $lastButton = count($data['buttons']) -1;

    foreach($data['buttons'] as $buttonId => $button){
        $iden = "b-" . $buttonId;
        if(isset($button['link'])){
            $string .= buttonCard($button['name'], $button['link'], ($buttonId), ($buttonId !== $lastButton), $iden);
        }
        elseif(isset($button['dropdown'])){
            $string .= dropdownCardStart($button['name'], ($buttonId), ($buttonId !== $lastButton), $iden);

            $lastDropdownItem = count($button['dropdown']) - 1;
            foreach($button['dropdown'] as $dropdownItemId => $dropdownItem){
                $string .= dropdownItem($dropdownItem['name'], $dropdownItem['link'], ($dropdownItemId), ($dropdownItemId !== $lastDropdownItem), $iden . "-" . $dropdownItemId);
            }
            $string .= dropdownEnd($iden);
        }
    }

    return $string;
}

function moveButtons(bool $canMoveUp, bool $canMoveDown, string $iden):string{
    return '
        <div class="btn-group-vertical" role="group" style="flex: 0 0 auto;">
            <button onclick="doAction(\'moveUp\',\''.$iden.'\');" type="button" class="btn btn-outline-secondary btn-sm p-0"'.($canMoveUp ? '':' disabled').'><i class="bi bi-caret-up-fill"></i></button>
            <button onclick="doAction(\'moveDown\',\''.$iden.'\');" type="button" class="btn btn-outline-secondary btn-sm p-0"'.($canMoveDown ? '':' disabled').'><i class="bi bi-caret-down-fill"></i></button>
        </div>
    ';
}
function nameAndLink(string $name, string $link, string $iden, int $nameWidthPercent=35):string{
    return '
        <input onkeyup="saveText(\''.$iden.'\', this.value, false);" type="text" class="form-control" style="flex: 0 0 '.$nameWidthPercent.'%;" value="'.$name.'" placeholder="Name">
        <input onkeyup="saveText(\''.$iden.'\', this.value, true);" type="text" class="form-control" style="flex: 1;" value="'.$link.'" spellcheck="false" placeholder="Link">
    ';
}
function binButton(string $iden):string{
    return '<button onclick="doAction(\'delete\',\''.$iden.'\');" class="btn btn-outline-danger btn-sm" style="height:36px;"><i class="bi bi-trash"></i></button>';
}
function row(string $name, string $link, bool $canMoveUp, bool $canMoveDown, string $iden):string{
    return moveButtons($canMoveUp, $canMoveDown, $iden) . nameAndLink($name, $link, $iden) . binButton($iden);
}

function buttonCard(string $name, string $link, bool $canMoveUp, bool $canMoveDown, string $iden):string{
    return buttonCardWrapStart() . row($name, $link, $canMoveUp, $canMoveDown, $iden) . '</div></div>';
}
function buttonCardWrapStart():string{
    return '<div class="card mb-3 nav-item-card"><div class="card-body d-flex align-items-center gap-2">';
}
function nameCard(string $name, string $link, string $iden):string{
    return buttonCardWrapStart() . nameAndLink($name, $link, $iden, 40) . '</div></div>';
}

function dropdownCardStart(string $name, bool $canMoveUp, bool $canMoveDown, string $iden):string{
    return '
        <div class="card mb-3 nav-item-card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2" style="margin-bottom:10px; width:100%;">
                    ' . moveButtons($canMoveUp, $canMoveDown, $iden) . '
                    <input onkeyup="saveText(\''.$iden.'\', this.value, false);" type="text" class="form-control" value="'.$name.'" placeholder="Name">
                    ' . binButton($iden) . '
                </div>
                
                <div class="ms-4 border-start border-2 ps-3 vstack gap-2">
    ';
}
function dropdownItem(string $name, string $link, bool $canMoveUp, bool $canMoveDown, string $iden):string{
    return '
        <div class="d-flex align-items-center gap-2">
            ' . row($name, $link, $canMoveUp, $canMoveDown, $iden) . '
        </div>
    ';
}
function dropdownEnd(string $iden):string{
    return '
                </div>

                <button onclick="doAction(\'addDropdownItem\',\''.$iden.'\');" class="btn btn-outline-secondary btn-sm ms-4" style="margin-top:10px;">Add Item</button>
            </div>
        </div>
    ';
}