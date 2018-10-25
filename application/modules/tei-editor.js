/* global require module */
const Tiny = require('tinymce')
require('tinymce/themes/modern/theme')
require('tinymce/plugins/code')

class TeiEditor {
  constructor(tei) {
    this.tei = tei
  }

  init() {
    Tiny.init({
      selector: 'textarea.tinymce-transcription',
      plugins: ['code'],
      branding: false,
      content_css: [
        '/build/css/tiny.css'
      ],
      forced_root_block: 'p',
      valid_elements: '*[*]',
      entity_encoding: 'raw',
      menubar: false,
      statusbar: true,
      toolbar1: 'undo redo | remove-current-tag | code',
      setup: (editor) => {
        editor.addButton('remove-current-tag', {
          text: '',
          icon: 'fas fa-delete', // defined in app.scss
          tooltip: Translator.trans('delete_current_tag'),
          onclick: () => {
            this.deleteCurrentTag(this.tei)
          }
        })
      },
      init_instance_callback: (editor) => {
        editor.on('click', () => {
          this.refreshPanels(this.tei)
        }),
        editor.on('change', () => {
          document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
        }),
        document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()

        // allow user to filter allowed elements
        const input = document.querySelector('.filter-elements')
        input.addEventListener('input', (e) => {
          const elements = document.querySelector('.elements').children
          Array.prototype.forEach.call(elements, (el) => {
            el.hidden = el.textContent.toLowerCase().indexOf(e.target.value.toLowerCase()) === -1
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

  /*
   * Get element(s) allowed as children of the current element if any
   * If no current element, display all TEI elements
   */
  getAllowedElements(tei, current) {
    const root = document.querySelector('.elements')
    const container = document.querySelector('.elements-container')
    root.innerHTML = ''
    if (current) {
      current.childrens.forEach(tagName => {
        this.appendLiToAllowedElements(tei, root, tagName)
      })
      this.displayContainer(container, current.childrens.length > 0)
    } else {
      tei.elements.forEach(element => {
        this.appendLiToAllowedElements(tei, root, element.tag)
        this.displayContainer(container, true)
      })
    }
  }

  /*
   * Display available attributes for the selected element
   * If no current element, hide the panel
   */
  displayCurrentAttributes(currentTeiElement, currentTinyElement){
    const container = document.querySelector('.element-attributes-container')
    const elementTitle = document.querySelector('.element-title')
    const elementAttributes = document.querySelector('.element-attributes')
    elementTitle.innerHTML = ''
    elementAttributes.innerHTML = ''
    if (currentTeiElement) {
      this.displayContainer(container, true)
      const cardTitle = document.createTextNode('[' + currentTinyElement.nodeName + ']')
      elementTitle.appendChild(cardTitle)
      currentTeiElement.attributes.forEach(teiAttribute => {
        const tinyAttr = currentTinyElement.attributes.getNamedItem(teiAttribute.key)
        let control
        switch (teiAttribute.type) {
          case 'text':
            control = document.createElement('input')
            control.setAttribute('value', tinyAttr ? tinyAttr.value : '')
            break
          case 'enumerated':
            control = document.createElement('select')
            if(!teiAttribute.required) {
              this.appendOptionToSelect(control, 'none', tinyAttr ? 'none' === tinyAttr.value : false)
            }
            teiAttribute.values.forEach(value => {
              this.appendOptionToSelect(control, value, tinyAttr ? value === tinyAttr.value : false)
            })
            break
        }
        control.classList.add('form-control', 'form-control-sm')
        control.required = teiAttribute.required
        control.addEventListener(teiAttribute.type === 'text' ? 'input' : 'change', (e) => {
          currentTinyElement.setAttribute(teiAttribute.key, e.target.value)
        })

        let label = teiAttribute.key
        label += teiAttribute.required ? ' *' : ''
        const li = this.createLiElement(label, teiAttribute, true)
        li.appendChild(control)
        elementAttributes.appendChild(li)
      })
    } else {
      this.displayContainer(container, false)
    }
  }

  /*
   * Append the proper tag to tinymce content
   */
  addTeiTag(teiElement) {
    const editor = Tiny.activeEditor
    const selectedContent = editor.selection.getContent({
      format: 'html'
    })
    editor.undoManager.transact(() => {
      if (teiElement.selfClosed) {
        editor.insertContent(
          `<${teiElement.tag}></${teiElement.tag}>&zwj;${selectedContent}`
        )
      } else {
        editor.selection.setContent(
          `<${teiElement.tag}>${selectedContent ? selectedContent:'&zwj;&zwj;'}</${teiElement.tag}>&zwj;`
        )
      }
    })
  }

  /*
   * Delete current tag from Tiny without deleting its content.
   * (test if current tag is not the root tag.)
   */
  deleteCurrentTag(tei) {
    const editor = Tiny.activeEditor
    let currentTinyElement = Tiny.activeEditor.selection.getNode()
    //if (!currentTinyElement.classList.contains('tiny-root') ) {
    editor.undoManager.transact(() => {
      Tiny.activeEditor.dom.remove(currentTinyElement, true)
      this.refreshPanels(tei)
    })
    //}
  }

  /*
   * Create popover HTML
   * popovers are initialized in refreshPanels() method
   */
  createHelp(teiElement, isAttribute) {

    const help = document.createElement('button')
    help.classList.add('btn', 'btn-link')
    help.innerHTML = '<i class="fas fa-question-circle"></i>'
    help.setAttribute('data-toggle', 'popover')

    const title = isAttribute ? teiElement.key :  teiElement.tag
    const link = Translator.locale === 'fr' ? teiElement.link_fr: teiElement.link_en
    const linkText = isAttribute ? Translator.trans('tei_link_to_official_attr_doc', {}) : Translator.trans('tei_link_to_official_doc', {'element': title})
    const popoverContent = `
      <div>
        <h6>${title}</h6>
        <hr/>
        <p>${Translator.trans(teiElement.help, {}, 'tei')}</p>
        <p>
          <a target="_blank" href="${link}">
            ${linkText}
          </a>
        </p>
      </div>
    `
    help.setAttribute('data-content', popoverContent)
    return help
  }

  /*
   * Create LI element with a given text and a given help text
   */
  createLiElement(labelText, teiElement, isAttribute = false) {
    const li = document.createElement('li')
    const labelContainer = document.createElement('span')
    const label = document.createTextNode(labelText)
    labelContainer.appendChild(label)
    const help = this.createHelp(teiElement, isAttribute)
    const btnGroup = document.createElement('div')
    btnGroup.classList.add('btn-group')
    if(!isAttribute) {
      const addBtn = document.createElement('button')
      addBtn.classList.add('btn', 'btn-link')
      addBtn.setAttribute('title', Translator.trans('add', {}))
      addBtn.innerHTML = '<i class="fas fa-plus"></i>'

      addBtn.addEventListener('click', () => {
        this.addTeiTag(teiElement)
      })
      btnGroup.appendChild(addBtn)
    }

    btnGroup.appendChild(help)
    li.appendChild(labelContainer)
    li.appendChild(btnGroup)
    li.classList.add('list-group-item', 'tei-element-li')

    return li
  }

  /*
   * Create OPTION element and append to SELECT element
   */
  appendOptionToSelect(ul, text, selected) {
    const option = document.createElement('option')
    option.value = text
    option.selected = selected
    option.text = text
    ul.appendChild(option)
  }

  /*
   *  Create LI element and append to allowed elements UL
   */
  appendLiToAllowedElements(tei, root, tagName) {
    const teiElement = tei.elements.find(element => element.tag === tagName)
    const li = this.createLiElement(tagName, teiElement)
    root.appendChild(li)
  }

  /*
   * Refresh right panels with current tiny element
   * Display attributes & allowed children.
   */
  refreshPanels(tei) {
    const currentTinyElement = Tiny.activeEditor.selection.getNode()
    const currentTeiElement = tei.elements.find(element => element.tag.toUpperCase() === currentTinyElement.nodeName.toUpperCase())
    this.displayCurrentAttributes(currentTeiElement, currentTinyElement)
    this.getAllowedElements(tei, currentTeiElement)
    $('[data-toggle="popover"]').popover({
      html : true,
      placement: 'top'
    })
    document.querySelector('.filter-elements').value = ''
  }

  getContent() {
    return Tiny.get('tiny-content').getContent()
  }

  /*
   * Toggle container display
   */
  displayContainer(container, show) {
    const value = show ? 'flex' : 'none'
    container.style.display = value
  }
}

module.exports = TeiEditor
