<?php
require '../../../localfiles/global.php';

if(isset($_GET["form"])){
    $settings = json::readFile($localDir . "\\settings.json",false);
    foreach($_POST as $settingName => $settingValue){

        if($settingValue === "true"){$settingValue = true;}
        if($settingValue === "false"){$settingValue = false;}
        if(is_numeric($settingValue)){$settingValue = intval($settingValue);}

        if($settingName === "version" | $settingName === "submit" | $settingName === "devmode" | $settingName === "site-index"){
            
        }
        else{
            $settings[$settingName] = $settingValue;
        }
    }
    json::writeFile($localDir . "\\settings.json",$settings,true);
    header("location: /admin/settings/");
    exit;
}

html::head();
echo '
        
';
html::top("admin","Settings");
$settings = json::readFile($localDir . "\\settings.json");
echo '<form action="?form=1" method="post">';
$num = 1;
foreach($settings as $settingName => $settingValue){
    if(!is_array($settingValue)){
        if($settingValue === true){$settingValue = "true";}
        if($settingValue === false){$settingValue = "false";}

        $disabled = "";
        if($settingName === "version" | $settingName === "submit" | $settingName === "devmode" | $settingName === "site-index"){
            $disabled = 'disabled="true" style="background:grey"';
        }
        else{
            
        }
        if($settingValue === "true" || $settingValue === "false"){
            echo '<label>' . $settingName . '
                <select name="' . $settingName . '" ' . $disabled . ' id="' . $num . '" class="account-form-input">
                    <option value="true" '; if($settingValue === "true"){echo 'selected ';} echo '>True</option>
                    <option value="false" '; if($settingValue === "false"){echo 'selected ';} echo '>False</option>
                </select>
            </label>';
        }
        elseif(is_int($settingValue)){
            echo '<label>' . $settingName . ' <input id="' . $num . '" class="account-form-input" ' . $disabled . ' type="number" name="' . $settingName . '" value="' . $settingValue . '" step="1"></label>';
        }
        else{
            echo '<label>' . $settingName . ' <input id="' . $num . '" class="account-form-input" ' . $disabled . ' type="text" name="' . $settingName . '" value="' . $settingValue . '"></label>';
        }
        
        $num++;
    }
}
echo '<button class="account-form-submit" type="submit" name="submit">Apply</button>
</form>';
html::end();
echo '
        
';
html::end2();