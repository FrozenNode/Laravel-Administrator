(function($){

	/**
	 * For the item form transition
	 */
	ko.bindingHandlers.itemTransition = {
		update: function(element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var $element = $(element),
				$child = $element.find('.item_edit');

			//if the value is false, we want to hide the form, otherwise show it
			if (!valueAccessor())
			{
				$child.stop().animate({marginLeft: 288}, 150, function() {
					$element.hide();
				});
			}
			else
			{
				viewModel.editFormClosed = true;
				$element.show();
				$child.stop().animate({marginLeft: 2}, 150);
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

	/**
	 * The number binding ensures that a value is decimal-like
	 */
	ko.bindingHandlers.number = {
		update: function (element, valueAccessor, allBindingsAccessor, viewModel)
		{
			var options = valueAccessor(),
				value = allBindingsAccessor().value(),
				floatVal = parseFloat(value);

			//if the value is not a number, set the value equal to 0.00
			if (isNaN(floatVal))
			{
				if (value !== '')
				{
					$(element).val('');
				}
			}
			else
			{
				$(element).val(floatVal.toFixed(parseInt(options.decimals)));
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
})(jQuery);