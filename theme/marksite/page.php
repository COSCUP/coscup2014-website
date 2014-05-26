<?php
$rootPath = MARKSITE_ABSOLUTE_PATH;
$theme_assets_uri = MARKSITE_ABSOLUTE_PATH."assets/";
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title><?php echo $title; ?> | 2014 COSCUP</title>

<meta name="keywords" content="COSCUP, COSCUP2014, 開源人年會, Conference for Open Source Coders, Users and Promoters Open Source, Free Software, 自由軟體, 開放原始碼, 研討會, 社群, FLOSS">
<meta name="description" content="COSCUP 2014, 7/19-20 中央研究院。台灣 Opens Source 相關社群聯合舉辦的大型開放源碼研討會。讓世界各地的 FLOSS 愛好者、專家藉由開源人年會齊聚一堂，分享經驗、想法與新技術，共同激發群眾投入貢獻開源 / 自由軟體。">
<meta name="COMPANY" content="COSCUP">

<!--locales-->
<!--[if gt IE 8]><!-->
  <link rel="localization" href="<?php echo $theme_assets_uri;?>locales/manifest.json">
  <script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/l20n.min.js"></script>
<!--<![endif]-->

<!--fb shareing-->
<meta property="og:title" content="2014 COSCUP" />
<meta name="og:description" content="COSCUP 2014, 7/19-20 中央研究院。台灣 Opens Source 相關社群聯合舉辦的大型開放源碼研討會。讓世界各地的 FLOSS 愛好者、專家藉由開源人年會齊聚一堂，分享經驗、想法與新技術，共同激發群眾投入貢獻開源 / 自由軟體。">
<meta property="og:type" content="website" />
<meta property="og:url" content="http://coscup.org/2014/" />
<meta property="og:site_name" content="2014 COSCUP" />
<meta property="og:image" content="http://coscup.org/2014/assets/og-image.jpg" />

<!--phone-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<link rel="apple-touch-icon" href="<?php echo $theme_assets_uri;?>ios-fav.jpg" />
<link href="<?php echo $theme_assets_uri;?>normalize.css" type= "text/css" rel="stylesheet" />
<link media="only screen and (max-width:768px)" href="<?php echo $theme_assets_uri;?>mobile.css" type= "text/css" rel="stylesheet" />
<link media="screen and (min-width:769px)" href="<?php echo $theme_assets_uri;?>style.css" type="text/css" rel="stylesheet" />
<?php
if (isset($styles)) {
  foreach ( $styles as $file ) {
?>
<link href="<?php echo $file;?>" type="text/css" rel="stylesheet" />
<?php
  }
}
?>
<!--favicon-->
<link type="image/x-icon" href="<?php echo $theme_assets_uri;?>favicon.ico" rel="shortcut icon">

