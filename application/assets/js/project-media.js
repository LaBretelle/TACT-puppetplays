import AppRouting from './modules/app-routing.js'
import * as Toastr from 'toastr'

const routing = new AppRouting()
let selectedMedia = []
let selectedFolder = -1

let checkedFolders = []
let deleteFoldersForm = null
let moveFoldersForm = null

// does the project has folder(s) ?
let hasFolder = false

$(document).ready(() => {

  hasFolder = $('.tree-entry').length > 0
  /* media
  ------------------------------------ */
  $('.delete-media').on('click', () => {
    $('.delete-project-media-confirm-modal').modal('show')
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

    if(selectedMedia.length > 0 && hasFolder) {
      $('.images-actions').find('button').attr('disabled', false)
    } else {
      $('.images-actions').find('button').attr('disabled', true)
    }
  })

  $('.project-image').on('click', (e) => {
    const image = e.target.cloneNode()
    image.classList.remove('project-image')
    image.setAttribute('style', 'width:100%;')
    const modalBody = $('.project-media-modal').find('.modal-body')
    modalBody.empty()
    modalBody.append(image)
    $('.project-media-modal').modal('show')
  })

  /* folder(s)
  ------------------------------------ */

  deleteFoldersForm = document.forms.namedItem('delete-folder-form')
  moveFoldersForm = document.forms.namedItem('move-folder-form')

  // modals folder select element
  $('.select-folder').on('change', (e) => {
    selectedFolder = e.target.options[e.target.selectedIndex].value
  })

  $('.btn-add-dir').on('click', () => {
    $('.add-dir-modal').modal('show')
  })

  $('.btn-del-dir').on('click', () => {
    $('.delete-project-folders-confirm-modal').modal('show')
  })

  $('.btn-move-dir').on('click', () => {
    $('.move-project-folder-modal').modal('show')
  })

  $('.move-dir-confirm-button').on('click', () => {
    // clean inputs
    $('form#move-folder-form').find('input').each((index, el) => {
      if($(el).hasClass('move-folder')) {
        $(el).remove()
      }
    })
    // append a field dirId  selectedFolder
    $('form#move-folder-form').append(`<input class="move-folder" type="hidden" name="dirId" value="${selectedFolder}"></input>`)
    moveFoldersForm.submit()
  })

  $('.delete-folders-confirm-button').on('click', () => {
    deleteFoldersForm.submit()
  })

  $('.btn-folder-name-edit').on('click', (e) => {
    const id = e.target.dataset.id
    $(`#dir-${id}-name`).hide()
    $(`#dir-${id}-edit-actions`).hide()
    $(`#dir-${id}-name-update-btn`).show()
    $(`#dir-${id}-name-input`).show()
  })

  $('.btn-folder-name-save').on('click', (e) => {
    const id = e.target.dataset.id
    const name = $(`#dir-${id}-name-input`).val().trim()
    if(name !== '') {
      $(`#dir-${id}-name-value`).text(name)
      $(`#dir-${id}-name`).show()
      $(`#dir-${id}-edit-actions`).show()
      $(`#dir-${id}-name-update-btn`).hide()
      $(`#dir-${id}-name-input`).hide()
      updateFolderName(id, name)
    }
  })

  $('.folder-check').on('change', (e) => {
    const id = e.target.dataset.id
    if(e.target.checked && checkedFolders.indexOf(id) === -1) {
      checkedFolders.push(id)
    } else {
      const index = checkedFolders.indexOf(id)
      checkedFolders.splice(index, 1)
    }

    if(checkedFolders.length > 0) {
      $('.btn-del-dir').attr('disabled', false)
      $('.btn-move-dir').attr('disabled', false)
      // clean inputs
      $('form#delete-folder-form').find('input').each((index, el) => {
        $(el).remove()
      })
      $('form#move-folder-form').find('input').each((index, el) => {
        $(el).remove()
      })
      checkedFolders.forEach( (id) => {
        $('form#delete-folder-form').append(`<input type="hidden" name="ids[]" value="${id}"></input>`)
        $('form#move-folder-form').append(`<input type="hidden" name="ids[]" value="${id}"></input>`)
      })

    } else {
      $('.btn-del-dir').attr('disabled', true)
      $('.btn-move-dir').attr('disabled', true)
    }
  })

  /* File(s) upload
  ------------------------------------------*/

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
    Toastr.info(Translator.trans('media_deleted'))
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
    Toastr.info(Translator.trans('media_moved'))
  })
}


const updateFolderName = (id, name) => {
  const url = routing.generateRoute('project_update_folder_name')
  $.ajax({
    method: 'POST',
    url: url,
    data: {id: id, name: name}
  }).done(() => {
    Toastr.info(Translator.trans('folder_name_changed'))
  })
}
