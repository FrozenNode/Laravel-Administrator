<?php
namespace Frozennode\Administrator;

use Illuminate\Config\Repository AS Config;
use Frozennode\Administrator\Config\Factory AS ConfigFactory;

class Menu {

	/**
	 * The config instance
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * The config instance
	 *
	 * @var Frozennode\Administrator\Config\Factory
	 */
	protected $configFactory;

	/**
	 * Create a new Menu instance
	 *
	 * @param Illuminate\Config\Repository				$config
	 * @param Frozennode\Administrator\Config\Factory	$config
	 */
	public function __construct(Config $config, ConfigFactory $configFactory)
	{
		$this->config = $config;
		$this->configFactory = $configFactory;
	}

	/**
	 * Gets the menu items indexed by their name with a value of the title
	 *
	 * @param array		$subMenu (used for recursion)
	 *
	 * @return array
	 */
	public function getMenu($subMenu = null)
	{
		$menu = array();

		if (!$subMenu)
		{
			$subMenu = $this->config->get('administrator::administrator.menu');
		}

		//iterate over the menu to build the return array of valid menu items
		foreach ($subMenu as $key => $item)
		{
			//if the item is a string, find its config
			if (is_string($item))
			{
				//if the prefix is 'settings.', it's a settings config
				$isSettings = strpos($item, 'settings.') !== false;

				//fetch the appropriate config file
				$config = $this->configFactory->make($item);

				//if a config object was returned and if the permission passes, add the item to the menu
				if ($config && $config->getPermission())
				{
					$menu[$item] = $config->getOption('title');
				}
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$menu[$key] = $this->getMenu($item);
			}
		}

		return $menu;
	}
}