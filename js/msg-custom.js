//////////////////////Inbox Actions//////////////////////////////
jQuery( document ).ready(function( $ ) {

  //Check all
  $( "#select-all" ).click(function() {
    if($(this).is(':checked')) {
      $('.selector').attr('checked', true);
    }else {
      $('.selector').attr('checked', false);
    }
  });
  //Settings
  $( "#settings" ).click(function() {
    let url = window.location.href;
    window.location.href = url+"?settings";
  });
  //Delete
  $( "#delete" ).click(function() {
    //Delete all previously added itemIDs
    $('.delete-items').remove();
    //Check selected items and get their ids
    let items = $( ".selector:checked" );
    if(items.length > 0) {
      $(items).each(function() {
        $( "#delete-form" ).append('<input class="delete-items" name="delete-items[]" type="hidden" value="'+$(this).val()+'">');
      });
      $( "#delete-form" ).submit();
    }
  });
  //Mark as unread
  $( "#unread" ).click(function() {
    //Delete all previously added itemIDs
    $('.unread-items').remove();
    //Check selected items and get their ids
    let items = $( ".selector:checked" );
    if(items.length > 0) {
      $(items).each(function() {
        $( "#unread-form" ).append('<input class="unread-items" name="unread-items[]" type="hidden" value="'+$(this).val()+'">');
      });
      $( "#unread-form" ).submit();
    }
  });
  //Mark as read
  $( "#read" ).click(function() {
    //Delete all previously added itemIDs
    $('.read-items').remove();
    //Check selected items and get their ids
    let items = $( ".selector:checked" );
    if(items.length > 0) {
      $(items).each(function() {
        $( "#read-form" ).append('<input class="read-items" name="read-items[]" type="hidden" value="'+$(this).val()+'">');
      });
      $( "#read-form" ).submit();
    }
  });

  //Update email preferences
  $( ".update-email" ).click(function() {
    //Set action and email accordint to clicked element
    var id = $(this).attr("id");
    var siteUrl = $('#site-url').val();
    if (id == "update-chosen-email") {
      var email = $( "input[name='chosen-email']" ).val();
      var action = 'chosen';
    }else {
      var email = $( "input[name='notification-email']" ).val();
      var action = 'notification';
    }
    //Delete previous messages
    $( "#action-error" ).text('');
    $( "#action-success" ).text('');
    $( "#action-error" ).css('display', "none");
    $( "#action-success" ).css('display', "none");
    //check if the new email is valid
    function checkEmail(email) {
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return regex.test(email);
    }
    var isEmail = checkEmail(email);
    if (isEmail) {
      $.post( siteUrl+"/inbox", { update_preference : true , action: action, email : email }).done(function(data) {
          $( "#action-success" ).text('You have successfully updated your email!');
          $( "#action-success" ).css('display', "block");
        }).fail(function(data) {
          $( "#action-error" ).text('There was an error during the process, please try again!');
          $( "#action-error" ).css('display', "block");
        });
    }else {
      $( "#action-error" ).text('Please enter a valid email address!');
      $( "#action-error" ).css('display', "block");
    }

  });


  var special = [ "t1", "t2", "t3", "t4", "t5", "t6", "t7", "all", "default" ];
  $("#message-to").on('change', function() {

    //Chech if selected not one of the specific group
    if (  $.inArray( $(this).val(), special ) =='-1' ){
      var userId = $(this).val();
      var userName = $('option[value='+userId+']').text();
      var html = '<input data-id="'+userId+'" type="hidden" name="message-to[]" value="'+userId+'"><p data-id="'+userId+'" class="selected-items">'+userName+' <span data-id="'+userId+'" class="remove dashicons dashicons-no"></span></p>';
      //Add it to a list
      $('.selected-ids').append(html);
      //Disable the special value field like all/teritories
      $.each(special, function( index, value ) {
        $('option[value='+value+']').prop('disabled', true);
      });
      //Remove name from select as we will use the hidden inputs instead

    }

  })
  //Remove selected value from list
  $(".selected-ids").on('click', '.remove', function() {
      var dataID = $(this).attr('data-id');
      $("*[data-id="+dataID+"]").remove();
      if ($('.selected-ids').children().length == 0) {

        $.each(special, function( index, value ) {
          $('option[value='+value+']').prop('disabled', false);
        });
      }

  })

});
