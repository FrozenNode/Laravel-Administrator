<?php
namespace Frozennode\Administrator;

use Illuminate\Support\Facades\Config;

class Menu {

	/**
	 * Gets the menu items indexed by their name with a value of the title
	 *
	 * @param array		$configMenu (used for recursion)
	 *
	 * @return array
	 */
	public static function getMenu($configMenu = null)
	{
		$menu = array();

		if (!$configMenu)
		{
			$configMenu = Config::get('administrator::administrator.menu', null);
		}

		//iterate over the menu to build the return array of valid menu items
		foreach ($configMenu as $key => $item)
		{
			//if the item is a string, find its config
			if (is_string($item))
			{
				//if the prefix is 'settings.', it's a settings config
				$isSettings = strpos($item, 'settings.') !== false;

				//fetch the appropriate config file
				$config = $isSettings ? SettingsConfig::find($item) : ModelConfig::find($item);

				//if a config object was returned and if the permission passes, add the item to the menu
				if ($config)
				{
					$permission = array_get($config, 'permission');

					if (is_callable($permission) && !$permission())
					{
						continue;
					}

					$menu[$item] = array_get($config, 'title', $item);
				}
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$menu[$key] = static::getMenu($item);
			}
		}

		return $menu;
	}
}