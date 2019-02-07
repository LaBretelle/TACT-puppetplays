/* global require module */
const Tiny = require('tinymce')

// A theme is also required
require('tinymce/themes/silver')
// Any plugins you want to use has to be imported
require('tinymce/plugins/link')
require('tinymce/plugins/paste')

class TinyEditor {
  constructor(){
    //console.log('yope')
  }

  init() {
    Tiny.init({
      selector: 'textarea.tinymce-enabled',
      plugins: ['link', 'paste'],
      //paste_word_valid_elements: 'b,strong,i,em,h1,h2',
      //paste_retain_style_properties: 'color',
      //paste_enable_default_filters: false,
      paste_as_text: true,
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
