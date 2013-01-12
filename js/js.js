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
	if( $("#libri-form").is('form') )
	{
		libriSelectSezione();
		hardwareSoftwareForm( $("#libri-form") );
	}
	if( $('#mediateca-how-to').is('div') )
	{
		dito_manageHowTos();
	}
	fixCheckBoxes();
});
function fixCheckBoxes()
{
	if( $('.hierarchical_checkboxes').is('ul') )
	{
		$('input[type="checkbox"]').removeAttr('disabled');
	}
};
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
				//console.error( textStatus, errorThrown );
			},
			beforeSend: function(XMLHttpRequest) 
			{ 
				if( $("#search-results").is('div') )
					{
						$("#search-results").fadeOut('fast', function()
						{
							mediateca_loading( el );
						});
					}
					else
					{
						mediateca_loading( el );
					}
					
			}, 
			success: function( data, textStatus, jqXHR )
			{
				if( data )
				{
					//console.log( data );
					
					el.parent().parent().after( data );
					$("#search-results").fadeIn(200, function(){
							mediatecaPagination()
					});
						
				}
			},
			complete: function( data, textStatus )
			{
				mediateca_loading_remove();
			}  
		});
};
function manageCategorySelect()
{
	$('#categoria').change(function()
	{
		var el = $(this), value = el.val();
		
		mediateca_loading( el );
		
		if( value != '')
		{
			if( $("#sottocategoria").is('select') )
			{
				$("#sottocategoria").fadeOut(300, function(){
					
					$(this).parent().remove();
				});
			}
			$.post(Mediateca.ajaxurl, { action: 'manage_category_select', parent: value, 'mediateca-nonce' : $('#mediateca-nonce').val() }, function(data){
				
				if( data )
				{
					el.parent().after(data);
					$("#sottocategoria").parent().fadeIn(200, function(){
						mediateca_loading_remove( );
					});
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
		
		//console.log( "Clicked the shit:::::::::::::::::::::::::::::::::::::::", $(event.target).attr('href') );
		
		var kind, send, action, a = $(event.target), href = a.attr('href'), query = href.split('?')[1], variable = query.split('=')[0], what = query.split('=')[1], tmp = href.split('page/'), page = tmp[1].split('/?')[0];
		
		if( variable == 'results' )
		{
			action = kind = $('input[name="action"]').val();
		}
		else if( variable == 'search' )
		{
			action = $("#hardware-e-software-text-search").val();
			kind = what;
		}
		
		send = $.param({action:action, paginated : what, pagenum : page, kind : kind }) + '&' + jQuery.param( Mediateca.query );
		
		ajaxCall( send, $("form.mediateca-form") )
	});
};
function mediatecaPagination()
{
	if( $('a.page-numbers').is('a') )
	{
		managePagination();
	}
};
function mediateca_loading( a )
{
	$('input[name="submit-search"]').attr('disabled', 'disabled');
	
	var src = Mediateca.plugin_url + 'img/spin.gif', loading = $('<img class="loading-gif" src="'+src+'" alt="loading" id="loading-gif" />'), append = a;
	//console.log( 'I should see the loader here', a.attr('class') );
	loading.insertAfter( append.parent().parent() ).fadeIn('fast');
};
function mediateca_loading_remove( )
{
	$("#loading-gif").fadeOut('fast', function()
	{
		$(this).remove();
		$('input[name="submit-search"]').removeAttr('disabled');
	});
};
function libriSelectSezione()
{
	$('input[type="radio"]').change(function(){
		var value = $(this).val();
		$('#libri-form-removables').fadeOut(200, function(){
			mediateca_loading( $(this) );
			$(this).remove();
		})
		$.post(Mediateca.ajaxurl, { action: 'change-sezione-libri', 'sezione' : value, 'mediateca-nonce' : $('#mediateca-nonce').val() }, function(data)
		{
			if( data )
			{
				mediateca_loading_remove();
				$('#libri-removables-container').append(data);
				fixCheckBoxes();
			}
		});
	});
};
function dito_manageHowTos()
{
	var div = $('#mediateca-how-to'), button = $('<span id="hideShow"></div>'), close = false;
	
	button.text('Nascondi aiuto');
	
	$('h2.orange').append(button);
	
	button.bind('click', function(event){
		var me = $(this);
		
		me.fadeOut(100);
		
		if( !close )
		{
			div.slideUp(400, function(){
				close = true;
				me.text('Visualizza aiuto');
				me.fadeIn(150);
			});
		}
		else if( close )
		{
			div.slideDown(400, function(){
				close = false;
				me.text('Nascondi aiuto');
				me.fadeIn(150);
			});
		}
	});
}