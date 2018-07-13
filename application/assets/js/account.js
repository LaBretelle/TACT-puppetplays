$(document).ready(() => {
  Tiny.initEditor()

  $('.btn-delete-account-image').on('click', () => {
    $('.user-account-image-row').empty()
  })
})
