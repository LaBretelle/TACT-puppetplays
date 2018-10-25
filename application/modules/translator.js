/* global require module */
const Translator = require('bazinga-translator')

/*

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
*/


let translations = {}

// this will load ONLY default trans domain... and it can not be ocnfigured to load multiple domains at once
const url = Routing.generate('bazinga_jstranslation_js', {
  _format: 'json'
})



$.ajax({
  method: 'GET',
  url: url
}).done((response) => {
  // messages domain are now loaded
  translations = response
}).then(() => {
  // quite awfull isn't it ?
  const url = Routing.generate('bazinga_jstranslation_js', {
    _format: 'json',
    domain: 'tei'
  })

  $.ajax({
    method: 'GET',
    url: url
  }).done((response) => {
    // tei domaine are now loaded
    translations.translations[Translator.locale].tei = response.translations[Translator.locale].tei
  }).then(() => {
    Translator.fromJSON(translations)
  })

})

module.exports = Translator
