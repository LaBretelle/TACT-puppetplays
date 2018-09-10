$(document).ready(() => {
  let editor = new TinyEditor()
  editor.init()

  $('.btn-delete-account-image').on('click', () => {
    $('.user-account-image-row').empty()
  })
})
