<?php

/*
|--------------------------------------------------------------------------
| Map constant configuration
|--------------------------------------------------------------------------
*/
Event::listen('orchestra.started', function()
{
	Config::set('administrator::administrator.login_path', handles('orchestra::login'));
	Config::set('administrator::administrator.redirect', 'redirect');
});

/*
|--------------------------------------------------------------------------
| Map Configuration to use database
|--------------------------------------------------------------------------
*/

Orchestra\Extension\Config::map('administrator', array(
	'title'           => 'administrator::administrator.title',
	'global_per_page' => 'administrator::administrator.global_per_page',
));

/*
|--------------------------------------------------------------------------
| Edit Extension `administrator`
|--------------------------------------------------------------------------
*/

Event::listen('orchestra.form: extension.administrator', function ($config, $form)
{
	$form->extend(function ($form)
	{
		$form->fieldset('Configuration', function ($fieldset)
		{
			$fieldset->control('input:text', 'Title', 'title');
			$fieldset->control('input:number', 'Global Per Page', 'global_per_page');
		});
	});
});