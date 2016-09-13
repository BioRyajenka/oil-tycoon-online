// TODO: apply namespace pattern

/*=============== switching functions ===============*/

function showMap() {
	document.getElementById("top_section").innerHTML = MAP_UPPER_SECTION_HTML;

	initMap();
	resizeAllCells();
	redrawMap();

	var arrowSize = document.getElementById("arrow_left").offsetWidth;
	document.getElementById("arrow_up").style.height = arrowSize + "px";
	document.getElementById("arrow_down").style.height = arrowSize + "px";
}

function onMapDemesneChanged(demesne) {
	setMapCenter(demesne.x, demesne.y);
	selectedParcel = {x: demesne.x, y: demesne.y};
	redrawMap();
}

/*=============== ParcelViewForMapPage ===============*/

function ParcelViewForMapPage(id) {
	ParcelView.call(this, id);
}
ParcelViewForMapPage.prototype = Object.create(ParcelView.prototype);

ParcelViewForMapPage.prototype.frameBorderWidth = "3px";

ParcelViewForMapPage.prototype.getHTML = function () {
	var res = `<div style="width: 100%; height: 100%; position: absolute; visibility: hidden" id="${this.id}_frame"></div>`;
	res += `<img style="position: absolute; visibility: hidden" src="${MAP_IMAGE_FOLDER}field/selection.png"
		id="${this.id}_sel">`;

	res += ParcelView.prototype.getHTML.call(this);
	return res;
};

ParcelViewForMapPage.prototype.init = function () {
	ParcelView.prototype.init.call(this);
	this.selectionObject = document.getElementById(`${this.id}_sel`);
	this.frameObject = document.getElementById(`${this.id}_frame`);
	this.frameObject.setColor = function (color) {
		this.style.boxShadow = `inset 0px 0px 0px ${ParcelViewForMapPage.prototype.frameBorderWidth} ${color}`;
	}
};

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
};

/*=============== consts & vars ===============*/
const MAP_SIZE = 5;
const CELL_PADDING = 1;

var selectedParcel = {x: playerDemesne[0].x, y: playerDemesne[0].y};

var mapGlobalCenter;
setMapCenter(playerDemesne[0].x, playerDemesne[0].y);

var parcelObjects = [];

/*=============== gui functions ===============*/

function onParcelClick(lX, lY) {
	var parcelObj = getParcelObject(lX, lY);
	var gX = parcelObj.data.x;
	var gY = parcelObj.data.y;
	var prevLocalX = globalToLocalX(selectedParcel.x);
	var prevLocalY = globalToLocalY(selectedParcel.y);
	var prevParcelObj = getParcelObject(prevLocalX, prevLocalY);
	selectedParcel = {x: gX, y: gY};
	if (prevParcelObj != null) prevParcelObj.update(); // hiding selection
	parcelObj.update(); // updating selection and description

	playerDemesne.forEach(function(d, i) {
		if (d.x == gX && d.y == gY) {
			currentDemesneIndex = i;
		}
	});
}

/*=============== map functions ===============*/

function moveMap(dx, dy) {
	var nx = mapGlobalCenter.x + dx;
	var ny = mapGlobalCenter.y + dy;

	setMapCenter(nx, ny);
	redrawMap();
}

function setMapCenter(gX, gY) {
	var delta = (MAP_SIZE - 1) / 2 - 1;
	gX = Math.max(delta, Math.min(gX, WHOLE_MAP_WIDTH - delta - 1));
	gY = Math.max(delta, Math.min(gY, WHOLE_MAP_HEIGHT - delta - 1));
	mapGlobalCenter = {x: gX, y: gY};
}

function redrawMap() {
	var delta = (MAP_SIZE - 1) / 2;
	for (var gX = mapGlobalCenter.x - delta; gX <= mapGlobalCenter.x + delta; gX++) {
		for (var gY = mapGlobalCenter.y - delta; gY <= mapGlobalCenter.y + delta; gY++) {
			var lX = gX - mapGlobalCenter.x + delta;
			var lY = gY - mapGlobalCenter.y + delta;

			var parcelObj = getParcelObject(lX, lY);
			if (inMapBounds(gX, gY)) {
				parcelObj.downloadData(gX, gY);
			} else {
				parcelObj.lastDssid = -1; // to invalidate any download sessions on this image
				parcelObj.data = null;
				parcelObj.imageObject.src = MAP_IMAGE_FOLDER + "field/terra_incognita.png";
				parcelObj.update();
			}
		}
	}

	for (gX = mapGlobalCenter.x - delta; gX <= mapGlobalCenter.x + delta; gX++) {
		lX = gX - mapGlobalCenter.x + delta;
		document.getElementById(`verticalAxis${lX}`).innerHTML = gX.toString();
	}

	for (gY = mapGlobalCenter.y - delta; gY <= mapGlobalCenter.y + delta; gY++) {
		lY = gY - mapGlobalCenter.y + delta;
		document.getElementById(`horizontalAxis${lY}`).innerHTML = gY.toString();
	}
}

