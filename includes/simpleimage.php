<?php
class SimpleImage {
   var $si_image;
   var $si_image_type;
   var $si_image_info;
   function si_load($filename) {
   		$this->si_image_info = getimagesize($filename);
      	$this->si_image_type = $this->si_image_info[2];
      	if( $this->si_image_type == IMAGETYPE_JPEG ) {
          	$this->si_image = imagecreatefromjpeg($filename);
      	} elseif( $this->si_image_type == IMAGETYPE_GIF ) {
          	$this->si_image = imagecreatefromgif($filename);
      	} elseif( $this->si_image_type == IMAGETYPE_PNG ) {
        	$this->si_image = imagecreatefrompng($filename);
      }
   }
   function si_save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->si_image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->si_image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->si_image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function si_output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->si_image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->si_image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->si_image);
      }
   }
   function si_getWidth() {
 
      return imagesx($this->si_image);
   }
   function si_getHeight() {
 
      return imagesy($this->si_image);
   }
   function si_resizeToHeight($height) {
 
      $ratio = $height / $this->si_getHeight();
      $width = $this->si_getWidth() * $ratio;
      $this->si_resize($width,$height);
   }
   function si_resizeToWidth($width) {
      $ratio = $width / $this->si_getWidth();
      $height = $this->si_getheight() * $ratio;
      $this->si_resize($width,$height);
   }
   function si_scale($scale) {
      $width = $this->si_getWidth() * $scale/100;
      $height = $this->si_getheight() * $scale/100;
      $this->si_resize($width,$height);
   }
   function si_resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->si_image, 0, 0, 0, 0, $width, $height, $this->si_getWidth(), $this->si_getHeight());
      $this->si_image = $new_image;
   }      
}
?> 
