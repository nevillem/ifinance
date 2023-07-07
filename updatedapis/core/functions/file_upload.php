<?php

      function file_upload_image($upload_path,$field_name){
        $randomnumber = rand(1000000000,10000000001);
        $fileName  =  $_FILES[$field_name]['name'];
        $tempPath  =  $_FILES[$field_name]['tmp_name'];
        $fileSize  =  $_FILES[$field_name]['size'];
        $file_name_array = (explode(".", $fileName));
        $f_extension = strtolower(end($file_name_array));
        $NewfileName = md5($randomnumber.$fileName).".$f_extension";

        if(empty($NewfileName))
        {
        	$errorMSG = json_encode(array("message" => "please select image", "status" => false));
        	echo $errorMSG;
        }
        else
        {
        	// $upload_path = 'storage/member-images'; // set upload folder path

        	$fileExt = strtolower(pathinfo($NewfileName,PATHINFO_EXTENSION)); // get image extension

        	// valid image extensions
        	$valid_extensions = array('jpeg', 'jpg', 'png', 'gif','pdf');

        	// allow valid image file formats
        	if(in_array($fileExt, $valid_extensions))
        	{
        		//check file not exist our upload folder path
        		if(!file_exists($upload_path . $NewfileName))
        		{
        			// check file size '2MB'
        			if($fileSize < 2000000){
        				move_uploaded_file($tempPath, $upload_path . $NewfileName); // move file from system temporary path to our upload folder path
        			}
        			else{
        				$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 2 MB size", "status" => false));
        				echo $errorMSG;
        			}
        		}
        		else
        		{
        			$errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));
        			echo $errorMSG;
        		}
        	}
        	else
        	{
        		$errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));
        		echo $errorMSG;
        	}
        }

          return $NewfileName;
      }

      function file_upload_image_document($doc_upload_path,$doc_field_name){
        $randomnumber = rand(1000000000,10000000001);
        $fileName  =  $_FILES[$doc_field_name]['name'];
        $tempPath  =  $_FILES[$doc_field_name]['tmp_name'];
        $fileSize  =  $_FILES[$doc_field_name]['size'];
        $file_name_array = (explode(".", $fileName));
        $f_extension = strtolower(end($file_name_array));
        $NewfileName = md5($randomnumber.$fileName).".$f_extension";
        if(empty($NewfileName))
        {
          $errorMSG = json_encode(array("message" => "please select image", "status" => false));
          echo $errorMSG;
        }
        else
        {
          // $upload_path = 'storage/member-images'; // set upload folder path

          $fileExt = strtolower(pathinfo($NewfileName,PATHINFO_EXTENSION)); // get image extension

          // valid image extensions
          $valid_extensions = array('jpeg', 'jpg', 'png', 'gif','pdf');

          // allow valid image file formats
          if(in_array($fileExt, $valid_extensions))
          {
            //check file not exist our upload folder path
            if(!file_exists($doc_upload_path . $NewfileName))
            {
              // check file size '1MB'
              if($fileSize < 2000000){
                move_uploaded_file($tempPath, $doc_upload_path . $NewfileName); // move file from system temporary path to our upload folder path
              }
              else{
                $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 2 MB size", "status" => false));
                echo $errorMSG;
              }
            }
            else
            {
              $errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));
              echo $errorMSG;
            }
          }
          else
          {
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));
            echo $errorMSG;
          }
        }

          return $NewfileName;
      }
