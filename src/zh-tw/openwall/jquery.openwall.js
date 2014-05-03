'use strict';

(function($) {
  // http://jsfromhell.com/array/shuffle
  Array.prototype.shuffle = function() { //v1.0
    for (var j, x, i = this.length; i;
      j = parseInt(Math.random() * i),
      x = this[--i], this[i] = this[j], this[j] = x);
    return this;
  };

  $.fn.openWall = function(options) {
    var picNameList = [];
    var settings = {
      imageDir: 'photo/',
      totalPicNumber: 34,
      picBase: 50,
      defaultImage: 'images/default.jpg',
      mobile: false,
      letterNumber: 4,
      picMask: {
        o: [
          [0, 0, 0, 0],
          [0, 1, 1, 0],
          [1, 0, 0, 1],
          [1, 0, 0, 1],
          [1, 0, 0, 1],
          [0, 1, 1, 0]
        ],
        p: [
          [0, 0, 0, 0],
          [1, 1, 1, 1],
          [1, 0, 0, 1],
          [1, 1, 1, 1],
          [1, 0, 0, 0],
          [1, 0, 0, 0]
        ],
        e: [
          [0, 0, 0, 0],
          [1, 1, 1, 1],
          [1, 0, 0, 0],
          [1, 1, 1, 1],
          [1, 0, 0, 0],
          [1, 1, 1, 1]
        ],
        n: [
          [0, 0, 0, 0],
          [1, 0, 0, 1],
          [1, 1, 0, 1],
          [1, 0, 1, 1],
          [1, 0, 0, 1],
          [1, 0, 0, 1]
        ]
      },
      borderMask: {
        upper: [
          [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          [0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0]
        ],
        lower: [
          [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          [1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1],
          [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        ]
      },
      gapMask: [[0], [0], [0], [0], [0], [0]],
      siderMask: [[0], [0], [0], [0], [0], [0], [0], [0], [0], [0], [0], [0]]
    };
    if (options) {
      $.extend(settings, options);
    }

    function getPicList() {
      for (var i = 0; i < settings.totalPicNumber; ++i) {
        picNameList[i] = i;
      }
      return picNameList;
    }

    function layoutPic(target) {
      var useDefault = Math.ceil(settings.totalPicNumber / settings.picBase);

      var letterIndex = 0;

      for (var letter in settings.picMask) {
        var defaultImg = (useDefault > letterIndex) ?
          null : settings.defaultImage;
        var letterMask = settings.picMask[letter];
        var div = document.createElement('div');
        $(div).addClass('letterBlock');
        var offset = letterIndex * letterMask.length * letterMask[0].length;
        fillPic(div, letterMask, offset, defaultImg);
        $(target).append(div);
        letterIndex++;
        // add a gap between each letter
        if (letterIndex < settings.letterNumber) {
          var gap = document.createElement('div');
          $(gap).addClass('gap');
          var offset = Math.ceil(Math.random() * 100);
          fillPic(gap, settings.gapMask, offset, null);
          $(target).append(gap);
        }
      }
    }

    function layoutBorder(target) {
      var upperBorder = document.createElement('div');
      $(upperBorder).addClass('border');
      var lowerBorder = document.createElement('div');
      $(lowerBorder).addClass('border');
      fillPic(upperBorder, settings.borderMask.upper, 0, null);
      $(target).prepend(upperBorder);
      fillPic(lowerBorder, settings.borderMask.lower, 10, null);
      $(target).append(lowerBorder);
    }

    function layoutSider(target, number) {
      for (var i = 0; i < number; ++i) {
        var sider = document.createElement('div');
        $(sider).addClass('sider');
        var offset = Math.ceil(Math.random() * 100);
        fillPic(sider, settings.siderMask, offset, null);
        if (i < (number / 2))
          $(target).prepend(sider);
        else
          $(target).append(sider);
      }
    }

    function fillPic(div, mask, offset, defaultImg) {
      var rows = mask.length;
      var cols = mask[0].length;
      for (var i = 0; i < rows; ++i) {
        for (var j = 0; j < cols; ++j) {
          var img = document.createElement('img');
          var pos = Math.floor((i * cols + j + offset) % settings.totalPicNumber);
          img.src = settings.imageDir + picNameList[pos] + '.jpg';
          $(img).addClass('thumbnail');
          if (mask[i][j]) {
            if (defaultImg) {
              img.src = defaultImg;
              $(img).addClass('default');
            } else {
              $(img).addClass('highlight');
            }
          }
          $(div).append(img);
        }
      }
    }
    return this.each(function() {
      getPicList().shuffle();
      var main = document.createElement('div');
      $(main).addClass('main');
      layoutPic(main);
      $(this).append(main);

      if (!settings.mobile) {
        layoutBorder(main);
        layoutSider(this, 6);
      }
      else {
        var siderGroup1 = document.createElement('div');
        $(siderGroup1).addClass('siderGroup');
        layoutSider(siderGroup1, 2);
        $(this).prepend(siderGroup1);
        var siderGroup2 = document.createElement('div');
        $(siderGroup2).addClass('siderGroup');
        layoutSider(siderGroup2, 2);
        $(this).append(siderGroup2);
      }
    });
  };
})(jQuery);
