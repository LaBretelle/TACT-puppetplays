// Import TinyMCE
import tinymce from 'tinymce/tinymce'
import * as Toastr from 'toastr'

// A theme is also required
import 'tinymce/themes/modern/theme'

// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste'
import 'tinymce/plugins/link'

$(document).ready(() => {

  /*******************
    BOOTSTRAP TOOLTIP & POPOVER
  *******************/
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

  $('#flashes .flash').each(function() {
    let flash = $(this)
    let type = flash.data('label')
    let msg = flash.data('message')
    switch (type) {
      case 'notice':
        Toastr.info(msg)
        break
      case 'warning':
        Toastr.warning(msg)
        break
      case 'error':
        Toastr.error(msg)
        break
    }
  })

  /*******************
    TINYMCE
  *******************/
  tinymce.init({
    selector: 'textarea.tinymce-enabled',
    plugins: ['paste', 'link'],
    setup: (editor) => {
      editor.on('change', function() {
        editor.save()
      })
    }
  })

})
