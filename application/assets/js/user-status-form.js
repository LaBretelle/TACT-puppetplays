$(document).ready(() => {
  $(document).on('click', '.edit-status', (event) => {
    event.preventDefault()
    const id = event.target.dataset.statusId
    const url = Routing.generate('status_form_get', {
      id: id
    })
    $.ajax({
      url: url,
      type: 'GET',
      async: true,
      success: (data) => {
        $('#status-modal-body').html(data)
        $('#status-modal').modal('show')
      }
    })
    return false
  })
})
