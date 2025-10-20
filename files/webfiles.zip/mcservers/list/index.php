<?php
require '../../../localfiles/global.php';

if(isset($_GET['ajax'])){
    $inc = 15;
    $min = "001";
    $max = str_pad($min + $inc,3,"0",STR_PAD_LEFT);
    while(istheseserversexisting($min,$max)){
        $min = str_pad($min,3,"0",STR_PAD_LEFT);
        $max = str_pad($min + $inc,3,"0",STR_PAD_LEFT);
        $range = $min . '-' . $max;
        echo '<a style="font-size:1.2rem; margin-left:7px;" class="link" href="/mcservers/list/?range=' . $range . '">Servers ' . $range . '</a><br>';
        $min = str_pad($max+1,3,"0",STR_PAD_LEFT);
        $max = str_pad($min + $inc,3,"0",STR_PAD_LEFT);
    }
    exit;
}
function istheseserversexisting($min,$max):bool{
    $rangei = $min;
    $rangevals = array();
    while($rangei < $max+1){
        $rangevals[] = $rangei;
        $rangei++;
    }
    $range = array();
    foreach($rangevals as $rangeval){
        $range[] = str_pad($rangeval,3,"0",STR_PAD_LEFT);
    }

    foreach($range as $server){
        if(runfunction('mcservers::validateId("' . $server . '",false);')){
            return true;
        }
    }
    return false;
}

html::fullhead("mcservers","",'style.css');

echo '<script src="script.js"></script>';

$i = 1;
$range = false;
if(isset($_GET['range'])){
    if(preg_match("/^\d{3}-\d{3}$/",$_GET['range']) === 1){
        $range = $_GET['range'];
        $min = intval(substr($range,0,3));
        $max = intval(substr($range,4,3));

        $rangei = $min;
        while($rangei < $max+1){
            $rangevals[] = $rangei;
            $rangei++;
        }

        $range = array();
        foreach($rangevals as $rangeval){
            $range[] = str_pad($rangeval,3,"0",STR_PAD_LEFT);
        }
    }
}

if($range === false){
    echo '
        <div id="listdiv">

        </div>
        <script>
            ajax("index.php?ajax=1","listdiv",0);
        </script>
    ';
}
else{
    $i = 1;
    foreach($range as $serverId){
        $serverData = runfunction('mcservers::serverInfo("' . $serverId . '")');
        if(!is_array($serverData)){
            continue;
        }
        if(in_array($serverId,$range)){
            echo '
                <div class="server">
                    <span class="id">' . $serverId . '</span>
                    <a class="name text-truncate" style="color:white; width:100%;" href="/mcservers/manager/?setPage=home&id=' . $serverId . '">' . $serverData['name'] . '</a>
                    <span class="serverinfo">
                        Type: ' . ucfirst($serverData['version']['type']) . '<br>
                        Version: ' . $serverData['version']['version'] . '<br>
                        Memory: ' . $serverData['run']['max_ram_mb'] . ' MB<br>
                    </span>
                    ';
                    echo'
                    <button onclick="changeState(\'' . $serverId . '\');" class="statebutton" id="server' . $serverId . 'statebutton"></button>
                </div>
                <script>
                    setTimeout(() => {
                        serverState("' . $serverId . '");
                    }, ' . $i . '0);
                </script>
            ';
            $i += 5;
        }
    }
}

html::end();
echo '
        
';
html::end2();