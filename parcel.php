<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 11.09.2016
 * Time: 0:49
 */

require "scripts/session_guard.php";
require "scripts/base_layout.php";

$x = $_GET['x'];
$y = $_GET['y'];

class ParcelPage extends BaseLayout {
	protected function printHead() {
		self::printDefaultHead("some default header");
	}

	protected function printBody() {
		global $x;
		global $y;
		$this->js();
		?>

		<tr style="height: 70%">
			<td colspan="4" class="td_body">
				<table style="width: 100%; height: 100%">
					<tr>
						<td rowspan="2" style="vertical-align: top; width: 60%" id="action_buttons">
						</td>
						<td style="position:relative; text-align: center; width:40%; height: 1%; border: 1px solid black;"
							id="image_holder">
						</td>
						<script type="application/javascript">
							var parcelObject = new ParcelViewForParcelPage("parcel_image");
							document.getElementById("image_holder").innerHTML = parcelObject.getHTML();
						</script>
					</tr>
					<tr>
						<td>
							<table
								style="margin-left:auto; margin-right:auto; border-collapse: separate; border-spacing: 0">
								<tr>
									<td class="facility_td" onclick="facilityClick(0)"><img src="res/loading.gif"
											id="facility1"></td>
									<td class="facility_td" onclick="facilityClick(1)"><img src="res/loading.gif"
											id="facility2"></td>
								</tr>
								<tr>
									<td class="facility_td" onclick="facilityClick(2)"><img src="res/loading.gif"
											id="facility3"></td>
									<td class="facility_td" onclick="facilityClick(3)"><img src="res/loading.gif"
											id="facility4"></td>
									<!--                                    <td style="position: relative"><div style="position: absolute; width: 100%; height: 100%" class="clickable2"></div><img src="res/loading.gif" id="facility4"></td>-->
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="height: 27%">
			<td colspan="4" style="vertical-align: top; position: relative">
				oil stored<br>
				oil left<br>
				other info

				<div
					style="position: absolute; left: 100%; top: 100%; transform: translate(-100%, -100%); white-space: nowrap"
					class="button"
					onclick="window.location.href = `/map.php?x=<?php echo $x ?>&y=<?php echo $y ?>`">
					Back to the map
				</div>
			</td>
		</tr>

		<script type="application/javascript">
			init();
		</script>
		<?php
	}

	private function js() {
		global $x;
		global $y;
		?>
		<script src="scripts/map.js"></script>
		<script type="application/javascript">
			function ParcelViewForParcelPage(id) {
				ParcelView.call(this, id);
			}
			ParcelViewForParcelPage.prototype = Object.create(ParcelView.prototype);

			ParcelViewForParcelPage.prototype.init = function () {
				ParcelView.prototype.init.call(this);
				for (var i = 0; i < 4; i++) {
					this[`facility${i}`] = document.getElementById(`facility${i + 1}`);
				}
			}

			ParcelViewForParcelPage.prototype.update = function () {
				ParcelView.prototype.update.call(this);
				if (this.data != null) {
					for (var i = 0; i < 4; i++) {
						this[`facility${i}`].src = "/res/facilities/" + this.data[`facility${i + 1}type`] + ".png";
					}
				}
			}

			//=========================================
			function facilityClick(num) {
				clearActions();
				var f = parcelObject[`facility${num}`];
				parcelObject.imageObject.src = f.src;
				parcelObject.habitabilityObject.style.visibility = "hidden";

				switch (parcelObject.data[`facility${num + 1}type`]) {
					case 'none':
						var types = ['rig', 'science lab', 'scout depot', 'silo', 'transport depot'];
						types.forEach(function(item) {
							addAction({
								name: `Build ${item}`
							});
						});
						break;
					case 'rig':
						break;
					case 'science lab':
						break;
					case 'scout depot':
						break;
					case 'silo':
						break;
					case 'transport depot':
						break;
					case 'locked':
						addAction({
							name: "Buy place"
						});
						break;
				}

				addAction({
					name: "Back", onClick: function () {
						parcelObject.update();
						clearActions();
					}
				});
			}

			function addAction(action) {
				actionButtonsHolder.addAction(action);
			}

			function clearActions() {
				actionButtonsHolder.clear();
			}

			var actionButtonsHolder;

			function init() {
				parcelObject.init();
				parcelObject.downloadData(<?php echo $x?>, <?php echo $y?>);

				actionButtonsHolder = document.getElementById("action_buttons");
				actionButtonsHolder.addAction = function (action) {
					if (typeof action.onClick == 'undefined') {
						action.onClick = function () {
							alert('It\'ll be implemented soon. Sorry');
						}
					}
					this.innerHTML += `<div class='button' style='margin-top: 5px; text-align: center; height: 3em; line-height:3em' onclick="(${action.onClick})()">${action.name}</div>`;
				}
				actionButtonsHolder.clear = function () {
					this.innerHTML = "";
				}
			}
		</script>
		<?php
	}

	protected function printFootage() {
	}
}

(new ParcelPage())->printHtml();