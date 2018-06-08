import AppRouting from './modules/app-routing.js';
import tinymce from 'tinymce/tinymce';
// A theme is also required
import 'tinymce/themes/modern/theme';
// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';

const routing = new AppRouting();

$(document).ready(() => {
  tinymce.init({
      selector: 'textarea.tinymce-transcription',
      plugins: ['paste', 'link']
  }).then((editors) => {
    // load content into tinyMCE
    editors.forEach(editor => {
      editor.load();
    })
  })

  $('.btn-save-transcription').on('click', (e) => {
    saveTranscription(e.target.dataset.id)
  })

  $('.btn-finish-transcription').on('click', (e) => {
    finishTranscription(e.target.dataset.id, e.target.dataset.pid)
  })

  $('.img-fluid').on('click', (e) => {
      const image = e.target.cloneNode()
      image.classList.remove('project-image')
      image.setAttribute('style', 'width:100%;')
      const modalBody = $('.project-media-modal').find('.modal-body')
      modalBody.empty()
      modalBody.append(image)
      $('.project-media-modal').modal('show')
  })

})


const saveTranscription = (id) => {
    const tinyContent = tinymce.get('tiny-content').getContent();
    const url = routing.generateRoute('media_transcription_save', {id: id})
    $.ajax({
      method: 'POST',
      url: url,
      data: {'transcription': tinyContent}
    }).done(function(response) {
        //console.log('transcription saved', response)
    });

}

const finishTranscription = (id, pid) => {
    const tinyContent = tinymce.get('tiny-content').getContent();
    const url = routing.generateRoute('media_transcription_finish', {id: id})
    const projectHome = routing.generateRoute('project_display', {id: pid})
    $.ajax({
      method: 'POST',
      url: url,
      data: {'transcription': tinyContent}
    }).done(function(response) {
        //console.log('transcription saved', response)
        window.location = projectHome
    });

}
