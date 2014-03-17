<?php
namespace Frozennode\Administrator\Includes;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

//Use Admin\Libraries\Includes\Resize as Resize;
/*
* @package Multup
* @version 0.2.0
* @author Nick Kelly @ Frozen Node
* @link github.com/
*
* Requires Validator, URL, and Str class from Laravel if used
*
*
*/
class Multup {

	/*
		image array
	*/
	private $image;

	/*
		string of laravel validation rules
	*/
	private $rules;

	/*
		randomize uploaded filename
	*/
	private $random;

	/*
		path relative to /public/ that the image should be saved in
	*/
	private $path;

	/*
		id/name of the file input to find
	*/
	private $input;

	/*
		How long the random filename should be
	*/
	private $random_length = 32;

	/*
	*	Callback function for setting your own random filename
	*/
	private $random_cb;

	/*
	* Sizing information for thumbs to create
	* array ( width, height, crop_type, path_to_save, quality)
	*/
	private $image_sizes;

	/*
	*	Upload callback function to be called after an image is done being uploaded
	*	@var function/closure
	*/
	private $upload_callback;

	/*
	*	Arry of additional arguements to be passed into the callback function
	*	@var array
	*/
	private $upload_callback_args;

	/**
	 * Instantiates the Multup
	 * @param mixed $file The file array provided by Laravel's Input::file('field_name') or a path to a file
	 */
	public function __construct($input, $rules, $path, $random)
	{
		$this->input  = $input;
		$this->rules  = $rules;
		$this->path = $path;
		$this->random = $random;
	}

	/**
	 * Static call, Laravel style.
	 * Returns a new Multup object, allowing for chainable calls
	 * @param  string $input name of the file to upload
	 * @param  string $rules laravel style validation rules string
	 * @param  string $path relative to /public/ to move the images if valid
	 * @param  bool $random Whether or not to randomize the filename, the filename will be set to a 32 character string if true
	 * @return Multup
	 */
	public static function open($input, $rules, $path, $random = true)
	{
		return new Multup( $input, $rules, $path, $random );
	}

	/*
	*	Set the length of the randomized filename
	*   @param int $len
	*/
	public function set_length($len)
	{
		if(!is_int($len)){
			return false;
		} else{
			$this->random_length = $len;
		}
		return $this;
	}

	/*
	*	Upload the image
	*	@return array of results
	*			each result will be an array() with keys:
	*			errors array -> empty if saved properly, otherwise $validation->errors object
	*			path string -> full URL to the file if saved, empty if not saved
	*			filename string -> name of the saved file or file that could not be uploaded
	*
	*/
	public function upload()
	{

		$this->image = array($this->input => Input::file($this->input));
		$result = array();

		$result[] = $this->post_upload_process($this->upload_image());

		return $result;

		if ($image)
		{
			$this->image = array(
				$this->input => array(
					'name'      => $image->getClientOriginalName(),
					'type'      => $image->getClientMimeType(),
					'tmp_name'  => $image->getFilename(),
					'error'     => $image->getError(),
					'size'      => $image->getSize(),
				)
			);

			$result[] = $this->post_upload_process($this->upload_image());
		}

		return $result;

		if(!is_array($images)){

			$this->image = array($this->input => $images);

			$result[] = $this->post_upload_process($this->upload_image());

		} else {
			$size = $count($images['name']);

			for($i = 0; $i < $size; $i++){

				$this->image = array(
					$this->input => array(
						'name'      => $images['name'][$i],
						'type'      => $images['type'][$i],
						'tmp_name'  => $images['tmp_name'][$i],
						'error'     => $images['error'][$i],
						'size'      => $images['size'][$i]
					)
				);

				$result[] = $this->post_upload_process($this->upload_image());
			}
		}

		return $result;

	}

	/*
	*	Upload the image
	*/
	private function upload_image()
	{

		/* validate the image */
		$validation = Validator::make($this->image, array($this->input => $this->rules));
		$errors = array();
		$original_name = $this->image[$this->input]->getClientOriginalName();
		$path = '';
		$filename = '';
		$resizes = '';

		if($validation->fails()){
			/* use the messages object for the erros */
			$errors = implode('. ', $validation->messages()->all());
		} else {

			if($this->random){
				if(is_callable($this->random_cb)){
					$filename =  call_user_func( $this->random_cb, $original_name );
				} else {
					$ext = File::extension($original_name);
					$filename = $this->generate_random_filename().'.'.$ext;
				}
			} else {
				$filename = $original_name;
			}

			/* upload the file */
			$save = $this->image[$this->input]->move($this->path, $filename);
			//$save = Input::upload($this->input, $this->path, $filename);

			if($save){
				$path = $this->path.$filename;

				if(is_array($this->image_sizes)){
					$resizer = new Resize();
					$resizes = $resizer->create($save, $this->path, $filename, $this->image_sizes);
				}

			} else {
				$errors = 'Could not save image';
			}
		}

		return compact('errors', 'path', 'filename', 'original_name', 'resizes' );
	}

	/*
	* Default random filename generation
	*/
	private function generate_random_filename()
	{
		 return Str::random($this->random_length);
	}

	/*
	* Default random filename generation
	*/
	public function filename_callback( $func )
	{
		if(is_callable($func)){
			$this->random_cb = $func;
		}

		return $this;
	}

	/*
		Set the callback function to be called after each image is done uploading
		@var mixed anonymous function or string name of function
	*/
	public function after_upload( $cb, $args = '')
	{
		if(is_callable($cb)){
			$this->upload_callback = $cb;
			$this->upload_callback_args = $args;
		} else {
			/* some sort of error... */
		}
		return $this;
	}

	/*
	*	Sets the sizes for resizing the original
	*  @param array(
	*		array(
	*			int $width , int $height , string 'exact, portrait, landscape, auto or crop', string 'path/to/file.jpg' , int $quality
	*		)
	*	)
	*
	*/
	public function sizes( $sizes )
	{
		$this->image_sizes = $sizes;
		return $this;
	}

	/*
		Called after an image is successfully uploaded
		The function will append the vars to the images property
		If an after_upload function has been defined it will also append a variable to the array
			named callback_result

		@var array
			path
			resize ->this will be empty as the resize has not yet occurred
			filename -> the name of the successfully uploaded file
		@return void
	*/
	private function post_upload_process( $args )
	{

		if(empty($args['errors'])){
			/* add the saved image to the images array thing */

			if(is_callable($this->upload_callback)){
				if(!empty($this->upload_callback_args) && is_array($this->upload_callback_args)){
					$args = array_merge($this->upload_callback_args, $args);
				}

				$args['callback_result']  = call_user_func( $this->upload_callback, $args);
			}

		}

		return $args;
	}

}