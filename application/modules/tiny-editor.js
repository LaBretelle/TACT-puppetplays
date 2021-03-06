/* global require module */
const Tiny = require('tinymce')

// A theme is also required
require('tinymce/themes/silver')
// Any plugins you want to use has to be imported
require('tinymce/plugins/link')
require('tinymce/plugins/paste')
require('tinymce/plugins/code')
require('tinymce/plugins/image')
require('tinymce/icons/default')

class TinyEditor {
  init() {
    Tiny.init({
      height: '50vh',
      selector: 'textarea.tinymce-enabled',
      plugins: ['link', 'paste', 'code', 'image'],
      toolbar1: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image | code',
      paste_as_text: true,
      menubar: false,
      statusbar: true,
      resize: true,
      setup: (editor) => {
        editor.on('change', () => {
          editor.save()
        })
      }
    })
  }
}

module.exports = TinyEditor