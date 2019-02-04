$(document).ready(function () {
  $('form').submit(function () {
    $('[type=\'submit\']', this)
      .html(Translator.trans('please_wait'))
      .attr('disabled', 'disabled')

    return true
  })
})
