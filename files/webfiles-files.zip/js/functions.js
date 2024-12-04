function ajax(url, divid, reptm){
    const div = document.getElementById(divid);

    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        div.innerHTML = this.responseText;

        const scripts = div.getElementsByTagName('script');
        for (let script of scripts) {
            const scriptTag = document.createElement('script');
            if (script.src) {
                scriptTag.src = script.src;
            } else {
                scriptTag.text = script.textContent;
            }
            document.head.appendChild(scriptTag).parentNode.removeChild(scriptTag);
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
function debounce(func, delay){
    let debounceTimer;
    return function(...args) {
        const context = this;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func.apply(context, args), delay);
    };
}