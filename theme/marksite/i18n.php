<?php
class i18n
{
  var $lang = 'zh-TW';
  var $l10n_sets = array
  (
  'en' => array(
     '首頁' => 'Home',
     '台灣台北' => 'Taipei, Taiwan',
     '加到 Google 日曆' => 'Add to Google Calendar',
     'Facebook 粉絲團' => 'Facebook Page',
     '訂閱電子報' => 'Subscribe',
     'Twitter' => 'Twitter',
     '噗浪' => 'Plurk',
	 'Flickr' => 'Flickr',
	 'YouTube' => 'YouTube',
     '部落格 RSS Feed' => 'Blog RSS Feed',
     '2012 年 8 月 18 - 19 日' => 'August 18th – 19th, 2012',
     'HOME_URI_APPENDS' => 'en/',
     'COPYRIGHT' => '&copy; 2013 COSCUP | <a href="http://coscup.org/2013/en/contact/">Contact us</a>'
     ),
  'zh-CN' => array(
     '首頁' => '主页',
     '開源人年會' => '开源人年会',
     '台灣台北' => '台湾台北',
     '加到 Google 日曆' => '加到 Google 日历',
     'Facebook 粉絲團' => 'Facebook 粉丝页面',
     '訂閱電子報' => '订阅电子报',
     'Twitter' => 'Twitter',
     '噗浪' => '噗浪',
	 'Flickr' => 'Flickr',
	 'YouTube' => 'YouTube',
     '部落格 RSS Feed' => '博客 RSS 种子',
     'HOME_URI_APPENDS' => 'zh-cn/',
     'COPYRIGHT' => '&copy; 2013 COSCUP | <a href="http://coscup.org/2013/zh-cn/contact/">联系我们</a>'
     ),
  'zh-TW' => array(
     'HOME_URI_APPENDS' => 'zh-tw/',
     'COPYRIGHT' => '&copy; 2013 COSCUP | <a href="http://coscup.org/2013/zh-tw/contact/">聯絡我們</a>'
     )
  );

  function _($s)
  {

    if ( isset($this->l10n_sets[$this->lang]) && isset($this->l10n_sets[$this->lang][$s]) )
    {
      return $this->l10n_sets[$this->lang][$s];
    }
    return $s;
  }
}
