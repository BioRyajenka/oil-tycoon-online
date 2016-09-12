const MAP_IMAGE_FOLDER = "/res/map/";

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

function ParcelView(id) {
    this.id = id;
}

ParcelView.prototype.getHTML = function () {
    var res = `<img style="position: absolute" src="${MAP_IMAGE_FOLDER}habitability.png" id="${this.id}_habitability">`;
    //res += `<img style="position: absolute" src="/res/map/frame.png" id="${this.id}_frame">`;
    res += `<img style="width: 100%; src="/res/loading.gif" id="${this.id}_image" border=0>`;
    return res;
};

/**
 * Intended to be called after html parsed
 */
ParcelView.prototype.init = function(){
    this.data = null;
    this.imageObject = document.getElementById(`${this.id}_image`);
    this.habitabilityObject = document.getElementById(`${this.id}_habitability`);
}

ParcelView.prototype.update = function () {
    //noinspection JSUnresolvedVariable
	if (this.data != null) {
		this.imageObject.src = MAP_IMAGE_FOLDER + "field/" + this.data.image_name;
	}
    this.habitabilityObject.style.width = this.imageObject.clientWidth;
    this.habitabilityObject.style.height = this.imageObject.clientHeight;
    if (this.data != null && this.data.owner_id != null) {
        this.habitabilityObject.style.visibility = 'visible';
    } else {
        this.habitabilityObject.style.visibility = 'hidden';
    }
};

ParcelView.prototype.setSize = function (width, height) {
    this.imageObject.style.width = width;
    this.imageObject.style.height = height;
    this.update();
};

ParcelView.prototype.getWidth = function () {
    return this.imageObject.clientWidth;
};

ParcelView.prototype.downloadData = function (globalX, globalY, onFinish = null) {
    // dssid <=. download session id
    if (typeof ParcelView.freeDssid == 'undefined') {
        ParcelView.freeDssid = 0;
    }
    var dssid = ++ParcelView.freeDssid;

    this.lastDssid = dssid;

    var that = this;
    // TODO: maybe cache it?
    performXmlHttpRequest(`/scripts/get_parcel_info.php?x=${globalX}&y=${globalY}`, function (data) {
        if (that.lastDssid != dssid) return;
        that.data = data;
        that.update();
        if (onFinish != null) onFinish(that);
    });

    /*
     function sleep(time) {
     return new Promise((resolve) => setTimeout(resolve, time));
     }
     var _delay = 0;
     sleep(_delay).then(() => {});
     */
};