<?php

class ScodeParse{
    /**
     * 短代码解析正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function scodeParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $type = "info";
//        print_r($attrs);
        switch (@$attrs['type']) {
            case 'yellow':
                $type = "warning";
                break;
            case 'red':
                $type = "error";
                break;
            case 'lblue':
                $type = "info";
                break;
            case 'green':
                $type = "success";
                break;
            case 'share':
                $type = "share";
                break;
        }
        if (@$attrs["simple"]!="" || @$attrs["size"] == "simple"){
            $type .= " simple";
        }else if (@$attrs["small"]!="" || @$attrs["size"] == "small"){
            $type .= " simple small";
        }
        return '<div class="tip inlineBlock ' . $type . '">' . "\n\n" . $matches[5] . "\n" . '</div>';
    }

    /**
     * 文章内相册解析
     * @param $matches
     * @return bool|string
     */
    public static function scodeAlbumParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数

        $content = $matches[5];


        return TypePost::parseContentToImage($content, @$attrs["type"]);


    }

    public static function scodeLinkParseCallback($matches){

        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $content = $matches[5];
        $pattern = CommonContent::get_shortcode_regex(array('item'));
        preg_match_all("/$pattern/", $content, $matches);
        $ret = '<div class="list-group list-group-lg list-group-sp row" style="margin: 0">';

        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
            $name = @$attrs["name"];
            $link = @$attrs["link"];
            $pic = @$attrs["pic"];
            $desc = @$attrs["desc"];

            if (empty($name)){
                $name = _mt("不知名");
            }

            if (empty($link)){
                $link = "http://example.com";
            }

            if (empty($desc)){
                $desc = "一个神秘的人";
            }

            if (empty($pic)){
                $options = Typecho_Widget::widget('Widget_Options');
                $pic = Typecho_Common::url('/usr/plugins/Handsome/assets/image/nopic.jpg',
                    $options->siteUrl);
            }


            $ret .= <<<EOF
<div class="col-sm-6">
<a href="{$link}" target="_blank" class="no-external-link no-underline-link list-group-item no-borders 
box-shadow-wrap-lg"> <span class="pull-left thumb-sm avatar m-r"> <img noGallery 
src="{$pic}" alt="Error" class="img-square"></span> <span class="clear"><span 
class="text-ellipsis">
  {$name}</span> <small class="text-muted clear text-ellipsis">{$desc}</small> </span> </a>
</div>
EOF;
        }

        $ret .="</div>";

        return $ret;
    }

    public static function scodeFontParseCallback($matches){
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $content = $matches[5];
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $ret = "<span style='";

        foreach ($attrs as $key => $value){
            $ret .= $key .":" .$value;
        }
        $ret.="'>".$content ."</span>";
        return $ret;

    }

    public static function scodeGoalParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数

        $title = (@$attrs["title"]) ? $attrs["title"] : _mt("小目标");
        $ret = <<< EOF
<div class="panel panel-default  box-shadow-wrap-lg goal-panel">
    <div class="panel-heading">
        {$title}
    </div>
    <div class="list-group">
   
