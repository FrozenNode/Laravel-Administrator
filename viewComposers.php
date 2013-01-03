<?php

use Admin\Libraries\ModelHelper;
use Admin\Libraries\Fields\Field;
use Admin\Libraries\Column;
use Admin\Libraries\Sort;

//View Composers

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$modelInstance = ModelHelper::getModel($view->modelName);
	$baseUrl = URL::to_route('admin_index');
	$route = parse_url($baseUrl);

	$columns = Column::getColumns($modelInstance);
	$editFields = Field::getEditFields($modelInstance);

	//add the view fields
	$view->modelTitle = Config::get('administrator::administrator.models.'.$view->modelName.'.title', $view->modelName);
	$view->modelSingle = Config::get('administrator::administrator.models.'.$view->modelName.'.single', $view->modelTitle);
	$view->columns = $columns['columns'];
	$view->includedColumns = $columns['includedColumns'];
	$view->primaryKey = $modelInstance::$key;
	$view->sort = Sort::get($modelInstance)->toArray();
	$view->rows = ModelHelper::getRows($modelInstance, $view->sort);
	$view->editFields = $editFields['arrayFields'];
	$view->dataModel = $editFields['dataModel'];
	$view->filters = Field::getFilters($modelInstance);
	$view->baseUrl = $baseUrl;
	$view->route = $route['path'].'/';
	$view->expandWidth = ModelHelper::getExpandWidth($modelInstance);
	$view->modelInstance = $modelInstance;
	$view->model = isset($view->model) ? $view->model : false;

});