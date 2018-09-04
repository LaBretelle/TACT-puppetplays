/* global require module */
const Tiny = require('tinymce')

// A theme is also required
require('tinymce/themes/modern/theme')

// Any plugins you want to use has to be imported
require('tinymce/plugins/paste')
require('tinymce/plugins/code')
require('tinymce/plugins/link')
require('tinymce/plugins/lists')

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
  // need to be an object ... but we pass an array...
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
    plugins: ['paste', 'link', 'code', 'lists'],
    content_css : '/build/css/transcription.css',
    forced_root_block : '',
    valid_elements: '*[*]',
    entity_encoding: 'raw',
    menubar: tei.modules.join(' '),
    menu: getMenuStructure(tei),
    toolbar1: 'undo redo code | numlist',
    init_instance_callback: (editor) => {
      editor.on('click', (e) => {
        const currentTinyElement = e.target
        const currentTeiElement = tei.elements.find(element => element.tag.toUpperCase() === currentTinyElement.nodeName.toUpperCase())
        if(currentTeiElement) {
          const elementTitle = document.querySelector('.element-title')
          const elementAttributes = document.querySelector('.element-attributes')
          const cardTitle = document.createTextNode(currentTinyElement.nodeName)
          // empty content
          elementTitle.innerHTML = ''
          elementAttributes.innerHTML = ''
          elementTitle.appendChild(cardTitle)
          // iterate through currentTeiElement.attributes
          currentTeiElement.attributes.forEach(teiAttribute => {

            const tinyAttr = currentTinyElement.attributes.getNamedItem(teiAttribute.key)
            const label = document.createTextNode(teiAttribute.key)
            let control
            switch(teiAttribute.type) {
              case 'text':
                // create text input element
                control = document.createElement('input')
                // set its value if any
                control.setAttribute('value', tinyAttr ? tinyAttr.value: '')
                break
              case 'enumerated':
                control = document.createElement('select')
                // create select options
                teiAttribute.values.forEach(value => {
                  const option = document.createElement('option')
                  option.value = value
                  option.selected = tinyAttr ? value === tinyAttr.value : ''
                  option.text = value
                  control.appendChild(option)
                })
                break
            }

            control.addEventListener(teiAttribute.type === 'text' ? 'input' : 'change', (e) => {
              currentTinyElement.setAttribute(teiAttribute.key, e.target.value)
            })

            control.classList.add('form-control')
            const li = document.createElement('li')
            li.classList.add('list-group-item')
            li.appendChild(label)
            li.appendChild(control)
            elementAttributes.appendChild(li)
          })
        }
      })
    },
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
                  let attributes = `data-tag="${element.tag}"`
                  Object.keys(this.data).forEach(key => {

                    const value = this.data[key]
                    if (value !== 'none') {
                      attributes += `${key}="${value}"`
                    }
                  })

                  // element.selfClosed does not work... tiny seems to happend the opening and closing tags automatically...
                  if(element.selfClosed){
                    Tiny.activeEditor.insertContent(
                      `<${element.tag} ${attributes}/>${selection}`
                    )
                  } else {
                    Tiny.activeEditor.selection.setContent(
                      `<${element.tag} ${attributes}>${selection ? selection:' '}</${element.tag}>`
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
      // add none value if attr is not required
      if (!attribute.required && !attribute.values.find(value => value === 'none')) {
        attribute.values.unshift('none')
      }
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

module.exports = Tiny
