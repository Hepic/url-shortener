$(document).ready(function()
{
	$('#btn').click(function()
	{	
		$.ajax({
			url: 'src-php/insert_url.php',
			
			data: 
			{
				url: $('#url').val()
			},
			
			success: function(data)
			{
				if(data != 'No valid url')
					$('#res_short_url').html('<a href='+data+'>'+data+'</a> <p id="msg"> created successfully! </p>');
				
				else
					$('#res_short_url').text('No valid url');
			},
			
			error: function()
			{
				alert('(jquery-ajax) -> Something went wrong');
			},
			
			type: 'POST'
		});
		
		$('#res_short_url').css({display: 'block'});
	});
});