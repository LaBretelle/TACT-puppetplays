
import AppRouting from './../modules/app-routing.js';

const routing = new AppRouting();
let userToDeleteId = null;

$(document).ready(() => {
    // bind to body since element will be dynamically added
    $('body').on('click', '.user-active-chk', (e) => {
      toggleUserIsActive(e.target);
    });

    $('body').on('click', '.user-delete', (e) => {
      userToDeleteId = e.target.dataset.id;
      $('.delete-user-confirm-modal').modal('show');
    });

    $('.delete-user-confirm-button').on('click', (e) => {
        if(e.target.dataset.action === 'valid'){
          deleteUser();
        };
    });

    $('.search-input').on('input', (e) => {
      getUsers(e.target.value);
    });
    getUsers();
});

const deleteUser = (element) => {
    const url = routing.generateRoute('admin_user_delete', {id: userToDeleteId});
    $.ajax({
      method: "POST",
      url: url
    }).done(function(response) {});
}

const toggleUserIsActive = (element) => {
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
