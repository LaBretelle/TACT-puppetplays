
import AppRouting from './../modules/app-routing.js';

const routing = new AppRouting();

$(document).ready(() => {
    // bind to body since element will be dynamically added
    $('body').on('click', '.user-active-chk', (e) => {
      activateUser(e.target);
    });
    $('.search-input').on('input', (e) => {
      getUsers(e.target.value);
    });
    getUsers();
});


const activateUser = (element) => {
    const url = routing.generateRoute('admin_user_activate_account', {id: element.dataset.id});
    $.ajax({
      method: "POST",
      url: url,
      data : {'active': element.checked},
    }).done(function(response) {});
}

const getUsers = (query = '') => {
  const url = routing.generateRoute('admin_user_fetch', {});
  $('.ajax-row').show();
  $.ajax({
    method: "POST",
    url: url,
    data : {'query': query},
  }).done(function(response) {
    $('.ajax-row').hide();
    applyUsersToDom(response);
  });
}

const applyUsersToDom = (data) => {
  const $container = $('.user-table').find('tbody');
  $container.empty();
  $container.append(data);
}
