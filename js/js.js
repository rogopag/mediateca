var jq = jQuery, Mediateca;

jq(function($)
{
	if( Mediateca.query.pagename == 'mediateca' )
	{
		mediatecaLinkButtons();
	}
	if( $("#hardware-and-software-form").is('form') )
	{
		hardwareSoftwareForm( $("#hardware-and-software-form") );
	}
	if( $("#text-search-form").is('form') )
	{
		hardwareSoftwareForm( $("#text-search-form") );
	}
	if( $('#categoria').is('select') )
	{
		manageCategorySelect();
	}
	if( $("#text-search-input").is('input') )
	{
		$("#text-search-input").click(function()
		{
			$(this).val('');
		});
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
function hardwareSoftwareForm( form )
{
	form.submit(function()
	{
		var el = $(this), send = el.serialize() + '&' +jQuery.param( Mediateca.query );
		
		ajaxCall( send, el );
		
		return false;
	});
};
function ajaxCall( data, element )
{
	var send = data, el = element;
	
	$.ajax({  
			type: 'post',  
			url: Mediateca.ajaxurl,  
			data: send,
			//dataType: 'json',
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{  
				console.error( textStatus, errorThrown );
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
				if( data )
				{
					
					if( $("#search-results").is('div') )
					{
						$("#search-results").fadeOut(200, function()
						{
							el.parent().parent().after( data );
							$("#search-results").fadeIn(200, function(){
								mediatecaPagination()
							});
						});
					}
					else
					{
						el.parent().parent().after( data );
						$("#search-results").fadeIn(200, function(){
							mediatecaPagination()
						});
					}
				}
			},
			complete: function( data, textStatus )
			{
				
			}  
		});
};
function manageCategorySelect()
{
	$('#categoria').change(function()
	{
		var el = $(this), value = el.val();
		
		if( value != '')
		{
			$.post(Mediateca.ajaxurl, { action: 'manage_category_select', parent: value, 'mediateca-nonce' : $('#mediateca-nonce').val() }, function(data){
				if( data )
				{
					
					if( $("#sottocategoria").is('select') )
					{
						$("#sottocategoria").fadeOut(300, function(){
							$(this).parent().remove();
							el.parent().after(data);
							$("#sottocategoria").parent().fadeIn(300, function(){
						
							});
						});
					}
					else
					{
						el.parent().after(data);
						$("#sottocategoria").parent().fadeIn(300, function(){
						
						});
					}
				}
			});
		}
	});
};
function managePagination()
{
	$('a.page-numbers').bind('click', function(event)
	{	
		event.preventDefault();
		
		var kind, send, action, a = $(event.target), href = a.attr('href'), query = href.split('?')[1], variable = query.split('=')[0], what = query.split('=')[1], tmp = href.split('page/'), page = tmp[1].split('/?')[0];
		
		if( variable == 'results' )
		{
			action = $('#hardware-e-software-search').val();
			kind = $('#hardware-e-software-search').val();
		}
		else if( variable == 'search' )
		{
			action = $("#hardware-e-software-text-search").val();
			kind = what;
		}
		
		send = $.param({action:action, paginated : what, pagenum : page, kind : kind }) + '&' +jQuery.param( Mediateca.query );
		
		console.log( send  );
		
		ajaxCall( send, $("#hardware-and-software-form") )
	});
};
function mediatecaPagination()
{
	if( $('a.page-numbers').is('a') )
	{
		managePagination();
	}
};