$(document).ready(function () {
  $('form').submit(function () {
    $('[type=\'submit\']', this)
      .html('Please Wait...')
      .attr('disabled', 'disabled')

    return true
  })
})
