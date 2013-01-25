<?php

class Fix_Missing_Sections {

	/**
	 * Fix the instances where a programme doesn't have an order.
	 *
	 * @return void
	 */
	public function up()
	{
		$fields = ProgrammeField::programme_fields();

		$sections = array();

		// Loop our fields, placing them into sections.
		foreach ($fields as $field)
		{
			$sections[$field->section][] = $field;
		}

		// We now have the fields in sections, but some of them lack order (unfortunately rather sporadically!)
		// Problem is we want to preserve the order if they lack it, but insert it if they do not.
		foreach ($sections as $section)
		{
			$unordered_fields = array();
			$ordered_fields = array();
			foreach($section as $field)
			{
				// No order is set. Jam it into another array to be appended on the end at the end of this loop.
				if ($field->order == 0)
				{
					$unordered_fields[] = $field;
				}
				else
				{
					$ordered_fields[$field->order] = $field;
				}

				// Sort the ones that have order.
				ksort($ordered_fields);

				$order = count($ordered_fields) + 1;

				// Append the ones that do not.
				foreach ($unordered_fields as $unordered_field)
				{
					$unordered_field->order = $order;
					$unordered_field->save();

					$order++;
				}

			}
		}
	}

	/**
	 * Unset the missing sections.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}