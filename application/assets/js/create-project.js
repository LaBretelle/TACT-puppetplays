$(document).ready(function() {
  $(document).on('click', '#add-user-status', function(event) {
    event.preventDefault()
    formHandler.addUserStatus($collectionHolder)
  })
  $(document).on('click', '.remove-userstatus', function(event) {
    event.preventDefault()
    $(this).closest('.userstatus-container').remove()
  })

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
  }
}
