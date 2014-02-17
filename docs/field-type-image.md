# Field Type - Image

- [Usage](#usage)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-image.jpg" />

The `image` field type should be a text-like type in your database. The image's *file name* is stored in this field, while the original will be saved in the `location` you specify, and any resized copies will be stored where you define in the `sizes` option.

	'image' => array(
		'title' => 'Image',
		'type' => 'image',
		'location' => public_path() . '/uploads/products/originals/',
		'naming' => 'random',
		'length' => 20,
		'size_limit' => 2,
		'sizes' => array(
			array(65, 57, 'crop', public_path() . '/uploads/products/thumbs/small/', 100),
			array(220, 138, 'landscape', public_path() . '/uploads/products/thumbs/medium/', 100),
			array(383, 276, 'fit', public_path() . '/uploads/products/thumbs/full/', 100)
		)
	)

In the edit form, an admin user will be presented with an image uploader. For the moment, this uploader only allows one image to be uploaded at a time.

The required `location` option lets you define where the original image should be stored.

The optional `naming` option lets you define whether to `keep` the file's name or to make the file name `random`. By default this is set to `random` in order to avoid naming collisions, but setting this to `keep` lets you keep your image's file names.

The optional `length` option lets you define size of file name in case `random` is supplied as `naming` option method.

The optional `size_limit` option lets you set an integer size limit counted in megabytes. This only affects the JavaScript file uploading dialog, it doesn't limit your PHP upload sizes.

The optional `sizes` option lets you define as many resizes as you want. The format for these is: `array([width], [height], [method], [save path], [quality])`. The different methods are `exact`, `portrait`, `landscape`, `fit`, `auto`, and `crop`.