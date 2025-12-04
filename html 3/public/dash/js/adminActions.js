$(document).ready(function() {
	$.post('/admin/getMerchant', function(data) {
		$('#fkBal').text(data);
	});
	$(document).on('click', '.versionUpdate', function () {
		$.post('/admin/versionUpdate')
		.then(e => {
			if(e.success) {
				$.notify({
	                type: 'success',
	                message: e.msg
	            });
			}
		})
		.fail(() => {
			$.notify({
	            type: 'error',
	            message: 'Ошибка на стороне сервера'
	        });
		})
	})
	$(document).on('click', '.slotsUpdate', function () {
		$.notify({
			type: 'success',
			message: 'Подождите минутку!'
		});

		$.post('/admin/slotsUpdate')
		.then(e => {
			if(e.success) {
				$.notify({
	                type: 'success',
	                message: e.msg
	            });
			}
		})
		.fail(() => {
			$.notify({
	            type: 'error',
	            message: 'Ошибка на стороне сервера'
	        });
		})
	})
});