(function($){

	/**
	 * For the item form transition
	 */
	ko.bindingHandlers.itemTransition = {
		init: function(element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var $element = $(element),
				viewModel = context.$root,
				$child = $element.find('.item_edit'),
				$tableContainer = $('div.table_container'),
				expandWidth = viewModel.expandWidth();

			//the lastItem gets reset to null when the form is closed. This way we can draw the table properly initially
			//so that it doesn't keep reopening.
			if (viewModel.lastItem === null)
			{
				$tableContainer.css('margin-right', 290);
				$element.hide();
				$child.css('marginLeft', expandWidth + 2);
			}
			else
			{
				$tableContainer.css('margin-right', expandWidth + 5);
				$child.css('marginLeft', 2);
			}
		},
		update: function(element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var $element = $(element),
				viewModel = context.$root,
				$child = $element.find('.item_edit'),
				$tableContainer = $('div.table_container'),
				expandWidth = viewModel.expandWidth();

			//if the value is false, we want to hide the form, otherwise show it
			if (!valueAccessor())
			{
				$child.stop().animate({marginLeft: expandWidth + 2}, 150, function()
				{
					$element.hide();
				});

				$tableContainer.stop().animate({marginRight: 290}, 150);
			}
			else
			{
				if (viewModel.lastItem === null)
				{
					$element.show();
					$child.stop().animate({marginLeft: 2}, 150);
					$tableContainer.stop().animate({marginRight: expandWidth + 5}, 150, function()
					{
						window.admin.resizePage();
					});
				}
			}
		}
	};

	//for chosen js
	ko.bindingHandlers.chosen = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			$(element).chosen();

			setTimeout(function() {$(element).trigger("liszt:updated")}, 50);
		}
	};

	//for ajax chosen js
	ko.bindingHandlers.ajaxChosen = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var options = valueAccessor(),
				viewModel = context.$root,
				data = {
					constraints: {},
					field: options.field,
					type: options.type
				};

			//figure out if there are any constraints that we need to send over
			$(options.constraints).each(function(ind, el)
			{
				data.constraints[ind] = viewModel[ind]();
			});

			$(element).ajaxChosen({
				minTermLength: 1,
				afterTypeDelay: 50,
				data: data,
				type: 'POST',
				url: base_url + adminData.model_name + '/update_options/',
				dataType: 'json',
				fillData: function()
				{
					var data = {};

					//if this is a filter, go through the filters until this one is found and update the value
					if (options.type === 'filter')
					{
						$.each(admin.filtersViewModel.filters, function(ind, el)
						{
							if (el.field === options.field && el.value())
							{
								data.selectedItems = el.value();
							}
						});
					}
					else
					{
						if (admin.viewModel[options.field]())
						{
							data.selectedItems = admin.viewModel[options.field]();
						}
					}

					return data;
				}
			}, function(data, term, select)
			{
				var $chosen = select.next(),
					$single = $chosen.find('div.chzn-search input'),
					$multi = $chosen.find('ul.chzn-choices'),
					$multiInput = $multi.find('li.search-field input'),
					singleVal = $single.val(),
					multiVal = $multiInput.val();

				if (options.type === 'filter')
				{
					admin.filtersViewModel.listOptions[options.field](data);
				}
				else
				{
					admin.viewModel.listOptions[options.field](data);
				}

				setTimeout(function()
				{
					//reset the search and focus
					if ($single.length)
					{
						$single.val(singleVal);
						$single.focus();
					}
					else
					{
						$multiInput.val(multiVal);
						$multiInput.focus();
					}
				}, 50);

				return false;
			});

			setTimeout(function() {$(element).trigger("liszt:updated")}, 50);
		}
	};

	/**
	 * The number binding ensures that a value is decimal-like
	 */
	ko.bindingHandlers.number = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor(),
				value = allBindingsAccessor().value(),
				floatVal;

			//if this is a null or false value, run a parseFloat on it so we can check for isNaN later
			if (value === null || value === false)
			{
				floatVal = parseFloat(value);
			}
			//else we will try to parse the number using the user-supplied thousands and decimal separators
			else
			{
				floatVal = value.toString().split(options.thousandsSeparator).join('').split(options.decimalSeparator).join('.');
			}

			//if the value is not a number, set the value equal to ''
			if (isNaN(floatVal))
			{
				if (value !== '')
				{
					$(element).val('');
				}
			}
			//else set up the value up using the accounting library with the user-supplied separators
			else
			{
				$(element).val(accounting.formatMoney(floatVal, "", options.decimals, options.thousandsSeparator, options.decimalSeparator));
			}
		}
	};

	/**
	 * The datepicker binding makes sure the jQuery UI datepicker is set for this item
	 */
	ko.bindingHandlers.datepicker = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor();

			$(element).datepicker({
				dateFormat: options.dateFormat
			});
		}
	};

	/**
	 * The timepicker binding makes sure the jQuery UI timepicker is set for this item
	 */
	ko.bindingHandlers.timepicker = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor();

			$(element).timepicker({
				timeFormat: options.timeFormat
			});
		}
	};

	/**
	 * The datetimepicker binding makes sure the jQuery UI datetimepicker is set for this item
	 */
	ko.bindingHandlers.datetimepicker = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor();

			$(element).datetimepicker({
				dateFormat: options.dateFormat,
				timeFormat: options.timeFormat
			});
		}
	};

	/**
	 * The characterLimit binding makes sure a text field only has so many characters
	 */
	ko.bindingHandlers.characterLimit = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var limit = valueAccessor(),
				val = allBindingsAccessor().value();

			val = val === null ? '' : val + '';

			if (!limit || val === null)
				return;

			val = val.substr(0, limit);

			$(element).val(val);
			allBindingsAccessor().value(val);
		}
	};

	/**
	 * The charactersLeft binding fills the element with (#chars allowed - #chars typed)
	 */
	ko.bindingHandlers.charactersLeft = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor(),
				limit = options.limit,
				val = options.value();

			val = val === null ? '' : val + '';

			//if the limit is zero, there is no limit
			if (!limit)
				return;

			//if the value is null, set it to an empty string
			if (val === null)
				val = '';

			left = limit - val.length;

