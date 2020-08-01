jQuery(document).ready(function($) {
	$(document).on('wsko_init_core', function(e) {
		wsko_set_kb_carousel();
	});
	
	function wsko_set_kb_carousel()
	{
		$('.wsko-carousel:not(.wsko-init)').addClass('wsko-init').each(function(index){
			var $this = $(this),
			id = uniqueID('wsko_carousel_'),
			$ca = $('<div id="'+id+'" class="carousel slide" data-ride="carousel"><ol class="carousel-indicators"></ol><div class="carousel-inner"></div><a class="carousel-control left" href="#'+id+'" role="button" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span><span class="sr-only">Previous</span></a><a class="carousel-control right" href="#'+id+'" role="button" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span><span class="sr-only">Next</span></a></div>'),
			$ind = $ca.find('.carousel-indicators'),
			$list = $ca.find('.carousel-inner'),
			curr = 0;
			$this.find('.wsko-carousel-item').each(function(index){
				var img = $(this).find('.img').text(),
				title = $(this).find('.title').text(),
				desc = $(this).find('.desc').text();
				$ind.append('<li data-target="#'+id+'" data-slide-to="'+curr+'" '+(curr==0?'class="active"':'')+'></li>');
				$list.append('<div class="item '+(curr==0?'active':'')+'" data-item=""><img src="'+img+'" alt="'+title+'"><div class="carousel-caption"><h3>'+title+'</h3><p>'+desc+'</p></div></div>');
				curr++;
			});
			var $newCarousel = $($ca[0].outerHTML);
			$this.replaceWith($newCarousel);
			$newCarousel.find('.carousel').carousel();
			$newCarousel.find('.carousel-indicators li').click(function(event){
				event.preventDefault();
				$("#"+id).carousel(parseInt($(this).data('slide-to')));
			});
			$newCarousel.find(".carousel-control").click(function(event){
				event.preventDefault();
				$("#"+id).carousel($(this).data('slide'));
			});
		});

		/*
		var native_width = 0;
		var native_height = 0;
		$(".wsko-zoom").each(function(index){
			var $wsko_small = $(this).find(".wsko-small");
			var $wsko_large = $(this).find(".wsko-large");

			$wsko_large.css("background","url('" + $wsko_small.attr("src") + "') no-repeat");
			$wsko_large.fadeIn(100);
			
			$(this).mousemove(function(e){
				if (!native_width && !native_height)
				{
					var image_object = new Image();
					image_object.src = $wsko_small.attr("src");

					native_width = image_object.width;
					native_height = image_object.height;
				}
				else
				{
					var magnify_offset = $(this).offset();

					var mx = e.pageX - magnify_offset.left;
					var my = e.pageY - magnify_offset.top;

					if (mx < $(this).width() && my < $(this).height() && mx > 0 && my > 0)
						$wsko_large.fadeIn(100);
					else
						$wsko_large.fadeOut(100);
					if ($wsko_large.is(":visible"))
					{
						var rx = Math.round(mx/$wsko_small.width()*native_width - $wsko_large.width()/2)*-1,
						ry = Math.round(my/$wsko_small.height()*native_height - $wsko_large.height()/2)*-1,
						bgp = rx + "px " + ry + "px",
						px = mx - $wsko_large.width()/2,
						py = my - $wsko_large.height()/2;
						$wsko_large.css({left: px, top: py, backgroundPosition: bgp});
					}
				}
			});
		}); */
		
		$('.wsko-zoom .wsko-large')
		// tile mouse actions
		.on('mouseover', function(){
		  $(this).children('.wsko-photo').css({'transform': 'scale(1.3)'});
		})
		.on('mouseout', function(){
		  $(this).children('.wsko-photo').css({'transform': 'scale(1)'});
		})
		.on('mousemove', function(e){
		  $(this).children('.wsko-photo').css({'transform-origin': ((e.pageX - $(this).offset().left) / $(this).width()) * 100 + '% ' + ((e.pageY - $(this).offset().top) / $(this).height()) * 100 +'%'});
		})
		// tiles set up
		.each(function(){
		  $(this)
			// add a photo container
			.append('<div class="wsko-photo"></div>')
			// set up a background image for each tile based on data-image attribute
			.children('.wsko-photo').css({'background-image': 'url('+ $(this).closest('.wsko-zoom').find('img').attr('src') +')'});
		})
	
	};


	var idCounter = 0;
	function uniqueID(prefix) {
		var id = '' + ++idCounter;
		return prefix ? prefix + id : id;
	};
	wsko_set_kb_carousel();
});