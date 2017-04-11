/**
 * 
 */
jQuery(document).ready(function() {
	var j = jQuery;
	/*
	j.ajax({
		method:"POST",
		dataType:"json",
		url:"WebContent/dbInfo.php"
	})
	.done(function(data){
		console.log("success " + data);
	})
	.fail(function(data){
		console.log("Fail " + data);
	});
	*/
	j('#lookupForm').submit(function(e) {
		e.preventDefault();
		var data = {};
		data.itemLookup = j('#itemValid').val();
		data.asinNum = j('#itemLookup').val();
		console.log(data);
		j.ajax({
			method : "POST",
			data : data,
			dataType : "json",
			url : "dbInfo.php"
		})
			.done(function(data) {
				console.log(data);
				if (data.Items.Request.IsValid && typeof data.Items.Request.Errors == "undefined") {
					j("#addItemForm").html(
						"<p>Asin: " + data.Items.Item.ASIN + "</p>" +
						"<input type='text' hidden='hidden' value='" + data.Items.Item.ASIN + "' name='addAsin' />" +
						"<p>Title: " + data.Items.Item.ItemAttributes.Title + "</p>" +
						"<input type='text' hidden='hidden' value='" + data.Items.Item.ItemAttributes.Title + "' name='addTitle'/>" +
						"<p>MPN: " + data.Items.Item.ItemAttributes.MPN + "</p>" +
						"<input type='text' hidden='hidden' value='" + data.Items.Item.ItemAttributes.MPN + "' name='addMpn'/>" +
						"<p>Price: " + data.Items.Item.ItemAttributes.ListPrice.FormattedPrice + "</p>" +
						"<input type='text' hidden='hidden' value='" + data.Items.Item.ItemAttributes.ListPrice.FormattedPrice + "' name='addPrice'/>" +
						"<input type='submit' value='Add Item' />"
					);
				}else{
					console.log('fail: ' + data.Items.Request.Errors.Error.Message);
					j("#addItemForm").html(data.Items.Request.Errors.Error.Message);
				}
			})
			.fail(function(data) {
				console.log("Fail " + data.responseText);
			});
	});
	j('#addItemForm').submit(function(e) {
		e.preventDefault();
		j.ajax({
			method : "POST",
			dataType : "json",
			url : "dbInfo.php"
		})
			.done(function(data) {
				console.log("success " + data);
			})
			.fail(function(data) {
				consoloe.log("Fail " + data);
			});
	});
});