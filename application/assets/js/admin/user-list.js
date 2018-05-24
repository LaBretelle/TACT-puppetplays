
import AppRouting from './../modules/app-routing.js';

const routing = new AppRouting();



$(document).ready(() => {
    $('.user-active-chk').on('click', (e) => {
      activateUser(e);
    });

    // routing.generateRoute('toto', 'titi');
    //console.log(AppRouting);
});


const activateUser = (data) => {
  console.log('activate me', data);
  //admin_user_activate_account
  const url = routing.generateRoute('admin_user_activate_account', {id: 1});
  console.log('url', url);

}
