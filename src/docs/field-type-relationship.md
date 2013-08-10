# Field Type - Relationship

- [Overview](#overview)
- [Belongs To](#belongs-to)
- [Belongs To Filter](#belongs-to-filter)
- [Belongs To Many](#belongs-to-many)
- [Belongs To Many Filter](#belongs-to-many-filter)
- [Large Datasets and Autocomplete](#large-datasets-and-autocomplete)
- [Filtering Relationship Options](#filtering-relationship-options)
- [Constraining Relationships](#constraining-relationships)

<a name="overview"></a>
## Overview

Relationship field types allow you to manage the `belongsTo` and `belongsToMany` relationships on your model. Unlike regular field types, the *key* of the relationship field type should be the *name of the relationship method*.

<a name="belongs-to"></a>
## Belongs To

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-single.png" />

	'user' => array(
		'type' => 'relationship',
		'title' => 'User',
		'name_field' => 'name', //what column or accessor on the other table you want to use to represent this object
	)

The `name_field` option lets you define which column or accessor on the other table will be used to represent the relationship. This field might be used in this model:

	class Hat extends Eloquent {

		public function user()
		{
			return $this->belongsTo('User');
		}
	}

<a name="belongs-to-filter"></a>
## Belongs To Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-single-filter.png" />

The `belongsTo` filter lets you filter a result set for items that are related to the selection you make.

<a name="belongs-to-many"></a>
## Belongs To Many

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-multi.png" />

	'actors' => array(
		'type' => 'relationship',
		'title' => 'Actors',
		'name_field' => 'full_name', //using the getFullNameAttribute accessor
		'options_sort_field' => "CONCAT(first_name, ' ' , last_name)",
	)

In this case, the `name_field` supplied is an accessor on the `User` model that combines the `first_name` field and the `last_name` field. However, since the `name_field` is an accessor and not a column in the database, you must also specify an `options_sort_field` if you want to order the options. The `options_sort_field` isn't required, but without it the options will be ordered by ascending order on the primary key column. You can also set the `options_sort_direction` to either `asc` or `desc`.

This field might be used in this model:

	class Film extends Eloquent {

		public function actors()
		{
			return $this->belongsToMany('Actor', 'films_actors');
		}
	}

With this setup, the user will be presented with a multi-select field to choose all of the actors in the film.

If you want to let your admin users reorder the selected values, you can create an integer-based sorting column on your pivot table and then specify that column as an option in the field. In our example above, we may wish to reorder the actors arbitrarily by dragging and dropping them in the UI. In order to do this, you would need to add an integer field (let's call it `ordering`) to the `films_actors` table. Then in your model config, you would provide that column name in the `sort_field` option:

	'actors' => array(
		'type' => 'relationship',
		'title' => 'Actors',
		'name_field' => 'full_name', 	//using the getFullNameAttribute accessor
		'sort_field' => 'ordering', 	//this will look for a numerical column at films_actors.ordering
	)

Now the individual items in the multi-select box will be sortable via drag and drop.

<a name="belongs-to-many-filter"></a>
## Belongs To Many Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-multi-filter.png" />

The `belongsToMany` filter lets you filter a result set for items that are related to the selection you make. This is an inclusive filter, not a progressively exclusive filter.

<a name="large-datasets-and-autocomplete"></a>
## Large Datasets and Autocomplete

If your relationship field points at another model whose data set is potentially very large, loading in all of the options may not be what you want. Fortunately, you can set the `autocomplete` option.

	'actors' => array(
		'type' => 'relationship',
		'title' => 'Actors',
		'name_field' => 'full_name',
		'autocomplete' => true,
		'num_options' => 5, //default is 10
		'search_fields' => array("CONCAT(first_name, ' ', last_name)"), //default is array([name_field])
	)

A relationship field with `autocomplete` set to `true` will wait for a user to type in a value.

Once a value is typed in, the `num_options` option determines how many results are returned to the user for each search.

The `search_fields` option should be an array of valid SQL select fields that can be searched with the `LIKE` operator. In the above example, the admin user will be able to search for "Liam N" and get "Liam Neeson" back. The default value for this field is just the name_field supplied in all relationship fields.

<a name="filtering-relationship-options"></a>
## Filtering Relationship Options

In some instances you may want to limit the available options for a relationship. This is easy to do with the `options_filter` option:

	'actors' => array(
		'type' => 'relationship',
		'title' => 'Actors',
		'name_field' => 'full_name',
		'options_filter' => function($query)
		{
			$query->whereNull('died_at'); //only returns living actors
		},
	)

The `options_filter` is passed the query builder instance so that you can modify the query however you like.

<a name="constraining-relationships"></a>
## Constraining Relationships

Occasionally you might be in a situation where you have two or more relationship fields in a model. These two fields might themselves be related via a `hasOne`, `hasMany`, or `belongsToMany` relationship. In this case, it's sometimes useful to be able to constrain the options of one of these fields based on the selected option of the other. An example might help clear things up...

### Has One or Has Many

Let's say we have a `Theater` model and we want to let our admin users select which country and state a theater is in. We would also have a `Country` model and a `State` model. A state belongs to a country, and a country has many states. The `Theater` model belongs to both the `Country` and `State` models. When a user selects a particular country, the available states should be limited by those in that country.

So our `Theater` model would look like this:

	class Theater extends Eloquent {

		public function country()
		{
			return $this->belongsTo('Country');
		}

		public function state()
		{
			return $this->belongsTo('State');
		}
	}


Our `Country` model would look like this:

	class Country extends Eloquent {

		public function states()
		{
			return $this->hasMany('State');
		}
	}

And the `State` model would look like this:

	class State extends Eloquent {

		public function country()
		{
			return $this->belongsTo('Country');
		}
	}

Now when we create the `Theater` [model config](/docs/model-configuration), we'd set up the [edit fields](/docs/fields) to something like this:

	'edit_fields' => array
	(
		'country' => array(
			'title' => 'Country',
			'type' => 'relationship',
			'name_field' => 'name',
		),
		'state' => array(
			'title' => 'State',
			'type' => 'relationship',
			'name_field' => 'name',
			'constraints' => array('country' => 'states') //this is the important bit!
		),
	)

The constraint we've set on the `state` field takes a key of the relationship name on the `Theater` model (i.e. the other field's name) and a value of the `states` relationship method name on the `Country` model. Now when the user selects a country, it will automatically limit the available states to those in that particular country.

### Belongs To Many

We can do the same sort of thing for two fields that are connected by a `belongsToMany` relationship. Let's pretend that we have two models: a `Film` model and a `Theater` model. There can be many films in a theater and each film can be in many theaters. This is a standard `belongsToMany` relationship. The `Film` model would look like this:

	class Film extends Eloquent {

		public function theaters()
		{
			return $this->belongsToMany('Theater', 'films_theaters');
		}
	}

And the `Theater` model would look like this:

	class Theater extends Eloquent {

		public function films()
		{
			return $this->belongsToMany('Film', 'films_theaters');
		}
	}

Let's also imagine that we have another model that counts the box office takes for each film in each theater. That would look like this:

	class BoxOffice extends Eloquent {

		public function theater()
		{
			return $this->belongsTo('Theater');
		}

		public function film()
		{
			return $this->belongsTo('Film');
		}
	}

Now when we create the `BoxOffice` [model config](/docs/model-configuration), we'd set up the [edit fields](/docs/fields) to something like this:

	'edit_fields' => array
	(
		'revenue' => array(
			'title' => 'Revenue',
			'type' => 'number',
			'symbol' => '$',
			'decimals' => 2,
		),
		'film' => array(
			'title' => 'Film',
			'type' => 'relationship',
			'name_field' => 'name',
			'constraints' => array('theater' => 'films') //films matches the relationship method name on the Theater model
		),
		'theater' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
			'constraints' => array('film' => 'theaters') //theaters matches the relationship method name on the Film model
		),
	)

So now when you select a particular film, it will limit the available theaters by those that have played that film. When you select a particular theater, it will only give the the ability to choose a film that's been in that theater.