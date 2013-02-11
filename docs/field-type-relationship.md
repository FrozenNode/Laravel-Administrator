# Field Type - Relationship

- [Overview](#overview)
- [Belongs To](#belongs-to)
- [Belongs To Filter](#belongs-to-filter)
- [Has Many And Belongs To](#has-many-and-belongs-to)
- [Has Many And Belongs To Filter](#has-many-and-belongs-to-filter)
- [Large Datasets and Autocomplete](#large-datasets-and-autocomplete)
- [Constraining Relationships](#constraining-relationships)

<a name="overview"></a>
## Overview

Relationship field types allow you to manage the `belongs_to` and `has_many_and_belongs_to` relationships on your model. Unlike regular field types, the *key* of the relationship field type should be the *name of the relationship method*.

<a name="belongs-to"></a>
## Belongs To

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-single.png" />

	'user' => array(
		'type' => 'relationship',
		'title' => 'User',
		'name_field' => 'name', //what column or getter on the other table you want to use to represent this object
	)

The `name_field` option lets you define which column on the other table will be used to represent the relationship. This field might be used in this model:

	class Hat extends Eloquent {

		public function user()
		{
			return $this->belongs_to('User');
		}
	}

<a name="belongs-to-filter"></a>
## Belongs To Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-single-filter.png" />

The `belongs_to` filter lets you filter a result set for items that are related to the selection you make.

<a name="has-many-and-belongs-to"></a>
## Has Many And Belongs To

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-multi.png" />

	'actors' => array(
		'type' => 'relationship',
		'title' => 'Actors',
		'name_field' => 'full_name', //using the get_full_name getter
	)

In this case, the `name_field` supplied is a getter on the `User` model that combines the `first_name` field and the `last_name` field. This field might be used in this model

	class Film extends Eloquent {

		public function actors()
		{
			return $this->has_many_and_belongs_to('Actor', 'films_actors');
		}
	}

With this setup, the user will be presented with a multi-select field to choose all of the actors in the film.

<a name="has-many-and-belongs-to-filter"></a>
## Has Many And Belongs To Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-relation-multi-filter.png" />

The `has_many_and_belongs_to` filter lets you filter a result set for items that are related to the selection you make. This is an inclusive filter, not a progressively exclusive filter.

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

<a name="constraining-relationships"></a>
## Constraining Relationships

Occasionally you might be in a situation where you have two or more relationship fields in a model. These two fields might themselves be related on a pivot table. In this case, it's sometimes useful to be able to constrain the options of one of these fields based on the selected option of the other. An example might help clear things up...

Let's pretend that we have two models: a `Film` model and a `Theater` model. There can be many films in a theater and each film can be in many theaters. This is a standard `has_many_and_belongs_to` relationship. The `Film` model would look like this:

	class Film extends Eloquent {

		public function theaters()
		{
			return $this->has_many_and_belongs_to('Theater', 'films_theaters');
		}
	}

And the `Theater` model would look like this:

	class Theater extends Eloquent {

		public function films()
		{
			return $this->has_many_and_belongs_to('Film', 'films_theaters');
		}
	}

Let's also imagine that we have another model that counts the box office takes for each film in each theater. That would look like this:

	class BoxOffice extends Eloquent {

		public function theater()
		{
			return $this->belongs_to('Theater');
		}

		public function film()
		{
			return $this->belongs_to('Film');
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
		),
		'theater' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	)

When we open up the admin screen for the `BoxOffice` model and create a new item, we will see 3 fields: the `revenue` for the box office take, a select field to choose the film, and a select field to choose the theater. But there's a problem: you can select any theater regardless of what film you've chosen, and you can select any film regardless of what theater you've chosen! This is a problem if you're trying to keep your data consistent. If you select the `Cineplex 5` theater, you want your admin users to only be able to select the films that have been in the `Cineplex 5`. If you select `The Matrix`, you want your admin users to only be able to select the theaters that `The Matrix` has played in.

Fortunately we can use `constraints` to make two relationship fields dependent on one another. Using constraints, this is what our `edit_fields` array would look like:

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
			'constraints' => array('theater' => 'films')
		),
		'theater' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
			'constraints' => array('film' => 'theaters')
		),
	)

A relationship field's `constraints` should be an array of items where the index is the other relationship *on this model* to which you want to constrain this relationship field.

For the `film` field, the array key is `theater`, which is the relationship method name on the `BoxOffice` model. The array value is `films` which is this field's relationship method name on the `Theater` model.

For the `theater` field, the array key is `film`, which is the relationship method name on the `BoxOffice` model. The array value is `theaters` which is this field's relationship method name on the `Film` model.