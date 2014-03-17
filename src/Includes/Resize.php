<?php
namespace Frozennode\Administrator\Includes;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\File as SFile;

/*
*	The bulk of this class is pulled from the Resizer bundle I edited it to fit my needs here
*	@author Nick Kelly(original author Jarrod Oberto &  Maikel D)
*	@version 1.0
*/
class Resize{

	/*
	*	The file object of the original image
	*	@var File
	*/
	private $file;

	/*
	*	Original width of the image being resized
	*	@var int
	*/
	private $width;

	/*
	*	New width of the image being resized
	*	@var int
	*/
	private $new_width;

	/*
	*	Original height of the image being resized
	*	@var int
	*/
	private $height;

	/*
	*	New height of the image being resized
	*	@var int
	*/
	private $new_height;

	/*
	*	Type of crop being performed
	*	@var str
	*/
	private $option;

	/*
	*	The resized image resource
	*	@var resource
	*/
	private $image_resized;

	/*
		Create multiple thumbs/resizes of an image
		Path to the original
		sizes
			width, height, crop type, path, quality
	*/
	public function create($file, $path, $filename, $sizes ){

		$this->file = $file;

		if(is_array($sizes)){

			$resized = array();

			foreach($sizes as $size){

				$this->new_width = $size[0]; //$new_width;
				$this->new_height = $size[1]; //$new_height;
				$this->option = $size[2]; //crop type

				//ensure that the directory path exists
				if (!is_dir($size[3]))
				{
					mkdir($size[3]);
				}

				$resized[] = $this->do_resize( $path.$filename, $size[3].$filename, $size[4] );
			}
		}

		return $resized;
	}

	/**
	 * Resizes and/or crops an image
	 * @param  mixed   $image resource or filepath
	 * @param  strung  $save_path where to save the resized image
	 * @param  int (0-100) $quality
	 * @return bool
	 */
	private function do_resize( $image, $save_path, $image_quality )
	{
		$image = $this->open_image( $image );

		$this->width  = imagesx( $image );
		$this->height = imagesy( $image );

		// Get optimal width and height - based on $option.
		$option_array = $this->get_dimensions( $this->new_width , $this->new_height , $this->option );

		$optimal_width	= $option_array['optimal_width'];
		$optimal_height	= $option_array['optimal_height'];

		// Resample - create image canvas of x, y size.
		$this->image_resized = imagecreatetruecolor( $optimal_width , $optimal_height );

		// Retain transparency for PNG and GIF files.
		imagecolortransparent( $this->image_resized , imagecolorallocatealpha( $this->image_resized , 255 , 255 , 255 , 127 ) );
		imagealphablending( $this->image_resized , false );
		imagesavealpha( $this->image_resized , true );

		// Create the new image.
		imagecopyresampled( $this->image_resized , $image , 0 , 0 , 0 , 0 , $optimal_width , $optimal_height , $this->width , $this->height );

		// if option is 'crop' or 'fit', then crop too
		if ( $this->option == 'crop' || $this->option == 'fit' ) {
			$this->crop( $optimal_width , $optimal_height , $this->new_width , $this->new_height );
		}

		// Get extension of the output file
		$extension = strtolower( File::extension($save_path) );

		// Create and save an image based on it's extension
		switch( $extension )
		{
			case 'jpg':
			case 'jpeg':
				if ( imagetypes() & IMG_JPG ) {
					imagejpeg( $this->image_resized , $save_path , $image_quality );
				}
				break;

			case 'gif':
				if ( imagetypes() & IMG_GIF ) {
					imagegif( $this->image_resized  , $save_path );
				}
				break;

			case 'png':
				// Scale quality from 0-100 to 0-9
				$scale_quality = round( ($image_quality/100) * 9 );

				// Invert quality setting as 0 is best, not 9
				$invert_scale_quality = 9 - $scale_quality;

				if ( imagetypes() & IMG_PNG ) {
					imagepng( $this->image_resized , $save_path , $invert_scale_quality );
				}
				break;

			default:
				return false;
				break;
		}

		// Remove the resource for the resized image
		imagedestroy( $this->image_resized );

		return true;
	}

	/**
	 * Open a file, detect its mime-type and create an image resrource from it.
	 * @param  array $file Attributes of file from the $_FILES array
	 * @return mixed
	 */
	private function open_image( $file )
	{
		$sfile = new SFile($file);

		// If $file isn't an array, we'll turn it into one
		if ( !is_array($file) ) {
			$file = array(
				'type'		=> $sfile->getMimeType(),
				'tmp_name'	=> $file
			);
		}

		$mime = $file['type'];
		$file_path = $file['tmp_name'];

		switch ( $mime )
		{
			case 'image/pjpeg': // IE6
			case 'image/jpeg':	$img = @imagecreatefromjpeg( $file_path );	break;
			case 'image/gif':	$img = @imagecreatefromgif( $file_path );	break;
			case 'image/png':	$img = @imagecreatefrompng( $file_path );	break;
			default:				$img = false;								break;
		}

		return $img;
	}

