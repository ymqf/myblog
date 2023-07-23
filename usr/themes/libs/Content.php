<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Content.php
 * Author     : hewro
 * Date       : 2017/05/21
 * Version    : 1.0.0
 * Description: 使用PHP方法输出内容
 */

$prefix = Utils::getMixPrefix();
require_once("typecho/" . $prefix . "List.php");
require_once("typecho/" . $prefix . "CommentArchive.php");

//分模块
require_once("content/CommentContent.php");
require_once("content/PostContent.php");
require_once("content/WidgetContent.php");
require_once("content/ScodeContent.php");
require_once("content/IndexContent.php");
require_once("content/CommonContent.php");

//require("component/Recent.php");

class Content
{
    const version = "42447ab88be199edcae3deeb830b8dd2";
    const debug = "5e82dd5106a545605319f13e03798261";


    /**
     * 文章以及搜索页面的导航条
     * @param $archive
     * @param $WebUrl
     */
    public static function BreadcrumbNavigation($archive, $WebUrl)
    {
        $WebUrl = $WebUrl . '/';
        $options = mget();
        if (@in_array("sreenshot", $options->featuresetup) && $archive->is("post")) {
            $screenshotStyle = '.breadcrumb i.fontello.fontello-weibo:after {
    padding: 0 5px 0 5px;
    color: #ccc;
    content: "/\00a0";
    }';
            $screenshot = '   <a id="generateShareImg" itemprop="breadcrumb" title="" data-toggle="tooltip" data-original-title="' . _mt("生成分享图") . '"><i style ="font-size:13px;" class="fontello fontello-camera" aria-hidden="true"></i></a>';
        } else {
            $screenshot = "";
            $screenshotStyle = "";
        }
        echo '<ol class="breadcrumb bg-white-pure" itemscope="">';
        echo '<li>
                 <a href="' . $WebUrl . '" itemprop="breadcrumb" title="' . _mt("返回首页") . '" data-toggle="tooltip"><span class="home-icons"><i data-feather="home"></i></span>' . _mt("首页") . '</a>
             </li>';
        if ($archive->is('archive')) {
            echo '<li class="active">';
            $archive->archiveTitle(array(
                'category' => _t('%s'),
                'search' => _t('%s'),
                'tag' => _t('%s'),
                'author' => _t('%s')
            ), '', '');
            echo '</li></ol>';
        } else {
            if ($archive->is('page')) {
                echo '<li class="active">' . $archive->title . '&nbsp;&nbsp;</li>';
            } else {
                echo '<li class="active">' . _mt("正文") . '&nbsp;&nbsp;</li>';
            }
            if (!@in_array("no-share", $options->featuresetup)) {
                echo '
              <div style="float:right;">
   ' . _mt("分享到") . '：
   <style>
   .breadcrumb i.iconfont.icon-qzone:after {
    padding: 0 0 0 5px;
    color: #ccc;
    content: "/\00a0";
    }
    ' . $screenshotStyle . '
   </style>
   <a href="https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . $archive->permalink . '&title=' .
                    $archive->title . '&site=' . $WebUrl . '" itemprop="breadcrumb" target="_blank" title="" data-toggle="tooltip" data-original-title="' . _mt("分享到QQ空间") . '" onclick="window.open(this.href, \'qzone-share\', \'width=550,height=335\');return false;"><i style ="font-size:15px;" class="iconfont icon-qzone" aria-hidden="true"></i></a>
   <a href="https://service.weibo.com/share/share.php?url=' . $archive->permalink . '&title=' . $archive->title . '" target="_blank" itemprop="breadcrumb" title="" data-toggle="tooltip" data-original-title="' . _mt("分享到微博") . '" onclick="window.open(this.href, \'weibo-share\', \'width=550,height=335\');return false;"><i style ="font-size:15px;" class="fontello fontello-weibo" aria-hidden="true"></i></a>' . $screenshot . '</div>';
            }
            echo '</ol>';
        }
    }


    /**
     * 文章页面标题
     * @param $archive
     * @param $isLogin
     * @param bool $isCustom 是否是独立页面模板
     * @return string
     */
    public static function exportPostPageHeader($archive, $isLogin, $isCustom = false)
    {
        $html = "";
        $html .= '
        <header id="small_widgets" class="bg-light lter wrapper-md">
             <h1 class="entry-title m-n font-thin text-black l-h">' . $archive->title;
        if (!$isCustom) {
            $html .= '<a class="plus-font-size" data-toggle="tooltip" data-original-title="' . _mt("点击改变文章字体大小") . '"><i data-feather="type"></i></a>';
        }

        if (@Utils::getExpertValue("post_speech", true) !== false) {
            $html .= '<a class="speech-button m-l-sm superscript" data-toggle="tooltip" data-original-title="' .
                _mt
                ("朗读文章") . '"><i data-feather="mic"></i></a>';
        }


        if ($isLogin) {
            if ($archive->is("page")) {
                $html .= '
                     <a class="m-l-sm superscript" href="' . Helper::options()->adminUrl . 'write-page.php?cid=' . $archive->cid . '" 
                     target="_blank"><i data-feather="edit" aria-hidden="true"></i></a>
                     ';
            } else {
                $html .= '
                     <a class="m-l-sm superscript" href="' . Helper::options()->adminUrl . 'write-post.php?cid=' . $archive->cid . '" target="_blank"><i data-feather="edit" aria-hidden="true"></i></a>
                     ';
            }
        }
        if (!$isCustom && !Utils::getExpertValue("off_read_mode")) {
            $html .= '<a data-morphing style="vertical-align: -1px;" id="morphing" data-src="#morphing-content" href="javascript:;" class="read_mode superscript m-l-sm" 
data-toggle="tooltip" data-placement="right" data-original-title="' . _mt("阅读模式") . '"><i data-feather="book-open"></i></a>';
        }

        if ($archive->is("page")) {
            $html .= '</h1></header>';
        } else {
            $html .= '</h1>';//此时不用封闭header标签
        }
        return $html;
    }


    /**
     * 输出DNS预加载信息
     * @return string
     */
    public static function exportDNSPrefetch()
    {
        $defaultDomain = array();
        $customDomain = mget()->dnsPrefetch;
        if (!empty($customDomain)) {
            $customDomain = mb_split("\n", $customDomain);
            $defaultDomain = array_merge($defaultDomain, $customDomain);
            $defaultDomain = array_unique($defaultDomain);
        }
        $html = "<meta http-equiv=\"x-dns-prefetch-control\" content=\"on\">\n";
        foreach ($defaultDomain as $domain) {
            $domain = trim($domain, " \t\n\r\0\x0B/");
            if (!empty($domain)) {
                $html .= "<link rel=\"dns-prefetch\" href=\"//{$domain}\" />\n";
            }
        }
        return $html;
    }


    public static function exportMobileBackground()
    {
        $html = "";
        $options = mget();
        if ($options->BGtype == 0) {
            $html .= 'background: ' . $options->bgcolor_mobile . '';
        } elseif ($options->BGtype == 1) {
            $html .= 'background: url(' . $options->bgcolor_mobile . ') center center no-repeat no-repeat fixed #6A6B6F;background-size: cover;';
        }
        return $html;
    }


    /**
     * 选择全站布局设置
     * @param $options
     * @return string
     */
    public static function selectLayout($options)
    {
        $layout = '<div id="alllayout" class="app ';
        if (@in_array('aside-fix', Utils::checkArray($options))) {
            $layout .= 'app-aside-fix ';
        }
        if (@in_array('aside-folded', Utils::checkArray($options))) {
            $layout .= 'app-aside-folded ';
        }
        if (@in_array('aside-dock', Utils::checkArray($options))) {
            $layout .= 'app-aside-dock ';

            if (Utils::getExpertValue("small-dock", true)) {
                $layout .= 'small-dock ';
            }
        }
        if (@in_array('container-box', Utils::checkArray($options))) {
            $layout .= 'container ';
        } else {
            $layout .= 'no-container ';
        }
        if (@in_array('header-fix', Utils::checkArray($options))) {
            $layout .= 'app-header-fixed ';
        }
        $layout .= '">';
        return $layout;
    }


    /**
     * 根据是否为盒子布局，输出相应的HTML标签
     * @param $options
     * @return string
     */
    public static function exportHtmlTag($options)
    {
        $theme = "";
        if (@$_COOKIE['theme_dark'] == "1") {
            $theme = " theme-dark ";
        }
        $html = '<html class="small-scroll-bar no-js' . $theme;
        if (@in_array('container-box', Utils::checkArray($options)) || @in_array('opacityMode', Utils::checkArray
            ($options))) {
            $html .= ' bg';
        }
        if (@in_array("opacityMode", $options)) {
            $html .= ' cool-transparent';
        }
        $html .= '"';
        return $html;
    }


    /**
     * 输出自定义css + 如果为盒子布局输出背景css
     * @param $archive
     * @return string
     */
    public static function exportCss($archive)
    {
        $css = "";
        $css .= '
        html.bg {
        ' . IndexContent::exportBackground() . '
        }
        .cool-transparent .off-screen+#content {
        ' . IndexContent::exportBackground() . '
        }';
        $css .= '
