<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor;

use think\facade\Console;
use think\facade\Event;

use Laket\Admin\Flash\Menu;
use Laket\Admin\Facade\Flash;
use Laket\Admin\Flash\Service as BaseService;
use Laket\Admin\Event as AdminEvent;
use Laket\Admin\Settings\Event as SettingsEvent;
use Laket\Admin\Ueditor\Listener as UeditorListener;

class Service extends BaseService
{
    /**
     * composer
     */
    public $composer = __DIR__ . '/../composer.json';
    
    /**
     * 设置
     */
    public $setting = __DIR__ . '/../resources/config/setting.php';
    
    protected $slug = 'laket-admin.flash.ueditor';
    
    /**
     * 启动
     */
    public function boot()
    {
        Flash::extend('laket/laket-ueditor', __CLASS__);
    }
    
    /**
     * 开始，只有启用后加载
     */
    public function start()
    {
        // 路由
        $this->loadRoutesFrom(__DIR__ . '/../resources/routes/admin.php');
        
        // 引入函数
        $this->loadFilesFrom(__DIR__ . "/helper.php");
        
        // 绑定事件
        $this->loadEvent();
    }
    
    protected function getInputItemJS($item)
    {
        $html = '
        <script type="text/javascript" src="laket-ueditor/ueditor/ueditor.config.js"></script>
        <script type="text/javascript" src="laket-ueditor/ueditor/ueditor.all.js"></script>
        <script type="text/javascript">
        var laket_ueditor = {
            "upload_url": "'.laket_route("admin.laket-ueditor.upload").'?dir=images"
        };
        </script>
        <script type="text/javascript" src="laket-ueditor/ueditor.js"></script>
        ';
        
        return $html;
    }
    
    protected function getInputItem($item)
    {
        $html = '';
        
        if (strtolower($item['type']) == 'ueditor') {
            $html .= '
            <div class="layui-form-item layui-form-text">
                <label class="">
                    '.$item['title'];
            
            if (isset($item['ifrequire']) && $item['ifrequire']) {
                $html .= '&nbsp;<font color="red">*</font>';
            }
            
            $html .= '
                </label>
                <div class="layui-form-field-label">
                    <script type="text/plain" class="js-ueditor" id="'.$item['name'].'" name="item['.$item['name'].']">'.$item['value'].'</script>
                </div>';
                
             if ($item['remark']) {
                $html .= '
                    <div class="layui-form-mid layui-word-aux">
                        '.$item['remark'].'
                    </div>
                ';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * 事件绑定
     */
    protected function loadEvent()
    {
        // 系统闪存插件设置
        $this->app->event->listen('laket_admin_input_item_js_before', function($item) {
            return $this->getInputItemJS($item);
        });
        
        $this->app->event->listen('laket_admin_input_item', function($item) {
            return $this->getInputItem($item);
        });
        
        // 设置闪存插件
        $this->app->event->listen('laket_settings_input_item_js_before', function($item) {
            return $this->getInputItemJS($item);
        });
        
        $this->app->event->listen('laket_settings_input_item', function($item) {
            return $this->getInputItem($item);
        });
        
        // 事件
        if (class_exists(SettingsEvent\ConfigModelGetFieldType::class)) {
            Event::listen(
                SettingsEvent\ConfigModelGetFieldType::class, 
                UeditorListener\ConfigModelGetFieldType::class
            );
        }
        
        if (class_exists(SettingsEvent\ConfigModelGetConfigs::class)) {
            Event::listen(
                SettingsEvent\ConfigModelGetConfigs::class, 
                UeditorListener\ConfigModelGetConfigs::class
            );
        }
        
        // 系统闪存插件
        Event::listen(
            AdminEvent\FlashModelGetConfigs::class, 
            UeditorListener\FlashModelGetConfigs::class
        );
    }
    
    /**
     * 安装后
     */
    public function install()
    {
        $menus = include __DIR__ . '/../resources/menus/menus.php';
        
        // 添加菜单
        Menu::create($menus);
        
        // 推送静态文件
        $this->publishes([
            __DIR__ . '/../resources/assets/' => public_path() . 'static/laket-ueditor/',
        ], 'laket-ueditor-assets');
        
        Console::call('laket-admin:publish', [
            '--tag=laket-ueditor-assets',
            '--force',
        ]);
    }
    
    /**
     * 卸载后
     */
    public function uninstall()
    {
        Menu::delete($this->slug);
    }
    
    /**
     * 更新后
     */
    public function upgrade()
    {}
    
    /**
     * 启用后
     */
    public function enable()
    {
        Menu::enable($this->slug);
    }
    
    /**
     * 禁用后
     */
    public function disable()
    {
        Menu::disable($this->slug);
    }
    
}
