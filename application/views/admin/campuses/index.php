<?php echo View::make('admin.inc.meta')->render()?>
    <title>Courses Dashboard</title>
  </head>
  <body>
    <?php echo View::make('admin.inc.header')->render()?>
    <div class="container">

      <div class="row-fluid">

        <div class="span3"> <!-- Sidebar -->
          <div class="well">
            <?php echo View::make('admin.inc.sidebar')->render()?>
          </div>
        </div> <!-- /Sidebar -->

        <div class="span9">
          <h1>Campuses</h1>
          <p>Use the table below to edit the campuses available in this system.</p>
          <?php echo Messages::get_html()?>
          <?php
            if($campuses){
              echo '<table class="table table-striped table-bordered table-condensed">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Actions</th>
                </tr>
              </thead><tbody>
              ';
              foreach($campuses as $campus){

                echo '<tr>
                  <td>'.$campus->name.'</td>
                  <td><a class="btn btn-primary" href="'.action(URI::segment(1).'/'.URI::segment(2).'/campuses@edit', array($campus->id)).'">Edit</a> <a class="delete_toggler btn btn-danger" rel="'.$campus->id.'">Delete</a></td>
                </tr>';
              }
              echo '</tbody></table>';
            }else{
              echo '<div class="well"><p>There are no campuses in the system yet. Feel free to add one below.</p></div>';
            }
          ?>


           <div class="form-actions">
          <a href="<?php echo action(URI::segment(1).'/'.URI::segment(2).'/campuses@create')?>" class="btn btn-primary right">New Campus</a>
        </div>
        </div>

      </div>

    </div> <!-- /container -->
    <div class="modal hide fade" id="delete_campus">
      <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3>Are You Sure?</h3>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this campus?</p>
      </div>
      <div class="modal-footer">
        <?php echo Form::open(URI::segment(1).'/'.URI::segment(2).'/campuses/delete', 'POST')?>
        <a data-toggle="modal" href="#delete_campus" class="btn">Keep</a>
        <input type="hidden" name="id" id="postvalue" value="" />
        <input type="submit" class="btn btn-danger" value="Delete" />
        <?php echo Form::close()?>
      </div>
    </div>
    <?php echo View::make('admin.inc.scripts')->render()?>
    <script>
      $('#delete_campus').modal({
        show:false
      }); // Start the modal

      // Populate the field with the right data for the modal when clicked
      $('.delete_toggler').each(function(index,elem) {
          $(elem).click(function(){
            $('#postvalue').attr('value',$(elem).attr('rel'));
            $('#delete_campus').modal('show');
          });
      });
    </script>
  </body>
</html>
