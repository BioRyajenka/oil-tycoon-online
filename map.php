<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 08.09.2016
 * Time: 23:55
 */

require "scripts/base_layout.php";
require "scripts/mysql_queries.php";
require "scripts/session_guard.php";


class MapPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("Some head");
    }

    protected function printBody() {
        $this->js();
        $arrowSize = "8%";
        ?>

        <!--suppress JSUnresolvedVariable -->
        <tr>
            <td colspan="4" class="td_body">
                <table style="width: 100%; table-layout:fixed">
                    <col style="width: <?php echo $arrowSize; ?>"/>
                    <col style="width: 100%"/>
                    <col style="width: <?php echo $arrowSize; ?>%"/>
                    <tr>
                        <td colspan="3" style="text-align: center" onclick="moveMap(0, -1)" class="clickable"
                            id="arrow_up">
                            <img src="/res/map/navigate_up.png">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center" onclick="moveMap(-1, 0)" class="clickable" id="arrow_left"><img
                                src="
                        /res/map/navigate_left.png"">
                        </td>

                        <td>
                            <table align="center" id="map_container"></table>
                        </td>

                        <td style="text-align: center" onclick="moveMap(1, 0)" class="clickable"><img
                                src="/res/map/navigate_right.png"></td>
                    </tr>
                    <tr style="height: <?php echo $arrowSize; ?>">
                        <td colspan="3" style="text-align: center" onclick="moveMap(0, 1)" class="clickable"
                            id="arrow_down">
                            <img src="/res/map/navigate_down.png">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <script type="application/javascript">
            initMap();
            resizeAllCells();
            redrawMap();


            var arrowSize = document.getElementById("arrow_left").offsetWidth;
            document.getElementById("arrow_up").style.height = arrowSize + "px";
            document.getElementById("arrow_down").style.height = arrowSize + "px";
        </script>

        <tr style="height: 100%">
            <td colspan="4">
                <table style="width: 100%; height: 100%; table-layout: fixed">
                    <tr>
                        <td style="width: <?php echo $arrowSize; ?>; text-align: center" onclick="previousDemesne()"
                            class="clickable">
                            <img src="/res/map/navigate_left.png"></td>
                        <td style="width: 100%; vertical-align: top; position: relative">
                            <div id="parcel_description"></div>
                            <div
                                style="position: absolute; left: 100%; top: 100%; transform: translate(-100%, -100%); white-space: nowrap"
                                class="button"
                                onclick="window.location.href = `/parcel.php?x=${selectedParcel.x}&y=${selectedParcel.y}`">
                                View cell
                            </div>
                        </td>
                        <td style="width: <?php echo $arrowSize; ?>; text-align: center" onclick="nextDemesne()"
                            class="clickable">
                            <img
                                src="/res/map/navigate_right.png"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
    }

    private function js() {
        if (isset($_GET['x']) && isset($_GET['y'])) {
            $requestedX = $_GET['x'];
            $requestedY = $_GET['y'];
        }
        ?>
        <script src="/scripts/map.js" type="text/javascript"></script>
        <script type="text/javascript">
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

            //=========================
            //noinspection JSAnnotator
            const USER_ID = <?php echo $_SESSION['user_id']?>;

            const MAP_SIZE = 5;
            const CELL_PADDING = 1;

            //noinspection JSAnnotator
            const WHOLE_MAP_WIDTH = <?php echo getMapSize()['width']?>;
            //noinspection JSAnnotator
            const WHOLE_MAP_HEIGHT = <?php echo getMapSize()['height']?>;

            //noinspection JSAnnotator
            const playerDemesne = <?php echo json_encode(getCurrentPlayerDemesne())?>;
            var currentDemesneIndex = 0;

            var mapGlobalCenterX = <?php echo(isset($requestedX) && isset($requestedY) ? $requestedX : "playerDemesne[0].x")?>;
            var mapGlobalCenterY = <?php echo(isset($requestedX) && isset($requestedY) ? $requestedY : "playerDemesne[0].y")?>;

            var selectedParcel = {x: mapGlobalCenterX, y: mapGlobalCenterY};

            var parcelObjects = [];

            /*=============== gui functions ===============*/
            function showCurrentDemesne() {
                var demesne = playerDemesne[currentDemesneIndex];
                setMapCenter(demesne.x, demesne.y);
                selectedParcel = {x: demesne.x, y: demesne.y};
                redrawMap();

                //var mid = (MAP_SIZE - 1) / 2;
                //onParcelClick(mid, mid)
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
                if (USER_ID != data.owner_id) {
                    //noinspection JSUnresolvedVariable
                    desc += `<b>land cost: </b> ${data.land_cost}$`;
                }
                descObj.innerHTML = desc;
            }

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
            }

            /*=============== util functions ===============*/
            function globalToLocalX(gX) {
                var delta = (MAP_SIZE - 1) / 2;
                return gX - mapGlobalCenterX + delta;
            }

            function globalToLocalY(gY) {
                var delta = (MAP_SIZE - 1) / 2;
                return gY - mapGlobalCenterY + delta;
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

            /*=============== map functions ===============*/

            function moveMap(dx, dy) {
                var nx = mapGlobalCenterX + dx;
                var ny = mapGlobalCenterY + dy;

                setMapCenter(nx, ny);
            }

            function setMapCenter(gX, gY) {
                var delta = (MAP_SIZE - 1) / 2 - 1;
                gX = Math.max(delta, Math.min(gX, WHOLE_MAP_WIDTH - delta - 1));
                gY = Math.max(delta, Math.min(gY, WHOLE_MAP_HEIGHT - delta - 1));

                mapGlobalCenterX = gX;
                mapGlobalCenterY = gY;
                redrawMap();
            }

            function redrawMap() {
                var delta = (MAP_SIZE - 1) / 2;
                for (var gX = mapGlobalCenterX - delta; gX <= mapGlobalCenterX + delta; gX++) {
                    for (var gY = mapGlobalCenterY - delta; gY <= mapGlobalCenterY + delta; gY++) {
                        var lX = gX - mapGlobalCenterX + delta;
                        var lY = gY - mapGlobalCenterY + delta;

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

                for (gX = mapGlobalCenterX - delta; gX <= mapGlobalCenterX + delta; gX++) {
                    lX = gX - mapGlobalCenterX + delta;
                    document.getElementById(`verticalAxis${lX}`).innerHTML = gX.toString();
                }

                for (gY = mapGlobalCenterY - delta; gY <= mapGlobalCenterY + delta; gY++) {
                    lY = gY - mapGlobalCenterY + delta;
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
        </script>
        <?php
    }

    protected function printFootage() {
    }
}

(new MapPage())->printHtml();