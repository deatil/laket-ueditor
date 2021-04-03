<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor\Controller;

use Laket\Admin\Controller\Base as BaseController;
use Laket\Admin\Ueditor\Service\Upload as UploadService;

/**
 * 百度编辑器
 *
 * @create 2021-4-1
 * @author deatil
 */
class Attachment extends BaseController
{
    /**
     * 附件上传
     */
    public function upload(
        $dir = '', 
        $sizelimit = -1, 
        $extlimit = ''
    ) {
        $UploadService = (new UploadService);
        
        return $UploadService->save($dir, $sizelimit, $extlimit);
    }
}
