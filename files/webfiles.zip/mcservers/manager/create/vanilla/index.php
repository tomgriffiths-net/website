<?php
require '../../../../../localfiles/global.php';

if(isset($_GET['listVersions'])){
    $array = runfunction('minecraft_releases_api::listVersions("' . $_GET['listVersions'] . '");');

    if(!is_array($array)){
        $array = array();
    }

    foreach($array as $version){
        echo '<option value="' . $version . '">' . ucfirst($version) . '</option>';
    }
    exit;
}

html::fullhead("mcservers");

?>

<form method="post" action="../submit.php?type=vanilla">
    <h4>Server Name:</h4>
    <input class="account-form-input" type="text" value="Server <?php echo date("Y-m-d H:i:s");?>" name="name" required>
    <br>

    <h4>Server Version:</h4>
    <button type="button" onclick="ajax('index.php?listVersions=release','versionSelector',0);">Releases</button>
    <button type="button" onclick="ajax('index.php?listVersions=snapshot','versionSelector',0);">Snapchots</button>
    <select id="versionSelector" name="version/version" class="account-form-input" required>
    </select>
    <br>

    <h4>Max Memory (MB):</h4>
    <input class="account-form-input" type="text" value="4096" name="run/max_ram_mb" required>
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