<?php if (is_user_logged_in()) { ?>

<?php
//Get user messages
global $wpdb;
$user_id = get_current_user_id();
//Delete items
if (isset($_POST['delete-items'])) {
  $ids = $_POST['delete-items'];
  foreach ($ids as $k => $id) {
    $wpdb->delete( 'client_inbox', array( 'ID' => $id, 'user_id' => $user_id ) );
  }
}
//Mark as unread
if (isset($_POST['unread-items'])) {
  $ids = $_POST['unread-items'];
  foreach ($ids as $k => $id) {
    $wpdb->update( 'client_inbox', array( 'seen' => 0 ), array( 'ID' => $id, 'user_id' => $user_id ) );
  }
}
//Mark as read
if (isset($_POST['read-items'])) {
  $ids = $_POST['read-items'];
  foreach ($ids as $k => $id) {
    $wpdb->update( 'client_inbox', array( 'seen' => 1 ), array( 'ID' => $id, 'user_id' => $user_id ) );
  }
}
if (isset($_POST['update_preference'])) {
  $action = sanitize_text_field($_POST['action']);
  $email = sanitize_text_field($_POST['email']);
  if ($action == "chosen") {
    //Have to use wpdb intead of update_usermeta cos the meta_key will be trimmed to FDChosenEmail
    $meta_key = 'FD Chosen Email';
    $wpdb->update(
  	'wp_usermeta',
  	array(
  		'meta_value' => $email
  	),
  	array( 'user_id' => $user_id,
           'meta_key' => $meta_key,
    )
  );
  }elseif ($action == "notification") {
    $meta_key = 'notification_emails';
    update_usermeta($user_id, $meta_key , $email);
  }

  die;
}

//Get individual message details
if (isset($_POST['message-id'])) {

  $sql = "SELECT * FROM client_inbox WHERE user_id = ". $user_id . " AND ID =".$_POST['message-id'];
  $message = $wpdb->get_row($sql);
  //update seen and opened status
  if($message->seen == 0) {
    $wpdb->update(
    	'client_inbox',
    	array(
    		'seen' => 1
    	),
    	array( 'ID' => $_POST['message-id'],
             'user_id' => $user_id
     )
    );
  }
  if($message->opened == 0) {
    $wpdb->update(
    	'client_inbox',
    	array(
    		'opened' => time()
    	),
    	array( 'ID' => $_POST['message-id'],
             'user_id' => $user_id
     )
    );
  }
  ?>
  <div class="no-padding-mobile content container">
    <div class="no-padding-mobile col-xs-12" style="margin-bottom:25px;">
      <h2 style="display:inline-block;padding-top:0px;"><?php echo $message->subject; ?></h2><a class="background-blue button btn pull-right" href="#" onclick="window.history.go(-1); return false;">Back To Inbox</a>
      <hr>
      <?php echo wp_unslash($message->message); ?>
    </div>
  </div>

  <?php
//Display all messages
}else if( isset($_GET['settings']) ) {
    ?>
    <div class="no-padding-mobile content container">
      <div class="no-padding-mobile col-xs-12" style="margin-bottom:25px;">
        <h2 style="display:inline-block;padding-top:0px;">Email Preferences</h2><a class="background-blue button btn pull-right" href="#" onclick="window.history.go(-1); return false;">Back To Inbox</a>
        <hr>
      </div>
      <div class="col-md-12 no-padding">
        <p id="action-success"></p>
        <p id="action-error"></p>
      </div>
      <div class="settings-table">

        <table class="table table-striped">
          <thead>
            <tr>
              <th>Type</th>
              <th>Email</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><p>Invoice Notifications</p></td>
              <?php $chosen_email = get_user_meta($user_id , 'FD Chosen Email', true) ?>
              <td> <input type="text" name="chosen-email" value="<?php echo $chosen_email; ?>"> </td>
              <td> <button id="update-chosen-email" class="update-email background-blue button btn-sm" type="button" name="button">Update</button> </td>
            </tr>
            <tr>
              <td><p>General Notifications</p></td>
              <?php $notification_email = get_user_meta($user_id , 'notification_emails', true) ?>
              <td> <input type="text" name="notification-email" value="<?php echo $notification_email; ?>"> </td>
              <td> <button id="update-notification-email" class="update-email background-blue button btn-sm" type="button" name="button">Update</button> </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <?php

}else {
  $sql = "SELECT * FROM client_inbox WHERE user_id = ". $user_id . " ORDER BY received DESC";
  $messages = $wpdb->get_results($sql);
  $count = count($messages);
  // echo "<pre>".print_r($_POST,true)."</pre>";
  ?>
  <div class="no-padding-mobile content container">
  	<div class="no-padding-mobile col-xs-12" style="margin-bottom:25px;">
      <div class="col-md-9">
        <h2 style="padding-top: 0px;" class="font-color-blue news-single-title">My Inbox <?php echo ($count > 0) ? "(".$count.")" : ""; ?></h2>
      </div>
      <div class="col-md-3">
        <span title="Settings" id="settings" class="fa-stack fa-lg inbox-action">
          <i style="color: #2c7fb7;" class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-cog fa-stack-1x fa-inverse"></i>
        </span>
        <span title="Mark as read" id="read" class="fa-stack fa-lg inbox-action">
          <i style="color: #2c7fb7;" class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-envelope-open-o fa-stack-1x fa-inverse"></i>
        </span>
        <span title="Mark as unread" id="unread" class="fa-stack fa-lg inbox-action">
          <i style="color: #2c7fb7;" class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-envelope-o fa-stack-1x fa-inverse"></i>
        </span>
        <span title="Delete" id="delete" class="fa-stack fa-lg inbox-action">
          <i style="color: #2c7fb7;" class="fa fa-square fa-stack-2x"></i>
          <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
        </span>
    </div>


    <?php if ($count > 0): ?>
      <div class="col-md-12">
      <hr>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Received</th>
            <th style="text-align: center;padding-right: 16px;"><input id="select-all" type="checkbox" name="all"></th>
          </tr>
        </thead>
        <tbody class="form-table">
          <?php foreach ($messages as $message => $value): ?>
            <tr>
              <form action="#" method="post">
                <td>
                  <input type="hidden" name="message-id" value="<?php echo $value->ID; ?>">
                  <input type="hidden" name="time" value="<?php echo time(); ?>">
                  <input type="submit" value="<?php echo $value->subject; ?>" class="<?php echo ($value->seen == 0) ? "bold" : ""; ?>">
                </td>
                <td><?php echo date('d/m/Y - H:i:s', $value->received); ?></td>
                <td style="text-align: center;"><input class="selector" type="checkbox" name="selected" value="<?php echo $value->ID; ?>"></td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Helper forms to perform delete and updates -->
      <form id="delete-form" action="" method="post"></form>
      <form id="read-form" action="" method="post"></form>
      <form id="unread-form" action="" method="post"></form>

    <?php else : ?>
      <div class="col-md-12">
        <p>You have no messages</p>
      </div>
    <?php endif; ?>
    </div>
  </div>
  </div> <!-- end of row -->
<?php
}
?>
<?php
}else {
	echo "You must be logged in to view you inbox";
}
?>
