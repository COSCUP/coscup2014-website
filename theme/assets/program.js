$(window).bind('pageload', function(){
  if ($('#navTab').length === 0)
    return;

  $('body').scrollspy();

  var program_data = null;
  var navHeight = $('#navTab').get(0).offsetTop;
  /* Program fixed Nav */
  $(window).scroll(function () {
    if ($(this).scrollTop() > navHeight) {
      $('#navTab').addClass("floatTab");
    } else {
      $('#navTab').removeClass("floatTab");
    }
  });

  function getDetailData(callback) {
    $.getJSON("/2014/api/program/").done(function (data) {
        program_data = data;
        callback(data);
    });
  }

  // implicit use sponsor display (which is decided by CSS media query)
  // to detect device, to avoid use matchmedia query in JavaScript
  var isMobile = ($('#mySwipe').css('display') === "block" )? true : false;
  if (isMobile) {
    initMobileUI();
  } else {
    initDesktopUI();
  }
  
  function initMobileUI() {

    getDetailData(initDetailView);
    
    function initDetailView(data) {
      var TAP_LIMIT = 500;
      var moved = false,
          startTime = 0;

      $('.program').each(function() {
        // ignore pop up which is also with class "program" attribute
        var eleId = $(this).attr('id');
        if ( eleId && eleId === 'program_detail' )
          return;

        var id = $(this).data('id');
        var program = data.program[id];
        var $detail = $('<div></div>').addClass('detail')
          .append($('<div></div>').addClass('content-title').html('Abstract'))
          .append($('<div></div>').addClass('abstract').html(program.abstract))
          .append($('<div></div>').addClass('content-title').html('Biography'))
          .append($('<div></div>').addClass('bio').html(program.bio));

        $(this).append($detail.hide());

        $(this).on('touchstart', function() {
          moved = false;
          startTime = +new Date()
        });
        $(this).on('touchmove', function() {
          moved = true;
        });
        $(this).on('touchend', function() {
          if (!moved && +new Date() - startTime < TAP_LIMIT) {
            $detail.toggle();
            $('body').scrollspy('refresh');
          }
        });
      });
    }
  }

  function initDesktopUI() {
    var disableWheel = function() {
          $('body').css('overflow', 'hidden')
        },
        enableWheel = function() {
          $('body').css('overflow', 'auto')
        };

    $('#lock_background').on('click', function(evt) {
      // the user is not clicking on the background.
      if (this.id !== evt.target.id)
        return;
      $(this).removeClass('show');
      enableWheel();
    });

    $('#content').on('click', '.article .program', function() {
      var id = $(this).data('id');
      function displayDetails(data) {
        var program = data.program[id];

        $('#program_detail').empty();
        $('#program_detail')
          .append($('<div class="metadata"></div>').addClass('track_tag colorTag-' + program.type)
            .append($('<div></div>').addClass('head')
              .append($('<div></div>').addClass('place').html(data.room[program.room]['zh-tw']))
              .append($('<div></div>').addClass('timeinfo').html(getTime(program.from) + ' - ' + getTime(program.to)))
              .append($('<div></div>').addClass('community').html(data.community[program.community])))
            .append($('<div></div>').addClass('body')
              .append($('<div></div>').addClass('topic').addClass(program.lang).html(program.name))
              .append($('<div></div>').addClass('speaker').html(program.speaker))
              .append($('<div></div>').addClass('speaker-title').html(program.speakerTitle))));

        $('#program_detail')
          .append($('<div></div>').addClass('detail')
            .append($('<div></div>').addClass('content-title').html('Abstract'))
            .append($('<div></div>').addClass('abstract').html(program.abstract))
            .append($('<div></div>').addClass('content-title').html('Biography'))
            .append($('<div></div>').addClass('bio').html(program.bio)));
        $('#lock_background').addClass('show');
        disableWheel();
      }

      if (!program_data) {
        getDetailData(displayDetails);
      } else {
        displayDetails(program_data);
      }
    });
  }

  function getTime(ts) {
    var date = new Date(ts*1000);
    var hour = date.getHours();
    hour = (hour < 10)? '0' + hour : hour.toString();
    var min = date.getMinutes();
    min = (min < 10)? '0' + min : min.toString();
    return hour + ':' + min;
  }

});

