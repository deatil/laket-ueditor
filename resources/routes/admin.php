<?php

use think\facade\Route;
use Laket\Admin\Facade\Flash;
use Laket\Admin\Ueditor\Controller\Attachment;

/**
 * 路由
 */
Flash::routes(function() {
    Route::get('laket-ueditor/upload', Attachment::class . '@upload')->name('admin.laket-ueditor.upload');
    Route::post('laket-ueditor/upload', Attachment::class . '@upload')->name('admin.laket-ueditor.upload-post');
});