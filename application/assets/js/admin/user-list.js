
import AppRouting from './../modules/app-routing.js';

const routing = new AppRouting();



$(document).ready(() => {
    $('.user-active-chk').on('click', (e) => {
      activateUser(e);
    });

    // routing.generateRoute('toto', 'titi');
    //console.log(AppRouting);
    //
    getUsers();
});


const activateUser = (data) => {
  console.log('activate me', data);
  //admin_user_activate_account
  const url = routing.generateRoute('admin_user_activate_account', {id: 1});
  console.log('url', url);
}

const getUsers = () => {
  const url = routing.generateRoute('admin_user_fetch', {});
  console.log(url);
  $.ajax({
    method: "POST",
    url: url
  }).done(function(data) {
    console.log('toto', data)
    applyUsersToDom(data);
  });
}

const applyUsersToDom = (data) => {
  console.log('apply ro dom with data', data);

  /*
  <tr>
    <th scope="row">{{user.id}}</th>
    <td>{{user.fullname}}</td>
    <td>{{user.username}}</td>
    <td>{{user.email}}</td>
    <td>
      <input type="checkbox" data-toggle="tooltip" title="{{'user_activate_action' | trans}}" class="user-active-chk" name="user_active" {% if user.active is same as(true) %}checked{% endif %}> </input>
    </td>
    <td>
      <div class="btn-group">
        <a href="#" data-toggle="tooltip" title="{{'user_edit' | trans}}" class="btn btn-link"><i class="fa fa-pencil"></i></a>
        <a href="#" data-toggle="tooltip" title="{{'user_delete' | trans}}" class="btn btn-link"><i class="fa fa-trash"></i></a>
      </div>
    </td>
  </tr>
   */
}