EOF;
        $content = $matches[5];
        $pattern = CommonContent::get_shortcode_regex(array('item'));
        preg_match_all("/$pattern/", $content, $matches);
        $ret .= '<div class="list-group-item">';

        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $text = $matches[5][$i];
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
//            print_r($attrs);
            if (@$attrs["progress"] || @$attrs["start"]) {//进度条

                $start = @$attrs["start"];
                $end = @$attrs["end"];

                if (!empty($start) && !empty($end)) {
                    $progress = (time() - strtotime($start)) * 1.0 / (strtotime($end) - strtotime($start));
                } else {
                    $progress = (float)@$attrs["progress"] / 100;
                }

                $color = "warning";
                if ($progress > 0 && $progress < 0.3) {
                    $color = "danger";
                } elseif ($progress > 0.6 && $progress < 0.8) {
                    $color = "info";
                } else if ($progress >= 0.8) {
                    $color = "success";
                }

                $progress = round($progress, 2) * 100 . "%";

                //根据进度自动选择颜色
                $ret .= <<<EOF
            <p class="goal_name">{$text}：</p>
            <div class="progress-striped active m-b-sm progress" value="dynamic" type="danger">
                <div class="progress-bar progress-bar-{$color}" role="progressbar" aria-valuenow="97" aria-valuemin="0"
                 aria-valuemax="100" style="width: {$progress};"><span> {$progress} </span></div>
            </div>
EOF;
            } else {//to do类型
                $isCheck = (@$attrs["check"] == "true") ? 'checked=""' : "";
                $ret .= <<< EOF
<div class="checkbox">
                <label class="i-checks">
                    <input type="checkbox" {$isCheck} disabled="" value="">
                    <i></i>
                    {$text}
                </label>
</div>
EOF;
            }

        }
        $ret .= '</div>';

        $ret .= '</div></div>';
        return $ret;
    }


    public static function scodeTimelineParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $title = (@$attrs["title"]) ? $attrs["title"] : _mt("大事记");
        $type = (@$attrs["type"]) ? $attrs["type"] : "small";
        $random = (@$attrs["random"]) ? $attrs["random"] : "true";
        $random = ($random == "true");
        $start = (@$attrs["start"]);
        $end = (@$attrs["end"]);

        $ret = <<<EOF
<div class="panel panel-default  box-shadow-wrap-lg goal-panel">
<div class="panel-heading">
        {$title}
    </div>
