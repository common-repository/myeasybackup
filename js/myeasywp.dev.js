/**
 * myeasywp.com
 * settings
 * 24 July 2012
 */
function toggleOptions(id) {
	var toggler=document.getElementById(id+'-toggler');
	var contents=document.getElementById(id+'-contents');
	if(toggler && contents) {

		if(contents.style.display=='none') {
			//open element
			contents.style.display='block';
			toggler.className='optionsGroup-toggler-close';
		}
		else {
			//close element
			contents.style.display='none';
			toggler.className='optionsGroup-toggler-open';
		}
	}
}

if($) {

	$(document).ready(function() {

		$('#signup').submit(function() {

			$('#mc-response').html('Adding email address...');

			$.ajax({

				url: location.protocol+'//'+location.hostname+'/wp-content/plugins/'+myeasyplugin+'/inc/mc/inc/store-address.php',
				data: 'ajax=true&email=' + escape($('#email').val()),
				success: function(msg) {
					$('#mc-response').html(msg);
				}
			});

			return false;
		});
	});
}