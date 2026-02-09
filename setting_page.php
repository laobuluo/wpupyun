<?php
/**
 *  插件设置页面
 */
function wpupyun_setting_page() {
// 如果当前用户权限不足
	if (!current_user_can('manage_options')) {
		wp_die('Insufficient privileges!');
	}

	$wpupyun_options = get_option('wpupyun_options');
	if ( ! is_array( $wpupyun_options ) ) {
		$wpupyun_options = array(
			'serviceName'   => '',
			'operatorName'  => '',
			'operatorPwd'   => '',
			'no_local_file' => false,
			'opt'           => array( 'auto_rename' => false, 'thumbsize' => null ),
		);
	}
	if ( ! empty( $_POST ) && isset( $_POST['type'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', -1 ) ) {
		if ( $_POST['type'] === 'cos_info_set' ) {

		    $wpupyun_options['no_local_file'] = isset($_POST['no_local_file']);
            $wpupyun_options['serviceName'] = isset($_POST['serviceName']) ? sanitize_text_field(trim(stripslashes($_POST['serviceName']))) : '';
            $wpupyun_options['operatorName'] = isset($_POST['operatorName']) ? sanitize_text_field(trim(stripslashes($_POST['operatorName']))) : '';
            $wpupyun_options['operatorPwd'] = isset($_POST['operatorPwd']) ? sanitize_text_field(trim(stripslashes($_POST['operatorPwd']))) : '';
            $wpupyun_options['opt']['auto_rename'] = isset($_POST['auto_rename']);

            $wpupyun_options = wpupyun_set_thumbsize($wpupyun_options, isset($_POST['disable_thumb']) );

            // 不管结果变没变，有提交则直接以提交的数据 更新wpupyun_options
            update_option('wpupyun_options', $wpupyun_options);
            # 替换 upload_url_path 的值
            update_option('upload_url_path', esc_url_raw(trim(stripslashes($_POST['upload_url_path']))));

            ?>
            <div class="notice notice-success settings-error is-dismissible"><p><strong>设置已保存。</strong></p></div>

            <?php }
	}
		?>
<link rel='stylesheet'  href='<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>layui/css/layui.css' />
<link rel='stylesheet'  href='<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>layui/css/laobuluo.css'/>
<script src='<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>layui/layui.js'></script>
<style type="text/css">
.wpcosform .layui-form-label{width:120px;}
.wpcosform .layui-input{width: 350px;}
.wpcosform .layui-form-mid{margin-left:3.5%;}
.wpcosform .layui-form-mid p{padding: 3px 0;}
.laobuluo-wp-hidden {position: relative;}
.laobuluo-wp-hidden .laobuluo-wp-eyes{padding: 5px;position:absolute;top:3px;z-index: 999;display: none;}
.laobuluo-wp-hidden i{font-size:20px;}
.laobuluo-wp-hidden i.dashicons-visibility{color:#009688 ;}
@media screen and (max-width: 768px) {
	.wpupyun-sidebar { display: none !important; }
}
</style>
<div class="container-laobuluo-main">
	<div class="laobuluo-wbs-header" style="margin-bottom: 15px;">
		<div class="laobuluo-wbs-logo">
			<span class="wbs-span">又拍云云存储插件</span><span class="wbs-free">Free V4.0</span>
		</div>
		<div class="laobuluo-wbs-btn">
			<a class="layui-btn layui-btn-primary" href="https://www.lezaiyun.com/1099.html" target="_blank">
				<i class="layui-icon layui-icon-home"></i> 插件主页
			</a>
			<a class="layui-btn layui-btn-primary" href="https://www.lezaiyun.com/contact/" target="_blank">
				<i class="layui-icon layui-icon-release"></i> 插件教程
			</a>
		</div>
	</div>
</div>
<!-- 内容 -->
<div class="container-laobuluo-main">
	<div class="layui-container container-m">
		<div class="layui-row layui-col-space15">
			<!-- 左边 -->
			<div class="layui-col-md9">
				<div class="laobuluo-panel">
					<div class="laobuluo-controw">
						<fieldset class="layui-elem-field layui-field-title site-title">
							<legend>
								<a name="get">
									设置选项
								</a>
							</legend>
						</fieldset>
						<form class="layui-form wpcosform" action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . WPUpYun_BASEFOLDER . '/actions.php' ) ) ); ?>" name="wpcosform" method="post" >
							<div class="layui-form-item">
								<label class="layui-form-label">服务名称</label>
								<div class="layui-input-block">
									<input class="layui-input" type="text" name="serviceName" value="<?php echo esc_attr( $wpupyun_options['serviceName'] ?? '' ); ?>" size="50" placeholder="云存储服务名称"/>
									<div class="layui-form-mid layui-word-aux">
										创建云存储服务填写的名称。示范：
										<code>
										laojiang
										</code>
									</div>
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label"> 绑定加速域名</label>
								<div class="layui-input-block">
									<input class="layui-input" type="text" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" size="50" placeholder="请输入又拍云存储绑定加速域名"/>
									<div class="layui-form-mid layui-word-aux">
										<p>
											1. 一般我们是以：
											<code>
											http(s)://{自定义加速域名}
											</code>
											，同样不要用"/"结尾。
										</p>
										<p>
											2. 不要使用又拍云存储自带的测试域名，测试域名不提供公开使用，须绑定备案域名，支持自定义二级目录。
										</p>
										<p>
											3. 示范：
											<code>
											http(s)://upyun.lezaiyun.com
											</code>
										</p>
										<p>
											4. 示范：
											<code>
											http(s)://upyun.lezaiyun.com/cnwper
											</code>
										</p>
									</div>
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label"> 操作员用户</label>
								<div class="layui-input-block">
									<div class="laobuluo-wp-hidden">
										<input class="layui-input"  type="password" name="operatorName" value="<?php echo esc_attr( $wpupyun_options['operatorName'] ?? '' ); ?>" size="50" placeholder="操作员用户名"/>
										<span class="laobuluo-wp-eyes"><i class="dashicons dashicons-hidden"></i></span>
									</div>
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label"> 授权操作员密码</label>
								<div class="layui-input-block">
									<div class="laobuluo-wp-hidden">
										<input class="layui-input"  type="password" name="operatorPwd" value="<?php echo esc_attr( $wpupyun_options['operatorPwd'] ?? '' ); ?>" size="50" placeholder="操作员密码"/>
										<span class="laobuluo-wp-eyes"><i class="dashicons dashicons-hidden"></i></span>
									</div>
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label"> 自动重命名</label>
								<div class="layui-input-inline" style="width:60px;">
									<input type="checkbox" name="auto_rename"  title="设置"
									 <?php
                                         if ( ! empty( $wpupyun_options['opt']['auto_rename'] ) ) {
                                          echo 'checked="TRUE"';
                                         }
                                     ?>
									>
								</div>
								<div class="layui-form-mid layui-word-aux">
									上传文件自动重命名，解决中文文件名或者重复文件名问题
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label">不在本地保存</label>
								<div class="layui-input-inline" style="width:60px;">
									<input type="checkbox"  name="no_local_file"  title="设置"
									<?php
									if ( ! empty( $wpupyun_options['no_local_file'] ) ) {
										echo 'checked="TRUE"';
									}
									?>
									>
								</div>
								<div class="layui-form-mid layui-word-aux">
									如不想在服务器中备份静态文件就 "勾选"。
								</div>
							</div>
							<div class="layui-form-item">
								<label class="layui-form-label">禁止缩略图</label>
								<div class="layui-input-inline" style="width:60px;">
									<input type="checkbox"  name="disable_thumb" title="禁止"
									<?php
									if (isset($wpupyun_options['opt']['thumbsize'])) {
										echo 'checked="TRUE"';
									}
									?>
									>
								</div>
								<div class="layui-form-mid layui-word-aux">
									仅生成和上传主图，禁止缩略图裁剪。
								</div>
							</div>
					 </div>
					 <div class="layui-form-item">
					 	  <label class="layui-form-label"></label>
					 	  <div class="layui-input-block"><input type="submit" name="submit" value="保存设置" class=" layui-btn" lay-submit lay-filter="formDemo" /></div>
					 </div>
					 <input type="hidden" name="type" value="cos_info_set">
					</form>
				</div>
			</div>
		<!-- 左边 -->
		<!-- 右边 -->
		<div class="layui-col-md3 wpupyun-sidebar">
			<div id="nav">
				 <div class="laobuluo-panel">
                        <div class="laobuluo-panel-title">关注公众号</div>
                        <div class="laobuluo-code">
                            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>layui/images/qrcode.png" alt="">
                            <p>微信扫码关注 <span class="layui-badge layui-bg-blue">老蒋朋友圈</span> 公众号</p>
                            <p><span class="layui-badge">优先</span> 获取插件更新 和 更多 <span class="layui-badge layui-bg-green">免费插件</span> </p>
                        </div>
                    </div>                 
			</div>


		</div>
		<!-- 右边 -->
	</div>
</div>
</div>
<!-- 内容 -->
<!-- footer -->
<div class="container-laobuluo-main">
	<div class="layui-container container-m">
		<div class="layui-row layui-col-space15">
			<div class="layui-col-md12">
				<div class="laobuluo-footer-code">
					<span class="codeshow"></span>
				</div>
				<div class="laobuluo-links">
                    <a href="https://www.lezaiyun.com/"  target="_blank">乐在云工作室</a>
                    <a href="https://www.zhujipingjia.com/pianyivps.html" target="_blank">便宜VPS推荐</a>
                    <a href="https://www.zhujipingjia.com/hkcn2.html" target="_blank">香港VPS推荐</a>
                    <a href="hhttps://www.zhujipingjia.com/uscn2gia.html" target="_blank">美国VPS推荐</a>
                </div>
			</div>
		</div>
	</div>
</div>
<!-- footer -->
<script>
layui.use(['form', 'element','jquery'], function() {
var $ =layui.jquery;
var form = layui.form;
function menuFixed(id) {
var obj = document.getElementById(id);
var _getHeight = obj.offsetTop;
var _Width= obj.offsetWidth
window.onscroll = function () {
changePos(id, _getHeight,_Width);
}
}
function changePos(id, height,width) {
var obj = document.getElementById(id);
obj.style.width = width+'px';
var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
var _top = scrollTop-height;
if (_top < 150) {
var o = _top;
obj.style.position = 'relative';
o = o > 0 ? o : 0;
obj.style.top = o +'px';

} else {
obj.style.position = 'fixed';
obj.style.top = 50+'px';

}
}
menuFixed('nav');

var laobueys = $('.laobuluo-wp-hidden')

laobueys.each(function(){

var inpu = $(this).find('.layui-input');
var eyes = $(this).find('.laobuluo-wp-eyes')
var width = inpu.outerWidth(true);
eyes.css('left',width+'px').show();

eyes.click(function(){
if(inpu.attr('type') == "password"){
inpu.attr('type','text')
eyes.html('<i class="dashicons dashicons-visibility"></i>')
}else{
inpu.attr('type','password')
eyes.html('<i class="dashicons dashicons-hidden"></i>')
}
})
})


})
</script>
<?php
}
?>