import AppRouting from './modules/app-routing.js'

const routing = new AppRouting()

$(document).ready(() => {

    $('.btn-logo-delete').on('click', (e) => {
        const url = routing.generateRoute('admin_platform_logo_delete')
        $.ajax({
          method: 'DELETE',
          url: url
        }).done(function(response) {
            e.target.closest('.platform-logo-row').remove()
        })
    })
})
