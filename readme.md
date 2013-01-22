# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 3.0.0

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/overview.png" />

## Inspiration / Credit

The initial inspiration for this project came from the [Lara Admin](https://github.com/chalien/lara_admin) bundle by chalien. In between then and the initial release of this bundle, pretty much the entire codebase has been changed. Still, some of the design elements of Lara Admin remain (for the time being), partially as a testament to chalien's work!


## Documentation

See the [installation instructions](http://administrator.frozennode.com/docs/installation).


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

##### Image
- **naming**: default is 'random'. This can be either 'keep' (keeps the file name) or 'random' (randomizes file names...avoids collisions).
- **location**: this field is currently required. It's where to put the original file that was uploaded.
- **size_limit**: default is 2. This is the file size in mb for the image. This block is applied in the front-end, not the PHP side.
- **sizes**: default is empty array(). Provide an array of sizes that the image will be resized to (see example below). In order these are width, height, the resize method ('exact', 'portrait', 'landscape', 'fit', 'auto', or 'crop'), the location to store the resized image, and the image quality (1 - 100)

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
	),
	'image' => array(
		'title' => 'Image',
		'type' => 'image',
		'naming' => 'random', //can be either "keep" or "random"
		'location' => 'public/uploads/products/originals/', //the location to store the original
		'size_limit' => 2, //the file size limit in megabytes (only limits the javascript, you still have to set your php limit)
		'sizes' => array( //alternate sizes to use
	 		array(65, 57, 'crop', 'public/uploads/products/thumbs/small/', 100),
	 		array(220, 138, 'landscape', 'public/uploads/products/thumbs/medium/', 100),
	 		array(383, 276, 'fit', 'public/uploads/products/thumbs/full/', 100)
	 	)
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

#### image

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-image.png" />

```php
'passport_photo' => array(
	'title' => 'Image',
	'type' => 'image',
	'naming' => 'random', //can be either "keep" or "random"
	'location' => 'public/uploads/products/originals/', //the location to store the original
	'size_limit' => 2, //the file size limit in megabytes (only limits the javascript, you still have to set your php limit)
	'sizes' => array( //alternate sizes to use
 		array(65, 57, 'crop', 'public/uploads/products/thumbs/small/', 100),
 		array(220, 138, 'landscape', 'public/uploads/products/thumbs/medium/', 100),
 		array(383, 276, 'fit', 'public/uploads/products/thumbs/full/', 100)
 	)
)
```

An image field will allow the admin user to upoad images. Administrator saves the image (and its different sizes) to the server and stores the image file name in the database.

#### color

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-color.png" />

```php
'hex' => array(
	'type' => 'color',
	'title' => 'Color (hex value)',
)
```

The color field provides a color picker that lets the admin user pick a hexadecimal color value (e.g. #ffffff for white).






## Copyright and License
Administrator was written by Jan Hartigan for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.

## Changelog


x - not done
d - done, but needs documentation
### 3.0.0
- Model configuration must now be done in model config files instead of in an Eloquent model
x Revamped the docs to make it more accessible/readable
x
d You can now group together models into menu groups
- New color field
- New image field
d Custom column outputs
- Admin users can now set a custom number of rows in each model's interface
d You can now add custom action buttons in the $actions property of a model
x You can now apply per-model permissions for creating, saving, and deleting items
- Renamed 'permission_check' and 'auth_check' to the uniform 'permission'
- Renamed 'global_per_page' to 'global_rows_per_page'
- The $edit property is now the 'edit_fields' option in the model config
- The $expand property is now the 'form_width' option in the model config
- Removed the before_delete() method. This can be handled by using the "eloquent.delete: {{classname}}" event
- Migrated from the old string-based jQuery template engine to the faster, smarter Knockout comment bindings
- Bugfix: BelongsTo filter no longer does a LIKE search (since it's an explicit key)


See *changelog.md* for the changelog from previous versions