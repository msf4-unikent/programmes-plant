<?php

class Programmes_Controller extends Revisionable_Controller {

	public $restful = true;
	public $views = 'programmes';
	protected $model = 'Programme';

	/**
	 * Routing for /$year/$type/programmes
	 *
	 * @param int    $year The year.
	 * @param string $type Undergraduate or postgraduate.
	 */
	public function get_index($year, $type)
	{
		$title_field = Programme::get_title_field();
		$award_field = Programme::get_award_field();
		$withdrawn_field = Programme::get_withdrawn_field();
		$suspended_field = Programme::get_suspended_field();
		$subject_to_approval_field = Programme::get_subject_to_approval_field();
		$model = $this->model;
		$programmes = $model::with('award')->where('year', '=', $year)->order_by($title_field)->get(array('id', $title_field, $award_field, $withdrawn_field, $suspended_field, $subject_to_approval_field, 'live'));
		
		$this->data[$this->views] = $programmes;

		$this->data['title_field'] = $title_field;
		$this->data['withdrawn_field'] = $withdrawn_field;
		$this->data['suspended_field'] = $suspended_field;
		$this->data['subject_to_approval_field'] = $subject_to_approval_field;

		$this->layout->nest('content', 'admin.'.$this->views.'.index', $this->data);
	}

	/**
	 * Present a form to allow ther creation of a new programme.
	 * If an item_id is passed, present the form prefilled with the item's values
	 *
	 * @param int    $year    The year
	 * @param string $type    Undergraduate or postgraduate.
	 * @param int    $item_id The ID of the programme to clone from.
	 */
	public function get_create($year, $type, $item_id = false)
	{
		if ($item_id)
		{
			// We're cloning item_id
			$model = $this->model;
			$course = $model::find($item_id);
			$this->data['clone'] = true;
			$this->data['programme'] = $course;
		} 
		else 
		{
			$this->data['clone'] = false;
		}
		
		$this->data['sections'] = ProgrammeField::programme_fields_by_section();
		$this->data['campuses'] = Campus::all_as_list();
		$this->data['school'] = School::all_as_list();
		$this->data['awards'] = Award::all_as_list();
		$this->data['programme_list'] = Programme::all_as_list($year);
		$this->data['leaflets'] = Leaflet::all_as_list();

		$this->data['create'] = true;
		$this->data['year'] = $year;

		$this->layout->nest('content', 'admin.'.$this->views.'.form', $this->data);
	}

	/**
	 * Routing for GET /$year/$type/edit/$programme_id
	 *
	 * @param int    $year    The year
	 * @param string $type    Undergraduate or postgraduate.
	 * @param int    $item_id The ID of the programme to edit.
	 */
	public function get_edit($year, $type, $itm_id = false)
	{
		// Do our checks to make sure things are in place
		if(!$itm_id) return Redirect::to($year.'/'.$type.'/'.$this->views);

		// Ensure we have a corresponding course in the database
		$model = $this->model;
		$course = $model::find($itm_id);
		if(!$course) return Redirect::to($year.'/'.$type.'/'.$this->views);

		$this->data['programme'] = $course ;
		
		$this->data['sections'] = ProgrammeField::programme_fields_by_section();
		$this->data['title_field'] = Programme::get_title_field();
		$this->data['year'] = $year;
		$this->data['active_revision'] = $course->get_active_revision();

		//Get lists data
		$this->layout->nest('content', 'admin.'.$this->views.'.form', $this->data);
	}

	/**
	 * Routing for POST /$year/$type/create
	 *
	 * The change request page.
	 *
	 * @param int    $year The year of the created programme.
	 * @param string $type The type, either ug (undergraduate) or pg (postgraduate)
	 */
	public function post_create($year, $type)
	{
		// placeholder for any future validation rules
		$rules = array(
		);
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails()) 
		{
			Messages::add('error',$validation->errors->all());
			return Redirect::to($year.'/'.$type.'/'.$this->views.'/create')->with_input();
		} 
		else 
		{
			$programme = new Programme;
			$programme->year = Input::get('year');
			$programme->created_by = Auth::user();
			
			// get the programme fields
			$programme_fields = ProgrammeField::programme_fields();
			
			// assign the input data to the programme fields
			$programme_modified = ProgrammeField::assign_fields($programme, $programme_fields, Input::all());
			
			// save the modified programme data
			$programme_modified->save();
			
			// success message
			Messages::add('success','Programme added');
			
			// redirect back to the same page
			return Redirect::to($year.'/'.$type.'/'.$this->views.'/edit/'.$programme->id);
		}
	}

	/**
	 * Routing for POST /$year/$type/edit
	 *
	 * Make a change.
	 *
	 * @param int    $year The year of the created programme
	 * @param string $type The type, either ug (undergraduate) or pg (postgraduate)
	 */
	public function post_edit($year, $type)
	{
		// placeholder for any future validation rules
		$rules = array(
		);
		$validation = Validator::make(Input::all(), $rules);
		if ($validation->fails())
		{
			Messages::add('error',$validation->errors->all());
			return Redirect::to($year.'/'.$type.'/'.$this->views.'/edit/')->with_input();
		} 
		else 
		{
			$programme = Programme::find(Input::get('programme_id'));
			$programme->year = Input::get('year');

			// get the programme fields
			$programme_fields = ProgrammeField::programme_fields();
			
			// assign the input data to the programme fields
			$programme_modified = ProgrammeField::assign_fields($programme, $programme_fields, Input::all());

			// save the modified programme data
			$programme_modified->save();
			
			// success message
			$title_field = Programme::get_title_field();
			Messages::add('success', "Saved ".$programme->$title_field);
			
			// redirect back to the same page we were on
			return Redirect::to($year.'/'. $type.'/'. $this->views.'/edit/'.$programme->id);
		}
	}

	/**
	 * Routing for GET /$year/$type/programmes/$programme_id/difference_with_live/$revision_id
	 * 
	 * 
	 *
	 * @param int    $year         The year of the programme (not used, but to keep routing happy).
	 * @param string $type         The type, either ug (undergraduate) or pg (postgraduate) (not used, but to keep routing happy).
	 * @param int    $programme_id The programme ID we are wishing to compare live revision to.
	 * @param int    $revision_id  The revision of that programme which we wish to compare to the live revision.
	 */
	public function get_difference_with_live($year, $type, $programme_id = false, $revision_id = false)
	{
		$live_revision_id = Programme::get_live_revision_id($programme_id);

		$programme = Programme::find($programme_id);
		$live = $programme->get_revision($live_revision_id);

		$revision = $programme->get_revision($revision_id);

		$difference = Programme::differences_between_revisions($live, $revision, true);

		if ($difference == null)
		{
			Repsonse::error(404);
		}

		$this->data['difference'] = $difference['difference'];
		$this->data['revision'] = $programme;

		$this->data['live'] = $difference['revision'];

		// Establish the information about the fields that have changed and pass them to the view.
		$this->data['programme_fields'] = array();

		foreach(array_keys($this->data['difference']) as $field_column)
		{
			$field = ProgrammeField::where('colname', '=', $field_column)->first();
			$this->data['programme_fields'][$field_column] = $field;

			unset($field); 
		}

		$this->layout->nest('content', 'admin.'.$this->views.'.difference', $this->data);
	}

	/**
	 * Routing for GET /changes
	 *
	 * The change request page.
	 * 
	 * @todo Update to reflect current revisions methodology.
	 */
	public function get_changes()
	{
		$this->data['revisions'] = DB::table('programmes_revisions')
			->where('status', '=', 'pending')
			->get();

		return View::make('admin.changes.index', $this->data);
	}

}