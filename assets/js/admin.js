jQuery(function ($) {
	// Repeatable rows (Specifications, Support Documents).
	$(document).on('click', '.cpc-add-row', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.cpc-repeater');
		var next = parseInt($wrap.attr('data-next'), 10) || 0;
		var tpl = $wrap.find('script.cpc-row-template').html();
		$wrap.find('.cpc-rows').append(tpl.replace(/__INDEX__/g, next));
		$wrap.attr('data-next', next + 1);
	});
	$(document).on('click', '.cpc-remove-row', function (e) {
		e.preventDefault();
		$(this).closest('.cpc-row').remove();
	});

	// Media library pickers.
	function openFrame(options, onSelect) {
		if (typeof wp === 'undefined' || !wp.media) {
			return;
		}
		var frame = wp.media(options);
		frame.on('select', function () {
			onSelect(frame.state().get('selection').first().toJSON());
		});
		frame.open();
	}

	$(document).on('click', '.cpc-image-select', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.cpc-image-picker');
		openFrame({ title: 'Select image', library: { type: 'image' }, multiple: false }, function (a) {
			var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
			$wrap.find('.cpc-image-picker__input').val(a.id);
			$wrap.find('.cpc-image-picker__preview').empty().append($('<img>').attr('src', url));
			$wrap.find('.cpc-image-remove').show();
		});
	});

	$(document).on('click', '.cpc-image-remove', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.cpc-image-picker');
		$wrap.find('.cpc-image-picker__input').val('');
		$wrap.find('.cpc-image-picker__preview').empty();
		$(this).hide();
	});

	$(document).on('click', '.cpc-url-select', function (e) {
		e.preventDefault();
		var $input = $(this).closest('.cpc-row').find('.cpc-url-input');
		openFrame({ title: 'Select file', multiple: false }, function (a) {
			$input.val(a.url);
		});
	});

	// Clear the icon picker after a category is added through the AJAX form.
	$(document).ajaxComplete(function (event, xhr, settings) {
		if (settings && typeof settings.data === 'string' && settings.data.indexOf('action=add-tag') > -1 && settings.data.indexOf('taxonomy=product_cat') > -1) {
			var $wrap = $('#addtag .cpc-image-picker');
			$wrap.find('.cpc-image-picker__input').val('');
			$wrap.find('.cpc-image-picker__preview').empty();
			$wrap.find('.cpc-image-remove').hide();
		}
	});
});
