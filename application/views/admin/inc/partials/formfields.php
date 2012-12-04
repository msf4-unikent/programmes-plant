<?php 
/*
Text Fields
*/

foreach($sections as $section_name => $section)
{
  echo "<legend>{$section_name}</legend>";

  foreach($section as $field)
  {
      //Get Column Name
      $column_name = $field->colname;
      $type = $field->field_type;

      $current_value = '';
      if (!$create && isset($programme->$column_name))
      {
          $current_value = $programme->$column_name;
      }

      //Build select box
      if($type=='select')
      {
        $options_list = explode(',',$field->field_meta);
        $form_element = Form::$type($column_name, array_combine( $options_list, $options_list), $current_value);
      }
      else if($type=='checkbox')
      {  
        $form_element = Form::hidden($column_name, 'false');//Provides default value
        $form_element .= Form::$type($column_name, 'true', ($current_value=='true') ? true : false);
      }
      else if($type=='table_select')
      {
        $model = $field->field_meta;
        $form_element = Form::select($column_name, $model::all_as_list($year), $current_value);

      }
      else if($type=='table_multiselect')
      {
        $model = $field->field_meta;
        $form_element = ExtForm::multiselect($column_name.'[]', $model::all_as_list($year), explode(',',$current_value), array('style'=>'height:200px;width:420px;'));
      }
      else if ($type == 'help')
      {
        // Do nothing
      }
      else
      {
        if($current_value == '' && $field->prefill == 1) $current_value = $field->field_initval;
        $form_element = Form::$type($column_name, $current_value, array('placeholder'=>$field->placeholder));
      }
?>
  <?php if ($type != 'help') : ?> 
  <div class="control-group">
              <?php echo Form::label($column_name, $field->field_name,array('class'=>'control-label'))?>
              <div class="controls">
                <?php echo $form_element?>
                <?php if(isset($field->programme_field_type) && $field->programme_field_type == ProgrammeField::$types['OVERRIDABLE_DEFAULT']): ?>
                  <div class="info">
                    <span class="badge badge-info" rel="popover"><i class="icon-flag icon-white"></i>
                      <?php if(isset($from) && strcmp($from, 'programmes') == 0): ?>
                        <?php echo __('fields.form.programme_overwrite_text_title')?>
                      <?php elseif (isset($from) && strcmp($from, 'programmesettings') == 0): ?>
                        <?php echo __('fields.form.programme_settings_overwrite_text_title')?>
                      <?php endif; ?>
                    </span>
                    <div class="title">
                      <?php if(isset($from) && strcmp($from, 'programmes') == 0): ?>
                        <?php echo __('fields.form.programme_overwrite_text_title')?>
                      <?php elseif (isset($from) && strcmp($from, 'programmesettings') == 0): ?>
                        <?php echo __('fields.form.programme_settings_overwrite_text_title')?>
                      <?php endif; ?>
                    </div>
                    <div class="description">
                      <?php if(isset($from) && strcmp($from, 'programmes') == 0): ?>
                        <?php echo __('fields.form.programme_overwrite_text')?>
                      <?php elseif (isset($from) && strcmp($from, 'programmesettings') == 0): ?>
                        <?php echo __('fields.form.programme_settings_overwrite_text')?>
                      <?php endif; ?>
                      
                      <?php if(isset($from) && strcmp($from, 'programmes') == 0): ?>
                        <?php if(ProgrammeSetting::get_setting($year, $field->colname) != null): ?>
                          <br />i.e. <br /> <pre><?php echo ProgrammeSetting::get_setting($year, $field->colname) ?></pre>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
                <span class="help-block"><?php echo  $field->field_description; ?></span>
              </div>
            </div>
<?php else: ?>
    <p>
      <?php echo $field->field_description; ?>
    </p>
<?php endif; ?>
<?php
  } // End fields foreach
} // End sections foreach 
?>
