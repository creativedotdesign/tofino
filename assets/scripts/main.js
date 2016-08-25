// Assign jQuery to $
var $ = window.jQuery;

// Import router
import Router from './router';

// Import local deps
import common from './routes/common';

// Import ajaxForm
import './ajax-form';

// Include the full Bootstrap JS lib.
import 'bootstrap/dist/js/bootstrap';

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
const routes = {
  // All pages
  common
};

// Load Events
$(document).ready(() => new Router(routes).loadEvents());
