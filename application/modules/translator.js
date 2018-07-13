/* global require module */

const Translator = require('bazinga-translator')

const url = Routing.generate('bazinga_jstranslation_js', {
  _format: 'json'
})

$.ajax({
  method: 'GET',
  url: url
}).done((response) => {
  Translator.fromJSON(response)
})

module.exports = Translator
