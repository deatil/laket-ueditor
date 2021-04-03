$(function() {
     // ueditor编辑器集合
     var ueditors = {};

     // ueditor编辑器
     var bindUeditor = laket_ueditor.bind_id || '.js-ueditor';
     $(bindUeditor).each(function() {
         var ueditor_name = $(this).attr('id');
         ueditors[ueditor_name] = UE.getEditor(ueditor_name, {
             initialFrameHeight: 400, //初始化编辑器高度,默认320
             autoHeightEnabled: false, //是否自动长高
             maximumWords: 50000, //允许的最大字符数
             serverUrl: laket_ueditor.upload_url,
         });
         
         // 本地化图片
         $('#' + ueditor_name + 'grabimg').click(function() {
             var con = ueditors[ueditor_name].getContent();
             $.post(laket_ueditor.upload_url, { 
                'content': con, 
                'type': 'images' 
             }, function(data) {
                 ueditors[ueditor_name].setContent(data);
                 layer.msg("图片本地化完成");
             }, 'html');
         })
     });
});