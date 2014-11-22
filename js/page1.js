var g_current_displ_month;
var g_current_displ_year;
var g_current_displ_month_id = 'id_header_curr_month';

var g_selected_dates = []; // {day, month, year}
var g_chosen_datetimes = [];

$(document).ready(function () {

    $('#id_header_prev_month').click(function() {changeMonth(-1);});
    $('#id_header_next_month').click(function() {changeMonth(+1);});
    fillCalendar(new Date(Date.now()));
	
	$('#id_time_display').click(addTimeColumn);
	
	// hour header
	for (var i = 0; i < 24; i++) {
		$('#id_hour_' + i).click(hourClicked);
	}
	// minute header
	for (var i = 0; i < 60; i += 5){
		var min = '' + i;
		if (i < 10) min = '0' + i;
		$('#id_min_' + min).click(minuteClicked);
	}

    $('#submit_finish_poll').click(function() {
        if (g_chosen_datetimes.length === 0) {
            alert('Nothing selected!\nClick on cells to turn them green...');
            return;
        }

        var form = document.finish_poll;
        form.elements['chosen_dates'].value = JSON.stringify(
            {
                chosen: g_chosen_datetimes
			});
		form.submit();
    });
});
function hourClicked(e) {

    // clear selected
    var sel = document.getElementsByClassName('cl_hour_selected');
    if (sel.length > 0) sel[0].className = 'cl_hour';

    // get current time display value
    var spl = document.getElementById('id_time_display').innerHTML.split(':');
	document.getElementById('id_time_display').innerHTML = e.target.innerHTML + ':' + spl[1];

    // set this selected
    e.target.className = 'cl_hour_selected';

}
function minuteClicked(e) {
    // clear selected
    var sel = document.getElementsByClassName('cl_min_selected');
    if (sel.length > 0) sel[0].className = 'cl_min';

    // get current time display value
	var spl = document.getElementById('id_time_display').innerHTML.split(':');
    document.getElementById('id_time_display').innerHTML = spl[0] + ':' + e.target.innerHTML;

    // set this selected
    e.target.className = 'cl_min_selected';

}

function changeMonth(offset) {
    var curr = new Date(g_current_displ_year, g_current_displ_month + offset, 1);
    fillCalendar(curr);
    return false;
}

function fillCalendar(now_date) {
    g_current_displ_month = now_date.getMonth();
    g_current_displ_year = now_date.getFullYear();
    // 0 <= month <= 11
    var elem = document.getElementById(g_current_displ_month_id);
    elem.id = g_current_displ_month_id = 'id_header_month_' + (now_date.getMonth()+1);
    $('#id_header_curr_year').html('' + now_date.getFullYear());

    //var cal_table = document.getElementsByTagName("tbody").item(0);
    var day = 1;
    var last_day_in_month = (new Date(now_date.getFullYear(), now_date.getMonth() + 1, 0).getDate());

    // 0 == Sunday
    var weekday_first = ((new Date(now_date.getFullYear(), now_date.getMonth(), 1)).getDay() + 6) % 7;

    var start_adding_days = false;
    //var last_day_in_month = (new Date(2014, 2, 0).getDate());
    for (var i = 0; i < 6; i++) {
        for (var j = 0; j < 7; j++) {

            if (!start_adding_days && (weekday_first == j)) start_adding_days = true;

            var td = $('#id_cell_' + i + '_' + j);
            if (start_adding_days && (day <= last_day_in_month)) {
				
				if (isDateSelected(g_current_displ_year, g_current_displ_month, day)) {
	                td.attr('class', 'td_selected');
				}
				else {
	                td.attr('class', 'td_shown');
				}
                td.html('' + day++);
                td.unbind('click').click(calendarCellClicked); //unbind: otherwise add handler every time
            }
            else {
                td.attr('class', 'td_hidden');
            }
        }
    }
}
// search a date in the array (optional remove)
function isDateSelected(year, month, day, remove) {
	for (var i = 0; i < g_selected_dates.length; i++) {
		var el = g_selected_dates[i];
		if ((el.day === day) && (el.month === month) && (el.year === year)){
			if (remove) {
				g_selected_dates.splice(i, 1);
			}
			return true;
		}
	}
	return false;
}
// add a date to the polls
function calendarCellClicked(e) {
    var selected_day = Number(e.target.innerHTML);
	
	var cl = e.target.className;
	var obj = {
		year : g_current_displ_year,
		month : g_current_displ_month,
		day : selected_day
	};

	if (cl === 'td_shown') {
		e.target.className = 'td_selected';
		
		// add date
		addDateRow(obj);
	}

}
function addDateRow(obj) {
    // write object in array
    g_selected_dates.push(obj);


    // do html stuff
    var tbl = document.getElementById('id_poss_table');
    var trow = document.createElement('tr');

    // find index where to insert...
    var rows = tbl.getElementsByTagName('tr');

    // insert sorted
    var inserted = false;
    for (var i = 1; i < rows.length; i++){ // #0 = 'date/time'
        var str = rows[i].getElementsByTagName('th')[0].innerHTML;
        var h_obj = {day: str.split('.')[0], month: str.split('.')[1] - 1, year: str.split('.')[2]}

        if (myCmpDate(obj, h_obj) < 0) {
            tbl.insertBefore(trow, rows[i]);
            inserted = true;
            break;
        }
    }
    if (!inserted){
        tbl.insertBefore(trow, null);
    }


    var theader = document.createElement('th');

    theader.className = 'cl_date_time_header';
    theader.onclick = removeDateRow;

    var str_date = '' + obj.day < 10 ? ('0' + obj.day) : obj.day;
    str_date += '.';
    str_date += ((obj.month+1) < 10) ? ('0' + (obj.month+1)) : (obj.month+1);
    str_date += '.' + obj.year;

    theader.appendChild(document.createTextNode(str_date));

    // add header (date)
    trow.appendChild(theader);


    var header_row = document.getElementById('id_poss_table_header_row');
    var cols = header_row.getElementsByTagName('th').length;


    for (var i = 1; i < cols; i++){
        var tdata = document.createElement('td');
        tdata.innerHTML = '&nbsp;';
        tdata.onclick = selectPoss;
        trow.appendChild(tdata);
    }
}
// helper for inserting sorted
function myCmpDate(obj1, obj2){
    str1 = '' + obj1.year;
    str1 += obj1.month > 9 ? obj1.month : '0' + obj1.month;
    str1 += obj1.day > 9 ? obj1.day : '0' + obj1.day;
    str2 = '' + obj2.year;
    str2 += obj2.month > 9 ? obj2.month : '0' + obj2.month;
    str2 += obj2.day > 9 ? obj2.day : '0' + obj2.day;

    return ( ( str1 == str2 ) ? 0 : ( ( str1 > str2 ) ? 1 : -1 ) );
}

