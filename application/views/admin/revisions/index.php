<div style='padding:10px;height:30px;' class='alert <?php if($programme->live=='2'):?>alert-success<?php else:?>alert-info<?php endif;?> alert-block'>    
  <div style='float:right;'>
    <a class="btn btn-info" href="<?php echo  action(URI::segment(1).'/'.URI::segment(2).'/'.URI::segment(3).'@edit', array($programme->id))?>" >Return to edit form</a>
  </div>
</div>

<h1><?php echo $programme->{Programme::get_title_field()}; ?><?php echo isset($programme->award->name) ? ' - <em>'.$programme->award->name.'</em>' : '' ; ?></h1>

<h3>Active revisions</h3>

<?php
// Loop through revisions (display modes for active and previous are different).
foreach ($revisions as $revision)
{
  echo View::make('admin.revisions.partials.active_revision', array('revision' => $revision, 'programme' => $programme))->render();
  
  //After live switch mode to "non-active"
  if($revision->status == 'live')
  {
    break;
  }
}
?>

<a class="btn btn-danger" href="<?php echo  action(URI::segment(1).'/'.URI::segment(2).'/'.URI::segment(3).'@rollback', array($programme->id))?>" >Emergency rollback options</a>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

<div class="modal hide fade" id="make_revision_live">
<div class="modal-header">
  <a class="close" data-dismiss="modal">×</a>
  <h3>Are you sure?</h3>
</div>
<div class="modal-body">
  <p>This will make the currenty selected revision live, meaning it will be visable on the course pages.</p>
  <p>Are you sure you want to do this?</p>
</div>
<div class="modal-footer">
    <a data-dismiss="modal" href="#" class="btn">Not right now</a>
    <a class="btn btn-danger yes_action">Make live</a>
</div>
</div>

<div class="modal hide fade" id="use_previous">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Are you sure?</h3>
  </div>
  <div class="modal-body">
    <p>This will revert the active copy of this page to the previous version</p>
    <p>Are you sure you want to do this?</p>
  </div>
  <div class="modal-footer">
      <a data-dismiss="modal" href="#" class="btn">Not right now</a>
      <a class="btn btn-danger yes_action">Revert</a>
  </div>
</div>

<div class="modal hide fade" id="use_revision">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Are you sure?</h3>
  </div>
  <div class="modal-body">
    <p>This will set the active copy of this page to the selected revision</p>
    <p>Are you sure you want to do this?</p>
  </div>
  <div class="modal-footer">
      <a data-dismiss="modal" href="#" class="btn">Not right now</a>
      <a class="btn btn-danger yes_action">Use revision</a>
  </div>
</div>

       
