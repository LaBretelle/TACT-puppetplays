/**
 * Helper Module for js routes
 * https://symfony.com/doc/master/bundles/FOSJsRoutingBundle/usage.html
 * Update routes :
 * bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json
 */

import Routing from './../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


export default class AppRouting {

  constructor() {
    this.routes = require('./../../../public/routes/fos_js_routes.json');
    Routing.setRoutingData(this.routes);
    this.routing = Routing;
  }

  generateRoute(name, params = {}, absolute = false) {
    // Generate a route with absolute url
    // Routing.generate('route_name', /* your params */, true)
    // Routing.generate('my_route_to_expose', { id: 10, foo: "bar" });
    return this.routing.generate(name, params, absolute);
  }


}
