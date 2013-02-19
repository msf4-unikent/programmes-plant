<?php

class Simple_Data_Api_Opt_Out {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('awards', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('campuses', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('faculties', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('leaflets', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('schools', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('subjects', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('subjectcategories', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('programmes', function($table){
			$table->boolean('exclude_from_api');
		});
		Schema::table('programmes_revisions', function($table){
			$table->boolean('exclude_from_api');
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('awards', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('campuses', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('faculties', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('leaflets', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('schools', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('subjects', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('subjectcategories', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('programmes', function($table){
			$table->drop_column('exclude_from_api');
		});
		Schema::table('programmes_revisions', function($table){
			$table->drop_column('exclude_from_api');
		});
	}

}