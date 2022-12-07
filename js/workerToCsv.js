importScripts('../js/papaparse.min.js');
self.addEventListener('message', function(e) {
  var data = e.data;
  switch (data.cmd) {
    case 'start':
        var blob = new Blob([Papa.unparse(data.msg)], {"type": "text/csv;charset=utf8;"});
        self.postMessage(blob);
        self.close(); 
      break;
    default:
      self.postMessage('Unknown command: ' + data.msg);
  };
}, false);
