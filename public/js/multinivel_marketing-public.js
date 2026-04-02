(function( $ ) {
	'use strict';

	$(function() {
		// Gatilho para atualizar o checkout quando a categoria do usuário for alterada
		$(document.body).on('change', 'input[name="billing_usuario"]', function() {
			$(document.body).trigger('update_checkout');
		});
	});

})( jQuery );
