function getAllButtons() {
	return Array.prototype.slice.call(document.getElementsByClassName("clickable")).concat(
		Array.prototype.slice.call(document.getElementsByClassName("button"))).concat(
		Array.prototype.slice.call(document.getElementsByClassName("facility_td")));
}

function blockAllButtons() {
	getAllButtons().forEach(function (button) {
		button.className += " disabled";// disabling buttons
		button.storedOnclick = button.onclick;
		button.onclick = "";
	})
}

function unblockAllButtons() {
	getAllButtons().forEach(function (button) {
		button.className = button.className.replace(/(?:^|\s)disabled(?!\S)/g, ''); // removing 'disabled' class
		button.onclick = button.storedOnclick;
	})
}

/**
 * Shows loading dialog respecting events id system (see below)
 * @returns {Function} function which should be called to hide loading dialog
 */
function showLoadingDialog() {
	var id = ++showLoadingDialog.freeId; // this system prevents from multiple dialogs

	var delay = 500; // delay before showing the dialog
	setTimeout(function () {
		if (id != showLoadingDialog.freeId) return;
		blockAllButtons();
		document.body.innerHTML += `
<div style="position:absolute; width: 60%; left: 20%; top: 25%;
			background-color: white; border: 3px solid black; 
			text-align: center" id="loading_dialog">
	<br><span style="font-size: 1.5em; font-style: italic">Please wait...</span><br>
	<img src="/res/loading2.gif">
</div>`;
	}, delay);
	return function () {
		var loadingDialog = document.getElementById("loading_dialog");
		if (id != showLoadingDialog.freeId) return;
		showLoadingDialog.freeId = 0;
		if (loadingDialog == null) return;
		loadingDialog.remove();
		unblockAllButtons();
	}
}
showLoadingDialog.freeId = 0;

function showAlertDialog(message) {

}