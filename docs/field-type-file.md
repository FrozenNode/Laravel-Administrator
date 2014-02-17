# Field Type - File

- [Usage](#usage)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-file.png" />

The `file` field type should be a text-like type in your database. The file's name is stored in this field, while the original will be saved in the `location` you specify, and any resized copies will be stored where you define in the `sizes` option.

	'media_document' => array(
		'title' => 'File',
		'type' => 'file',
		'location' => storage_path() . '/media_documents/',
		'naming' => 'random',
		'length' => 20,
		'size_limit' => 2,
		'mimes' => 'pdf,psd,doc',
	)

In the edit form, an admin user will be presented with a file uploader.

The required `location` option lets you define where the file should be stored.

The optional `naming` option lets you define whether to `keep` the file's name or to make the file name `random`. By default this is set to `random` in order to avoid naming collisions, but setting this to `keep` lets you keep your file's names.

The optional `length` option lets you define size of file name in case `random` is supplied in the `naming` option.

The optional `size_limit` option lets you set an integer size limit counted in megabytes. This only affects the JavaScript file uploading dialog, it doesn't limit your PHP upload sizes (which you can do in your php.ini).

The optional `mimes` option by default allows all file types. This uses Laravel's [mimes validation](http://laravel.com/docs/validation#rule-mimes), which in turn uses the PHP Fileinfo extension to read the contents of the file and determine the actual MIME type.