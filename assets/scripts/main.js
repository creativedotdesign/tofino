// Import router
import Router from './router';

// Import local deps
import common from './routes/common';
import contact from './routes/contact';

// Import ajaxForm
import './ajax-form';

// Boostrap
import 'bootstrap/dist/js/bootstrap.js'; // All of Bootstrap JS

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
// You add additional pages to this array by referencing the the body class
// and creating the js file in the routes directory. Remember to import the
// file as per the common example near the top of this file.
const routes = {
  // All pages
  common,
  // Contact page
  contact,
};

// Load Events
document.addEventListener('DOMContentLoaded', () => new Router(routes).loadEvents());

// Window Loaded
window.onload = () => new Router(routes).loadEvents('loaded');
