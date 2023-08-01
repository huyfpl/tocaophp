<form action="" method="post" class="profile_post3 registration-form">
    <fieldset>

      <p style="color: #580024;">Error</p>
      <textarea rows="1" style="text-align: center;" class="form-control  btn-" name="error1"></textarea><br>
      <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">

      <button type="button" class="btn btn-default btn-primary" name="post_button" id="submit_profile_post3"><p style="color: #580024; margin-bottom: 0px;">Submit</p></button>

      <button type="button" class="btn btn-default" data-dismiss="modal">clos</button>
    </fieldset>
  </form>



  <?php
  if(isset($_POST['post_button'])) {
  echo "   <script>
      $(window).on('load',function(){
          $('#myModal').modal('show');
      });
  </script>";
  } ?>


  <div id='myModal' class='modal fade'>
    <div class='modal-dialog modal-confirm'>
      <div class='modal-content'>
        <div class='modal-header'>
          <div class='icon-box'>
            <i class='material-icons'>&#xE876;</i>
          </div>
          <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
        </div>
        <div class='modal-body text-center'>
          <h4>Great!</h4> 
          <p>Your Message Has Been Reported.</p>
          <button class='btn btn-success' data-dismiss='modal'><span>Done</span> <i class='material-icons'>&#xE5C8;</i></button>
        </div>
      </div>
    </div>
  </div>