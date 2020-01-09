/* Opencart functions */ 
function getURLVar(key) {
	var value = [];

	var query = document.location.search.split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}	
// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		var datapr = 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1);
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				//$('#cart > button').button('loading');
			},
			complete: function() {
				//$('#cart > button').button('reset');
			},
			success: function(json) {
				$('.alert, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container',function(){
						setTimeout(fancyFastCart(), 600);
					});
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){svgFix();});
					if ($('.js-cart-call').length) {
//						$('.js-cart-btn').trigger('click');
						CartShow();
					} else {
						$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
						alertClose();
					}
					sendYM('lightshop_addtocart_catalog');
					sendGA(datapr,'lightshop_addtocart_catalog');				
					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
					}, 100);


					

				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});

		
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				//$('#cart > button').button('loading');
			},
			complete: function() {
				//$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
					svgFix();fancyFastCart();
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				//$('#cart > button').button('loading');
			},
			complete: function() {
				//$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
						if (json['total'] > 0) {
							$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
						} else {
							$('#cart-total').html('');
						}
				}, 100);
				
				var now_location = String(document.location.pathname);

				if ((now_location == '/cart/') || (now_location == '/cart')  || (now_location == '/checkout/') || (getURLVar('route') == 'checkout/cart') || (getURLVar('route') == 'checkout/checkout')) {
					location = '/index.php?route=checkout/cart';
				} else {
					$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
					svgFix();fancyFastCart();
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				//$('#cart > button').button('loading');
			},
			complete: function() {
				//$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
						if (json['total'] > 0) {
							$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
						} else {
							$('#cart-total').html('');
						}
				}, 100);

				var now_location = String(document.location.pathname);
				
				if ((now_location == '/cart/') || (now_location == '/checkout/') || (getURLVar('route') == 'checkout/cart') || (getURLVar('route') == 'checkout/checkout')) {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
					svgFix();fancyFastCart();
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alert').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			complete: function() {
				getCompareWish();
			},
			success: function(json) {
				$('.alert').remove();

				if (json['success']) {
					$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();

					$('#compare-total').html(json['total']);

				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
		
	},
	'remove': function() {

	}
}
	
var review = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/product/write&product_id=' + product_id,
			type: 'post',
			dataType: 'json',
			data: $("#form-review").serialize(),
			beforeSend: function() {
				$('#form-review > button').attr('disabled', 'disabled');
			},
			complete: function() {
				$('#form-review > button').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert--red, .alert--green').remove();
				if (json['error']) {
					$('h3').before('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + json['error'] + '</span></div>');
						setTimeout(function(){
							$('.alert--red').remove();
						}, 5000)	
				}
				if (json['success']) {
					$('h3').before('<div class="alert alert--green" style="margin-bottom: 20px;"><span class="alert__text"> ' + json['success'] + '</span></div>');
					$('input[name=\'name\']').val('');
					$('textarea[name=\'text\']').val('');
					$('input[name=\'rating\']:checked').prop('checked', false);
						setTimeout(function(){
							$('.alert--green').remove();
							$.fancybox.close();
						}, 5000)
				}
			}
		});
	},
}

