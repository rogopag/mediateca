var jq = jQuery, Mediateca;

jq(function($)
{
	if( Mediateca.query.pagename == 'mediateca' )
	{
		mediatecaLinkButtons();
	}
	if( $("#hardware-and-software-form").is('form') )
	{
		hardwareSoftwareForm();
	}
});

function mediatecaLinkButtons()
{
	$('.mediatecaButtons').click(function()
	{
		window.location = $(this).children('a').attr('href');
	});
	$('.mediatecaButtons').hover(function()
	{
		$(this).children('a').css('color', '#ff9a00');
	},
	function()
	{
		$(this).children('a').css('color', 'black');
	});
};

function hardwareSoftwareForm()
{
	$("#hardware-and-software-form").submit(function()
	{
		var send = $(this).serialize();
		
		$.ajax({  
			type: 'post',  
			url: Mediateca.ajaxurl,  
			data: send,
			//dataType: 'json',
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{  
				console.log( textStatus, errorThrown );
			},
			beforeSend: function(XMLHttpRequest) 
			{ 
				if (XMLHttpRequest && XMLHttpRequest.overrideMimeType) 
				{
				    //XMLHttpRequest.overrideMimeType("application/j-son;charset=UTF-8");
				}
			}, 
			success: function( data, textStatus, jqXHR )
			{
				console.log( data );
			},
			complete: function( data, textStatus )
			{
				//console.log( data, textStatus );
			}  
		});
		return false;
	});
};




