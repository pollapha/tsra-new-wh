importScripts('../js/Blob.js','../js/xlsx.core.min.js');
var dataArray = [];

self.addEventListener('message', function(e) {
  var data = e.data;
  switch (data.cmd) {
    case 'start':
      	var ws_name = "Sheet 1";
		var wb = new Workbook(), ws = sheet_from_array_of_arrays(data.msg);
		wb.SheetNames.push(ws_name);
		wb.Sheets[ws_name] = ws;
		var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:false, type: 'binary'});
      	self.postMessage(new Blob([s2ab(wbout)],{type:"application/octet-stream"}));
      	self.close(); 
      break;
    default:
      self.postMessage('Unknown command: ' + data.msg);
  };
}, false);

function s2ab(s) {
	var buf = new ArrayBuffer(s.length);
	var view = new Uint8Array(buf);
	for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
	return buf;
}

function datenum(v, date1904) {
	if(date1904) v+=1462;
	var epoch = Date.parse(v);
	return (epoch - new Date(Date.UTC(1899, 11, 30))) / (24 * 60 * 60 * 1000);
}
 
function sheet_from_array_of_arrays(data, opts) {
	var ws = {};
	var range = {s: {c:10000000, r:10000000}, e: {c:0, r:0 }};
	for(var R = 0; R != data.length; ++R) {
		for(var C = 0; C != data[R].length; ++C) {
			if(range.s.r > R) range.s.r = R;
			if(range.s.c > C) range.s.c = C;
			if(range.e.r < R) range.e.r = R;
			if(range.e.c < C) range.e.c = C;
			var cell = {v: data[R][C] };
			if(cell.v == null) continue;
			var cell_ref = XLSX.utils.encode_cell({c:C,r:R});
			
			if(typeof cell.v === 'number') cell.t = 'n';
			else if(typeof cell.v === 'boolean') cell.t = 'b';
			else if(cell.v instanceof Date) {
				cell.t = 'n'; cell.z = XLSX.SSF._table[14];
				cell.v = datenum(cell.v);
			}
			else cell.t = 's';
			
			ws[cell_ref] = cell;
		}
	}
	if(range.s.c < 10000000) ws['!ref'] = XLSX.utils.encode_range(range);
	return ws;
}
function getStrDay()
{
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth();
	var curr_year = d.getFullYear();
	var curr_h = d.getHours();
	var curr_m = d.getMinutes();
	var curr_s = d.getMilliseconds();

	return curr_year.toString() + (curr_month+1).toString() + curr_date.toString()+curr_h.toString()+curr_m.toString()+curr_s.toString();
}

function Workbook() {
	if(!(this instanceof Workbook)) return new Workbook();
	this.SheetNames = [];
	this.Sheets = {};
}
