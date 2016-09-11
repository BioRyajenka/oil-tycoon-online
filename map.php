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
        $this->initJS();
        $arrowSize = "8%";
        ?>

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

        <script type="application/javascript">
            //onParcelClick(mapGlobalCenterX, mapGlobalCenterY);
        </script>
        <?php
    }

    private function initJS() {
        ?>
        <script src="/scripts/map.js" type="text/javascript"></script>
        <script type="text/javascript">
            const MAP_SIZE = 5;
            const CELL_PADDING = 1;

            //noinspection JSAnnotator
            const WHOLE_MAP_WIDTH = <?php echo getMapSize()['width']?>;
            //noinspection JSAnnotator
            const WHOLE_MAP_HEIGHT = <?php echo getMapSize()['height']?>;

            //noinspection JSAnnotator
            const playerDemesne = <?php echo json_encode(getCurrentPlayerDemesne())?>;
            var currentDemesneIndex = 0;

            var mapGlobalCenterX = playerDemesne[0].x;
            var mapGlobalCenterY = playerDemesne[0].y;

            var selectedParcel = {x: mapGlobalCenterX, y: mapGlobalCenterY};

            /*=============== gui functions ===============*/
            function showCurrentDemesne() {
                mapGlobalCenterX = playerDemesne[currentDemesneIndex].x;
                mapGlobalCenterY = playerDemesne[currentDemesneIndex].y;
                selectedParcel = {x: mapGlobalCenterX, y: mapGlobalCenterY};
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
                var ownerNick = data.ownerNickname;

                desc += `<b>owner: </b>${ownerNick}<br>`;
                //noinspection JSUnresolvedVariable
                desc += `<b>oil: </b>${data.oil_amount}<br>`;
                desc += `<b>land cost: </b> <span style="color: blue">todo<span>`;
                descObj.innerHTML = desc;
            }

            function onParcelClick(lX, lY) {
                var parcelObj = getFieldObject("po", lX, lY);
                var data = parcelObj.data;
                var gX = data.x;
                var gY = data.y;
                selectedParcel = {x: gX, y: gY};
                updateImageSelection();
                updateDescription(parcelObj);
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

            function inMapBounds(x, y, delta = 0) {
                return inBounds(x, delta, WHOLE_MAP_WIDTH - delta) && inBounds(y, delta, WHOLE_MAP_HEIGHT - delta);
            }

            function getFieldObject(prefix, localX, localY) {
                return document.getElementById(`${prefix}${localX}_${localY}`);
            }

            /*=============== map functions ===============*/

            function moveMap(dx, dy) {
                var nx = mapGlobalCenterX + dx;
                var ny = mapGlobalCenterY + dy;

                var delta = (MAP_SIZE - 1) / 2 - 1;

                nx = Math.max(delta, Math.min(nx, WHOLE_MAP_WIDTH - delta - 1));
                ny = Math.max(delta, Math.min(ny, WHOLE_MAP_HEIGHT - delta - 1));

                mapGlobalCenterX = nx;
                mapGlobalCenterY = ny;
                redrawMap();
            }

            function updateImageSelection(parcel = null) {
                function updateParcelSelectionImage(parcelObj) {
                    if (parcelObj == null) {
                        return;
                    }
                    var gX = parcelObj.data.x;
                    var gY = parcelObj.data.y;
                    var lX = globalToLocalX(gX);
                    var lY = globalToLocalY(gY);
                    var selectionObj = getFieldObject("sel", lX, lY);
                    if (selectedParcel != null && gX == selectedParcel.x && gY == selectedParcel.y) {
                        selectionObj.style.visibility = "visible";
                    } else {
                        selectionObj.style.visibility = "hidden";
                    }
                }

                if (parcel != null) {
                    updateParcelSelectionImage(parcel)
                } else {
                    for (var x = 0; x < MAP_SIZE; x++) {
                        for (var y = 0; y < MAP_SIZE; y++) {
                            updateParcelSelectionImage(getFieldObject("po", x, y));
                        }
                    }
                }
            }

            function redrawMap() {
                var delta = (MAP_SIZE - 1) / 2;
                for (var gX = mapGlobalCenterX - delta; gX <= mapGlobalCenterX + delta; gX++) {
                    for (var gY = mapGlobalCenterY - delta; gY <= mapGlobalCenterY + delta; gY++) {
                        var lX = gX - mapGlobalCenterX + delta;
                        var lY = gY - mapGlobalCenterY + delta;

                        var parcelObj = getFieldObject("po", lX, lY);
                        if (inMapBounds(gX, gY)) {
                            parcelObj.downloadData(gX, gY, (function (lX, lY, gX, gY) {
                                if (gX == selectedParcel.x && gY == selectedParcel.y) {
                                    return function () {
                                        var parcelObj = getFieldObject("po", lX, lY);
                                        updateDescription(parcelObj);
                                        updateImageSelection(parcelObj);
                                    }
                                } else {
                                    return updateImageSelection;
                                }
                            })(lX, lY, gX, gY));
                        } else {
                            parcelObj.lastDssid = -1; // to invalidate any download sessions on this image
                            parcelObj.src = MAP_FIELD_IMAGE_FOLDER + "terra_incognita.png";
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
                var imSize = document.getElementById("po0_0").getWidth();
                var tdSize = imSize + 2 * CELL_PADDING;
                for (var x = 0; x < MAP_SIZE; x++) {
                    for (var y = 0; y < MAP_SIZE; y++) {
                        var td = getFieldObject("td", x, y);
                        td.style.width = tdSize;
                        td.style.height = tdSize;
                        var parcelObj = getFieldObject("po", x, y);
                        parcelObj.setSize(imSize, imSize);
                        var selectionImage = getFieldObject("sel", x, y);
                        selectionImage.style.width = imSize;
                        selectionImage.style.height = imSize;
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

                var resultHTML = "";

                var cellSize = Math.round(100 / MAP_SIZE);
                for (var y = 0; y < MAP_SIZE; y++) {
                    resultHTML += "<tr>";
                    for (var x = 0; x < MAP_SIZE; x++) {
                        resultHTML += `<td style="position: relative; width: ${cellSize}%; padding: ${CELL_PADDING}px" id="td${x}_${y}" onclick="onParcelClick(${x}, ${y})">`;
                        if (y == 0) {
                            resultHTML += `<span style="position: absolute; font-size: .8em; transform: translate(-50%, -100%); left: 50%" id="verticalAxis${x}">${x}</span>`;
                        }
                        if (x == 0) {
                            resultHTML += `<span style="position: absolute; font-size: .8em; transform: translate(-100%, -50%); top: 50%" id="horizontalAxis${y}">${y}</span>`;
                        }
                        resultHTML += `<img style="position: absolute; visibility: hidden" src="${MAP_FIELD_IMAGE_FOLDER}selection.png" id="sel${x}_${y}">`;
                        resultHTML += createParcelHtml(`po${x}_${y}`);
                        resultHTML += "</td>";
                    }
                    resultHTML += "</tr>";
                }
                mapContainer.innerHTML = resultHTML;

                for (x = 0; x < MAP_SIZE; x++) {
                    for (y = 0; y < MAP_SIZE; y++) {
                        initParcelFunctions(`po${x}_${y}`);
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