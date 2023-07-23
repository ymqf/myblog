<?php
/**
* 时光机
*
* @package custom
*/
if (!defined('__TYPECHO_ROOT_DIR__')) exit;?>
<?php
$tag = new TagUtil(false);
$base_url = Utils::get_tag_base_url();

if ($this->user->hasLogin()) {
    $action = @$_POST["action"];
    $tid = @$_POST["tid"];
    if ($action == "delete_tag") {
        $tag->deleteTag($tid);
    $this->response->redirect($base_url);
    } else if ($action == "edit_tag") {
        $value = @$_POST["edited_name"];
        if ($value){
            $tag->editTag($tid, $value);
        }
        $this->response->redirect($base_url);
    }
}
?>
<?php
$this->need('component/header.php');
function echoTimeMemoryItem($i,$month,$content,$isLogin){
    $content = Markdown::convert($content);
    $content = CommentContent::postCommentContent($content,$isLogin,"","","",true);
    $content = CommonContent::returnExceptShortCodeContent(trim(strip_tags($content)));
    echo Utils::getTimeLineItem($month." 月前",$content,"small",$i,true);
}
?>


	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->

<?php

$tag_list = $tag->getList();
$tag_ret = [];
$tag_map = array();
$tag_html = "";
foreach ($tag_list as $item) {
    $tid = $item['tid'];
    $name = $item["name"];
    $tag_html .= '<li><a data-pjax href="' . $base_url . '?tag=' . $item["tid"] . '">';
    if ($this->user->hasLogin()) {
        $tag_html .= '<span data-name="'.$name.'" data-tid="'.$tid.'" class="time_operation_click time_tag_operation hide dropdown pull-right">
    <span data-target="#" data-toggle="dropdown" role="button" aria-haspopup="true"  aria-expanded="false">
        <i data-feather="more-horizontal"></i>
    </span>
</span>';
    }
    $tag_html .= '
<b class="badge pull-right">' . $item["count"] . '</b><span class="bg-tag">' . TagContent::getTagName($tid, $item["name"]) . '</span></a></li>';
    $tag_ret[] = array("value" => $item["name"]);
    $tag_map["i_$tid"] = $item["name"];
}

$_GET["tag_map"] = $tag_map;


?>
  <!-- content -->
<!-- <div id="content" class="app-content"> -->
    <a class="off-screen-toggle hide"></a>
  	<main id="time_content" class="app-content-body <?php  Content::returnPageAnimateClass($this); ?>">
        <span id="post_category" class="hide"><a href="<?php echo BLOG_URL_PHP.'cross.html' ?>"></a></span>
        <div class="hbox hbox-auto-xs hbox-auto-sm">
            <div class="col center-part gpu-speed" id="post-panel">
                <div style="background:url(<?php $this->options->timepic(); ?>) center center; background-size:cover">
                    <div class="wrapper-lg bg-white-opacity">
                        <div class="row m-t">
                            <div class="col-sm-6">
                                <div class="clear m-b">
