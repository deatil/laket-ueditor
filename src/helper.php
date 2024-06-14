<?php

if (! function_exists('laket_ueditor_config')) {
    /**
     * 获取配置信息
     * @param string $key 取值key
     * @param mix $default 默认值
     * @return mix
     */
    function laket_ueditor_config(string $key = null, $default = null) {
        return laket_flash_setting('laket/laket-ueditor', $key, $default);;
    }
}

if (! function_exists('laket_ueditor_bind')) {
    /**
     * 编辑器绑定，默认绑定类 js-ueditor
     *
     * @return string
     */
    function laket_ueditor_bind($id = '') {
        $html = '
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor/ueditor.config.js").'"></script>
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor/ueditor.all.js").'"></script>
        <script type="text/javascript">
        var laket_ueditor = {
            "upload_url": "'.laket_route("admin.laket-ueditor.upload").'?dir=images",
            "bind_id": "'.$id.'"
        };
        </script>
        <script type="text/javascript" src="'.assets("laket-ueditor/ueditor.js").'"></script>
        ';
        
        return $html;
    }
}
