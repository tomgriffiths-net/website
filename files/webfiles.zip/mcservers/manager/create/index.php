<?php
require '../../../../localfiles/global.php';

html::fullhead("mcservers");

echo '
    <style>
        .form-control, .form-select{
            max-width:350px;
        }
        h5{
            margin-top:10px;
        }
    </style>
';

if(!isset($_GET['type'])){
    $types = runfunction('mcservers::listKnownServerTypes()');

    if(is_array($types)){
        echo '
            <h4>Select server type:<br></h4>
            <select id="select" class="form-select">
                ';
                foreach($types as $type){
                    $name = ucfirst($type);
                    echo '<option value="' . $type . '">' . $name . '</option>';
                }
                echo '
            </select>
            <br>

            <button class="btn btn-success" onclick="window.location.href=\'?type=\' + document.getElementById(\'select\').value;">Next</button>
        ';
    }
    else{
        echo "Failed to get types list";
    }
}
elseif(!isset($_GET['version'])){
    $channels = runfunction('mcservers::listChannels(unserialize(\'' . serialize($_GET['type']) . '\'))');
    if(is_array($channels) && !empty($channels)){
        echo '
            <h4>Select update channel:<br></h4>
            <select id="channels" class="form-select" onchange="updateVersions(\'' . $_GET['type'] . '\');">
                ';
                foreach($channels as $channel){
                    $name = ucfirst($channel);
                    echo '<option value="' . $channel . '">' . $name . '</option>';
                }
                echo '
            </select>
            <br>
        ';
    }

    echo '
        <h4>Select major version:<br></h4>
        <select id="versions" class="form-select" onchange="updateSpecialVersions(\'' . $_GET['type'] . '\');">
        </select>
        <br>

        <h4>Select minor version:<br></h4>
        <select id="specialversions" class="form-select">
        </select>
        <br>

        <button class="btn btn-success" onclick="submit();">Next</button>

        <script>
            updateVersions("' . $_GET['type'] . '");

            function submit(){
                window.location.href="?type=' . $_GET['type'] . '&version=" + document.getElementById("versions").value + "&specialversion=" + document.getElementById("specialversions").value;
            }
            function updateVersions(type){
                let url = "/mcservers/api/?function=listVersions&noid=true&type=" + type;

                const channels = document.getElementById("channels");
                if(channels){
                    url += "&channel=" + channels.value;
                }

                ajax(url, "versions");
            }
            function updateSpecialVersions(type){
                let url = "/mcservers/api/?function=listSpecialVersions&noid=true&type=" + type + "&version=" + document.getElementById("versions").value;

                const channels = document.getElementById("channels");
                if(channels){
                    url += "&channel=" + channels.value;
                }

                ajax(url, "specialversions");
            }
        </script>
    ';
}
elseif(!isset($_GET['name'])){
    $defaultInfo = runfunction('mcservers::serverTypeInfo(unserialize(\'' . serialize($_GET['type']) . '\'), unserialize(\'' . serialize($_GET['version']) . '\'), unserialize(\'' . serialize($_GET['specialversion']) . '\'))');
    if(is_array($defaultInfo)){
        echo '
            <form>
            <input style="display:none;" type="text" name="type" value="' . $_GET['type'] . '">
            <input style="display:none;" type="text" name="version" value="' . $_GET['version'] . '">
            <input style="display:none;" type="text" name="specialversion" value="' . $_GET['specialversion'] . '">

            <h5>Name:</h5>
            <input class="form-control" type="text" value="' . ucfirst($_GET['type']) . " Server " . date("Y-m-d") . '" name="name">

            <h5>Maximum memory (MB):</h5>
            <input class="form-control" min="128" type="number" value="' . $defaultInfo['run']['maxMem'] . '" name="maxMem">

            <h5>Minimum memory (MB):</h5>
            <input class="form-control" min="0" type="number" value="' . $defaultInfo['run']['minMem'] . '" name="minMem">

            <h5>Hide GUI:</h5>
            <select name="hideGui" class="form-select">
                <option ' . ($defaultInfo['run']['hideGui'] ? "selected" : "") . ' value="true">True</option>
                <option ' . ($defaultInfo['run']['hideGui'] ? "" : "selected") . ' value="false">False</option>
            </select>

            <h5>Stop command:</h5>
            <input class="form-control" type="text" value="' . $defaultInfo['run']['stopCommand'] . '" name="stopCommand">
        ';

        foreach(["mods", "plugins", "datapacks", "resourcepacks"] as $thing){
            echo '
                <h5>Allow ' . ucfirst($thing) . ':</h5>
                <select name="' . $thing . '" class="form-select">
                    <option ' . ($defaultInfo['abilities'][$thing] ? "selected" : "") . ' value="true">True</option>
                    <option ' . ($defaultInfo['abilities'][$thing] ? "" : "selected") . ' value="false">False</option>
                </select>
            ';
        }

        echo '
            <h5>Use RCON:</h5>
            <select name="hasRcon" class="form-select">
                <option ' . ($defaultInfo['spec']['hasRcon'] ? "selected" : "") . ' value="true">True</option>
                <option ' . ($defaultInfo['spec']['hasRcon'] ? "" : "selected") . ' value="false">False</option>
            </select>

            <br>
            <button class="btn btn-success" type="submit">Next</button>
            </form>
        ';
    }
    else{
        echo "Failed to get default information";
    }
}
else{
    echo '
        <p>Creating server...</p>

        <div id="loading" style="width:100px; height:100px;">
            <p>Error sending request.</p>
        </div>

        <script>
            ajax("/mcservers/api/?function=createServer&noid=true&data=' . html::encodeInString($_GET) . '", "loading");
        </script>
    ';
}

html::fullend();