<div class="padder-md wrapper">     
EOF;

        if ($type == "small") {
            $ret .= '<div class="streamline b-l m-b">';
        } else {
            $ret .= '<ul class="timeline">';
            if (!empty($start)) {
                $ret .= Utils::getTimelineHeader($start, $type, -1, false);
            }

        }

        $content = $matches[5];
        $pattern = CommonContent::get_shortcode_regex(array('item'));
        preg_match_all("/$pattern/", $content, $matches);

        //颜色的随机选择
        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $text = trim($matches[5][$i]);
            if ($type == "small") {//小样式过滤换行，不然样式会变的很难看
                $text = str_replace(array("/r/n", "/r", "/n"), "", $text);
            }
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
            $date = @$attrs["date"];
            $color_ = @$attrs["color"];
            if ($color_ == "") {
                $color_ = "light";
            }

            if (!empty($date)) {//有日期
                $ret .= Utils::getTimeLineItem($date, $text, $type, $i, $random, $color_);
            } else {//没有日期，只显示文字
                $ret .= Utils::getTimelineHeader($text, $type, $i, $random, $color_);

            }
        }


        if ($type == "small") {
            $ret .= '</div>';
        } else {
            if (!empty($end)) {
                $ret .= Utils::getTimelineHeader($end, $type, -1, false, "light");
            }
            $ret .= '</ul>';
        }
        $ret .= '</div></div>';
        return $ret;
    }

    /**
     * 文档助手markdown正则替换回调函数
     * @param $matches
     * @return string
     */
    public static function sCodeMarkdownParseCallback($matches)
    {
        $type = "info";
        switch ($matches[1]) {
            case '!':
                $type = "warning";
                break;
            case 'x':
                $type = "error";
                break;
            case 'i':
                $type = "info";
                break;
            case '√':
                $type = "success";
                break;
            case '@':
                $type = "share";
                break;
        }
        return '<div class="tip inlineBlock ' . $type . '">' . $matches[2] . '</div>';
        //return $matches[2];
    }

    public static function tagParseCallback($matches)
    {
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $type = @$attrs["type"];
        $tid = @$attrs["id"];
        if ($type == "") {
            $type = "light dk";
        }
        $content = @$matches[5];

        if ($tid){
            $base_url = Utils::get_tag_base_url();
            return '<a class="bg-tag-a" data-pjax href="'.$base_url.'?tag='.$tid.'">'.'<span class="label bg-tag">' . TagContent::getTagName($tid,$content) . '</span>'.'</a>';
        }else{
            return '<span class="label bg-' . $type . '">' . $content . '</span>';
        }


    }

    public static function tabsParseCallback($matches)
    {
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $content = $matches[5];

        $pattern = CommonContent::get_shortcode_regex(array('tab'));
        preg_match_all("/$pattern/", $content, $matches);
        $tabs = "";
        $tabContents = "";
        for ($i = 0; $i < count($matches[3]); $i++) {
            $item = $matches[3][$i];
            $text = $matches[5][$i];
            $id = "tabs-" . md5(uniqid()) . rand(0, 100) . $i;
            $attr = htmlspecialchars_decode($item);//还原转义前的参数列表
            $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
            $name = @$attrs['name'];
            $active = @$attrs['active'];
            $in = "";
            $style = "style=\"";
            foreach ($attrs as $key => $value) {
                if ($key !== "name" && $key !== "active") {
                    $style .= $key . ':' . $value . ';';
                }
            }
            $style .= "\"";

            if ($active == "true") {
                $active = "active";
                $in = "in";
            } else {
                $active = "";
            }
            $tabs .= "<li class='nav-item $active' role=\"presentation\"><a class='nav-link $active' $style data-toggle=\"tab\" 
aria-controls='" . $id . "' role=\"tab\" data-target='#$id'>$name</a></li>";
            $tabContents .= "<div role=\"tabpanel\" id='$id' class=\"tab-pane fade $active $in\">
            $text</div>";
        }


        return <<<EOF
<div class="tab-container post_tab box-shadow-wrap-lg">
<ul class="nav no-padder b-b scroll-hide" role="tablist">
{$tabs}
</ul>
<div class="tab-content no-border">
{$tabContents}
</div>
</div>
EOF;
    }

    public static function QRParseCallback($matches)
    {
        $options = mget();
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $title = @$attrs["title"];
        $sub = $attrs["sub"];
        $desc = $attrs["desc"];
        $url = @$attrs["url"];
        $img = THEME_URL . "libs/interface/GetCode.php?type=url&content=" . $url;

        return <<<EOF
<div class="m-b-md text-center countdown border-radius-6 box-shadow-wrap-normal">
<div class="r item hbox no-border">
<div class="col bg-light  w-210 v-middle wrapper-md">
<div class="entry-title font-thin h4 text-black l-h margin-b" ><span>{$title}</span></div>
<div class="font-thin h5"><span>{$sub}</span></div>
</div>
<div class="col bg-white padder-v r-r vertical-flex">
<div class="row text-center no-gutter w-full padder-sm ">
<div class="font-thin">
<img class="img-QR" src="{$img}" />
<span class="font-bold">{$desc}</span>
</div>
</div>             
</div>
</div>
</div>
EOF;


    }

    /**
     * @param $matches
     * @return bool|string
     */
    public static function countdownParseCallback($matches)
    {
        $options = mget();
        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $id = "countdown-" . md5(uniqid()) . rand(0, 100);
        $end = $attrs["time"];
        $title = @$attrs['title'];
        $head = "";
        if (trim($title) !== "") {
            $head = <<<EOF
            <div class="entry-title font-thin h4 text-black l-h margin-b" ><span>{$title}</span></div>
EOF;
        }
        $link = @$attrs["url"];
        $button = "";

        if (trim($link) !== "") {
            $button .= <<<EOF
<a class="btn m-b-xs btn-danger btn-addon margin-t" href="$link" target="_blank">
查看详情</a>
EOF;
        }
        $desc = @$attrs["desc"];
        if (trim($desc) == "") {
            $desc = _mt("倒计时");
        }
        $html = "";
        $js = "";
        //当前距离截止时间的秒数
        $leaveTime = strtotime($end);
        if ($leaveTime < 0) {

            $html .= <<<EOF
<div class="m-b-md text-center countdown border-radius-6 box-shadow-wrap-normal" id="{$id}">
              <div class="r item hbox no-border">
                <div class="col bg-light  w-210 v-middle wrapper-md">
                    $head
                  <div class="font-thin h5"><span>{$desc}</span></div>
                  $button
                </div>
                <div class="col bg-white padder-v r-r vertical-flex">
                <div class="row text-center no-gutter w-full">
                     <div class="font-thin text-muted"><span>已结束，截止日期：{$end}</span></div>

                </div>             
                 </div>
            </div>
</div>
EOF;
        } else {
            $html .= <<<EOF
<div class="m-b-md text-center countdown border-radius-6 box-shadow-wrap-normal" id="{$id}">
              <div class="r item hbox no-border">
                <div class="col bg-light  w-210 v-middle wrapper-md">
                  $head
                  <div class="font-thin h5"><span>{$desc}</span></div>
                  $button
                </div>
                <div class="col bg-white padder-v r-r vertical-flex">
                <div class="row text-center no-gutter w-full">
                <div class="col-xs-3">
                    <div class="inline m-t-sm">
                  <div  class="easyPieChart pie-days">
                    <div class="text-muted">
                      <span class="span-days">0</span>天
                    </div>
                </div>
              </div>
              </div>
              
              <div class="col-xs-3">
                <div class="inline m-t-sm">
                  <div class="easyPieChart pie-hours">
                    <div class="text-muted ">
                      <span class="span-hours">0</span>小时
                    </div>
                </div>
              </div>
              </div>
              
              <div class="col-xs-3">
                <div class="inline m-t-sm">
                  <div class="easyPieChart pie-minutes">
                    <div class="text-muted">
                      <span class="span-minutes">0</span>分钟
                    </div>
                </div>
              </div>
            </div>
            
            <div class="col-xs-3">
                <div class="inline m-t-sm">
                  <div class="easyPieChart pie-seconds">
                    <div class="text-muted">
                      <span class="span-seconds">0</span>秒
                    </div>
                </div>
              </div>
            </div>
            
                </div>             
                 </div>
            </div>
</div>
EOF;

            $js .= <<<EOF
<script>
$(function() {
     $.Module_Timer({
     
        startTime: $leaveTime,
        id: '$id'
    });
});         
</script>
EOF;

            $html .= $js;
        }
        return $html;


    }

    /**
     * 折叠框解析
     * @param $matches
     * @return bool|string
     */
    public static function collapseParseCallback($matches)
    {
        // 不解析类似 [[player]] 双重括号的代码
//        var_dump($matches);
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数

        $style = "style=\"";
        foreach ($attrs as $key => $value) {
            if ($key !== "title" && $key !== "status") {
                $style .= $key . ':' . $value . ';';
            }
        }
        $style .= "\"";


        $title = $attrs['title'];
        $default = @$attrs['status'];
        if ($default == null || $default == "") {
            $default = "true";
        }
        if ($default == "false") {//默认关闭
            $class = "collapse";
        } else {
            $class = "collapse in";
        }
        $class.=" collapse-content";
        $content = $matches[5];
        $notice = _mt("开合");
        $id = "collapse-" . md5(uniqid()) . rand(0, 100);

        return <<<EOF
<div class="panel panel-default collapse-panel box-shadow-wrap-lg"><div class="panel-heading panel-collapse" data-toggle="collapse" data-target="#{$id}" aria-expanded="true"><div class="accordion-toggle"><span $style>{$title}</span>
<i class="pull-right fontello icon-fw fontello-angle-right"></i>
</div>
</div>
<div class="panel-body collapse-panel-body">
<div id="{$id}" class="{$class}"><p></p>{$content}<p></p></div></div></div>
EOF;


    }

    public static function parseSingleMusic($info, $setting)
    {

        $artist = @$info['artist'] ? $info['artist'] : @$info['author'];
        $server = @$info['server'] ? $info['server'] : @$info['media'];


        if (@$info['id'] !== null) {//云解析
            try {
                $salt = Typecho_Widget::widget('Widget_Options')->plugin('Handsome')->salt;
                $auth = md5(@$salt.$server.$info['type'].$info['id'].$salt);
                $playCode = '<div class="handsome_aplayer_music" data-id="' . @$info['id'] . '" data-server="'
                    . $server . '" data-type="'
                    . $info['type'] . '" data-auth="' . $auth . '"';
            }catch (Typecho_Plugin_Exception $e){
                $playCode = "<div";
            }

        } else {//本地资源
            $playCode = '<div class="handsome_aplayer_music" data-name="' . @$info['title'] . '" data-artist="'
                . $artist
                . '" data-url="'
                . @$info['url'] . '" data-cover="' . @$info['pic'] . '" data-lrc="' . @$info['lrc'] . '"';
        }


        $playCode .= "></div>\n";


        return $playCode;

    }

    /**
     * 音乐解析的正则替换回调函数
     * @param $matches
     * @return bool|string
     * url - 自定义mp3链接的地址
     * title - 歌曲名称
     * author - 歌曲作者 --> artist
     * media - 云解析媒体 --> server
     */
    public static function musicParseCallback($matches)
    {
        /*
        $mathes array
        * 1 - An extra [ to allow for escaping shortcodes with double [[]]
        * 2 - 短代码名称
        * 3 - 短代码参数列表
        * 4 - 闭合标志
        * 5 - 内部包含的内容
        * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     */

        // 不解析类似 [[player]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //[hplayer media=&quot; netease&quot; type=&quot; song&quot;  id=&quot; 23324242&quot; /]
        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/", '$1', $matches[3]);
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数，最外层短代码的参数，旧版本中 = 歌曲信息 + 设置信息 新版本中 = 设置信息

        //获取内部内容
        $pattern = CommonContent::get_shortcode_regex(array('Music'));
        preg_match_all("/$pattern/", $matches[5], $all);

        $playerCode = self::parsePlayerAttribute($attrs);

        //播放器内部的歌曲/歌曲列表
        if (sizeof($all[3])) {
            //当内部有内容时候，可能是一首歌曲或者多首歌曲
            foreach ($all[3] as $vo) {
                $t = CommonContent::shortcode_parse_atts(htmlspecialchars_decode($vo));
                $playerCode .= self::parseSingleMusic($t, $attrs);
            }
        } else {
            //旧版本兼容，单首歌曲解析
            $playerCode .= self::parseSingleMusic($attrs, $attrs);

        }

        $playerCode .= "</div>\n";//player 结束符号

        return $playerCode;

    }

    public static function parsePlayerAttribute($setting, $isGlobal = false){
        //播放器默认设置
        $player = array(
            'preload' => 'auto',
            'autoplay' => 'false',
            'listMaxHeight' => '340px',
            'order' => 'list',
        );
        $global = ($isGlobal) ? " player-global" : " player-content";
        $head = "<div class='handsome_aplayer" . $global . "'";

        if (is_array($setting)) {
            foreach ($setting as $key => $vo) {
                $player[$key] = $vo;
            }
        }
        foreach ($player as $key => $vo) {
            $head .= " data-{$key}=\"{$vo}\"";
        }
        $head .= ">\n";
        $head .= '<div class="handsomePlayer-tip-loading"><span></span> <span></span> <span></span> <span></span><span></span></div>';

        return $head;

    }



    public static function emojiParseCallback($matches)
    {
        $emotionPathPrefix = THEME_FILE . 'assets/img/emotion';
        $emotionUrlPrefix = STATIC_PATH . 'img/emotion';
        $path = $emotionPathPrefix . '/' . @$matches[1] . '/' . @$matches[2] . '.png';
        $url = $emotionUrlPrefix . '/' . @$matches[1] . '/' . @$matches[2] . '.png';
        //检查图片文件是否存在
        if (is_file($path) == true) {
            return '<img src="' . $url . '" class="emotion-' . @$matches[1] . '">';
        } else {
            return @$matches[0];
        }
    }


    /**
     * 一篇文章中引用另一篇文章正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function quoteOtherPostCallback($matches)
    {
        $options = mget();
        // 不解析类似 [[post]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/", '$1', $matches[3]);
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数

        //这里需要对id做一个判断，避免空值出现错误
        $cid = @$attrs["cid"];
        $url = @$attrs['url'];
        $cover = @$attrs['cover'];//封面
        $targetTitle = "";//标题
        $targetUrl = "";//链接
        $targetSummary = "";//简介文字
        $targetImgSrc = "";//封面图片地址
        if (!empty($cid)) {
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $posts = $db->fetchAll($db
                ->select()->from($prefix . 'contents')
                ->orWhere('cid = ?', $cid)
                ->where('type = ? AND password IS NULL AND (status = ? OR status = ?)', 'post', 'publish',"hidden"));
            //这里需要对id正确性进行一个判断，避免查找文章失败
            if (count($posts) == 0) {
                $targetTitle = "文章不存在，或文章是加密、私密文章";
            } else {
                $result = Typecho_Widget::widget('Widget_Abstract_Contents')->push($posts[0]);
                if ($cover == "" || $cover == "http://") {

                    $thumbArray = $db->fetchAll($db
                        ->select()->from($prefix . 'fields')
                        ->orWhere('cid = ?', $cid)
                        ->where('name = ? ', 'thumb'));
                    $targetImgSrc = CommonContent::whenSwitchHeaderImgSrc(null, 0, 2,
                        @$thumbArray[0]['str_value'],$result['text']);

                } else {
                    $targetImgSrc = $cover;
                }
                $targetSummary = CommonContent::excerpt(Markdown::convert($result['text']), 60);
                $targetTitle = $result['title'];
                $targetUrl = $result['permalink'];
            }
        } else if (empty($cid) && $url !== "") {
            $targetUrl = $url;
            $targetSummary = @$attrs['intro'];
            $targetTitle = @$attrs['title'];
            $targetImgSrc = $cover;
        } else {
            $targetTitle = "文章不存在，请检查文章CID";
        }

        $imageHtml = "";
        $size=@$attrs["size"];
        if ($size == "small"){
            return <<<EOF
<a class="post_link" href="{$targetUrl}"><i data-feather="file-text"></i>{$targetTitle}</a>
EOF;
        }

        $noImageCss = "";
        if (trim($targetImgSrc) !== "") {
            $targetImgSrc = Utils::returnImageSrcWithSuffix($targetImgSrc,null,800,0);
            $imageHtml = '<div class="inner-image bg" style="background-image: url(' . $targetImgSrc . ');background-size: cover;"></div>
';
        } else {
            $noImageCss = 'style="margin-left: 10px;"';
        }

        return <<<EOF
<div class="preview">
<div class="post-inser post box-shadow-wrap-normal">
<a href="{$targetUrl}" target="_blank" class="post_inser_a no-external-link no-underline-link">
{$imageHtml}
<div class="inner-content" $noImageCss>
<p class="inser-title">{$targetTitle}</p>
<div class="inster-summary text-muted">
{$targetSummary}
</div>
</div>
</a>
<!-- .inner-content #####-->
</div>
<!-- .post-inser ####-->
</div>
EOF;

    }

    public static function returnIconHtml($name,$needParcel=false){
        if (count(mb_split(" ", $name)) == 1 && strpos($name, "fontello") === false && strpos($name, "glyphicon") === false) {
            if (Utils::has_special_char($name)){
                if ($needParcel){
                    return '<i class="icon-emoji">'.$name.'</i>';
                }else{
                    return $name;
                }
            }else{
                if ($needParcel){
                    return '<i><i data-feather="'.$name.'"></i></i>';
                }else{
                    return '<i data-feather="'.$name.'"></i>';
                }
            }
        }else{
            return '<i class="'.$name.'"></i>';

        }
    }
    
    /**
     * 解析显示按钮的短代码的正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function parseButtonCallback($matches)
    {
        // 不解析类似 [[post]] 双重括号的代码
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        /*$matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]);*/
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = CommonContent::shortcode_parse_atts($attr);//获取短代码的参数
        $type = "";
        $color = "primary";
        $icon = "";
        $addOn = " ";
        $linkUrl = "";
        $size = "";
        if (@$attrs['type'] == "round") {
            $type = "btn-rounded";
        }
        $size =(@$attrs['size']);
        if ($size == ""){
            $size = "";
        }else{
            $size = "btn-".$size;
        }

        if (@$attrs['url'] != "") {
            $linkUrl = 'window.open("' . $attrs['url'] . '","_blank")';
        }
        if (@$attrs['color'] != "") {
            $color = $attrs['color'];
        }

        if (@$attrs['icon'] != "") {//判断是否是feather 图标
            $icon = ScodeParse::returnIconHtml($attrs['icon'],true);
            $addOn = 'btn-addon';
        }

        return <<<EOF
<button class="$size btn m-b-xs btn-{$color} {$type}{$addOn}" onclick='{$linkUrl}'>{$icon}{$matches[5]}</button>
EOF;
    }
}