var comment = {
	'add': function(blog_id) {
		$.ajax({
			url: 'index.php?route=extension/module/lightshop_blog/write&blog_id=' + blog_id,
			type: 'post',
			dataType: 'json',
			data: $("#form-comment").serialize(),
			beforeSend: function() {
				$('#form-comment > button').attr('disabled', 'disabled');
			},
			complete: function() {
				$('#form-comment > button').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert--red, .alert--green').remove();
				if (json['error']) {
					$('.message').append('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + json['error'] + '</span></div>');
						setTimeout(function(){
							$('.alert--red').remove();
						}, 5000)	
				}
				if (json['success']) {
					$('.message').append('<div class="alert alert--green" style="margin-bottom: 20px;"><span class="alert__text"> ' + json['success'] + '</span></div>');
					$('input[name=\'name\']').val('');
					$('textarea[name=\'text\']').val('');
					$('input[name=\'email\']:checked').prop('checked', false);
						setTimeout(function(){
							$('.alert--green').remove();
							$.fancybox.close();
						}, 5000)
				}
			}
		});
	},
}

/* end Opencart functions */ 

/* placeholder */
function placeholder(objInputs){
	if (objInputs.length) objInputs.placeholder();
}
/* placeholder end */

/*Custom select*/
function initCustomSelect($obj) {
/*	if($('.select').length) {
		
		var selectConfig = {	
			header: false,
			height: 300,
			minWidth: 'auto',	
			classes: 'select',
			selectedList: 1,
			multiple: false,
			position: {
				my: 'left top',
				at: 'left bottom',
				collision: 'none',
				using: function(position, feedback) {				
					addPositionClass(position, feedback, $(this));
				}
			},
			arrow: true,
			divider: false,
			corner: false,
			icon: false,
			jscrollpane: false,
			filter: false,
			filterOptions: {},
			open: function(event, ui) {
				//console.log(1);
			}	
		}
		if($obj.length) {
			customSelect($obj, selectConfig);
			$obj.each(function() {
				var inner = $(this).find('.ui-multiselect-inner'),
					innerArrow = $(this).next().find('.ui-multiselect-arrow');
					innerArrow.find('.icon-arrow-select').remove();
					innerArrow.append('<svg class="icon-arrow-select"><use xlink:href="#chevron-small-left"></use></svg>');
			});
		}

	}*/
}
/*Custom select end*/

/*spinner*/
function spinner() {
	if ($('.spinner').length) {
		$('.spinner').spinner({
			//min: 2.2,
			//step: 2.2
		});
	}

	$( "#cartcontent .spinner" ).on( "change", function( event, ui ) {setTimeout(function() { $('#cartcontent').submit();}, 100);	} );
	$( "#product .spinner" ).on( "change", function( event, ui ) { $('#product input[name="quantity"]').change();  } );
	$( "#cartcontent .spinner-wrap a" ).on( "click", function( event, ui ) { $('#cartcontent').submit();} );

}
/*spinner end*/

/*slider preview*/
function simpleSlider() {
	if($('.js-simple-slider').length) {
		var config = {
			lazyLoad: 'ondemand',
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
		}
		$('.js-simple-slider').slick(config);
	}
}
/*slider preview endr*/

 /*slider btn hover*/
 function sliderBtnHover() {
 	if($('.js-btn-hover').length) {
 		$('.js-btn-hover').hover(function() {
 			var cur = $(this);
 			cur.parents($('.js-btn-hover-parent')).find($('.js-btn-clear')).addClass('hide');
 			cur.parent().find($('.js-img-hover')).addClass('hide');
 		},
 		function() {
 			var cur = $(this);
 			cur.parent().find($('.js-img-hover')).removeClass('hide');
 			cur.parents($('.js-btn-hover-parent')).find($('.js-btn-clear')).removeClass('hide');
 		}

 		);

	}
 }
 /*slider btn hover end*/

 /*price slider
 function priceSlider() {
	if ($('.js-price-slider').length) {
		var  span_start = $('.js-span-start');
		var  span_end = $('.js-span-end');
		var input_val_from = $('input.js-price-slider-input-from').attr('val');
		var input_val_to = $('input.js-price-slider-input-to').attr('val');


		$('input.js-price-slider-input-from').parent().find(span_start).html(input_val_from+'<span class="rub">p</span>');
		$('input.js-price-slider-input-to').parent().find(span_end).html(input_val_to+'<span class="rub">p</span>');

		$( ".js-price-slider" ).slider({
			range: true,
			min: 0,
			max: 200000,
			values: [ 22000 , 107000 ],
			slide: function( event, ui ) {
				for (var i = 0; i < ui.values.length; ++i) {
					$("input.js-price-slider-input[data-index=" + i + "]").val(ui.values[i]);
				}
				$('.js-span-start').html(ui.values[0]+'<span class="rub">p</span>');
				$('.js-span-end').html(ui.values[1]+'<span class="rub">p</span>');
			}
		}).draggable();
		
		$("input.js-price-slider-input").change(function() {
			var $this = $(this);
			$(".js-price-slider").slider("values", $this.data("index"), $this.val() );

			$this.parent().find($( '.js-span-start, .js-span-end')).html($this.val()+'<span class="rub">p</span>');
		});

		span_start.on('click', function(){
			span_start.hide();
			$(this).parent().find($('input.js-price-slider-input')).trigger('focus');

		});
		 span_end.on('click', function(){
			 span_end.hide();
			$(this).parent().find($('input.js-price-slider-input')).trigger('focus');
		})
		$('input.js-price-slider-input').on('blur', function(){
			$(this).parent().find($( '.js-span-start, .js-span-end')).show();
			
		}); 

	}
	
		
}
 price slider end*/

 /*drop */
 function drop() {
 	if($('.js-drop-select-btn').length) {
 		$('.js-drop-select-btn').on('click', function(e){
 			var cur = $(this);
 			var drop = cur.parents($('.js-drop-select-parent')).find($('.js-drop-select-drop'));
 			drop.toggleClass('js-active');
 			var yourClick = true;
 				$(document).bind('click.drop', function (e) {
					if (!yourClick  && !$(e.target).closest(drop).length) {
						drop.removeClass('js-active');
						$(document).unbind('click.drop');
					}
					yourClick  = false;
				});
 			e.preventDefault();
 		});
 	}
 }
 /*drop  end */

 /*drop with select*/
function dropSelect() {
	if($('.js-list-select').length) {
		$('.js-list-select').on('click', function(e){
			var cur = $(this);
			var cur_val = cur.html();
			var btn = $('.js-drop-select-btn');
			var list = $('.js-drop-select-drop');
			list.toggleClass('open');
			$('.js-list-select').not(cur).removeClass('active');
			cur.addClass('active').prependTo(list);
			var yourClick = true;
				$(document).bind('click.list', function (e) {
					if (!yourClick  && !$(e.target).closest(list).length) {
						list.removeClass('open');
						$(document).unbind('click.list');

					}
					yourClick  = false;
				});
			e.preventDefault();
		});
		
	}

			$('.js-select-lists').on('click', function(e) {
				if($('.js-lists').hasClass('catalogue__products-list--five') || $('.js-lists').hasClass('catalogue__products-list--four')) {
						$('.js-lists').removeClass('catalogue__products-list--five catalogue__products-list--four');
					}
				$('.js-lists').addClass('active');
				$('.js-full-list, .js-table').removeClass('active');
				$('.js-simple-slider').slick('unslick');
				$('.js-simple-slider').slick({
					nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
					prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
				});
				sliderBtnHover();
				e.preventDefault();
				svgFix();
				lazyLoad();

			});

			$('.js-select-four-items').on('click', function(e) {
				if($('.js-lists').hasClass('catalogue__products-list--five')) {
					$('.js-lists').removeClass('catalogue__products-list--five');
				}
				$('.js-lists').addClass('active catalogue__products-list--four');
				$('.js-full-list, .js-table').removeClass('active');
				$('.js-simple-slider').slick('unslick');
				$('.js-simple-slider').slick({
					nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
					prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
				});
				sliderBtnHover();
				e.preventDefault();
				svgFix();
				lazyLoad();
			});
			$('.js-select-five-items').on('click', function(e) {
				if($('.js-lists').hasClass('catalogue__products-list--four')) {
					$('.js-lists').removeClass('catalogue__products-list--four');
				}
				$('.js-lists').addClass('active catalogue__products-list--five');
				$('.js-full-list, .js-table').removeClass('active');
				$('.js-simple-slider').slick('unslick');
				$('.js-simple-slider').slick({
					nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
					prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
				});
				sliderBtnHover();
				e.preventDefault();
				svgFix();
				lazyLoad();
			});
}

 /*drop with select end*/

 /*hide filter blocks*/
function hideFilterBlocks() {
	if($('.js-filter-arrow').length ) {
		$('.js-filter-arrow').on('click', function(e) {
			var cur = $(this);
			cur.toggleClass('active').parents('.js-filter-parent:eq(0)').find('.js-filter-content:eq(0)').slideToggle();
			e.preventDefault();
		});
	}

}

function filterResp() {
	if ($(".js-btn-filter").length ) {
		var filter = $('.js-filter-resp'),
			sbw = scrollbarWidth();
		$(".js-btn-filter").on('click', function(e) {
			e.preventDefault();
				$('body').css({
				   'overflow' : 'hidden',
				   'width' : 'calc(100% - ' + sbw + 'px)',
				});	
				filter.addClass('js-show');
				$('.body-overlay-filter').toggleClass('active');

				filter.removeClass('js-hide');
				
				$(document).on('click', function(e) {
					$target = $(e.target);
					isBtn = !!$target.closest($(".js-btn-filter")).length;
					isPopup = !!$target.closest(filter).length;
					isSelect = !!$target.closest($('.ui-multiselect-menu')).length;
					if (!isBtn && !isPopup && !isSelect) {
						filter.removeClass('js-active');
						filter.addClass('js-hide');
						$('.body-overlay-filter').removeClass('active');
						$('body').css({'overflow':'visible','width':'inherit',});	

					}
				});
		});
		$('.js-filter-hide').on('click', function(e){
			e.preventDefault();
			$('body').css({'overflow':'visible','width':'inherit',});
			filter.removeClass('js-show');
			filter.addClass('js-hide');
			$('.body-overlay-filter').removeClass('active');

			
		});
		
	}
}
/*hide filter blocks end*/

/*slider preview*/
function simpleSliderCust($selector) { 
	if($($selector + ' .js-simple-slider').length) {
		var config = {
			lazyLoad: 'ondemand',
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
		}
		$($selector + ' .js-simple-slider').slick(config);
		svgFix();
	}
}
/*slider preview endr*/

	$(document).on('click','.product-view__list-item', function(e){ 
		e.preventDefault();
		var view = $(this).attr("data-columns");
		var path = '';
		var type = 'product/category';
		if($('#path').val()){
			var path = "&path="+$('#path').val();
		}
		if($('#type').val()){
			var type = $('#type').val();
		}
		if(type == 'product/manufacturer'){
			cat_id = "/info&manufacturer_id="+$('#manufacturer_id').val()+"&view="+view;
		}
		if(type == 'product/special'){
			cat_id = "&view="+view;
		}
		if(type == 'product/search'){
			cat_id = "&view="+view;
		}
		if(type == 'product/category'){
			var cat_id = path+"&view="+view;
		}
		var href = window.location.search.substr(1);
		if(href){
			href ='&'+href;
		}
		if($('#url').val()){
			var href = $('#url').val();
		}
		
		if (localStorage.getItem('display') == view) {
			return;
		}
		localStorage.setItem('display', view);		
		$('#mainContainer').load('index.php?route='+type+cat_id+href,{type: 'post'},function(){
			spinner();simpleSliderCust('#mainContainer');sliderBtnHover();svgFix();fancyFastCart();lazyLoad();
		}); 
	});	

/*products view*/

function productsView() {
				$(document).on('click', function(k) {
					$target = $(k.target);
					isPopupprod = !!$target.closest('#popupprod').length;
					isFastCart = !!$target.closest('#popup-buy-click, .popup-simple__close').length;
					isActive = $('#popupprod').hasClass('active');
					if (!isPopupprod && !isFastCart && isActive) {
						$('#popupprod').removeClass('active');
						$('#popupprod').addClass('hide');
						$('.body-overlay').removeClass('active');
						$('body').css('overflow', 'visible');

					}
				});		
		var btn = $('.js-product-view-btn');
		var btnSel = '.js-product-view-btn';
		var btnTab = '.js-tab';
		var popup = $('.js-product-view');
		var popupSel = '.js-product-view';
		$(document).on('click','.js-product-view-btn', function(e){
			e.preventDefault();
			var product_id = $(this).attr("data-for");
			var path = '',
				href = '',
				manufacturer_id = '',
				popuptype = '';
			if($('#path_id').val()){
				path = '&popuppath='+$('#path_id').val();
			}
			if($('#popuptype').val()){
				popuptype = '&popuptype='+$('#popuptype').val();
			}
			if($('#manufacturer_id').val()){
				manufacturer_id = '&manufacturer_id='+$('#manufacturer_id').val();
			}
			if($('#url').val()){
				href = $('#url').val();
			}
			if(!$('#popupprod').html().length > 10){
				$('#popupprod .js-tab').removeClass('active');
				$('#popupprod .js-tab-content').fadeOut(200);
				$('#popupprodid_'+product_id).fadeIn(200, function(){
								$('#popupprod .js-simple-slider').slick('unslick');
								$('#popupprod .js-simple-slider').slick({
									nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
									prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
								});					
					
				}).addClass('active');
				$('#popupprod .js-tab-tabpopup').removeClass('active');	
				$('#popupprodtabid_'+product_id).addClass('active');
				initPopupTab();	
				svgFix();
			}else{
				$('#popupprod').load('index.php?route=product/category/popupdetail'+path+popuptype+manufacturer_id+'&prod_id='+product_id+'&'+href,function(){
					spinner();
					initPopupTab();
					simpleSliderCust('#popupprod');ocDateTimepicker();
					sliderBtnHover();fancyFastCart();
					$('#popupprod .js-custom-scroll').customScroll({offsetBottom: 80,});
					//initCustomSelect($('#popupprod .js-tab-content select'));
					//$('#popupprod .js-tab-content select').multiselect('refresh');
					svgFix();
				});
			}
			setTimeout(function() {
				$('#popupprod').addClass('active');
				$('#popupprod').removeClass('hide');
			}, 30);			

			
			$('body').css('overflow', 'hidden');

			popup.removeClass('hide');		

				$('.body-overlay').toggleClass('active');
		});
	
			$(document).on('click','.js-product-view-hide', function(e){
				e.preventDefault();
				$('#popupprod').removeClass('active');
				$('#popupprod').addClass('hide');
				$('.body-overlay').removeClass('active');
				$('body').css('overflow', 'visible');
			
			});
}

/*products view end*/

function initPopupTab() {
	$tabs = $('#popupprod .js-tab-tabpopup');
	var index = 0;
	$tabs.each(function(indx, element){
		if($(element).hasClass("active")){
			index = indx;
		}	
	});

	var size = $tabs.length,
	windHeight = window.innerHeight,
	shiftmin,
	shiftmax,
	shift = 3;
			
	if(windHeight > 700){
		shift = shift+1;
	}
	if(windHeight < 400){
		shift = shift-1;
	}
	shiftmin = index - shift;
	shiftmax = index + shift;
	if(shift>index){shiftmax=2*shift+1;}
	if(shiftmax>size){shiftmin=size-2*shift;}
	$tabs.hide();
	$tabs.each(function(indx, element){
		if(indx >= shiftmin && indx <= shiftmax){
			$tabs.eq(indx).show();
		}
	});

}

/*Custom Scroll*/
function initCustomScroll() {
	var $block = $('.js-custom-scroll');
	if($block.length) {
		$block.customScroll({
			vertical: true,
			horizontal: false
		});
	}
}
/*Custom Scroll end*/

/*categories animate*/
function categoriesAnimate() {
	if($('.js-animate').length) {
		
		$('.js-animate').hover(function() {
			window.initHandler = setTimeout( handler, 500 );
			var cur = $(this);
			var block = $('.categories__link:after');
			var caption = $('.js-animate-caption');
			function handler() {
				cur.find($('.categories-overlay')).show(300);
				
				cur.find($('.js-animate-caption')).animate({
				fontSize: "24px",
				bottom: "42%",
				width:'100%',
				color: '#fff',
				backgroundColor: 'transparent'
			}, {
				duration: 300,
				file: function() {
						var cur = $(this);
						var block = $('.categories__link:after');
						var caption = $('.js-animate-caption');
						cur.find($('.js-animate-caption')).animate({
							fontSize: "12px",
							bottom: "-1px",
							width:'75%',
							color: "#000",
							backgroundColor: 'rgba(255, 255, 255, 0.9)',
							right: '0'
						}, 300);
					}
				}
			);
			}
				
			}, function() {
				clearTimeout( window.initHandler );
				var cur = $(this);
				var block = $('.categories__link:after');
				var caption = $('.js-animate-caption');
					setTimeout(function() {cur.find($('.categories-overlay')).hide(300);}, 300);
					setTimeout(function(){cur.find($('.js-animate-caption')).animate({
						fontSize: "12px",
						bottom: "-1px",
						width:'75%',
						color: "#000",
						backgroundColor: 'rgba(255, 255, 255, 0.9)',
						right: '0'
					}, 300);
				}, 300);
		});
	}
}
/*categories animate end*/

/* recomend slider */
function recomSlider() {
	var $slider = $('.js-recom-slider');
	if ($slider.length) {
		$slider.slick({
			dots: false,
			arrows: true,
			nextArrow: '<span class="slick-next"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			prevArrow: '<span class="slick-prev"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
			slidesToShow: 4,
			slidesToScroll: 1,
			responsive: [
				{
					breakpoint: 1024,
	      			settings: {
						slidesToShow: 3
	      			}
				},
				{
					breakpoint: 767,
	      			settings: {
						slidesToShow: 1,
						arrows: false,
						variableWidth: true
	      			}
				},
				{
					breakpoint: 450,
	      			settings: {
						slidesToShow: 1,
						arrows: false,
						variableWidth: true
	      			}
				}
			]
		});
	}
}

/* submenu promo slider */

function submenuPromoSlider() {
	var $slider = $('.js-submenu-promo-slider');
	if ($slider.length) {
		$slider.slick({
			dots: false,
			arrows: true,
			lazyLoad: 'ondemand',
			nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
			slidesToShow: 1,
			slidesToScroll: 1,
		});
	}
}

/* recomend slider2 */

function recomSlider2() {
	var $slider = $('.js-recom-slider2');
	if ($slider.length) {
		$slider.slick({
			dots: false,
			arrows: true,
			nextArrow: '<span class="slick-next"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			prevArrow: '<span class="slick-prev"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
			slidesToShow: 3,
			slidesToScroll: 1,
			variableWidth: true,
			responsive: [
				{
					breakpoint: 1024,
	      			settings: {
						slidesToShow: 3,
						variableWidth: true
	      			}
				},
				{
					breakpoint: 767,
	      			settings: {
						slidesToShow: 1,
						variableWidth: true
	      			}
				},
				{
					breakpoint: 450,
	      			settings: {
						slidesToShow: 1,
						variableWidth: true
	      			}
				}
			]
		});
	}
}


/* popup */

function popupDrop() {
		
	var $link = $('.js-popup-call'),
		$linkHover = $('.js-popup-call-hover'),
		$popup = $('.popup'),
		$overlay = $('.popup-overlay'),
		$btnClose = $('.js-popup-close'),
		self,
		sbw = scrollbarWidth(),
		$currentPopup;
	/* позиционирование попапа */
	function setPosition($obj, $rel) {
		var check = $obj[0];
		var	relPos = $rel.offset(),
			relW = $rel.width(),
			relH = $rel.outerHeight(),
			windWidth = window.innerWidth,
			popupW,
			popupPos = {},
			popupPosR,
			overLeft,
			overRight,
			$marker,
			markerPos = {};
		if (windWidth > 767) {
			popupW = $obj.width();
			popupPosR = relPos.left - 50 + popupW;
			overRight = windWidth - popupPosR;
			overLeft = relPos.left - 50;

			if (overLeft < 0) {
				popupPos.left = 30;
			} else if (overLeft > 0 && overRight > 0) {
				popupPos.left = relPos.left - 50;
			} else if (overRight < 0) {
				if (windWidth > 800) {
					popupPos.left = windWidth - popupW - 30;
				} else {
					popupPos.left = windWidth - popupW;
				}
				
			}

			$obj.offset(popupPos)
			$marker = $('.js-popup-marker');
			markerPos.left = relPos.left - 11 + relW / 2;
			$marker.offset(markerPos);

	}

	}
	/* закрытие попапа */
	function closePopup($obj) {
		
		$obj.removeClass('visible');
		clearTimeout($obj.prop('time_popup'));
		var time_popup = setTimeout(function(){
			$obj.removeClass('active').find('.js-popup-marker').remove();
		}, 300);
		$obj.prop('time_popup', time_popup);
		
	}
	/* открытие попапа */
	function openPopup($obj) {

		 var ob = $obj[0]
		 ob = String($(ob).attr('id'))
		  if (!$($obj).hasClass('active')) { 
				$obj.addClass('active').append('<div class="popup__triangle js-popup-marker"></div>');
			}

		clearTimeout($obj.prop('time_popup'));

		var time_popup = setTimeout(function () {
			setPosition($obj, self);
			$obj.addClass('visible');
		}, 0);
		$obj.prop('time_popup', time_popup);
	
	}
	
	function overlayActive() {
		if (window.innerWidth < 767) {
			$overlay.addClass('active').addClass('visible');
				$('body').css({
				   'overflow' : 'hidden',
				   'width' : 'calc(100% - ' + sbw + 'px)',
				});	
		}
	}
	
	function overlayDeactive() {
		if (window.innerWidth < 767) {
			$overlay.removeClass('visible');
			setTimeout(function(){
				$overlay.removeClass('active');
			}, 300);
				$('body').css({
				   'overflow' : 'visible',
				   'width' : 'inherit',
				});		
		}
	}
	
	if ($link.length) {
		
		$link.on('click', function(e){
			e.preventDefault();
			self = $(this);
			$currentPopup = $(self.attr('href'));
			if (!self.hasClass('active')) {
				
				$popup.each(function(){
					var flag = $(this).context.id == $currentPopup[0].id;
					
					if (!flag) {
						closePopup($(this));
					}
				});
				$link.removeClass('active');
				self.addClass('active');
				openPopup($currentPopup);
				if ($currentPopup[0].id != 'account') {
				overlayActive();
				} else {
					if (window.innerWidth < 767) {
						$('body').css({
						   'overflow' : 'hidden',
						   'width' : 'calc(100% - ' + sbw + 'px)',
						});	
					}
				}
				mobSubMenu();
				$('.js-submenu-promo-slider').slick('unslick');
				$('.js-submenu-promo-slider').slick({
					nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
					prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
				});
				$('.js-submenu-promo-slider').slick('setPosition');
				svgFix();
			} else {
				self.removeClass('active');
				closePopup($currentPopup);
				overlayDeactive();
			}
		});
		$(document).on('click', function(e) {
			$target = $(e.target);
			isBtn = !!$target.closest($link).length;
			isPopup = !!$target.closest($popup).length;
			if (!isBtn && $link.hasClass('active') && !isPopup) {
				$link.removeClass('active');
				closePopup($popup);
				overlayDeactive();
			}
		});

		$btnClose.on('click', function(){
			self.removeClass('active');
			closePopup($currentPopup);
			overlayDeactive();
		});
		
		$('#popup-navigation').on('click','.js-popup-close', function(){
			self.removeClass('active');
			closePopup($currentPopup);
			overlayDeactive();
		});
		
		$(window).on('resize', function(){
			if ($popup.hasClass('active')) {
				setPosition($currentPopup, self);
			}
		});
	}
	if  ($linkHover.length && window.innerWidth > 767) {
		hoverLinkPopup($currentPopup);
	}
	
	function hoverLinkPopup ($curPop) {
		$linkHover.on('mouseover', function(){
			self = $(this);
			$curPop = $(self.attr('data-href'));
			clearTimeout($curPop.prop('time_popup'));
		
			if (!self.hasClass('active')) {

				if ($('.more').is(":hover")) {
					return
				}

				else{

				$popup.each(function(){
					var flag = $(this).context.id == $curPop[0].id;
					
					if (!flag) {
						closePopup($(this));
					}
				});

				$linkHover.removeClass('active');
				self.addClass('active');
				openPopup($curPop);
				overlayActive();
				$('.js-submenu-promo-slider').slick('unslick');
				$('.js-submenu-promo-slider').slick({
					nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
					prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
				});
				$('.js-submenu-promo-slider').slick('setPosition');
				svgFix();
			}
			} else {
				$linkHover.removeClass('active');
				closePopup($curPop);
				overlayDeactive();
			}

			var $obj = 0;
			$('.popup').mouseover(function(){
  				$obj = '#'+this.id;
			});

			$linkHover.on('mouseleave', function(e){
			
				var hdata = $(this).attr('data-href');
				
				clearTimeout($curPop.prop('time_popup'));
				var time_popup = setTimeout(function(){
					if($obj != hdata){
						$linkHover.removeClass('active');
						closePopup($curPop);
						overlayDeactive();
					}
				
				}, 5);
				$curPop.prop('time_popup', time_popup);

			});
			$curPop.on('mouseleave', function(e){
				
				self.removeClass('active');
				closePopup($curPop);
				overlayDeactive();
			});
		});
	}
}

/* open search */

function openSearch() {
	var $btn = $('.js-search-btn'),
		$form = $('.js-search'),
		$btnClose = $('.js-search-close');
	if ($btn.length) {
		$btn.on('click', function(e){
			e.preventDefault();
			var self = $(this);
			if (!self.hasClass('active') && !$form.hasClass('active')) {
				self.addClass('active');
				$form.addClass('active');
				setTimeout(function(){
					$form.addClass('visible');
				}, 100);
			} 
			//$('#search_text').focus();
		});
		$btnClose.on('click', function(){
			if ($form.hasClass('visible')) {
				$form.removeClass('visible').find('input').val('');
				setTimeout(function(){
					$form.removeClass('active');
					$btn.removeClass('active');
				},100);
			}
		});
		$(document).on('click', function(e){
			$target = $(e.target);
			isActive = $form.hasClass('active') && $btn.hasClass('active');
			isBtn = !!$target.closest($btn).length;
			isForm = !!$target.closest($form).length;
			if (!isBtn && !isForm && isActive) {
				$form.removeClass('visible').find('input').val('');
				setTimeout(function(){
					$form.removeClass('active');
					$btn.removeClass('active');
				},100);
			}
		});
	}
}

/*-- tabs --*/

		$(document).on('click','.js-tab', function(e){
			$btn = $('.js-tab');
			e.preventDefault();
			var self = $(this),
				index = self.index(),
				$box = self.closest('.js-tabs-box'),
				$content = $box.find('.js-tab-content');

			if (!self.hasClass('active')) {
				self.addClass('active').siblings($btn).removeClass('active');
				initPopupTab();
				$content.eq(index).siblings($content).fadeOut(200);
				
				setTimeout(function(){
					$content.eq(index).fadeIn(200, function(){
						if($content.hasClass('active')) {
							if($('.js-simple-slider').length){
								$('.js-simple-slider').slick('unslick');
								$('.js-simple-slider').slick({
									nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
									prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
								});
								svgFix();
							}
							//if($('.select').length) {
							//	self.parents('.js-tabs-box').find('.js-tab-content select').multiselect('refresh');
							//}
							if($('.js-custom-scroll').length) {
								self.parents('.js-tabs-box').find('.js-custom-scroll').customScroll();
							}
						} 
					}).addClass('active');
							
				},200);
			}
			
		});

		$(document).on('click','#popupprod .js-tab-tabpopuplink', function(e){
			e.preventDefault();
			var url = $(this).find('a').attr('href').split('?');
			if(url[1]){
				href = '&'+url[1];
			}else{
				href = '';
			}
			var path = '';
			if($('#path_id').val()){
				path = '&popuppath='+$('#path_id').val();
			}
			$('#popupprod').load('index.php?route=product/category/popupdetail'+href+path,function(){
					spinner();
					initPopupTab();
					fancyFastCart();
					simpleSliderCust('#popupprod');
					sliderBtnHover();//addProduct2cart();
					$('#popupprod .js-custom-scroll').customScroll({offsetBottom: 80,});
					//initCustomSelect($('#popupprod .js-tab-content select'));
					//$('#popupprod .js-tab-content select').multiselect('refresh');
					$('#popupprod').addClass('active');
					$('#popupprod').removeClass('hide');
					svgFix();
			});

		});
		
		$(document).on('click','#popupprod .js-tab-tabpopup', function(e){
			$btn = $('.js-tab-tabpopup');
			$pagination = $('.catalogue__pagination_popup .pagination');
			e.preventDefault();
			var self = $(this),
				index = self.index(),
				$box = self.closest('.js-tabs-box'),
				windHeight = window.innerHeight,
				btnnext = false,
				btnprev = false,
				$content = $box.find('.js-tab-content'),
				popup_link_prod = $content.eq(index).find('input[name=\'product_href\']').val();

				
			$('#popup_link_prod').attr('href',popup_link_prod);

			if($pagination.find('li').length){
				var urlnext = $pagination.find(".active").next().find('a').attr('href');
				var urlprev = $pagination.find(".active").prev().find('a').attr('href');
				$('#popupprod .js-tab-tabpopuplink').remove();
				if(index == 0 && urlprev){
					$('#popupprod .js-tab-tabpopuplink').remove();
					$('#popupprod .product-tabs__list').prepend('<li class="product-tabs__list-item js-tab-tabpopuplink" style="display: list-item;"><a href="'+urlprev+'" class="product-tabs__link product-tabs__link--prevlast"><svg class="icon-chevron-left"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#chevron-small-left"></use></svg></a></li>');
				}
				if(index >= $btn.length-4 && urlnext){
					$('#popupprod .js-tab-tabpopuplink').remove();
					$('#popupprod .product-tabs__list').append('<li class="product-tabs__list-item js-tab-tabpopuplink" style="display: list-item;"><a href="'+urlnext+'" class="product-tabs__link product-tabs__link--prevlast"><svg class="icon-chevron-right"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#chevron-small-left"></use></svg></a></li>');
				}
			}
			if (!self.hasClass('active')) {
				self.addClass('active').siblings($btn).removeClass('active');
				$content.eq(index).siblings($content).fadeOut(200);
				initPopupTab();

					$content.eq(index).fadeIn(200, function(){
						if($content.hasClass('active')) {
							if($('.js-simple-slider').length){
								$('.js-simple-slider').slick('unslick');
								$('.js-simple-slider').slick({
									nextArrow: '<span class="slick-next js-btn-hover"><svg class="icon-chevron-blue-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
									prevArrow: '<span class="slick-prev js-btn-hover"><svg class="icon-chevron-blue-left"><use xlink:href="#chevron-small-left"></use></svg></span>'
								});
								svgFix();
							}
							//if($('.select').length) {
							//	self.parents('.js-tabs-box').find('.js-tab-content select').multiselect('refresh');
							//}
							if($('.js-custom-scroll').length) {
								self.parents('.js-tabs-box').find('.js-custom-scroll').customScroll();
							}
						} 
						
					}).addClass('active');
			}
			
		});

/* call shopping cart */
function CartShow(event){
	var $popup = $('#cart'),
		$overlay = $('.popup-overlay'),
		self = $(this),
		sbw = scrollbarWidth();
		if(typeof(event) != 'undefined'){
			event.preventDefault();
		}
			
			if (!self.hasClass('active')) {
				$overlay.addClass('active');
				$('body').css({
				   'overflow' : 'hidden',
				   'width' : 'calc(100% - ' + sbw + 'px)',
				});
				setTimeout(function(){
					$overlay.addClass('visible');
					$popup.addClass('active');
				}, 0);
			}
}

function callCart(){
	var $popup = $('#cart'),
		$btn = $('.js-cart-btn'),
		$btnClose = $popup.find('.js-cart-close'),
		$overlay = $('.popup-overlay'),
		sbw = scrollbarWidth();

	if ($btn.length && $popup.length) {
		$btn.on('click',CartShow);

		$btnClose.on('click', function(){
			if ($popup.hasClass('active')) {
				$popup.removeClass('active');
				$overlay.removeClass('visible');
				$('body').css({
				   'overflow' : 'visible',
				   'width' : 'inherit',
				});
				setTimeout(function(){
					$overlay.removeClass('active');
				}, 200);
			}
		});
		$(document).on('click', function(e){
			$target = $(e.target);
			isPopup = !!$target.closest($popup).length;
			isPopupFastOrder = !!$target.closest('#popup-buy-click-cc').length;
			if (!isPopup && !isPopupFastOrder && $popup.hasClass('active')) {
				$popup.removeClass('active');
				$overlay.removeClass('visible');
				$('body').css({
				   'overflow' : 'visible',
				   'width' : 'inherit',
				});
				setTimeout(function(){
					$overlay.removeClass('active');
				}, 200);
			}
		});
	}
}

/* mob submnenu */

function mobSubMenu() {
	var $link = $('.js-submenu-link'),
		isMob = window.innerWidth < 767,
		$menu = $('.nav-submenu__list'),
		$close = $menu.find('li:first-child'),
		$self;
	if ($link.length) {
		if (isMob) {
			$link.on('click', function(e){
				e.preventDefault();
				$self = $(this);
				if (!$self.hasClass('active')) {
					$($self).siblings($menu).addClass('active');
				}
				if(!$(this).closest('.nav-submenu').hasClass('nav-submenu-about'))
				$('.popup__submenu-scroll').animate({
					scrollTop: 0
				}, 300);


			});
			$close.on('click', function() {
				$(this).closest($link).removeClass('active');
				$(this).closest($menu).removeClass('active');
			});
		}
	}
	
}

function bcSlider() {
	var $slider = $('.js-slider-main');
	if ($slider.length) {
		if ( $('.js-slider-main-title, .main-slider .slick-prev, .main-slider .slick-next, .header--min').length ) {
			BackgroundCheck.refresh();
			BackgroundCheck.init({
				targets: '.js-slider-main-title, .main-slider .slick-prev, .main-slider .slick-next, .header--min',
				images: $('.main-slider__img'),
				threshold: 55,
			});
		}
	}
}

function mainSlider() {
	var $slider = $('.js-slider-main'),
		$sliderImg = $('.main-slider__img');

	if ($sliderImg.length) {
		$slider.find($sliderImg).each(function(){
			var atti, $tfi = $(this).find('img');
			if(!$tfi.hasClass('src-img')){
				atti = $tfi.data('bg');
			}else{
				atti = $tfi.attr('src');
			}
			//console.log(atti);
			// var src = 'url(' + $(this).find('img').attr('src') + ')';
			var src = 'url(' + atti + ')';
			$(this).css('background-image', src);
			$(this).find('img').hide();
		});
		$slider.on('init', function(event, slick){
			bcSlider();
        });	
		$slider.each(function(){
			$(this).slick({
				dots: true,
				arrows: true,
				prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
				nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
				appendArrows: $(this).parents('.main-slider').find('.arrows-container'),
				slidesToShow: 1,
				slidesToScroll: 1,
				speed: $(this).parents('.main-slider').find('input[name=\'main_slider_speed\']').attr('value'),
				autoplay: $(this).parents('.main-slider').find('input[name=\'main_slider_autoplay_speed\']').attr('value') == 0 ? false : true,
				autoplaySpeed: $(this).parents('.main-slider').find('input[name=\'main_slider_autoplay_speed\']').attr('value'),
				responsive: [
					{
						breakpoint: 767,
						settings: {
							arrows: true,
							dots: false,
						}
					}
				]
			});
		});
		$slider.on('afterChange', function(event, slick, currentSlide){
			bcSlider();
        });
		$slider.on('setPosition', function(event, slick){
			BackgroundCheck.refresh();
        });
	}
}

function dragCatalog() {
	var $drag = $('.js-drag');
	if ($drag.length) {
		$drag.draggable({ 
			containment: '.js-drag-container', 
			scroll: false,
			axis: 'x'
		});
	}
}

function fixedHeader() {
	var $fix = $('.js-fixed-header').not('.header--min'),
		$hv2 = $('.header--version2'),
		w = window.innerWidth,
		$ht = $('.header__top'),
		$hc = $('.header__call');
	if ($fix.length ) {
		if (!!$hv2.length && w < 768) {
			$(window).scroll(function(){
				if ($(this).scrollTop() > $hc.outerHeight()){ 
					$ht.addClass('header__top--fix');
					$hc.addClass('header__call--hide');
					$('.wrapper').css({
					   'padding-top' : ($hc.outerHeight()+ 71) + 'px',
					});
				} else {
					$ht.removeClass('header__top--fix');
					$hc.removeClass('header__call--hide');
					$('.wrapper').css({
					   'padding-top' : '0px',
					});
				}
			});	
		} else {
			$(window).scroll(function(){
				if ($(this).scrollTop() > $ht.outerHeight()){ 
					$fix.addClass('header__bottom--fix');
					$('.wrapper').css({
					   'padding-top' : $fix.outerHeight() + 'px',
					});
				} else {
					$fix.removeClass('header__bottom--fix');
					$('.wrapper').css({
					   'padding-top' : '0px',
					});
				}
			});	
		}
	}
}

function promoItem() {
	var $promo = $('.js-promo-item');
	if ($promo.length) {
		BackgroundCheck.init({
			targets: '.promo__caption',
			images: '.js-promo-item a img',
			windowEvents: false,
		});	
	}
}

function sliderBrands() {
	var $slider = $('.js-slider-brands'),
		options = {
			dots: false,
			arrows: true,
			slidesToShow: 6,
			slidesToScroll: 1,
			prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
			nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
			responsive: [
				{
					breakpoint: 1024,
	      			settings: {
						slidesToShow: 5
	      			}
				},
				{
					breakpoint: 767,
	      			settings: {
						slidesToShow: 3
	      			}
				},
				{
					breakpoint: 450,
	      			settings: {
						slidesToShow: 1
	      			}
				}
			]
		}
	
	if ($slider.length) {
		$slider.slick(options);
	}
}

function scrollbarWidth() {
	var a = document.createElement("div");
	a.style.visibility = "hidden", a.style.width = "100px", a.style.msOverflowStyle = "scrollbar", document.body.appendChild(a);
	var b = a.offsetWidth;
	a.style.overflow = "scroll";
	var c = document.createElement("div");
	c.style.width = "100%", a.appendChild(c);
	var d = c.offsetWidth;
	return a.parentNode.removeChild(a), b - d
}
		
function order() {
	var step = $('.checkout'),
		title = step.find('.checkout__title'),
		inner = step.find('.checkout__inner'),
		backBtn = step.find('.js-checkout-back'),
		nextBtn = step.find('.js-checkout-next'),
		fix = $('.js-fixed-header');
	if (step.length) {
		nextBtn.on('click', function(e){
			var thisStep = $(this).closest(step);
			e.preventDefault();
			thisStep.addClass('pass');
			thisStep.find(inner).slideUp(200);
			thisStep.next(step).find(inner).slideDown({
				duration: 200,
				complete: function(){
					$('body, html').animate({scrollTop: thisStep.next(step).offset().top - (fix.length ? fix.outerHeight() : 0) });
				}
			});	
		});
		title.on('click', function(){
			if ($(this).closest(step).hasClass('pass')) {
				var thisStep = $(this).closest(step);
				thisStep.siblings(step).find(inner).slideUp(200);
				thisStep.find(inner).slideDown({
					duration: 200,
					complete: function(){
						$('body, html').animate({scrollTop: thisStep.offset().top - (fix.length ? fix.outerHeight() : 0)});
					}
				});
				
				//step.find('select').multiselect('refresh');
			}
		});
		backBtn.on('click', function(e){
			var thisStep = $(this).closest(step);
			e.preventDefault();
			thisStep.find(inner).slideUp(200);
			thisStep.prev(step).find(inner).slideDown({
				duration: 200,
				complete: function(){
					$('body, html').animate({scrollTop: thisStep.prev(step).offset().top - (fix.length ? fix.outerHeight() : 0)});
				}
			});
		});
	}
}

function previewImg() {
	var $slider = $('.js-preview-img');
	if ($slider.length) {
		$slider.slick({
			dots: false,
			arrows: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			asNavFor: '.js-preview-slider',
			responsive: [
				{
					breakpoint: 767,
	      			settings: {
						arrows: true,
						prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
						nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
	      			}
				}
			]
		});
	}
}

function previewSlider() {
	var $slider = $('.js-preview-slider');
	if ($slider.length) {
		$slider.slick({
			lazyLoad: 'ondemand',
			dots: false,
			arrows: true,
			slidesToShow: 3,
			slidesToScroll: 1,
			centerMode: true,
			focusOnSelect: true,
			centerPadding: '0px',
			asNavFor: '.js-preview-img',
			prevArrow: '<span class="slick-prev"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></span>',
			nextArrow: '<span class="slick-next"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></span>',
		});
	}
}

function simplePopup() {
	var $popup,
		$btn = $('.js-simple-popup-call'),
		$close ;
	if ($btn.length) {
		$btn.on('click', function(e){
			e.preventDefault();
			$popup = $($(this).attr('href'));
			$close = $popup.find('.js-simple-popup-close');
			if (!$popup.hasClass('active')) {
				$popup.addClass('active').fadeIn(200);
			}
			$close.on('click', function(){
				if (!!$popup.length && $popup.hasClass('active')) {
					$popup.removeClass('active').fadeOut(200);
				}
			});
		});
		
	}
}

function openText() {
	var link = $('.js-product-text-link');
	if (link.length) {
		link.on('click', function(e){
			e.preventDefault();
			var $tab = $('.js-tabs-box-product').find('.js-tab'),
				$tabContent = $('js-tab-content'),
				$tabDesc = $('.js-tab-descr'),
				index = $tabDesc.index(),
				$fix = $('.js-fixed-header'),
				block = $('.js-product-info');
			if ($tabDesc.is(':visible')) {
				$('body, html').animate({scrollTop: block.offset().top - ($fix.length ? $fix.outerHeight() : 0)});
			} else {
				$tab.eq(index).addClass('active').siblings($tab).removeClass('active');
				$tabDesc.siblings($tabContent).fadeOut(200);
				setTimeout(function(){
					$tabDesc.fadeIn(200).addClass('active');
				},200);
				$('body, html').animate({scrollTop: block.offset().top - ($fix.length ? $fix.outerHeight() : 0)});
			}
		});
	}
}

function fancyImg() {
	var $img = $('.js-fancy-img');
	if ($img.length) {
		$img.fancybox({
			margin : [110, 20, 20, 20],
			slideShow  : false,
			fullScreen : false,
			thumbs     : false,
			loop: true,
			buttons: [
				"thumbs",
				"close"
			],
			baseTpl	:
            '<div class="fancybox-container" role="dialog" tabindex="-1">' +
                '<div class="fancybox-bg"></div>' +
                    '<div class="fancybox-toolbar">' +
                        '{{buttons}}' +
                    '</div>' +
					
                '<div class="fancybox-inner">' +
                    '<div class="fancybox-navigation">' +
                        '<button data-fancybox-prev title="{{PREV}}" class="fancybox-arrow fancybox-arrow--left"><svg class="icon-chevron-left"><use xlink:href="#chevron-small-left"></use></svg></button>' +
                        '<button data-fancybox-next title="{{NEXT}}" class="fancybox-arrow fancybox-arrow--right"><svg class="icon-chevron-right"><use xlink:href="#chevron-small-left"></use></svg></button>' +
                    '</div>' +
                    '<div class="fancybox-stage"></div>' +
                    '<div class="fancybox-caption-wrap">' +
                        '<div class="fancybox-caption"></div>' +
                    '</div>' +
                '</div>' +
            '</div>',
			btnTpl : {
				close   : '<button data-fancybox-close class="popup-simple__close" title="{{CLOSE}}">x</button>'
			},
			transitionEffect : 'slide',
			afterLoad : function() {
				svgFix();
			}
			});
	}
}

function cloudZoom() {
	var $img = $('.cloud-zoom');
	if ($img.length) {

		if($('.product__img.product-page__img img:eq(0)').attr('data-src') != ''){
			var timer = setInterval(myFunction, 1000);
			function myFunction() {
				if($('.product__img.product-page__img img:eq(0)').attr('src') != '') {
					clearInterval(timer);
					cloudZoomLazy();
					return;
				}
			}
		}

		function cloudZoomLazy(){
			if (window.innerWidth > 767) {
				$img.CloudZoom({
					position: 'inside',
					transparentImage:'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
				});
			} else {
				$('.mousetrap').remove();
				$img.addClass('js-fancy-img').attr('data-fancybox', 'gallery');
				fancyImg();
			}
		}

		$('.js-preview-img').on('lazyLoaded', function (e, slick, image, imageSource) {
			setTimeout(cloudZoomLazy(), 100);
		});


	}
}


function fancyPopUp() {
	var $popup = $('.js-fancy-popup');
	if ($popup.length) {
			$popup.fancybox({
				slideClass : 'popup-simple--fancybox', 
				btnTpl : {
					smallBtn   : '<button data-fancybox-close class="popup-simple__close" title="{{CLOSE}}">x</button>'
				},
				autoFocus : false,
				closeClickOutside : true,
				touch : false
			});
	}
}
function fancyFastCart() {
	var $popup = $('.js-fancy-popup-cart');
	if ($popup.length) {
			$popup.fancybox({
				slideClass : 'popup-simple--fancybox', 
				btnTpl : {
					smallBtn   : '<button data-fancybox-close class="popup-simple__close" title="{{CLOSE}}">x</button>'
				},
				autoFocus : false,
				closeClickOutside : true,
				touch : false,
				beforeLoad: function( instance, slide ) {
					fastCartData(slide.opts.$orig);
				}
			});

	}
}

function CategoriesMin() {
	var $div = $('.js-categories-min');
	if ($div.length) {
		$div.masonry({
			itemSelector: '.js-categories-item',
			columnWidth: '.js-categories-sizer',
			percentPosition: true
		});
	}
}

function tabsMob() {
	var $tab = $('.js-tab-content'),
	$title = $('.js-tab-title'),
	$tabDesctop = $('.js-tab'),
	$fix = $('.js-fixed-header'),
	$inner = $('.js-tab-inner');


	if ($tab.length) {
		$('.product-info .tabs-content.active .tabs-content__inner').hide().show();
		$title.on('click', function(e){
			e.preventDefault();
			var self = $(this);

			if ($('.product-info__tabs-container > .tabs-content').length != 1) {
				if (!self.closest($tab).hasClass('active')) {
					self.closest($tab).siblings($tab).removeClass('active').find($inner).slideUp(200, function(){
						self.closest($tab).find($inner).slideDown(200, function(){
							self.closest($tab).addClass('active');
							$('html, body').animate({
								scrollTop: $(self).offset().top - ($fix.length ? $fix.outerHeight() : 0)
							}, 200);
						});

					});
				} else {
					self.closest($tab).removeClass('active').find($inner).slideUp(200);
				}
			}else{
				if (!self.closest($tab).hasClass('active')) {
					self.closest($tab).find($inner).slideDown(200, function(){
						self.closest($tab).addClass('active');
						$('html, body').animate({
							scrollTop: $(self).offset().top - ($fix.length ? $fix.outerHeight() : 0)
						}, 200);
					});
				} else {
					self.closest($tab).removeClass('active').find($inner).slideUp(200);
				}
			} 
		});
	}
}

function navigationResize() {  
	
	
	$('#nav li.more').before($('#overflow > li'));
	
	var navItemMore = $('#nav > li.more'),
		navItems = $('#nav > li:not(.more)'),
		navItemWidth = navItemMore.width(),
		windowWidth = $('#nav').parent().width(),
		navOverflowWidth;
	
	navItems.each(function(){
		navItemWidth += $(this).width();
	});
	
	navItemWidth > windowWidth ? navItemMore.show() : navItemMore.hide();
	
	while (navItemWidth > windowWidth) {
		navItemWidth -= navItems.last().width();
		navItems.last().prependTo('#overflow');
		navItems.splice(-1,1);
	}
	navOverflowWidth = $('#overflow').width();

}

function mainMenu() {  
	var $fix_h = $('.js-fixed-header.header--min'),
		$hm = $('.header--min'),
		$ms = $('.main-slider');
	if (!$fix_h.length && $ms.length) {
		$hm.css({
			'position':'absolute', 
			'background':'transparent',
			'box-shadow':'none'
			});
	}
	if (window.innerWidth > 767) {
		$('.header__nav').each(function() {
			var lo = $('.header__logo').outerWidth(true),
				la = $('.header__languages').hasClass('header__languages--hide') ? 0 : 53,
				cu = $('.header__currencies').hasClass('header__currencies--hide') ? 0 : 53,
				z = lo + la + cu + 130;
				if (!$('.header--version2').length) {
					$(this).css('width','calc(100% - ' + Math.round(z) +'px)');
				}
				setTimeout(function(){
					$('.nav__list').css('overflow', 'inherit')
				}, 200);
		});
	}
}

function preloader() {
	setTimeout(function() {
		$('.preloader').addClass('invisible');
		setTimeout(function(){
			$('.preloader').hide();
			$('body').removeClass('overflow-hidden').css('padding', '');
		}, 100)
    $('.preloader').fadeOut('slow', function() {
    	$('body').removeClass('overflow-hidden').css('padding', '');
    });
  }, 250);

}

function ocReview() {
	$('#review').delegate('.pagination a', 'click', function(e) {
		e.preventDefault();

		$('#review').fadeOut('slow');

		$('#review').load(this.href);

		$('#review').fadeIn('slow');
	});
	$('#review').load('index.php?route=product/product/review&product_id=' + $('input[name=\'product_id\']').val());
}

function breadLoad() {
	$('.breadload').each(function(indx, element){
		var id = $(this).attr('data-id');
		var i = $(this).attr('data-i');
 		$('#categories-popup' + i).load('index.php?route=product/product/breadlistcr&cat_id=' + id);
	});
	
}

function ocAutocomplete() {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');
				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.search__list').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.search__list').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.search__list').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (json[i]['price']) {
							html += '<li class="search__list-item" data-value="' + json[i]['value'] + '">';
							html += '<a href="' + json[i]['href'] + '" class="search__list-item-container">';
							html += '<div class="search__img"><img src="' + json[i]['image1'] + '" alt=""></div>';
							html += '<span class="search__name">' + json[i]['label'] + '</span>';
							html += '<div class="search__price">';
							if (json[i]['special'] == false){
								html += '<span class="search__price-default">' + json[i]['price'] + '</span></div>';
							}else{
								html += '<span class="search__price-old">' + json[i]['price'] + '</span>';
								html += '<span class="search__price-default">' + json[i]['special'] + '</span></div>';								
							}
							
							html += '</a></li>';

						}else{
							if (!json[i]['category']) {
								html += '<li class="search__list-item" data-value="' + json[i]['value'] + '"><a href="#" class="search__list-item-container">' + json[i]['label'] + '</a></li>';
							}	
						}
					}

					for (i = 0; i < json.length; i++) {
						if (json[i]['track']) {
							html += '<li class="search__list-item" data-value="' + json[i]['value'] + '">';
							html += '<a href="' + json[i]['href'] + '" class="search__list-item-container">';
							html += '<span class="search__name">' + json[i]['label'] + '</span>';							
							html += '</a></li>';

						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.search__list').html(html);
			}

			$(this).after('<ul class="search__list"></ul>');
			$(this).siblings('ul.search__list').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
}


