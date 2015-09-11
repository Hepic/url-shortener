<?php
	define('BASE_HREF', 'http://' . $_SERVER['HTTP_HOST'] . '/' . 'url-shortener/');
	
	
	class short_url
	{
		private $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		
		
		public function getShortUrl($id) //convert number '$id' to base strlen($chars)
		{
			$length = strlen($this->chars);
			$short_code = '';

			while($id)
			{
				$short_code = $this->chars[$id % $length] . $short_code;
				$id = floor($id / $length);
			}
										
			return $short_code;
		}
	
	
		public function insertUrl($url) //insert long_url in database
		{
			$date = date('F d, Y');
			
			$query = "INSERT INTO url_shortener (long_url, date_created)
					  VALUES('$url', '$date');";
					  
			mysql_query($query);
		
			return mysql_insert_id();
		}
		
		
		public function insertShortUrl($id, $short_code) //insert short_url in database
		{
			$query = "UPDATE url_shortener SET short_code='$short_code' WHERE id='$id';";
			mysql_query($query);
		}
		
		
		public function insert($user_url)
		{
			$is_valid = $this->isValidUrl($user_url);
			
			if($is_valid) //check if long_url is valid
			{
				$user_short_url = $this->searchUrl($user_url); 

				if($user_short_url == 'no-found') // if that long_url is new, then we make a new short_url
				{	
					$last_id = $this->insertUrl($user_url); //insert long url in db
					$user_short_url = $this->getShortUrl($last_id); //make a short url
					$this->insertShortUrl($last_id, $user_short_url); //insert short url in db
				}

				return BASE_HREF.$user_short_url;
			}
			
			return "No valid url";
		}
		
		
		public function selectUrl($short_code)
		{
			$query = "SELECT * FROM url_shortener WHERE short_code='$short_code';"; //searchs for $short_code that use sent us
			$res = mysql_query($query);
			$num_rows = mysql_num_rows($res);
			
			if($num_rows)
			{
				$row = mysql_fetch_assoc($res);
				$url = $row['long_url'];
				
				header("Location: $url");  //if we find it, we direct him to that website
			}
		}


		public function searchUrl($url)
		{
			$query = "SELECT * FROM url_shortener WHERE long_url='$url' LIMIT 1;"; //searchs if long_url already exists
			$res = mysql_query($query);
			$num_rows = mysql_num_rows($res);
			
			if($num_rows)
			{
				$row = mysql_fetch_assoc($res);
				$user_short_url = $row['short_code'];
				
				return $user_short_url;
			}
			
			return 'no-found';
		}
		
		
		public function isValidUrl($url)
		{
		   if(!filter_var($url, FILTER_VALIDATE_URL)) //check, if a valid url is provided
				return false;

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch); 
			
			if($httpcode == 200)
				return true;
			
			return false;	
		}
	}
?>