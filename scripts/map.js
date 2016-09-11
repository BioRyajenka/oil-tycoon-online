const MAP_FIELD_IMAGE_FOLDER = "/res/map/field/";

/**
 * Creates XMLHttpRequest object in capability with all browsers
 */
function getXmlHttp() {
    var xmlhttp;
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
}

function performXmlHttpRequest(url, consumer) {
    var xmlHttp = getXmlHttp();
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) {
                consumer(JSON.parse(xmlHttp.responseText));
            } else {
                console.error("error doing xml http request");
            }
        }
    };
    xmlHttp.send(null);
}

function createParcelHtml(id) {
    this.id = id;
    var res = `<img style="position: absolute" src="/res/map/habitability.png" id="${id}_habitability">`;
    res += `<img style="width: 100%" src="${MAP_FIELD_IMAGE_FOLDER}loading.gif" id="${id}" border=0>`;
    return res;
}

function initParcelFunctions(id) {
    var imageObj = document.getElementById(id);

    imageObj.habitabilityObject = document.getElementById(`${id}_habitability`);

    imageObj.updateSubElements = function() {
        this.habitabilityObject.style.width = this.clientWidth;
        this.habitabilityObject.style.height = this.clientHeight;
        if (typeof this.data != 'undefined' && this.data.owner_id != null) {
            this.habitabilityObject.style.visibility = 'visible';
        } else {
            this.habitabilityObject.style.visibility = 'hidden';
        }
    };

    imageObj.setSize = function(width, height) {
        this.style.width = width;
        this.style.height = height;
        this.updateSubElements();
    };

    imageObj.getWidth = function() {
        return this.clientWidth;
    };

    imageObj.downloadData = function(globalX, globalY, onFinish = null) {
        // dssid <=. download session id
        if (typeof initParcelFunctions.freeDssid == 'undefined') {
            initParcelFunctions.freeDssid = 0;
        }
        var dssid = ++initParcelFunctions.freeDssid;

        imageObj.lastDssid = dssid;

        // TODO: maybe cache it?
        performXmlHttpRequest(`/scripts/get_parcel_info.php?x=${globalX}&y=${globalY}`, function (data) {
            if (imageObj.lastDssid != dssid) return;
            imageObj.data = data;
            //noinspection JSUnresolvedVariable
            imageObj.src = MAP_FIELD_IMAGE_FOLDER + data.image_name;
            imageObj.updateSubElements();
            if (onFinish != null) onFinish(imageObj);
        });

        /*
         function sleep(time) {
         return new Promise((resolve) => setTimeout(resolve, time));
         }
         var _delay = 0;
         sleep(_delay).then(() => {});
         */
    };
}