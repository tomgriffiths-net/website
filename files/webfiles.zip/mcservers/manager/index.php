<?php
require '../../../localfiles/global.php';

if(!isset($_GET["id"])){
    html::loadurl("/mcservers/list/");
}
$id = $_GET["id"];
if(!website_mcservers::validateId($id)){
    html::loadurl("/mcservers/list/");
}

html::fullhead("mcservers","Edit Server","style.css");

$serverInfo = runfunction('mcservers::serverInfo("' . $id . '");');
$serverDir = str_replace("\\", "/", runfunction('mcservers::serverDir("' . $id . '")'));

?>

<div id="sidebar">
    <div onclick="setContentPage('home');">
        <img src="<?php echo $filesUrl; ?>/img/house.webp">
        <a>Home</a>
    </div>
    <div onclick="setContentPage('runtime');">
        <img src="<?php echo $filesUrl; ?>/img/bash.png">
        <a>Run</a>
    </div>
    <?php
        if($serverInfo['abilities']['datapacks'] === true){
            echo '
                <div onclick="setContentPage(\'datapacks\');">
                    <img src="' . $filesUrl . '/img/html-code.svg">
                    <a>Datapacks</a>
                </div>
            ';
        }
        if($serverInfo['abilities']['resourcepacks'] === true){
            echo '
                <div onclick="setContentPage(\'resourcepacks\');">
                    <img src="' . $filesUrl . '/img/zip-file.svg">
                    <a>Resourcepacks</a>
                </div>
            ';
        }
        if($serverInfo['abilities']['mods'] === true){
            echo '
                <div onclick="setContentPage(\'mods\');">
                    <img src="' . $filesUrl . '/img/modification.svg">
                    <a>Mods</a>
                </div>
            ';
        }
        if($serverInfo['abilities']['plugins'] === true){
            echo '
                <div onclick="setContentPage(\'plugins\');">
                    <img src="' . $filesUrl . '/img/puzzle-piece.png">
                    <a>Plugins</a>
                </div>
            ';
        }
    ?>
    <div onclick="setContentPage('actions');">
        <img src="<?php echo $filesUrl; ?>/img/settings.svg">
        <a>Actions</a>
    </div>
    <div onclick="showFileViewer('<?php echo $serverDir; ?>');">
        <img src="<?php echo $filesUrl; ?>/img/folder.png">
        <a>Files</a>
    </div>
    
    <a id="contentPageName" style="bottom:22px;"></a>
    <a id="globalServerId"><?php echo $id; ?></a>
</div>

<div id="content">
</div>

<script src="script.v2.js"></script>

<?php

if(isset($_GET['setPage'])){
    echo '<script>setContentPage("' . trim($_GET['setPage']) . '"); history.pushState(null, "", "/mcservers/manager/?id=' . $id . '");</script>';
}

if(isset($_GET['settingsApplied'])){
    echo '
        <script>
            setTimeout(function() {
                alert("Settings Applied!");
            }, 250);
        </script>
    ';
}

html::fullend();