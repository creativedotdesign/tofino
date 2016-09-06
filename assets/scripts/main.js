// Assign jQuery to $
var $ = window.jQuery;

// Import router
import Router from './router';

// Import local deps
import common from './routes/common';

// Import ajaxForm
import './ajax-form';

/**
 * Tether (http://github.hubspot.com/tether/)
 *
 * Tether is a Bootstrap 4 dependency when including Tooltip or Popover
 * You must include Tether if including the full Bootstrap JS code or tooltip.js or popover.js
 */
//import Tether from 'tether';  // eslint-disable-line no-unused-vars

/**
 * Bootstrap Components
 *
 * If your build requires Tooltip or Popover then you must include Tether.
 */
/* eslint-disable no-unused-vars */
// import Alert from 'bootstrap';
// import Button from 'bootstrap';
// import Carousel from 'bootstrap';
import Collapse from 'bootstrap';
import Dropdown from 'bootstrap';
// import Modal from 'bootstrap';
// import Popover from 'bootstrap';
// import Scrollspy from 'bootstrap';
// import Tab from 'bootstrap';
// import Tooltip from 'bootstrap';
// import Util from 'bootstrap';
/* eslint-enable no-unused-vars */

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
const routes = {
  // All pages
  common
};

// Load Events
$(document).ready(() => new Router(routes).loadEvents());
