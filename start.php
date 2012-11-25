<?php


Autoloader::directories(array(
    Bundle::path('administrator').'Libraries',
));

Autoloader::namespaces(array(
	'Admin'   => Bundle::path('administrator'),
));



//set the config items if a user has provided an application config
foreach (Config::get('administrator::administrator', array()) as $key => $option)
{
	if (Config::has('administrator.'.$key))
	{
		Config::set('administrator::administrator.'.$key, Config::get('administrator.'.$key));
	}
}