//			text = ' character' + (left !== 1 ? 's' : '') + ' left';
			text = (left !== 1 ? adminData.languages['characters_left'] : adminData.languages['character_left']);

			$(element).text(left + text);
		}
	};

	/**
	 * This ensures that a bool field is always a boolean value
	 */
	ko.bindingHandlers.bool = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var viewModel = context.$root,
				modelVal = viewModel[valueAccessor()]();

			if (modelVal === '0')
				viewModel[valueAccessor()](false);
			else if (modelVal === '1')
				viewModel[valueAccessor()](true);
		}
	};

	/**
	 * The wysiwyg binding makes the field a ckeditor wysiwyg
	 */
	ko.bindingHandlers.wysiwyg = {
		init: function (element, valueAccessor, allBindingsAccessor, context)
		{
			var value = ko.utils.unwrapObservable(valueAccessor()),
				$element = $(element);

			$element.html(value);
			$element.ckeditor({ language : language });

			var editor = $element.ckeditorGet();

			//destroy the existing editor if the DOM node is removed
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				var existingEditor = CKEDITOR.instances[element.name];

				if (existingEditor)
					existingEditor.destroy(true);
			});

			//wire up the blur event to ensure our observable is properly updated
			editor.focusManager.blur = function()
			{
				var observable = valueAccessor();

				observable($element.val());
			}

			editor.setData(value);
		},
		update: function (element, valueAccessor, allBindingsAccessor, context)
		{
			//handle programmatic updates to the observable
			var value = ko.utils.unwrapObservable(valueAccessor()),
				$element = $(element),
				editor = $element.ckeditorGet();

			$element.html(value);
			editor.setData(value);
		}
	};

	/**
	 * The markdown binding is attached to the field next a markdown textarea
	 */
	 ko.bindingHandlers.markdown = {
	 	update: function (element, valueAccessor, allBindingsAccessor, context)
		{
			//handle programmatic updates to the observable
			var value = ko.utils.unwrapObservable(valueAccessor());

			if (!value)
			{
				$(element).html(value);
			}
			else
			{
				$(element).html(markdown.toHTML(value.toString()));
			}
		}
	 };


	/**
	 * File uploader using plupload
	 */
	ko.bindingHandlers.imageupload = {
		init: function(element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var options = valueAccessor(),
				cacheName = options.field + '_uploader',
				viewModel = context.$root;

			viewModel[cacheName] = new plupload.Uploader({
				runtimes: 'html5,flash,silverlight,gears,browserplus',
				browse_button: cacheName,
				container: 'edit_field_' + options.field,
				drop_element: cacheName,
				multi_selection: false,
				max_file_size: options.size_limit + 'mb',
				url: options.upload_url,
				flash_swf_url: asset_url + 'js/plupload/js/plupload.flash.swf',
				silverlight_xap_url: asset_url + 'js/plupload/js/plupload.silverlight.xap',
				filters: [
					{title: 'Image files', extensions: 'jpg,jpeg,gif,png'}
				]
			});

			viewModel[cacheName].init();

			viewModel[cacheName].bind('FilesAdded', function(up, files) {

				$(files).each(function(i, file) {
					//parent.uploader.removeFile(file);

				});

				options.upload_percentage(0);
				options.uploading(true);

				viewModel[cacheName].start();
			});

			viewModel[cacheName].bind('UploadProgress', function(up, file) {
				options.upload_percentage(file.percent);
			});

			viewModel[cacheName].bind('Error', function(up, err) {
				alert(err.message);
			});

			viewModel[cacheName].bind('FileUploaded', function(up, file, response) {
				var data = JSON.parse(response.response);

				options.uploading(false);

				if (!data.errors.length) {
					//success
					//iterate over the images until we find it and then set the proper fields
					viewModel[options.field](data.filename);

					setTimeout(function()
					{
						viewModel[cacheName].splice();
						viewModel[cacheName].refresh();
						$('div.plupload').css('z-index', 71);
					}, 200);
				} else {
					//error
					alert('ERRRORRRRR');
				}
			});

			$('#' + cacheName).bind('dragenter', function(e)
			{
				$(this).addClass('drag');
			});

			$('#' + cacheName).bind('dragleave drop', function(e)
			{
				$(this).removeClass('drag');
			});

			//destroy the existing editor if the DOM node is removed
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				viewModel[cacheName].destroy();
			});
		},
		update: function(element, valueAccessor, allBindingsAccessor, viewModel, context)
		{
			var options = valueAccessor(),
				cacheName = options.field + '_uploader',
				viewModel = context.$root;

			//hack to get the z-index properly set up
			setTimeout(function()
			{
				viewModel[cacheName].refresh();
				$('div.plupload').css('z-index', 71);
			}, 200);
		}
	}

})(jQuery);