<?php

/*
 * 短代码编写
 */
require_once ("ScodeParse.php");
class ScodeContent
{
    public static function parseColumnCallback($matches)
    {

        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $content = $matches[5];

        $pattern = CommonContent::get_shortcode_regex(array('block'));
        preg_match_all("/$pattern/", $content, $block_matches);

        $html = '<div class="flex-column">';

        //$block_matches[3] 是一个数组，每个成员是block的属性字符串
        //$block_matches[5] 是一个数组，每个成员是block中间的内容

        for ($i = 0; $i < count($block_matches[3]); $i++) {
            $item = $block_matches[3][$i];
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数

            //根据属性值来获取当前栏目的flex比例
            $flex = (@$attrs["size"]) ? $attrs["size"] : "auto";

            $content = $block_matches[5][$i];
            if (substr($content, 0, 4) == "<br>") {
                $content = substr($content, 4);
            }
            $html .= '<div class="flex-block" style="flex:' . $flex . '">' . htmlspecialchars_decode($content) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    public static function parseExternalSingleVideo($info, $api, $needActive, $id)
    {

        $url = $api . $info["url"];
        $title = $info["title"];
        $active = $needActive ? "active" : "";
        $ret = <<<EOF
  <label class="btn btn-info $active" data-src="$url" data-iframe="$id" data-origin="{$info["url"]}">
    <input type="radio" name="options" id="option1"> $title
  </label>
EOF;
        return $ret;


    }

    /**
     * 视频解析的回调函数
     * @param $matches
     * @return bool|string
     */
    public static function videoParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        // 判断是单个视频还是视频合集
        if ($matches[5] != "") {
            //视频合集，现在默认是外部解析接口
            // todo 暂不支持本地视频地址的合集
            //获取内部内容
            $pattern = CommonContent::get_shortcode_regex(array('Video'));
            preg_match_all("/$pattern/", $matches[5], $all);
            $playerCode = "";
            if (sizeof($all[3])) {
                //当内部有内容时候，可能是一首歌曲或者多首歌曲
                $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
                $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
                $isCollapse = @$attrs["status"] != "true";// 默认折叠
                $collapse = $isCollapse ? "collapse" : "collapse in";


                $index = 0;
                $id = uniqid();

                foreach ($all[3] as $vo) {
                    $info = CommonContent::shortcode_parse_atts(htmlspecialchars_decode($vo));
                    $api = Utils::getExpertValue("video_external_api", "https://okjx.cc/?url=");

                    $title = _mt("正在播放：");
                    $list = _mt("剧集列表");

                    if ($index == 0) {
                        $first_url = $isCollapse ? $api . $info["url"] : "";
                        $iframe_url = $isCollapse ? "" : $api . $info["url"];
                        $playerCode .= <<<EOF
<div class="video-player panel panel-default box-shadow-wrap-lg collapse-panel">
    <div class="panel-heading panel-collapse" data-toggle="collapse" data-iframe="$id" data-src="$first_url" data-target="#video-$id" aria-expanded="false"><i data-feather="airplay"></i><span>{$title}</span><a id="origin_$id" target="_blank" href="{$info["url"]}"><b id="title_$id" class="video-name">{$info["title"]}</b></a></div>
     <div class="panel-body $collapse" id="video-$id">
                <div class="iframe_player embed-responsive embed-responsive-16by9">
                    <iframe id="{$id}" allowfullscreen="true" src="{$iframe_url}"></iframe>
                </div>
            
[collapse status="true" title="<i data-feather='list'></i> $list"]

<div class="btn-group" data-toggle="buttons">
EOF;
                    }
                    $playerCode .= self::parseExternalSingleVideo($info, $api, $index == 0, $id);
                    $index++;
                }

                $playerCode .= <<<EOF
</div>  <!--end of btn-group-->
[/collapse]
</div> <!--end of panel-body-->
</div> <!--end of video-player-->
EOF;


            }
            return $playerCode;
        } else {
            //单个视频
            //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
            $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/", '$1', $matches[3]);
            $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
            if ($attrs['url'] !== null || $attrs['url'] !== "") {
                $url = $attrs['url'];
            } else {
                return "";
            }

            if (array_key_exists('pic', $attrs) && ($attrs['pic'] !== null || $attrs['pic'] !== "")) {
                $pic = $attrs['pic'];
            } else {
                $pic = STATIC_PATH . 'img/video.jpg';
            }
            $playCode = '<video src="' . $url . '" style="background-image:url(' .
                $pic . ');background-size: cover;"></video>';

            //把背景图片作为第一帧
//        $playCode = '<video src="' . $url . '" poster="'.$pic.'"></video>';
            return $playCode;
        }


    }

    /**
     * 一些公用的解析，文章、评论、时光机公用的，与用户状态无关
     * @param $content
     * @return null|string|string[]
     */
    public static function parseContentPublic($content)
    {
        $options = mget();


        //链接转二维码
        if (strpos($content, '[QR') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('QR'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'QRParseCallback'),
                $content);
        }

        //倒计时
        if (strpos($content, '[countdown') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('countdown'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'countdownParseCallback'),
                $content);
        }


