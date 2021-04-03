<?php

declare (strict_types = 1);

namespace Laket\Admin\Ueditor\Listener;

use Laket\Admin\Settings\Event;

/**
 * 配置类型
 */
class ConfigModelGetFieldType
{
    /**
     * 构造方法
     */
    public function handle(Event\ConfigModelGetFieldType $event)
    {
        $fieldType = $event->data->fieldType;
        $fieldType[] = [
            "name" => "ueditor",
            "title" => "百度编辑器",
            "ifoption" => 0,
        ];
        
        $event->data->fieldType = $fieldType;
    }

}
