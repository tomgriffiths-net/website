<?php
require '../../../../../localfiles/global.php';

if(isset($_GET['listVersions'])){
    $array = runfunction('papermc_api_v2::listVersions("' . $_GET['listVersions'] . '");');

    //$array = array_flip($array);
    $array = array_reverse($array);

    if($array === false){
        $array = array("Communicator server is not running");
    }

    foreach($array as $version){
        echo '<option value="' . $version . '">' . ucfirst($version) . '</option>';
    }
    exit;
}

if(!isset($_GET['type'])){
    echo "type not set";
    exit;
}
$type = $_GET['type'];
if(!in_array($type,array("paper","velocity","waterfall"))){
    echo "Invalid type";
    exit;
}

if(isset($_GET['listBuilds'])){
    $array = runfunction('papermc_api_v2::listBuilds("' . $type . '","' . $_GET['listBuilds'] . '");');

    //$array = array_flip($array);
    $array = array_reverse($array);

    if($array === false){
        $array = array("Communicator server is not running");
    }

    foreach($array as $version){
        echo '<option value="' . $version . '">' . ucfirst($version) . '</option>';
    }
    exit;
}


if(isset($_POST['submit'])){
    unset($_POST['submit']);
    $_POST['version/type'] = $type;
    foreach($_POST as $name => $value){
        echo $name . " : " . $value . "<br>";
    }
    //echo '<pre style="color:white">' . json_encode($data,JSON_PRETTY_PRINT) . '</pre>';
    exit;
}

//

html::fullhead("mcservers");

?>

<form method="post" action="../submit.php?type=<?php echo $type;?>">
    <h4>Server Name:</h4>
    <input class="account-form-input" type="text" value="<?php echo ucfirst($type) . ' Server ' . date("Y-m-d H:i:s");?>" name="name" required>
    <br>

    <h4>Server Version:</h4>
    <select onchange="ajax('index.php?type=<?php echo $type;?>&listBuilds=' + document.getElementById('versionSelector').value,'buildSelector',0);" id="versionSelector" name="version/version" class="account-form-input" required>
    </select>
    <br>

    <h4>Select Build:</h4>
    <select id="buildSelector" name="version/special_version" class="account-form-input" required>
    </select>
    <br>

    <script>
        ajax('index.php?listVersions=<?php echo $type;?>','versionSelector',0);

        setTimeout(() => {
            ajax('index.php?type=<?php echo $type;?>&listBuilds=' + document.getElementById('versionSelector').value,'buildSelector',0);
        }, 500);
    </script>

    <h4>Max Memory (MB):</h4>
    <input class="account-form-input" min="128" type="number" value="4096" name="run/max_ram_mb" required>
    <br>

    <h4>Hide Console GUI:</h4>
    <select name="run/nogui" class="account-form-input" required>
        <option value="true">True</option>
        <option value="false">False</option>
    </select>
    <br>

    <br>
    <button id="formsubmit1" class="account-form-submit" type="submit" name="submit">Next</button>
</form>

<?php

html::fullend();