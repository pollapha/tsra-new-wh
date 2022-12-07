Array.prototype.sortByProp = function(p,pp)
{
    if(pp == undefined || pp == 1)
    {
        return this.sort(function(a,b)
        {
            return (a[p] > b[p]) ? 1 : (a[p] < b[p]) ? -1 : 0;
        });
    }
    else if(pp == -1)
    {
        return this.sort(function(a,b)
        {
            return (a[p] < b[p]) ? 1 : (a[p] > b[p]) ? -1 : 0;
        });
    }
}

window.addEventListener("load",function()
{
    this.removeEventListener('load',arguments.callee,false);

    // use <input type="text" allow=":,p" class="number">
    var number = document.getElementsByClassName("number"),len;
    if(number)
    {
        len = number.length;
        for(var i=-1;++i<len;)
        {
            number[i].addEventListener('keypress',function(e)
            {
                var allow,allowArray,allowArrayLen,key;
                allow = this.getAttribute('allow');
                if(e.keyCode!="")key = e.keyCode;
                else key = e.charCode;
                if(allow != undefined && allow !="")
                {
                    allowArray = allow.split(",");
                    allowArrayLen = allowArray.length;
                   for(var j=-1;++j<allowArrayLen;)
                   if(allowArray[j] == String.fromCharCode(key)) return;
                }
                   if (key < 48 || key > 57)
                       return e.preventDefault();
            });
        }
    }

    // use <input type="text" class="en">
    var en = document.getElementsByClassName("en");
    if(en)
    {
        len = en.length;
        var str="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-#@$%&*0123456789:",key,txt,key;
        for(var i=-1;++i<len;)
        {
            en[i].addEventListener('keypress',function(e)
            {
                if(e.keyCode!="")key = e.keyCode;
                else key = e.charCode;
                txt = String.fromCharCode(key);
                txt = str.indexOf(txt);
                if(txt == -1)e.preventDefault();
            });
        }
    }

    var upper = document.getElementsByClassName("upper");
    if(upper)
    {
        len = upper.length;
        for(var i=-1;++i<len;)
        {
            upper[i].addEventListener('keypress',function(e)
            {
                if(e.keyCode!="")key = e.keyCode;
                else key = e.charCode;
                e.preventDefault();
                e.target.value += String.fromCharCode(key).toUpperCase();
            });
        }
    }

    //use <input type="text" tabIndex="3"  class="tebEnter">
    //use <input type="text" tabIndex="4"  class="tebEnter">
    var tebEnter = document.getElementsByClassName("tebEnter");
    if(tebEnter)
    {
        var len=tebEnter.length,objHtmlArray=[],l;
        for(var i=-1;++i<len;)
        {
            objHtmlArray[i] = tebEnter[i];
            objHtmlArray[i].addEventListener('keypress', function(e)
            {
                var key;
                if(e.keyCode!="")key = e.keyCode;
                else key = e.charCode;
                if(key == 13)
                {
                    l = objHtmlArray.indexOf(this);

                    if(l != (len-1))
                    {
                        objHtmlArray[l+1].focus();
                        if(len == (l+2) && objHtmlArray[len-1].type == 'button')
                        {
                            var evt = document.createEvent("MouseEvents")
                            evt.initMouseEvent("click", true, true, window, 1, 0, 0, 0, 0,
                            false, false, false, false, 0, null);
                            objHtmlArray[len-1].dispatchEvent(evt);
                        }
                    }else objHtmlArray[0].focus();
                }
            });
        }
        objHtmlArray.sortByProp("tabIndex");
    }
},false);



(function(window)
{
    function MessageBox(select,text,callBack)
    {
    	var id = new Date().getTime(),str='';
    	var head = '', body = '', button ='';
    	var click = 0;
    	if(select == 1)
    	{
    		head = '<h4 class="modal-title">แจ้ง</h4>';
    		body = '<p>'+text+'</p>';
    		button = '<button type="button" class="btn btn-primary" data-dismiss="modal" id="moClick" onclick="this.name=1" name="0">ตกลง</button>';
    	}
    	else if(select == 2)
    	{
    		head = '<h4 class="modal-title">กรุณายืนยัน</h4>';
    		body = '<p>'+text+'  <b>ใช่</b>หรือ<b>ไม่</b></p>';
    		button = '<button type="button" class="btn btn-success" data-dismiss="modal" id="moClick" name="0" onclick="this.name=1">ใช่</button> <button data-dismiss="modal" type="button" class="btn btn-danger">ไม่</button>';
    	}
    	str +='<div class="modal" id="'+id+'">';
		str +=  '<div class="modal-dialog">';
		str +=    '<div class="modal-content">';
		str +=      '<div class="modal-header">';
		str +=        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
		str +=  head;
		str +=     '</div>';
		str +=      '<div class="modal-body">';
		str +=  body;
		str +=      '</div>';
		str +=      '<div class="modal-footer">';
		str +=  button;
		str +=      '</div>';
		str +=    '</div>';
		str +=  '</div>';
		str +='</div>';
		document.body.appendChild($(str)[0]);
		var model = $('#'+id);


		model.on('hidden.bs.modal', function (e) 
		{
			callBack($('#moClick')[0].name*1);
			document.body.removeChild(model[0]);
		})
		model.modal('show');

		$('#moClick')[0].focus();
		$('#moClick')[0].addEventListener('keypress', function(e)
        {
        	var key;
            if(e.keyCode!="")key = e.keyCode;
            else key = e.charCode;
            if(key == 13)
            {
            	$('#myModal').modal('hide');
            }
        });
    }
    window.MessageBox = MessageBox;
}(window));


(function(window)
{
    function Box(html,dHead,callBack)
    {
        var id = new Date().getTime(),str='';
        var head = '', body = '', button ='';
        var click = 0;

        head = '<h4 class="modal-title text-center">'+dHead+'</h4>';
        button = '<button type="button" class="btn btn-primary" data-dismiss="modal" id="moClick" onclick="this.name=1" name="0">ตกลง</button>';
        

        str +='<div class="modal" id="'+id+'">';
        str +=  '<div class="modal-dialog modal-lg">';
        str +=    '<div class="modal-content">';
        str +=      '<div class="modal-header">';
        str +=        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        str +=  head;
        str +=     '</div>';
        str +=      '<div class="modal-body">';
        str +=  html;
        str +=      '</div>';
        str +=      '<div class="modal-footer">';
        str +=  button;
        str +=      '</div>';
        str +=    '</div>';
        str +=  '</div>';
        str +='</div>';
        document.body.appendChild($(str)[0]);
        var model = $('#'+id);


        model.on('hidden.bs.modal', function (e) 
        {
            callBack($('#moClick')[0].name*1);
            document.body.removeChild(model[0]);
        })
        model.modal('show');

        $('#moClick')[0].focus();
        $('#moClick')[0].addEventListener('keypress', function(e)
        {
            var key;
            if(e.keyCode!="")key = e.keyCode;
            else key = e.charCode;
            if(key == 13)
            {
                $('#myModal').modal('hide');
            }
        });
    }
    window.Box = Box;
}(window));