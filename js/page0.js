

$(document).ready(function () {
    var cells = document.getElementsByClassName('cl_delete');
    for (var i = 0; i < cells.length; i++) {
        var cell = cells[i];
        cells[i].onclick = deletePoll;
    }

	// set handler
    cells = document.getElementsByClassName('cl_link');
    for (var i = 0; i < cells.length; i++) {
        var cell = cells[i];
        cells[i].onclick = pollClicked;
    }

	// submit
    $('#id_submit').click(function() {
        var form = document.form1;
        var title = document.getElementById('id_in_title').value;
        var descr = document.getElementById('id_in_descr').value;
		// we need at least a title
        if (title.length < 1) {
            alert('title cannot be empty!');
        }
        else {
            form.elements['j'].value = JSON.stringify(
			{
				q: 'page1', 
				title: title, 
				descr: descr
			});
            form.submit();
        }
    });

});

// open an existing poll
function pollClicked(e) {
    // td has inner 'input'; value is poll id
    var cell = e.target;
    var id = cell.getElementsByTagName('input')[0].value;

    var form = document.form1;
    form.elements['j'].value = JSON.stringify({q: 'vote', poll_id: id});
    form.submit();
}

function deletePoll(e){
	var str = 'are you sure you want to delete this poll?';
	var child = this;
	while( child.previousSibling != null ) {
		child = child.previousSibling;
	}
	str += '\n\n    ' + child.innerHTML.split('<')[0];

	if (confirm(str)) {

		document.form1.elements['j'].value = JSON.stringify(
		{
			q: 'delete',
			id: this.id.split('_')[2]
		});
		document.form1.submit();

	}
}
