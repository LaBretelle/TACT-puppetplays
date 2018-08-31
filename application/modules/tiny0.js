/* global require module */
const Tiny = require('tinymce')
//const buttons = require('./buttons.json')

const jsonRawData = require('./tei_elements_A.json')

// A theme is also required
require('tinymce/themes/modern/theme')

// Any plugins you want to use has to be imported
require('tinymce/plugins/paste')
require('tinymce/plugins/code')
require('tinymce/plugins/link')

Tiny.initEditor = () => {
  Tiny.init({
    selector: 'textarea.tinymce-enabled',
    plugins: ['paste', 'link'],
    menubar: false,
    statusbar: false,
    setup: (editor) => {
      editor.on('change', () => {
        editor.save()
      })
    }
  })
}

const getMenuStructure = (tei) => {
  const menu = tei.modules.reduce((obj, module) => {
    obj[module] = {
      title: module,
      items: tei.elements.filter(element => element.module === module).map(element => element.tag).join(' ')
    }
    return obj
  }, {})
  return menu
}

Tiny.initTEIEditor = (tei) => {
  Tiny.init({
    selector: 'textarea.tinymce-transcription',
    plugins: ['paste', 'link', 'code'],
    valid_elements: '*[*]',
    entity_encoding: 'raw',
    menubar: tei.modules.join(' '),
    menu: getMenuStructure(tei),
    toolbar1: 'undo redo code',
    setup: (editor) => {
      // add buttons to tiny
      tei.elements.forEach( (element) => {
        editor.addMenuItem(element.tag, {
          text: element.label,
          element: element,
          icon: element.icon,
          onclick: function () {
            const selection = Tiny.activeEditor.selection.getContent({
              format: 'text'
            })
            editor.windowManager.open({
              title: element.label,
              body: addFormElementAttr(element),
              onsubmit: (e) => {
                // e.data is the data that you get from tiny form
                this.data = e.data
                editor.undoManager.transact(() => {
                  // apply tags / attributes to selection or add it to content if no selection
                  let attributes = ''
                  Object.keys(this.data).forEach(key => {

                    const value = this.data[key]
                    if (value != 'none') {
                      attributes += `${key}="${value}"`
                    }
                  })

                  // does not work... tiny seems to happend the opening and closing tags...
                  if(element.selfClosed){
                    Tiny.activeEditor.selection.setContent(
                      `<${element.tag} ${attributes} />`
                    )
                  } else {
                    Tiny.activeEditor.selection.setContent(
                      `<${element.tag} ${attributes}>
                        ${selection}
                       </${element.tag}>`
                    )
                  }
                })
              }
            })
          }
        })
      })
    }
  }).then((editors) => {
    // load content into tinyMCE
    editors.forEach(editor => {
      editor.load()
    })
  })
}


const addFormElementAttr = (element) => {
  const attributes = element.attributes.map(attribute => {
    if (attribute.type === 'text') {
      return {
        type: 'textbox',
        name: attribute.key,
        label: attribute.label
      }
    } else if (attribute.type === 'enumerated') {
      return {
        type: 'listbox',
        name: attribute.key,
        label: attribute.label,
        values: attribute.values.map(entry => {
          return {
            text: entry,
            value: entry
          }
        })
      }
    }
  })
  return attributes
}

/*
Tiny.initTEIEditor = () => {
  Tiny.init({
    selector: 'textarea.tinymce-transcription',
    plugins: ['paste', 'link', 'code'],
    valid_elements: '*[*]',
    entity_encoding: 'raw',
    menubar: 'structure',
    menu: {
      structure: {
        title: 'Structure',
        items: buttons.tei1.map(button => button.cl).join(' ')
      }
    },
    toolbar1: 'undo redo code',
    setup: (editor) => {
      getMenuStructure()
      // add buttons to tiny
      buttons.tei1.forEach(button => {
        editor.addMenuItem(button.cl, {
          text: button.cl,
          data: button,
          icon: button.icon,
          classes: button.cl,
          //context: 'TEI', <- à priori OSEF
          //bouton: button, // Paramètre custom pour accès dans onClick
          //disabledStateSelector: ':not(a)', // ?
          onclick: function () {
            const selection = Tiny.activeEditor.selection.getContent({
              format: 'text'
            })
            editor.windowManager.open({
              title: this.settings.data.cl.toUpperCase() + ' Attributes',
              body: addFormElements(this.settings.data),
              onsubmit: (e) => {
                this.data = e.data
                editor.undoManager.transact(() => {
                  let attributes = ''
                  Object.keys(this.data).forEach(key => {

                    const value = this.data[key]
                    if (value != 'none') {
                      attributes += `${key}="${value}"`
                    }
                  })

                  Tiny.activeEditor.selection.setContent(
                    `<${button.cl} ${attributes}>
                      ${selection}
                    </${button.cl}>`
                  )
                })
              }
            })
          }
        })
      })
    }
  }).then((editors) => {
    // load content into tinyMCE
    editors.forEach(editor => {
      editor.load()
    })
  })
}

function addFormElements(data) {
  let formElement = {}
  let formElement2 = '' //??????
  if (typeof data.values != 'undefined') {
    // push a none value if not present ?
    if (data.values[data.values.length - 1].text !== 'none') {
      data.values.push({
        text: 'none',
        value: 'none'
      })
    }
    formElement = {
      type: 'listbox',
      name: data.att,
      label: data.att, // to be translated
      values: data.values
    }
  } else if (typeof data.att != 'undefined') {
    formElement = {
      type: 'textbox',
      name: data.att,
      label: data.att // to be translated
    }
    formElement['att'] = data.att
  } else {
    formElement = null
  }
  // normalement on s'en sert pas
  if (typeof data.att2 != 'undefined') {
    // TODO : Textbox to type attributes' values
    // TODO : condition + valeurs exactes
    formElement2 = {
      type: 'textbox',
      name: data.att2,
      label: data.att2
    }

  }
  // du coup possible de contruire un array.map ou qq chose du genre
  return [formElement, formElement2]
}
*/

/*
 * Build popup form for a button
 */



module.exports = Tiny
