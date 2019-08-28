const editor = new TinyEditor()
editor.init()

$(document).ready(function () {
  $(document).on('click', '#delete-image', function () {
    formHandler.deleteImage($(this).data('project-id'))
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
}
