# Field Type - Password

- [Usage](#usage)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-password.png" />

The `password` field type should be any text-like type in your database. Password field types are automatically created as setters (i.e. they won't display a value). You should use [Eloquent mutators](http://laravel.com/docs/eloquent#accessors-and-mutators) in conjunction with a password field to make sure that the supplied password is properly hashed.

	'password' => array(
		'type' => 'password',
		'title' => 'Password',
	)

In the edit form, an admin user will be presented with a password input.