	/**
	 * Return the image dimentions based on the option that was chosen.
	 * @param  int    $new_width  The width of the image
	 * @param  int    $new_height The height of the image
	 * @param  string $option     Either exact, portrait, landscape, auto or crop.
	 * @return array
	 */
	private function get_dimensions( $new_width , $new_height , $option )
	{
		switch ( $option )
		{
			case 'exact':
				$optimal_width	= $new_width;
				$optimal_height	= $new_height;
				break;
			case 'portrait':
				$optimal_width	= $this->get_size_by_fixed_height( $new_height );
				$optimal_height	= $new_height;
				break;
			case 'landscape':
				$optimal_width	= $new_width;
				$optimal_height	= $this->get_size_by_fixed_width( $new_width );
				break;
			case 'auto':
				$option_array	= $this->get_size_by_auto( $new_width , $new_height );
				$optimal_width	= $option_array['optimal_width'];
				$optimal_height	= $option_array['optimal_height'];
				break;
			case 'fit':
				$option_array	= $this->get_size_by_fit( $new_width , $new_height );
				$optimal_width	= $option_array['optimal_width'];
				$optimal_height	= $option_array['optimal_height'];
				break;
			case 'crop':
				$option_array	= $this->get_optimal_crop( $new_width , $new_height );
				$optimal_width	= $option_array['optimal_width'];
				$optimal_height	= $option_array['optimal_height'];
				break;
		}

		return array(
			'optimal_width'		=> $optimal_width,
			'optimal_height'	=> $optimal_height
		);
	}

	/**
	 * Returns the width based on the image height
	 * @param  int    $new_height The height of the image
	 * @return int
	 */
	private function get_size_by_fixed_height( $new_height )
	{
		$ratio		= $this->width / $this->height;
		$new_width	= $new_height * $ratio;

		return $new_width;
	}

	/**
	 * Returns the height based on the image width
	 * @param  int    $new_width The width of the image
	 * @return int
	 */
	private function get_size_by_fixed_width( $new_width )
	{
		$ratio		= $this->height / $this->width;
		$new_height	= $new_width * $ratio;

		return $new_height;
	}

	/**
	 * Checks to see if an image is portrait or landscape and resizes accordingly.
	 * @param  int    $new_width  The width of the image
	 * @param  int    $new_height The height of the image
	 * @return array
	 */
	private function get_size_by_auto( $new_width , $new_height )
	{
		// Image to be resized is wider (landscape)
		if ( $this->height < $this->width )
		{
			$optimal_width	= $new_width;
			$optimal_height	= $this->get_size_by_fixed_width( $new_width );
		}
		// Image to be resized is taller (portrait)
		else if ( $this->height > $this->width )
		{
			$optimal_width	= $this->get_size_by_fixed_height( $new_height );
			$optimal_height	= $new_height;
		}
		// Image to be resizerd is a square
		else
		{
			if ( $new_height < $new_width )
			{
				$optimal_width	= $new_width;
				$optimal_height	= $this->get_size_by_fixed_width( $new_width );
			}
			else if ( $new_height > $new_width )
			{
				$optimal_width	= $this->get_size_by_fixed_height( $new_height );
				$optimal_height	= $new_height;
			}
			else
			{
				// Sqaure being resized to a square
				$optimal_width	= $new_width;
				$optimal_height	= $new_height;
			}
		}

		return array(
			'optimal_width'		=> $optimal_width,
			'optimal_height'	=> $optimal_height
		);
	}

	/**
	 * Resizes an image so it fits entirely inside the given dimensions.
	 * @param  int    $new_width  The width of the image
	 * @param  int    $new_height The height of the image
	 * @return array
	 */
	private function get_size_by_fit( $new_width , $new_height )
	{

		$height_ratio	= $this->height / $new_height;
		$width_ratio	= $this->width /  $new_width;

		$max = max( $height_ratio , $width_ratio );

		return array(
			'optimal_width'		=> $this->width / $max,
			'optimal_height'	=> $this->height / $max,
		);
	}

	/**
	 * Attempts to find the best way to crop. Whether crop is based on the
	 * image being portrait or landscape.
	 * @param  int    $new_width  The width of the image
	 * @param  int    $new_height The height of the image
	 * @return array
	 */
	private function get_optimal_crop( $new_width , $new_height )
	{
		$height_ratio	= $this->height / $new_height;
		$width_ratio	= $this->width /  $new_width;

		if ( $height_ratio < $width_ratio ) {
			$optimal_ratio = $height_ratio;
		} else {
			$optimal_ratio = $width_ratio;
		}

		$optimal_height	= $this->height / $optimal_ratio;
		$optimal_width	= $this->width  / $optimal_ratio;

		return array(
			'optimal_width'		=> $optimal_width,
			'optimal_height'	=> $optimal_height
		);
	}

	/**
	 * Crops an image from its center
	 * @param  int    $optimal_width  The width of the image
	 * @param  int    $optimal_height The height of the image
	 * @param  int    $new_width      The new width
	 * @param  int    $new_height     The new height
	 * @return true
	 */
	private function crop( $optimal_width , $optimal_height , $new_width , $new_height )
	{
		// Find center - this will be used for the crop
		$crop_start_x = ( $optimal_width  / 2 ) - ( $new_width  / 2 );
		$crop_start_y = ( $optimal_height / 2 ) - ( $new_height / 2 );

		$crop = $this->image_resized;

		$dest_offset_x	= max( 0, -$crop_start_x );
		$dest_offset_y	= max( 0, -$crop_start_y );
		$crop_start_x	= max( 0, $crop_start_x );
		$crop_start_y	= max( 0, $crop_start_y );
		$dest_width		= min( $optimal_width, $new_width );
		$dest_height	= min( $optimal_height, $new_height );

		// Now crop from center to exact requested size
		$this->image_resized = imagecreatetruecolor( $new_width , $new_height );

		imagealphablending( $crop , true );
		imagealphablending( $this->image_resized , false );
		imagesavealpha( $this->image_resized , true );

		imagefilledrectangle( $this->image_resized , 0 , 0 , $new_width , $new_height,
			imagecolorallocatealpha( $this->image_resized , 255 , 255 , 255 , 127 )
		);

		imagecopyresampled( $this->image_resized , $crop , $dest_offset_x , $dest_offset_y , $crop_start_x , $crop_start_y , $dest_width , $dest_height , $dest_width , $dest_height );

		return true;
	}
}