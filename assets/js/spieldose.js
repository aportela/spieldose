// put alerts on signin / signout errors
function showAlert(msg) {
	var html = '\
		<div class="alert alert-danger alert-dismissible" role="alert">\
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
			<strong>error:</strong> ' + msg + '\
		</div>\
	';
	$("div#container").append(html);										
}

// signin form submit event
$("form#f_signin").submit(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("action"),
		method: "post", 
		data: $(this).serialize(),
		dataType : "json"
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			location.reload(); 
		} else {
			showAlert(data.errorMsg);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		showAlert("ajax error");
	});				
});

// signout link
$("a#signout").click(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("href"),
		method: "post", 
		data: $(this).serialize(),
		dataType : "json"
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			location.reload(); 
		} else {
			showAlert(data.errorMsg);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		showAlert("ajax error");
	});				
});
