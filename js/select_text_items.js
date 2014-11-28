var g_items = [];

$(document).ready(function () {

	// add item handler
	document.getElementById('id_add_text_item').onclick = addItem;

	// submit
	$('#submit_finish_poll').click(function () {



		var form = document.finish_poll;
		form.elements['items'].value = JSON.stringify(
			{
				items: g_items
			});
		form.submit();

	});
});

function addItem(){
	// get text and description from inputs
	var text = document.getElementById('input_text_item').value;
	if (!text) return;

	// check if text is duplicate
	for (var i = 0; i < g_items.length; i++) {
		if (g_items[i].dt == text) {
			alert (t('polls', 'You already have an item with the same text'));
			return;
		}
	}

	var desc = document.getElementById('input_text_desc').value;

	g_items.push({dt: text, desc: desc});



	var tbl = document.getElementById('id_table_text_items');
	var trow = document.createElement('tr');

	// column delete
	var tdata = document.createElement('td');
	tdata.innerHTML = '\u2716';
	tdata.className = 'cl_del_item';
	tdata.onclick = deleteItem;
	trow.appendChild(tdata);

	// column text
	document.getElementById('input_text_item').value = '';

	tdata = document.createElement('td');
	tdata.innerHTML = text;
	tdata.onclick = deleteItem;
	trow.appendChild(tdata);

	// column description
	document.getElementById('input_text_desc').value = '';

	tdata = document.createElement('td');
	tdata.innerHTML = desc;
	tdata.onclick = deleteItem;
	trow.appendChild(tdata);

	tbl.insertBefore(trow, null);


}

function deleteItem(){
	// get text
	var text = this.parentNode.getElementsByTagName('td')[1].innerHTML;
	// remove from array
	for (var i = 0; i < g_items.length; i++) {
		if (g_items[i].dt == text) {
			g_items.splice(i, 1);
			break;
		}
	}

	// remove row
	this.parentNode.parentNode.removeChild(this.parentNode);
}