@media (max-width:767px){
    html.bg {
        ' . Content::exportMobileBackground() . '
        }
        .cool-transparent .off-screen+#content {
        ' . Content::exportMobileBackground() . '
        }
}
';

        $options = mget();

        $css .= $options->customCss;

        if (Utils::getExpertValue("p_indent")) {
            $css .= <<<EOF
.entry-content p ,#morphing-content p{
    text-indent:2em;            
}
EOF;
        }


        if (COMMENT_SYSTEM != 0) {
            $css .= <<<EOF
    .nav-tabs-alt .nav-tabs>li[data-index="2"].active~.navs-slider-bar {
        transform: translateX(405%);
    }
    .nav-tabs-alt .nav-tabs>li[data-index="0"].active~.navs-slider-bar {
        transform: translateX(100%);
    }
EOF;

        }

        if (@in_array("snow", $options->featuresetup)) {
            $image = STATIC_PATH . "img/snow.gif";
            $css .= <<<EOF
#aside .wrapper:hover {
	background: url({$image});
	background-size: cover;
	color: #999;
}
EOF;

        }
        return $css;
    }


    /**
     * 处理显示是否有图片地址的逻辑：根据thumb字段和后台外观设置的是否开启头图开关
     * @param $widget
     * @param string $select 判断是首页还是文章页面的头图，因为文章页面头图一定是大头图的
     * @param int $index 序号
     * @param $flag true 表示一定会返回图片，不受设置影响，相册列表页面会使用
     * @return string
     */
    public static function returnHeaderImgSrc($widget, $select, $index = 0, $flag = false)
    {
        $options = mget();
        $thumbChoice = $widget->fields->thumbChoice;
        $thumb = $widget->fields->thumb;
        if ($thumb == "no") {
            $thumbChoice = "no";
        }
        $imgSrc = "";
        $howToThumb = $options->RandomPicChoice;
        $thumbSelect = 0;//0不显示，1全显示，2只显示首页，3只显示文字页面,-1表示不管什么情况都返回头图地址
        if ($flag) {
            $thumbSelect = 1;//保证返回图片
            $howToThumb = 2;//保证相册分类一定有图片
        } else {
            if ($thumbChoice == "no") {
                $thumbSelect = 0;
            } else if ($thumbChoice == "yes") {
                $thumbSelect = 1;
            } else if ($thumbChoice == "yes_only_index") {
                $thumbSelect = 2;
            } else if ($thumbChoice == "yes_only_post") {
                $thumbSelect = 3;
            } else if ($thumbChoice == "default" || $thumbChoice == "") {
                if (in_array('NoRandomPic-post', Utils::checkArray($options->indexsetup)) && in_array('NoRandomPic-index',
                        $options->indexsetup) && $flag == false) {//全部关闭
                    $thumbSelect = 0;
                } else if ((!in_array('NoRandomPic-post', Utils::checkArray($options->indexsetup)) && !in_array('NoRandomPic-index',
                        $options->indexsetup))) {//全部开启
                    $thumbSelect = 1;
                } else {//一开一闭
                    if (in_array('NoRandomPic-post', Utils::checkArray($options->indexsetup))) {//不显示文章头图，显示首页头图
                        $thumbSelect = 2;
                    } else {//不显示首页头图，显示文章页面头图
                        $thumbSelect = 3;
                    }

                }
            }
        }


        switch ($thumbSelect) {
            case 0://全部关闭
                break;
            case 1://全部开启
                $imgSrc = CommonContent::whenSwitchHeaderImgSrc($widget, $index, $howToThumb, $widget->fields->thumb);
                break;
            case 2://不显示文章头图，显示首页头图
                if ($select == "post") {
                    $imgSrc = "";
                } else {
                    $imgSrc = CommonContent::whenSwitchHeaderImgSrc($widget, $index, $howToThumb, $widget->fields->thumb);
                }
                break;
            case 3://不显示首页头图，显示文章页面头图
                if ($select == "post") {
                    $imgSrc = CommonContent::whenSwitchHeaderImgSrc($widget, $index, $howToThumb, $widget->fields->thumb);
                } else {
                    $imgSrc = "";
                }
                break;
        }
        return $imgSrc;
    }


    public static function returnSharePostDiv($obj)
    {
        $headImg = Content::returnHeaderImgSrc($obj, "post", 0);
        if (trim($headImg) == "") {//头图为空
            $headImg = STATIC_PATH . 'img/video.jpg';
        }
        $author = $obj->author->screenName;
        $title = $obj->title;
        $url = $obj->permalink;
        if ($obj->fields->album != "") {
            $content = $obj->fields->album;
        } else {
            $content = $obj->excerpt;
        }
        $expert = CommonContent::excerpt($content, 60);
        if (trim($expert) == "") {
            $expert = _mt("暂无文字描述");
        }
        $year = date('Y/m', $obj->date->timeStamp);
        $day = date('d', $obj->date->timeStamp);
        $notice = _mt("扫描右侧二维码阅读全文");
        $image = THEME_URL . 'libs/interface/GetCode.php?type=url&content=' . $url;
        $options = mget();
        //如果开启了开关才需要生成，否则返回空
        if (@in_array("sreenshot", $options->featuresetup) && $obj->is("post")) {

            return <<<EOF
        <style>
        
        .mdx-si-head .cover{
            object-fit: cover;
            width: 100%;
            height: 100%
        }
        
</style>
<div class="mdx-share-img" id="mdx-share-img"><div class="mdx-si-head" style="background-image:url({$headImg})"><p>{$author}</p><span>{$title}</span></div><div 
class="mdx-si-sum">{$expert}</div><div class="mdx-si-box"><span>{$notice}</span><div class="mdx-si-qr" id="mdx-si-qr"><img 
src="{$image}"></div></div><div class="mdx-si-time">{$day}<br><span 
class="mdx-si-time-2">{$year}</span></div></div>
EOF;
        } else {
            return "";
        }
    }


    public static function returnReadModeContent($obj, $status,$content)
    {
        if (Utils::getExpertValue("off_read_mode")) {
            return "";
        }
        $html = "";
        $author = $obj->author->screenName;
        $time = date("Y 年 m 月 d 日", $obj->date->timeStamp);

        $way = PostContent::getPostParseWay($obj->fields->parseWay);
        $options = mget();
        //阅读模式不对图片进行懒加载
        $key = array_search("lazyload", $options->featuresetup);
        if (isset($key)) {
            $options_tmp = $options->featuresetup;
//            print_r($key);
            $options_tmp[$key] = "";
            $options->featuresetup = $options_tmp;
        }
//        $content = PostContent::postContent($obj, $status, $way, null,Device::isIE());
        if (isset($key)) {
            $options_tmp[$key] = "lazyload";
            $options->featuresetup = $options_tmp;
        }

        if ($way == "vditor") {
            $core_content = '<div class="loading-post text-center m-t-lg m-b-lg">
                 <p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...</p>
             </div><div id="morphing-content-real"></div>
            <textarea class="hide" id="morphing-content-real_text">' . htmlspecialchars($content) . '</textarea>';
        } else {
            $core_content = '<textarea id="morphing-content-real_origin_text">' . htmlspecialchars($content) . '</textarea>
<div id="morphing-content-real_origin"></div>';

        }
        $html .= <<<EOF

<div id="morphing-content" class="hidden read_mode_article">
        <div class="page">
            <h1 class="title">$obj->title</h1>
            <div class="metadata singleline"><a href="#" rel="author" class="byline">{$author}</a>&nbsp;•&nbsp;<span class="delimiter"></span><time class="date">{$time}</time></div>     
            {$core_content}
        </div>
    </div>
EOF;
        return $html;
    }

    /**
     * 单个页面输出头图函数
     * @param $obj
     * @param int $index
     * @return string
     */
    public static function exportHeaderImg($obj, $index = 0)
    {

        $parameterArray = array();
        $options = mget();
        $parameterArray['imgSrc'] = Content::returnHeaderImgSrc($obj, "post", $index);
        $parameterArray['thumbDesc'] = $obj->fields->thumbDesc;
        $parameterArray['isIndex'] = false;
        //是否固定图片大小（默认8：3）
        //echo $options->featuresetup.'test';
        if (in_array('FixedImageSize', Utils::checkArray($options->featuresetup))) {
            $parameterArray['isFixedImg'] = true;
        } else {
            $parameterArray['isFixedImg'] = false;
        }
        return Content::returnPostItem($parameterArray);
    }

    /**
     * 输出文章列表:首页和archive页面
     * @param $obj
     */
    public static function echoPostList($obj)
    {

        //todo 优化性能


        $options = mget();

        $index = 0;

        if ($options->thumbArrangeStyle == "water_fall") {
//            echo '<div class="blog-post water-fall">';
            echo '<div class="blog-post water-fall-container post-list">';
        } else {
            echo '<div class="blog-post post-list">';
        }
//        print_r($obj);
        while ($obj->next()) {
            $parameterArray = array();
            $data = Utils::isLock($obj->title, @$obj->categories[0]['description'], @$obj->categories[0]['slug'], "category", $obj->date->timeStamp);
            if ($data["flag"] && Utils::getExpertValue("lock_hide_title", false)) {
                $parameterArray['title'] = _mt("此内容被加密分类保护");
            } else {
                $parameterArray['title'] = $obj->sticky . $obj->title;
            }

            //是否是大版式头图
            $styleThumb = strtoupper($obj->fields->thumbStyle);
            if ($styleThumb == "DEFAULT" || trim($styleThumb) == "") {//跟随外观设置的配置
                if ($options->thumbStyle == "0") {//小头图
                    $parameterArray['thumbStyle'] = "SMALL";
                } else if ($options->thumbStyle == "2") {//交错显示
                    if ($index % 2 == 0) {
                        $parameterArray['thumbStyle'] = "LARGE";
                    } else {
                        $parameterArray['thumbStyle'] = "SMALL";
                    }
                } else if ($options->thumbStyle == "1") {//大头图
                    $parameterArray['thumbStyle'] = "LARGE";
                } else if ($options->thumbStyle == "3") {
                    $parameterArray['thumbStyle'] = "PICTURE";
                } else {//默认是大图
                    $parameterArray['thumbStyle'] = "LARGE";
                }
            } else {
                $parameterArray['thumbStyle'] = $styleThumb;
            }

            if (in_array('NoSummary-index', Utils::checkArray($options->indexsetup))) {
                $expertNum = 0;
            } else {
                if ($parameterArray['thumbStyle'] == "SMALL") {//小头图
                    if ($options->numberOfSmallPic == "") {//自定义摘要字数
                        $expertNum = 80;
                    } else {
                        $expertNum = $options->numberOfSmallPic;
                    }
                } else {
                    if ($options->numberOfBigPic == "") {//自定义摘要字数
                        $expertNum = 200;
                    } else {
                        $expertNum = $options->numberOfBigPic;
                    }
                }
            }
            if (trim($obj->fields->customSummary) != "") {
                $obj->excerpt = $obj->fields->customSummary;
            }
            if ($data["flag"]) {//加密分类
                $parameterArray['summary'] = CommonContent::excerpt("", $expertNum);
            } else {
                $parameterArray['summary'] = CommonContent::excerpt($obj->excerpt, $expertNum);
            }

//            $startTime = microtime(true);
            $parameterArray['imgSrc'] = Content::returnHeaderImgSrc($obj, "index", $index);
//            $endTime = microtime(true);
//            print_r(($endTime-$startTime)*1000 . ' ms');

            $parameterArray['linkUrl'] = $obj->permalink;
            /*if (index == 0){
                print_r($obj->author);
            }*/
            $parameterArray['author'] = $obj->author->screenName;
            $parameterArray['authorUrl'] = $obj->author->permalink;
            $parameterArray['date'] = $obj->date->timeStamp;
            $parameterArray['commentNum'] = $obj->commentsNum;
            // 在使用原生评论系统时候，不进行view数目查询，过多的view查询会导致性能问题
            if ($options->commentChoice != "0") {
//                $startTime = microtime(true);
//                $parameterArray['viewNum'] = get_post_view($obj);
//                $endTime = microtime(true);
//                print_r(($endTime-$startTime)*1000 . ' ms');
            } else {
                $parameterArray['viewNum'] = 0;
            }
            //是否是首页
            $parameterArray['isIndex'] = true;

            //是否固定图片大小（默认8：3）
            if (in_array('FixedImageSize', Utils::checkArray($options->featuresetup))) {
                $parameterArray['isFixedImg'] = true;
            } else {
                $parameterArray['isFixedImg'] = false;
            }

            $parameterArray["smallThumbField"] = $obj->fields->thumbSmall;
            $parameterArray["nothumbStyle"] = $obj->fields->noThumbInfoStyle;
            if ($parameterArray["nothumbStyle"] == "default" || $parameterArray["nothumbStyle"] == "") {
                $parameterArray["nothumbStyle"] = $obj->fields->noThumbInfoEmoji;
            }
            $parameterArray["allowComment"] = $obj->allowComment;


            echo Content::returnPostItem($parameterArray);
            $index++;
        }
        echo '</div>';
    }


    /**
     * @param $parameterArray
     * @return string : 返回单篇文章头部的HTML代码
     * @internal param $title : 标题
     * @internal param $summary : 摘要
     * @internal param $imgSrc : 头图地址
     * @internal param $linkUrl : 文章地址
     * @internal param $author : 作者
     * @internal param $authorUrl : 作者链接
     * @internal param $date : 日期
     * @internal param int $commentNum : 评论数
     * @internal param int $viewNum : 浏览数
     * @internal param bool $isBig : 是否是大版式头图
     * @internal param bool $isIndex : 是否是首页
     * @internal param bool $isFixedImg : 是否固定图片大小（默认8：3）
     */
    public static function returnPostItem($parameterArray)
    {
        $options = mget();

        if ($parameterArray['isIndex']) {
            //格式化时间
            if ($parameterArray['date'] != 0) {
                $dateString = date(I18n::dateFormat(), $parameterArray['date']);
            }
            //格式化评论数
            if ($parameterArray['allowComment'] == 1) {
                if ($parameterArray['commentNum'] == 0) {
                    $commentNumString = _mt("暂无评论");
                } else {
                    $commentNumString = $parameterArray['commentNum'] . " " . _mt("条评论");
                }
            } else {
                $commentNumString = _mt("关闭评论");
            }
            //格式化浏览次数
//            $viewNumString = $parameterArray['viewNum'] . " " . _mt("次浏览");
        }

        $html = "";
        //首页文章
        if ($parameterArray['isIndex']) {//首页界面的文章item结构
            //头图部分
            if ($parameterArray['imgSrc'] == "") {//图片地址为空即不显示头图
                $parameterArray['thumbStyle'] = "LARGE";
                $html .= '<div class="single-post panel box-shadow-wrap-normal">';
            } else {
                if ($parameterArray['thumbStyle'] == "LARGE") {//大版式头图
                    $backgroundImageHtml = Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'], 1200, 0);
                    if ($parameterArray['isFixedImg']) {//裁剪头图
                        $html .= <<<EOF
<div class="single-post panel box-shadow-wrap-normal">
    <div class="index-post-img">
        <a href="{$parameterArray['linkUrl']}">
            <div class="item-thumb lazy" {$backgroundImageHtml}>
</div>
           
        </a>
    </div>
EOF;
                    } else {//头图没有裁剪
                        $imageHtml = Utils::returnImageLazyLoadHtml(false, $parameterArray['imgSrc'], 1200, 0);
                        $html .= <<<EOF
<div class="single-post panel box-shadow-wrap-normal">
<div class="index-post-img"><a href="{$parameterArray['linkUrl']}"><img {$imageHtml} 
class="img-full lazy" /></a></div>
EOF;
                    }
                } else if ($parameterArray['thumbStyle'] == "SMALL") {//小版式头图
                    if (trim($parameterArray['smallThumbField']) !== "") {//小头图专用字段
                        $parameterArray['imgSrc'] = $parameterArray['smallThumbField'];
                    }
                    $backgroundImageHtml = Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'], 800, 0);
                    $html .= <<<EOF
<div class="panel-small single-post box-shadow-wrap-normal">
    <div class="index-post-img-small post-feature index-img-small">
        <a href="{$parameterArray['linkUrl']}">
            <div class="item-thumb-small lazy" {$backgroundImageHtml}></div>
        </a>
    </div>
EOF;
                } else {//图片版式
                    //TODO: picture
                    $backgroundImageHtml = Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'], 1200, 0);
                    $html .= <<<EOF
<div class="single-post panel-picture border-radius-6 box-shadow-wrap-normal">
                <figure class="post-thumbnail border-radius-6">
                    <a class="post-thumbnail-inner index-image lazy" href="{$parameterArray['linkUrl']}" 
                    $backgroundImageHtml>  </a>
                </figure>
                <header class="entry-header wrapper-lg">
                    <h3 class="m-t-none text-ellipsis index-post-title"><a href="{$parameterArray['linkUrl']}" rel="bookmark" tabindex="0" data-pjax-state="">{$parameterArray['title']}</a></h3>
                    <div class="entry-meta">
                        <span class="byline"><span class="author vcard"><a  href="{$parameterArray['linkUrl']}" tabindex="0">{$parameterArray['summary']}</a></span></span>
                    </div>
                </header>
</div>
EOF;
                }
            }
            //图片样式不用添加这部分
            if ($parameterArray['thumbStyle'] !== "PICTURE") {
                //标题部分
                $html .= <<<EOF
<div class="post-meta wrapper-lg">
EOF;
                //个性化徽标
                if ($parameterArray['imgSrc'] == "" && $parameterArray['nothumbStyle'] != "default" && $parameterArray['nothumbStyle'] != "") { //无头图且显示个性化徽标
                    $style = $parameterArray['nothumbStyle'];
                    $content = "";
                    if (Utils::has_special_char($parameterArray['nothumbStyle'])) {
                        $content = $parameterArray['nothumbStyle'];
                        $style = "emoji";
                    }
                    // 判断是否有emoji
                    $html .= <<<EOF
<div class="item-meta-ico bg-ico-$style">{$content}</div>
EOF;
                }
                $html .= <<<EOF
    <h2 class="m-t-none text-ellipsis index-post-title text-title"><a 
    href="{$parameterArray['linkUrl']}">{$parameterArray['title']}</a></h2>
EOF;
                //if (!in_array('multiStyleThumb', Utils::checkArray($options->indexsetup))){
                //摘要部分
                $html .= <<<EOF
<p class="summary l-h-2x text-muted">{$parameterArray['summary']}</p>
EOF;
                //}
                //页脚部分，显示评论数、作者等信息
                $html .= <<<EOF
<div class="line line-lg b-b b-light"></div>
<div class="text-muted post-item-foot-icon text-ellipsis list-inline">
<li>
<span class="m-r-sm right-small-icons"><i data-feather="user"></i></span><a href="{$parameterArray['authorUrl']}">{$parameterArray['author']}</a></li>

<li><span class="right-small-icons m-r-sm"><i data-feather="clock"></i></span>{$dateString}</li>
EOF;
                if ($options->commentChoice == '0') {
                    $html .= <<<EOF
<li><span class="right-small-icons m-r-sm"><i 
data-feather="message-square"></i></span><a href="{$parameterArray['linkUrl']}#comments">{$commentNumString}</a></li>
EOF;

                } else {
//                    $html .= <<<EOF
//<li><span class="right-small-icons m-r-sm"><i data-feather="eye"></i></span><a
//href="{$parameterArray['linkUrl']}#comments">{$viewNumString}</a></li>
//EOF;

                }
                $html .= <<<EOF
</div><!--text-muted-->
</div><!--post-meta wrapper-lg-->
</div><!--panel/panel-small-->

EOF;
            } else {//picture头图样式


            }
        } else {//文章页面的item结构，只有头图，没有其他的了
            if ($parameterArray['imgSrc'] !== "") {
                $copyright = "";
                if (trim($parameterArray['thumbDesc']) != "") {
                    $copyright = '<div class="img_copyright" data-toggle="tooltip" data-placement="left" data-original-title="' . $parameterArray['thumbDesc'] . '"><i class="glyphicon glyphicon-copyright-mark"></i></div>';
                }
                //显示头图版权信息

                if ($parameterArray['isFixedImg']) {//固定头图大小
                    $html .= '<div class="entry-thumbnail" aria-hidden="true"><div class="item-thumb lazy" ' . Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'], 1200, 0) . '>' . $copyright . '</div></div>';
                } else {
                    $html .= '<div class="entry-thumbnail" aria-hidden="true"><img width="100%" height="auto" '
                        . Utils::returnImageLazyLoadHtml(false, $parameterArray['imgSrc'], 1200, 0) . ' 
 class="img-responsive lazy" />' . $copyright . '</div>';
                }
            }
        }
        return $html;
    }


    /**
     * 浏览器顶部标题
     * @param $obj
     * @param $title
     * @param $currentPage
     */
    public static function echoTitle($obj, $title, $currentPage, $commentPage)
    {
        $options = mget();
        if ($currentPage > 1) {
            _me("第%s页 - ", $currentPage);
        }

        if ($commentPage > 1) {
            //todo 这种描述容易有歧义
            _me("第%s页 - ", $commentPage);
        }
        $obj->archiveTitle(array(
            'category' => _mt('分类 %s 下的文章'),
            'search' => _mt('包含关键字 %s 的文章'),
            'tag' => _mt('标签 %s 下的文章'),
            'author' => _mt('%s 发布的文章')
        ), '', ' - ');
        echo $title;

        $titleIntro = $options->titleintro;
        if ($obj->is('index') && trim($titleIntro) !== "") {
            echo ' - ' . $options->titleintro . '';
        }
    }


    public static function parseGlobalPlayer()
    {
        $options = mget();
        $content = $options->musicUrl;

        $arr = explode("\n", $content);
        $setting = [
            'listMaxHeight' => '200px',
            "theme" => '#8ea9a7',
            "listFolded" => 'true',
            "fix_position" => 'true',
            "preload" => 'false',
            "i_autoplay" => in_array('autoPlay', Utils::checkArray($options->playerSetting)),
            "order" => (in_array('randomPlay', Utils::checkArray($options->playerSetting))) ? "random" : "list",
        ];//全局播放器设置
        $playerCode = ScodeParse::parsePlayerAttribute($setting, true);

        foreach ($arr as $item) {
            //判断是否是json数组
            if (trim($item) == "") {
                //跳过
            } else {
                //末尾如果又逗号需要去掉
                if (substr($item, -1) == ",") {
                    $item = substr($item, 0, -1);
                }

                $music = @json_decode($item, true);//本地音乐的json解析
//                print_r($item);
                if (!is_array($music)) {//直接云解析连接
                    $music = Utils::parseMusicUrlText($item);
                }

                if (is_array($music) && !empty($music)) {
                    $playerCode .= ScodeParse::parseSingleMusic($music, []);
                }
            }
        }

        $playerCode .= "</div>\n";//player 结束符号
        return $playerCode;
    }

    public static function returnLinkList($sort, $id)
    {


        $mypattern = <<<eof
<div class="col-sm-6">
<a href="{url}" target="_blank" class="list-group-item no-borders box-shadow-wrap-lg"> <span 
class="pull-left thumb-sm avatar m-r"> <img 
  src={image} alt="Error" class="img-square" /> <i class="{color} right"></i> </span> <span class="clear"><span class="text-ellipsis">
  {name}</span> <small 
  class="text-muted clear text-ellipsis">{title}</small> </span> </a>
</div>
eof;

        return '<div class="tab-pane fade in" id="' . $id . '"><div class="list-group list-group-lg list-group-sp row" style="margin: 0">' . Handsome_Plugin::output_str($mypattern, 0, $sort) . '</div></div>';
    }


    public static function postContentHtml($obj, $status)
    {

        $mathjaxOption = Utils::getMathjaxOption($obj->fields->mathjax);
        $parseWayOption = PostContent::getPostParseWay($obj->fields->parseWay);

        echo <<<EOF
<script>
        LocalConst.POST_MATHJAX = "{$mathjaxOption}"
        LocalConst.EDITOR_CHOICE = "{$parseWayOption}"
</script>
EOF;

        if ($mathjaxOption && PostContent::getPostParseWay($obj->fields->parseWay) == "origin") {
            echo <<<EOF
<script>            
        if (!window.MathJax){
            MathJax = {
                tex:{
                    inlineMath: [['$', '$']],
                },
                svg:{
                    fontCache: 'global'
                },
                startup: {
                    elements: [document.querySelector('#content')]          // The elements to typeset (default is document body)
                }
            };
        };
        </script>
EOF;

        }


        $way = PostContent::getPostParseWay($obj->fields->parseWay);


        $content = PostContent::postContent($obj, $status, $way,null,Device::isIE());

        //高亮关键词
        $keyword = trim(@$_GET["keyword"]);
        if ($keyword){
            $keyword = mb_split(" ",$keyword);
            $reg_str = "";
            $keyword = array_unique($keyword);
            $keyword = array_values($keyword);
            foreach ($keyword as $key){
                if (trim($key)!="" && preg_match("/[\x7f-\xff]/", $key)){//目前只支持包含中文的关键词
                    $reg_str.= "(".preg_quote($key).")|";
                }
            }
            if ($reg_str){
                $reg_str = mb_substr($reg_str,0,mb_strlen($reg_str)-1);
                $content = Utils::handle_preg_replace_callback("/$reg_str/",function ($matches){
                    return '<mark>'.$matches[0].'</mark>';
                },$content);
            }

        }


        if ($way == "vditor") {
            echo
            '<div class="loading-post text-center m-t-lg m-b-lg">
                 <p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...</p>
             </div>';

            echo '<div class="entry-content hide l-h-2x" id="md_handsome"></div>';

            echo '<textarea id="handsome_md_text" class="hide" >' . htmlspecialchars($content) . '</textarea>';
        } else {
            echo '<div class="entry-content l-h-2x" id="md_handsome_origin">' . $content . '</div>';
        }

        //显示文章tip
        if (@$obj->fields->tip != "") {
            $title = _mt("文章提示");

            $ret = mb_split("\|", $obj->fields->tip);
            if (count($ret) === 2) {
                $title = trim($ret[0]);
                $tip_content = trim($ret[1]);
            } else {
                $tip_content = $obj->fields->tip;
            }
            echo <<<EOF
<script>
LocalConst.TIP_TITLE = "{$title}"
LocalConst.TIP_CONTENT = "{$tip_content}"
</script>
EOF;
            return $content;

        }

        return $content;
    }


    /**
     * 输出系统原生评论必须的js，主要是用来绑定按钮的
     * @param Widget_Archive $archive
     * @param $security
     * @param $request_url
     */
    public static function outputCommentJS($archive, $security, $request_url)
    {

        $options = mget();

        $header = "";
        if ($options->commentsThreaded && $archive->is('single')) {
            $header .= "<script type=\"text/javascript\" id='outputCommentJS'>
(function () {
    window.TypechoComment = {
        dom : function (id) {
            return document.getElementById(id);
        },
    
        create : function (tag, attr) {
            var el = document.createElement(tag);
        
            for (var key in attr) {
                el.setAttribute(key, attr[key]);
            }
        
            return el;
        },

        reply : function (cid, coid) {
            var comment = this.dom(cid), parent = comment.parentNode,
                response = this.dom('" . $archive->respondId . "'), input = this.dom('comment-parent'),
                form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
                textarea = response.getElementsByTagName('textarea')[0];

            if (null == input) {
                input = this.create('input', {
                    'type' : 'hidden',
                    'name' : 'parent',
                    'id'   : 'comment-parent'
                });

                form.appendChild(input);
            }

            input.setAttribute('value', coid);

            if (null == this.dom('comment-form-place-holder')) {
                var holder = this.create('div', {
                    'id' : 'comment-form-place-holder'
                });

                response.parentNode.insertBefore(holder, response);
            }

            comment.appendChild(response);
            this.dom('cancel-comment-reply-link').style.display = '';

            if (null != textarea && 'text' == textarea.name) {
                textarea.focus();
            }

            return false;
        },

        cancelReply : function () {
            var response = this.dom('{$archive->respondId}'),
            holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');

            if (null != input) {
                input.parentNode.removeChild(input);
            }

            if (null == holder) {
                return true;
            }

            this.dom('cancel-comment-reply-link').style.display = 'none';
            holder.parentNode.insertBefore(response, holder);
            return false;
        }
    };
})();
</script>
";
        }
        if ($archive->is('single')) {
            $requestURL = $request_url;

            $requestURL = str_replace('&_pjax=%23content', '', $requestURL);
            $requestURL = str_replace('?_pjax=%23content', '', $requestURL);
            $requestURL = str_replace('_pjax=%23content', '', $requestURL);

            $requestURL = str_replace('&_pjax=%23post-comment-list', '', $requestURL);
            $requestURL = str_replace('?_pjax=%23post-comment-list', '', $requestURL);
            $requestURL = str_replace('_pjax=%23post-comment-list', '', $requestURL);
            if (Utils::endsWith($requestURL,"?")){
                $requestURL = rtrim($requestURL, "?");
            }

            $header .= "<script type=\"text/javascript\">
var registCommentEvent = function() {
    var event = document.addEventListener ? {
        add: 'addEventListener',
        focus: 'focus',
        load: 'DOMContentLoaded'
    } : {
        add: 'attachEvent',
        focus: 'onfocus',
        load: 'onload'
    };
    
    var r = document.getElementById('{$archive->respondId}');
    if (null != r) {
        var forms = r.getElementsByTagName('form');
        if (forms.length > 0) {
            var f = forms[0], textarea = f.getElementsByTagName('textarea')[0], added = false;
            var submitButton = f.querySelector('button[type=\"submit\"]');
            if (null != textarea && 'text' == textarea.name) {
                var referSet =  function () {
                    if (!added) {
//                        console.log('commentjs');
                        const child = f.querySelector('input[name=\"_\"]');
                        const child2 = f.querySelector('input[name=\"checkReferer\"]');
                        if (child!=null){
                            f.removeChild(child);                        
                        } 
                        if (child2!=null){
                            f.removeChild(child2);                        
                        } 
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_';
                            input.value = " . Typecho_Common::shuffleScriptVar(
                    $security->getToken($requestURL)) . "
                    
                        f.appendChild(input);
                        ";

            $header .= "
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'checkReferer';
                        input.value = 'false';
                        
                        f.appendChild(input);
                        ";

            $header .= "

                        added = true;
                    }
                };//end of reset
                referSet();
            }
        }
    }
};

$(function(){
    registCommentEvent();
});
</script>";
        }
        echo $header;
    }


    /**
     * 生成typecho过滤头部输出信息的标志
     * @param Widget_Archive $archive
     * @return string
     */
    public static function exportGeneratorRules($archive)
    {


        $rules = array(
            "commentReply",
        );

        if (!$archive->is('index')) {
            if ($archive->is('category')) {//是分类
                $content = $archive->getDescription();
                $content = json_decode($content, true);
                if (is_array($content) && @$content['lock'] == true) {
                    $rules[] = "description";
                }
            } else {
                //如果文章是分类加密的文章也不显示description
                $content = @$archive->categories[0]['description'];
                $content = json_decode($content, true);
                if (is_array($content) && @$content['lock'] == true) {
                    $rules[] = "description";
                    $rules[] = "keywords";
                }
            }
        }


        //$options = mget();
        //if (@in_array('isPjax', Utils::checkArray( $options->featuresetup))){
        //$rules[] = "antiSpam";
        //}
        $rules[] = "antiSpam";
        return join("&", $rules);
    }

    /*prevent music api abuse*/ static $music_api_verification="gAAAAABklr3jSErU_1QVY8u6vNyDEfFXzkdvfnHU-pKdHP-gMwMatFMdMGiDKC8ALGj62Oy3qaoSbqDAcS0_xbQ20qqUgYdvQA==";

    /**
     * 选择 最左上边的交集的地方 的颜色【1】
     * @return string
     */
    public static function slectNavbarHeader()
    {
        return '<div id="header_left" class="text-ellipsis navbar-header bg-' . self::getThemeColor()[0] . '">';
    }

    /**
     * 选择顶部的导航栏的颜色【2】
     * @return string
     */
    public static function selectNavbarCollapse()
    {
        return '<div id="header_right" class="collapse pos-rlt navbar-collapse bg-' . self::getThemeColor()[1] . '">';
    }


    /**
     * 选择左侧边栏配色【3】
     * @return string
     */
    public static function selectAsideStyle()
    {
        return '<aside id="aside" class="gpu-speed app-aside hidden-xs bg-' . self::getThemeColor()[2] . '">';
    }


    public static function getThemePresetColorArray()
    {
        return [
            ["black", "white-only", "black"],//1
            ["dark", "white-only", "dark"],//2
            ["white", "white-only", "black"],//3
            ["primary", "white-only", "dark"],//4
            ["info", "white-only", "black"],//5
            ["success", "white-only", "dark"],//6
            ["danger", "white-only", "dark"],//7
            ["black", "black", "white"],//8
            ["dark", "dark", "light"],//9
            ["info dker", "info dker", "light"],//10
            ["primary", "primary", "dark"],//11
            ["info dker", "info dk", "black"],//12
            ["success", "success", "dark"],//13
            ["danger", "danger", "dark"]//14
        ];
    }

    public static function getToolbarColor()
    {
        $options = mget();
        if (trim($options->ChromeThemeColor) != "") {
            return trim($options->ChromeThemeColor);
        }
        $colorArray = [
            "black" => "#1c2b36",
            "dark" => "#3a3f51",
            "white" => "#f9f9f9",
            "white-only" => "#f9f9f9",
            "primary" => "#7266ba",
            "info" => "#23b7e5",
            "info dk" => "#16aad8",
            "success" => "#27c24c",
            "danger" => "#f05050"
        ];
        $custom = @$options->themetypeEdit;
        if (trim($custom) != "") {
            $themeArray = explode("-", $custom);
        } else {
            $color = self::getThemePresetColorArray();
            $themeArray = $color[$options->themetype];
        }
        if (array_key_exists($themeArray[0], $colorArray)) {
            return $colorArray[$themeArray[0]];
        } else {
            return "";
        }
    }

    public static function getThemeColor()
    {
        $options = mget();
        //0-交集部分  1-导航栏 2- 左侧边栏 3-对比色 4-对比色2(暂时不用)
        $colorArray = self::getThemePresetColorArray();
        $ret = $colorArray[0];//默认值
        $custom = @$options->themetypeEdit;
        $isCustom = false;//用户自定义色调
        if (trim($custom) != "") {
            $customArray = explode("-", $custom);
            if (count($customArray) == 3) {
                $isCustom = true;
                $ret = $customArray;
                $ret[] = "danger";
            } elseif (count($customArray) == 4) {
                $isCustom = true;
                $ret = $customArray;
            }
        } else {
            $ret[] = "danger";
        }

        if (!$isCustom && $options->themetype != null) {
            $ret = $colorArray[$options->themetype];
        }
        return $ret;

    }


    /**
     * 返回时光机插入按钮的model模态框HTML代码
     * @param $modelId
     * @param $okButtonId
     * @param $title
     * @param $label
     * @param String $type 类型，img,music video 决定是否有上传按钮
     * @return string
     */
    public static function returnCrossInsertModelHtml($modelId, $okButtonId, $title, $label, $type)
    {

        $uploadHtml = "";
        $title = _mt("本地上传");
        if ($type == "img") {
            $placeholder = _mt("未选择任何文件");
            $select = _mt("选择文件");
            $uploadHtml = <<<EOF
<label class="insert_tips m-t-sm">$title</label>
     <input type="file" id="time_file" multiple name="file" class="hide">
<div class="bootstrap-filestyle input-group"><input type="text" id="file-info" class="form-control" value="$placeholder" 
disabled=""> <span 
class="group-span-filestyle input-group-btn" tabindex="0"><label for="filestyle-0" class="btn btn-primary" id="time-upload">$select</label></span></div>
EOF;

        }

        return '
 <div class="modal fade" id="' . $modelId . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <div class="modal-dialog modal-sm" role="document">
                                    <div class="modal-content box-shadow-wrap-lg">
                                        <div class="modal-header box-shadow-bottom-normal">
                                            <button type="button" class="close" data-dismiss="modal"><i style="vertical-align: bottom;" data-feather="x-circle"></i></button>
                                            <h4 class="modal-title" id="myModalLabel">' . _mt("$title") . '</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label class="insert_tips">' . _mt("$label") . '</label>
                                                        <input name="' . $modelId . '" type="text" class="form-control" >
                                                        ' . $uploadHtml . '
                                                        
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal"> ' . _mt("取消") . ' </button>
                                            <button type="button" id="' . $okButtonId . '" class="btn btn-primary">' . _mt("确定") . '</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
 ';
    }

    /**
     * 返回pjax自定义动画所需的css
     * @return string
     */
    public static function returnPjaxAnimateCss()
    {
        $options = mget();
        $css = "";
        $color = $options->progressColor;

        switch (trim($options->pjaxAnimate)) {
            case "minimal":
                $css = <<<EOF
.pace .pace-progress{
    background: {$color};
}
EOF;
                break;

            case "flash":
                $css = <<<EOF
.pace .pace-progress{
    background: {$color};
}

.pace .pace-progress-inner{
      box-shadow: 0 0 10px {$color}, 0 0 5px {$color};
}
.pace .pace-activity{
      border-top-color: {$color};
      border-left-color: {$color};
}
EOF;
                break;

            case "big-counter":
                $textColor = Utils::hex2rgb($color);
                $css = <<<EOF
.pace .pace-progress:after{
      color: rgba({$textColor}, 0.19999999999999996);
}
EOF;
                break;

            case "corner-indicator":
                $css = <<<EOF
.pace .pace-activity{
    background: {$color};
}
EOF;
                break;

            case "center-simple":
                $css = <<<EOF
.pace{
    border: 1px solid {$color};
    
}
.pace .pace-progress{
    background: {$color};
}
EOF;
                break;

            case "loading-bar":
                $css = <<<EOF
.pace .pace-progress{
      background: {$color};
      color: {$color};
}

.pace .pace-activity{
    box-shadow: inset 0 0 0 2px {$color}, inset 0 0 0 7px #FFF;
}
EOF;
                break;

            case "whiteRound":
                $css = <<<EOF
.loading {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    z-index: 123456789 !important;
    opacity: 1;
    -webkit-transition: opacity 0.3s ease;
    -moz-transition: opacity 0.3s ease;
    -ms-transition: opacity 0.3s ease;
    -o-transition: opacity 0.3s ease;
    transition: opacity 0.3s ease
}

.loading .preloader-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%)
}

