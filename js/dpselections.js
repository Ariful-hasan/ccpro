var loadurl = '';
var num_select_box = 1;

function load_sels(num_dids)
{
	if (num_dids == 0) num_dids++;
	show_sels(num_dids);
}

function hide_sels(i)
{
	for (var j=i+2; j < num_select_box; j++) {
		$("#disposition_id" + j).hide();
	}
}

function show_sels(i)
{
	for (var j=0; j < i; j++) {
		$("#disposition_id" + j).show();
	}
}

function set_loadurl(load_url)
{
	loadurl = load_url;
}

function set_num_select_box(num_sel_box)
{
	num_select_box = num_sel_box;
}

function reload_sels(i, val)
{
	if (i < num_select_box) {
		hide_sels(i);
		var j = i+1;
		$select = $("#disposition_id" + j);
		$select.html('<option value="">Select</option>');
		/*var ids = $select.selector;
		console.log(ids.substr(ids.length - 1));*/
		get_templates_by_disp(val, $select.selector);
		if (val.length > 0) {
			var jqxhr = $.ajax({
				type: "POST",
				url: loadurl, 
				data: { did: val },
				dataType: "json"
			})
			.done(function(data) {
				show_sels(j+1);
				if (data.length == 0) {
					//console.log('0');
					$select.hide();
			 	} else {
					$.each(data, function(key, val){
						$select.append('<option value="' + key + '">' + val + '</option>');
					});
				}
			})
			.fail(function() {
				//
			})
			.always(function() {
			});
		}
	
	
	}
}
