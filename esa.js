var sendErrorLog = function(error) {
	query = "e="+error.type+"&";
	query = query + "f=" + encodeURIComponent(error.filename) + "&";
	query = query + "c=" + encodeURIComponent(error.colno) + "&";
	query = query + "l=" + encodeURIComponent(error.lineno) + "&";
	query = query + "m=" + encodeURIComponent(error.message) + "&";
	query = query + "u=" + encodeURIComponent(location.href) + "&";

	var a = document.createElement("img");
	a.src = esa_baseUrl + 'page-e.gif?' + query;
	a.width= '1';
	a.height= '1';
	document.body.appendChild(a);

};

window.addEventListener('error', e => {
	sendErrorLog(e);
});

var serialize = function(obj) {
	var str = [];
	for(var p in obj)
		if (obj.hasOwnProperty(p)) {
			str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
		}
	return str.join("&");
};

var createId = function(type) {
	let id = null;
	let store = localStorage;
	if (type === 2) {
		store = sessionStorage;
	}

	try {
		id = store.getItem("esaId");
	} catch (e) {
		error = error + "||" + e.message
	}

	if (id === null) {
		id = Date.now().toString() + Math.round(10000*Math.random());
		store.setItem("esaId", id);
	}
	return id;
};

(function() {
	var query = "";
	var properties = {};
	try {
		properties.w = window.innerWidth ? window.innerWidth : document.body.offsetWidth;
		properties.h = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
		properties.sw = window.screen.width;
		properties.sh = window.screen.height;
		properties.r = document.referrer;
		properties.pd = screen.pixelDepth;
		properties.cd = screen.colorDepth;
		properties.pr = window.devicePixelRatio;
		properties.iv = createId(1);
		properties.is = createId(2);
		properties.si = esa_setSiteId;
		properties.u = location.href;
		query = serialize(properties);
	} catch (e) {
		try {
			query = "e="+e.type+"&";
			query = query + "f=" + encodeURIComponent(e.filename) + "&";
			query = query + "c=" + encodeURIComponent(e.colno) + "&";
			query = query + "l=" + encodeURIComponent(e.lineno) + "&";
			query = query + "m=" + encodeURIComponent(e.message) + "&";
			query = query + "u=" + encodeURIComponent(location.href) + "&";			
		} catch(e) {
			query = "e=100";
		}		
	}
	var a = document.createElement("img");
	a.src = esa_baseUrl + 'page-t.gif?' + query;
	a.width= '1';
	a.height= '1';
	document.body.appendChild(a);

})();