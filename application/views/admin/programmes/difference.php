<h1>Comparing revision <?php echo $revision->get_identifier(); ?> to live</h2>
<p>The following shows the differences between live published <?php echo $programme->get_published_time(); ?> by <?php echo $programme->made_live_by(); ?> and revision <?php echo $revision->get_identifier_string(); ?>.</p>
<table class="table table-striped table-bordered">
  <thead>
    <th></th>
    <th>Live</th>
    <th>Revision <?php echo $revision->get_identifier(); ?></th>
  </thead>
  <tbody>
    <?php foreach ($difference as $field => $value) : ?>
    <tr>
      <td><?php echo $field; ?></td>
      <td><?php echo $value['self']; ?></td>
      <td><?php echo SimpleDiff::html_diff($value['self'], $value['revision']); ?>
    </tr>
    <?php endforeach; ?>
</table>
<div class="form-actions">
  <a class="btn btn-danger promote_toggler" href="#promote_revision" rel="<?php echo  action(URI::segment(1).'/'.URI::segment(2).'/programmes.' . $programme->id . '@promote', array($revision->id))?>">Accept changes and make revision live</a>
  <a class="btn btn-secondary" href="<?php echo url(URI::segment(1).'/'.URI::segment(2).'/programmes/revisions/'.URI::segment(4))?>">Return to revisions</a>
</div>


<div class="modal hide fade" id="promote_revision">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Are you sure?</h3>
  </div>
  <div class="modal-body">
    <p>This will promote this revision to the live version of the programme for this year.</p>
    <p>Are you sure you want to do this?</p>
  </div>
  <div class="modal-footer">
    <?php echo Form::open('programmess/promote', 'POST')?>
      <a data-dismiss="modal" href="#promote_revision" class="btn">Not right now</a>
      <a class="btn btn-danger" id="promote_now">Promote revision</a>
  <?php echo Form::close()?>
  </div>


<script>
$('#promote_revision').modal({
show:false
}); // Start the modal

// Populate the field with the right data for the modal when clicked
$(".promote_toggler").click(function(){
$('#promote_now').attr('href', $(this).attr('rel'));
$('#promote_revision').modal('show');
});
</script>
