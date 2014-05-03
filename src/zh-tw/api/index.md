# API 文件

COSCUP 2014 網站提供下列**唯讀** JSON-P API 供第三方程式取用。因為具有 JSON-P 的能力，您可以用在 client 端網路應用程式；在引用時加上 `callback` 參數即可。若目標瀏覽器支援跨來源資源共享 (Cross-Origin Resource Sharing)，您也可以不使用 `callback` 參數；各 URL 皆會輸出 `Access-Control-Allow-Origin: *` HTTP 標頭。

## 導覽選單 (`menu`)

* URL: `http://coscup.org/2014/api/menu/`。[縮排顯示](http://json-indent.appspot.com/indent?url=http://coscup.org/2014/api/menu/)。

提供三種語言的網站導覽選單 HTML，設計給 `coscup.org` 的子網站使用。超連結（`href`）沒有 hostname，只有絕對路徑。取用這些頁面的網站通常是 `coscup.org`，除非您想要測試 IPv6 連線（改用 `ipv6.coscup.org`）。

## 議程 (`program`)

* URL: `http://coscup.org/2014/api/program/`。[縮排顯示](http://json-indent.appspot.com/indent?url=http://coscup.org/2014/api/program/)。

大約 ~150KB 大的 JSON 文件，列出 COSCUP 2014 的所有議程。可能的話，應用程式**應**留存此資料的快取（網路應用程式可以使用`localStorage`；[參考資料](http://diveintohtml5.org/storage.html)）。議程*可能*是照時間順序排列，但是不一定；程式**應**具有處理不照時間排列的資料的能力。另外，議程更新的時候講次在列表上的順序可能會改變；程式**不應**使用順序來辨識講次。

主題列表以及會場會議廳的資訊也在此提供。

時間的記錄方式為 UNIX Timestamp（1970/1/1 0:00 UT 至當時的秒差）。您可以使用 `isBreak` 屬性來檢查該「議程」是否為休息時間。

**注意**: 不是每一項議程都會有主題類型以及語言等資訊。

## 贊助單位 (`sponsors`)

* URL: `http://coscup.org/2014/api/sponsors/`。[縮排顯示](http://json-indent.appspot.com/indent?url=http://coscup.org/2014/api/sponsors/)。

COSCUP 2014 的贊助商資訊。程式**應**內建各贊助等級的順序，各等級內的贊助單位順序在列出時**應**保存；在只能顯示一個贊助單位的場合，要顯示哪個贊助單位的機制必須使用加權隨機的方式挑選，加權比率如下：

> diamond:gold:silver:bronze:media = 10:5:2:1:0

此演算法的 Javascript 版本在 COSCUP 2014 手機版網頁有實作，您可以參考[此處](http://coscup.org/2014-theme/assets/script.js)位於 `mobileSponsorLogo()` 的程式碼。

程式至少需每日更新贊助單位資訊。
