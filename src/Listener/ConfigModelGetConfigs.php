<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor\Listener;

use Laket\Admin\Settings\Event;

/**
 * 获取配置
 */
class ConfigModelGetConfigs
{
    /**
     * 构造方法
     */
    public function handle(Event\ConfigModelGetConfigs $event)
    {
        $configs = $event->data->configs;
        $newConfigs = $event->data->newConfigs;
        
        foreach ($configs as $key => $value) {
            if ($value['type'] == 'ueditor') {
                $newConfigs[$value['name']] = htmlspecialchars_decode($value['value']);
            }
        }
        
        $event->data->newConfigs = $newConfigs;
    }

}
