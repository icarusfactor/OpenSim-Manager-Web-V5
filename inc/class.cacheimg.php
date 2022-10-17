<?php
/**
 * @package  Cache HTML Img
 */

class CacheIMG
{

        //public variable for : cache directory : cache time
	/**
        * public variables          
        */

       # public function __construct($cache_dir) {
       #  $this->cache_dir = $cache_dir;
       # }

        //Need to add function to pull image name and ext from url and use it as cahce file name. 

		
	/* Gets the cache file if it exists, otherwise grabs and cache url */
	function get_cache($file,$url,$hours = 24) {

        require 'inc/config.php';

        $cached_file = $cache_dir . $file;
        error_log("LOCATION:".$cached_file );

        $current_time = time(); /* Get Current time */
        $expire_time = $hours * 60 * 60; /* 60 sec x 60 mins x hours to get expire time */
        $file_time = filemtime( $cached_file  ); /* Get file change time */

        /* START BLOCK: Check if file exist and if matches expire time , if it does update content,if not send cache content  */
        if(file_exists( $cached_file) && ($current_time - $expire_time < $file_time)) {
                return file_get_contents( $cached_file);
        }
        else {
                $content = $this->get_url($url);
                file_put_contents($cached_file,$content);
                return $content;
        } /* END BLOCK  */

	} /* End get_cache*/

	/* gets content from a URL via curl */
	function get_url($url) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
	}
 
	
}  //End of class CacheIMG
           
