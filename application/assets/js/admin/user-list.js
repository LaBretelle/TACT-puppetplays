
import AppRouting from './../modules/app-routing.js';

const routing = new AppRouting();

$(document).ready(() => {
    $('.user-active-chk').on('click', (e) => {
      activateUser(e);
    });
    $('.search-input').on('input', (e) => {
      getUsers(e.target.value);
    });
    getUsers();
});


const activateUser = (data) => {
  console.log('activate me', data);
  const url = routing.generateRoute('admin_user_activate_account', {id: 1});
  console.log('url', url);
}

const getUsers = (query = '') => {
  const url = routing.generateRoute('admin_user_fetch', {});

  $.ajax({
    method: "POST",
    url: url,
    data : {'query': query},
  }).done(function(response) {
    applyUsersToDom(response);
  });
}

const applyUsersToDom = (data) => {

  const $container = $('.user-table').find('tbody');
  $container.empty();
  $container.append(data);
}
