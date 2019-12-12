const editor = new TinyEditor()
editor.init()

$(document).ready(function() {
  $(document).on('click', '#delete-image', function() {
    formHandler.deleteImage($(this).data('project-id'))
  })
  $(document).on('click', '#delete-helplink', function() {
    formHandler.deleteHelpLink($(this).data('project-id'))
  })
})

const formHandler = {
  deleteImage: function(projectId) {
    var url = Routing.generate('project_image_delete', {
      id: projectId
    })
    $.ajax({
      url: url,
      type: 'DELETE',
      async: true,
      success: function() {
        $('.project-image-row').empty()
      }
    })

    return false
  },
  deleteHelpLink: function(projectId) {
    var url = Routing.generate('project_helplink_delete', {
      id: projectId
    })
    $.ajax({
      url: url,
      type: 'DELETE',
      async: true,
      success: function() {
        $('.project-helplink-row').empty()
      }
    })

    return false
  }
}