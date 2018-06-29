// Import TinyMCE
import tinymce from 'tinymce/tinymce'
import * as Toastr from 'toastr'

// A theme is also required
import 'tinymce/themes/modern/theme'

// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste'
import 'tinymce/plugins/link'

$(document).ready(() => {

  $('[data-toggle="popover"]').popover()
  $('[data-toggle="tooltip"]').tooltip()

  /*******************
    TOASTER
  *******************/
  Toastr.options = {
    'closeButton': true,
    'debug': false,
    'newestOnTop': true,
    'progressBar': false,
    'positionClass': 'toast-top-right',
    'preventDuplicates': false,
    'onclick': null,
    'showDuration': '300',
    'hideDuration': '1000',
    'timeOut': '5000',
    'extendedTimeOut': '1000',
    'showEasing': 'swing',
    'hideEasing': 'linear',
    'showMethod': 'fadeIn',
    'hideMethod': 'fadeOut'
  }

  $('#flashes .flash').each(function () {
    Toastr.info($(this).data('message'))
  })

  /*******************
    TINYMCE
  *******************/
  tinymce.init({
    selector: 'textarea.tinymce-enabled',
    plugins: ['paste', 'link'],
    setup: (editor) => {
      editor.on('change', function () {
        editor.save()
      })
    }
  })

})
