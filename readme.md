# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 2.3.0

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/overview.png" />

## Inspiration / Credit

The initial inspiration for this project came from the [Lara Admin](https://github.com/chalien/lara_admin) bundle by chalien. In between then and the initial release of this bundle, pretty much the entire codebase has been changed. Still, some of the design elements of Lara Admin remain (for the time being), partially as a testament to chalien's work!

## Tutorials/Guides

I'm currently working on expanding this section. So far I've only got the one overview video. More to come soon!

###Videos

[Administrator Bundle Overview](https://vimeo.com/54058030)


## Installation / Documentation

You can either create a bundle directory called `administrator` and manually copy the bundle contents into it, or you can run the artisan command:

```php
php artisan bundle:install administrator
```

Then add this to your `bundles.php` array:

```php
'administrator' => array(
	'handles' => 'admin', //determines what URI this bundle will use
	'auto' => true,
),
```

Once the bundle is installed, create a new file in your application config called administrator.php (`application/config/administrator.php`). Then copy the contents of the bundle's config file (`administrator/config/administrator.php`) and put it into the application config file you just created.

### Config

The configuration is detailed below. The models array requires a 'title' and a 'model' key, both of which are strings, and the latter being the fully-qualified class name of your admin model. It also accepts an optional 'permission_check' property which should be a function that returns a boolean which Administrator uses to determine if the current user is allowed to access this model. This runs after the auth_check function, so you don't need to check for general authentication again in the model's permission_check. If the model can be accessed by all users who pass the auth_check, then you don't need to provide a permission_check for that model.

```php
/**
 * Page title
 *
 * @type string
 */
'title' => 'Admin',

/**
 * Models
 *
 * @type array
 *
 * Each item in the array should itself be an array with two required items inside it (title, model) and two optional items (single, permission_check).
 * The key will be what the user sees as the URI for this model.
 *
 * 'user' => array(
 * 		'title' => 'Users', //The title that will be used when displaying the model's page
 * 		'single' => 'user', //The name used for singular items. Film model would be 'film'. BoxOffice model might be 'take'
 * 		'model' => 'AdminModels\\User', //The string class name of the model you will be using. If you wish to extend your app models directly, you can just pass in 'User'. Beware, though: your model will need to have the required properties on it for Administrator to recognize it.
 *  	'permission_check' => function() { ... }, //[OPTIONAL] Return bool true if the current user is allowed to access this model. False otherwise
 * )
 */
'models' => array(
	'user' => array(
		'title' => 'Users',
		'single' => 'user',
		'model' => 'AdminModels\\User', //This is just a fully-qualified classname. Here I've namespaced my admin models to AdminModels so I can reuse the "User" classname.
	),
	'role' => array(
		'title' => 'Roles',
		'single' => 'role',
		'model' => 'AdminModels\\Role',
		'permission_check' => function()
		{
			//An example permission check using the Authority bundle:
			return Auth::user()->has_role('superadmin');
		}
	),
	'hat' => array(
		'title' => 'Hats',
		'single' => 'hat',
		'model' => 'Hat', //In this case I'm just using the un-namespaced "Hat" class/model.
	),
	'film' => array(
		'title' => 'Films',
		'single' => 'film',
		'model' => 'Film',
	),
),

/**
 * Auth Check
 *
 * @type closure
 *
 * This is a closure that should return true if the current user is allowed to view the admin section. If this fails, it will redirect the user to the login_path. This is run prior to the model's permission_check closure (if provided). Consider this a catch-all for the entire admin section.
 */
'auth_check'=> function()
{
	//An example auth check using the Authority bundle:
	return Auth::check() && Auth::user()->has_role('admin');
},

/**
 * Login Path
 *
 * @type string
 *
 * This is the path where Administrator will send the user if the the auth_check fails
 */
'login_path' => 'user/login',

/**
 * Redirect key
 *
 * @type string
 *
 * This comes with the redirection to your login_action. Input::get('redirect') will hold the return URL.
 */
'login_redirect_key' => 'redirect',

/**
 * Global items per page
 *
 * @type NULL|int
 *
 * If you set this to an integer value greater than 0, it will override the $per_page static property in ALL of your models
 * If you set this to false/NULL/0, each model's $per_page property will be used
 */
'global_per_page' => NULL,
```


### Data Models

This bundle was designed to take advantage of the data models that already exist on your site (normally in `application/models`). Administrator data models should ultimately extend from an Eloquent data model with several additional required properties ($columns, $filters, $edit ...see below for info on these). As long as you provide the fully-qualified class name in the config (see above), Administrator will be able to use the model. This means that you have several organizational options:

- Use your existing Eloquent models and add the required properties

- Create new models that extend your existing Eloquent models and prefix them with something like `Admin_` so there are no namespace collisions. This would allow you to keep your Administrator properties separated from your regular models.

- (preferred) Create new models that extend your existing Eloquent models and namespace them to something like `AdminModels`. This also would separate your Administrator properties from your base model.

The last option has (in my opinion) the best of all worlds: it would allow you to retain your clear model names, avoid namespace collisions, and keep your Administrator properties separated from your regular Eloquent models.

What I like to do is create a directory under my models directory called `admin`. Each of the models in this directory can extend any Eloquent-based class (which means Eloquent, Aware, or your base models)

#### Extending from Eloquent

```php
namespace AdminModels;

class User extends \Eloquent
{ .. }
```

#### Extending from Aware
```php
namespace AdminModels;

class User extends \Aware
{ .. }
```

#### Extending from an existing User model
```php
namespace AdminModels;

class User extends \User
{ .. }
```

If you're using the namespace approach as I did above, make sure you add that backslash in front of the class you're extending. This tells PHP to look in the base namespace.

In the first two examples, you need to set up the model as if it were any other Eloquent/Aware model. You will need to make a `$rules` array that works just like Aware's.

Now let's take a look at the properties that you can set on Administrator models and what they mean. Keep in mind that since these are Eloquent models, all of the traditional properties apply (e.g. $per_page, $table).


#### $columns

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/columns.png" />

This property tells Administrator what columns to use when displaying the tabular result set. You can either pass it a simple string which will be used as the data key (i.e. if your database column is called `potato_farming_score`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `potato_farming_score` and it would contain an array of options.

When defining relational or getter columns, you have several extra options.

If you want to make a column have the value of a getter, you can do that easily. However, if you do so without setting a valid `sort_field` value, the column won't be sortable. The sort_field should be used when you're using a getter as a column key so that Administrator knows which column to sort.

If you want to get a field from another table through a relationship, you'll have to set the `relationship` option to the *method name* of the relationship and provide a valid select statement for your SQL driver. Since the result set is grouped by the current data model's primary key, this means you can use any of the grouping functions (like COUNT, AVG, MIN, MAX, etc.).

The available options are:

- **title**: default is column name
- **sort_field**: default is the field key (i.e. if you do 'name' like below, it will look for the 'name' column). If this column is derived from a getter, it won't be sortable until you define a sort_field
- **relationship**: default is null. Set this to the method name of the relationship. Only set this if you need to pull this field from another table
- **select**: default is null. This can be used for relationship columns or on-table columns. If it's an on-table column, you can use any regular SQL select function (e.g. IF, CONCAT, etc.). If you've set the relationship, this has to be set as well. It is the SQL command to use to select this field. So if you want to count the related items, you'd do 'COUNT((:table).id)' where (:table) is substituted for the adjoining table. If you don't include the (:table), SQL will likely throw an ambiguous field error. You can use any of the SQL grouping functions or you can simply provide the name of the field you'd like to use.

```php
public $columns = array(
	'id',
	'name',
	'price',
	'formatted_salary' => array(
		'title' => 'Salary',
		'sort_field' => 'salary', //must be a valid field on the model's table
	),
	'is_good' => array(
		'title' => 'Is Good',
		'select' => "IF((:table).is_good, 'yes', 'no')", //here using a select for items on this table
	),
	'num_films' => array(
		'title' => '# films',
		'relationship' => 'films', //must be the relationship method name
		'select' => 'COUNT((:table).id)', //the (:table) is replaced with the relevant relationship table
	),
	'created_at' => array(
		'title' => 'Created', //the header title of the column
	),
	'updated_at' => array(
		'title' => 'Updated',
	),
);
```


#### $edit

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/edit-form.png" />

This property tells Administrator what columns to use when editing an item. You can either pass it a simple string which will be used as the data key (i.e. if your database column is called `name`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `name` and it would contain an array of options.

**If you want to edit a related field, you have to put the relationship method name in the $edit array and use type 'relationship'.**

The available options are:

##### Common
- **title**
- **type**: default is 'text'. Choices are: relationship, text, textarea, wysiwyg, markdown, date, time, datetime, number, bool, enum

##### Relationships
- **name_field**: default is 'name'. Only use this if type is 'relationship'. This is the field on the other table to use for displaying the name/title of the other data model.
- **autocomplete**: default is false. If this is true, the related items won't be prefilled. The user will have to start typing some values which will then be used to create suggestions. *(Set this to true if you expect any large amount of records, or it will try to load all rows!)*
- **num_options**: default is 10. If autocomplete is on, this is the number of items to show
- **search_fields**: default is array(name_field). Must be an array. You can supply an on-table column name or a raw SQL function like CONCAT(first_name, ' ', last_name)
- **constraints**: default is array(). Must be an array of string key => value pairs where the key is the relationship method on this model and the value is the method name on that relationship's model. So let's say you're setting up a film times model where each film time needs a film and a theater. When the user is creating the film time and selects a particular theater, you want to limit the film options to those films that are in that theater. When they select a film, you want to limit the theater options to those theaters that are showing that film. In this case you would put `'constraints' => array('film' => 'theaters')` in the theater relationship field, and `'constraints' => array('theater' => 'films')` in the film relationship field. This only applies when the 'films' and 'theaters' tables/models themselves have a pivot table (i.e. films_theaters).

##### Enum
- **options**: default is an empty array. This can either be an array of strings (array('Spring', 'Winter')) or an array of strings indexed on the enum value (array('Spring' => 'Beautiful Spring!', 'Winter' => 'Cold Winter! :(')). In the latter case, the key value will be used to save to / query the database.

##### Text/Textarea/Markdown
- **limit**: default is 0 (i.e. no character limit).
- **height**: default is 100. Supply any integer value which will be the height in pixels. Only applies to textarea and markdown fields.

##### Numbers
- **symbol**: default is NULL. Only use this for 'number' field type.
- **decimals**: default is 0. Only use this for 'number' field type.
- **thousands_separator**: default is ','. Only use this for 'number' field type.
- **decimal_separator**: default is '.'. Only use this for 'number' field type.

##### Date/Time
- **date_format**: default is 'yy-mm-dd'. Use this for 'date' and 'datetime' field types. Uses [jQuery datepicker formatDate](http://docs.jquery.com/UI/Datepicker/formatDate).
- **time_format**: default is 'HH:mm'. Use this for 'time' and 'datetime' field types. Uses [jQuery timepicker formatting](http://trentrichardson.com/examples/timepicker/#tp-formatting).

```php
public $edit = array(
	'email',
	'name' => array(
		'title' => 'Name',
	),
	'is_good' => array(
		'title' => 'Is Good',
		'type' => 'bool',
	),
	'season' => array(
		'title' => 'Season',
		'type' => 'enum',
		'options' => array('Winter', 'Spring', 'Summer', 'Fall'), //must be an array
	),
	'birthdate' => array(
		'title' => 'Birth Date',
		'type' => 'date',
	),
	'roles' => array(
		'title' => 'Roles',
		'type' => 'relationship',
		'name_field' => 'title', //field on other table to use for the name/title
	),
	'elements' => array(
		'title' => 'Element',
		'type' => 'relationship',
		'name_field' => 'name', //field on other table to use for the name/title
		'autocomplete' => true,
		'num_options' => 5,
		'search_fields' => array("CONCAT(first_name, ' ', last_name)"),
	),
	'price' => array(
		'title' => 'Price',
		'type' => 'number',
		'symbol' => '$', //symbol shown in front of the number
		'decimals' => 2, //the number of digits after the decimal point
		'decimalSeparator' => ',', //the character to use for the decimal place
		'thousandsSeparator' => '.', //the character to use between thousands groups
	),
	'release_date' => array(
		'title' => 'Release Date',
		'type' => 'datetime',
		'date_format' => 'yy-mm-dd',
		'time_format' => 'HH:mm',
	)

);
```

If you select the 'relationship' type, the edit form will display either a single- or multi-select box depending on the type of relationship. The Administrator bundle recognizes all types of Eloquent relationships (belongs_to, has_one, has_many, has_many_and_belongs_to), but only belongs_to and has_many_and_belongs_to relationships can be used for editing/filtering. All you have to do is ensure that the key in the $edit array is **the name of the relationship method in your data model**. If you have a User model and your user has many Roles, you'll typically want to have a method that looks like this in Eloquent:

```php
public function roles()
{
	return $this->has_many_and_belongs_to('Role', 'role_user');
}
```

Similarly, if each of your users has only a *single* Job, you'll want a method that looks like:

```php
public function job()
{
	return $this->has_one('Job');
}
```

Unless you're extending directly from Eloquent/Aware, these methods should already exist in your data models. Here's how each of those would look in your $edit property:

```php
'roles' => array(
	'title' => 'Roles',
	'type' => 'relationship',
),
'job' => array(
	'title' => 'Job',
	'type' => 'relationship',
	'name_field' => 'title', //this lets Administrator know what column to reference on the Job model. Default is 'name'
),
```

#### $filters

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/filters.png" />

This property tells Administrator what columns to use to build the filterable set. This works almost exactly like the $edit property, so you can either pass it a simple string which will be used as the data key (i.e. if your database column is called `name`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `name` and it would contain an array of options.

The date/time and number field types automatically get min/max filters where the user can select the range of dates, times, or numbers.

**If you want to filter a related field, you have to put the relationship method name in the $filters array and use type 'relationship'.**

The available options are the same as the $edit property's options.

```php
public $filters = array(
	'id', //if this is the name of the primary key for this model, it will be an id lookup, which is useful for quick searches
	'email',
	'name' => array(
		'title' => 'Name',
	),
	'birthdate' => array(
		'title' => 'Birth Date',
		'type' => 'date',
	),
	'roles' => array(
		'title' => 'Roles',
		'type' => 'relationship',
		'name_field' => 'name',
	),

);
```

The relationship type behaves just like it does in the $edit array.

#### $sortOptions (not required)

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/sorting.png" />

The $sortOptions array has these options:

```php
public $sortOptions = array(
	'field' => 'id', 		//can be any of the supplied keys in the $columns array
	'direction' => 'asc', 	//either 'asc' or 'desc'
)
```

Naturally, this is only the initial sort. As the user interacts with the table, it will change.

#### $expand (not required)

The $expand property, when used, determines how wide the edit area should be for a model. It can either be set to boolean true to accept the default expand (which is 500px), or it can be set to an integer above 285 (which is the size of the filter area that the edit box covers). This is useful if you want some extra space for a textarea type, a has_many_and_belongs_to relationship with many possible items, etc.

```php
public $expand = 400;
```


#### before_delete()

The before_delete() method will always be run before an item is deleted. It's important to know that **Administrator does not automatically delete relationships**. This is done because some people have cascading deletes built into their database, others might want to only delete some related data, and others might want to delete all of it. So if you have a users relationship (has_many_and_belongs_to) on your Role model, you might want to do something like this:

```php
public function before_delete()
{
	//delete the users relationship
	$this->users()->delete();
}
```

#### create_link()

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/create-link.png" />

The create_link() method lets you define a model's front-end URL if applicable. If provided, this will show up as a link in the edit form as shown above.

```php
public function create_link()
{
	//here I have a named route to which I'm passing the id parameter
	return URL::to_route('my_named_route', array($this->id));
}
```

You can construct this URL however you like. In the above example you can see that I used named routes. However, you can also build the URL manually or use controller actions. You could also just return 'http://www.google.com' for every item if that's what you want.

### Field Types

This is a list of all the field types that you can use in the $edit array.

#### text

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-text.png" />

```php
'name' => array(
	'type' => 'text',
	'title' => 'Name',
	'limit' => 50,
)
```

This is the default type. You can set a character limit by providing an integer value to the 'limit' option

#### textarea

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-textarea.png" />

```php
'name' => array(
	'type' => 'textarea',
	'title' => 'Name',
	'limit' => 500,
	'height' => 130, //default is 100
)
```

The textarea is basically the same thing as a text type, except it uses a textarea.

#### wysiwyg

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-wysiwyg.png" />

```php
'name' => array(
	'type' => 'wysiwyg',
	'title' => 'Name',
)
```

The wysiwyg type is a text field that uses CKEditor. If you use this field you'll likely want to also set the $expand property so that the wysiwyg has enough space on the page.

#### markdown

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-markdown.png" />

```php
'name' => array(
	'type' => 'markdown',
	'title' => 'Name',
	'height' => 200, //default is 100
)
```

The markdown type lets you create a field that is essentially a text field but shows a preview of the rendered markdown.

#### relationship

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-relation-single.png" />

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-relation-multi.png" />

```php
'actors' => array(
	'type' => 'relationship',
	'title' => 'Actors',
	'name_field' => 'name', //what column or getter on the other table you want to use to represent this object
	'constraints' => array('film' => 'actors') //'film' should be another relationship *in this model* and 'actors' should be a method on the film model
	'autocomplete' => true, //set the following three fields if you want to have an autocomplete select box
	'num_options' => 5,
	'search_fields' => array("CONCAT(first_name, ' ', last_name)"),
)
```

The relationship field should have the relationship's method name as its index. The only required option is 'type'. The name_field will be the item's name when displayed in the select boxes. If autocomplete is set to true, you can also provide num_options and search_fields. If constraints are supplied, the key must be the method name of the other relationship you want to couple the constraint, and the value must be the name of the relationship on that relationship's model. Constriants only apply when the models representing the two fields in question themselves have a pivot table (i.e. films_actors).

#### number

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-currency.png" />

```php
'price' => array(
	'type' => 'number',
	'title' => 'Price',
	'symbol' => '$', //symbol shown in front of the number
	'decimals' => 2, //the number of digits after the decimal point
	'thousands_separator' => ',',
	'decimal_separator' => '.',
)
```

The number field should be a numeric field (integer, decimal, float) in your database. The symbol will be displayed before the number if present. The decimal separator will be used if the decimal value is above 0.

#### bool

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-bool.png" />

```php
'is_good' => array(
	'type' => 'bool',
	'title' => 'Is Good',
)
```

The bool field should be an integer field (usually tinyint(1) or whatever your db supports).

#### enum

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-enum.png" />

```php
'season' => array(
	'title' => 'Season',
	'type' => 'enum',
	'options' => array('Winter', 'Spring', 'Summer', 'Fall'), //must be an array
),
//alternate method:
'season' => array(
	'title' => 'Season',
	'type' => 'enum',
	'options' => array(
		'Winter' => 'Cold, Cold Winter!',
		'Spring',
		'Summer' => 'Hot, Hot Summer!',
		'Fall'
	),
),
```

The enum field gives the user a permanent limited selection of items from which to choose. If an array with string keys is supplied, those string keys will be considered the enum value, while the values will be displayed to the user. You can mix and match these array slots as seen above.

#### date

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-date.png" />

```php
'start_date' => array(
	'type' => 'date',
	'title' => 'Start Date',
	'date_format' => 'yy-mm-dd', //The date format to be shown in the field (in the database it should be a Date type)
)
```

Using the date type will set the field up as a jQuery UI datepicker.

The date_format supplied has to be a valid DatePicker date format. You can see a full list here: [http://docs.jquery.com/UI/Datepicker/formatDate](http://docs.jquery.com/UI/Datepicker/formatDate).

#### time

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-time.png" />

```php
'opening_time' => array(
	'type' => 'time',
	'title' => 'Opening Time',
	'time_format' => 'HH:mm', //The time format to be shown in the field
)
```

Using the time type will set the field up as a jQuery UI timepicker.

The time format supplied has to be a valid timepicker time format. You can see a full list here: [http://trentrichardson.com/examples/timepicker/#tp-formatting](http://trentrichardson.com/examples/timepicker/#tp-formatting).

#### datetime

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-datetime.png" />

```php
'game_start_time' => array(
	'type' => 'datetime',
	'title' => 'Start Time',
	'date_format' => 'yy-mm-dd', //The date format to be shown in the field
	'time_format' => 'HH:mm', //The time format to be shown in the field
)
```

Using the datetime type will set the field up as a jQuery UI datetimepicker. The formatters are the same as above.






## Copyright and License
Administrator was written by Jan Hartigan for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.

## Changelog

### 2.3.0
- Relationship constraints are now possible if you want to limit one relationship field's options by its relation to another relationship field (only applies when those two fields themselves have a pivot table)
- You can now hit the enter key on text/textarea fields to submit the create/edit form
- Self-relationships are now possible
- Bugfix: Bool field now works properly with SQLite (or any database that returns ints as strings)
- Bugfix: History.js now recognizes base URIs other than '/'
- Bugfix: In PostgreSQL there was an issue with using boolean false to pull back no results on an integer column
- Bugfix: If you are on some high page number and you filter the set such that that page number is now outside the range of the filtered set, you will be brought back to the last page of the set instead of staying on that page
- Bugfix: Adding real-time viewModel updates to the wysiwyg field. Sometimes if you hit "save" fast enough it wouldn't write the changes back to the viewModel from the CKEditor
- Bugfix: There was an array index error when not providing a name_field or when only providing one of the sort options

### 2.2.0
- There is now an autocomplete option for relationships that could have a lot of potential values
- You can now set the $expand property for a model to boolean true or any integer above 285 (i.e. pixels) to get more room for the edit form
- Model config now allows for a 'single' name. Example: Film model would be 'film'. BoxOffice model would be 'take'. i.e. New film, New take
- New 'bool' field type
- New 'enum' field type
- New 'wysiwyg' field type
- New 'textarea' field type
- New 'markdown' field type
- Added 'limit' option for text/textarea/markdown field types
- Added 'height' option for textarea/markdown field types (pixels as an integer)
- You can now provide a create_link method in your model that should return the URL of the string of the item's front-end page
- You can now optionally provide a 'permission_check' closure for each model in the config. This works just like auth_check but on a per-model basis. If provided, and if it evaluates to false, the user will be redirected back to the admin dashboard.
- Bugfix: Multiple commas in number fields were messing up the values
- Bugfix: The custom binding for the number field now uses the user-supplied fields like decimals, thousands_separator, and decimal_separator.
- Bugfix: Various animation bugs in the UI

### 2.1.0
- You can no longer use has_one or has_many fields in the $edit property. This is because those relationships require a new item to be created on the other table.
- The number field now formats nicely in the interface
- Added the first tutorial video to the README and added the code from that video to the examples/application directory
- Bugfix: There was a case sensitivity issue with the libraries folder because of the namespaces I was using. Quickfixed this by changing libraries to Libraries.
- Bugfix: Getting model rows was calling 'SELECT * FROM [whatever_relationship_table]' multiple times. This should alleviate some performance issues.

### 2.0.1
- Bugfix: related to grouping functions in the 'select' option
- Bugfix: related to the model title showing up

### 2.0.0
- Reorganized the libraries
- title_field is now name_field
- relation is now relationship
- currency is now number and non-currency number types are now supported
- $edit and $filters arrays no longer have default values. You must supply them or they won't show up
- $column now accepts a 'select' option for any field to allow for proper sorting
- Temporarily found a work around for a major bug with Laravel paginate() method where it wouldn't properly count the rows when using a grouping (will be fixed in L4)
- Innumerable bugfixes (with plenty more to come)

### 1.2.0
- Added all field types to filters
- Currency (and soon all numbers), date, datetime, and time filters are now a min/max range
- Assorted improvements to make it easier to add field types

### 1.1.0
- Sorting getter columns
- Sorting relational columns with custom select statements
- Fixed several bugs related to sorting
- Fixed several bugs related to using getters as columns

### 1.0.1
- 'id' filter type now works
- Getter values now show up in the result set

### 1.0.0
- Initial release.