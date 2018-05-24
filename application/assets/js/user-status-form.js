import AppRouting from './modules/app-routing.js';

const routing = new AppRouting();

$(document).ready(function() {
  $(document).on('click', '.edit-status', function(event) {
    event.preventDefault();
    var id = $(this).data("status-id");
    var url = routing.generateRoute('status_form_get', {id: id});
    $.ajax({
          url: url,
          type: "GET",
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
