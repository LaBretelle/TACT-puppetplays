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
    valid_elements: '*[*]',
    entity_encoding: 'raw',
    menubar: tei.modules.join(' '),
    menu: getMenuStructure(tei),
    toolbar1: 'undo redo code | numlist',
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
                    if (value !== 'none') {
                      attributes += `${key}="${value}"`
                    }
                  })

                  // element.selfClosed does not work... tiny seems to happend the opening and closing tags automatically...
                  if(element.selfClosed){
                    /*Tiny.activeEditor.selection.setContent(
                      `<${element.tag} ${attributes} />${selection}`
                    )*/
                    // it replace the selection if any... bot for a self closed tag we dont want this behaviour
                    /*Tiny.activeEditor.insertContent(
                      `<${element.tag} ${attributes} />`
                    )*/

                    Tiny.activeEditor.insertContent(
                      `<${element.tag} ${attributes} />${selection}`
                    )
                  } else {
                    Tiny.activeEditor.selection.setContent(
                      `<${element.tag} ${attributes}>${selection}</${element.tag}>`
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



function activate_menu_items() {
	// Element type selected in TinyMCE
	element = tinymce.activeEditor.selection.getNode();
	elementType = element.nodeName;
	// Tous les items visibles : ceux qui viennent d'apparaître suite au clic sur le menu
	menuItems = $('.mce-menu-item:visible span');
  console.log('yope', menuItems)
	$.each(menuItems, function(i, menuItem) {
  	menuName = $(menuItem).text();
		var balise = menuName.substr(0, menuName.indexOf(' '));
		var contexte = tag_context[balise];



// 		var contexte = 1; // temporaire == tout est légal
		if (typeof contexte != 'undefined') {
			// Remplacement des balises "spéciales" pour qu'elles soient un contexte valide
			i = contexte.indexOf('HEAD');
			contexte[i] = 'HEADD';
			i = contexte.indexOf('TITLE');
			contexte[i] = 'TITTLE';
			i = contexte.indexOf('FIGURE');
			contexte[i] = 'FFIGURE';
			if ($.inArray(elementType, contexte) > -1 || balise == 'DIV' && elementType == 'BODY') {
				// On le marque comme visible si le contexte l'autorise ...
				$(menuItem).parent().addClass('tei-ok');
			} else {
				// ... et invisible si non ...
				$(menuItem).parent().addClass('tei-ko');
			}
		}
	});
	 // ... et enfin on applique les propriétés à tous les items en même temps.
	$('.tei-ok').show();
	$('.tei-ko').hide();
}

$('body').on('click', '.mce-tinymce > .mce-container-body .mce-btn', function() {
	activate_menu_items();
});
$('body').on('mouseenter', '.mce-tinymce > .mce-container-body .mce-btn', function() {
	activate_menu_items();
});

const addFormElementAttr = (element) => {
  const attributes = element.attributes.map(attribute => {
    if (attribute.type === 'text') {
      return {
        type: 'textbox',
        name: attribute.key,
        label: attribute.label
      }
    } else if (attribute.type === 'enumerated') {
      let listValues = attribute.values.map(entry => {
        return {
          text: entry,
          value: entry
        }
      })
      // add a none value so that we wont happend values proposed but not wanted
      listValues.push({
        text: 'none',
        value: 'none'
      })
      return {
        type: 'listbox',
        name: attribute.key,
        label: attribute.label,
        values: listValues
      }
    }
  })
  return attributes
}

module.exports = Tiny
