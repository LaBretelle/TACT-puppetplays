/* global mode */
import OpenSeadragon from 'openseadragon'
import introJs from 'intro.js'

// normally every 2 minutes but to avoid any time problem let's put a bit less
const updateLogTimeout = 100000
const id = 'seadragon-viewer'
const el = document.getElementById(id)
const url = el.getAttribute('data-url').trim()
let nextBtn = document.querySelector('#test-osd-next')
let previousBtn = document.querySelector('#test-osd-previous')
let warningDiv = document.querySelector('#warning-media-changed')
let homeBtn = document.querySelector('#test-osd-home')


let editor

let item = {
  type: 'image',
  url: url
}

let viewer = new OpenSeadragon.Viewer({
  id: id,
  showNavigator: false,
  collectionMode: false,
  showRotationControl: true,
  sequenceMode: true,
  showReferenceStrip: true,
  referenceStripScroll: 'vertical',
  prefixUrl: '',
  zoomInButton: 'osd-zoom-in',
  zoomOutButton: 'osd-zoom-out',
  homeButton: 'osd-home',
  nextButton: 'osd-next',
  previousButton: 'osd-previous',
  rotateLeftButton: 'osd-left',
  rotateRightButton: 'osd-right',
  tileSources: item
})
// disable keyboard shortcuts
viewer.innerTracker.keyHandler = null

$(document).ready(() => {
  $('#test-osd-next').on('click', (e) => {
    let btn = e.target
    btn.disabled = true
    displayOtherFacsimile(btn, true)
  })

  $('#test-osd-previous').on('click', (e) => {
    let btn = e.target
    btn.disabled = true
    displayOtherFacsimile(btn, false)
  })

  $('#test-osd-home').on('click', (e) => {
    displayOriginalFacsimile(e.target)
  })

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

    $('.btn-tesseract').on('click', (e) => {
      tesseract(e.target.dataset.id, e.target.dataset.url, e.target)
    })

    $('.btn-save-transcription').on('click', (e) => {
      saveTranscription(e.target.dataset.id, e.target)
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
      saveTranscription(e.target.dataset.id, e.target)
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

  $('#start-tutorial').on('click', () => {
    startTutorial()
  })
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

const tesseract = (id, imgURL, btn) => {
  $('#tesseract-modal').modal('show')

  const url = Routing.generate('media_tesseract', {
    id: id
  })

  $.ajax({
    method: 'POST',
    url: url,
    data: {
      'imgURL': imgURL
    }
  }).done((data) => {
    data = data.replace(/(?:\r\n|\r|\n)/g, '<br>')
    editor.setContent(data)
    $('#tesseract-modal').modal('hide')
  })

  return true
}

const saveTranscription = (id, btn) => {
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
    btn.classList.add('btn-outline-secondary')
    btn.classList.remove('btn-info')
  })

  return true
}

const reportTranscription = (id) => {
  $('#report-modal').modal('hide')
  const url = Routing.generate('media_transcription_report', {
    id: id
  })
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
    'overlayOpacity': 0.5,
    'exitOnOverlayClick': false,
    'exitOnEsc': false,
    'nextLabel': Translator.trans('tutorial_next'),
    'prevLabel': Translator.trans('tutorial_previous'),
    'doneLabel': Translator.trans('tutorial_finished'),
    'skipLabel': 'Skip'
  }).start()
}

const testFirstTranscript = () => {
  const firstTranscript = document.getElementById('firstTranscript').value
  if (firstTranscript == 1) {
    startTutorial()
    const url = Routing.generate('user_tutorial_viewed')
    $.ajax({
      method: 'POST',
      url: url
    })

    return true
  }
}

const displayOtherFacsimile = (btn, next) => {
  let id = btn.dataset.id

  nextBtn.disabled = true
  previousBtn.disabled = true

  const url = Routing.generate('media_transcription_next', {
    id: id,
    next: next ? 1 : 0
  })

  $.ajax({
    method: 'POST',
    url: url,
    data: {}
  }).done((data) => {
    if (data.id) {
      nextBtn.dataset.id = data.id
      previousBtn.dataset.id = data.id
      viewer.open({
        type: 'image',
        url: data.url
      })
      Toastr.info(Translator.trans('media_change'))
      nextBtn.disabled = false
      previousBtn.disabled = false
    } else {
      if (next) {
        previousBtn.disabled = false
      } else {
        nextBtn.disabled = false
      }
      Toastr.warning(Translator.trans('no_media_available'))
    }
    if (previousBtn.dataset.id != homeBtn.dataset.id) {
      warningDiv.classList.remove('d-none')
    } else {
      warningDiv.classList.add('d-none')
    }
  })
}

const displayOriginalFacsimile = (btn) => {
  viewer.open(item)
  warningDiv.classList.add('d-none')
  nextBtn.dataset.id = btn.dataset.id
  previousBtn.dataset.id = btn.dataset.id
  nextBtn.disabled = false
  previousBtn.disabled = false
}