requirejs.config({
  paths: {
    "jquery": "//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min"
  },
  shim: {
    'lib/bootstrap-scrollspy': {
      deps: ['jquery'],
      exports: "$.fn.scrollspy"
    },
    'lib/swipe': {
      deps: ['jquery'],
      exports: 'Swipe'
    }
  }
});

