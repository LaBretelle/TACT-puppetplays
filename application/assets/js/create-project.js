import AppRouting from './modules/app-routing.js'

const deleteProjectForm = document.forms.deleteProject
const routing = new AppRouting()

$(document).ready(function() {
  $(document).on('click', '#delete-image', function(event) {
    formHandler.deleteImage($(this).data("project-id"));
  })

  $(document).on('click', '#add-user-status', function(event) {
    event.preventDefault()
    formHandler.addUserStatus($collectionHolder)
  })

  $(document).on('click', '.remove-userstatus', function(event) {
    event.preventDefault()
    $(this).closest('.userstatus-container').remove()
  })

  deleteProjectForm.onsubmit = (e) => {
    e.preventDefault()
    console.log('form submitted')
    $('.delete-project-confirm-modal').modal('show')
    return false
  }

  $('.delete-project-confirm-button').on('click', (e) => {
      if(e.target.dataset.action === 'confirm'){
        deleteProjectForm.submit()
      }
  });

  let $collectionHolder
  $collectionHolder = $('div.userstatuses')
  $collectionHolder.data('index', $collectionHolder.find(':input').length)
})

const formHandler = {

  addUserStatus: function(collectionHolder) {
    let prototype = collectionHolder.data('prototype')
    let index = collectionHolder.data('index')
    let newForm = prototype
    newForm = newForm.replace(/__name__/g, index)
    collectionHolder.data('index', index + 1)
    collectionHolder.append(newForm)
  },

  deleteImage: function(projectId) {
    var url = routing.generateRoute('project_delete_image', {id: projectId})
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
