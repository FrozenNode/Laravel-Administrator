<?php

class FileHelper
{
	public static function upload($model, $modelName, $name, $attribute, $removePastImage = true, $sizes = array())
	{
		$uploadOptions = $attribute["uploadOptions"];
		$path = "public";
		$beforeImage = $model->$name;
		if (isset($uploadOptions["sizes"])) {
			$sizes = $uploadOptions["sizes"];
		}

		if (isset($uploadOptions["path"])) {
			$path = $uploadOptions["path"];
		}

		$files = Input::file($name);
		if ($files["name"] == "") {
			return false;
		}
		$extension = File::extension($files["name"]);
		$directory = path($path) . $uploadOptions["directory"];
		$nameFile = sha1(Session::has("token_user") . microtime());
		$filename = "$nameFile.{$extension}";
		$fullPath = $directory . "/" . $filename;
		$defaultImage = $directory . "/" . $filename;
		$defaultImageName = $filename;
		$successUpload = Input::upload($name, $directory, $filename);

		if ($successUpload === false) {
			return false;
		}

		if (File::exists($directory . "/" . $beforeImage)) {
			File::delete($directory . "/" . $beforeImage);
		}

		var_dump($beforeImage);
		$beforeExtension = File::extension($beforeImage);
		$preg = $directory . "/" . preg_replace("/\.$beforeExtension/", "", $beforeImage);

		if(!empty($beforeImage)){
			foreach (glob("$preg*", GLOB_ONLYDIR) as $key => $dir) {
				File::rmdir($dir);
			}
		}

		foreach ($sizes as $key => $size) {
			if ( ! preg_match("/\\d*x\\d*/", $size)) {
				throw new Exception("Size doesnt have a valid format valid for $size example: ddxdd", 1);
			}
			if ( ! class_exists("Resizer")) {
				throw new Exception("Bundle Resizer must be installed <br> Please got to <a href='http://bundles.laravel.com/bundle/resizer'>http://bundles.laravel.com/bundle/resizer</a>", 1);
			}

			$filename = $nameFile . "_$key.{$extension}";
			$sizeOptions = preg_split("/x/", $size);
			$fullPath = $directory . "/$nameFile$key/" . $filename;
			$beforeImageWithSize = $directory . "/$nameFile$key/" . $beforeImage;

			if ( ! is_dir($directory . "/" . $nameFile . $key)) {
				mkdir($directory . "/" . $nameFile . $key, 0777);
			}

			$success = Resizer::open($defaultImage)
				->resize($sizeOptions[0], $sizeOptions[1], 'fit')
				->save($fullPath, 90);

			if ($success === false) {
				return false;
			}
		}

		return array("fullPath" => $defaultImage, "fileName" => $defaultImageName);
	}

	private static function fileAttributes($attributes)
	{
		$searchFunc = function ($value) {
			return isset($value['type']) && $value['type'] === 'file';
		};

		return array_filter($attributes, $searchFunc);
	}

}