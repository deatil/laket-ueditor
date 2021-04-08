<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor\Service;

use Laket\Admin\Model\Attachment as AttachmentModel;

/**
 * 附件上传处理类
 *
 * @create 2021-4-1
 * @author deatil
 */
class Upload
{
    public $request = null;
    
    //编辑器初始配置
    private $confing = [
        /* 上传图片配置项 */
        "imageActionName" => "uploadimage", /* 执行上传图片的action名称 */
        "imageFieldName" => "upfile", /* 提交的图片表单名称 */
        "imageMaxSize" => 2048000, /* 上传大小限制，单位B */
        "imageAllowFiles" => [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
        "imageCompressEnable" => true, /* 是否压缩图片,默认是true */
        "imageCompressBorder" => 1600, /* 图片压缩最长边限制 */
        "imageInsertAlign" => "none", /* 插入的图片浮动方式 */
        "imageUrlPrefix" => "", /* 图片访问路径前缀 */
        'imagePathFormat' => '',
        /* 涂鸦图片上传配置项 */
        "scrawlActionName" => "uploadscrawl", /* 执行上传涂鸦的action名称 */
        "scrawlFieldName" => "upfile", /* 提交的图片表单名称 */
        'scrawlPathFormat' => '',
        "scrawlMaxSize" => 2048000, /* 上传大小限制，单位B */
        'scrawlUrlPrefix' => '',
        'scrawlInsertAlign' => 'none',
        /* 截图工具上传 */
        "snapscreenActionName" => "uploadimage", /* 执行上传截图的action名称 */
        'snapscreenPathFormat' => '',
        'snapscreenUrlPrefix' => '',
        'snapscreenInsertAlign' => 'none',
        /* 抓取远程图片配置 */
        'catcherLocalDomain' => ['127.0.0.1', 'localhost', 'img.baidu.com'],
        "catcherActionName" => "catchimage", /* 执行抓取远程图片的action名称 */
        'catcherFieldName' => 'source',
        'catcherPathFormat' => '',
        'catcherUrlPrefix' => '',
        'catcherMaxSize' => 0,
        'catcherAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],
        /* 上传视频配置 */
        "videoActionName" => "uploadvideo", /* 执行上传视频的action名称 */
        "videoFieldName" => "upfile", /* 提交的视频表单名称 */
        'videoPathFormat' => '',
        'videoUrlPrefix' => '',
        'videoMaxSize' => 0,
        'videoAllowFiles' => [".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg", ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"],
        /* 上传文件配置 */
        "fileActionName" => "uploadfile", /* controller里,执行上传视频的action名称 */
        'fileFieldName' => 'upfile',
        'filePathFormat' => '',
        'fileUrlPrefix' => '',
        'fileMaxSize' => 0,
        'fileAllowFiles' => [".flv", ".swf"],
        /* 列出指定目录下的图片 */
        "imageManagerActionName" => "listimage", /* 执行图片管理的action名称 */
        'imageManagerListPath' => '',
        'imageManagerListSize' => 20,
        'imageManagerUrlPrefix' => '',
        'imageManagerInsertAlign' => 'none',
        'imageManagerAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],
        /* 列出指定目录下的文件 */
        "fileManagerActionName" => "listfile", /* 执行文件管理的action名称 */
        'fileManagerListPath' => '',
        'fileManagerUrlPrefix' => '',
        'fileManagerListSize' => '',
        'fileManagerAllowFiles' => [".flv", ".swf"],
    ];

    public function __construct()
    {        
        $this->request = request();
    }

    public function save($dir = '') 
    {
        if ($dir == '') {
            return json([
                'state' => '没有指定上传目录',
            ]);
        }
        
        return $this->ueditor();
    }

    private function ueditor()
    {
        $action = $this->request->get('action');
        switch ($action) {
            /* 获取配置信息 */
            case 'config':
                $result = $this->confing;
                break;
            /* 上传图片 */
            case 'uploadimage':
                return $this->saveFile('images', 'ueditor');
                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
                return $this->saveFile('images', 'ueditor_scrawl');
                break;
            /* 上传视频 */
            case 'uploadvideo':
                return $this->saveFile('videos', 'ueditor');
                break;
            /* 上传附件 */
            case 'uploadfile':
                return $this->saveFile('files', 'ueditor');
                break;
            /* 列出图片 */
            case 'listimage':
                return $this->showFileList('listimage');
                break;

            /* 列出附件 */
            case 'listfile':
                return $this->showFileList('listfile');
                break;
            default:
                $result = [
                    'state' => '请求地址出错',
                ];
                break;
        }
        
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                return json(['state' => 'callback参数不合法']);
            }
        } else {
            return json($result);
        }
    }

