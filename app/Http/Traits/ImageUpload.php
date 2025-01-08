<?php

namespace App\Http\Traits;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait ImageUpload{

	// Save 
	public function uploadImage($request, $filename='image', $name, $folder, $width=380, $height=317){
		if($request->hasFile($filename)){
			$manager = new ImageManager(new Driver());
		    $image = $request->file($filename);
		    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
		    // dd("uploads/$folder/".$imageName);
		    $manager->read($image)->resize($width, $height)->save("uploads/$folder/".$imageName);
		    return $imageUrl = "uploads/$folder/".$imageName;
		}
	}

	// Update
	public function updateImage($request, $old_image=null, $filename='image', $name, $folder, $width=380, $height=317){
		if($request->hasFile($filename)){
		    if(file_exists($old_image)){
		        unlink($old_image);
		    }

		    // dd(55);

		    $manager = new ImageManager(new Driver());

		    $image = $request->file($filename);
		    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
		    $manager->read($image)->resize($width, $height)->save("uploads/$folder/".$imageName);
		    // dd("uploads/$folder/".$imageName);
		    return $imageUrl = "uploads/$folder/".$imageName;
		}
	}

	// Delete
	public function deleteImage($old_image=null){
	    if(file_exists($old_image)){
	        unlink($old_image);
	    }
	}


	// Save 
	public function multiImageUpload($request, $filename='image', $name, $folder, $width=380, $height=317){
		if($request->hasFile($filename)){
			// dd($request->multi_image);
			$manager = new ImageManager(new Driver());
			$imageUrl = [];

			foreach ($request->multi_image as $single_image) {
				// dd($single_image);
				$image = $single_image;
			    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
			    $manager->read($image)->resize($width, $height)->save("uploads/$folder/".$imageName);
			    $imageUrl[] = "uploads/$folder/".$imageName;
			}
			// dd($imageUrl);
			return $imageUrl;
		}
	}


}