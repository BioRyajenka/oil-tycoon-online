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
	}
};

ParcelViewForParcelPage.prototype.update = function () {
	ParcelView.prototype.update.call(this);
	if (this.data == null) return;
	for (var i = 0; i < 4; i++) {
		this[`facility${i}img`].src = "/res/facilities/" + this.data[`facility${i + 1}type`] + ".png";
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
	var facilityType = parcelObject.data['facility' + (num + 1) + "type"];
	var facilityLevel = parcelObject.data['facility' + (num + 1) + "level"];

	actionButtonsHolder.innerHTML = generateFacilityHeaderHTML();

	switch (parcelObject.data[`facility${num + 1}type`]) {
		case 'none':
			var types = ['rig', 'science lab', 'scout depot', 'silo', 'transport depot'];
			// WARNING! Can't use double quotes here
			types.forEach(function (item) {
				addAction({
					name: `Build ${item}`,
					action: function() {
						var hideLoadingDialog = showLoadingDialog();
						var facility_id = parcelObject.data[`facility${num + 1}id`];
						performDatabaseRequest("try_build", `facility_id=${facility_id}&type=${item}`, function(result) {
							console.log("result: " + result);
							hideLoadingDialog();
							if (result == 'success') {
								parcelObject.downloadData(selectedParcel.x, selectedParcel.y);
								clearActions();
							}
						});
					}
				});
			});
			break;
		case 'rig':
		case 'science lab':
		case 'scout depot':
		case 'silo':
		case 'transport depot':
			addAction({
				name: "Upgrade"
			});
			addAction({
				name: "Destroy"
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
	parcelObject.downloadData(selectedParcel.x, selectedParcel.y);

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
						<td class="facility_td" onclick="facilityClick(0)"><img src="res/loading.gif" id="facility1"></td>
						<td class="facility_td" onclick="facilityClick(1)"><img src="res/loading.gif" id="facility2"></td>
					</tr>
					<tr>
						<td class="facility_td" onclick="facilityClick(2)"><img src="res/loading.gif" id="facility3"></td>
						<td class="facility_td" onclick="facilityClick(3)"><img src="res/loading.gif" id="facility4"></td>
						<!--                                    <td style="position: relative"><div style="position: absolute; width: 100%; height: 100%" class="clickable2"></div><img src="res/loading.gif" id="facility4"></td>-->
					</tr>
				</table>
			</td>
		</tr>
	</table>
</td>
`;