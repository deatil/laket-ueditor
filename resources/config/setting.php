<?php

// 设置
return [
    [ 
        'name' => "upload_image_size", 
        'title' => '图片上传大小限制', 
        'type' => 'text', 
        'value' => '0', 
        'remark' => '0为不限制大小，单位：kb',
    ],
    [
        'name' => "upload_image_ext", 
        'title' => '允许上传的图片后缀',
        'type' => 'text',
        'value' => 'gif,jpg,jpeg,bmp,png',
        'remark' => '多个后缀用逗号隔开，不填写则不限制类型',
    ],
    [
        'name' => "upload_file_size", 
        'title' => '文件上传大小限制',
        'type' => 'text',
        'value' => '0',
        'remark' => '0为不限制大小，单位：kb',
    ],
    [
        'name' => "upload_file_ext", 
        'title' => '允许上传的文件后缀',
        'type' => 'text',
        'value' => 'doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip,gz,bz2,7z',
        'remark' => '多个后缀用逗号隔开，不填写则不限制类型',
    ],
];