<!-- GA -->
<script>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-12923351-1']);
    _gaq.push(['_setDomainName', 'coscup.org']);
    _gaq.push(['_trackPageview']);

    (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
</script>
</head>
<body>
<div id="header">
    <img id="mobile-header" src="<?php echo $theme_assets_uri;?>mobile/header.jpg" width="100%" />
    <div class="wrap">
      <div id="logo"><a href="<?php echo $home_path.$this->current[0]."/index.html"?>">COSCUP 2014</a></div>
        <ul id="languages" class="no-decoration">
          <li><a href="<?php echo $rootPath;?>en/" title="English" lang="en">EN</a></li>
          <li><a href="<?php echo $rootPath;?>zh-tw/" title="正體中文" lang="zh-TW">正體</a></li>
          <li><a href="<?php echo $rootPath;?>zh-cn/" title="简体中文" lang="zh-CN">简体</a></li>
        </ul>
	    <ul id="desktop-social-links" class="no-decoration">
        <li><a href="https://www.facebook.com/coscup" title="facebook" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_fb.png"/></a></li>
        <li><a href="https://plus.google.com/101434041225212178932" title="Google+" rel="publisher" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_gplus.png"/></a></li>
        <li><a href="http://www.plurk.com/coscup" title="plurk" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_plurk.png" /></a></li>
        <li><a href="https://twitter.com/coscup" title="twitter" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_twitter.png" /></a></li>
        <li><a href="http://blog.coscup.org" title="blog" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_blog.png" /></a></li>
        <li><a href="http://www.flickr.com/photos/coscup/sets/" title="flickr" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_flickr.png"  /></a></li>
        <li><a href="http://www.youtube.com/user/thecoscup?feature=watch" title="youtube" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_utube.png" /></a></li>
	    </ul>
      <nav id="nav-wrap">
        <div id="mobile-menu-icon" data-l10n-id="menu">選單</div>
        <ul id="mainNav" class="no-decoration">
          <?php echo $this->menu(1); ?>
        </ul>
      </nav> 
    </div>
</div>
<!--Main-->
<div id="main">
<div class="wrap">
  <!--Sponsor-->
  <div id="sponsor" class="empty">
    <h2 data-l10n-id="sponsorship">贊助 COSCUP</h2>
    <p data-l10n-id="mailtoSponsor"><a href="mailto:sponsorship@coscup.org">sponsorship@coscup.org</a></p>
  </div>
  <div id="mySwipe" style='max-width:500px;margin:0 auto' class="swipe empty">
  </div>
  <div id="content">
    <?php echo $transformed; ?>
  </div>
</div>
</div>
<!--social Mobile-->
<ul id="mobile-social-links" class="no-decoration">
  <li class="title">Follow Us!!<hr></li>
  <li><a href="https://www.facebook.com/coscup" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_fb.png" align="absmiddle" /><span>facebook</span></a></li>
  <li><a href="https://plus.google.com/101434041225212178932" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_gplus.png" align="absmiddle" /><span>Google+</span></a></li>
  <li><a href="http://www.plurk.com/coscup" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_plurk.png" align="absmiddle" /><span>Plurk</span></a></li>
  <li><a href="https://twitter.com/coscup" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_twitter.png" align="absmiddle" /><span>twitter</span></a></li>
  <li><a href="http://blog.coscup.org" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_blog.png"  align="absmiddle" /><span>Blog</span></a></li>
  <li><a href="http://www.flickr.com/photos/coscup/sets/" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_flickr.png"  align="absmiddle" /><span>flickr</span></a></li>
  <li><a href="http://www.youtube.com/user/thecoscup?feature=watch" target="_blank"><img src="<?php echo $theme_assets_uri;?>icon_utube.png"  align="absmiddle" /><span>Youtube</span></a></li>
</ul><!--social Mobile end-->
<!--底-->
<div id="footer">
  <ul class="no-decoration">
    <li><span data-l10n-id="copyright"><a href="http://coscup.org/2014/en/contact/">Contact us</a></span> | </li>
    <li><a href="http://coscup.org/2006/" target="_blank">2006</a>|</li>
    <li><a href="http://coscup.org/2007/" target="_blank">2007</a>|</li>
    <li><a href="http://coscup.org/2008/" target="_blank">2008</a>|</li>
    <li><a href="http://coscup.org/2009/" target="_blank">2009</a>|</li>
    <li><a href="http://coscup.org/2010/" target="_blank">2010</a>|</li>
    <li><a href="http://coscup.org/2011/" target="_blank">2011</a>|</li>
    <li><a href="http://coscup.org/2012/" target="_blank">2012</a>|</li>
    <li><a href="http://coscup.org/2013/" target="_blank">2013</a>|</li>
  </ul>
</div>
</body>
<script>
  // FIXME: global ojects!
  var lang = (navigator.language || 'zh-TW').toLowerCase();
  var rootURL = window.location.origin + '<?php echo $rootPath;?>';
  var themeURL = '<?php echo $theme_assets_uri; ?>';

  // implicit use sponsor display (which is decided by CSS media query)
  // to detect device, to avoid use matchmedia query in JavaScript
  // FIXME: CSS dependent test
  var isMobile = (document.getElementById('mySwipe').clientWidth !== 0)? true : false;
  if (navigator.userAgent.match(/(Android|iPhone|iPod|iPad|IEMobile|Mobile)/)) {
    isMobile = true;
  }
</script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/respond.min.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/swipe.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/widget.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/sponsor.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/script.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/program.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/program-filter.js"></script>
<script type="text/javascript" src="<?php echo $theme_assets_uri;?>js/bootstrap-scrollspy.js"></script>
</html>
