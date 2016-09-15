const MAP_IMAGE_FOLDER = "/res/map/";

//================= switching code

var currentMode;

function switchToMap() {
	currentMode = 'map';

	var sb = document.getElementById("switch_button");
	sb.onclick = switchToParcel;
	sb.innerHTML = "View cell";

	showMap();
}

function switchToParcel() {
	currentMode = 'parcel';

	var sb = document.getElementById("switch_button");
	sb.onclick = switchToMap;
	sb.innerHTML = "Back to map";

	showParcel();
}

//==================

function updateMoneyInfo() {
	performDatabaseRequest("money", "", function (result) {
		document.getElementById("money").innerHTML = `Money: ${result}$`;
	}, true);
}

var currentDemesneIndex = 0;

function showCurrentDemesne() {
	var demesne = playerDemesne[currentDemesneIndex];
	if (currentMode == 'map') {
		onMapDemesneChanged(demesne);
	} else {
		onParcelDemesneChanged(demesne);
	}
}

function previousDemesne() {
	currentDemesneIndex--;
	if (currentDemesneIndex < 0) {
		currentDemesneIndex += playerDemesne.length;
	}
	showCurrentDemesne();
}

function nextDemesne() {
	currentDemesneIndex++;
	if (currentDemesneIndex >= playerDemesne.length) {
		currentDemesneIndex -= playerDemesne.length;
	}
	showCurrentDemesne();
}

function updateDescription(parcelObj) {
	var data = parcelObj.data;
	var descObj = document.getElementById("parcel_description");
	var desc = `<b>[${data.x}:${data.y}]</b><br><br>`;

	//noinspection JSUnresolvedVariable
	var ownerNick = data.owner_nickname;

	desc += `<b>owner: </b>${ownerNick == null ? "nobody" : USER_ID == data.owner_id ? "you" : ownerNick}<br>`;
	//noinspection JSUnresolvedVariable
	desc += `<b>oil resources: </b>${data.oil_amount == null ? "undiscovered" : data.oil_amount + " barrels"}<br>`;
	desc += `<b>oil sell cost: </b> ${data.oil_sell_cost}$<br>`;
	if (USER_ID != data.owner_id) {
		//noinspection JSUnresolvedVariable
		desc += `<b>land cost: </b> ${data.land_cost}$<br>`;
	}
	descObj.innerHTML = desc;
}

//=================================

function ParcelViewForMapPage(id) {
	ParcelView.call(this, id);
}
ParcelViewForMapPage.prototype = Object.create(ParcelView.prototype);

ParcelViewForMapPage.prototype.frameBorderWidth = "3px";

ParcelViewForMapPage.prototype.getHTML = function () {
	var res = `<div style="width: 100%; height: 100%; position: absolute; visibility: hidden" id="${this.id}_frame"></div>`;
	res += `<img style="position: absolute; visibility: hidden" src="${MAP_IMAGE_FOLDER}field/selection.png" id="${this.id}_sel">`;

	res += ParcelView.prototype.getHTML.call(this);
	return res;
}

ParcelViewForMapPage.prototype.init = function () {
	ParcelView.prototype.init.call(this);
	this.selectionObject = document.getElementById(`${this.id}_sel`);
	this.frameObject = document.getElementById(`${this.id}_frame`);
	this.frameObject.setColor = function (color) {
		this.style.boxShadow = `inset 0px 0px 0px ${ParcelViewForMapPage.prototype.frameBorderWidth} ${color}`;
	}
}

ParcelViewForMapPage.prototype.update = function () {
	ParcelView.prototype.update.call(this);

	this.selectionObject.style.width = this.imageObject.clientWidth;
	this.selectionObject.style.height = this.imageObject.clientHeight;
	if (this.data != null && this.data.x == selectedParcel.x && this.data.y == selectedParcel.y) {
		this.selectionObject.style.visibility = "visible";
		updateDescription(this);
	} else {
		this.selectionObject.style.visibility = "hidden";
	}

	this.frameObject.style.width = this.imageObject.clientWidth;
	this.frameObject.style.height = this.imageObject.clientHeight;
	if (this.data != null && this.data.owner_id != null) {
		this.frameObject.style.visibility = "visible";
		this.frameObject.setColor(this.data.owner_color);
	} else {
		this.frameObject.style.visibility = "hidden";
	}
}

function performDatabaseRequest(methodName, arguments, consumer, needLoadingDialog) {
	/**
	 * Creates XMLHttpRequest object in capability with all browsers
	 */
	function getXmlHttpRequestObject() {
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

	function performXmlHttpRequest(url, consumer, needLoadingDialog) {
		if (needLoadingDialog) {
			var hideLoadingDialog = showLoadingDialog();
		}

		var xmlHttp = getXmlHttpRequestObject();
		xmlHttp.open("GET", url, true);
		xmlHttp.onreadystatechange = function () {
			if (xmlHttp.readyState == 4) {
				if (xmlHttp.status == 200) {
					if (needLoadingDialog) {
						hideLoadingDialog();
					}
					consumer(JSON.parse(xmlHttp.responseText));
				} else {
					console.error("error doing xml http request");
				}
			}
		};
		xmlHttp.send(null);
	}
	var url = `/scripts/database_adapter.php?method=${methodName}&${arguments}`;
	performXmlHttpRequest(url, consumer, needLoadingDialog);
}

function ParcelView(id) {
    this.id = id;
}

ParcelView.prototype.getHTML = function () {
    var res = `<img style="position: absolute" src="${MAP_IMAGE_FOLDER}habitability.png" id="${this.id}_habitability">`;
    //res += `<img style="position: absolute" src="/res/map/frame.png" id="${this.id}_frame">`;
    res += `<img style="width: 100%" src="/res/loading.gif" id="${this.id}_image" border=0>`;
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

ParcelView.prototype.downloadData = function (globalX, globalY, onFinish = null, needLoadingDialog = false) {
	//if (typeof onFinish != 'function') console.log(onFinish);

	// dssid <=> download session id
    if (typeof ParcelView.freeDssid == 'undefined') {
        ParcelView.freeDssid = 0;
    }
    var dssid = ++ParcelView.freeDssid;

    this.lastDssid = dssid;

    var that = this;
    performDatabaseRequest("get_parcel_info", `x=${globalX}&y=${globalY}`, function (data) {
        if (that.lastDssid != dssid) return;
        that.data = data;
        that.update();
        if (onFinish != null) onFinish(that);
    }, needLoadingDialog);

    /*
     function sleep(time) {
     return new Promise((resolve) => setTimeout(resolve, time));
     }
     var _delay = 0;
     sleep(_delay).then(() => {});
     */
};