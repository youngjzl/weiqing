<?php defined('IN_IA') or exit('Access Denied');?>			</div>
		</div>
	</div>
</div>
</div>
<div class="container-fluid footer text-center" role="footer">	
	<div class="friend-link">
		<?php  if(empty($_W['setting']['copyright']['footerright'])) { ?>
			<a href="http://www.w7.cc">微信开发</a>
			<a href="https://xydai.cn">微信应用</a>
			<a href="https://xydai.cn">微擎论坛</a>
			<a href="https://xydai.cn">小程序开发</a>
		<?php  } else { ?>
			<?php  echo $_W['setting']['copyright']['footerright'];?>
		<?php  } ?>
	</div>
	<div class="copyright"><?php  if(empty($_W['setting']['copyright']['footerleft'])) { ?>Powered by <a href="http://www.w7.cc"><b>微擎</b></a> v<?php echo IMS_VERSION;?> &copy; 2014-2018 <a href="http://www.w7.cc">www.w7.cc</a><?php  } else { ?><?php  echo $_W['setting']['copyright']['footerleft'];?><?php  } ?></div>
	<?php  if(!empty($_W['setting']['copyright']['icp'])) { ?>
	<div>备案号：<a href="http://beian.miit.gov.cn/" target="_blank"><?php  echo $_W['setting']['copyright']['icp'];?></a></div><?php  } ?>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer-base', TEMPLATE_INCLUDEPATH)) : (include template('common/footer-base', TEMPLATE_INCLUDEPATH));?>
</body>
</html>