function resizeAllCells() {
	var mapContainer = document.getElementById("map_container");
	mapContainer.removeAttribute("width");
	mapContainer.style.tableLayout = "auto";
	var imSize = getParcelObject(0, 0).getWidth();
	var tdSize = imSize + 2 * CELL_PADDING;
	for (var x = 0; x < MAP_SIZE; x++) {
		for (var y = 0; y < MAP_SIZE; y++) {
			var td = document.getElementById(`td${x}_${y}`);
			td.style.width = tdSize;
			td.style.height = tdSize;
			var parcelObj = getParcelObject(x, y);
			parcelObj.setSize(imSize, imSize);
		}
	}
}

/**
 * Fills the table <b>(#mapContainer)</b> with a temp contents to create base markup.
 */
function initMap() {
	var mapContainer = document.getElementById("map_container");

	// first step - loading data and determine best cell size
	mapContainer.style.width = "100%";
	mapContainer.style.tableLayout = "fixed";
	mapContainer.style.borderCollapse = "collapse";
	//mapContainer.style.height = "100%";

	for (var x = 0; x < MAP_SIZE; x++) {
		parcelObjects[x] = [];
	}

	var resultHTML = "";
	var cellSize = Math.round(100 / MAP_SIZE);
	for (y = 0; y < MAP_SIZE; y++) {
		resultHTML += "<tr>";
		for (x = 0; x < MAP_SIZE; x++) {
			resultHTML += `<td style="position: relative; width: ${cellSize}%; padding: ${CELL_PADDING}px" id="td${x}_${y}" onclick="onParcelClick(${x}, ${y})">`;
			if (y == 0) {
				resultHTML += `<span style="position: absolute; font-size: .8em; transform: translate(-50%, -100%); left: 50%" id="verticalAxis${x}">${x}</span>`;
			}
			if (x == 0) {
				resultHTML += `<span style="position: absolute; font-size: .8em; transform: translate(-100%, -50%); top: 50%" id="horizontalAxis${y}">${y}</span>`;
			}
			var po = parcelObjects[x][y] = new ParcelViewForMapPage(`po${x}_${y}`);
			resultHTML += po.getHTML();
			resultHTML += "</td>";
		}
		resultHTML += "</tr>";
	}
	mapContainer.innerHTML = resultHTML;

	for (x = 0; x < MAP_SIZE; x++) {
		for (y = 0; y < MAP_SIZE; y++) {
			parcelObjects[x][y].init();
		}
	}
}

/*=============== util functions ===============*/
function globalToLocalX(gX) {
	var delta = (MAP_SIZE - 1) / 2;
	return gX - mapGlobalCenter.x + delta;
}

function globalToLocalY(gY) {
	var delta = (MAP_SIZE - 1) / 2;
	return gY - mapGlobalCenter.y + delta;
}

function inBounds(val, from, to) {
	return val >= from && val <= to;
}

function inMapBounds(gX, gY, delta = 0) {
	return inBounds(gX, delta, WHOLE_MAP_WIDTH - delta) && inBounds(gY, delta, WHOLE_MAP_HEIGHT - delta);
}

function getParcelObject(localX, localY) {
	if (!inBounds(localX, 0, MAP_SIZE - 1) || !inBounds(localY, 0, MAP_SIZE - 1)) return null;
	return parcelObjects[localX][localY];
}

/*=============== top section markup ===============*/

const MAP_UPPER_SECTION_HTML = `
<td colspan="4" class="td_body"> 
	<table style="width: 100%; table-layout:fixed">
		<col style="width: <?php echo $arrowSize; ?>"/>
		<col style="width: 100%"/>
		<col style="width: <?php echo $arrowSize; ?>%"/>
		<tr>
			<td colspan="3" style="text-align: center" onclick="moveMap(0, -1)" class="clickable" id="arrow_up">
				<img src="/res/map/navigate_up.png">
			</td>
		</tr>
		<tr>
			<td style="text-align: center" onclick="moveMap(-1, 0)" class="clickable" id="arrow_left"><img
					src="/res/map/navigate_left.png"">
			</td>
	
			<td><table align="center" id="map_container"></table></td>
	
			<td style="text-align: center" onclick="moveMap(1, 0)" class="clickable"><img
					src="/res/map/navigate_right.png">
			</td>
		</tr>
		<tr style="height: <?php echo $arrowSize; ?>">
			<td colspan="3" style="text-align: center" onclick="moveMap(0, 1)" class="clickable" id="arrow_down">
				<img src="/res/map/navigate_down.png">
			</td>
		</tr>
	</table>
</td>
`;