<!--                                    <div class="m-b m-t-sm">-->
<!--                                        <span class="h3 text-black">--><?php //$this->options->BlogName() ?><!--</span>-->
<!--                                        <small class="m-l">--><?php //$this->options->BlogJob() ?><!--</small>-->
<!--                                    </div>-->
                                    <p class="m-b social_icon">
                                        <?php
                                        $socialItemsOutput = '';
                                        $socialSingleItem = '';
                                        $socialItems = Content::parseJson2Array(Typecho_Widget::widget('Widget_Options')->socialItems);
                                        foreach ($socialItems as $socialItem){
                                            $itemName = $socialItem->name;
                                            @$itemStatus = $socialItem->status;
                                            @$itemLink = $socialItem->link;
                                            @$itemClass = $socialItem->class;
                                            if ($itemStatus == 'single'){
                                                $socialSingleItem .= '<a target="_blank" href="'.$itemLink.'" class="btn btn-sm btn-success btn-rounded">'.$itemName.'</a>';
                                            }else{
                                                $socialItemsOutput .= '<a target="_blank" title="'.$itemName.'" href="'.$itemLink.'" class="btn btn-sm btn-bg btn-rounded btn-default btn-icon">'.ScodeParse::returnIconHtml($itemClass).'</a>';
                                            }
                                        }
                                        ?>
                                        <?php echo $socialItemsOutput; ?>
                                    </p>
                                    <?php echo $socialSingleItem; ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <?php Typecho_Widget::widget('Widget_Stat')->to($stat); ?>
                                <div class="pull-right pull-none-xs text-center">
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $stat->publishedCommentsNum() ?></span>
                                        <small><?php _me("评论") ?></small>
                                    </a>
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $stat->publishedPostsNum() ?></span>
                                        <small><?php _me("文章") ?></small>
                                    </a>
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $this->commentsNum(); ?></span>
                                        <small><?php _me("动态") ?></small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wrapper bg-white">
                    <ul class="nav nav-pills nav-sm" id="time-tabs">
                        <li class="active"><a href="#talk" role="tab" data-toggle="tab" aria-expanded="true"><?php _me("我的动态") ?></a></li>
                        <?php

                        $json = '['.Typecho_Widget::widget('Widget_Options')->rssItems.']';
                        $rssItems = json_decode($json);
                        $tabPanes = "";
                        foreach ($rssItems as $rssItem) {
                            $itemId = $rssItem->id;
                            $itemUrl = $rssItem->url;
                            $itemName = $rssItem->name;
                            @$itemType = $rssItem->type;
                            @$itemImg = $rssItem->img;
                            echo Content::returnTimeTab($itemId,$itemName,$itemUrl,$itemType,$itemImg);
                            $tabPanes .= Content::returnTimeTabPane($itemId);
                        }
                        ?>
                    </ul>
                </div>

                <div class="tab-content">
                    <div id="talk" class="padder tab-pane  fade in active">
                        <?php $this->need('component/say.php') ?>
                    </div><!--end of #pedder-->

                    <?php echo $tabPanes; ?>
                </div>
                <?php echo WidgetContent::returnRightTriggerHtml() ?>
            </div><!--end of .center-part -->
            <div class="col w-lg bg-light lter bg-auto" id="rightAside">
                <div class="wrapper">
                    <div class="padder-md">
                        <div class="m-b-xs text-md"><?php _me("联系方式") ?></div>
                        <ul class="list-group no-bg no-borders pull-in">
                            <?php
                            $contactItemsOutput = '';
                            $contactItems = Content::parseJson2Array(Typecho_Widget::widget('Widget_Options')->contactItems);
                            foreach ($contactItems as $contactItem){
                                $itemName = $contactItem->name;
                                $itemImg = $contactItem->img;
                                $itemValue = $contactItem->value;
                                $itemLink = $contactItem->link;

                                $contactItemsOutput .= '<li class="list-group-item"><a target="_blank" href="'.$itemLink.'" class="pull-left thumb-sm avatar m-r"><img 
src="'.$itemImg.'" class="img-40px photo img-square normal-shadow"><i class="on b-white bottom"></i></a><div class="clear"><div><a target="_blank" href="'.$itemLink.'">'.$itemName.'</a></div><small class="text-muted">'.$itemValue.'</small></div></li>';
                            }
                            ?>
                            <?php echo $contactItemsOutput; ?>
                        </ul>
                    </div>

                    <div class="panel box-shadow-wrap-normal">
                        <h4 class="font-thin padder"><?php _me("关于我") ?></h4>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <p><?php $this->options->about() ?></p>
                            </li>
                        </ul>
                    </div>

                    <nav id="tag_list" ui-nav="" class="navi not-fold clearfix bg-white box-shadow-wrap-normal panel">
                        <ul class="nav">

                            <li class="active">
                                <a class="auto tag_list_title" no-fold="">
                  <span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span>
                                    <span class="m-b-xs text-md"><?php _me("全部标签"); ?></span>
                                </a>
                                <ul class="nav nav-sub dk">
                                <?php
                                echo $tag_html;
                                $tag_ret = json_encode($tag_ret,true);
                                if (Typecho_Widget::widget('Widget_User')->hasLogin()){
                                    echo '<script>LocalConst.TAG_LIST = \''.$tag_ret.'\';</script>';
                                }else{
                                    echo '<script>LocalConst.TAG_LIST = \'[{"value":"未登录用户无权限插入标签"}]\'</script>';
                                }
                                ?>
                                </ul>
                            </li>
                        </ul>
                    </nav>

                    <?php if ($this->options->timeHistory == 1): ?>
                    <div class="padder-md">
                        <!-- streamline -->
                        <div class="m-b text-md"><?php _me("那年今日"); ?></div>
                        <div class="streamline b-l m-b">
                            <?php
                            require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/cache/driver/controller/cache_util.php';

                            function updateBigMemory($db,$options,$login){
                                $year = date("Y");
                                $month = date("m");
                                $day = date("d");

                                $tempYear = $year;
                                $tempMonth = $month;
                                $tempDay = $day;
                                $timeList = [];


                                $pageId = Utils::get_time_cid();

                                for ($i = 1; $i <= 20; $i++) {
                                    $tempMonth = $tempMonth - 1;
                                    if ($tempMonth == 0) {
                                        $tempMonth = 12;
                                        $tempYear = $tempYear - 1;
                                    }

                                    $begin =  strtotime($tempYear . "-" . $tempMonth . "-" . $tempDay . " 00:00:00");
                                    $end =  strtotime($tempYear . "-" . $tempMonth . "-" . $tempDay . " 23:59:59");

                                    //查询数据



                                    $comments = $db->fetchAll($db->select()->from('table.comments')
                                        ->where('table.comments.status = ?', 'approved')
                                        ->where('table.comments.created <= ?', $end)
                                        ->where('table.comments.created >= ?', $begin)
                                        ->where('table.comments.type = ?', 'comment')
                                        ->where('table.comments.authorId != ?', 0) //过滤游客评论
                                        ->where('table.comments.cid = ?', $pageId)
                                        ->order('table.comments.created', Typecho_Db::SORT_DESC)
                                        ->limit(1));
                                    if (!empty($comments[0])){
                                        $tempArray = [];
                                        $tempArray['content'] = $comments[0]['text'];
                                        $tempArray['month'] = $i;
                                        $timeList[] = $tempArray;
                                    }

                                    $cache = new CacheUtil();
                                    $expire = strtotime(date('Y-m-d').'23:59:59');#到期时间戳
                                    $cache->cacheWrite("cross",json_encode($timeList),$expire-time(),"cross",false,true);
                                }
                                return json_encode($timeList);
                            }

                            $cache = new CacheUtil();
                            $contents = $cache->cacheRead("cross");
                            if (!$contents){
                                $contents = updateBigMemory($this->db,$this->options,$this->user->hasLogin());
                            }


                            $data = Utils::json_decode($contents);

                            for ($i = 1; $i <= count($data);$i++){
                                echoTimeMemoryItem($i,$data[$i-1]->month,$data[$i-1]->content,$this->user->hasLogin());
                            }

                            if (count($data) == 0){
                                echoTimeMemoryItem(2,"某",_mt("大家就当无事发生过"),$this->user->hasLogin());
                            }

                            ?>
                        </div>
                        <!-- / streamline -->
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
	</main>

