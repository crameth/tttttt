$(document).ready(function(){
	var r = 0;
	$.PeriodicalUpdater('data.json', {
		method: 'GET',
		minTimeout: 5000, // 5 seconds
		maxTimeout: 15000, // 15 seconds
		type: 'json', // text, xml, json
		ifModified: true,
		cache: false
    }, function(remoteData, success, xhr, handle) {
    	if (r < 2)
    		++r; // ignore first 2 calls because they are not true
    	else
    		location.reload();
	});
});

function draw(o,i,t){
	if ((o > 0 && o < 10) && (i > 0 && i < 10) && (t > -1))
	{
		$.ajax({
			type: 'POST',
			url: 'draw.php',
			async: false,
			data: {outer:o, inner:i, turn:t},
			success: function(m){
				location.reload();
			}
		});
	}
	else
	{
		location.reload();
	}
}