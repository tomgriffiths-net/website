function serverState(serverId){
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function(){
        const serverData = JSON.parse(this.responseText);
        const statebutton = document.getElementById('server' + serverId + 'statebutton');

        if(serverData['state'] === "stopped"){
            statebutton.style.backgroundColor = "red";
            statebutton.innerHTML = "Start Server";
        }
        else if(serverData['state'] === "starting"){
            statebutton.style.backgroundColor = "orange";
            statebutton.innerHTML = "Starting...";
            setTimeout(() => {
                serverState(serverId);
            }, 2000);
        }
        else if(serverData['state'] === "online"){
            statebutton.style.backgroundColor = "lime";
            statebutton.innerHTML = "Stop Server";
        }
        else if(serverData['state'] === "stopping"){
            statebutton.style.backgroundColor = "orange";
            statebutton.innerHTML = "Stopping...";
            setTimeout(() => {
                serverState(serverId);
            }, 2000);
        }
        else if(serverData['state'] === "backup"){
            statebutton.style.backgroundColor = "blue";
            statebutton.innerHTML = "Backing up";
            setTimeout(() => {
                serverState(serverId);
            }, 2000);
        }
        else if(serverData['state'] === "loading"){
            statebutton.style.backgroundColor = "lightgrey";
            statebutton.innerHTML = "Loading...";
            setTimeout(() => {
                serverState(serverId);
            }, 5000);
        }
        else{
            statebutton.style.backgroundColor = "lightgrey";
            statebutton.innerHTML = "Unknown";
        }
    }
    xhttp.open("GET", '/admin/mcservers/api/?function=serverStats&id=' + serverId);
    xhttp.send();
}
function changeState(serverId){
    const statebutton = document.getElementById('server' + serverId + 'statebutton');
    if(statebutton.innerHTML === "Stop Server"){
        statebutton.innerHTML = "Loading...";
        ajax_mcserver('/admin/mcservers/api/?function=stop_server&id=' + serverId);
        serverState(serverId);
    }
    else if(statebutton.innerHTML === "Start Server"){
        statebutton.innerHTML = "Loading...";
        ajax_mcserver('/admin/mcservers/api/?function=start_server&id=' + serverId);
        serverState(serverId);
    }
    statebutton.click();
}
function ajax_mcserver(url){
    const xhttp = new XMLHttpRequest();
    xhttp.open("GET", url, false);
    xhttp.send();
    return xhttp.responseText;
}