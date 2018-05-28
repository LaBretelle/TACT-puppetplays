import AppRouting from './modules/app-routing.js';

const routing = new AppRouting();

$(document).ready(() => {

  $('.btn-delete-account-image').on('click', (e) => {
      $('.user-account-image-row').empty();
  })

});
