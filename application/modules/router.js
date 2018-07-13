
/* global require module */
const router = require('./../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js')
// dumped_routes.json is the output file for the fos:js-routing:dump command
const routerConfig = require('./../public/routes/fos_js_routes.json')

router.setRoutingData(routerConfig)

module.exports = router