function ocSearchAutocomplete() {
	$('#search_text').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=product/product/autocomplete&filter_name=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['name'],
							price: item['price'],
							special: item['special'],
							image1: item['image'],
							href: item['href'],
							value: item['product_id']
						}
					}));
				}
			});
		},
		'select': function(item) { 
			$('#search_text').val(item['label']);
			window.location.href = item['href']; 
		}
	});
}

		$(document).on('click','.js-btn-add-cart-popup', function() {

			var prod_id = $(this).attr("for");
			var datas = $('#popupprodid_'+prod_id).find('form').serialize();
			$.ajax({
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: datas,
				dataType: 'json',
				beforeSend: function() {
					$('.js-btn-add-cart').attr('disabled', 'disabled');
				},
				complete: function() {
					$('.js-btn-add-cart').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert, .product-page__input-box-error').remove();

					if (json['error']) {
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								var element = $('#input-option' + i.replace('_', '-'));
								
								if (element.parent().hasClass('select')) {
								element.parent().after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								} else {
								element.after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								}
							}
						}

						if (json['error']['recurring']) {
							$('select[name=\'recurring_id\']').next().after('<div class="product-page__input-box-error">' + json['error']['recurring'] + '</div>');
						}
						
					}
					if (json['success']) {
						
						$('.js-product-view-hide').trigger('click');
						
						setTimeout(function () {
							if ($('.js-cart-call').length) {
								CartShow();
							} else {
								$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
								alertClose();
							}
						}, 500);
						// Need to set timeout otherwise it wont update the total
						setTimeout(function () {
							$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
						}, 100);

						$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
						svgFix();fancyFastCart();
					});
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});

