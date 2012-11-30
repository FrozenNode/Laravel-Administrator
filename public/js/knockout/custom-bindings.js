(function($){

	/**
	 * For the item form transition
	 */
	ko.bindingHandlers.itemTransition = {
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
				viewModel.editFormClosed = true;
				$element.show();
				$child.stop().animate({marginLeft: 2}, 150);
				$tableContainer.stop().animate({marginRight: expandWidth + 5}, 150);
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
			var options = valueAccessor();

			$(element).ajaxChosen({
				minTermLength: 1,
				afterTypeDelay: 50,
				type: 'GET',
				url: base_url + 'search_relation/' + adminData.model_name + '/' + options.field + '/' + options.type,
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
				floatVal = value === null || value === false ? parseFloat(value) : parseFloat(value.split(',').join(''));

			//if the value is not a number, set the value equal to ''
			if (isNaN(floatVal))
			{
				if (value !== '')
				{
					$(element).val('');
				}
			}
			else
			{
				$(element).val(accounting.formatMoney(floatVal, "", 2, ",", "."));
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
	 * The wysiwyg binding makes the field a redactor wysiwyg
	 */
	ko.bindingHandlers.wysiwyg = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{

		}
	};
})(jQuery);