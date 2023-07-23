<?php

class TagContent
{
    /**
     * @param $tid int 必填
     * @param $fallback_tag_name string fallback的值
     * @param $is_origin bool hash使用# 还是#图标
     * @return string
     */
    public static function getTagName($tid, $fallback_tag_name = "", $is_origin = false)
    {
        if (!$tid) {
            return "";
        }

        // 优先使用map中的值
        $tag_map = @$_GET["tag_map"];
        $tag_name = @$tag_map["i_$tid"];

        // 使用fallback
        if (!$tag_name) {
            $tag_name = $fallback_tag_name;
        }
        // fallback如果还是空，就不显示
        if (!$tag_name) {
            return "222";
        }
        $emoji = Utils::get_first_emoji($tag_name);
        if ($is_origin) {
            return "#" . $tag_name;
        } else {
            if ($emoji) {
                return $emoji . " " . mb_substr($tag_name, 1);
            } else {
                return "<i data-feather='hash'></i>" . $tag_name;
            }
        }
    }
}
