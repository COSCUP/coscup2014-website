<?php
/**
 The MIT License

 Copyright (c) 2011 <Tsung-Hao>

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 *
 * @author: Tsung <tsunghao@gmail.com>
 */

/**
 * API Document: http://code.google.com/intl/zh-TW/apis/language/translate/v2/using_rest.html
 * Key: https://code.google.com/apis/console/?api=translate&pli=1
 */

# define('TRANSLATE_KEY', 'PUT_YOUR_GOOGLE_API_KEY');
define('TRANSLATE_POST_URL', 'https://www.googleapis.com/language/translate/v2');

function post_url_data($url, $postvar)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvar);

    /* Google translate post api need send this header */
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function translate_post($q, $from = 'zh-TW', $to = 'zh-CN')
{
    if (empty($q))
        return $q;

    $arg = array(
        'q'      => $q,
        'key'    => TRANSLATE_KEY,
        'source' => $from,
        'target' => $to,
    );

    foreach ($arg as $k => $v)
        $postvar[] = $k . '=' . urlencode($v);

    $postvar_string = implode("&", $postvar);

    $obj = json_decode(post_url_data(TRANSLATE_POST_URL, $postvar_string));

    if (isset($obj->error))
        return $obj->error->message;

    return $obj->data->translations[0]->translatedText;
}
?>
