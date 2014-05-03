(function ($) {
$(function () {
	$('#file_containor').bind(
		'drop',
		function (ev) {
			$(this).removeClass('dragover');
			addQueue(ev.originalEvent.dataTransfer.files);
			return false;
		}
	).bind(
		'dragover',
		function () {
			return false;
		}
	).bind(
		'dragenter',
		function () {
			$(this).addClass('dragover');
			return false;
		}
	).bind(
		'dragleave',
		function () {
			$(this).removeClass('dragover');
			return false;
		}
	);

	$(window).bind(
		'drop',
		function () {
			return false;
		}
	).bind(
		'dragover',
		function () {
			return false;
		}
	);

	$('#files').bind(
		'change',
		function (ev) {
			addQueue(this.files);
			this.form.reset();
		}
	);
});

var queue = [],
    uploading = false,
    $status = $('#status');

if (!window.FormData || !window.XMLHttpRequest) {
	$status.text('Error: Browser unsupported. 錯誤，不支援的瀏覽器。');
}

function updateStatus(loaded, total) {
	if (!uploading) $status.text('Done.');
	else {
		$status.text(
			'Uploading: ' + (loaded >> 10).toString(10) + '/' + (total >> 10) + ' Kbytes (' + (loaded/total*100).toPrecision(3)  + '%)'
			+ ', ' + queue.length.toString(10) + ' file(s) remaining.'
		);
	}
}

function xhrProgressHandler(ev) {
	updateStatus(ev.loaded, ev.total);
}

function addQueue(filelist) {
	if (!filelist) return;
	$.each(
		filelist,
		function (i, file) {
			queue.push(file);
		}
	);
	if (!uploading) startUpload();
}

function startUpload() {
	var formData = new FormData(),
	    file = queue.shift(),
	    xhr = new XMLHttpRequest();

	formData.append("file", file);

	xhr.open("POST", './drop.php');
	uploading = true;
	//updateStatus(0, file.size);
	xhr.upload.onprogress = xhrProgressHandler;
	xhr.onreadystatechange = function xhrStateChangeHandler(ev) {
		if (xhr.readyState == 4) {
			uploading = false;
			updateStatus();
			if(xhr.status == 200) {
				if (queue.length) startUpload();
			} else {
				alert('Upload failed! 上傳失敗！');
			}
		}
	};
	xhr.send(formData);
}

})(jQuery);
