$(document).ready(function() {
  $(document).on('click', '.edit-status', function(event) {
    event.preventDefault();
    var id = $(this).data("status-id");
    $.ajax({
          url:'/index.php/status/'+id+'/form',
          type: "GET",
          //dataType: "json",
          async: true,
          success: function (data)
          {
              $('#status-modal-body').html(data);
              $('#status-modal').modal('show');
          }
      });
      return false;
    });
  });
