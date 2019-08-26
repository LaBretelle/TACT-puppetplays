import bsCustomFileInput from 'bs-custom-file-input'


$(document).ready(() => {
  /*******************
    BOOTSTRAP TOOLTIP & POPOVER
  *******************/
  $('[data-toggle="popover"]').popover()
  $('[data-toggle="tooltip"]').tooltip()

  $('#flashes .flash').each(function () {
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


  bsCustomFileInput.init()
  
})
