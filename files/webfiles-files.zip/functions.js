function ajax(url, divid, reptm=0){
    const div = document.getElementById(divid);

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        div.innerHTML = this.responseText;

        const scripts = div.getElementsByTagName('script');
        for(let script of scripts){
            eval(script.textContent);
        }
    }

    div.innerHTML = '<div class="loadingAnimation"></div>';
    
    xhttp.open("GET", url);
    xhttp.send();
    
    if(reptm > 0){
        setTimeout(() => {
            ajax(url, divid, reptm);
        }, reptm);
    }
}
function encodeInString(data){
    // Convert the data to a JSON string
    const jsonString = JSON.stringify(data);

    // Convert the JSON string to a Base64 string
    const base64String = btoa(jsonString);

    // Replace characters to make the Base64 string URL-friendly
    const urlFriendlyBase64 = base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

    return urlFriendlyBase64;
}
function decodeInString(data){
    // Pad the URL-friendly Base64 string with '=' characters to make its length a multiple of 4
    const paddedData = data.padEnd(data.length + (4 - data.length % 4) % 4, '=');

    // Replace URL-friendly characters back to Base64 characters
    const base64String = paddedData.replace(/-/g, '+').replace(/_/g, '/');

    // Convert the Base64 string to a JSON string
    const jsonString = atob(base64String);

    // Parse the JSON string to get the original data
    const parsedData = JSON.parse(jsonString);

    return parsedData;
}
function debounce(func, delay=500){
    let debounceTimer;
    return function(...args) {
        const context = this;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func.apply(context, args), delay);
    };
}
function debounceBasedOnFirstParameter(func, wait=500){
    const timeouts = new Map();
    const latestParams = new Map();
    
    return function(...args) {
        const firstParam = args[0];

        latestParams.set(firstParam, args.slice(1));

        if (timeouts.has(firstParam)) {
            clearTimeout(timeouts.get(firstParam));
        }
        
        const timeoutId = setTimeout(() => {
            func(firstParam, ...latestParams.get(firstParam));
            timeouts.delete(firstParam);
            latestParams.delete(firstParam);
        }, wait);

        timeouts.set(firstParam, timeoutId);
    };
}

let lastPaths = [];
let currentBase = null;
function hideFileViewer(){
    document.getElementById('fileViewer').style.display = "none";
    //document.getElementById('fileViewerCover').style.display = "none";
    currentBase = false;
}
function showFileViewer(path){
    document.getElementById('fileViewer').style.display = "";
    //document.getElementById('fileViewerCover').style.display = "";
    fileViewerLoad(path);
}
function fileViewerLoad(path, log=true){
    if(!currentBase){
        currentBase = path;
    }

    if(log){
        lastPaths.push(document.getElementById("somewhereOnPlanetEarth").innerHTML);
    }

    let url = "/api.php?function=filesList";
    if(path){
        url += "&path=" + path;
    }
    else if(currentBase){
        url += "&path=" + currentBase;
    }

    ajax(url, "filesList");
}
function saveFileTextarea(){
    let path = document.getElementById("somewhereOnPlanetEarth").innerHTML;
    let url = "/api.php?function=filesListSave&file=" + path;
    let data = document.getElementById("filesListFileContents").value;

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function(){
        eval(this.responseText);
    }
    xhttp.open("POST", url);
    xhttp.setRequestHeader("Content-Type", "text/plain");
    xhttp.send(data);
}

function fileslistDragStart(e) {
    fileslistInitialX = e.clientX - fileslistXOffset;
    fileslistInitialY = e.clientY - fileslistYOffset;

    if (e.target === fileslistDragHandle) {
        fileslistIsDragging = true;
    }
}
function fileslistTouchStart(e) {
    fileslistInitialX = e.touches[0].clientX - fileslistXOffset;
    fileslistInitialY = e.touches[0].clientY - fileslistYOffset;

    if (e.target === fileslistDragHandle) {
        fileslistIsDragging = true;
    }
}
function fileslistDrag(e) {
    if (fileslistIsDragging) {
        e.preventDefault();
        fileslistCurrentX = e.clientX - fileslistInitialX;
        fileslistCurrentY = e.clientY - fileslistInitialY;
        fileslistXOffset = fileslistCurrentX;
        fileslistYOffset = fileslistCurrentY;
        fileslistSetTranslate(fileslistCurrentX, fileslistCurrentY, fileslistDraggableDiv);
    }
}
function fileslistTouchMove(e) {
    if (fileslistIsDragging) {
        e.preventDefault();
        fileslistCurrentX = e.touches[0].clientX - fileslistInitialX;
        fileslistCurrentY = e.touches[0].clientY - fileslistInitialY;
        fileslistXOffset = fileslistCurrentX;
        fileslistYOffset = fileslistCurrentY;
        fileslistSetTranslate(fileslistCurrentX, fileslistCurrentY, fileslistDraggableDiv);
    }
}
function fileslistSetTranslate(xPos, yPos, el) {
    el.style.transform = `translate(calc(-50% + ${xPos}px), calc(-50% + ${yPos}px))`;
}
function fileslistDragEnd() {
    fileslistInitialX = fileslistCurrentX;
    fileslistInitialY = fileslistCurrentY;
    fileslistIsDragging = false;
}