    /**
     * 保存附件
     * @param string $dir 附件存放的目录
     * @param string $from 来源
     * @return string|\think\response\Json
     */
    protected function saveFile($dir = '', $from = '') 
    {
        if (!function_exists("finfo_open")) {
            return json([
                'state' => '检测到环境未开启php_fileinfo拓展',
            ]);
        }
        
        // 附件大小限制
        $size_limit = $dir == 'images' 
            ? laket_ueditor_config('upload_image_size') 
            : laket_ueditor_config('upload_file_size');
        
        $size_limit = $size_limit * 1024;
        
        // 附件类型限制
        $ext_limit = $dir == 'images' 
            ? laket_ueditor_config('upload_image_ext') 
            : laket_ueditor_config('upload_file_ext');

        // 获取附件数据
        $file = $this->request->file('upfile');
        if ($file == null) {
            return json([
                'state' => '获取不到文件信息'
            ]);
        }

        // 判断附件是否已存在
        $file_exists = AttachmentModel::where([
            'md5' => $file->hash('md5'),
        ])->find();
        if ($file_exists) {
            $file_path = AttachmentModel::objectUrl($file_exists['path']);
            
            AttachmentModel::where([
                'md5' => $file->hash('md5'),
            ])->data([
                'update_time' => time(),
            ])->update();
            
            return json([
                "state" => "SUCCESS", // 上传状态，上传成功时必须返回"SUCCESS"
                "url" => $file_path, // 返回的地址
                "title" => $file_exists['name'], // 附件名
            ]);
        }

        // 判断附件大小是否超过限制
        if ($size_limit > 0 && ($file->getSize() > $size_limit)) {
            return json([
                'state' => '附件过大'
            ]);
        }
        
        // 判断附件格式是否符合
        $file_name = $file->getOriginalName();
        $file_ext = strtolower(substr($file_name, strrpos($file_name, '.') + 1));
        $error_msg = '';
        if (empty($ext_limit)) {
            return json([
                'state' => '获取文件后缀限制信息失败！',
            ]);
        }
        
        $ext_limit = preg_split('/[,，]/', $ext_limit);
        
        try {
            $fileMine = $file->getMime();
        } catch (\Exception $ex) {
            return json([
                'state' => $ex->getMessage(),
            ]);
        }
        
        if ($fileMine == 'text/x-php' || $fileMine == 'text/html') {
            $error_msg = '禁止上传非法文件！';
        }
        if (preg_grep("/php/i", $ext_limit)) {
            $error_msg = '禁止上传非法文件！';
        }
        if (!preg_grep("/$file_ext/i", $ext_limit)) {
            $error_msg = '附件类型不正确！';
        }

        if (!in_array($file_ext, $ext_limit)) {
            $error_msg = '附件类型不正确！';
        }
        if ($error_msg != '') {
            return json([
                'state' => $error_msg
            ]);
        }
        
        // 移动到框架应用根目录指定目录下
        $savename = AttachmentModel::filesystem()
            ->putFile('images', $file);
        if ($savename) {
            // 获取附件信息
            $file_info = [
                'type' => 'admin',
                'type_id' => env('admin_id'),
                'name' => $file->getOriginalName(),
                'mime' => $file->getOriginalMime(),
                'path' => $savename,
                'ext' => $file->getOriginalExtension(),
                'size' => $file->getSize(),
                'md5' => $file->hash('md5'),
                'sha1' => $file->hash('sha1'),
                'driver' => AttachmentModel::getFilesystemDefaultDisk(),
                'status' => 1,
            ];
            if ($file_add = AttachmentModel::create($file_info)) {
                $url = AttachmentModel::objectUrl($file_info['path']);
                return json([
                    "state" => "SUCCESS", // 上传状态，上传成功时必须返回"SUCCESS"
                    "url" => $url, // 返回的地址
                    "title" => $file_info['name'], // 附件名
                ]);
            } else {
                return json(['state' => '上传失败']);
            }
        } else {
            return json(['state' => '上传失败']);
        }
    }

    /**
     * @param string $type 类型
     * @param $config
     * @return \think\response\Json
     */
    protected function showFileList($type = '')
    {
        /* 获取参数 */
        $size = input('get.size/d', 0);
        $start = input('get.start/d', 0);
        $allowExit = input('get.exit', '');
        if ($size == 0) {
            $size = 20;
        }
        
        /* 判断类型 */
        switch ($type) {
            /* 列出附件 */
            case 'listfile':
                $allowExit = '' == $allowExit 
                    ? laket_ueditor_config('upload_file_ext') 
                    : $allowExit;
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowExit = ('' == $allowExit) 
                    ? laket_ueditor_config('upload_image_ext') 
                    : $allowExit;
        }
        
        if (! empty($allowExit)) {
            $allowExitWhere = [
                ['ext', 'in', explode(',', $allowExit)],
            ];
        } else {
            $allowExitWhere = '';
        }

        /* 获取附件列表 */
        $filelist = AttachmentModel::order('id desc')
            ->where($allowExitWhere)
            ->where('status', 1)
            ->limit($start, $size)
            ->column('id,path,create_time,name,size');
        if (empty($filelist)) {
            return json([
                "state" => "没有找到附件",
                "list" => [],
                "start" => $start,
                "total" => 0
            ]);
        }
        
        $list = [];
        $i = 0;
        foreach ($filelist as $value) {
            $list[$i]['id'] = $value['id'];
            $list[$i]['url'] = AttachmentModel::objectUrl($value['path']);
            $list[$i]['name'] = $value['name'];
            $list[$i]['size'] = $this->byteFormat((float) $value['size']);
            $list[$i]['mtime'] = $value['create_time'];
            $i++;
        }

        /* 返回数据 */
        $result = [
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => AttachmentModel::where('ext', 'in', $allowExit)->count(),
        ];
        return json($result);

    }
    
    /**
     * 计算文件大小
     */
    public function byteFormat($bytes)
    {
        $sizeText = [" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"];
        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizeText[$i];
    }

}
