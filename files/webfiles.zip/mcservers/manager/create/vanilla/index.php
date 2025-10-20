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

<form method="post" action="../submit.php?type=vanilla" style="max-width:300px;">
    <h4>Server Name:</h4>
    <input class="form-control" type="text" value="Server <?php echo date("Y-m-d H:i:s");?>" name="name" required>
    <br>

    <h4>Server Version:</h4>
    <div class="btn-group" role="group">
        <input type="radio" onclick="ajax('index.php?listVersions=release','versionSelector',0);" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off">
        <label class="btn btn-outline-primary" for="btnradio1">Releases</label>

        <input type="radio" onclick="ajax('index.php?listVersions=snapshot','versionSelector',0);" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
        <label class="btn btn-outline-primary" for="btnradio2">Snapshots</label>
    </div>
    <select id="versionSelector" name="version/version" class="form-select" required>
    </select>
    <br>

    <h4>Max Memory (MB):</h4>
    <input class="form-control" type="text" value="4096" name="run/max_ram_mb" required>
    <br>

    <h4>Hide Console GUI:</h4>
    <select name="run/nogui" class="form-select" required>
        <option value="true">True</option>
        <option value="false">False</option>
    </select>
    <br>

    <button id="formsubmit1" class="btn btn-success" type="submit" name="submit">Next</button>
</form>

<?php

html::fullend();