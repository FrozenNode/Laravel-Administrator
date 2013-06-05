<?php

use Frozennode\Administrator\ModelHelper;
use Frozennode\Administrator\Fields\Field;
use Frozennode\Administrator\ModelConfig;
use Frozennode\Administrator\SettingsConfig;
use Frozennode\Administrator\Menu;

//View Composers

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$config = App::make('itemconfig');
	$model = $config->model;
	$baseUrl = URL::route('admin_dashboard');
	$route = parse_url($baseUrl);

	//get the edit fields
	$editFields = Field::getEditFields($config);

	//add the view fields
	$view->config = $config;
	$view->primaryKey = $model->getKeyName();
	$view->rows = ModelHelper::getRows($config->sort);
	$view->editFields = $editFields;
	$view->actions = $config->actions;
	$view->filters = Field::getFilters($config);
	$view->baseUrl = $baseUrl;
	$view->assetUrl = URL::to('packages/frozennode/administrator/');
	$view->route = $route['path'].'/';
	$view->model = isset($view->model) ? $view->model : false;
});

//admin settings view
View::composer('administrator::settings', function($view)
{
	$config = App::make('itemconfig');
	$baseUrl = URL::route('admin_dashboard');
	$route = parse_url($baseUrl);

	//get the edit fields
	$editFields = Field::getEditFields($config);

	//add the view fields
	$view->config = $config;
	$view->editFields = $editFields;
	$view->actions = $config->actions;
	$view->baseUrl = $baseUrl;
	$view->assetUrl = URL::to('packages/frozennode/administrator/');
	$view->route = $route['path'].'/';
});

//header view
View::composer(array('administrator::partials.header', 'administrator::dashboard'), function($view)
{
	$view->menu = Menu::getMenu();
	$view->settingsPrefix = SettingsConfig::$prefix;
});