        //文章中标签页的功能
        if (strpos($content, '[tabs') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('tabs'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'tabsParseCallback'),
                $content);
        }

        //文章中标签功能
        if (strpos($content, '[tag') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('tag'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'tagParseCallback'),
                $content);
        }

        //文章中播放器功能
        if (strpos($content, '[hplayer') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('hplayer'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'musicParseCallback'), $content);
        }

        //文章中视频播放器功能
        if (strpos($content, '[vplayer') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('vplayer'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('SCodeContent', 'videoParseCallback'), $content);
        }

        //文章中折叠框功能
        if (strpos($content, '[collapse') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('collapse'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'collapseParseCallback'),
                $content);
        }


        //解析文章中的表情短代码
        $content = Utils::handle_preg_replace_callback('/::([^:\s]*?):([^:\s]*?)::/sm', array('ScodeParse', 'emojiParseCallback'),
            $content);

        //调用其他文章页面的摘要
        if (strpos($content, '[post') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('post'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'quoteOtherPostCallback'), $content);
        }

        //解析短代码功能
        if (strpos($content, '[scode') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('scode'));
            $content = preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeParseCallback'),
                $content);
        }

        //解析文章内图集
        if (strpos($content, '[album') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('album'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeAlbumParseCallback'),
                $content);
        }

        //解析文章内的进度条
        if (strpos($content, '[goal') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('goal'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeGoalParseCallback'),
                $content);
        }

        //解析文章内的font
        if (strpos($content, '[font') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('font'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeFontParseCallback'),
                $content);
        }


        if (strpos($content, '[link') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('link'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeLinkParseCallback'),
                $content);
        }

        //进行时间树
        if (strpos($content, '[timeline') !== false) {
            $pattern = CommonContent::get_shortcode_regex(array('timeline'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'scodeTimelineParseCallback'),
                $content);
        }

        //解析link


        //解析markdown扩展语法
        if ($options->markdownExtend != "" && in_array('scode', Utils::checkArray( $options->markdownExtend))) {
            $content = Utils::handle_preg_replace_callback("/(@|√|!|x|i)&gt;\s(((?!<\/p>).)*)(<br \/>|<\/p>)/is", array('ScodeParse', 'sCodeMarkdownParseCallback'), $content);
        }

        //解析拼音注解写法
        if ($options->markdownExtend != "" && in_array('pinyin', Utils::checkArray( $options->markdownExtend))) {
            $content = Utils::handle_preg_replace('/\{\{\s*([^\:]+?)\s*\:\s*([^}]+?)\s*\}\}/is',
                "<ruby>$1<rp> (</rp><rt>$2</rt><rp>) </rp></ruby>", $content);
        }


        //解析显示按钮短代码
        if (strpos($content, '[button') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('button'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('ScodeParse', 'parseButtonCallback'), $content);
        }


        //解析分栏显示
        if (strpos($content, '[column') !== false) {//提高效率，避免每篇文章都要解析
            $pattern = CommonContent::get_shortcode_regex(array('column'));
            $content = Utils::handle_preg_replace_callback("/$pattern/", array('SCodeContent', 'parseColumnCallback'), $content);
        }


        //文章中的链接，以新窗口方式打开
        $content = preg_replace_callback("/<a href=\"([^\"]*)\">(.*?)<\/a>/", function ($matches) {
            if (strpos($matches[1], substr(@BLOG_URL, 0, -1)) !== false || strpos(substr($matches[1], 0, 6), "http") === false) {
                return '<a href="' . $matches[1] . '">' . $matches[2] . '</a>';
            } else {
                if (Utils::getExpertValue("no_link_ico", false)) {//true 则不加图标
                    return '<a href="' . $matches[1] . '" target="_blank">' . $matches[2] . '</a>';
                } else {
                    return '<span class="external-link"><a class="no-external-link" href="' . $matches[1] . '" target="_blank"><i data-feather="external-link"></i>' .
                        $matches[2] .
                        "</a></span>";
                }

            }
        }, $content);


        return $content;
    }

}
