var edit_access_id = null;

$(document).ready(function () {
	edit_access_id = null;

    var cells = document.getElementsByClassName('cl_delete');
    for (var i = 0; i < cells.length; i++) {
        var cell = cells[i];
        cells[i].onclick = deletePoll;
    }

	// set "go to poll" handler
    cells = document.getElementsByClassName('cl_link');
    for (var i = 0; i < cells.length; i++) {
        var cell = cells[i];
        cells[i].onclick = pollClicked;
    }

    // set "show poll url" handler
    cells = document.getElementsByClassName('cl_poll_url');
    for (var i = 0; i < cells.length; i++) {
        var cell = cells[i];
        cells[i].onclick = function(e) {
            // td has inner 'input'; value is poll url
            var cell = e.target;
            var url = cell.getElementsByTagName('input')[0].value;
            window.prompt("Copy to clipboard: Ctrl+C, Enter", url);
        }
    }

	// prevent poll creation without title
	var submit_create_poll = document.getElementById('submit_create_poll');
	if (submit_create_poll != null) {
		submit_create_poll.onclick = function() {
			var title = document.getElementById('text_title');
			if (title == null || title.value.length < 1) {
				alert('You must enter at least a title for the new poll.');
				return false;
			}
			else {
				var groups = [];
				cells = document.getElementsByClassName('cl_group_item_selected');
				for (var i = 0; i < cells.length; i++) {
					groups.push(cells[i].innerHTML);
				}
				var users = [];
				cells = document.getElementsByClassName('cl_user_item_selected');
				for (var i = 0; i < cells.length; i++) {
					users.push(cells[i].id.split('_')[1]);
				}

				var form = document.create_poll;
				form.elements['access_ids'].value = JSON.stringify(
					{
						groups: groups,
						users: users
					});

				return true;
			}
		}
	}


	// users, groups
	var elem = document.getElementById('select');
	if (elem) elem.onclick = showAccessDialog;
	elem = document.getElementById('button_close_access');
	if (elem) elem.onclick = closeAccessDialog;

	cells = document.getElementsByClassName('cl_group_item');
	for (var i = 0; i < cells.length; i++) {
		cells[i].onclick = groupItemClicked;
	}
	cells = document.getElementsByClassName('cl_user_item');
	for (var i = 0; i < cells.length; i++) {
		cells[i].onclick = userItemClicked;
	}

	// edit access
	cells = document.getElementsByClassName('cl_poll_access');
	for (var i = 0; i < cells.length; i++) {
		cells[i].onclick = editAccess;
	}

});

function editAccess(e){

	// search left for cell with poll_id
	var child = this;
	while( child.className != 'cl_link' && child.previousSibling != null ) {
		child = child.previousSibling;
	}
	// inner hidden input has id
	edit_access_id = child.getElementsByTagName('input')[0].value;
	showAccessDialog(e);
}

function groupItemClicked() {
	if (this.className == 'cl_group_item') {
		this.className = 'cl_group_item_selected';
	}
	else {
		this.className = 'cl_group_item';
	}
}
function userItemClicked() {
	if (this.className == 'cl_user_item') {
		this.className = 'cl_user_item_selected';
	}
	else {
		this.className = 'cl_user_item';
	}
}
//Popup dialog
function showAccessDialog(e) {
	var message = 'Please choose the groups or users you want to add to your poll.';

	// get the screen height and width
	var maskHeight = $(document).height();
	var maskWidth = $(window).width();

	// calculate the values for center alignment
	var dialogTop = (maskHeight / 3) - ($('#dialog-box').height());
	var dialogLeft = (maskWidth / 2) - ($('#dialog-box').width() / 2);

	// assign values to the overlay and dialog box
	$('#dialog-overlay').css({height: maskHeight, width: maskWidth}).show();
	$('#dialog-box').css({top: dialogTop, left: dialogLeft}).show();

	// display the message
	$('#dialog-message').html(message);

	cells_grp = document.getElementsByClassName('cl_group_item');
	cells_usr = document.getElementsByClassName('cl_user_item');

	if (edit_access_id) {
		// called to edit poll; set selected
		var groups = [];
		var users = [];
		var arr = e.target.innerHTML.split(';');

		for (var i = 0; i < arr.length; i++) {
			var item = arr[i].replace(/^\s+|\s+$/g, '');
			if (item.indexOf('group_') == 0) {
				for (var j = 0; j < cells_grp.length; j++){
					var cell_text = 'group_' + cells_grp[j].innerHTML;
					if (cell_text == item){
						cells_grp[j].className = 'cl_group_item_selected';
						break;
					}
				}
			}
			else if (item.indexOf('user_') == 0) {
				for (var j = 0; j < cells_usr.length; j++){
					var cell_text = 'user_' + cells_usr[j].innerHTML;
					if (cell_text == item){
						cells_usr[j].className = 'cl_user_item_selected';
						break;
					}
				}
			}
		}


	}

}
function closeAccessDialog() {
	if (edit_access_id) {
		var html = '';
		cells = document.getElementsByClassName('cl_user_item_selected');
		for (var i = 0; i < cells.length; i++) {
			//users.push(cells[i].innerHTML);
			html += 'user_' + cells[i].innerHTML + ';';
		}

		cells = document.getElementsByClassName('cl_group_item_selected');
		for (var i = 0; i < cells.length; i++) {
			//groups.push(cells[i].innerHTML);
			html += 'group_' + cells[i].innerHTML + ';';
		}

		// search cell with this id
		cells = document.getElementsByClassName('cl_link');
		var id_cell = null;
		for (var i = 0; i < cells.length; i++){
			var input = cells[i].getElementsByTagName('input');
			if (input) {
				if (input[0].value == edit_access_id) {
					id_cell = cells[i];
					break;
				}
			}
		}
		if (id_cell) {
			// get access cell and replace content
			var parent = id_cell.parentNode;
			var access_cell = parent.getElementsByClassName('cl_poll_access')[0];
			access_cell.innerHTML = html;
		}

	}

	$.post("ajax/access.php", { access: html, poll_id: edit_access_id });

	$('#dialog-box').hide();
	return false;
}

// open an existing poll
function pollClicked(e) {
    // td has inner 'input'; value is poll id
    var cell = e.target;
    var id = cell.getElementsByTagName('input')[0].value;
	
    var form = document.new_poll;
    form.elements['j'].value = 'vote';
	form.elements['poll_id'].value = id;

    form.submit();
}

function deletePoll(e) {
	var str = 'Are you sure you want to delete this poll:\n\n';
	var child = this;
	while( child.className != 'cl_link' && child.previousSibling != null ) {
		child = child.previousSibling;
	}
	str += '\'' + child.innerHTML.split('<')[0].trim() + '\'' + '?';

	// reformat: delete '\n   name'? -> delete \n'   name'?
	//str = str.replace(/\'\n\s+/, '\n              \'');

	if (confirm(str)) {
		var form = document.new_poll;
		form.elements['j'].value = 'delete';
		form.elements['delete_id'].value = this.id.split('_')[2];


		form.submit();
	}
}
