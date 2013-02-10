# Field Type - Markdown

- [Usage](#usage)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-markdown.png" />

The `markdown` field type should be any text-like type in your database.

	'name' => array(
		'type' => 'markdown',
		'title' => 'Name',
		'limit' => 300, //optional, defaults to no limit
		'height' => 130, //optional, defaults to 100
	)

In the edit form, an admin user will be presented with a textarea on the left side and the marked up version of that text on the right side. When the field's value is saved to the database, the markdown will be saved, not the marked up html.

The `limit` option lets you set a character limit for the field.

The `height` option lets you set the height of the textarea in pixels.

Since the `markdown` field type requires a bit of space, you may want to think about [expanding your model's form width](/docs/model-configuration#form-width) to something like `400`.