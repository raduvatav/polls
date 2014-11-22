

$(document).ready(function () {
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

});

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
	var str = 'Are you sure you want to delete the poll ';
	var child = this;
	while( child.previousSibling != null ) {
		child = child.previousSibling;
	}
	str += '\'' + child.innerHTML.split('<')[0] + '\'' + '?';
	//str += '\n\n    ' + child.innerHTML.split('<')[0];

	if (confirm(str)) {
		var form = document.new_poll;
		form.elements['j'].value = 'delete';
		form.elements['delete_id'].value = this.id.split('_')[2];
		form.submit();
	}
}