<script>

    var timeTemple = '<div class="m-l-n-md">\n' +
        '          <a class="pull-left thumb-sm avatar">\n' +
        '            <img class="img-square" src="{IMG_AVATAR}">\n' +
        '          </a>          \n' +
        '          <div class="time-machine m-l-xxl panel">\n' +
        '            <div class="panel-heading pos-rlt">\n' +
        '              <span class="arrow left pull-up"></span>\n' +
        '              <span class="text-muted m-l-sm pull-right">\n' +
        '                {TIME}\n' +
        '              </span>\n' +
        '              {CONTENT}</div><div class="text-muted say_footer panel-footer">\n' +
        '                <a target="_blank" href="{LINK}" class="text-muted m-xs"><i class="iconfont icon-redo"></i>&nbsp;&nbsp;查看全文</a>\n' +
        '              </div>' +
        '          </div>' +
        '        </div>';

    $('#time-tabs').find('a').click(function (e) {
        var object = $(this);
        var rss = $(this).data("rss");
        var id = $(this).data("id");
        var flag = $(this).attr("data-status");
        var type = $(this).data("type");
        var img = $(this).data("img");
        // console.log(flag);
        // console.log(rss);
        if ('undefined' !== rss && 'undefined' !== id && flag === "false"){
            //动态加载内容
            handsome_util.addScript(LocalConst.BASE_SCRIPT_URL + "assets/js/features/jFeed.min.js","feed_js",function () {
                $.getFeed({
                    url: rss,
                    success: function(feed){
                        $.each(feed.items,function(i,item){

                            var date = new Date(Date.parse(item.updated));
                            Y = date.getFullYear() + '-';
                            M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                            D = date.getDate() + ' ';
                            h = date.getHours() + ':';
                            m = date.getMinutes();
                            date=Y+M+D+h+m;
                            var itemContent="";
                            if (type!==""){
                                if (type === "title"){
                                    itemContent = item.title;
                                }else if (type === "description"){
                                    itemContent = item.description;
                                }else {
                                    itemContent = item.description;
                                }
                            }else {
                                itemContent = item.description;
                            }
                            if (img ===""){
                                img = "<?php $this->options->BlogPic(); ?>";
                            }
                            if (date === "NaN-NaN-NaN NaN:NaN"){
                                date = "";
                            }
                            var content=timeTemple.
                            replace("{TIME}",date).
                            replace("{CONTENT}",itemContent).
                            replace("{LINK}",item.link).
                            replace("{IMG_AVATAR}",img);


                            $("#"+id).find(".comment-list").append(content);
                            $("#"+id).find(".streamline").removeClass("hide");
                            $("#"+id).find(".loading-nav").addClass("hide");
                            object.attr("data-status","true");

                            /*lightGallery  */
                            handsome_content.setFancyBox();

                        });
                    },
                    error: function (feed) {
                        $("#"+id).find(".loading-nav").addClass("hide");
                        $("#"+id).find(".error-nav").removeClass("hide");
                    }
                });
            });
        }
    });
    $("#time-upload").bind("click",function () {
        $("#time_file").trigger("click");

    });

    /*监听文件上传框*/
    $("#time_file").bind("change",function () {
        if (!$(this).val()) {
            $("#file-info").html("没有选择文件");
            return;
        }


        var input = $('#time_file');
        // 相当于： $input[0].files, $input.get(0).files
        var files = input.prop('files');
        // console.log(files);
        //判断文件类型
        if (files[0].type!=="image/jpeg" && files[0].type!=="image/png" && files[0].type!=="image/gif"){
            $("#file-info").val("错误的文件类型！" + files[0].type);
            return;
        }
        var suffix = "." + files[0].type.slice(6);
        // console.log(suffix);
        //开始上传文件
        var file = files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            var data = e.target.result;//base64 加密后的图片数据
            var content = new FormData();
            content.append("action","upload_img");
            content.append("file",data);
            content.append("suffix",suffix);


            $.ajax({
                url: "?action=upload_img",
                type: 'post',
                data: content,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    data = JSON.parse(data);
                    $("#time-upload").text("选择文件");
                    $("#time-upload").attr("disabled",false);
                    $("input[ name='imageInsertModal']").val(data.data);//插入返回的图片地址

                }, error: function (jqXHR, textStatus, errorThrown) {
                    $("#time-upload").attr("disabled",false);
                    $("#time-upload").text("选择文件");
                    $("#file-info").val($("#file-info").val() + "上传失败" + textStatus);
                }
            });
        };

        // data.append('data', "2333");
        $("#file-info").val(files[0].name);
        $("#time-upload").text("正在上传");
        $("#time-upload").attr("disabled",true);
        reader.readAsDataURL(file);
    })

</script>

    <!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->
