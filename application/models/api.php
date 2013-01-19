<?php

class API {
	
	/**
	 * Return the programmes index
	 *
	 * @param year year to get index for
	 * @param level ug|pg
	 * @return array Index of programmes
	 */
	public static function get_index($year, $level = 'ug'){
		// Get index of programmes
		return Programme::get_api_index($year);
	}

	/**
	 * Return the programmes item from the API
	 *
	 * @param id ID of programme
	 * @param year year to get index for
	 * 
	 * @return programme data array
	 */
	// Get a fully combined programme 
	public static function get_programme($id, $year){

		// Get basic data set
		$globals 				= GlobalSetting::get_api_data($year);	
		$programme_settings 	= ProgrammeSetting::get_api_data($year);
		$programme 				= Programme::get_api_programme($id, $year);

		if($globals === false || $programme_settings === false){
			// Error A: No live versions of globals or settings
			// Maybe throw exception here, or return status code?
			return false;
		}
		if($programme === false){
			// Error B: Programme not published.
			// Maybe throw exception here, or return status code?
			return false;
		}

		// Start combineing to create final super object for output
		// Use globals as base
		$final = $globals;
		
		// Then add in values from settings
		foreach($programme_settings as $key => $value)
		{
			$final[$key] = $value;
		}

		// Pull in all programme dependencies eg an award id 1 will pull in all that award's data.
		// Loop through them, adding them to the $final object.
		$programme = API::load_external_data($programme);

		// Add in all values from main programme
		// Only overwrite values previously added from "settings" when they are not blank
		foreach($programme as $key => $value)
		{
			// Make sure any existing key in the $final object gets updated with the new $value.
			if(!empty($value) ){
				$final[$key] = $value;
			}
		}

		// Remove unwanted attributes
		foreach(array('id','global_setting_id') as $key)
		{
			unset($final[$key]);
		}
		
		// Now remove IDs from our field names, they're not necessary and return.
		// e.g. 'programme_title_1' simply becomes 'programme_title'.
		return static::remove_ids_from_field_names($final);;
	}

	/**
	 * Removes the automatically generated field ids from our field names.
	 * 
	 * @param $record Record to remove field ids from.
	 * @return $new_record Record with field ids removed.
	 */
	public static function remove_ids_from_field_names($record)
	{
		$new_record = array();
		
		foreach ($record as $name => $value) 
		{
			$new_record[preg_replace('/_\d{1,3}$/', '', $name)] = $value;
		}

		return $new_record;
	}

	/**
	 * look through the passed in record and substitute any ids with data from the correct table
	 * 
	 * @param $record The record
	 * @return $new_record A new record with ids substituted
	 */
	public static function load_external_data($record)
	{
		// get programme fields (mapping of columns to their datatypes)
		$programme_fields =  ProgrammeField::get_api_data();

		// For each column with a special data type, update its value in the record;
		foreach($programme_fields as $field_name => $data_type){
			$record[$field_name] = $data_type::replace_ids_with_values($record[$field_name]);
		}

		return $record;
	}

	/**
	 * Function to convert feed to XML (in case we ever need to)
	 * 
	 * @param $data Data to show as XML
	 * @return Raw XML
	 */ 
	public static function array_to_xml($data, $xml = false){

		if ($xml === false)
		{
			$xml  = new SimpleXMLElement('<?xml version="1.0" encoding="'.Config::get('application.encoding').'"?><response/>');
		}

		foreach($data as $key => $value)
		{
			if(is_int($key)) $key = 'item'; //Else will use 1/2/3/etc which is invalid xml

			if (is_array($value))
			{
				static::array_to_xml($value, $xml->addChild($key));
			}
			else
			{	
				$xml->addChild($key, $value);
			}
		}

		return $xml->asXML();
	}




}