// TODO: apply namespace pattern

/*=============== switching functions ===============*/

function showParcel() {
	document.getElementById("top_section").innerHTML = PARCEL_UPPER_SECTION_HTML;

	parcelObject = new ParcelViewForParcelPage("parcel_image");
	document.getElementById("image_holder").innerHTML = parcelObject.getHTML();

	init();
}

function onParcelDemesneChanged(demesne) {
	clearActions();
	parcelObject.downloadData(demesne.x, demesne.y);
}

/*=============== ParcelViewForParcelPage ===============*/

function ParcelViewForParcelPage(id) {
	ParcelView.call(this, id);
}
ParcelViewForParcelPage.prototype = Object.create(ParcelView.prototype);

ParcelViewForParcelPage.prototype.init = function () {
	ParcelView.prototype.init.call(this);
	for (var i = 0; i < 4; i++) {
		this[`facility${i}img`] = document.getElementById(`facility${i + 1}`);
		this[`facility${i}progressbar`] = document.getElementById(`facility${i + 1}progress_bar`);
		this[`facility${i}levelspan`] = document.getElementById(`facility${i + 1}levelspan`);
	}
};

ParcelViewForParcelPage.prototype.update = function () {
	ParcelView.prototype.update.call(this);
	if (this.data == null) return;
	for (var i = 0; i < 4; i++) {
		var type = this.data[`facility${i + 1}type`];
		var level = this.data[`facility${i + 1}level`];
		this[`facility${i}img`].src = `/res/facilities/${type}.png`;
		if (type != 'none' && type != 'locked') {
			this[`facility${i}levelspan`].innerHTML = `lvl${level}`;
		} else {
			this[`facility${i}levelspan`].innerHTML = "";
		}

		var cp = this.data[`facility${i + 1}construction_progress`];
		if (cp == 'finished') {
			this[`facility${i}progressbar`].style.width = "0";
		} else {
			this[`facility${i}progressbar`].style.width = (cp * 100) + "%";
		}
		console.log("constr: " + cp);
	}
	addParcelActions();
	updateDescription(this);
};

/*=============== functions ===============*/
function addParcelActions() {

}

