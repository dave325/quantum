/**
 * 
 */
jQuery(document).ready(function() {
	var j = jQuery;
	// Will populate the table when page loads
	j.ajax({
		method : "POST",
		dataType : "json",
		url : "dbInfo.php"
	})
		.done(function(data) {
			// cycle through data and insert into table
			// I would check for empty data and inform user that nothing is there yet
			for (i in data) {
				j('#itemTable tbody').prepend(
					"<tr><td>" + data[i].asin + "</td><td>" + data[i].title + "</td><td>" + data[i].mpn + "</td><td>" + data[i].price + "</td></tr>"
				);
			}
			console.log(data);
		})
		.fail(function(data) {
			console.log("Fail " + data);
		});
	// When user enters asin number the ajax call will be triggered
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
				// Check if data is valid and if there are no errors
				if (data.Items.Request.IsValid && typeof data.Items.Request.Errors == "undefined") {
					j("#addItemForm").html(
						"<input hidden='hidden' value='true' type='text' name='addItemForm' id='itemFormValid'/>" +
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
				} else {
					// Will display error
					console.log('fail: ' + data.Items.Request.Errors.Error.Message);
					j("#addItemForm").html(data.Items.Request.Errors.Error.Message);
				}
			})
			.fail(function(data) {
				console.log("Fail " + data.responseText);
			});
	});
	// Will add the data to the database and then add new data to table
	j('#addItemForm').submit(function(e) {
		e.preventDefault();
		var formInfo = {};
		formInfo.addItemForm = j('#itemFormValid').val();
		formInfo.addAsin = j('[name=addAsin]').val();
		formInfo.addTitle = j('[name=addTitle]').val();
		formInfo.addMpn = j('[name=addMpn]').val();
		formInfo.addPrice = j('[name=addPrice]').val();
		j.ajax({
			method : "POST",
			data : formInfo,
			dataType : "json",
			url : "dbInfo.php"
		})
			.done(function(data) {
				// Want to check for new data to add
				for (i in data) {
					console.log(data[i].asin + " " + formInfo.addAsin );
					// Checks for new asin added to database
					if (data[i].asin == formInfo.addAsin) {
						j('#itemTable tbody').prepend(
							"<tr><td>" + data[i].asin + "</td><td>" + data[i].title + "</td><td>" + data[i].mpn + "</td><td>" + data[i].price + "</td></tr>"
						);
					}
				}
				console.log(data);
			})
			.fail(function(data) {
				console.log("Fail " + data.reponseText);
			});
	});
});