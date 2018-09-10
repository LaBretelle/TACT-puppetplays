/* global mode */
import OpenSeadragon from 'openseadragon'

// normally every 2 minutes but to avoid any time problem let's put a bit less
const updateLogTimeout = 100000

const id = 'seadragon-viewer'
const el = document.getElementById(id)
const url = el.getAttribute('data-url')
let editor

OpenSeadragon({
  id: id,
  showNavigator: false,
  showRotationControl: true,
  prefixUrl: '',
  zoomInButton: 'osd-zoom-in',
  zoomOutButton: 'osd-zoom-out',
  homeButton: 'osd-home',
  fullPageButton: 'osd-full-page',
  nextButton: 'osd-next',
  previousButton: 'osd-previous',
  rotateLeftButton: 'osd-left',
  rotateRightButton: 'osd-right',
  tileSources: {
    type: 'image',
    url: url
  }
})

$(document).ready(() => {
  const logId = document.getElementById('log-id').value

  if ('edit' === mode) {
    // load proper TEI schema
    const jsonTeiDef = JSON.parse(document.getElementById('tei-schema').value)
    editor = new TeiEditor(jsonTeiDef)
    editor.init()

    $('.btn-save-transcription').on('click', (e) => {
      saveTranscription(e.target.dataset.id)
    })

    $('.btn-finish-transcription').on('click', (e) => {
      finishTranscription(e.target.dataset.id, e.target.dataset.pid)
    })

    // update islocked log every 2 (-) minutes
    window.setInterval(() => {
      updateLockedLog(logId)
    }, updateLogTimeout)
  } else if ('validation' === mode) {
    $('.btn-validate-transcription').on('click', (e) => {
      validateTranscription(e.target.dataset.id, e.target.dataset.pid)
    })

    $('.btn-unvalidate-transcription').on('click', (e) => {
      unvalidateTranscription(e.target.dataset.id, e.target.dataset.pid)
    })
  } else {
    $('.img-fluid').on('click', (e) => {
      const image = e.target.cloneNode()
      image.classList.remove('project-image')
      image.setAttribute('style', 'width:100%;')
      const modalBody = $('.project-media-modal').find('.modal-body')
      modalBody.empty()
      modalBody.append(image)
      $('.project-media-modal').modal('show')
    })
  }
})

const updateLockedLog = (id) => {
  const url = Routing.generate('transcription_log_locked_update', {
    id: id
  })
  $.ajax({
    method: 'POST',
    url: url
  }).done(() => {})
}

const saveTranscription = (id) => {
  const tinyContent = editor.getContent()
  const url = Routing.generate('media_transcription_save', {
    id: id
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'transcription': tinyContent
    }
  }).done(() => {
    Toastr.info(Translator.trans('transcription_saved'))
  })
}

const finishTranscription = (id, pid) => {
  const tinyContent = editor.getContent()
  const url = Routing.generate('media_transcription_finish', {
    id: id
  })
  const projectHome = Routing.generate('project_transcriptions', {
    id: pid
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'transcription': tinyContent
    }
  }).done(() => {
    window.location = projectHome
  })
}

const validateTranscription = (id, pid) => {

  const url = Routing.generate('media_transcription_validate', {
    id: id
  })
  const projectHome = Routing.generate('project_transcriptions', {
    id: pid
  })
  $.ajax({
    method: 'POST',
    url: url
  }).done(() => {
    window.location = projectHome
  })
}

const unvalidateTranscription = (id, pid) => {
  const url = Routing.generate('media_transcription_unvalidate', {
    id: id
  })
  const projectHome = Routing.generate('project_transcriptions', {
    id: pid
  })
  $.ajax({
    method: 'POST',
    url: url
  }).done(() => {
    window.location = projectHome
  })
}
