$(document).ready(function() {
  $(document).on('click', '#add-user-status', function(event) {
    event.preventDefault();
    formHandler.addUserStatus($collectionHolder);
  });
  $(document).on('click', '.remove-userstatus', function(event) {
    event.preventDefault();
    $(this).closest(".userstatus-container").remove();
  });

  var $collectionHolder;
  $collectionHolder = $('div.userstatuses');
  $collectionHolder.data('index', $collectionHolder.find(':input').length);
});

var formHandler = {
  addUserStatus: function(collectionHolder) {
    var prototype = collectionHolder.data('prototype');
    var index = collectionHolder.data('index');
    var newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);
    collectionHolder.data('index', index + 1);
    collectionHolder.append(newForm);
  },
};
