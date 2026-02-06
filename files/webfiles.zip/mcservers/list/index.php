<?php
require '../../../localfiles/global.php';


html::fullhead("mcservers","All Servers",'style.css');

echo '<script src="script.v2.js"></script>';

$serverStates = runfunction('mcservers::manager_getServerStates(true)');

if(is_array($serverStates)){
    foreach($serverStates as $id => $info){
        if(!isset($info['name'])){
            $info['name'] = "Server " . $id;
        }
        echo '
            <div class="server">
                <span class="id">' . $id . '</span>
                <a class="name text-truncate" style="color:white; width:100%;" href="/mcservers/manager/?setPage=home&id=' . $id . '">' . $info['name'] . '</a>
                <span class="serverinfo">
                    Type: ' . (isset($info['type']) ? ucfirst($info['type']) : "Unknown") . '<br>
                    Version: ' . (isset($info['version']) ? $info['version'] : "Unknown") . '<br>
                    Memory: ' . (isset($info['memory']) ? $info['memory'] : "Unknown") . ' MB<br>
                </span>
                ';
                echo '
                <button onclick="changeState(\'' . $id . '\');" class="statebutton" id="server' . $id . 'statebutton"></button>
                <script>
                    setState("' . $id . '", "' . $info['state'] . '");
                </script>
            </div>
        ';
    }
}
else{
    echo "<p>Failed to read server information.</p>";
}

html::fullend();