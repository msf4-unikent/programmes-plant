<?php 

// Figure out what we are viewing year/type for any given page
class Mode {

	private static $year;
	private static $type;

	public static function get_type(){
        static::load();
		return static::$type;

	}

	public static function get_year(){
		static::load();
		return static::$year;
	}

	protected static function load(){
		static::$year = '2014';
		static::$type = 'ug';

		if (is_numeric(URI::segment(1))){
			 static::$year = URI::segment(1);
			 static::$type = URI::segment(2);
		}else{
			 static::$type = URI::segment(1);
		} 

	}

}