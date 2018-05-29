import AppRouting from './modules/app-routing.js';

const routing = new AppRouting();
let currentDeleteAction = null;

$(document).ready(() => {
    $('.delete-image').on('click', (e) => {
        currentDeleteAction = e.target;
        $('.delete-project-media-confirm-modal').modal('show');
    });

    $('.delete-media-confirm-button').on('click', (e) => {
        deleteImage(currentDeleteAction);
    });
});

const deleteImage = (element) => {
  const url = routing.generateRoute('project_media_delete', {id: element.dataset.id});
  $.ajax({
    method: "DELETE",
    url: url
  }).done(function(response) {
      element.closest('.image-container').remove();
      currentDeleteAction = null;
  });
}