.loading .loader-inner.ball-scale-multiple div {
    background-color: #000
}

.loading.loading-loaded {
    opacity: 0;
    display: none;
}

@-webkit-keyframes ball-scale-multiple {
    0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 0
    }

    5% {
        opacity: 1
    }

    100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 0
    }

}

@keyframes ball-scale-multiple {
    0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 0
    }

    5% {
        opacity: 1
    }

    100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 0
    }

}

.ball-scale-multiple {
    position: relative;
    -webkit-transform: translateY(-30px);
    transform: translateY(-30px)
}

.ball-scale-multiple>div:nth-child(2) {
    -webkit-animation-delay: -.4s;
    animation-delay: -.4s
}

.ball-scale-multiple>div:nth-child(3) {
    -webkit-animation-delay: -.2s;
    animation-delay: -.2s
}

.ball-scale-multiple>div {
    position: absolute;
    left: -30px;
    top: 0;
    opacity: 0;
    margin: 0;
    width: 60px;
    height: 60px;
    -webkit-animation: ball-scale-multiple 1s 0s linear infinite;
    animation: ball-scale-multiple 1s 0s linear infinite
}

.ball-scale-multiple>div {
    background-color: #fff;
    border-radius: 100%
}

EOF;

                break;
            case "customise":
                $css = $options->pjaxCusomterAnimateCSS;
                $css .= <<<EOF
