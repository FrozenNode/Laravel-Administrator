(function($)
{
	switch (tooltip)
	{
		case "administrator":
			$(document).tooltip({
				track: true
			});
		break;
		/*
		 * Add your own tooltip property case here!
		 *
		 */
		default:
		case "default":
			$(document).tooltip();
		break;
	}
})(jQuery);
