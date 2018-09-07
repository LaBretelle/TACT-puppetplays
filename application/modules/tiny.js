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
    toolbar1: 'undo redo | remove-current-tag | code',
    setup: (editor) => {
      editor.addButton('remove-current-tag', {
        text: 'x',
        tooltip: 'Delete current tag',
        onclick: () => {
          deleteCurrentTag(tei)
        }
      })
    },
    init_instance_callback: (editor) => {
      editor.on('click', () => {
        refreshPanels(tei)
      }),
      editor.on('change', () => {
        document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
      }),
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
  const container = document.querySelector('.elements-container')
  root.innerHTML = ''
  if (current) {
    current.childrens.forEach(tagName => {
      appendLiToAllowedElements(tei, root, tagName)
    })
    if (current.childrens.length === 0) {
      container.style.display = 'none'
    } else {
      container.style.display = 'flex'
    }
  } else {
    tei.elements.forEach(element => {
      appendLiToAllowedElements(tei, root, element.tag)
      container.style.display = 'flex'
    })
  }
}

const displayCurrentAttributes = (currentTeiElement, currentTinyElement) => {
  const container = document.querySelector('.element-attributes-container')
  const elementTitle = document.querySelector('.element-title')
  const elementAttributes = document.querySelector('.element-attributes')
  elementTitle.innerHTML = ''
  elementAttributes.innerHTML = ''
  if (currentTeiElement) {
    container.style.display = 'flex'
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

      control.classList.add('form-control', 'form-control-sm')
      const li = document.createElement('li')
      li.classList.add('list-group-item')
      li.appendChild(label)
      const help = createHelp(teiAttribute.help)
      li.appendChild(help)
      li.appendChild(control)
      elementAttributes.appendChild(li)
    })
  } else {
    container.style.display = 'none'
  }

}

const createHelp = (text) => {
  const help = document.createElement('span')
  help.classList.add('float-right')
  help.innerHTML = '<i class="fas fa-question-circle"></i>'
  help.setAttribute('data-content', text)
  help.setAttribute('data-toggle', 'popover')
  return help
}

const appendOptionToUl = (ul, text, selected) => {
  const option = document.createElement('option')
  option.value = text
  option.selected = selected
  option.text = text
  ul.appendChild(option)
}

const appendLiToAllowedElements = (tei, root, tagName) => {
  const teiElement = tei.elements.find(element => element.tag === tagName)
  const li = document.createElement('li')
  const help = createHelp(teiElement.help)
  li.textContent = tagName
  li.appendChild(help)
  li.classList.add('list-group-item')
  root.appendChild(li)
  li.addEventListener('click', () => {
    addTeiTag(teiElement)
  })
}

const addTeiTag = (teiElement) => {
  const editor = Tiny.activeEditor
  const selectedContent = editor.selection.getContent({
    format: 'html'
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

const deleteCurrentTag = (tei) => {
  let currentTinyElement = Tiny.activeEditor.selection.getNode()
  // test if current tag is not the root tag.
  if (!currentTinyElement.classList.contains('tiny-root') ) {
    Tiny.activeEditor.dom.remove(currentTinyElement, true)
    refreshPanels(tei)
  }
}

const refreshPanels = (tei) => {
  const currentTinyElement = Tiny.activeEditor.selection.getNode()
  const currentTeiElement = tei.elements.find(element => element.tag.toUpperCase() === currentTinyElement.nodeName.toUpperCase())
  displayCurrentAttributes(currentTeiElement, currentTinyElement)
  getAllowedElements(tei, currentTeiElement)
  $('[data-toggle="popover"]').popover({
    html : true,
    placement: 'top',
    trigger: 'hover'
  })
}

module.exports = Tiny
