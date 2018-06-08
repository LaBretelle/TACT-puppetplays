// Import TinyMCE
import tinymce from 'tinymce/tinymce';

// A theme is also required
import 'tinymce/themes/modern/theme';

// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';

$(document).ready(() => {

  $('[data-toggle="popover"]').popover();
  $('[data-toggle="tooltip"]').tooltip();
  // Initialize the app
  tinymce.init({
      selector: 'textarea.tinymce-enabled',
      plugins: ['paste', 'link']
  });

  tinymce.triggerSave(); 


});
