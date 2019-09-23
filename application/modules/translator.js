/* global require module */
const Translator = require('bazinga-translator')

// this version loads translations from messages domain (and only this domain)
const url = Routing.generate('bazinga_jstranslation_js', {
  _format: 'json'
})

$.ajax({
  method: 'GET',
  url: url
}).done((response) => {
  // translations from messages domain (and only this domain) are now loaded
  Translator.fromJSON(response)
})

module.exports = Translator
