function serverState(serverId){
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function(){
        const serverData = JSON.parse(this.responseText);
        
        setState(serverId, serverData['state']);
    }
    xhttp.open("GET", '/mcservers/api/?function=serverStats&id=' + serverId);
    xhttp.send();
}
function changeState(serverId){
    const statebutton = document.getElementById('server' + serverId + 'statebutton');
    if(statebutton.innerHTML === "Stop Server"){
        statebutton.innerHTML = "Loading...";
        ajax_mcserver('/mcservers/api/?function=stop_server&id=' + serverId);
        serverState(serverId);
    }
    else if(statebutton.innerHTML === "Start Server"){
        statebutton.innerHTML = "Loading...";
        ajax_mcserver('/mcservers/api/?function=start_server&id=' + serverId);
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
function setState(serverId, state){
    const statebutton = document.getElementById('server' + serverId + 'statebutton');

    if(state === "stopped"){
        statebutton.style.backgroundColor = "red";
        statebutton.innerHTML = "Start Server";
    }
    else if(state === "starting"){
        statebutton.style.backgroundColor = "orange";
        statebutton.innerHTML = "Starting...";
        setTimeout(() => {
            serverState(serverId);
        }, 2000);
    }
    else if(state === "online"){
        statebutton.style.backgroundColor = "lime";
        statebutton.innerHTML = "Stop Server";
    }
    else if(state === "stopping"){
        statebutton.style.backgroundColor = "orange";
        statebutton.innerHTML = "Stopping...";
        setTimeout(() => {
            serverState(serverId);
        }, 2000);
    }
    else if(state === "backup"){
        statebutton.style.backgroundColor = "blue";
        statebutton.innerHTML = "Backing up";
        setTimeout(() => {
            serverState(serverId);
        }, 2000);
    }
    else if(state === "loading"){
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