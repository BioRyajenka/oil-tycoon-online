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
require_once "scripts/log.php";


class MapPage extends BaseLayout {
	protected function printHead() {
		echo "<th id='money'>Money here</th>";

		\clearLog();
	}

	protected function printBody() {
		$this->js();
		$arrowSize = "8%";
		?>

		<tr id="top_section"></tr>
		<tr id="bottom_section">
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
								class="button" id="switch_button">
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
			var bs = document.getElementById("bottom_section");
			bs.style.height = "100%";
			switchToMap();
			bs.style.height = bs.clientHeight - 2; // 2 because of padding, I suppose
		</script>
		<?php
	}

	private function js() {
		if (isset($_GET['x']) && isset($_GET['y'])) {
			$requestedX = $_GET['x'];
			$requestedY = $_GET['y'];
		}
		?>
		<script type="text/javascript">
			//noinspection JSAnnotator
			const USER_ID = <?php echo $_SESSION['user_id']?>;
			//noinspection JSAnnotator
			const WHOLE_MAP_WIDTH = <?php echo getMapSize()['width']?>;
			//noinspection JSAnnotator
			const WHOLE_MAP_HEIGHT = <?php echo getMapSize()['height']?>;
			//noinspection JSAnnotator
			const playerDemesne = <?php echo json_encode(getCurrentPlayerDemesne())?>;
		</script>
		<script src="/scripts/js/aux_gui.js" type="text/javascript"></script>
		<script src="/scripts/js/game_common.js" type="text/javascript"></script>
		<script src="/scripts/js/map.js" type="text/javascript"></script>
		<script src="/scripts/js/parcel.js" type="text/javascript"></script>
		<?php
	}

	protected function printFootage() {
	}
}

(new MapPage())->printHtml();