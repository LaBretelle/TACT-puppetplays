import AppRouting from './modules/app-routing.js'

const routing = new AppRouting()
let currentDeleteAction = null

$(document).ready(() => {
  $('.delete-image').on('click', (e) => {
    currentDeleteAction = e.target
    $('.delete-project-media-confirm-modal').modal('show')
  })

  $('.delete-media-confirm-button').on('click', () => {
    deleteImage(currentDeleteAction)
  })

  // set default to multiple images upload
  $('#project_media_zip').closest('.form-group').hide()
  $('#project_media_zip').attr('required', false)

  // allow user to choose between a single zip file or multiple images files
  $('.radio-file-type').on('change', (e) => {
    const toHide = e.target.value === 'images' ? $('#project_media_zip') : $('#project_media_images')
    const toShow = e.target.value === 'images' ? $('#project_media_images') : $('#project_media_zip')
    toHide.attr('required', false)
    toShow.attr('required', true)
    if(e.target.value === 'images'){
      $('.max-file-upload-msg').show()
    } else {
      $('.max-file-upload-msg').hide()
    }
    toHide.closest('.form-group').hide()
    toShow.closest('.form-group').show()
  })

  // handle file selection... if file input exists
  const imagesFileInput = document.getElementById('project_media_images')
  if (imagesFileInput) {
    imagesFileInput.onchange = (e) => {
      const span = document.getElementById('nb-media-selected')
      span.textContent = e.target.files.length
    }
  }

  $('.project-image').on('click', (e) => {
    const image = e.target.cloneNode()
    image.classList.remove('project-image')
    image.setAttribute('style', 'width:100%;')
    const modalBody = $('.project-media-modal').find('.modal-body')
    modalBody.empty()
    modalBody.append(image)
    $('.project-media-modal').modal('show')
  })
})

const deleteImage = (element) => {
  const url = routing.generateRoute('project_media_delete', {
    id: element.dataset.id
  })
  $.ajax({
    method: 'DELETE',
    url: url
  }).done(function () {
    element.closest('.col-2').remove()
    currentDeleteAction = null
  })
}
