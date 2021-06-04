<?php

/**
 * 抓取WordPress文章转为hexo
 * Author iVampireSP.com
 * Blog iVampireSP.com
 */

# Hexo 目录
$hexo_path = "contents";
if (!file_exists($hexo_path)) {
    mkdir($hexo_path);
}
/* 站点列表 */
$site_list = json_decode(file_get_contents('sites.json'), true);

echo '本次要抓取' . count($site_list) . "个站点。\n";
foreach ($site_list as $site_list) {
    echo "正在抓取: $site_list ...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://$site_list/wp-json/wp/v2/posts?per_page=100&page=1");
    // 不要http header 加快效率
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch); //已经获取到内容,没有输出到页面上.
    curl_close($ch);

    $json = json_decode($res, true);
    foreach ($json as $array) {
        $title = $array['title']['rendered'];
        $date = str_replace("T", " ", $array['date']);
        $content = $array['content']['rendered'];
        $link = $array['link'];
        $filename = $site_list . '-' . htmlspecialchars($title) . '.md';
        $write_content = <<<EOF
---
title: "$title 由 $site_list"
date: $date
tags: $site_list
---
$content

**该文章来自[$link]($link)**

**原作者同意后，MemoryArt将会拉取文章，但是请不要刻意的爬取本站。**
EOF;
        echo <<<EOF
-------------------------------------
             操作文件
    $site_list  -> $title
                ↓
$filename \n
EOF;
        file_put_contents("$hexo_path/$filename", $write_content);
    }
}

/* 全部完成后开始部署 */
echo "#####################################\n抓取写入完成... \n";
// exec("cd $hexo_path && hexo g");
echo "全部完成！\n";