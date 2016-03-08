var xmlhttp;

function loadXMLDoc(url) {
    xmlhttp = null;
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (xmlhttp != null) {
        xmlhttp.onreadystatechange = state_Change_ajaxResult;
        xmlhttp.open("GET", url, true);
        xmlhttp.send(null);
    }
    else {
        alert("Your browser does not support XMLHTTP.");
    }
}

function state_Change_ajaxResult() {
    if (xmlhttp.readyState == 4) {
        if (xmlhttp.status == 200) {
            document.getElementById('ajax_result').innerHTML = xmlhttp.responseText;
            $('#loading').fadeOut();
        }
    }
    else {
        $('#loading').fadeIn();
    }
}