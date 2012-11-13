# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 1.0.1

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/overview.png" />

## Inspiration / Credit

The initial inspiration for this project came from the [Lara Admin](https://github.com/chalien/lara_admin) bundle by chalien. In between then and the initial release of this bundle, pretty much the entire codebase has been changed. Still, some of the design elements of Lara Admin remain (for the time being), partially as a testament to chalien's work! 

## Installation / Documentation

You can either create a bundle directory called `administrator` and manually copy the bundle contents into it, or you can run the artisan command:

<pre>
php artisan bundle:install administrator
</pre>

Then add this to your `bundles.php` array:

<pre>
'administrator' => array(
	'handles' => 'admin', //this determines what URI this bundle will use
	'auto' => true,
),
</pre>

Once the bundle is installed, create a new config file in your application config called administrator.php (`application/config/administrator.php`). Then copy the contents of the bundle's config file (`administrator/config/administrator.php`) and put it into the application config file you just created. If you prefer to keep your bundle config inside the bundle, you can just change the bundle's config directly. However, moving this to the application config helps avoid issues when you are updating the bundle.

### Config

<pre>
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
 * Each item in the array should itself be an array (with two items inside it: title, model) and it should be indexed on the model name.
 * This should look something like this:
 * 
 * 'user' => array(
 * 		'title' => 'Users', //The title that will be used when displaying the model's page
 * 		'model' => 'AdminModels\\User', //The string class name of the model you will be using. If you wish to extend your app models directly, you can just pass in 'User'. Beware, though: your model will need to have the required properties on it for Administrator to recognize it.
 * )
 */
'models' => array(
	'user' => array(
		'title' => 'Users',
		'model' => 'AdminModels\\User', //This is just a fully-qualified classname. Here I've namespaced my admin models to AdminModels so I can reuse the "User" classname.
	),
	'role' => array(
		'title' => 'Roles',
		'model' => 'AdminModels\\Role',
	),
	'hat' => array(
		'title' => 'Hats',
		'model' => 'Hat', //In this case I'm just using the un-namespaced "Hat" class/model.
	),
	'film' => array(
		'title' => 'Films',
		'model' => 'Film',
	),
),

/**
 * Auth Check
 * 
 * @type closure
 * 
 * This is a closure that should return true if the current user is allowed to view the admin section. If this fails, it will redirect the user to the login_path.
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
</pre>


### Data Models

This bundle was designed to take advantage of the data models that already exist on your site (normally in `application/models`). Administrator data models should be, at their base, some form of an Eloquent data model with several additional required properties. As long as you provide the fully-qualified class name in the config (see above), Administrator will be able to use the model. This means that you have several organizational options:

- Use your existing Eloquent models and add the required properties

- Create new models that extend your existing Eloquent models and prefix them with something like `Admin_` so there are no namespace collisions. This would allow you to keep your Administrator properties separated from your regular models.

- (preferred) Create new models that user the same name as and extend your existing Eloquent models and namespace them to something like `AdminModels`. This also would separate your Administrator properties from your base model.

The last option has (in my opinion) the best of all worlds: it would allow you to retain your clear model names, avoid namespace collisions, and keep your Administrator properties separated from your base models.

What I like to do is create a directory under my models (or entities, or however you define it) directory called `admin`. Each of the models in this directory can extend any Eloquent-based class (which means Eloquent, Aware, or your base models)

#### Extending from Eloquent
<pre>
&lt;?php namespace AdminModels; 
 
class User extends \Eloquent
{ .. }
</pre>

#### Extending from Aware
<pre>
&lt;?php namespace AdminModels; 
 
class User extends \Aware
{ .. }
</pre>

#### Extending from an existing User model
<pre>
&lt;?php namespace AdminModels; 
 
class User extends \User
{ .. }
</pre>

If you're using the namespace approach as I did above, make sure you add that backslash in front of the class you're extending. This tells PHP to look in the core Laravel namespace.

In the first two examples, you need to set up the model as if it were any other Eloquent/Aware model. You will need to make a `$rules` array that works just like Aware's.

The last case will likely be the most common as it means you won't have to repeat your model's rules. You can just declare the Administrator properties and you'll be all set to go!

Now let's take a look at the properties that you can set on Administrator models and what they mean. Keep in mind that since these are Eloquent models, all of the traditional properties apply (e.g. $per_page, $table).


#### $columns

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/columns.png" />

This property tells Administrator what columns to use when displaying the tabular result set. You can either pass it a simple string which will be used as the data key (i.e. if your database column is called `potato_farming_score`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `potato_farming_score` and it would contain an array of options.

The available options are:

- *title*: default is column name

<pre>
public $columns = array(
	'id',
	'name',
	'price',
	'created_at' => array(
		'title' => 'Created', //the header title of the column
	),
	'updated_at' => array(
		'title' => 'Updated',
	),
);
</pre>

One of the nice things about extending Eloquent is that you can use getters to return computed values. For example, if you wanted to have a column called `# Posts`, all you would have to do is set a getter for the field `num_posts` and then add this to the $columns array:

<pre>
	'num_posts' => array(
		'title' => '# Posts',
	)
</pre>

#### $sortOptions

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/sorting.png" />

The $sortOptions array has these options:

<pre>
public $sortOptions = array(
	'field' => 'id', 		//can be any of the supplied keys in the $columns array
	'direction' => 'asc', 	//either 'asc' or 'desc'
)
</pre>

Naturally, this is only the initial sort. As the user interacts with the table, it will change.

#### $edit

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/edit-form.png" />

This property tells Administrator what columns to use when editing an item. You can either pass it a simple string which will be used as the data key (i.e. if your database column is called `potato_farming_score`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `potato_farming_score` and it would contain an array of options.

**If you want to edit a related field, you have to put the relationship method name in the $edit array and use type 'relation'.**

The available options are:

- *title*
- *type*: default is 'text'. Choices are: relation, text, date, time, datetime, currency
- *title_field*: default is 'name'. Only use this if type is 'relation'. This is the field on the other table to use for displaying the name/title of the other data model.

<pre>
public $edit = array(
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
		'type' => 'relation',
		'title_field' => 'title', //field on other table to use for the name/title
	),
	'price' => array(
		'title' => 'Price',
		'type' => 'currency',
		'symbol' => '$', //symbol shown in front of the number
		'precision' => 10, //the number of digits in front of the decimal point (e.g. 1234567890.00 for 10)
		'scale' => 2, //the number of digits after the decimal point
	),
	
);
</pre>

If you select the 'relation' type, the edit form will display either a single- or multi-select box depending on the type of relationship. The Administrator bundle recognizes all types of Eloquent relationships (belongs_to, has_one, has_many, has_many_and_belongs_to). All you have to do is ensure that the key in the $edit array is **the name of the relation method in your data model**. If you have a User model and your user has many Roles, you'll typically want to have a method that looks like this in Eloquent:

<pre>
public function roles()
{
	return $this->has_many_and_belongs_to('Role', 'role_user');
}
</pre>

Similarly, if each of your users has only a *single* Potato, you'll want a method that looks like:

<pre>
public function potato()
{
	return $this->has_one('Potato');
}
</pre>

Unless you're extending directly from Eloquent/Aware, these methods should already exist in your data models. Here's how each of those would look in your $edit property:

<pre>
'roles' => array(
	'title' => 'Roles',
	'type' => 'relation',
),
'potato' => array(
	'title' => 'Potato',
	'type' => 'relation',
	'title_field' => 'title', //this lets Administrator know what column to reference on the Potato model. Default is 'name'
),
</pre>

#### $filters

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/filters.png" />

This property tells Administrator what columns to use to build the filterable set. This works almost exactly like the $edit property, so you can either pass it a simple string which will be used as the data key (i.e. if your database column is called `potato_farming_score`, put that in), or you can pass it a key-indexed array of options. In this case, the array key will be `potato_farming_score` and it would contain an array of options.

**If you want to filter a related field, you have to put the relationship method name in the $filters array and use type 'relation'.**

The available options are:

- *title*
- *type*: default is 'text'. choices are: text, date, time, datetime, relation

<pre>
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
		'type' => 'relation',
	),
	
);
</pre>

The relation type behaves just like it does in the $edit array. If you don't supply a $filters property, it will automatically use your $edit property to set up the filter.


#### before_delete()

The before_delete() method will always be run before an item is deleted. It's important to realize that **Administrator does not automatically delete relationships**. This is done because some people have cascading deletes built into their database, others might want to only delete some related data, and others might want to delete all of it. So if you have a users relationship (has_many_and_belongs_to) on your Role model, you might want to do something like this:

<pre>
public function before_delete()
{
	//delete the users relationship
	$this->users()->delete();
}
</pre>


### Field Types

This is a list of all the field types that you can use in the $edit array.

#### text

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-text.png" />

<pre>
'name' => array(
	'type' => 'text',
	'title' => 'Name',
)
</pre>

This is the default type. It has no unique options. Soon there will be an option to have different text input types and text size limits.

#### relation

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-relation-single.png" />
<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-relation-multi.png" />

<pre>
'roles' => array(
	'type' => 'relation',
	'title' => 'Roles',
	'title_field' => 'name', //what column on the other table you want to use to represent this object
)
</pre>

The relation field should have the relationship's method name as its index. The title_field will be the item's name when displayed in the select boxes.

#### currency

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-currency.png" />

<pre>
'price' => array(
	'type' => 'currency',
	'title' => 'Price',
	'symbol' => '$', //symbol shown in front of the number
	'precision' => 10, //the number of digits in front of the decimal point (e.g. 1234567890.00 for 10)
	'scale' => 2, //the number of digits after the decimal point
)
</pre>

The currency field should be a numeric field in your database (normally something like Decimal(precision, scale)). The symbol will be displayed before the number.

#### date

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-date.png" />

<pre>
'start_date' => array(
	'type' => 'date',
	'title' => 'Start Date',
	'date_format' => 'yy-mm-dd', //The date format to be shown in the field (in the database it should be a Date type)
)
</pre>

Using the date type will set the field up as a jQuery UI datepicker. 

The date_format supplied has to be a valid DatePicker date format. You can see a full list here: [http://docs.jquery.com/UI/Datepicker/formatDate](http://docs.jquery.com/UI/Datepicker/formatDate).

#### time

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-time.png" />

<pre>
'opening_time' => array(
	'type' => 'time',
	'title' => 'Opening Time',
	'time_format' => 'HH:mm', //The time format to be shown in the field
)
</pre>

Using the time type will set the field up as a jQuery UI timepicker.

The time format supplied has to be a valid timepicker time format. You can see a full list here: [http://trentrichardson.com/examples/timepicker/#tp-formatting](http://trentrichardson.com/examples/timepicker/#tp-formatting).

#### datetime

<img src="https://github.com/FrozenNode/Laravel-Administrator/raw/master/examples/images/field-type-datetime.png" />

<pre>
'game_start_time' => array(
	'type' => 'datetime',
	'title' => 'Start Time',
	'date_format' => 'yy-mm-dd', //The date format to be shown in the field
	'time_format' => 'HH:mm', //The time format to be shown in the field
)
</pre>

Using the datetime type will set the field up as a jQuery UI datetimepicker. The formatters are the same as above.






## Copyright and License
Administrator was written by Jan Hartigan for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.

## Changelog

### Administrator 1.0.1
- 'id' filter type now works
- Getter values now show up in the result set

### Administrator 1.0.0
- Initial release.