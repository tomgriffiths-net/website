getRunnings();
setInterval(getRunnings, 10000);

function getRunnings(){
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function(){
        document.querySelectorAll('.site').forEach(site => {
            site.className = 'site off';
        });

        document.querySelectorAll('.servers > div').forEach(site => {
            site.className = 'off';
        });

        data = JSON.parse(this.responseText);
        if(data){
            Object.entries(data['sites']).forEach(([site, state]) => {
                document.getElementById('site_' + site).className = 'site ' + state;
            });

            Object.entries(data['servers']).forEach(([site, serverlist]) => {
                serverlist.forEach(server => {
                    document.getElementById('server_' + site + '_' + server).className = 'on';
                });
            });
        }
    }
    xhttp.open("GET", '?getRunnings=1');
    xhttp.send();
}

function command(command, site="", server=""){
    if(server === ""){
        document.getElementById('site_' + site).classList.add('processing');
    }
    else{
        document.getElementById('server_' + site + '_' + server).classList.add('processing');
    }

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function(){
        if(this.responseText !== "OK"){
            if(this.responseText.slice(0,7) === "weberr:"){
                alert('The operation failed with error: ' + this.responseText.slice(8));
            }
            else{
                alert('A general error occured');
            }
        }
    }
    xhttp.open("GET", '?command=' + command + '&site=' + site + '&server=' + server);
    xhttp.send();

    setTimeout(() => {
        getRunnings();
    }, 1000);
}