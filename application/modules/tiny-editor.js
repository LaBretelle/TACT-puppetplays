/* global require module */
const Tiny = require('tinymce')

// A theme is also required
require('tinymce/themes/modern/theme')
// Any plugins you want to use has to be imported
require('tinymce/plugins/link')

class TinyEditor {
  constructor(){
    console.log('yope')
  }
  
  init() {
    Tiny.init({
      selector: 'textarea.tinymce-enabled',
      plugins: ['link'],
      menubar: false,
      statusbar: false,
      setup: (editor) => {
        editor.on('change', () => {
          editor.save()
        })
      }
    })
  }
}

module.exports = TinyEditor
