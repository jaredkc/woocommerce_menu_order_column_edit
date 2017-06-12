jQuery(function($){

	$('.jc-menu-order').click(function(e){
		$(this).select().parent().addClass('jc-active');
	});

	$('.jc-menu-order-update').click(function(e){
		e.preventDefault;
		var $this = $(this);
		var productID = $this.parent().find('.jc-menu-order').data('product');
		var menuOrder = $this.parent().find('.jc-menu-order').val();
		var ajaxData = {
			'product_id': productID,
			'menu_order': menuOrder,
			'action': 'jc_update_menu_order'
		};

		$this.text('Updating...');

		$.ajax({
			type: 'POST',
			url: jc_update_menu_order.ajax_url,
			data: ajaxData,
			success: function(data) {
				$this.text('Update').parent().removeClass('jc-active');
			},
			error: function(errorThrown){
				$this.text('ERROR!');
				console.log(errorThrown);
			}
		});

	});

});