function add2cartFast($data) {

			$.ajax({
				url: 'index.php?route=extension/module/lightshop/lightshopcart/fastadd2cart',
				type: 'post',
				data: $data,
				dataType: 'json',
				beforeSend: function() {
					$('.quickbuy-send').attr('disabled', 'disabled');
					$('.alert, .product-page__input-box-error, .popup-simple__inner-error-text').remove();
					$('input[name=\'name\'], input[name=\'phone\'], input[name=\'email\']').removeClass('error');	
				},
				complete: function() {
					$('.quickbuy-send').removeAttr('disabled');
				},
				success: function(json) {


					if (json['error']) { 
						if (json['redirect']) {
											
							setTimeout(function() { $.fancybox.close() }, 2000);
							setTimeout(function() { location = json['redirect']; }, 3000);

						}
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								var element = $('#input-option' + i.replace('_', '-'));
								
								if (element.parent().hasClass('select')) {
								element.parent().after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								} else {
								element.after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								}
							}

							$('h3').before('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + 'Заполните обязательные поля' + '</span></div>');
								setTimeout(function(){
									$('.alert--red').remove();
								}, 2000);											
							setTimeout(function() { $.fancybox.close() }, 1500);
	
						}

						if (json['error']['error_stock']) {

							$('h3').before('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + json['error']['error_stock'] + '</span></div>');
								setTimeout(function(){
									$('.alert--red').remove();
								}, 2000);											
							setTimeout(function() { $.fancybox.close() }, 1500);
	
						}


						if (json['error']['recurring']) {
							$('select[name=\'recurring_id\']').next().after('<div class="product-page__input-box-error">' + json['error']['recurring'] + '</div>');
							$.fancybox.close();
						}
						if (json['error']['popup']) { 
							if (json['error']['popup']['name']) {
								$('input[name=\'name\']').after('<div class="popup-simple__inner-error-text">' + json['error']['popup']['name'] + '</div>').addClass('error');
							}
							if (json['error']['popup']['phone']) {
								$('input[name=\'phone\']').after('<div class="popup-simple__inner-error-text">' + json['error']['popup']['phone'] + '</div>').addClass('error');
							}	
							if (json['error']['popup']['email']) {
								$('input[name=\'email\']').after('<div class="popup-simple__inner-error-text">' + json['error']['popup']['email'] + '</div>').addClass('error');
							}	
							if (json['error']['popup']['comment']) {
								$('textarea[name=\'comment\']').after('<div class="popup-simple__inner-error-text">' + json['error']['popup']['comment'] + '</div>').addClass('error');
							}	
							if (json['error']['popup']['captcha']) { 

								$('h3').before('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + json['error']['popup']['captcha'] + '</span></div>');
									setTimeout(function(){
										$('.alert--red').remove();
									}, 2000);											
		
							}
						}
					}
					if (json['success']) {
						sendMetrics('lightshop_buyclick_success');

						
						$.fancybox.close();
						$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
						$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
							svgFix();
						});
						location = json['redirect'];
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});	
}

function fastCartData($objectClick) { 		
			var product_id = $objectClick.attr('data-for');
			var qty = $objectClick.closest(".products-qty-info").find('.products-qty-info-spinner input').val();
			var type = $objectClick.attr('data-typefrom');

			$('#cat_qty').val(qty);
			$('#cat_prod_id').val(product_id);
			if(type == "category-popup"){
				$('.fast-redirect').val(0);
				var selector = 'popupprodid_'+product_id;
				var $data = $('#'+selector+' input[type=\'text\'], #'+selector+' input[type=\'hidden\'], #'+selector+' input[type=\'radio\']:checked, #'+selector+' input[type=\'checkbox\']:checked, #'+selector+' select, #'+selector+' textarea');
			}
			if(type == "category-list"){
				$('.fast-redirect').val(1);
				var $data = $('#popup-buy-click input');
			}
			if(type == "cart-popup"){
				return;
			}
			if(type == "product"){
				var $data = $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea');
			}
			add2cartFast($data);
}

		$(document).delegate('.js-btn-add-cart-fast','click', function(e) {
			var $data = $('#popup-buy-click input,#popup-buy-click textarea,  #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product input[type=\'text\']:not([name=\'quantity\']), #product select, #product textarea');
			add2cartFast($data);			
		});

		$(document).delegate('.js-btn-add-cart-fast-custom','click', function(e) {
			var $data = $('#popup-buy-click-cc input,#popup-buy-click-cc textarea');
			add2cartFast($data);			
		});		
	
		$(document).delegate('.js-btn-add-cart-fast-popup','click', function(e) {
			e.preventDefault();
			var selector = 'popupprodid_'+$('#cat_prod_id').val();
			var $data = $('#'+selector+' input[type=\'text\']:not([name=\'quantity\'],[name=\'product_id\']), #'+selector+' input[type=\'radio\']:checked, #'+selector+' input[type=\'checkbox\']:checked, #'+selector+' select, #'+selector+' textarea, #popup-buy-click textarea, #popup-buy-click input');
			add2cartFast($data);			
		});


function ocProduct() {

	var $pp = $('#product');
	var datapr = $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea');
	if ($pp.length) {
		$('.js-btn-add-cart').on('click', function() {
			$.ajax({
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('.js-btn-add-cart').attr('disabled', 'disabled');
				},
				complete: function() {
					$('.js-btn-add-cart').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert, .product-page__input-box-error').remove();

					if (json['error']) {
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								var element = $('#input-option' + i.replace('_', '-'));
								
								if (element.parent().hasClass('select')) {
								element.parent().after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								} else {
								element.after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								}
							}
						}

						if (json['error']['recurring']) {
							$('select[name=\'recurring_id\']').next().after('<div class="product-page__input-box-error">' + json['error']['recurring'] + '</div>');
						}
					}
					if (json['success']) {
						if ($('.js-cart-call').length) {
								CartShow();
						} else {
							$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
							alertClose();
						}
						sendYM('lightshop_addtocart_product');
						sendGA(datapr,'lightshop_addtocart_product');

						setTimeout(function () {
							$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
						}, 100);

						$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container');
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){
					svgFix();fancyFastCart();
					});
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
		
		$('.js-btn-add-cart-fast1').on('click', function() {
			$.ajax({
				url: 'index.php?route=extension/module/lightshop/lightshopcart/fastadd2cart',
				type: 'post',
				data: $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea, #popup-buy-click textarea, #popup-buy-click input[type=\'text\']'),
				dataType: 'json',
				beforeSend: function() {
					$('.js-btn-add-cart').attr('disabled', 'disabled');
				},
				complete: function() {
					$('.js-btn-add-cart').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert, .product-page__input-box-error').remove();

					if (json['error']) {
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								var element = $('#input-option' + i.replace('_', '-'));
								
								if (element.parent().hasClass('select')) {
								element.parent().after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								} else {
								element.after('<div class="product-page__input-box-error">' + json['error']['option'][i] + '</div>');
								}
							}
						}

						if (json['error']['recurring']) {
							$('select[name=\'recurring_id\']').next().after('<div class="product-page__input-box-error">' + json['error']['recurring'] + '</div>');
						}
						$.fancybox.close();
					}
					if (json['success']) {

					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	
		$('select[name=\'recurring_id\'], input[name="quantity"]').change(function(){
			$.ajax({
				url: 'index.php?route=product/product/getRecurringDescription',
				type: 'post',
				data: $('input[name=\'product_id\'], input[name=\'quantity\'], select[name=\'recurring_id\']'),
				dataType: 'json',
				beforeSend: function() {
					$('#recurring-description').html('');
				},
				success: function(json) {
					$('.alert, .product-page__input-box-error').remove();

					if (json['success']) {
						$('#recurring-description').html(json['success']);
					}
				}
			});
		});


			
		$(document).ready(function() {
			var hash = window.location.hash;
			if (hash) {
				var hashpart = hash.split('#');
				var  vals = hashpart[1].split('-');
				for (i=0; i<vals.length; i++) {
					$('div.options').find('select option[value="'+vals[i]+'"]').attr('selected', true).trigger('select');
					$('div.options').find('input[type="radio"][value="'+vals[i]+'"]').attr('checked', true).trigger('click');
				}
			}
		})

	}
}

		$(document).on('click','button[id^=\'button-upload\']', function(e) {
			var node = this;

			$('#form-upload').remove();

			$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

			$('#form-upload input[name=\'file\']').trigger('click');

			if (typeof timer != 'undefined') {
				clearInterval(timer);
			}

			timer = setInterval(function() {
				if ($('#form-upload input[name=\'file\']').val() != '') {
					clearInterval(timer);

					$.ajax({
						url: 'index.php?route=tool/upload',
						type: 'post',
						dataType: 'json',
						data: new FormData($('#form-upload')[0]),
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function() {
							$(node).attr('disabled', 'disabled');
						},
						complete: function() {
							$(node).removeAttr('disabled');
						},
						success: function(json) {
							$(node).parent().find('.product-page__input-box-error').remove();

							if (json['error']) {
								$(node).parent().find('input').after('<div class="product-page__input-box-error">' + json['error'] + '</div>');
							}

							if (json['success']) {						
								$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
								alertClose();
								
								$(node).parent().find('input').val(json['code']);
							}
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				}
			}, 500);
		});

function ocAddCoupon() {
	$('.js-add-coupon').on('click', function() {
		$.ajax({
			url: 'index.php?route=extension/total/coupon/coupon',
			type: 'post',
			data: 'coupon=' + encodeURIComponent($('input[name=\'coupon\']').val()),
			dataType: 'json',
			beforeSend: function() {
				$('.js-add-coupon').attr('disabled', 'disabled');
			},
			complete: function() {
				$('.js-add-coupon').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert').remove();

				if (json['error']) {
					$('.alerts').append($('<div class="alert alert--red"><span class="alert__text"> ' + json['error'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();
				}

				if (json['redirect']) {
					location = json['redirect'];
				}
			}
		});
	});
    $('input[name=\'coupon\']').keypress(function(e){
        if(e.which == 13){
            $(this).next().click();
        }
    });
}

function ocAddVoucher() {
	$('.js-add-voucher').on('click', function() {
		$.ajax({
			url: 'index.php?route=extension/total/voucher/voucher',
			type: 'post',
			data: 'voucher=' + encodeURIComponent($('input[name=\'voucher\']').val()),
			dataType: 'json',
			beforeSend: function() {
			  $('.js-add-voucher').attr('disabled', 'disabled');
			},
			complete: function() {
			  $('.js-add-voucher').removeAttr('disabled');
			},
			success: function(json) {
			  $('.alert').remove();

			  if (json['error']) {
					$('.alerts').append($('<div class="alert alert--red"><span class="alert__text"> ' + json['error'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();
			  }

			  if (json['redirect']) {
				location = json['redirect'];
			  }
			}
		});
	});
    $('input[name=\'voucher\']').keypress(function(e){
        if(e.which == 13){
            $(this).next().click();
        }
    });	
}

function ocAddReward() {
	$('.js-add-reward').on('click', function() {
		$.ajax({
			url: 'index.php?route=extension/total/reward/reward',
			type: 'post',
			data: 'reward=' + encodeURIComponent($('input[name=\'reward\']').val()),
			dataType: 'json',
			beforeSend: function() {
				$('.js-add-reward').attr('disabled', 'disabled');
			},
			complete: function() {
				$('.js-add-reward').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert').remove();

				if (json['error']) {
					$('.alerts').append($('<div class="alert alert--red"><span class="alert__text"> ' + json['error'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();
				}

				if (json['redirect']) {
					location = json['redirect'];
				}
			}
		});
	});
    $('input[name=\'reward\']').keypress(function(e){
        if(e.which == 13){
            $(this).next().click();
        }
    });		
}

function ocCartShipping() {
	$('#button-quote').on('click', function() {
		$.ajax({
			url: 'index.php?route=extension/total/shipping/quote',
			type: 'post',
			data: 'country_id=' + $('select[name=\'country_id\']').val() + '&zone_id=' + $('select[name=\'zone_id\']').val() + '&postcode=' + encodeURIComponent($('input[name=\'postcode\']').val()),
			dataType: 'json',
			beforeSend: function() {
				$('#button-quote').attr('disabled', 'disabled');
			},
			complete: function() {
				$('#button-quote').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert, .checkout__input-box-error').remove();
				$('select[name=\'country_id\'], select[name=\'zone_id\']').next().removeClass('select--error');

				if (json['error']) {
					if (json['error']['warning']) {
						$('.alerts').append($('<div class="alert alert--red"><span class="alert__text"> ' + json['error'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
						alertClose();						
					}

					if (json['error']['country']) {
						$('select[name=\'country_id\']').next().after('<div class="checkout__input-box-error">' + json['error']['country'] + '</div>').addClass('select--error');
					}

					if (json['error']['zone']) {
						$('select[name=\'zone_id\']').next().after('<div class="checkout__input-box-error">' + json['error']['zone'] + '</div>').addClass('select--error');
					}

					if (json['error']['postcode']) {
						$('input[name=\'postcode\']').after('<div class="checkout__input-box-error">' + json['error']['postcode'] + '</div>');
					}
				}

				if (json['shipping_method']) {
					$('#modal-shipping').remove();

					html  = '<div class="popup-simple" id="modal-shipping">'; 
					html += '  <div class="popup-simple__inner">';
					html += '        <h3>'+ $('input[name=\'fix_text_shipping_method\']').val() + '</h3>';
					html += '        <div class="checkout">';
					html += '        <div class="checkout__radio-box-row">';

					for (i in json['shipping_method']) {
						html += '<span class="checkout__input-box-title">' + json['shipping_method'][i]['title'] + '</span>';

						if (!json['shipping_method'][i]['error']) {
							for (j in json['shipping_method'][i]['quote']) {
								html += '<div class="checkout__radio-box">';
								
								if (json['shipping_method'][i]['quote'][j]['code'] == $('input[name=\'fix_shipping_method\']').val()) {
									html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" id="' + json['shipping_method'][i]['quote'][j]['code'] + '" checked="checked">';
								} else {
									html += '<input type="radio" name="shipping_method" value="' + json['shipping_method'][i]['quote'][j]['code'] + '" id="' + json['shipping_method'][i]['quote'][j]['code'] + '">';
								}
								html += '  <label for="' + json['shipping_method'][i]['quote'][j]['code'] + '">';
								html += json['shipping_method'][i]['quote'][j]['title'] + ' - ' + json['shipping_method'][i]['quote'][j]['text'] + '</label></div>';
							}
						} else {
							html += '<div class="alert alert-danger">' + json['shipping_method'][i]['error'] + '</div>';
						}
					}
					html += '        </div>';
					if ($('input[name=\'fix_shipping_method\']').val()) {
					html += '        <button type="button" id="button-shipping" class="btn">'+ $('input[name=\'fix_button_shipping\']').val() + '</button>';
					} else {
					html += '        <button type="button" id="button-shipping"  class="btn" disabled="disabled">'+ $('input[name=\'fix_button_shipping\']').val() + '</button>';
					}
					
					
					html += '  </div>';
					html += '  </div>';
					html += '</div> ';

					$('body').append(html);

					$.fancybox.open([{
						src  : '#modal-shipping',
						opts : {
							slideClass : 'popup-simple--fancybox', 
							btnTpl : {
								smallBtn   : '<button data-fancybox-close class="popup-simple__close" title="{{CLOSE}}">x</button>'
							},
							autoFocus : false,
							closeClickOutside : true,
							touch : false
						}
					}]);

					$('input[name=\'shipping_method\']').on('change', function() {
						$('#button-shipping').prop('disabled', false);
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$(document).delegate('#button-shipping', 'click', function() {
		$.ajax({
			url: 'index.php?route=extension/total/shipping/shipping',
			type: 'post',
			data: 'shipping_method=' + encodeURIComponent($('input[name=\'shipping_method\']:checked').val()),
			dataType: 'json',
			beforeSend: function() {
				$('#button-shipping').attr('disabled', 'disabled');
			},
			complete: function() {
				$('#button-shipping').removeAttr('disabled');
			},
			success: function(json) {
				$('.alert').remove();

				if (json['error']) {
					$('.alerts').append($('<div class="alert alert--red"><span class="alert__text"> ' + json['error'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
					alertClose();		
				}
				setTimeout(function(){
					$.fancybox.close();
				}, 500)
				
				if (json['redirect']) {
					location = json['redirect'];
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});


	$('select[name=\'country_id\']').on('change', function() {
		$.ajax({
			url: 'index.php?route=extension/total/shipping/country&country_id=' + this.value,
			dataType: 'json',
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
			
				if (json['postcode_required'] == '1') {
					$('input[name=\'postcode\']').parent().parent().addClass('required');
				} else {
					$('input[name=\'postcode\']').parent().parent().removeClass('required');
				}

				html = '<option value="">'+ $('input[name=\'fix_text_select\']').val() +'</option>';

				if (json['zone'] && json['zone'] != '') {
					for (i = 0; i < json['zone'].length; i++) {
						html += '<option value="' + json['zone'][i]['zone_id'] + '"';

						if (json['zone'][i]['zone_id'] == $('input[name=\'fix_zone_id\']').val()) {
							html += ' selected="selected"';
						}

						html += '>' + json['zone'][i]['name'] + '</option>';
					}
					
				} else {
					html += '<option value="0" selected="selected">'+ $('input[name=\'fix_text_none\']').val() +'</option>';
				}

				$('select[name=\'zone_id\']').html(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
			
		});

	});

	$('select[name=\'country_id\']').trigger('change');
}

function ocTrackingAutocomplete() {
	$('#input-generator').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=account/tracking/autocomplete&filter_name=' +  encodeURIComponent(request) + '&tracking=' + encodeURIComponent($('#input-code').val()),
				dataType: 'json',
				success: function(json) {									
					response($.map(json, function(item) {
						return {
							label: item['name'],
							image1: item['thumb'],
							href: item['link'],
							price: item['price'],
							special: item['special'],
							value: item['link']
						}
					}));
				}
			});
		},
		'select': function(item) {
			$('input[name=\'product\']').val(item['label']);
			$('#input-link').val(item['value']);
		}
	});
}

function ocDateTimepicker() {
	var $d = $('.date'),
		$dt = $('.datetime'),
		$t = $('.time');		
	if ($d.length) {
		$d.datetimepicker({
			pickTime: false
		});
	}	
	if ($dt.length) {	
		$dt.datetimepicker({
			pickDate: true,
			pickTime: true
		});
	}	
	if ($t.length) {	
		$t.datetimepicker({
			pickDate: false
		});
	}
}

function alertClose() {
	var $al = $('.alert__close');
		$al.click(function(e){
			$al.parent().fadeOut(500, function() {
				$(this).remove();
			});
		e.preventDefault();	
		})	
}

function ocAgree() {
	/* Agree to Terms */
	$(document).delegate('.agree', 'click', function(e) {
		e.preventDefault();

		$('#modal-agree').remove();

		var element = this;

		$.ajax({
			url: $(element).attr('href'),
			type: 'get',
			dataType: 'html',
			success: function(data) {
				html  = '<div class="popup-simple" id="modal-agree">';
				html += '  <div class="popup-simple__inner">';
				html += '        <h3>' + $(element).text() + '</h4>';
				html += '      <div class="popup-simple__inner-text">' + data + '</div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$.fancybox.open([{
					src  : '#modal-agree',
					opts : {
						slideClass : 'popup-simple--fancybox', 
						btnTpl : {
							smallBtn   : '<button data-fancybox-close class="popup-simple__close" title="{{CLOSE}}">x</button>'
						},
						autoFocus : false,
						closeClickOutside : true,
						touch : false
					}
				}]);
			}
		});
	});
}

function ocFilter() {
	$('#button-filter').on('click', function() {
		filter = [];

		$('input[name^=\'filter\']:checked').each(function(element) {
			filter.push(this.value);
		});
		var min_price = $('#min_price').val();
		var max_price = $('#max_price').val();

		location = $('input[name=\'fix_filter_action\']').val() + '&filter=' + filter.join(',') + '&min_price=' + min_price + '&max_price=' + max_price;
	});
}

function textHeight() {
	var cth = $('.js-cat-title-height');
	if(cth.length) {
		cth.next().css('height','calc(100% - ' + (cth.height() + 20) +'px)');
	}
}

function other() {
	//cart fix
	if($('.js-cart-tabs-modules').length) {
		$('.js-cart-tabs-modules .js-tab').appendTo('.js-cart-tabs-list');
		$('.js-cart-tabs-modules .js-tab-content').appendTo('.js-cart-tabs-container');
		$('.js-cart-tabs-modules').remove();
		$('.cart__meta .js-tab:eq(0)').trigger('click');
	}
	
	//btn mobile fix
	if ($('.sidebar').length) {	
		$('.sidebar .js-popup-call').appendTo('#sidebar-mob-btn');
		$('.sidebar .js-btn-filter').appendTo('#sidebar-mob-btn');
	}
		
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent(); 

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// Currency
	$('body').on('change','#form-currency select', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).val());

		$('#form-currency').submit();
	});

	// Language
	$('body').on('change','#form-language select', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).val());

		$('#form-language').submit();
	});

	/* Search */
	$('#search_text').parent().find('button').on('click', function() {
		var url = $('base').attr('href') + 'index.php?route=product/search';
		var value = $('#search_text').val();
		if (value) {
			url += '&search=' + encodeURIComponent(value);
		}
		location = url;
	});

	$('#search_text').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('#search_text').parent().find('button').trigger('click');
		}
	});

	// Checkout
	$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
		if (e.keyCode == 13) {
			$('#collapse-checkout-option #button-login').trigger('click');
		}
	});
	
	// Mobile languages and currencies
	if (window.innerWidth < 768) {
		if (!$('.header__languages').hasClass('header__languages--hide') || !$('.header__currencies').hasClass('header__currencies--hide')) {	
			$('.header__languages,.header__currencies').appendTo('#langcurr');
			$('#langcurr').addClass('account__col');
		}
	}
}

function svgFix() {
/*! https://gist.github.com/leonderijke/c5cf7c5b2e424c0061d2#file-svgfixer-js */
		var ua = navigator.userAgent;
		if (ua.search(/Firefox/) == -1) {return ;}
		var baseUrl = window.location.href
			.replace(window.location.hash, "");

		if ( /#$/.test(baseUrl) ) {// Если заканчивается на # - обрезаем
			baseUrl = baseUrl.slice(0, -1);
		}

		[].slice.call(document.querySelectorAll("use[*|href]"))
			.filter(function(element) {
				return (element.getAttribute("xlink:href").indexOf("#") === 0);
			})
			.forEach(function(element) {
				element.setAttribute("xlink:href", baseUrl + element.getAttribute("xlink:href"));
			});
}

function compSliderArrows () {
	var arrLeft = $(".slick-prev2").html();
	var arrRight = $(".slick-next2").html();
	$(".slider-for1 .slick-prev").html("").append(arrLeft).prepend("<svg class='icon-left'><use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#chevron-small-left'></use></svg>");
	$(".slider-for1 .slick-next").html("").prepend(arrRight).append("<svg class='icon-right'><use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#chevron-small-left'></use></svg>");
	$(".slider-nav1 .slick-prev").html("").append(arrLeft).prepend("<svg class='icon-left'><use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#chevron-small-left'></use></svg>");
	$(".slider-nav1 .slick-next").html("").prepend(arrRight).append("<svg class='icon-right'><use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='#chevron-small-left'></use></svg>");
}

function comparisonScroll (){
	if($('.comparison').length) {
		$(window).scroll(function(e) {
			var fix = $('.js-fixed-header').length ? $('.js-fixed-header').outerHeight() : 0,
				//sBtn = $('.slick-prev').length ? 50 : 0,
				sBtn = 50,
				top = $(document).scrollTop(),
				dWidth = $(window).width(),
				comp = $('.comparison'),
				compPos = comp.offset().top - fix,
				compHeight = $('.comparison__cont').height();
			if (dWidth > 767){
				if (top >= compPos - sBtn) {
					$('.comparison__head').addClass('fixed').css({'top' : fix + 'px', 'width' : comp.outerWidth() + 'px'});
					comp.css("padding-top", 140 + 'px');
				} else {
					$('.comparison__head').removeClass('fixed').css('width', 'auto');
					comp.css('padding-top', '0px');
				}
			} else if (dWidth > 520 && dWidth < 767){
				if (top >= compPos - sBtn && top <= compPos + compHeight) {
					$('.comparison__head').addClass('fixed').css({'top' : fix + 'px', 'width' : comp.outerWidth() + 'px'});
					comp.css('padding-top', 170 + 'px');
				} else {
					$('.comparison__head').removeClass('fixed').css('width', 'auto');
					comp.css('padding-top', '40px');
				}
			} else{
				if (top >= compPos - sBtn && top <= compPos + compHeight) {
					$('.comparison__head').addClass('fixed').css({'top' : fix + 'px', 'width' : comp.outerWidth() + 'px'});
					comp.css('padding-top', 230 + 'px');
				} else {
					$('.comparison__head').removeClass('fixed').css('width', 'auto');
					comp.css('padding-top', '40px');
				}
			}
		});
	}
}
function comparisonCell (){
	if($('.comparison').length) {
		var prodW = $('.comparison__cont .right_side .table-wrap').width();
		$('.comparison__head .slick-slide').css('max-width', prodW+1);
	}
}

function shareBtn (){
	$('.share-btn').click(function ( e ) {
		$(this).hide();
		$('.share-btns').fadeToggle();
	});
};

function mobiheader() {
		windWidth = window.innerWidth;
		if (windWidth < 768) {
			$('#popup-navigation').load('index.php?route=common/header&mobiheader=1', function(e) {
				mobSubMenu();
			});			
			var date = new Date(new Date().getTime() + 60 * 1000);
			document.cookie = "ismobile=1; path=/; expires=" + date.toUTCString();
		}else{
			var date = new Date(new Date().getTime() + 60 * 1000);
			document.cookie = "ismobile=0; path=/; expires=" + date.toUTCString();
		}
}

function getCompareWish() {
	$('#compare').load('index.php?route=common/header/getcompare');			
	$('#wish').load('index.php?route=common/header/getwish');
	$.ajax({
		url: 'index.php?route=common/header/getwishcompare',
		type: 'post',
		dataType: 'json',
		data: '',
		success: function (data) {
			$('#wishcomptotall').html(data['counTotall']);
			if (data['counTotall'] == 0) {
				$('#wishcomptotall').hide();
			}else{
				$('#wishcomptotall').show();
			}
		}
	}); 
}

function productView() {
	$('.product-view__list-item').on('click', function(){
		var cat_id = $category_id
	var href = window.location.search;		
		$('#mainContainer').load('index.php?route=product/category/chview/'+href); 

	});

}
function addSubscribe() {
	
    $('input[name=\'emailsubscr\']').keypress(function(e){ 
        if(e.which == 13){
            $(this).next().click();
        }
    });	
	
	$('.subscribe__btn').on('click', function(){
	var email = $('input[name="emailsubscr"]').val();

	$.ajax({
		url: 'index.php?route=extension/module/lightshop_subscribe/addsubscribe',
		type: 'post',
		dataType: 'json',
		data: 'email='+email+'&module=0',
		success: function (data) {

			if (data['error']) {
				alert(data['error']);
			}

			if (data['success']) {
					sendMetrics('lightshop_subscribe');

				alert(data['success']);
				$('input[name=\'emailsubscr\']').val('');
			}
		}
	}); 

	});
}

function scrollToTop() {
	var $s = $('.js-stt'),
		$c = $('.cookieagry');
	if ($s.length && window.innerWidth > 767) {
		$(window).scroll(function(){
			if ($(this).scrollTop() > 300){ 
				$s.addClass('active');
			} else {
				$s.removeClass('active');
			}
		});	
 		$s.on('click', function(e){
			$('html, body').animate({
				scrollTop: 0
			}, 400);
 			e.preventDefault();
 		});
		if ($c.length) {
			$s.css({
			'bottom' : $c.outerHeight() + 'px',
			});
		}
	}	
}

function getOcFilterUrl() {
		filter = [];

		$('input[name^=\'filter\']:checked').each(function(element) {
			filter.push(this.value);
		});
		var min_price = $('#min_price').val();
		var max_price = $('#max_price').val();

		var url = $('input[name=\'fix_filter_action\']').val() + '&filter=' + filter.join(',') + '&min_price=' + min_price + '&max_price=' + max_price ;
		return url ;
}

function sliderProducts(t) {
		$(".slidproducts").remove();
		$('#button-filter').removeAttr('disabled');
		var	filter = [];
		var $el = t.closest(".js-filter-parent");

		$('input[name^=\'filter\']:checked').each(function(element) {
			filter.push(this.value);
		});
		var min_price = $('#min_price').val();
		var max_price = $('#max_price').val();
		var cat_id = $('#filter_category_id').val();

	var url = getOcFilterUrl();
	$.ajax({
	  url: 'index.php?route=product/category/totalproducts/'+ '&filter=' + filter.join(',') + '&min_price=' + min_price + '&max_price=' + max_price   + '&filter_category_id=' + cat_id,
	  dataType: 'json',
	  success: function (json) {
	  var balun ;
	  if(json['total']){
		balun = '<span class="products-amount slidproducts" id="count-'+json['id']+'"><span class="products-amount__amount"> '+json['text_products']+' </span>'+ json['total']+'<a href="'+url+'">'+json['text_show']+'</a></span>';

	  }else{
		balun = '<span class="products-amount slidproducts" id="count-'+json['id']+'"><span class="products-amount__amount"> '+json['text_products']+' </span>'+ json['total'];
		$('#button-filter').attr('disabled','disabled');
	  }

	$el.append(balun);	
					setTimeout(function(){
						$("#count-"+json['id']).hide();
					}, 6000)
	  }
	});
}

$(document).on('click', '.js-fancy-popup-cart', function(e) {
		var eventtag = 'lightshop_buyclick';
		sendMetrics(eventtag);
});

function sendYM(event){
	if (typeof (ym) === "function") { // If YandexMetric is turned on in admin's pannel
		if($("meta[property='yandex_metric']").attr('content')){
			var yandex_metric = $("meta[property='yandex_metric']").attr('content');
			ym(yandex_metric, 'reachGoal', event);
		}
	}
}

function sendMetrics(eventname){
	if (typeof (gtag) === "function") { // If Google Metric is turned on in admin's pannel 
		gtag('event', eventname, {});
	}	
	if (typeof (ym) === "function") { // If YandexMetric is turned on in admin's pannel 
		if($("meta[property='yandex_metric']").attr('content')){
			var yandex_metric = $("meta[property='yandex_metric']").attr('content');
			ym(yandex_metric, 'reachGoal', eventname);
		}
	}		
}

function sendGA(datapr,event){

		if (typeof (gtag) !== "function") { // If GA is turned off in admin's pannel, return 
			return;
		}

		$.ajax({
	  			url: 'index.php?route=product/product/analystdata',
				dataType: "json",
				type: "POST",
				data: datapr,
				success: function(item){
					if(!!item){
						gtag('event', event, item);
					}
				}
			});
}

function sendGAch(datapr,event){ 
		if (typeof (gtag) !== "function") { // If GA is turned off in admin's pannel, return 
			return;
		}
		$.ajax({
	  			url: 'index.php?route=product/product/analystdataorder',
				dataType: "json",
				type: "POST",
				data: datapr,
				success: function(data){ 
//					if(!!data){
						setTimeout(function(){

							gtag('event', event, data);

						},100);
//					}
				}
		});
}

function priceslider() {
  if ($('.js-price-slider').length) {

	 var d = parseFloat($("#min_price_init").val());
     var c = parseFloat($("#max_price_init").val());
	 var e = parseFloat($("#min_price_current").val());
     var f = parseFloat($("#max_price_current").val());
	var  span_start = $('.js-span-start');
	var  span_end = $('.js-span-end');
	var  currencydata = $("#currencydata").val().split('_');


    $("#slider-range").slider({range:true, min:d, max:c, values:[e, f], step:0.01,
     stop:function (a, b) {
        sliderProducts($(this));
    },
    slide:function (a, b) {
        $("#min_price").val(b.values[0]);
        $("#max_price").val(b.values[1]);
        	if(currencydata[0] == 'R'){
				$('.js-span-start').html(b.values[0]+currencydata[1]);
				$('.js-span-end').html(b.values[1]+currencydata[1]);
        	}else{
 				$('.js-span-start').html(currencydata[1] + b.values[0]);
				$('.js-span-end').html(currencydata[1] + b.values[1]);       		
        	}

    }});
    $("#min_price").val($("#slider-range").slider("values", 0));
    $("#max_price").val($("#slider-range").slider("values", 1));

	$('input[name^=\'filter\']').on('click', function(e) {
		sliderProducts($(this));
	});
	$('.price_limit').on('change', function() {
		sliderProducts($(this));
		var s = $("#min_price").val();
		var t = $("#max_price").val();
		if(currencydata[0] == 'R'){		
			span_start.html(s+currencydata[1]);
			span_end.html(t+currencydata[1]);
		}else{
			span_start.html(currencydata[1]+s);
			span_end.html(currencydata[1] + t);			
		}
	   $("#slider-range").slider({values:[s, t]});

	});

		span_start.on('click', function(){
			span_start.hide();
			$(this).parent().find($('input.js-price-slider-input')).trigger('focus');

		});
		 span_end.on('click', function(){
			 span_end.hide();
			$(this).parent().find($('input.js-price-slider-input')).trigger('focus');
		})
		$('input.js-price-slider-input').on('blur', function(){
			$(this).parent().find($( '.js-span-start, .js-span-end')).show();
			
		}); 
  }
}

$(document).on('click','.contact-send',function() {
		var success = 'false';
		$.ajax({
			url: 'index.php?route=extension/module/callback',
			type: 'post',
			data: $(this).closest('.data-callback').serialize() + '&action=send',
			dataType: 'json',
			beforeSend: function() {
				$('.data-callback > button').attr('disabled', 'disabled');
				$('.popup-simple__inner-error-text').remove();
			},
			complete: function() {
				$('.data-callback > button').removeAttr('disabled');
			},
			success: function(json) {
			
				if (json['warning']) {
					if (json['warning']['name']) {
						$('input[name=\'name\']').after('<div class="popup-simple__inner-error-text">' + json['warning']['name'] + '</div>').addClass('error');
					}
					if (json['warning']['phone']) {
						$('input[name=\'phone\']').after('<div class="popup-simple__inner-error-text">' + json['warning']['phone'] + '</div>').addClass('error');
					}
					if (json['warning']['captcha']) {
						$('.data-callback h3').before('<div class="alert alert--red" style="margin-bottom: 20px;"><span class="alert__text">' + json['warning']['captcha'] + '</span></div>');
							setTimeout(function(){
								$('.alert--red').remove();
							}, 5000)	
					}
				}
				if (json['success']){
					$('.data-callback h3').before('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + '</span></div>');				
					success = 'true';
					sendMetrics('lightshop_callback');

					setTimeout(function(){
						$('.data-callback .popup-simple__inner-error-text,#data-callback .alert').remove();
						$('.data-callback input[name=\'name\'],#data-callback input[name=\'phone\']').removeClass('error');
						$('.data-callback input[name=\'name\'],#data-callback input[name=\'phone\']').val('');
						$.fancybox.close();	
					}, 3000)
				} 
			}

		});
});

	$(document).on('click', '.catalogue__btn-cart-list', function(e) {
		var product_id = $(this).attr('data-for');
		var qty = $(this).closest('.products-qty-info').find('input.spinner').val();
		cart.add(product_id, qty);
	});

	$(document).on('click', '#cookieagry', function(e) {
		var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 365);
		document.cookie = "cookieagry=1; path=/; expires=" + date.toUTCString();
		$('.cookieagry').hide();
		$('.js-stt').css({
			'bottom' : '0px',
		});
	});

	$(document).on('click', '.catalogue__btn-cart', function(e) {
		var product_id = $(this).attr('data-for');
		var qty = $(this).closest(".catalogue__table-row").find('.catalogue__table-spinner input').val();
		cart.add(product_id, qty);
	});
	
	$(document).on('click', '#resetfilter', function(e) {
		var cat_url = $('#cat_url').val();
		location = cat_url;
	});

	$(document).on('click', '.js-btn-add-set2cart', function(e) {

		$.ajax({
			url: 'index.php?route=checkout/cart/setadd',
			type: 'post',
			data: $(this).closest('.setdata').serialize(),
			dataType: 'json',
			beforeSend: function() {

			},
			complete: function() {
			},
			success: function(json) {

					if ($('.js-cart-call').length) {
								CartShow();
					} else {
						$('.alerts').append($('<div class="alert alert--green"><span class="alert__text"> ' + json['success'] + ' </span><a href="#" class="alert__close">x</a></div>').hide().fadeIn(500));
						alertClose();
					}
					sendMetrics('lightshop_settocart');
				
					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#cart-total').html('<span class="actions__counter">' + json['total'] + '</span>');
					}, 100);

					$('#cart .js-cart-bottom').load('index.php?route=common/cart/info .scroll-container',function(){
						setTimeout(fancyFastCart(), 600);
					});
					$('#cart .js-cart-title').load('index.php?route=common/cart/info .js-cart-items',function(){svgFix();});			
			}

		});
		return;
	});






// SETS start
function lightshopSet() {

    $(document).on('click','.js-set-chng-btn', function(e){
      var product_id = $(this).attr("data-for");
      var set_id = $(this).closest('.package__result-container').attr('data-for');
      var popup = $('.js-popup-package');

       $('#popup-set-click-content').empty();

        $('#popup-set-click-content').load('index.php?route=extension/module/lightshop_set/getvariants&product_id='+product_id+'&set_id='+set_id,function(){
          		spinner();
          		svgFix();
        });

    });


    $(document).on('click','.js-add-toset', function(e){
      e.preventDefault();
      var old_product_id = $(this).attr("data-for");
 	  var set_id = $(this).closest('div.package__pick-container').attr("data-for");
 	  var qty = $(this).attr("data-qty");
	  var elm = $('.package__result-container[data-for = "'+set_id+'"]').find('.productsinset[data-for = "'+old_product_id+'"]'); 
 	  var product_id = $(this).find('.package__pick-item-add').attr("data-for");
 	  var popup = $('.js-popup-package'); 
 	  var proddata =  $('.set_product_data');

 	  $('.set_product_data').each(function(indx, element){

 	  		if ($(element).attr("data-for") == old_product_id) {
 	  			$(element).attr("data-for",product_id);
 	  			$(element).attr("name",'setproducts['+product_id+']');
 	  			
 	  		}
		});

      elm.empty();


        elm.load('index.php?route=extension/module/lightshop_set/getproduct&product_id='+product_id+'&qty='+qty,function(){
          		spinner();
          		svgFix();
          		fancyPopUp();
        });

      elm.attr("data-for",product_id);

      setTimeout(function() { $.fancybox.close() }, 1000);

      		var datas = $('.setdata-'+set_id+'').serialize();
			$.ajax({
				url: 'index.php?route=extension/module/lightshop_set/refreshtotal',
				type: 'post',
				data: datas,
				dataType: 'json',
				beforeSend: function() {
				},
				complete: function() {
				},
				success: function(json) {

					if (json['success']) {
						$('.setdata-'+set_id+' .set-total').html(json['success']['total']);
						$('.setdata-'+set_id+' .set-discount').html(json['success']['discount']);
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});



    });

	$('.package__result-container').each(function(indx){
		var width = 0;
		var elem = $(this);
		elem.find('.package__result-item').each(function(indx,element){
			var style = window.getComputedStyle(element);
  			width += $(this).width() + parseFloat(style.marginLeft) + parseFloat(style.paddingLeft) + parseFloat(style.paddingRight);		
  		});
  		width += 10;
  		$(this).css("min-width", width+"px");
	});

}

// SETS end

function chats() {
	$(document).on('click', '.app-chats__toggle', function (event) {
		event.preventDefault();
		if ($('html').is('.is-chats-open')) {
			$('html').removeClass('is-chats-open');
		} else {
			$('html').addClass('is-chats-open');
		}
	});

	$(document).on('click', function (e) {
		if ($(e.target).closest('.app-chats').length == 0) {
			$('html').removeClass('is-chats-open');
		}
	});
}

function tabsOpen() {
		$('.js-product-info').find('.js-tab').eq(0).addClass('active');
		$('.js-product-info').find('.js-tab-content').hide().eq(0).show().addClass('active');
}

function lazyLoad() {
	$('[data-src]').lazy({
        threshold: 400,
        visibleOnly: false
    });
}

$(document).on('ready', function() {
	lazyLoad();
	tabsOpen();
//	initCustomSelect($('select'));
	placeholder($('input[placeholder], textarea[placeholder]'));
	mobiheader();
	priceslider();
	recomSlider();
	submenuPromoSlider();
	popupDrop();
	openSearch();
	callCart();
	mainSlider();
	spinner();
	simpleSlider();
	sliderBrands();
	order();
	drop();
	dropSelect();
	hideFilterBlocks();
	previewImg();	
	previewSlider();
	filterResp();
	productsView();
	simplePopup();
	openText();
	textHeight();
	promoItem();
	fixedHeader();
	categoriesAnimate();
	fancyImg();
	cloudZoom();
	CategoriesMin();
	tabsMob();
	mainMenu();
	navigationResize();
	preloader();
	fancyPopUp();
	fancyFastCart();
	initCustomScroll();
	alertClose();
//	ocReview();
	ocAutocomplete();
	ocSearchAutocomplete();
	ocDateTimepicker();
	ocProduct();
	ocAddCoupon();
	ocAddVoucher();
	ocAddReward();
	ocCartShipping();
	ocAgree();
	ocTrackingAutocomplete();
	ocFilter();
	other();
	sliderBtnHover();
	addSubscribe();
	comparisonScroll();
	comparisonCell();
	shareBtn();
	recomSlider2();
	scrollToTop();
	lightshopSet();
	svgFix();
	chats();
});

$(window).on('resize', function(){
	mainMenu();
	navigationResize();
	initCustomScroll();	
	mobiheader();
	fixedHeader();
	scrollToTop();
	cloudZoom();
});

$(window).load(function() {
	mainMenu();
	navigationResize();
	bcSlider();
});
