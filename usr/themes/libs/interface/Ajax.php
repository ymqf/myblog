<?php

/**
 * get 请求接口
 */
require_once(dirname(__DIR__)."/Utils.php");

require_once("Time.php");
require_once("Star.php");


if ($_SERVER["REQUEST_METHOD"] == "GET" && @$_GET['action'] != "open_world"){
    $options = mget();
    //如果路径包含后台管理路径，则不显示Lock.php
    if (strpos($_SERVER["SCRIPT_NAME"], __TYPECHO_ADMIN_DIR__)===false){
        $rData = Utils::isLock( $options->title,"",-1,"index");
        if ($rData["flag"]){//没有cookie认证且访问的不是管理员界面
            $_GET['data']=$rData;
            require_once(dirname(__DIR__).'/Lock.php');
            die();
        }else{
            //检查是否有mbstring扩展
            if (!function_exists("mb_split") || !function_exists("file_get_contents")){
                throw new Typecho_Exception(CDN_Config::not_support);
            }
        }
    }
}


class Ajax{
    public static function request(){
        $options = mget();
        if (strtoupper($options->language) != "AUTO") {
            I18n::setLang($options->language);
        }
        TimeMachine::getInterface();
        Star::getInterface();
        themeBackUpGet();
        staticInfoGet();
        searchGet();
        lockOpenGet();
        avatarGet();
        searchCacheGet();
    }

    public static function post(){
        TimeMachine::postInterface();
        Star::postInterface();
    }
}



function themeBackUpGet(){
    if (@$_GET['action'] == 'back_up' || @$_GET['action'] == 'un_back_up' || @$_GET['action'] == 'recover_back_up'){//备份管理

        $action = @$_GET['action'];

        if (Typecho_Widget::widget('Widget_User')->hasLogin()){
            $db = Typecho_Db::get();

            $themeName = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?', 'theme'));
            $handsomeThemeName = "theme:".$themeName['value'];
            $handsomeThemeBackupName = "theme:HandsomePro-X-Backup";


            if ($action == "back_up"){//备份数据
                $handsomeInfo=$db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeName));
                $handsomeValue = $handsomeInfo['value'];//最新的主题数据

                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))) {//如果有了，直接更新
                    $update = $db->update('table.options')->rows(array('value' => $handsomeValue))->where('name = ?', $handsomeThemeBackupName);
                    $updateRows = $db->query($update);
                    echo 1;
                }else{//没有的话，直接插入数据
                    $insert = $db->insert('table.options')
                        ->rows(array('name' => $handsomeThemeBackupName,'user' => '0','value' => $handsomeValue));
                    $db->query($insert);
                    echo 2;
                }
            }else if ($action == "un_back_up"){//删除备份
                $db = Typecho_Db::get();
                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                    $delete = $db->delete('table.options')->where ('name = ?', $handsomeThemeBackupName);
                    $deletedRows = $db->query($delete);
                    echo 1;
                }else{
                    echo -1;//备份不存在
                }
            }else if ($action == "recover_back_up"){//恢复备份
                $db = Typecho_Db::get();
                if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                    $themeInfo = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?',
                        $handsomeThemeBackupName));
                    $themeValue = $themeInfo['value'];
                    $update = $db->update('table.options')->rows(array('value'=>$themeValue))->where('name = ?', $handsomeThemeName);
                    $updateRows= $db->query($update);
                    echo 1;
                }else{
                    echo -1;//没有备份数据
                }
            }
        }else{
            echo -2;//鉴权失败
        }
        die();//只显示ajax请求内容，禁止显示博客内容
    }
}


function staticInfoGet(){
    if (@$_GET['action'] == "get_statistic"){
        header('Content-type:text/json');     //这句是重点，它告诉接收数据的对象此页面输出的是json数据；

        Typecho_Widget::widget('Widget_Metas_Category_List')->to($categorys);
        Typecho_Widget::widget('Widget_Metas_Tag_Cloud','ignoreZeroCount=1&limit=30')->to($tags);

        $object = [];

        $windowSize = @$_GET['size'];
        $monthNum = 10;
        if ($windowSize !== ""){
            if ($windowSize> 1600){
                $monthNum = 12;
            } else if ($windowSize > 1200){
                $monthNum = 10;
            }else if ($windowSize>992){
                $monthNum = 8;
            }else if ($windowSize > 600){
                $monthNum = 10;
            }
            else{
                $monthNum = 5;
            }
        }

        $post_calendar = Content::getStatisticContent("post-calendar",null,$monthNum);
        $posts_chart = Content::getStatisticContent("posts-chart",null);
        $category_radar = Content::getStatisticContent("category-radar",$categorys);
        $categories_chart = Content::getStatisticContent("categories-chart",$categorys);
        $tags_chart = Content::getStatisticContent("tags-chart",$tags);

        $object["post_calendar"] = $post_calendar;
        $object["post_chart"] = $posts_chart;
        $object["category_radar"] = $category_radar;
        $object["categories_chart"] = $categories_chart;
        $object["tags_chart"] = $tags_chart;

        echo json_encode($object);

        die();
    }
}