function facilityClick(num) {
	function generateFacilityHeaderHTML() {
		if (facilityType == 'none') return "";
		var res = `<span style="font-size: 1.5em">${facilityType}`;
		if (facilityType != 'locked') {
			res += `, ${facilityLevel} level`;
		}
		res += "</span>";
		return res;
	}

	clearActions();
	var f = parcelObject[`facility${num}img`];
	parcelObject.imageObject.src = f.src;
	parcelObject.habitabilityObject.style.visibility = "hidden";
	var facilityId = parcelObject.data[`facility${num + 1}id`];
	var facilityType = parcelObject.data['facility' + (num + 1) + "type"];
	var facilityLevel = parcelObject.data['facility' + (num + 1) + "level"];

	var reloadOnSuccessFunction = function () {
		//console.log("reloadOnSuccessFunctionResult: " + result);
		updateMoneyInfo();
		parcelObject.downloadData(selectedParcel.x, selectedParcel.y, null, true);
		clearActions();
	};

	actionButtonsHolder.innerHTML = generateFacilityHeaderHTML();

	switch (parcelObject.data[`facility${num + 1}type`]) {
		case 'none':
			var types = ['rig', 'science lab', 'scout depot', 'silo', 'transport depot'];
			types.forEach(function (item) {
				addAction({
					name: `Build ${item}`,
					action: function () {
						performDatabaseRequest("try_build", `facility_id=${facilityId}&type=${item}`, reloadOnSuccessFunction, true);
					}
				})
				;
			});
			break;
		case 'rig':
		case 'science lab':
		case 'scout depot':
		case 'silo':
		case 'transport depot':
			addAction({
				name: "Upgrade",
				action: function() {
					performDatabaseRequest("try_upgrade", `facility_id=${facilityId}`, reloadOnSuccessFunction, true);
				}
			});
			addAction({
				name: "Destroy",
				action: function () {
					performDatabaseRequest("destroy", `facility_id=${facilityId}`, reloadOnSuccessFunction, true);
				}
			});
			break;
		case 'locked':
			addAction({
				name: "Buy place"
			});
			break;
	}

	addAction({
		name: "Back",
		action: function () {
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
	parcelObject.downloadData(selectedParcel.x, selectedParcel.y, null, true); // true means show load dialog

	actionButtonsHolder = document.getElementById("action_buttons");
	actionButtonsHolder.onclick = function (event) {
		if (event.target.id == 'action_buttons') return;
		this.actions[event.target.id]();
	};
	actionButtonsHolder.actions = [];
	actionButtonsHolder.actionButtonFreeId = 0;
	actionButtonsHolder.addAction = function (action) {
		if (typeof action.action == 'undefined') {
			action.action = function () {
				alert('This feature isn\'t implemented yet. Sorry');
			}
		}
		var abid = this.actionButtonFreeId++;
		var divId = `action_button_${abid}`;
		this.actions[divId] = action.action;
		this.innerHTML += `<div class='button' style='margin-top: 5px; text-align: center; height: 3em; line-height:3em' id="${divId}">${action.name}</div>`;
	};
	actionButtonsHolder.clear = function () {
		this.actionButtonFreeId = 0;
		this.innerHTML = "";
	};
}

/*=============== top section markup ===============*/

const PARCEL_UPPER_SECTION_HTML = `
<td colspan="4" class="td_body">
	<table style="width: 100%; height: 100%">
		<tr>
			<td rowspan="2" style="vertical-align: top; width: 60%" id="action_buttons">
			</td>
			<td style="position:relative; text-align: center; width:40%; height: 1%; border: 1px solid black;"
				id="image_holder">
			</td>
		</tr>
		<tr>
			<td>
				<table
					style="margin-left:auto; margin-right:auto; border-collapse: separate; border-spacing: 0">
					<tr>
						<td style="position: relative" class="facility_td" onclick="facilityClick(0)">
							<span style="position: absolute; left: 98%; top: 100%; transform: translate(-100%, -100%); 
								white-space: nowrap;color: midnightblue" id="facility1levelspan"></span>
							<div class="facility_progress_bar" id="facility1progress_bar"></div>
							<img src="res/loading.gif" id="facility1">
						</td>
						<td style="position: relative" class="facility_td" onclick="facilityClick(1)">
							<span style="position: absolute; left: 98%; top: 100%; transform: translate(-100%, -100%); 
								white-space: nowrap;color: midnightblue" id="facility2levelspan"></span>
							<div class="facility_progress_bar" id="facility2progress_bar"></div>	
							<img src="res/loading.gif" id="facility2">
						</td>
					</tr>
					<tr>
						<td style="position: relative" class="facility_td" onclick="facilityClick(2)">
							<span style="position: absolute; left: 98%; top: 100%; transform: translate(-100%, -100%); 
								white-space: nowrap;color: midnightblue" id="facility3levelspan"></span>
								<div class="facility_progress_bar" id="facility3progress_bar"></div>
							<img src="res/loading.gif" id="facility3">
						</td>
						<td style="position: relative" class="facility_td" onclick="facilityClick(3)">
							<span style="position: absolute; left: 98%; top: 100%; transform: translate(-100%, -100%); 
								white-space: nowrap;color: midnightblue" id="facility4levelspan"></span>
							<div class="facility_progress_bar" id="facility4progress_bar"></div>
							<img src="res/loading.gif" id="facility4">
						</td>
						<!--                                    <td style="position: relative"><div style="position: absolute; width: 100%; height: 100%" class="clickable2"></div><img src="res/loading.gif" id="facility4"></td>-->
					</tr>
				</table>
			</td>
		</tr>
	</table>
</td>
`;