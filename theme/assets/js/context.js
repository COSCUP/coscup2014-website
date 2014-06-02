define(['jquery'], function($) {

  'use strict';

  // implicit use sponsor display (which is decided by CSS media query)
  // to detect device, to avoid use matchmedia query in JavaScript
  // FIXME: CSS dependent test
  var isMobile = ($('#mySwipe').css('display') ==='none')? false : true;
  if (navigator.userAgent.match(/(Android|iPhone|iPod|iPad|IEMobile|Mobile)/)) {
    isMobile = true;
  }

  var result = window.location.href.match(/2014\/([-\w]+)\//);
  var lang = ((result && result[1]) || 'zh-tw').toLowerCase();

  return {
    origin: 'http://coscup.org',
    api_path: '/2014/api',
    lang: lang,
    mobile: isMobile
  }
});
