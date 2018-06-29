import AppRouting from './modules/app-routing.js'

const routing = new AppRouting()
let selectedMedia = []
let selectedFolder = -1

$(document).ready(() => {
  $('.delete-media').on('click', () => {
    $('.delete-project-media-confirm-modal').modal('show')
  })

  $('#select-folder').on('change', (e) => {
    selectedFolder = e.target.options[e.target.selectedIndex].value
  })

  $('.move-media').on('click', () => {
    $('.move-project-media-modal').modal('show')
  })

  $('.move-media-confirm-button').on('click', () => {
    moveMedia()
  })

  $('.delete-media-confirm-button').on('click', () => {
    deleteMedia()
  })

  $('.image-select').on('change', (e) => {
    const id = e.target.dataset.id
    if(e.target.checked && selectedMedia.indexOf(id) === -1) {
      selectedMedia.push(id)
    } else {
      const index = selectedMedia.indexOf(id)
      selectedMedia.splice(index, 1)
    }

    if(selectedMedia.length > 0) {
      $('.images-actions').find('button').attr('disabled', false)
    } else {
      $('.images-actions').find('button').attr('disabled', true)
    }
  })

  $('#btn-toggle-upload-form').on('click', (e) => {
    if ($(e.target).children().first().hasClass('fa-toggle-on')) {
      $(e.target).children().first().removeClass('fa-toggle-on').addClass('fa-toggle-off')
    } else {
      $(e.target).children().first().removeClass('fa-toggle-off').addClass('fa-toggle-on')
    }
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

const deleteMedia = () => {
  const url = routing.generateRoute('project_media_delete')
  $.ajax({
    method: 'POST',
    url: url,
    data: {'ids' : selectedMedia}
  }).done(() => {
    selectedMedia.forEach((id) => {
      $('#img-col-' + id).remove()
    })
    selectedMedia = []
    selectedFolder = -1
    $('.images-actions').find('button').attr('disabled', true)
  })
}

const moveMedia = () => {
  const url = routing.generateRoute('project_move_media')
  $.ajax({
    method: 'POST',
    url: url,
    data: {'ids' : selectedMedia, 'dirId': selectedFolder}
  }).done(() => {
    selectedMedia.forEach((id) => {
      $('#img-col-' + id).remove()
    })
    selectedMedia = []
    selectedFolder = -1
    $('.images-actions').find('button').attr('disabled', true)
  })
}
