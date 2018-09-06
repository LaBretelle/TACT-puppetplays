/* global require module */
const Tiny = require('tinymce')

// A theme is also required
require('tinymce/themes/modern/theme')
// Any plugins you want to use has to be imported
require('tinymce/plugins/code')
require('tinymce/plugins/link')

Tiny.initEditor = () => {
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

Tiny.initTEIEditor = (tei) => {
  Tiny.init({
    selector: 'textarea.tinymce-transcription',
    plugins: ['code'],
    branding: false,
    content_css: '/build/css/tiny.css',
    forced_root_block: 'div',
    forced_root_block_attrs: {
      'class': 'tiny-root'
    },
    valid_elements: '*[*]',
    entity_encoding: 'raw',
    menubar: false,
    statusbar: true,
    toolbar1: 'undo redo code',
    init_instance_callback: (editor) => {
      editor.on('click', (e) => {
        const currentTinyElement = e.target
        const currentTeiElement = tei.elements.find(element => element.tag.toUpperCase() === currentTinyElement.nodeName.toUpperCase())
        displayCurrentAttributes(currentTeiElement, currentTinyElement)
        getAllowedElements(tei, currentTeiElement)
      }),
      editor.on('change', () => {
        document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
      })

      document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
    }
  }).then((editors) => {
    // load content into tinyMCE
    editors.forEach(editor => {
      editor.load()
    })

  })
}

const getAllowedElements = (tei, current) => {
  const root = document.querySelector('.elements')
  root.innerHTML = ''
  if (current) {
    current.childrens.forEach(tagName => {
      appendLiToAllowedElements(tei, root, tagName)
    })
  } else {
    tei.elements.forEach(element => {
      appendLiToAllowedElements(tei, root, element.tag)
    })
  }
}

const displayCurrentAttributes = (currentTeiElement, currentTinyElement) => {
  const elementTitle = document.querySelector('.element-title')
  const elementAttributes = document.querySelector('.element-attributes')
  elementTitle.innerHTML = ''
  elementAttributes.innerHTML = ''
  if (currentTeiElement) {
    const cardTitle = document.createTextNode(currentTinyElement.nodeName)
    elementTitle.appendChild(cardTitle)
    currentTeiElement.attributes.forEach(teiAttribute => {
      const tinyAttr = currentTinyElement.attributes.getNamedItem(teiAttribute.key)
      const label = document.createTextNode(teiAttribute.key)
      let control
      switch (teiAttribute.type) {
        case 'text':
          control = document.createElement('input')
          control.setAttribute('value', tinyAttr ? tinyAttr.value : '')
          break
        case 'enumerated':
          control = document.createElement('select')
          if(!teiAttribute.required) {
            appendOptionToUl(control, 'none', tinyAttr ? 'none' === tinyAttr.value : false)
          }
          teiAttribute.values.forEach(value => {
            appendOptionToUl(control, value, tinyAttr ? value === tinyAttr.value : false)
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
}

const appendOptionToUl = (ul, text, selected) => {
  const option = document.createElement('option')
  option.value = text
  option.selected = selected
  option.text = text
  ul.appendChild(option)
}

const appendLiToAllowedElements = (tei, root, tagName) => {
  const li = document.createElement('li')
  li.textContent = tagName
  li.classList.add('list-group-item')
  root.appendChild(li)
  li.addEventListener('click', () => {
    const teiElement = tei.elements.find(element => element.tag === tagName)
    addTeiTag(teiElement)
  })
}

const addTeiTag = (teiElement) => {
  const editor = Tiny.activeEditor
  const selectedContent = editor.selection.getContent({
    format: 'text'
  })
  editor.undoManager.transact(() => {
    if (teiElement.selfClosed) {
      editor.insertContent(
        `<${teiElement.tag} data-tag="${teiElement.tag}"></${teiElement.tag}>&zwj;${selectedContent}`
      )
    } else {
      editor.selection.setContent(
        `<${teiElement.tag} data-tag="${teiElement.tag}">${selectedContent ? selectedContent:'&zwj;&zwj;'}</${teiElement.tag}>&zwj;`
      )
    }
  })
}

module.exports = Tiny
