<?php
require '../localfiles/global.php';

$f = false;
if(isset($_GET["function"])){
    $f = $_GET["function"];
}
if($f === false || empty($f)){
    echo "No function specified";
    exit;
}

if($f === "filesList"){
    if(!isset($_GET['path']) || empty($_GET['path'])){
        $cliCwd = runfunction('getcwd()');
        if(!is_string($cliCwd)){
            echo "Failed to contact communicator";
            exit;
        }
        $_GET['path'] = $cliCwd;
    }
    else{
        $_GET['path'] = $_GET['path'];
    }

    echo '
        <script>
            document.getElementById("somewhereOnPlanetEarth").innerHTML = \'' . str_replace("\\","/",$_GET['path']) . '\';
            document.getElementById("fileViewerSave").disabled = true;
        </script>
    ';

    if(runfunction("is_file(base64_decode('" . base64_encode($_GET['path']) . "'))")){
        $contents = runfunction("file_get_contents(base64_decode('" . base64_encode($_GET['path']) . "'))");
        if(!is_string($contents)){
            echo '
                <p style="position:absolute; top:10%; left:50%; transform:translateX(-50%);">Failed to get contents for file ' . $_GET['path'] . '</p>
            ';
            exit;
        }

        echo '
            <textarea id="filesListFileContents" style="width:100%; height:100%; position:absolute; top:0; left:0; resize:none; overflow:auto; white-space:pre;">' . $contents . '</textarea>
            <script>document.getElementById("fileViewerSave").disabled=false;</script>
        ';
    }
    else{
        $files = runfunction("website::listFiles(base64_decode('" . base64_encode($_GET['path']) . "'))");
        if(!is_array($files)){
            echo '
                <p style="position:absolute; top:10%; left:50%; transform:translateX(-50%);">Failed to list files for ' . $_GET['path'] . '</p>
            ';
            exit;
        }

        if(empty($files)){
            echo '
                <p style="position:absolute; top:10%; left:50%; transform:translateX(-50%);">Empty folder.</p>
            ';
            exit;
        }

        foreach($files as $file){
            $icon = $filesUrl . "/img/unknown-doc.svg";

            if(substr($file['path'], -4) === ".jar"){
                $icon = $filesUrl . "/img/java-icon.png";
            }

            if(!$file['isBinary']){
                $icon = $filesUrl . "/img/document.svg";
            }
            if($file['isDir']){
                $icon = $filesUrl . "/img/folder.svg";
            }
            
            echo '
                <div class="fileListItem" onclick="' . ($file['isBinary'] ? '' : 'fileViewerLoad(\'' . str_replace("\\","/",$file['path']) . '\')') . ';">
                    <img style="' . ((substr($icon, -15) === "unknown-doc.svg") ? 'margin-right:3px; margin-left:7px;' : 'margin-right:5px; margin-left:5px;') . '" src="' . $icon . '">
                    <span>' . basename($file['path']) . '</span>
                    <span>' . date("Y-m-d H:i:s", $file['modified']) . '</span>
                    <span>' . ($file['isDir'] ? "" : $file['size']) . '</span>
                </div>
            ';
        }
    }
}
elseif($f === "filesListSave"){
    if(!isset($_GET['file']) || $_SERVER['REQUEST_METHOD'] !== 'POST'){
        echo 'alert("Failed");';
        exit;
    }

    $contents = file_get_contents('php://input');
    if(!is_string($contents)){
        echo 'alert("Failed");';
        exit;
    }

    if(!runfunction("file_put_contents(base64_decode('" . base64_encode($_GET['file']) . "'), base64_decode('" . base64_encode($contents) . "'))")){
        echo 'alert("Failed");';
        exit;
    }

    echo 'alert("File Saved");';
}