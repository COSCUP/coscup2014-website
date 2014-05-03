# API 文档

COSCUP 2013 站点提供下列**只读** JSON-P API 供第三方程序取用。因为具有 JSON-P 的能力，您可以用在 client 端网络应用程序；在引用时加上 `callback` 参数即可。若目标浏览器支援跨来源资源分享 (Cross-Origin Resource Sharing)，您也可以不使用 `callback` 参数；各 URL 皆会输出 `Access-Control-Allow-Origin: *` HTTP 标头。

## 导航菜单 (`menu`)

* URL: `http://coscup.org/2013/api/menu/`。[缩进显示](http://json-indent.appspot.com/indent?url=http://coscup.org/2013/api/menu/)。

提供三种语言的站点导航菜单 HTML，设计给 `coscup.org` 的子站点使用。超链接（`href`）没有 hostname，只有绝对路径。取用这些页面的站点通常是 `coscup.org`，除非您想要测试 IPv6 连线（改用 `ipv6.coscup.org`）。

## 议程 (`program`)

* URL: `http://coscup.org/2013/api/program/`。[缩进显示](http://json-indent.appspot.com/indent?url=http://coscup.org/2013/api/program/)。

大约 ~150KB 大的 JSON 文档，列出 COSCUP 2013 的所有议程。可能的话，应用程序**应**留存此数据的缓存（网络应用程序可以使用`localStorage`；[参考数据](http://diveintohtml5.org/storage.html)）。议程*可能*是照时间顺序排列，但是不一定；程序**应**具有处理不照时间排列的数据的能力。另外，议程更新的时候讲次在列表上的顺序可能会改变；程序**不应**使用顺序来辨识讲次。

主题列表以及会场会议厅的信息也在此提供。

时间的记录方式为 UNIX Timestamp（1970/1/1 0:00 UT 至当时的秒差）。您可以使用 `isBreak` 属性来检查该“议程”是否为休息时间。

**注意**: 不是每一项议程都会有主题类型以及语言等信息。

## 赞助单位 (`sponsors`)

* URL: `http://coscup.org/2013/api/sponsors/`。[缩进显示](http://json-indent.appspot.com/indent?url=http://coscup.org/2013/api/sponsors/)。

COSCUP 2013 的赞助商信息。程序**应**内建各赞助等级的顺序，各等级内的赞助单位顺序在列出时**应**保存；在只能显示一个赞助单位的场合，要显示哪个赞助单位的机制必须使用加权随机的方式挑选，加权比率如下：

> diamond:gold:silver:bronze:media = 10:5:2:1:0

此演算法的 Javascript 版本在 COSCUP 2013 手机版网页有实现，您可以参考[此处](http://coscup.org/2013-theme/assets/script.js)位于 `mobileSponsorLogo()` 的源代码。

程序至少需每日更新赞助单位信息。