function selectPoss(e){
    var cell = e.target;
    var header_row = document.getElementById('id_poss_table_header_row');
    var time = header_row.getElementsByTagName('th')[cell.cellIndex].innerHTML;
    var date = cell.parentNode.getElementsByTagName('th')[0].innerHTML;

    if (cell.style.backgroundColor == 'green') {
        cell.style.backgroundColor = 'white';
        // remove from array
        for (var i = 0; i < g_chosen_datetimes.length; i++){
            if ((g_chosen_datetimes[i].date) === date && (g_chosen_datetimes[i].time === time))
            g_chosen_datetimes.splice(i, 1);
            break;
        }
    }
    else {
        cell.style.backgroundColor = 'green';
        // add to array
        g_chosen_datetimes.push({date: date, time: time});
    }

}
function removeDateRow(e) {
    var spl = e.target.innerHTML.split('.');
	
	// delete from array
    isDateSelected(Number(spl[2]), Number(spl[1]) - 1, Number(spl[0]), true);

    fillCalendar(new Date(g_current_displ_year, g_current_displ_month, 1));

    this.parentNode.parentNode.removeChild(this.parentNode);
}
function addTimeColumn() {
    var tbl = document.getElementById('id_poss_table');
    var header_row = document.getElementById('id_poss_table_header_row');

    var theader = document.createElement('th');
    theader.className = 'cl_date_time_header';
    theader.onclick = removeTimeColumn;

    var time = document.getElementById('id_time_display').innerHTML;

    theader.appendChild(document.createTextNode(time));

    // insert sorted:
    var cols = header_row.getElementsByTagName('th');

    var index_before = -1;
    for (var i = 1; i < cols.length; i++){ // #0 = 'date/time'
        if (time === cols[i].innerHTML) {

            // w/o id?? :)
            cols[i].id = 'klkjqwtrhashbf';
            $('#klkjqwtrhashbf').animate({fontSize: "2em"}, 500).animate({fontSize: "1em"}, 250);
            cols[i].id = null;

            return;
        }

        if (time < cols[i].innerHTML){
            index_before = i;
            break;
        }
    }

    if (index_before > 0){
        header_row.insertBefore(theader, cols[index_before]);
    }
    else {
        header_row.appendChild(theader);
    }

    for (var i = 1; i < tbl.rows.length; i++){
        var cells = tbl.rows[i].getElementsByTagName('td');
        var tdata = document.createElement('td');
        tdata.innerHTML = '&nbsp;';
        tdata.onclick = selectPoss;

        if (index_before > 0) {
            tbl.rows[i].insertBefore(tdata, cells[index_before]);
        }
        else {
            tbl.rows[i].appendChild(tdata);
        }

    }

}
function removeTimeColumn(){
    var tbl = document.getElementById('id_poss_table');

    // get column index
    var ch_ind = 0;
    var child = this;
    while( (child = child.previousSibling) != null ) {
        ch_ind++;
    }

    // remove this index cell from all columns
    for (var i = 0; i < tbl.rows.length; i++){
        var row = tbl.rows[i];
        // todo: -2?? (textnode -> th -> textnode -> x
        row.deleteCell(ch_ind - 2);
    }

}

