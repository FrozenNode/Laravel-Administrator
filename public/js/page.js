(function($)
{
	var $menu, $mobileMenu, $menuButton, $filterButton, $content;

	//dom ready
	$(function()
	{
		$menu = $('ul#menu, ul#lang_menu');
		$mobileMenu = $('#mobile_menu_wrapper');
		$menuButton = $('a.menu_button');
		$filterButton = $('a.filter_button');
		$filters = $('#sidebar');
		$content = $('#content');

		//set the menu hover and hoverout states
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

		//clicking the menu button hides/shows the mobile menu
		$menuButton.click(function(e)
		{
			e.preventDefault();

			$menuButton.toggleClass('current');
			$mobileMenu.toggle();

			//if the filter is visible, hide it
			if ($filterButton.hasClass('current'))
				$filterButton.click();
		});

		//clicking the filter button hides/shows the filter
		$filterButton.click(function(e)
		{
			e.preventDefault();

			$filterButton.toggleClass('current');
			$filters.toggleClass('shown');
			$content.toggleClass('hidden');

			//if the menu is visible, hide it
			if ($menuButton.hasClass('current'))
				$menuButton.click();

			admin.resizePage();
		});

		//clicking menu items in the mobile menu hides/shows that submenu
		$mobileMenu.on('click', 'li.menu > span', function()
		{
			$(this).siblings('ul').toggle();
		});

		//set up the customscroll plugin for the mobile menu
		$mobileMenu.customscroll();
	});
})(jQuery);

//fixes the issue with media queries not firing when the user resizes the browser in another tab
(function() {
	var hidden = "hidden";

	// Standards:
	if (hidden in document)
		document.addEventListener("visibilitychange", onchange);
	else if ((hidden = "mozHidden") in document)
		document.addEventListener("mozvisibilitychange", onchange);
	else if ((hidden = "webkitHidden") in document)
		document.addEventListener("webkitvisibilitychange", onchange);
	else if ((hidden = "msHidden") in document)
		document.addEventListener("msvisibilitychange", onchange);
	// IE 9 and lower:
	else if ('onfocusin' in document)
		document.onfocusin = document.onfocusout = onchange;
	// All others:
	else
		window.onpageshow = window.onpagehide
			= window.onfocus = window.onblur = onchange;

	function onchange (evt) {
		var v = 'sg-tab-bust-visible', h = 'sg-tab-bust-hidden',
			evtMap = {
				focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h
			};

		evt = evt || window.event;
		if (evt.type in evtMap)
			document.body.className = evtMap[evt.type];
		else
			document.body.className = this[hidden] ? "sg-tab-bust-hidden" : "sg-tab-bust-visible";

		//clear out the body's class
		document.body.className = '';
	}
})();