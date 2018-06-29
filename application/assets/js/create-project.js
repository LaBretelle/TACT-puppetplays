import AppRouting from './modules/app-routing.js'

const deleteProjectForm = document.forms.deleteProject

const routing = new AppRouting()

$(document).ready(function () {
  $(document).on('click', '#delete-image', function () {
    formHandler.deleteImage($(this).data('project-id'))
  })
  if (deleteProjectForm) {
    deleteProjectForm.onsubmit = (e) => {
      e.preventDefault()
      $('.delete-project-confirm-modal').modal('show')
      return false
    }
  }

  $('.delete-project-confirm-button').on('click', (e) => {
    if (e.target.dataset.action === 'confirm') {
      deleteProjectForm.submit()
    }
  })
})

const formHandler = {
  deleteImage: function (projectId) {
    var url = routing.generateRoute('project_delete_image', {
      id: projectId
    })
    $.ajax({
      url: url,
      type: 'DELETE',
      async: true,
      success: function () {
        $('.project-image-row').empty()
      }
    })

    return false
  }
}
