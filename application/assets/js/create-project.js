const editor = new TinyEditor()
editor.init()

const deleteProjectForm = document.forms.deleteProject

$(document).ready(function () {
  $(document).on('click', '#delete-image', function () {
    formHandler.deleteImage($(this).data('project-id'))
  })

  $(document).on('click', '#delete-xsl', function () {
    formHandler.deleteXsl($(this).data('project-id'))
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
    var url = Routing.generate('project_image_delete', {
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
  } ,

  deleteXsl: function (projectId){
    var url = Routing.generate('project_xslt_delete', {
      id: projectId
    })
    $.ajax({
      url: url,
      type: 'DELETE',
      async: true,
      success: function () {
        $('.project-xsl-row').empty()
      }
    })

    return false
  }
}
