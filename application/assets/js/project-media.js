let selectedMedia = []
let selectedFolder = -1

let checkedFolders = []
let ignoredFolders = []
let deleteFoldersForm = null
let moveFoldersForm = null


$(document).ready(() => {

  /* media
  ------------------------------------ */
  $('.delete-media').on('click', () => {
    $('.delete-project-media-confirm-modal').modal('show')
  })

  $('.move-media').on('click', () => {
    $('.move-project-media-modal').modal('show')
  })

  $('.media-filter, .media-filter-clear').on('click', () => {
    filterOnTextAndStatus()
  })

  $('#text-filter-media').on('keyup', () => {
    filterOnTextAndStatus()
  })

  // toggle all media selection / unselection
  $('.toggle-select-all-media').on('click', (e) => {
    const checked = e.target.dataset.state === 'check'
    $('.image-select').each((index, element) => {
      element.checked = checked
      handleMediaSelection(element)
    })
    e.target.dataset.state = checked ? 'uncheck' : 'check'
  })

  $('.move-media-confirm-button').on('click', (e) => {
    moveMedia(e.target.dataset.pid)
  })

  $('.delete-media-confirm-button').on('click', (e) => {
    deleteMedia(e.target.dataset.pid)
  })

  $('.image-select').on('change', (e) => {
    handleMediaSelection(e.target)
  })

  $('.project-image').on('click', (e) => {
    const image = e.target.cloneNode()
    const url = e.target.dataset.full
    image.classList.remove('project-image')
    image.setAttribute('src', url)
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
    $('.target-directory').show()
    ignoredFolders.forEach((id) => {
      $('.target-directory[data-dir=' + id + ']').hide()
    })

    $('.move-project-folder-modal').modal('show')
  })

  $('.move-dir-confirm-button').on('click', () => {
    // clean inputs
    $('form#move-folder-form').find('input').each((index, el) => {
      if ($(el).hasClass('move-folder')) {
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
    const pid = e.target.dataset.pid
    const name = $(`#dir-${id}-name-input`).val().trim()
    if (name !== '') {
      updateFolderName(pid, id, name)
    }
  })

  $('.folder-check').on('change', (e) => {
    const folderToggled = e.target
    const id = folderToggled.dataset.id

    if (folderToggled.checked && checkedFolders.indexOf(id) === -1) {
      checkedFolders.push(id)
      ignoredFolders.push(id)
      $('[data-dir-parent=' + id + ']').find('.folder-check').each((index, el) => {
        ignoredFolders.push($(el).attr('data-id'))
      })

    } else {
      const indexChecked = checkedFolders.indexOf(id)
      checkedFolders.splice(indexChecked, 1)

      const indexIgnored = ignoredFolders.indexOf(id)
      ignoredFolders.splice(indexIgnored, 1)


      $('[data-dir-parent=' + id + ']').find('.folder-check').each((index, el) => {
        let idEl = $(el).attr('data-id')
        let idx = ignoredFolders.indexOf(idEl)
        ignoredFolders.splice(idx, 1)
      })


    }

    if (checkedFolders.length > 0) {
      $('.btn-del-dir').attr('disabled', false)
      $('.btn-move-dir').attr('disabled', false)
      // clean inputs
      $('form#delete-folder-form').find('input').each((index, el) => {
        $(el).remove()
      })
      $('form#move-folder-form').find('input').each((index, el) => {
        $(el).remove()
      })
      checkedFolders.forEach((id) => {
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
  // set default to multiple XMl upload
  $('#xml_zip').closest('.form-group').hide()
  $('#xml_zip').attr('required', false)


  // allow user to choose between a single zip file or multiple images files
  $('.radio-file-type').on('change', (e) => {
    const radiobtn = e.target
    const what = radiobtn.dataset.what

    if (what == 'images') {
      const toHide = (radiobtn.value === 'images') ? $('#project_media_zip') : $('#project_media_images')
      const toShow = (radiobtn.value === 'images') ? $('#project_media_images') : $('#project_media_zip')
      toHide.attr('required', false)
      toShow.attr('required', true)
      toHide.closest('.form-group').hide()
      toShow.closest('.form-group').show()

    } else {
      const toHideXML = radiobtn.value === 'xmls' ? $('#xml_zip') : $('#xml_xmls')
      const toShowXML = radiobtn.value === 'xmls' ? $('#xml_xmls') : $('#xml_zip')
      toHideXML.attr('required', false)
      toShowXML.attr('required', true)
      toHideXML.closest('.form-group').hide()
      toShowXML.closest('.form-group').show()
    }

    if (radiobtn.value === 'images' || radiobtn.value === 'xmls') {
      $('.max-file-upload-msg').show()
    } else {
      $('.max-file-upload-msg').hide()
    }

  })

  // handle file selection... if file input exists
  let uploadFiles = document.getElementById('project_media_images')
  if (uploadFiles) {
    uploadFiles.onchange = (e) => {
      const span = document.getElementById('nb-media-selected')
      span.textContent = e.target.files.length
    }
  }

  // handle file selection... if file input exists
  uploadFiles = document.getElementById('xml_xmls')
  if (uploadFiles) {
    uploadFiles.onchange = (e) => {
      const span = document.getElementById('nb-xmls-selected')
      span.textContent = e.target.files.length
    }
  }
})

const deleteMedia = (projectId) => {
  const url = Routing.generate('project_media_delete', {
    id: projectId
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'ids': selectedMedia
    }
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

const moveMedia = (projectId) => {
  const url = Routing.generate('project_move_media', {
    id: projectId
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'ids': selectedMedia,
      'dirId': selectedFolder
    }
  }).done((data) => {
    data.movedMedia.forEach((id) => {
      $('#img-col-' + id).remove()
    })
    $('.images-actions').find('button').attr('disabled', true)


    if (selectedMedia.length != data.movedMedia.length) {
      Toastr.warning(Translator.trans('media_not_all_moved'))
    }
    if (data.movedMedia.length > 0) {
      Toastr.info(Translator.trans('media_moved'))
    }
    selectedFolder = -1
    selectedMedia = []
  })
}

const updateFolderName = (projectId, id, name) => {
  const url = Routing.generate('project_update_folder_name', {
    id: projectId
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      id: id,
      name: name
    }
  }).done((data) => {
    if (data.hasBeenUpdated === true) {
      $(`#dir-${id}-name-value`).text(name)
      Toastr.info(Translator.trans('folder_name_changed'))
      $('[data-dir=' + id + ']').text(name)
    } else {
      Toastr.warning(Translator.trans('folder_name_not_changed'))
    }

    $(`#dir-${id}-name`).show()
    $(`#dir-${id}-edit-actions`).show()
    $(`#dir-${id}-name-update-btn`).hide()
    $(`#dir-${id}-name-input`).hide()
  })
}

const handleMediaSelection = (element) => {
  const id = element.dataset.id
  if (element.checked && selectedMedia.indexOf(id) === -1) {
    selectedMedia.push(id)
  } else {
    const index = selectedMedia.indexOf(id)
    selectedMedia.splice(index, 1)
  }

  if (selectedMedia.length > 0) {
    $('.delete-media').attr('disabled', false)
    $('.move-media').attr('disabled', false)
  } else {
    $('.delete-media').attr('disabled', true)
    $('.move-media').attr('disabled', true)
  }
}

const filterOnTextAndStatus = () => {
  setTimeout(function () {

    let status = false
    let medias = Array.from(document.getElementsByClassName('status'))
    let statusBtn = document.querySelector('.media-filter.active')
    let text = document.querySelector('#text-filter-media').value.toLowerCase()

    if (statusBtn) {
      status = statusBtn.getAttribute('data-status')
    }

    medias.forEach(function (media) {

      if (media.getAttribute('data-name').toLowerCase().includes(text) && (!status || (status && media.classList.contains(status)))) {
        media.parentNode.classList.remove('d-none')
      } else {
        media.parentNode.classList.add('d-none')
      }
    })
  }, 100)
}
