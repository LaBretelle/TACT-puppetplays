/* global mode */
import OpenSeadragon from 'openseadragon'
import introJs from 'intro.js'

// normally every 2 minutes but to avoid any time problem let's put a bit less
const updateLogTimeout = 100000

const id = 'seadragon-viewer'
const el = document.getElementById(id)
const url = el.getAttribute('data-url')
let editor

let viewer = new OpenSeadragon.Viewer({
  id: id,
  showNavigator: false,
  showRotationControl: true,
  prefixUrl: '',
  zoomInButton: 'osd-zoom-in',
  zoomOutButton: 'osd-zoom-out',
  homeButton: 'osd-home',
  nextButton: 'osd-next',
  previousButton: 'osd-previous',
  rotateLeftButton: 'osd-left',
  rotateRightButton: 'osd-right',
  tileSources: {
    type: 'image',
    url: url
  }
})
// disable keyboard shortcuts
viewer.innerTracker.keyHandler = null

$(document).ready(() => {

  $('.send-report').on('click', (e) => {
    reportTranscription(e.target.dataset.id)
  })

  if ('edit' === mode) {
    lockTranscription()
    testFirstTranscript()
    const jsonTeiDef = JSON.parse(document.getElementById('tei-schema').value)
    editor = new TeiEditor(jsonTeiDef)
    editor.init()

    $('.btn-validation-modal').on('click', () => {
      $('#validation-modal').modal('show')
    })

    $('.btn-save-transcription').on('click', (e) => {
      saveTranscription(e.target.dataset.id)
    })

    $('#start-tutorial').on('click', () => {
      startTutorial()
    })

  } else if ('validation' === mode) {
    lockTranscription()
    const jsonTeiDef = JSON.parse(document.getElementById('tei-schema').value)
    editor = new TeiEditor(jsonTeiDef)
    editor.init()

    $('.btn-save-transcription').on('click', (e) => {
      saveTranscription(e.target.dataset.id)
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

const lockTranscription = () => {
  const logIdEl = document.getElementById('log-id')
  if (logIdEl) {
    const logId = logIdEl.value
    window.setInterval(() => {
      updateLockedLog(logId)
    }, updateLogTimeout)
  }
}

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
  const url = Routing.generate('media_transcription_save', {
    id: id
  })
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'transcription': editor.getContent()
    }
  }).done(() => {
    Toastr.info(Translator.trans('transcription_saved'))
  })

  return true
}

const reportTranscription = (id) => {
  $('#report-modal').modal('hide')
  const url = Routing.generate('media_transcription_report', {id: id})
  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'reportType': document.getElementById('report').value
    }
  }).done(() => {
    Toastr.info(Translator.trans('transcription_reported'))
  })

  return true
}

const startTutorial = () => {
  introJs().setOptions({
    'showProgress': true,
    'showBullets': false,
    'showStepNumbers': false,
    'scrollToElement': false,
    'overlayOpacity': 0.5
  }).start()
}

const testFirstTranscript = () => {
  const firstTranscript = document.getElementById('firstTranscript').value
  if(firstTranscript == 1){
    startTutorial()
    const url = Routing.generate('user_tutorial_viewed')
    $.ajax({
      method: 'POST',
      url: url
    })

    return true
  }
}
