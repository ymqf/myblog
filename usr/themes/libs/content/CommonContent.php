<?php

/**
 * 提供给外部，不引用Content 文件夹中外部的函数
 * 其它的Content 子类不能互相调用，避免循环引用的问题，只提供给Content 引用
 */
class CommonContent{

    /**
     * 处理具体的头图显示逻辑：当有头图时候，显示随机图片还是第一个附件还是一张图片还是thumb字段
     * @param $widget $this变量
     * @param int $index
     * @param $howToThumb 显示缩略图的方式，0，1，2，3
     * @param $thumbField thumb字段
     * @return string
     */
    public static function whenSwitchHeaderImgSrc($widget, $index, $howToThumb, $thumbField,$extra_content= "")
    {

        if ($howToThumb == '4'){//该种方式解析速度最快
            if (!empty($thumbField)) {
                return $thumbField;
            } else {
                return "";
            }
        }
        $randomNum = unserialize(INDEX_IMAGE_ARRAY);

        // 随机缩略图路径
        $random = STATIC_PATH . 'img/sj/' . @$randomNum[$index];//如果有文章置顶，这里可能会导致index not undefined
        $pattern = '/\<img.*?src\=\"(.*?)\"[^>]*>/i';
        $patternMD = '/\!\[.*?\]\((http(s)?:\/\/.*?(jpg|png|JPEG|webp|jpeg|bmp|gif))/i';
        $patternMDfoot = '/\[.*?\]:\s*(http(s)?:\/\/.*?(jpg|png|JPEG|webp|jpeg|bmp|gif))/i';

        if ($howToThumb == '0') {
            return $random;
        } elseif ($howToThumb == '1' || $howToThumb == '2') {

            if (!empty($thumbField)) {
                return $thumbField;
            }

            //解析附件
            if ($widget!=null){
                $attach = @$widget->attachments(1)->attachment;
                if ($attach != null && isset($attach->isImage) && $attach->isImage == 1) {
                    return $attach->url;
                }
            }

            if ($widget != null){
                //解析文章内容，这个是最慢的
                $content = $widget->content;
            }else{
                $content = $extra_content;
            }


            if (preg_match_all($pattern, $content, $thumbUrl)) {
                $thumb = $thumbUrl[1][0];
            } elseif (preg_match_all($patternMD, $content, $thumbUrl)) {
                $thumb = $thumbUrl[1][0];
            } elseif (preg_match_all($patternMDfoot, $content, $thumbUrl)) {
                $thumb = $thumbUrl[1][0];
            } else {//文章中没有图片
                if ($howToThumb == '1') {
                    return '';
                } else {
                    return $random;
                }
            }
            return $thumb;

        } elseif ($howToThumb == '3') {
            if (!empty($thumbField)) {
                return $thumbField;
            } else {
                return $random;
            }
        }else{
            return "";
        }
    }

    /**
     * 过滤掉短代码，根据是否登陆来区分
     * @param $content
     * @param $isExceptLock boolean 是否排除加密的短代码，比如lock、login、hide
     * @return array|string|string[]|null
     */
    public static function returnExceptShortCodeContent($content,$isExceptLock=true)
    {

        $exceptArray = array();
        //排除QR
        //排除倒计时
        if (strpos($content, '[QR') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('QR'));
            $exceptArray[] = "/$pattern/";
        }
        //排除图集
        if (strpos($content, '[album') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('album'));
            $exceptArray[] = "/$pattern/";
        }

        //排除图集
        if (strpos($content, '[goal') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('goal'));
            $exceptArray[] = "/$pattern/";
        }

        if (strpos($content, '[font') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('font'));
            $exceptArray[] = "/$pattern/";
        }


        //排除图集
        if (strpos($content, '[link') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('link'));
            $exceptArray[] = "/$pattern/";
        }


        //排除图集
        if (strpos($content, '[timeline') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('timeline'));
            $exceptArray[] = "/$pattern/";
        }


        //排除倒计时
        if (strpos($content, '[countdown') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('countdown'));
            $exceptArray[] = "/$pattern/";
        }
        //排除摘要的collapse 公式
        if (strpos($content, '[collapse') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('collapse'));
            $exceptArray[] = "/$pattern/";
        }

        if (strpos($content, '[tag') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('tag'));
            $exceptArray[] = "/$pattern/";
        }

        if (strpos($content, '[tabs') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('tabs'));
            $exceptArray[] = "/$pattern/";
        }

        //排除摘要中的块级公式
        $exceptArray[]='/\$\$[\s\S]*\$\$/sm';
        //排除摘要的vplayer
        if (strpos($content, '[vplayer') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('vplayer'));
            $exceptArray[] = "/$pattern/";
        }
        //排除摘要中的短代码
        if (strpos($content, '[hplayer') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('hplayer'));
            $exceptArray[] = "/$pattern/";
        }
        if (strpos($content, '[post') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('post'));
            $exceptArray[] = "/$pattern/";
        }
        if (strpos($content, '[scode') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('scode'));
            $exceptArray[] = "/$pattern/";
        }
        if (strpos($content, '[button') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('button'));
            $exceptArray[] = "/$pattern/";
        }


        //排除文档助手
        if (strpos($content, '>') !== false) {
            $exceptArray[] = "/(@|√|!|x|i)&gt;/";
        }


        if ($isExceptLock){
            //排除回复可见的短代码
            if (strpos($content, '[hide') !== false) {
                $pattern = CommonContent::get_shortcode_regex(array('hide'));
                $exceptArray[] = "/$pattern/";
            }

            //排除login
            if (strpos($content, '[login') !== false) {
                $pattern = CommonContent::get_shortcode_regex(array('login'));
                $exceptArray[] = "/$pattern/";
            }
        }



        return preg_replace($exceptArray, '', $content);
    }

    /**
     * 输出文章摘要
     * @param $content
     * @param number $limit 字数限制
     * @param string $emptyText
     * @return string
     */
    public static function excerpt($content, $limit, $emptyText = null)
    {

        if ($emptyText === null){
            $emptyText = _mt("暂时无可提供的摘要");
        }
        if ($limit == 0) {
            return "";
        } else {
            $content = self::returnExceptShortCodeContent($content);
            if (trim($content) == "") {
                return _mt($emptyText);
            } else {
                return Typecho_Common::subStr(strip_tags($content), 0, $limit, "...");
            }
        }
    }

    public static function get_markdown_regex($tagName = '?')
    {
        return '\\' . $tagName . '&gt; (.*)(\n\n)?';

    }

    /**
     * 获取匹配短代码的正则表达式
     * @param null $tagnames
     * @return string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L254
     */
    public static function get_shortcode_regex($tagnames = null)
    {
        global $shortcode_tags;
        if (empty($tagnames)) {
            $tagnames = array_keys($shortcode_tags);
        }
        $tagregexp = join('|', array_map('preg_quote', $tagnames));
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return
            '\\['                                // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        // phpcs:enable
    }

    /**
     * 获取短代码属性数组
     * @param $text
     * @return array|string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L508
     */
    public static function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = self::get_shortcode_atts_regex();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8]) && strlen($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                } elseif (isset($m[9])) {
                    $atts[] = stripcslashes($m[9]);
                }
            }
            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }

        if (!is_array($atts)){
            $atts = [];
        }
        return $atts;
    }

    /**
     * Retrieve the shortcode attributes regex.
     *
     * @return string The shortcode attribute regular expression
     * @since 4.4.0
     *
     */
    public static function get_shortcode_atts_regex()
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }

}
