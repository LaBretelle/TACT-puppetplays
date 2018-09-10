$(document).ready(() => {

  let editor = new TinyEditor()
  editor.init()

  $('.btn-logo-delete').on('click', (e) => {
    const url = Routing.generate('admin_platform_logo_delete')
    $.ajax({
      method: 'DELETE',
      url: url
    }).done(function () {
      e.target.closest('.platform-logo-row').remove()
    })
  })
})
