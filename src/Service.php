<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor;

use think\facade\Console;

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
    
    /**
     * 包名
     */
    protected $pkg = 'laket/laket-ueditor';
    
    /**
     * 权限菜单 slug
     */
    protected $slug = 'laket-admin.flash.ueditor';
    
    /**
     * 启动
     */
    public function boot()
    {
        Flash::extend('laket/laket-ueditor', __CLASS__);
    }
    
    /**
     * 在插件安装、插件卸载等操作时有效
     */
    public function action()
    {
        register_install_hook($this->pkg, [$this, 'install']);
        register_uninstall_hook($this->pkg, [$this, 'uninstall']);
        register_upgrade_hook($this->pkg, [$this, 'upgrade']);
        register_enable_hook($this->pkg, [$this, 'enable']);
        register_disable_hook($this->pkg, [$this, 'disable']);
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

    /**
     * 事件绑定
     */
    protected function loadEvent()
    {
        // 通用事件
        add_action('ueditor_js', function() {
            echo $this->getInputItemJS();
        });
        
        add_action('ueditor_init', function($item) {
            echo laket_ueditor_bind("js-ueditor");
        });
        
        // 系统插件设置
        add_action('laket_admin_input_item_js_before', function() {
            echo $this->getInputItemJS();
        });
        
        add_action('laket_admin_input_item', function($item) {
            echo $this->getInputItem($item);
        });
        
        // 设置
        add_action('laket_settings_input_item_js_before', function() {
            echo $this->getInputItemJS();
        });
        
        add_action('laket_settings_input_item', function($item) {
            echo $this->getInputItem($item);
        });
        
        // CMS
        add_action('cms_input_item_js', function() {
            echo $this->getInputItemJS();
        });
        
        add_action('cms_input_item_editor', function($item) {
            echo laket_ueditor_bind("js-ueditor");
        });
        
        // 事件
        add_filter('config_model_get_field_type', function($fieldType) {
            $fieldType[] = [
                "name" => "ueditor",
                "title" => "百度编辑器",
                "ifoption" => 0,
            ];
            
            return $fieldType;
        });
        
        add_filter('config_model_get_configs', function($newConfigs, $configs) {
            foreach ($configs as $key => $value) {
                if ($value['type'] == 'ueditor') {
                    $newConfigs[$value['name']] = htmlspecialchars_decode($value['value']);
                }
            }
            
            return $newConfigs;
        });
        
        // 系统闪存插件
        add_filter('flash_model_get_configs', function($settingDatalist, $settinglist) {
            foreach ($settinglist as $value) {
                if ($value['type'] == 'ueditor') {
                    $settingDatalist[$value['name']] = htmlspecialchars_decode($value['value']);
                }
            }
            
            return $settingDatalist;
        });
    }
    
    protected function getInputItemJS()
    {
        $html = '
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor/ueditor.config.js").'"></script>
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor/ueditor.all.js").'"></script>
        <script type="text/javascript">
        var laket_ueditor = {
            "upload_url": "'.laket_route("admin.laket-ueditor.upload").'?dir=images"
        };
        </script>
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor.js").'"></script>
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
     * 安装后
     */
    public function install()
    {
        $slug = $this->slug;
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
