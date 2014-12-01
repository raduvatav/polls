var g_selected_yes = [];
var g_selected_no = [];

var strong_cnt_regex = /([^\d]*)(\d+)([^\d]*)/;
var max_votes = 0;

$(document).ready(function () {

    // check if ac
    /*var ac = document.getElementById('id_ac_detected');
    if (ac != null) {
        var name = null;
        while ((name === null) || (name.length < 3)) {
            name = window.prompt("You are not registered.\nPlease enter your name to vote\n(at least 3 characters!)", "");
        }
        //ac.innerHTML = name;
		var user_name = document.getElementById('user_name');
		user_name.value = name;
    }*/

    var cells = [];
    cells = document.getElementsByClassName('cl_click');

    // loop over 'user' cells
    for (var i = 0; i < cells.length; i++){
        // set handler for cell-clicks (yes/no/maybe)
        cells[i].onclick = possClicked;

        // fill arrays (if this is edit)
        if (cells[i].className.indexOf('cl_yes') >= 0){
            g_selected_yes.push(cells[i].getElementsByTagName('input')[0].value)
        }
        if (cells[i].className.indexOf('cl_no') >= 0){
            g_selected_no.push(cells[i].getElementsByTagName('input')[0].value)
        }
    }


	$('#button_home').click(function() {
		document.getElementById('j').value = 'home';
		document.finish_poll.submit();
	});

    $('#submit_finish_poll').click(function() {
        // todo
        var form = document.finish_poll;

        var comm = document.getElementById('comment_box');

        var ac_user = '';
        var ac = document.getElementById('user_name');
        if (ac != null) {
            //ac_user = ac.innerHTML;
			if(ac.value.length >= 3){
				ac_user = ac.value;
				//alert("Thank you for voting!")
			} else {
				alert(t('polls', 'You are not registered.\nPlease enter your name to vote\n(at least 3 characters!)'));
				return;
			}
		}
        form.elements['options'].value = JSON.stringify(
            {
                sel_yes: g_selected_yes,
                sel_no: g_selected_no,
                comment: comm.value,
                ac_user: ac_user
            });
        form.submit();
    });

});

function possClicked(e) {
    // get column index
    var child = this;
    var ch_ind = child.cellIndex;
	
    /*while( (child = child.previousSibling) != null ) {
        ch_ind++;
    }*/
	var cell_tot_y = document.getElementById('id_y_' + (ch_ind - 1));
	var cell_tot_n = document.getElementById('id_n_' + (ch_ind - 1));

	//alert('col: ' + ch_ind);

    // td has inner 'input'; value is date/time string
    var cell = e.target;
    var dt = cell.getElementsByTagName('input')[0].value;


    if (cell.className.indexOf('cl_maybe') >= 0) {
        g_selected_yes.push(dt);

        cell.className = cell.className.replace('cl_maybe', 'cl_yes');
		strong_cnt_regex.exec(cell_tot_y.innerHTML);
		var cnt_value = Number(RegExp.$2) + 1;
		if(cnt_value > max_votes) max_votes = cnt_value;
		cell_tot_y.innerHTML = (RegExp.$1 + cnt_value + RegExp.$3);
    }
    else if (cell.className.indexOf('cl_yes') >= 0) {
        g_selected_no.push(dt);
        for (var i = 0; i < g_selected_yes.length; i++){
            if (g_selected_yes[i] === dt) {
                g_selected_yes.splice(i, 1);
                break;
            }
        }
		strong_cnt_regex.exec(cell_tot_y.innerHTML);
		cell_tot_y.innerHTML = (RegExp.$1 + (Number(RegExp.$2) - 1) + RegExp.$3);
		cell_tot_n.innerHTML = ('' + (Number(cell_tot_n.innerHTML) + 1));
        cell.className = cell.className.replace('cl_yes', 'cl_no');
    }
    else if (cell.className.indexOf('cl_no') >= 0) {
        for (var i = 0; i < g_selected_no.length; i++){
            if (g_selected_no[i] === dt) {
                g_selected_no.splice(i, 1);
                break;
            }
        }
		cell_tot_n.innerHTML = ('' + (Number(cell_tot_n.innerHTML) - 1));

        cell.className = cell.className.replace('cl_no', 'cl_maybe');
    }
	findNewMaxCount();
	updateStrongCounts();

}

function findNewMaxCount(){
	var i = 0;
	var cell_tot_y = document.getElementById('id_y_' + i);
	max_votes = 0;
	while(cell_tot_y != null){
		strong_cnt_regex.exec(cell_tot_y.innerHTML);
		var curr = Number(RegExp.$2);
		if(curr > max_votes) max_votes = curr;
		cell_tot_y = document.getElementById('id_y_' + (++i));
	}
}

function updateStrongCounts(){
	var i = 0;
	var cell_tot_y = document.getElementById('id_y_' + i);
	var cell_tot_n = document.getElementById('id_n_' + i);

	var cel_win = document.getElementById('id_total_' + i);

	while(cell_tot_y != null) {

		strong_cnt_regex.exec(cell_tot_y.innerHTML);
		var curr = Number(RegExp.$2);
		var win = '' + Number(curr) - Number(cell_tot_n.innerHTML);

		if(win < max_votes) {
			cell_tot_y.innerHTML = curr;
			cel_win.style = "background-color: white; font-size: 1em;";

		}
		else {
			cell_tot_y.innerHTML = ('<strong>' + curr + '</strong>');
			cel_win.style = "background-color: green;font-size: 2em;";
			cel_win.innerHTML = curr;
		}

		cel_win.innerHTML = win;

		cell_tot_y = document.getElementById('id_y_' + (++i));
		cell_tot_n = document.getElementById('id_n_' + i);

		cel_win = document.getElementById('id_total_' + i);
	}
}