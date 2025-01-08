<?php

namespace App\Http\Traits;


trait NormalImageUpload{

	// Save 
	public function uploadImage($request, $filename, $name, $folder){
		if($request->hasFile($filename)){
		    $image = $request->file($filename);
		    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
		    $image->move("uploads/$folder/", $imageName);
		    return $imageUrl = "uploads/$folder/".$imageName;
		}
	}

	// Update
	public function updateImage($request, $old_image=null, $filename, $name, $folder){
		if($request->hasFile($filename)){
		    if(file_exists($old_image)){
		        unlink($old_image);
		    }

		    $image = $request->file($filename);
		    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
		    $image->move("uploads/$folder/", $imageName);
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
	public function multiImageUpload($request, $filename, $name, $folder){
		if($request->hasFile($filename)){
			// dd($request->multi_image);
			$manager = new ImageManager(new Driver());
			$imageUrl = [];

			foreach ($request->multi_image as $single_image) {
				// dd($single_image);
				$image = $single_image;
			    $imageName = $name.rand(10000, 999999).time().'.'.$image->extension();
			    $img = $manager->read($image);
			    $img = $img->resize(380, 317);
			    // $img->toPng()->save("uploads/$folder/".$imageName);
			    $img->save("uploads/$folder/".$imageName);
			    $imageUrl[] = "uploads/$folder/".$imageName;
			}
			// dd($imageUrl);
			return $imageUrl;
		}
	}



}