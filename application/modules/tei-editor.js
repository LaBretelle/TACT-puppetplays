/* global require module */
const Tiny = require('tinymce')
require('tinymce/themes/silver')
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
        '/css/tiny-20190401D.css'
      ],
      forced_root_block: false,
      valid_elements: '*[*]',
      //extended_valid_elements : '*[*]',
      //allow_html_in_named_anchor: true,
      valid_children: '+body[title]',
      entity_encoding: 'raw',
      menubar: false,
      statusbar: true,
      toolbar1: 'undo redo | remove-current-tag | code',
      setup: (editor) => {
        editor.ui.registry.addButton('remove-current-tag', {
          icon: 'remove',
          tooltip: Translator.trans('delete_current_tag'),
          onAction: () => {
            this.deleteCurrentTag(this.tei)
          }
        })
      },
      init_instance_callback: (editor) => {
        editor.on('click', () => {
          this.refreshPanels(this.tei)
        }),
        editor.on('input', () => {
          document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
          this.handleSaveBtn()
        }),
        editor.on('change', () => {
          document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()
          this.handleSaveBtn()
        }),
        document.getElementById('preview').innerHTML = Tiny.activeEditor.getContent()

        // allow user to filter allowed elements
        const input = document.querySelector('.filter-elements')
        if (input) {
          input.addEventListener('input', (e) => {
            const elements = document.querySelector('.elements').children
            Array.prototype.forEach.call(elements, (el) => {
              el.hidden = el.textContent.toLowerCase().indexOf(e.target.value.toLowerCase()) === -1
            })
          })
        }
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

    this.emptyElement(root)
    let count = 0
    let fragment = document.createDocumentFragment()

    if (current) {
      count = current.childrens.length

      let childrenObject = []
      current.childrens.forEach(child => {
        let element = this.tei.elements.find(function (el){
          return el.tag === child
        })
        childrenObject.push(element)
      })
      this.sortElementsByCounter(childrenObject)

      childrenObject.forEach(child => {
        this.appendLiToAllowedElements(tei, fragment, child.tag)
      })

    } else {
      count = tei.elements.length
      this.sortElementsByCounter(tei.elements)
      tei.elements.forEach(element => {
        this.appendLiToAllowedElements(tei, fragment, element.tag)
      })
    }

    this.displayContainer(container, count > 0)
    root.appendChild(fragment)
  }

  findElementByTagName(tagName){

    return this.tei.elements.find(function (el){
      return el.tag === tagName
    })
  }

  /*
   * Display available attributes for the selected element
   * If no current element, hide the panel
   */
  displayCurrentAttributes(currentTeiElement, currentTinyElement){
    const container = document.querySelector('.element-attributes-container')
    const elementTitle = document.querySelector('.element-title')
    const elementAttributes = document.querySelector('.element-attributes')

    this.emptyElement(elementTitle)
    this.emptyElement(elementAttributes)

    if (currentTeiElement) {
      this.displayContainer(container, true)
      const cardTitle = document.createTextNode('[' + currentTinyElement.nodeName + ']')
      elementTitle.appendChild(cardTitle)

      if (currentTeiElement.attributes.length > 0) {
        currentTeiElement.attributes.forEach(teiAttribute => {
          const tinyAttr = currentTinyElement.attributes.getNamedItem(teiAttribute.key)
          let control
          switch (teiAttribute.type) {
            case 'string':
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

          control.addEventListener(teiAttribute.type === 'string' ? 'input' : 'change', (e) => {
            currentTinyElement.setAttribute(teiAttribute.key, e.target.value)
          })

          let label = teiAttribute.key
          label += teiAttribute.required ? ' *' : ''
          const li = this.createLiElement(label, teiAttribute, true)
          li.appendChild(control)
          elementAttributes.appendChild(li)
        })
      } else {
        const li = document.createElement('li')
        li.innerHTML = Translator.trans('no_attribute')
        li.classList.add('list-group-item', 'tei-element-li', 'text-muted')
        elementAttributes.appendChild(li)
      }

    } else {
      this.displayContainer(container, false)
    }
  }

  /*
   * Append the proper tag to tinymce content and refresh available tags and attributes
   */
  addTeiTag(teiElement) {
    const editor = Tiny.activeEditor
    const selectedContent = editor.selection.getContent({
      format: 'html'
    })
    // keep selected range to reselect after content insert
    const range = editor.selection.getRng()

    this.maximizeCounter(teiElement)
    let attributes = {'data-tag': teiElement.tag}
    editor.undoManager.transact(() => {
      let elem = null
      if (teiElement.selfClosed) {
        elem = editor.dom.create(teiElement.tag, attributes)
      } else {
        elem = editor.dom.create(teiElement.tag, attributes, selectedContent ? selectedContent : '<>')
        range.deleteContents()
      }

      range.insertNode(elem)
      editor.selection.select(elem, true)
      editor.focus()
      this.refreshPanels(this.tei)
    })
  }

  handleSaveBtn(){
    let btn = document.getElementById('main-save-btn')
    btn.classList.add('btn-info')
    btn.classList.remove('btn-outline-secondary')
  }

  /*
   * Delete current tag from Tiny without deleting its content.
   */
  deleteCurrentTag(tei) {
    const editor = Tiny.activeEditor
    let currentTinyElement = Tiny.activeEditor.selection.getNode()
    if (!currentTinyElement.classList.contains('mce-content-body')) {
      editor.undoManager.transact(() => {
        Tiny.activeEditor.dom.remove(currentTinyElement, true)
        this.refreshPanels(tei)
      })
    }
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
    btnGroup.classList.add('btn-group', 'btn-group-sm', 'btn-tag-tei')
    if(!isAttribute) {
      /* ADD TAG BUTTON */
      const addBtn = document.createElement('button')
      addBtn.classList.add('btn', 'btn-sm', 'btn-outline-secondary', 'mr-1')
      addBtn.setAttribute('title', Translator.trans('add', {}))
      addBtn.innerHTML = '<i class="fas fa-plus"></i>'
      addBtn.addEventListener('click', () => {
        this.addTeiTag(teiElement)
      })

      /* UP BUTTON */
      const upBtn = document.createElement('button')
      upBtn.classList.add('btn', 'btn-link')
      upBtn.setAttribute('title', Translator.trans('tag_up', {}))
      upBtn.innerHTML = '<i class="fas fa-arrow-up"></i>'
      upBtn.addEventListener('click', () => {
        this.maximizeCounter(teiElement)
      })

      /* DOWN BUTTON */
      const downBtn = document.createElement('button')
      downBtn.classList.add('btn', 'btn-link')
      downBtn.setAttribute('title', Translator.trans('tag_down', {}))
      downBtn.innerHTML = '<i class="fas fa-arrow-down"></i>'
      downBtn.addEventListener('click', () => {
        this.minimizeCounter(teiElement)
      })

      labelContainer.prepend(addBtn)
      btnGroup.appendChild(upBtn)
      btnGroup.appendChild(downBtn)
    }

    btnGroup.appendChild(help)

    li.appendChild(labelContainer)
    labelContainer.appendChild(btnGroup)
    li.classList.add('list-group-item', 'tei-element-li')

    return li
  }

  /* HANDLE ELEMENT COUNTER
  *************************/
  maximizeCounter(teiElement){
    teiElement.counter = this.getMaxCounter() + 1
    this.refreshPanels(this.tei)
  }

  minimizeCounter(teiElement){
    teiElement.counter = this.getMinCounter() - 1
    this.refreshPanels(this.tei)
  }

  getMaxCounter(){
    this.sortElementsByCounter(this.tei.elements)

    return (this.tei.elements[0].counter) ? this.tei.elements[0].counter : 0
  }

  getMinCounter(){
    this.sortElementsByCounter(this.tei.elements)
    let length = this.tei.elements.length

    return (this.tei.elements[length - 1].counter) ? this.tei.elements[length - 1].counter : 0
  }

  sortElementsByCounter(array){
    return array.sort(function (a,b){
      if (!a.counter) a.counter = 0
      if (!b.counter) b.counter = 0
      return (a.counter < b.counter) ? 1 : -1
    })
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
    if (typeof tei.elements !== 'undefined' ) {
      const currentTinyElement = Tiny.activeEditor.selection.getNode()
      const currentTeiElement = (currentTinyElement.id != 'tinymce')
        ? tei.elements.find(element => element.tag.toUpperCase() === currentTinyElement.nodeName.toUpperCase())
        : null

      this.displayCurrentAttributes(currentTeiElement, currentTinyElement)
      this.getAllowedElements(tei, currentTeiElement)
      $('[data-toggle="popover"]').popover({
        html : true,
        placement: 'top',
        trigger: 'focus'
      })
      document.querySelector('.filter-elements').value = ''
    }
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

    document.getElementById('selected-element').style.display = 'block'
  }

  emptyElement(el){
    while (el.firstChild) el.removeChild(el.firstChild)
  }
}

module.exports = TeiEditor
