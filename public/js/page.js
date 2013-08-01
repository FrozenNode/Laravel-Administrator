(function($)
{
	var $menu;

	//dom ready
	$(function()
	{
		//set the menu hover and hoverout states
		$menu = $('ul#menu, ul#lang_menu');

		$menu.find('li.menu').each(function()
		{
			var $this = $(this),
				$submenu = $this.children('ul');

			//bind events for the top-level menu item
			$this.bind({
				mouseenter: function()
				{
					clearTimeout($this.data('timer'));
					$this.addClass('current');
				},
				mouseleave: function()
				{
					$this.data('timer', setTimeout(function()
					{
						$submenu.fadeOut(150);
						$this.removeClass('current');
					}, 150));
				}
			});

			//make the submenu slide down on hover
			$this.hover(function()
			{
				//if this is a sub-submenu, slide it right instead of down
				if ($this.parent().closest('li.menu').length)
				{
					$this.addClass('current');
					$submenu.stop(true, true).show('slide', { direction: 'left' }, 200);;
				}
				else
					$submenu.stop(true, true).slideDown(200);
			});
		});
	});
})(jQuery);