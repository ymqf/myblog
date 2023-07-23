<?php
/**
 * 多种不同的文章内容，目前支持相册和普通文章两种，有计划支持视频类型
 */

class TypePost{

    /**
     * @param $content
     * @param $obj
     * @return string
     */
    public static function postImagePost($content, $obj)
    {
        if ($obj->hidden === true) {//输入密码访问
            return $content;
        } else {
            return TypePost::parseContentToImage($content, "album");
        }
    }

    /**
     * 解析文章内容为图片列表（相册）
     * @param $content
     * @param $type
     * @return string
     */
    public static function parseContentToImage($content, $type)
    {
        preg_match_all('/<img.*?src="(.*?)"(.*?)(alt="(.*?)")??(.*?)\/?>/', $content, $matches);

        if (is_array($matches) && count($matches[0]) > 0) {

            $html = "";
            if ($type === "photos") {//自适应拉伸的
                $html .= "<div class='album-photos'>";
            } else {//统一宽度排列
                $html .= "<div class='photos'>";
            }
            for ($i = 0; $i < count($matches[0]); $i++) {
                $info = trim($matches[5][$i]);
                preg_match('/alt="(.*?)"/', $info, $info);
                if (is_array($info) && count($info) >= 2) {
//                        print_r($info);
                    $info = @$info[1];
                } else {
                    $info = "";
                }
                if ($type == "photos") {
                    $html .= <<<EOF
<figure>
        {$matches[0][$i]}
        <figcaption>{$info}</figcaption>
</figure>
EOF;
                } else {
                    $html .= <<<EOF
<figure class="image-thumb" itemprop="associatedMedia" itemscope="" itemtype="http://schema.org/ImageObject">
          {$matches[0][$i]}
          <figcaption itemprop="caption description">{$info}</figcaption>
      </figure>
EOF;
                }
            }

            $html .= "</div>";

            return $html;
        } else {
            //解析失败，就不解析，交给前端进行解析，还原之前的短代码
            $type = ($type == "photos") ? ' type="photos"' : "";
            return "<div class='album_block'>\n\n[album" . $type . "]\n" . $content . "[/album] </div>";
        }


    }
}
