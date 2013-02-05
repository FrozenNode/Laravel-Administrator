<?php

use Admin\Libraries\ModelHelper;
use Admin\Libraries\Fields\Field;
use Admin\Libraries\ModelConfig;

//View Composers

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$config = $view->config;
	$model = $config->model;
	$baseUrl = URL::to_route('admin_index');
	$route = parse_url($baseUrl);

	//get the edit fields
	$editFields = Field::getEditFields($config);

	//add the view fields
	$view->primaryKey = $model::$key;
	$view->rows = ModelHelper::getRows($config, $config->sort);
	$view->editFields = $editFields;
	$view->actions = $config->actions;
	$view->filters = Field::getFilters($config);
	$view->baseUrl = $baseUrl;
	$view->assetUrl = URL::to('bundles/administrator/');
	$view->route = $route['path'].'/';
	$view->model = isset($view->model) ? $view->model : false;
});

//header view
View::composer(array('administrator::partials.header', 'administrator::dashboard'), function($view)
{
	$view->title = ModelConfig::getMainTitle();
	$view->menu = ModelConfig::getMenu();
});