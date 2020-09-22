const editor = new TinyEditor()
editor.init()

$(document).ready(function () {

  $(document).on('click', '#delete-xsl', function () {
    formHandler.deleteXsl($(this).data('project-id'))
  })

  $(document).on('click', '#delete-json', function () {
    formHandler.deleteJson($(this).data('project-id'))
  })

})

const formHandler = {
  deleteXsl: function (projectId) {
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
  },
  deleteJson: function (projectId) {
    var url = Routing.generate('project_json_delete', {
      id: projectId
    })
    $.ajax({
      url: url,
      type: 'DELETE',
      async: true,
      success: function () {
        $('.project-json-row').empty()
      }
    })

    return false
  }
}