function search_cache_header($type){
    $time = ($type == "POST") ? Utils::getLatestTimestamp() : Utils::getLatestTimeCommentTimestamp();
    $offset = 30*60*60*24; // cache 1 month

    //强制缓存
    header("Cache-Control: max-age=$offset");
    header("Expires: ".gmdate("D, d M Y H:i:s", time() + $offset)." GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $time)." GMT");

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
        strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= intval($time)) {// 协商缓存
        Typecho_Response::getInstance()->setStatus(304);
        exit;
    }
}

function searchGet(){
    if (@$_GET['action'] == "ajax_search"){

        $isLogin = Typecho_Widget::widget('Widget_User')->hasLogin();
        search_cache_header("POST");

        header('Content-type:text/json');     //这句是重点，它告诉接收数据的对象此页面输出的是json数据；
        $thisText = @$_GET['content'];
        $object = [];
        $html = "";

        if (trim($thisText) !== ""){
            $searchResultArray = Utils::searchGetResult($thisText,$isLogin);//搜索结果

            if (count($searchResultArray) ===0){
                $html = "<li><a href=\"#\">"._mt("无相关搜索结果")."🔍</a></li>";
            }else{
                foreach ($searchResultArray as $item){
                    $html .= "<li><a href=\"".$item["path"]."\">".$item["title"]."<p class=\"text-muted\">"
                        .$item["content"]."</p></a></li>";
                }
            }
        }


        $object['results'] = $html;
        echo json_encode($object);

        die();
    }
}

function getCatePassword($slug){
    $db = Typecho_Db::get();
    $row = $db->fetchRow($db->select()->from('table.metas')
        ->where('table.metas.slug = ?', $slug)->limit(1));
    if ($row!=null){
        $cate_desc = @$row['description'];
        if ($cate_desc!=""){
            $cate_desc = json_decode($cate_desc, true);
            return @$cate_desc["password"];
        }
    }
    return "";
}

function getSinglePassword($id){
    $widget = Utils::widgetById("Contents",$id);
    $password = @$widget->fields->password;
    if ($password!=""){
        return $password;
    }
    return "";
}

function getIndexPassword(){
    return mget()->open_new_world;
}


function getLockPassword($type,$id){
    if ($id == ""){
        return "";
    }
    if ($type== "index"){
        return getIndexPassword();
    }else if ($type == "category"){
        return getCatePassword($id);

    }else if ($type == "single"){
        return getSinglePassword($id);
    }
    return "";
}


function lockOpenGet(){
    if(@$_GET['action'] == 'open_world'){
        if (!empty($_GET['password'])){
            $password = $_GET['password'];// 用户输入的密码，传过来之前进行了md5
            $type = $_GET['type'];//type:index 表示首页 category 表示分类加锁，single 表示单个页面
            $returnData = array();
            $correct_password = getLockPassword($type,$_GET['unique_id']);
            if ($correct_password === ""){ //错误逻辑
                $returnData['status'] = "-3";
                echo json_encode($returnData);
                die();
            }
            if (Utils::md5($password) == Utils::md5($correct_password)){
                $returnData['status'] = "1";
//                echo 1;//密码正确
                if ($type == "index"){
                    Typecho_Cookie::set('open_new_world', Utils::md5($password)); //保存密码的cookie，以便后面可以直接访问
                }elseif($type == "category") {
                    $category = $_GET['unique_id'];//需要加密的分类缩略名
                    Typecho_Cookie::set('category_'.$category, Utils::md5($password)); //保存密码的cookie，以便后面可以直接访问
                }elseif ($type == "single"){
                    $id = $_GET['unique_id'];//需要加密的分类缩略名
                    Typecho_Cookie::set('single_'.$id, Utils::md5($password)); //保存密码的cookie，以便后面可以直接访问
                }
            }else{
                $returnData['status'] = "-1";
//                echo -1;//密码错误
            }
        }else{
            $returnData['status'] = "-2";
//            echo -2;//信息不完成
        }
        echo json_encode($returnData);

        die();
    }
}

function avatarGet(){
    if(@$_GET['action'] == 'ajax_avatar_get') {
        $email = strtolower( $_GET['email']);
        echo Utils::getAvator($email,65);
        die();
    }
}

function searchCacheGet(){
    if (@$_GET['action'] == 'get_search_cache'){

        $type = @$_GET['type'];

        search_cache_header($type);
        header('Content-type:text/json');     //这句是重点，它告诉接收数据的对象此页面输出的是json数据；
        require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Handsome/cache/driver/controller/cache_util.php';

        $cache = new CacheUtil();
        if ($type == "POST"){
            $file = $cache->cacheRead("search");
            $isLogin = Typecho_Widget::widget('Widget_User')->hasLogin();
            if ($file !== false){
                $posts = json_decode($file,true);
                foreach ($posts as $key=>$item){
                    if (!Utils::filterSearchItem($item,$isLogin)){
                        unset($posts[$key]);
                        continue;
                    }
                    $posts[$key]["content"] = CommonContent::returnExceptShortCodeContent(trim(strip_tags($item['content'])),!$isLogin);
                }
                echo json_encode(array_values($posts));
            }else{
                echo "{}";
            }
            die();
        }else if ($type == "TIME_COMMENT"){
            $pageId = Utils::get_time_cid();
            echo Utils::get_time_comments($pageId);
            die();
        }

    }
}


