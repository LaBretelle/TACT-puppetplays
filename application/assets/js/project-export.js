let transcrCheckbox = document.querySelector('#export_transcriptions')
let xslCheckbox = document.querySelector('#export_transcriptions_apply_xsl')
let metadatasCheckbox = document.querySelector('#export_transcriptions_metadatas')

transcrCheckbox.addEventListener('change', function () {
  if (!this.checked) {
    xslCheckbox.checked = false
    metadatasCheckbox.checked = false
  }
})

xslCheckbox.addEventListener('change', function () {
  uncheckSub(this)
})

metadatasCheckbox.addEventListener('change', function () {
  uncheckSub(this)
})

function uncheckSub(checkbox) {
  if (checkbox.checked) {
    if (!transcrCheckbox.checked) {
      checkbox.checked = false
    }
  }
}