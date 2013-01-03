(function($){

	/**
	 * For the item form transition
	 */
	ko.bindingHandlers.itemTransition = {
		init: function(element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var $element = $(element),
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
		update: function(element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var $element = $(element),
				$child = $element.find('.item_edit'),
				$tableContainer = $('div.table_container'),
				expandWidth = viewModel.expandWidth();

			//if the value is false, we want to hide the form, otherwise show it
			if (!valueAccessor())
			{
				$child.stop().animate({marginLeft: expandWidth + 2}, 150, function() {
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
					$tableContainer.stop().animate({marginRight: expandWidth + 5}, 150);
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
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor(),
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
						$.each(admin.filtersViewModel.filters(), function(ind, el)
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

			//if the limit is zero, there is no limit
			if (!limit)
				return;

			//if the value is null, set it to an empty string
			if (val === null)
				val = '';

			left = limit - val.length;
			text = ' character' + (left !== 1 ? 's' : '') + ' left';

			$(element).text(left + text);
		}
	};

	/**
	 * This ensures that a bool field is always a boolean value
	 */
	ko.bindingHandlers.bool = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var modelVal = viewModel[valueAccessor()]();

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
			$element.ckeditor();

			var editor = $element.ckeditorGet();

			//handle edits made in the editor
			editor.on('change', function (e)
			{
				if (ko.isWriteableObservable(this))
				{
					this($(e.listenerData).val());
				}
			}, valueAccessor(), element);

			//destroy the existing editor if the DOM node is removed
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				var existingEditor = CKEDITOR.instances[element.name];

				if (existingEditor)
					existingEditor.destroy(true);
			});
		},
		update: function (element, valueAccessor, allBindingsAccessor, context)
		{
			//handle programmatic updates to the observable
			var value = ko.utils.unwrapObservable(valueAccessor());
			$(element).html(value);
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
})(jQuery);