
import AppRouting from './../modules/app-routing.js'

const routing = new AppRouting()
let currentForm = null

$(document).ready(() => {
    // bind to body since element will be dynamically added
    $('body').on('click', '.user-active-chk', (e) => {
      toggleUserIsActive(e.target)
    })

    $('body').on('submit', '#fully-anonymize-user-form', (e) => {
        e.preventDefault()
        currentForm = document.forms['fully-anonymize-user-form']
        $('.anonymize-user-confirm-modal').modal('show')
    })

    $('body').on('submit', '#partially-anonymize-user-form', (e) => {
        e.preventDefault()
        currentForm = document.forms['partially-anonymize-user-form']
        $('.anonymize-user-confirm-modal').modal('show')
    })

    $('.add-user').on('click', () => {
      $('.add-user-modal').modal('show')
    })

    $('.anonymize-user-confirm-button').on('click', (e) => {
        if(e.target.dataset.action === 'confirm'){
          currentForm.submit()
        }
        currentForm = null
    });

    $('.search-input').on('input', (e) => {
      getUsers(e.target.value)
    })
    getUsers()
});

const toggleUserIsActive = (element) => {
    const url = routing.generateRoute('admin_user_activate_account', {id: element.dataset.id})
    $.ajax({
      method: "POST",
      url: url,
      data : {'active': element.checked},
    }).done(function(response) {})
}

const getUsers = (query = '') => {
  const url = routing.generateRoute('admin_user_fetch', {})
  $('.ajax-row').show()
  $.ajax({
    method: "POST",
    url: url,
    data : {'query': query},
  }).done(function(response) {
    $('.ajax-row').hide()
    applyUsersToDom(response)
  })
}

const applyUsersToDom = (data) => {
  const $container = $('.user-table').find('tbody')
  $container.empty()
  $container.append(data)
  $('[data-toggle="tooltip"]').tooltip()
}
