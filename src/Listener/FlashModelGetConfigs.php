<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor\Listener;

use Laket\Admin\Event;

/**
 * 闪存获取配置
 */
class FlashModelGetConfigs
{
    /**
     * 构造方法
     */
    public function handle(Event\FlashModelGetConfigs $event)
    {
        $settinglist = $event->data->settinglist;
        $settingDatalist = $event->data->settingDatalist;
        
        foreach ($settinglist as $value) {
            if ($value['type'] == 'ueditor') {
                $settingDatalist[$value['name']] = htmlspecialchars_decode($value['value']);
            }
        }
        
        $event->data->settingDatalist = $settingDatalist;
    }

}
