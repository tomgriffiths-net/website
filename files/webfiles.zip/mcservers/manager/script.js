let serverId = document.getElementById('globalServerId').innerHTML;
let serverInfo = JSON.parse(ajax_mcserver('/mcservers/api/?function=serverInfo&id=' + serverId));

function setContentPage(pageName){
    document.getElementById('contentPageName').innerHTML = pageName;
    ajax('/mcservers/api/?function=manager_page_' + pageName + '&id=' + serverId,'content',0);
}
function currentPage(){
    let page = document.getElementById('contentPageName');
    if(page !== undefined){
        return page.innerHTML;
    }
    return "";
}

//

function ajax_mcserver(url){
    const xhttp = new XMLHttpRequest();
    xhttp.open("GET", url, false);
    xhttp.send();
    return xhttp.responseText;
}
function updateServerStats(){
    var stats = JSON.parse(ajax_mcserver('/mcservers/api/?function=serverStats&id=' + serverId));
    pageName = currentPage();
    if(pageName === "home"){
        memoryPercent = (stats['memory']/serverInfo['run']['max_ram_mb'])*100;
        memoryUsageBar = document.getElementById('servermemoryusage');
        memoryUsageBar.style.width = memoryPercent + "%";
        if(memoryPercent > 90){
            memoryUsageBar.style.backgroundColor = "red";
        }
        else if(memoryPercent > 75){
            memoryUsageBar.style.backgroundColor = "orange";
        }
        else{
            memoryUsageBar.style.backgroundColor = "lime";
        }
        document.getElementById('servermemorytext').innerHTML = "Memory: " + Math.round(memoryPercent) + "%";

        cpuUsageBar = document.getElementById('servercpuusage');
        cpuUsageBar.style.width = stats['cpu'] + "%";
        if(stats['cpu'] > 90){
            cpuUsageBar.style.backgroundColor = "red";
        }
        else if(stats['cpu'] > 80){
            cpuUsageBar.style.backgroundColor = "orange";
        }
        else{
            cpuUsageBar.style.backgroundColor = "lime";
        }
        document.getElementById('servercputext').innerHTML = "CPU: " + Math.round(stats['cpu']) + "%";

        let statebutton = document.getElementById('statebutton');
        if(stats['state'] === "stopped"){
            statebutton.style.backgroundColor = "red";
            statebutton.innerHTML = "Start Server";
        }
        else if(stats['state'] === "starting"){
            statebutton.style.backgroundColor = "orange";
            statebutton.innerHTML = "Starting...";
        }
        else if(stats['state'] === "online"){
            statebutton.style.backgroundColor = "lime";
            statebutton.innerHTML = "Stop Server";
        }
        else if(stats['state'] === "stopping"){
            statebutton.style.backgroundColor = "orange";
            statebutton.innerHTML = "Stopping...";
        }
        else if(stats['state'] === "backup"){
            statebutton.style.backgroundColor = "blue";
            statebutton.innerHTML = "Backing up";
        }
        else{
            statebutton.style.backgroundColor = "lightgrey";
            statebutton.innerHTML = "Unknown";
        }

        document.getElementById('homepage_backupButton').disabled = (stats['state'] != "stopped");
        document.getElementById('homepage_killButton').disabled = (stats['state'] == "stopped");

        canSendCommand = false;
        if(stats['state'] === "online"){
            canSendCommand = true;
        }
        document.getElementById('homepage_commandButton').disabled = !canSendCommand;

        document.getElementById('homepage_eventLogText').innerHTML += stats['newoutput'];
        eventLog = document.getElementById('homepage_eventLog');
        eventLog.scrollTo(0, eventLog.scrollHeight);
    }
    

    setTimeout(() => {
        updateServerStats();
    }, 1000);
    
}
function changeState(){
    const statebutton = document.getElementById('statebutton');
    if(statebutton.innerHTML === "Stop Server"){
        statebutton.innerHTML = "Stopping...";
        statebutton.style.backgroundColor = "orange";
        ajax_mcserver('/mcservers/api/?function=stop_server&id=' + serverId);
    }
    else if(statebutton.innerHTML === "Start Server"){
        statebutton.innerHTML = "Starting...";
        statebutton.style.backgroundColor = "orange";
        ajax_mcserver('/mcservers/api/?function=start_server&id=' + serverId);
    }
    else{
        
    }
}
//
setContentPage("home");
setTimeout(() => {
    updateServerStats();
}, 1000);

//

function backupServer(){
    ajax_mcserver('/mcservers/api/?function=backup_server&id=' + serverId);
}
function killServer(){
    ajax_mcserver('/mcservers/api/?function=kill_server_yesiamsure&id=' + serverId);
}
function sendCommand(){
    input = document.getElementById('homepage_commandInput');
    ajax_mcserver('/mcservers/api/?function=sendCommand&id=' + serverId + '&command=' + encodeInString(input.value));
    input.value = "";
}

//

let modrinthContentType = "mod";
const modrinthContentSearchDebounced = debounce(function() {modrinthContentSearch()}, 500);
window.modrinthContentSearchDebounced = modrinthContentSearchDebounced;
function setModrinthContentType(type){
    let types = ["mod", "modpack", "plugin", "resourcepack", "datapack"];
    if(types.includes(type)){
        modrinthContentType = type;
    }
    else{
        console.log("Invalid modrinth content type.");
    }
}

function modrinthContentSearch(){
    var query = document.getElementById('modrinthContentSearchBar').value;
    ajax('/mcservers/api/?function=modrinthContentSearch&id=' + serverId + '&type=' + modrinthContentType + '&query=' + query,'modrinthContentSearchResults',0);
}
function modrinthContentLoadResultVersions(projectId,projectNameEncoded){
    document.getElementById('modrinthContentSearchButton').removeAttribute('onclick');
    document.getElementById('modrinthContentSearchBar').removeAttribute('onkeyup');
    document.getElementById('modrinthContentSearchButtonImage').style.opacity = "50%";
    document.getElementById('modrinthContentSearchBar').disabled = true;
    document.getElementById('modrinthContentSearchBar').value = decodeInString(projectNameEncoded);
    ajax('/mcservers/api/?function=modrinthContentLoadResultVersions&id=' + serverId + '&projectId=' + projectId + '&type=' + modrinthContentType,'modrinthContentSearchResults',0);
}
function modrinthContentApply(projectId, projectVersion){
    ajax('/mcservers/api/?function=modrinthContentApply&id=' + serverId + '&projectId=' + projectId + '&projectVersion=' + projectVersion + '&type=' + modrinthContentType,'modrinthContentSearchResults',0);
    setTimeout(() => {
        document.getElementById('modrinthContentSearchButton').onclick = function (){modrinthContentSearch();};
        document.getElementById('modrinthContentSearchBar').onkeyup = function(){modrinthContentSearchDebounced();};
        document.getElementById('modrinthContentSearchButtonImage').style.opacity = "100%";
        document.getElementById('modrinthContentSearchBar').disabled = false;
        document.getElementById('modrinthContentSearchBar').value = "";
    }, 500);
}