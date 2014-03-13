function OnReadyStateChange() {
	if (httpRequestStatus && httpRequestStatus.readyState === 4 && httpRequestStatus.responseText) {
		if (httpRequestStatus.responseText.charAt(0) !== "<") {
			var params = statusRegExp.exec(httpRequestStatus.responseText);
			OnStatus(params[1], params[2], parseInt(params[3], 10), params[4], parseInt(params[5], 10), params[6], parseInt(params[7], 10), parseInt(params[8], 10), params[9]);
		} else {
			alert(httpRequestStatus.responseText);
		}
		httpRequestStatus = null;
	}
}

function StatusLoop() {
	console.log("test");
	if (!httpRequestStatus || httpRequestStatus.readyState === 0) {
		httpRequestStatus = getXMLHTTP();
		try {
			httpRequestStatus.open("GET", "http://localhost:13579/status.html", true);
			httpRequestStatus.onreadystatechange = OnReadyStateChange;
			httpRequestStatus.send(null);
		} catch (e) {
		}
	}
	setTimeout(StatusLoop, 500);
}

OnStatus = function (title, status, pos, posStr, dur, durStr, muted, volume, filePath) {
	var maxTitle = 70;
	if (title.length > maxTitle) {
		title = title.substr(0, maxTitle - 3) + "...";
	}
	var timestr = dur > 0 && posStr && durStr ? posStr + "&nbsp;/&nbsp;" + durStr : "&nbsp;";
	if (!dur || dur === 0) {
		dur = 1;
	}
	var sbpercent = Math.floor(100 * pos / dur);
	if (e = document.getElementById("title")) {
		e.innerHTML = title;
	}
	if (e = document.getElementById("seekbarchleft")) {
		e.width = sbpercent > 0 ? sbpercent + "%" : "1px";
	}
	if (e = document.getElementById("seekbarchright")) {
		e.width = sbpercent < 100 ? (100 - sbpercent) + "%" : "1px";
	}
	if ((e = document.getElementById("status")) && e.innerHTML !== status) {
		e.innerHTML = status;
	}
	if ((e = document.getElementById("timer")) && e.innerHTML !== timestr) {
		e.innerHTML = timestr;
	}
	if (e = document.getElementById("controlvolumemute")) {
		url = "url(img/controlvolume" + (muted ? "off" : "on") + ".png)";
		if (e.style.backgroundImage !== url) {
			e.style.backgroundImage = url;
		}
	}
	if (e = document.getElementById("controlvolumegrip")) {
		volume = (document.getElementById("controlvolumebar").offsetWidth - e.offsetWidth) * volume / 100;
		e.style.position = "relative";
		e.style.top = "2px";
		e.style.left = Math.floor(volume) + "px";
	}
}