.loading {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    z-index: 123456789 !important;
    opacity: 1;
    -webkit-transition: opacity 0.3s ease;
    -moz-transition: opacity 0.3s ease;
    -ms-transition: opacity 0.3s ease;
    -o-transition: opacity 0.3s ease;
    transition: opacity 0.3s ease
}
.loading .preloader-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
}
EOF;

                break;
        }
        return $css;
    }


    /**
     * 热门文章，按照评论数目排序
     * @param $hot
     */
    public static function returnHotPosts($hot)
    {
        $options = mget();
        $db = Typecho_Db::get();
        //echo date('Y-n-j H:i:s',time());
        Database::createFiledInTable("views", Database::$type_int_10, "contents");
        $orderType = $options->hotPostOrderType;
        if (empty($orderType)) {
            $orderType = 'commentsNum';
        } else {
            $orderType = $options->hotPostOrderType;
        }
        $sql = $db->select()->from('table.contents')
            ->where('created >= ?', 0)
            ->where('table.contents.created <= ?', time())
            ->where('type = ?', 'post')
            ->where('status =?', 'publish')
            ->limit(5)
            ->order($orderType, Typecho_Db::SORT_DESC);
        //echo $sql->__toString();
        $result = $db->fetchAll($sql);
        $index = 0;
        $isShowImage = true;
        if (count($options->indexsetup) > 0 && in_array('notShowRightSideThumb', Utils::checkArray($options->indexsetup))) {
            $isShowImage = false;
        }
        foreach ($result as $val) {
            $val = Typecho_Widget::widget('Widget_Abstract_Contents')->filter($val);
            echo '<li class="list-group-item">
                <a href="' . $val['permalink'] . '" class="pull-left thumb-sm m-r">' . self::returnRightSideImageHtml($isShowImage, $hot, $index) . '</a>
                <div class="clear">
                    <h4 class="h5 l-h text-second"> <a href="' . $val['permalink'] . '" title="' . $val['title'] . '"> ' .
                $val['title'] . ' </a></h4>
                    <small class="text-muted post-head-icon">';
            if ($options->hotPostOrderType != "views") {
                echo '<span class="meta-views"> <span class="right-small-icons"><i data-feather="message-circle"></i></span>  <span class="sr-only">评论数：</span> <span class="meta-value">' . $val['commentsNum'] . '</span>
                    </span>';
            } else {
                echo '<span class="meta-date"> <i class="fontello fontello-eye" aria-hidden="true"></i> <span class="sr-only">浏览次数:</span> <span class="meta-value">' . $val['views'] . '</span>
                    </span>
              ';
            }
            echo '</small></div></li>';
            $index++;
        }
    }

    /**
     * 随机文章，显示5篇
     * @param $random
     */
    public static function returnRandomPosts($random)
    {
        $options = mget();
        $modified = $random->modified;
        $db = Typecho_Db::get();
        $adapterName = Utils::getAdapterDriver();//兼容非MySQL数据库
        if (strtoupper($adapterName) == "PGSQL" || strtoupper($adapterName) == 'PDO_PGSQL' || strtoupper($adapterName) == 'PDO_SQLITE' || strtoupper($adapterName) == 'SQLITE') {
            $order_by = 'RANDOM()';
        } else {
            $order_by = 'RAND()';
        }
        $sql = $db->select()->from('table.contents')
            ->where('status = ?', 'publish')
            ->where('table.contents.created <= ?', time())
            ->where('type = ?', 'post')
            ->limit(5)
            ->order($order_by);

        $result = $db->fetchAll($sql);
        $index = 0;
        $isShowImage = true;
        if (count($options->indexsetup) > 0 && in_array('notShowRightSideThumb', Utils::checkArray($options->indexsetup))) {
            $isShowImage = false;
        }
        foreach ($result as $val) {
            $val = Typecho_Widget::widget('Widget_Abstract_Contents')->filter($val);
            echo '<li class="list-group-item">
                <a href="' . $val['permalink'] . '" class="pull-left thumb-sm m-r">' . self::returnRightSideImageHtml($isShowImage, $random, $index) . '</a>
                <div class="clear">
                    <h4 class="h5 l-h text-second"> <a href="' . $val['permalink'] . '" title="' . $val['title'] . '"> ' . $val['title'] . ' </a></h4>
                    <small class="text-muted post-head-icon">';
            if ($options->hotPostOrderType != "views") {
                echo '<span class="meta-views "> <span class="right-small-icons"><i data-feather="message-circle"></i></span> <span class="sr-only">评论数：</span> <span class="meta-value">' . $val['commentsNum'] . '</span>
                    </span>';
            } else {
                echo '<span class="meta-date"> <i class="fontello fontello-eye" aria-hidden="true"></i> <span class="sr-only">浏览次数:</span> <span class="meta-value">' . $val['views'] . '</span>
                    </span>
              ';
            }
            echo '</small></div></li>';
            $index++;
        }
    }

    /**
     * @param $isShowImage
     * @param $obj
     * @param $index
     * @return string
     */
    public static function returnRightSideImageHtml($isShowImage, $obj, $index)
    {
        if ($isShowImage) {
            return '<img src="'
                . Utils::returnImageSrcWithSuffix
                (showSidebarThumbnail($obj,
                    $index), null, 80, 80) . '" class="img-40px normal-shadow img-square">';
        } else {
            return "";
        }
    }


    /**
     * 返回主题的文章显示动画class
     * @param $obj
     */
    public static function returnPageAnimateClass($obj)
    {
        $options = mget();
        if (in_array('isPageAnimate', Utils::checkArray($options->featuresetup)) && ($obj->is('post') || $obj->is('page'))) {
            echo "animated fadeIn";
        } else if (in_array('isOtherAnimate', Utils::checkArray($options->featuresetup)) && !$obj->is('post') &&
            !$obj->is('page')) {
            echo "animated fadeIn";
        } else {
            echo "";
        }
    }

    public static function returnCommentList($obj, $security, $comments, $request_url)
    {
        echo '<div id="post-comment-list" class="skt-loading">';


        if ($comments->have()) {
            echo '<h4 class="comments-title m-t-lg m-b">';
            $obj->commentsNum(_mt('暂无评论'), _mt('1 条评论'), _mt('%d 条评论'));
            echo '</h4>';
            echo <<<EOF
<nav class="loading-nav text-center m-t-lg m-b-lg hide">
<p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...</p>
</nav>
EOF;
            $comments->listComments();//列举评论
            echo '<nav id="comment-navigation" class="text-center m-t-lg m-b-lg" role="navigation">';
            $comments->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>');
            echo '</nav>';
        }
        Content::outputCommentJS($obj, $security, $request_url);
        echo '</div>';

    }

    public static function parseJson2Array($json)
    {
        $json = '[' . Utils::remove_last_comma($json) . ']';
        $asideItems = json_decode($json);
        if (!is_array($asideItems)) {
            _me("<div class='wrapper-md'><b>你的格式配置错误，请仔细检测格式，比如是否存在多余或者缺少的符号</b></div>");
            return [];
        }
        return $asideItems;
    }

    public static function returnLeftItem($asideItem, $haveSub, $subListHtml)
    {

        $ret = "";
        @$itemName = $asideItem->name;
        @$itemStatus = $asideItem->status;
        @$itemLink = $asideItem->link;
        @$itemClass = $asideItem->class;
        @$itemFeather = $asideItem->feather;
        @$itemSub = $asideItem->sub;
        @$itemTarget = $asideItem->target;
        @$itemIconColor = $asideItem->icon_color;
        @$itemTextColor = $asideItem->text_color;

        $iconColor = ($itemIconColor) ? 'style="color:' . $itemIconColor . '"' : "";
        $textColor = ($itemTextColor) ? 'style="color:' . $itemTextColor . '"' : "";


        if (@$itemTarget) {
            $linkStatus = 'target="' . $itemTarget . '"';
        } else {
            $linkStatus = 'target="_self"';
        }

        $iconName = $itemClass;
        if (trim($itemFeather) != "") {
            $iconName = $itemFeather;
        }


        $right_arrow = ($haveSub) ? '<span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span>' : "";


        return '<li> <a ' . $linkStatus . ' href="' . $itemLink . '" 
class ="auto">' . $right_arrow . '<span class="nav-icon" ' . $iconColor . '>' . ScodeParse::returnIconHtml($iconName) . '</span><span ' . $textColor . '>' . _mt
            ($itemName) . '</span></a>' . $subListHtml . '</li>';
    }

    /**
     * @param $categories
     * @return string
     */
    public static function returnCategories($categories)
    {
        $html = "";
        $options = mget();
        while ($categories->next()) {
            if ($categories->levels === 0) {//父亲分类

                $children = $categories->getAllChildren($categories->mid);//获取当前父分类所有子分类
                //print_r($children);
                //var_dump(empty($children));
                if (!empty($children)) {//子分类不为空
                    $html .= '<li><a class="auto"><span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span><span>' . $categories->name . '</span></a>';
                    //循环输出子分类
                    $childCategoryHtml = '<ul class="nav nav-sub dk child-nav">';
                    foreach ($children as $mid) {
                        $child = $categories->getCategory($mid);
                        $childCategoryHtml .= '<li><a href="' . $child['permalink'] . '"><b class="badge pull-right">' . $child['count'] . '</b><span>' . $child['name'] . '</span></a></li>';
                    }
                    $childCategoryHtml .= '</ul>';

                    $html .= $childCategoryHtml;
                    $html .= "</li>";
                } else {//没有子分类
                    $html .= '<li><a href="' . $categories->permalink . '"><b class="badge pull-right">' . $categories->count . '</b><span>' . $categories->name . '</span></a></li>';
                }
            }
        }

        return $html;
    }

    public static function returnPjaxAnimateHtml()
    {
        $options = mget();
        $html = "";
        $is_hide = (Utils::getExpertValue("first_screen_animate")) ? "" : "hide";
        if ($options->pjaxAnimate == "default") {
            $html .= '<div id="loading" class="butterbar active ' . $is_hide . '">
            <span class="bar"></span>
        </div>';
        } else if ($options->pjaxAnimate == "whiteRound") {
            $html .= '<section id="loading" class="loading ' . $is_hide . '">
    <div class="preloader-inner">
        <div class="loader-inner ball-scale-multiple"><div></div><div></div><div></div></div>
    </div>
</section>';
        } else if ($options->pjaxAnimate == "customise") {
            $html .= $options->pjaxCusomterAnimateHtml;
        }
        return $html;
    }

    public static function returnTimeTab($id, $name, $url, $type, $img)
    {
        return <<<EOF
<li ><a href="#$id" role="tab" data-id="$id" data-toggle="tab" aria-expanded="false" 
data-rss="$url" data-status="false" data-type="$type" data-img="$img">$name</a></li>
EOF;
    }


    public static function returnTimeTabPane($id)
    {
        return <<<EOF
<div id="$id" class="padder fade tab-pane">
                        <nav class="loading-nav text-center m-t-lg m-b-lg">
                            <p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...</p>
                        </nav>
                        <nav class="error-nav hide text-center m-t-lg m-b-lg">
                            <p class="infinite-scroll-request"><i class="glyphicon 
                            glyphicon-refresh"></i>加载失败！尝试重新加载</p>
                        </nav>
                        <div class="streamline b-l m-l-lg m-b padder-v hide">
                            <ol class="comment-list">
                            </ol>
                        </div>
                    </div>
EOF;
    }

    public static function pageFooter($options, $obj)
    {
        if ($options->adContentPage != "") {
            $options->adContentPage();
        }
        $ifHideStar = $obj->fields->off_star == "true" || Utils::getExpertValue("off_star_page");
        $ifHideReward = !(!empty($options->featuresetup) && in_array('payforauthorinpage', Utils::checkArray($options->featuresetup)));

        echo PostContent::exportPayForAuthors($obj->cid, $ifHideStar, $ifHideReward);
    }

    public static function returnWheelHtml($content)
    {
        $json = "[" . $content . "]";
        $wheelItems = json_decode($json);
        $wheelItemsOutput = "\n" . '<div id="index-carousel" class="box-shadow-wrap-normal border-radius-6 carousel slide m-b-md" data-ride="carousel">';

        $carouselIndicators = <<<EOF
                <ol class="carousel-indicators">
EOF;
        $carouselInner = <<<EOF
                <div class="carousel-inner border-radius-6" role="listbox">
EOF;

        $index = 0;

        foreach ($wheelItems as $item) {
            @$itemTitle = $item->title;
            @$itemDesc = $item->desc;
            @$itemLink = $item->link;
            $itemCover = $item->cover;


            $insert = "";
            if ($index === 0) {
                $insert = 'active';
            }
            $carouselIndicators .= <<<EOF
<li data-target="#index-carousel" data-slide-to="$index" class="$insert"></li>
EOF;

            $carouselInner .= <<<EOF
<div class="item $insert border-radius-6">
<a href="$itemLink">
                        <img class="border-radius-6" src="$itemCover" data-holder-rendered="true">
                        <div class="carousel-caption">
                           <h3>$itemTitle</h3>
                            <p>$itemDesc</p>
                        </div>
                        </a>
                    </div>
EOF;

            $index++;

        }

        $carouselIndicators .= "</ol>";
        $carouselInner .= "</div>";

        $carouselControl = <<<EOF
<a class="left carousel-control" href="#index-carousel" role="button" data-slide="prev">
                    <svg class="glyphicon-chevron-left" viewBox="0 0 24 24" width="24" height="24" 
                    stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span class="sr-only">Previous</span>
                </a>
<a class="right carousel-control" href="#index-carousel" role="button" data-slide="next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="glyphicon-chevron-right" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span class="sr-only">Next</span>
                </a>
EOF;

        $wheelItemsOutput .= $carouselIndicators . "\n" . $carouselInner . "\n" . $carouselControl;


        $wheelItemsOutput .= "</div>";

        return $wheelItemsOutput;

    }


    /**
     * @param $type post-calendar category-radar posts-chart categories-chart tags-chart
     * @param $obj
     * @param $monthNum
     * @return array
     */
    public static function getStatisticContent($type, $obj, $monthNum = 10)
    {

        require_once __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Handsome/cache/driver/controller/cache_util.php';
        $cache = new CacheUtil();

        $object = array();

        if ($type == "post-calendar" || $type == "posts-chart") {
            //获取统计信息
            $fileContent = $cache->cacheRead("search");


            //1. 确定时间段
            //获取今天所在的月份
            $end_m = (int)date('m');
            $end_d = (int)date('d');
            $end_y = (int)date('Y');

            $start_y = $end_y;

            if ($type == "post-calendar") {
                $start_m = $end_m - $monthNum + 1;
            } else {//post-chart 只需要近5个月的数据
                $start_m = $end_m - 5;
            }
            $start_d = $end_d;
            if ($start_m < 0) {
                $start_m = $start_m + 12;
                $start_y = $start_y - 1;
            }
            //获取该时间段的每一天的日期，及其该日的文章数目。

            $start_timestamp = strtotime($start_y . "-" . $start_m . "-" . $start_d);
            $start_timestamp_const = $start_timestamp;
            $end_timestamp = strtotime($end_y . "-" . $end_m . "-" . $end_d);

            $data = array();
            $max_single = 0;// 最大每日/月的文章数目

            if ($type == "post-calendar") {

                $commentContent = $cache->cacheRead("comment");


                // 计算日期段内有多少天
                $date = array();// 保存每天日期
                while ($start_timestamp <= $end_timestamp) {
                    $temp = date('Y-m-d', $start_timestamp);
                    $date[] = $temp;
                    //搜索当日发表的文章数目
                    $temp_num = substr_count($fileContent, '"date":"' . $temp);
                    $temp_comment_num = substr_count($commentContent, $temp);
                    $temp_num = $temp_comment_num + $temp_num;

                    if ($temp_num > $max_single) {
                        $max_single = $temp_num;
                    }

                    $data[] = array(
                        $temp,
                        $temp_num
                    );
                    $start_timestamp = strtotime('+1 day', $start_timestamp);
                }

                if ($max_single > 3) {//max 最大值是3，再后面是4+
                    $max_single = 4;
                }

                $object['series'] = $data;
                $object['range'] = array(
                    $start_y . "-" . $start_m . "-" . $start_d,
                    $end_y . "-" . $end_m . "-" . $end_d
                );
                $object['categories'] = range(0, $max_single);
                $object['max'] = $max_single;
//                $object['num_range'] = range(0,$max_single);
//                if (count($object['categories']) ==5){
//                    $object['categories'][4] = 'N';
//                }

//                var_dump($object);

            } else {
                // 计算时间段内有多少个月
                $xAxis = [];
                while ($start_timestamp <= $end_timestamp) {
                    $temp = date('Y-m', $start_timestamp); // 取得递增月;
                    $temp_num = substr_count($fileContent, '"date":"' . $temp);
                    if ($temp_num > $max_single) {
                        $max_single = $temp_num;
                    }
                    $xAxis[] = $temp;
                    $data[] = $temp_num;
                    $start_timestamp = strtotime('+1 month', $start_timestamp);
                }

                $object['series'] = $data;
                $object["xAxis"] = $xAxis;
                $object['categories'] = range(0, $max_single);
            }
        }

        if ($type == "category-radar" || $type == "categories-chart" || $type == "tags-chart") {
            $object = [];
            $name = [];
            $indicator = [];
            $num = [];
            $i = -1;
            $root = false;
            while ($obj->next()) {
//                print_r("\n\n================i:".$i."================\n\n");
                //判断是否是子分类
                if ($obj->levels == 0) {
                    $i++;
//                    print_r("\nmid".$obj->mid."|count:".$obj->count."\n");
                    $name[] = $obj->name;
                    $num [] = $obj->count;
                    $root = true;
                } else {
                    //如果是子分类，则把父亲分类的 count本身给减掉
                    if ($root) {
                        $num [$i] = $obj->count;
                        $root = false;
                    } else {
                        $num [$i] += $obj->count;
                    }
//                    print_r("level:".$obj->levels."|id".$obj->mid."parent:".$obj->parent."|");
                }
            }

            if (count($num) > 0) {
                $max_single = @max($num);
            } else {
                $max_single = 0;
            }

//            print_r("count:".$i);

            for ($i = 0; $i < count($num); $i++) {
                if ($type == "category-radar") {
                    $indicator[] = (object)array(
                        "name" => $name[$i],
                        "max" => $max_single
                    );
                } else if ($type == "categories-chart") {
                    $indicator[] = (object)array(
                        "name" => $name[$i],
                        "value" => $num[$i]
                    );
                } else if ($type == "tags-chart") {
                    $indicator[] = $name[$i];
                }
            }

            if ($type == "categories-chart") {
                //保证color数组的颜色不重复
                $color = ['#6772e5', '#ff9e0f', '#fa755a', '#3ecf8e', '#82d3f4', '#ab47bc', '#525f7f', '#f51c47', '#26A69A', '#6772e5', '#ff9e0f', '#fa755a', '#3ecf8e', '#82d3f4', '#ab47bc', '#525f7f', '#f51c47', '#26A69A'];
                $cColor = [];
                for ($i = 0; $i < count($num); $i++) {
                    $cColor [] = $color[$i % count($color)];
                }

                $object["color"] = $cColor;
            }

            if ($type == "tags-chart") {
                $indicator = array_slice($indicator, 0, min(8, count($indicator)));
                $num = array_slice($num, 0, min(8, count($num)));
            }

            $object["indicator"] = $indicator;
            $object["series"] = $num;

        }
        return $object;

    }

    public static function getNoticeContent()
    {
        $options = mget();
        if ($options->blogNotice) {
            echo <<<EOF
<div class="alert bg-light lter blogNotice" style="padding: 1px">
<div class="alert-warning alert-block box-shadow-wrap-lg" style="margin: 10px 10px 10px 10px;">
                    <button type="button" class="close" data-dismiss="alert"><i data-feather="x-circle"></i></button><p><i width="16px" height="16px" style="vertical-align: -3px;margin-right: 5px" data-feather="bell" aria-hidden="true"></i>&nbsp;
                        {$options->blogNotice}</p>
</div>
</div>
EOF;
        }
    }
}


