jQuery( document ).ready( function($){


	$('#publish').click( function( e ) {	

		//verify that position has a section
		if ( !$('input:radio[name=datafile_extension]:checked').length ) {

			e.preventDefault();
			e.stopPropagation();

			alert( datafiles.missingExtensionMsg );

			$('#ajax-loading').hide();
			setTimeout( "jQuery('#publish').removeClass('button-primary-disabled')", 1);

			return false;

		}


	});
	
	$('#add_datafile_extension_toggle').live( 'click', function(e){
			$('#add_datafile_extension_div').toggle();
			e.preventDefault();
		});
		$('#add_datafile_extension_button').live( 'click', function(e) {
			var type = $(this).attr('id').replace('_button', '').replace('add_', '');
			$('#datafile_extension-ajax-loading').show();
			$.post('admin-ajax.php?action=add_' + type, $('#new_datafile_extension, #_datafile_extension_nonce, #post_ID').serialize(), function(data) { 
				$('#datafile_extension .inside').html(data); 
			});
			e.preventDefault();
		});
		
	$( '#datafile_extension input:radio' ).live( 'change', function(){
		$('.extension').text( $('label[for="' + $(this).attr( 'id' ) + '"]').text() ); 
	});
	
});