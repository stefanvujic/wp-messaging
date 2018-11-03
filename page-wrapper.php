<?php
global $wpdb;
//TODO: Update query to contain all relevant surgery including the ones without email set
$users = $wpdb->get_results("SELECT u.ID as ID, um1.meta_value as 'dr', um2.meta_value as 'surgery', um3.meta_value as 'email'  FROM wp_users u
    LEFT JOIN wp_usermeta um1
    	ON um1.user_id = u.ID
      AND um1.meta_key = 'Forte ID'
    LEFT JOIN wp_usermeta um2
    	ON um2.user_id = u.ID
        AND um2.meta_key = 'Surgery'
    LEFT JOIN wp_usermeta um3
    	ON um3.user_id = u.ID
      AND um3.meta_key = 'FD Chosen Email'
    WHERE um1.meta_value IS NOT NULL
    AND (um3.meta_value IS NOT NULL AND um3.meta_value != '' )
    ORDER BY um1.meta_value ASC
    ");

function users_by_territory($territory) {
  global $wpdb;
  $sql = "SELECT u.ID as ID, um3.meta_value as 'email'  FROM wp_users u
    LEFT JOIN wp_usermeta um2
    	ON um2.user_id = u.ID
        AND um2.meta_key = 'Terr No.'
    LEFT JOIN wp_usermeta um3
    	ON um3.user_id = u.ID
      AND um3.meta_key = 'FD Chosen Email'
    WHERE um2.meta_value LIKE '%".$territory."%'
    AND (um3.meta_value IS NOT NULL AND um3.meta_value != '' )";
  $users = $wpdb->get_results($sql);

  return $users;
}

//Add notification to client_inbox by territory
function add_notification_to_db ($territory) {
  global $wpdb;
  $importance = sanitize_text_field($_POST['importance']);
  $subject = sanitize_text_field($_POST['message-title']);
  $message_content = $_POST['message-content'];
  $received = time();
  $sql ="INSERT INTO client_inbox (user_id, importance, subject, message, received, seen, opened )
        SELECT ID AS user_id, '".$importance."' AS importance, '".$subject."' AS subject, '".$message_content."' AS message, '".$received."' AS received, '0' AS seen, '0' AS opened
        FROM wp_users
        LEFT JOIN wp_usermeta um
             ON um.user_id = ID
             AND um.meta_key = 'Terr No.'
         WHERE um.meta_value LIKE '".$territory."'";

  $wpdb->query($sql);

}

//Add notification to client_inbox to every user
function add_notification_to_all () {
  global $wpdb;
  $importance = sanitize_text_field($_POST['importance']);
  $subject = sanitize_text_field($_POST['message-title']);
  $message_content = $_POST['message-content'];
  $received = time();
  $sql ="INSERT INTO client_inbox (user_id, importance, subject, message, received, seen, opened )
        SELECT ID AS user_id, '".$importance."' AS importance, '".$subject."' AS subject, '".$message_content."' AS message, '".$received."' AS received, '0' AS seen, '0' AS opened
        FROM wp_users";

  $wpdb->query($sql);

}

//Save massage in clinet_inbox - indiviadual user
function save_to_inbox( $user_id) {
  global $wpdb;
  $importance = sanitize_text_field($_POST['importance']);
  $subject = sanitize_text_field($_POST['message-title']);
  $message_content = $_POST['message-content'];
  $received = time();
  //Save data
  $wpdb->insert(
    'client_inbox',
    array(
      'user_id' => $user_id,
      'importance' => $importance,
      'subject' => $subject,
      'message' => $message_content,
      'received' => $received,
      'seen' => 0,
    )
  );
}

function send_email($to, $users) {

    $message = "<p>You received a new message from Forte.<br><br>Please <a href='".$url."'>sign in</a> to your account to see your new message.</p><br>";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: Forte <messages@forte.co.uk>';

    //Send to everyone
    //TODO: uncomment loops
    if ($to == 'all') {
      foreach ($users as $key => $member) {
        $headers[] = "Bcc: ".$member->email;
      }
      $to = 'alert@forte.co.uk';

    //To territory
    }elseif ($to = 'territory') {
      foreach ($users as $key => $member) {
        $headers[] = "Bcc: ".$member->email;
      }
      $to = 'alert@forte.co.uk';
    }

    $url = get_site_url();

    if (  wp_mail( $to , $subject = 'You have a new message from Forte', $message, $headers)  ) {
      $msg = '<div class="notice notice-success is-dismissible">
          <p>Your message was sent!</p>
      </div>';

    }else {
        $msg = '<div class="notice notice-error is-dismissible">
            <p>There was an error while sending your message!</p>
        </div>';

    }
    return $msg;
}


if (isset($_POST['send'])) {
  //Send notification email
  switch ($_POST["message-to"]) {
    case 'all':
        $alert = send_email('all', $users);
        echo $alert;
        add_notification_to_all();
        break;
    case 't1':
        $users = users_by_territory("Territory 1");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 1');
        echo $alert;
        break;

    case 't2':
        $users = users_by_territory("Territory 2");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 2');
        echo $alert;
        break;

    case 't3':
        $users = users_by_territory("Territory 3");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 3');
        echo $alert;
        break;

    case 't4':
        $users = users_by_territory("Territory 4");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 4');
        echo $alert;
        break;

    case 't5':
        $users = users_by_territory("Territory 5");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 5');
        echo $alert;
        break;

    case 't6':
        $users = users_by_territory("Territory 6");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 6');
        echo $alert;
        break;

    case 't7':
        $users = users_by_territory("Territory 7");
        $alert = send_email('territory', $users);
        add_notification_to_db('Territory 7');
        echo $alert;
        break;

    default:

        foreach ($_POST['message-to'] as $key => $value) {

          $user_id = sanitize_text_field($value);
          $to = get_user_meta($user_id, 'FD Chosen Email', true);
          // $alert = send_email($to, 0);
          // echo $alert;
          save_to_inbox($user_id);
        }

  }
}

?>

<div class="wrap">
  <h1>Internal Messaging</h1>
  <form class="" action="" method="post">
    <table class="form-table">
      <tbody>
        <tr valign="top">
  				<th scope="row" class="titledesc">
  					<label for="message-to">To:</label>
  				</th>
          <td class="forminp forminp-select">
  					<select name="message-to" id="message-to" class="wc-enhanced-select enhanced message-form-content" tabindex="-1" aria-hidden="true">
              <option value="default" selected="selected">Please select recipient</option>
              <option value="all">Every Memeber</option>
              <option value="t1">Territory 1</option>
              <option value="t2">Territory 2</option>
              <option value="t3">Territory 3</option>
              <option value="t4">Territory 4</option>
              <option value="t5">Territory 5</option>
              <option value="t6">Territory 6</option>
              <option value="t7">Territory 7</option>
              <option disabled="disabled">──────────────────────────────</option>
              <?php
              for ($i=0; $i < count($users); $i++) {
                $all_emails[] = $users[$i]->email;
                echo '<option value="'.$users[$i]->ID.'">'.$users[$i]->dr.' -  '.$users[$i]->surgery.'  -  '.$users[$i]->email.'</option>';
              }

              ?>
  					</select>
            <div class="selected-ids">

            </div>
          </td>
  			</tr>

        <tr valign="top">
  				<th scope="row" class="titledesc">
  					<label for="message-title">Subject</label>
          </th>
  				<td class="forminp forminp-text">
  					<input required name="message-title" id="message-title" type="text" style="" value="" class="message-form-content" placeholder="">
          </td>
  			</tr>

        <tr valign="top">
  				<th scope="row" class="titledesc">
  					<label for="message-title">Importance</label>
          </th>
  				<td class="forminp forminp-text">
            <select name="importance" class="wc-enhanced-select enhanced message-form-content" tabindex="-1" aria-hidden="true">
              <option value="normal">Normal</option>
              <option value="high">High</option>
            </select>
          </td>
  			</tr>

  		</tbody>
    </table>

    <?php wp_editor( '', 'message-content', $settings = array('media_buttons' => false ) ); ?>

    <input type="submit" id="btn-send" class="button button-primary button-large" name="send" value="Send">
  </form>

</div>
<style media="screen">
.updated, .error, .notice-info, .update-nag {
  display: none !important;
}
</style>
