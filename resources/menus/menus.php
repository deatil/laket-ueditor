<?php

return [
    "title" => "百度编辑器",
    "url" => "laket-ueditor/upload",
    "method" => "GET",
    "slug" => $slug,
    "icon" => "icon-shiyongwendang",
    "listorder" => 1055,
    "menu_show" => 0,
    "remark" => "",
    "children" => [
        [
            "title" => "百度编辑器上传",
            "url" => "laket-ueditor/upload",
            "method" => "GET",
            "slug" => "admin.laket-ueditor.upload",
            "menu_show" => 0,
            "listorder" => 15,
        ],
        [
            "title" => "百度编辑器上传",
            "url" => "laket-ueditor/upload",
            "method" => "POST",
            "slug" => "admin.laket-ueditor.upload-post",
            "menu_show" => 0,
            "listorder" => 10,
        ],
    ],
];
