
$(document).ready(() => {
    $('.user-active-chk').on('click', (e) => {
      activateUser(e);
    });
});


const activateUser = (data) => {
  console.log('activate me', data);
}
