<?php

class cImage extends cModule {
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// basic backend implementation for an image upload
		// -> this takes an image, and saves it to a "destination"
		// -> returns an associative array, 
		//		if an error occured, the array contains the index 'error'
		//		if no error occured, the array contains information about the image
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function upload($file_input_name, $destination_path, $destination_filename) {
				$retval = array();
				
				if(isset($_FILES[$file_input_name]['tmp_name'])) {
						if(is_uploaded_file($_FILES[$file_input_name]['tmp_name'])) {
								//check that this file is a jpg, png or gif
								$imageinfo = getimagesize($_FILES[$file_input_name]['tmp_name']);
								
								$imagetype = $imageinfo['mime'];
								
								if(empty($imagetype)) {
										$retval['error'] = '11';
										return $retval;
								}
								
								switch($imagetype) {
										case 'image/gif':
												$retval['file_extension'] = '.gif';
												break;
										case 'image/jpeg':
												$retval['file_extension'] = '.jpg';
												break;
										case 'image/png':
												$retval['file_extension'] = '.png';
												break;
										default:
												$retval['error'] = '14';
												return $retval;
												break;
								}
								
								//move the file
								$result = @move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $destination_path . $destination_filename . $retval['file_extension']);
								
								if(empty($result)) {
										$retval['error'] = '12';
										return $retval;
								}
								
								//set the new filename..
								$retval['original_filename'] = $_FILES[$file_input_name]['name'];
								$retval['filename'] = $destination_filename . $retval['file_extension'];
						} else {
								$retval['error'] = '13';
								return $retval;
						}
				} else {
						$retval['error'] = '17';		//uploaded file not found
				}
				
				return $retval;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		// basic backend implementation for an image upload without checking the file-extension and/or
		// file type.
		// -> this takes an image, and saves it to a "destination"
		// -> returns an associative array, 
		//		if an error occured, the array contains the index 'error'
		//		if no error occured, the array contains information about the image
		///////////////////////////////////////////////////////////////////////////////////////////////////
		public static function uploadWithoutCheck($file_input_name, $destination_path, $destination_filename) {
				$retval = array();
				
				if(isset($_FILES[$file_input_name]['tmp_name'])) {
						if(is_uploaded_file($_FILES[$file_input_name]['tmp_name'])) {
								//move the file
								$result = @move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $destination_path . $destination_filename);
								
								if(empty($result)) {
										$retval['error'] = '12';
										return $retval;
								}
								
								//set the new filename..
								$retval['original_filename'] = $_FILES[$file_input_name]['name'];
								$retval['filename'] = $destination_filename;
						} else {
								$retval['error'] = '13';
								return $retval;
						}
				} else {
						$retval['error'] = '17';		//uploaded file not found
				}
				
				return $retval;
		}
}

?>