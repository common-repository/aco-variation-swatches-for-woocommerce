jQuery(document).ready( function ($) {

	function acovswTrigger() {
		if ( $('body').find('.acovswTriggerSelect').length ) { 
			$('.acovswTriggerSelect').each(function () { 
				// let form = jQuery(this).parents('form:first');
				// let acovswVaraitionData = form.data('product_variations');
				let dis = $(this);
				let selectedVal = dis.find("select option:selected").val();
				if ( selectedVal ) {
					dis.parent().find('.acovsw-attribute-style li[data-attr_value='+selectedVal+']').addClass('acovsw-selected');
				}
				dis.parent().find('.acovsw-attribute-style li').on('click', function (e) { 
					e.preventDefault();
					e.stopPropagation();
					if ( !$(this).hasClass('acovsw-disabled') ) { 
						$(this).parent('ul').find('li').removeClass('acovsw-selected');
						$(this).addClass('acovsw-selected');
						let val = $(this).data('attr_value');
						// let index = dis.find(".acovsw-attribute-select option[value='"+val+"']").index();
						// dis.find('.acovsw-attribute-select>option:eq('+index+')').prop('selected', 'selected');
						dis.find('select').val(val).trigger('change');
					}
				});
			});
		}
	}

	$(document).on('woocommerce_update_variation_values','.variations_form', function(e){ 

		var form = $(this);
        var variationAttributes = form.find('[data-attribute_name]'); 

		acovswTrigger();

		if (variationAttributes ) { 
			variationAttributes.each (function(){ 
				let options = $(this).find('option.enabled'); 
				let enabledOptions = [];
				let attrName = $(this).attr('name'); 

				options.each(function(){
					enabledOptions.push($(this).val());
				});

				$('.acovswTriggerSelect').each(function () {
					let dis = $(this); 
					let val = 'attribute_' + dis.parent().find('.acovsw-attribute-style').attr('data-attribute').toLowerCase();
					if ( attrName == val ) {
						dis.parent().find('.acovsw-attribute-style li').each(function () {
							let opt = $(this).attr('data-attr_value');
							if ($.inArray( opt, enabledOptions ) < 0 ) {
								$(this).addClass('acovsw-disabled');
								$(this).removeClass('acovsw-selected');
							} else {
								$(this).removeClass('acovsw-disabled');
							}
						});
					}
				});

			});
		}
		
	});

	$(document).on('click','.reset_variations', function(e) {

		$('.acovsw-attribute-style li').removeClass('acovsw-selected');
		$('.acovsw-attribute-style li').removeClass('acovsw-disabled');
		$('.acovsw-attribute-style li').removeClass('acovsw-outofstock');
		$('.acovsw-attribute-style li input[type="radio"]').prop('checked', false);
	
	});

});
