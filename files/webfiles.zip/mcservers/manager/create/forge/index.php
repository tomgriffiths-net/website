<?php
require '../../../../../localfiles/global.php';

if(isset($_GET['listVersions'])){
    $array = runfunction('forge_installer::listVersions();');

    if($array === false){
        $array = array("Communicator server is not running");
    }

    foreach($array as $version => $builds){
        echo '<option value="' . $version . '">' . ucfirst($version) . '</option>';
    }
    exit;
}

if(isset($_GET['listBuilds'])){
    $array = runfunction('forge_installer::listSpecialVersions("' . $_GET['listBuilds'] . '");');

    if($array === false){
        $array = array("Communicator server is not running");
    }

    foreach($array as $version){
        echo '<option value="' . $version . '">' . ucfirst($version) . '</option>';
    }
    exit;
}

//

html::fullhead("mcservers");

?>

<form method="post" action="../submit.php?type=forge">
    <h4>Server Name:</h4>
    <input class="account-form-input" type="text" value="Forge Server <?php echo date("Y-m-d H:i:s");?>" name="name" required>
    <br>

    <h4>Server Version:</h4>
    <select onchange="ajax('index.php?listBuilds=' + document.getElementById('versionSelector').value,'buildSelector',0);" id="versionSelector" name="version/version" class="account-form-input" required>
    </select>
    <br>

    <h4>Select Build:</h4>
    <select id="buildSelector" name="version/special_version" class="account-form-input" required>
    </select>
    <br>

    <script>
        ajax('index.php?listVersions=','versionSelector',0);

        setTimeout(() => {
            ajax('index.php?listBuilds=' + document.getElementById('versionSelector').value,'buildSelector',0);
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