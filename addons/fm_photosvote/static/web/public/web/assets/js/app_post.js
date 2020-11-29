$(function() {
    $('.tpl-switch').find('.tpl-switch-btn-view').on('click', function() {
        $(this).prev('.tpl-switch-btn').prop("checked", function() {
                if ($(this).is(':checked')) {
                    return false
                } else {
                    return true
                }
            })
            // console.log('123123123')

    })

})

// ==========================
// 侧边导航下拉列表
// ==========================

$('.tpl-left-nav-link-list').on('click', function() {
    $(this).siblings('.tpl-left-nav-sub-menu').slideToggle(80)
        .end()
        .find('.tpl-left-nav-more-ico').toggleClass('tpl-left-nav-more-ico-rotate');
})
// ==========================
// 头部导航隐藏菜单
// ==========================

$('.tpl-header-nav-hover-ico').on('click', function() {
    $('.tpl-left-nav').toggle();
    $('.tpl-content-wrapper').toggleClass('tpl-content-wrapper-hover');
})

$('.label').hover(function(){
	$(this).tooltip('show');
},function(){
	$(this).tooltip('hide');
});
$('.btn').hover(function(){
	$(this).tooltip('show');
},function(){
	$(this).tooltip('hide');
});
$('[data-toggle="tooltip"]').tooltip()
function clip(elm, str,link_swf) {
	//console.log(str);
	$(elm).zclip({
		path: link_swf,
		copy: str,
		afterCopy: function(){
		//console.log(str);
			var obj = $('<em> &nbsp; <span class="label label-success"><i class="fa fa-check-circle"></i> 复制成功</span></em>');
			var enext = $(elm).next().html();
			if (!enext || enext.indexOf('&nbsp; <span class="label label-success"><i class="fa fa-check-circle"></i> 复制成功</span>')<0) {
				$(elm).after(obj);
			}
			setTimeout(function(){
				obj.remove();
			}, 2000);
		}
	});
};

function alert(elm) {
    $('.am-alert').addClass('am-fade am-in');
	$('.am-alert').hide();
}
function drop_confirm(msg, url){
	if(confirm(msg)){
		window.location = url;
	}
}
  function fmloadding(id, text) {
  	$(id).show();
  	$(id).removeClass("beforeFadeIn");

  	setTimeout(function() {
  		$(id).hide();
  		$(id).addClass("beforeFadeIn");
  	}, 3000)
  	$('#toast').html(text);
  }

  function sendpic() {
  	$('#Modal1').modal('toggle');
  }

  function sendmusic() {
  	$('#Modal2').modal('toggle');
  }

  function sendvedio() {
  	$('#Modal3').modal('toggle');
  }

  function sendp() {
  	$('#Modalp').modal('toggle');
  }

  function showpic(imgurl) {
  	$('#showpic').modal('toggle');
  	var picpre = document.getElementById("picpre");
  	picpre.src = imgurl;
  	picpre.style.display = 'block';
  	picpre.style.width = '120px';
  }

  function uppic() {
  	$('#uppic').modal('toggle');
  }




function vedioafter(mid){
    var html ='<div class="parentFileBox allboxv" id="parentFileBoxv" style="width: 50%;"> ';
      html +='      <a href="javascript:;" class="fmwebuploader-pick" id="webuploaderv" onclick="javascript:$(\'#vedio\').click();" style="width: 90%;height: 200px;"><img src="{FM_STATIC_MOBILE}public/images/addimages.png?v=3.1" width="30" style="width: 60%;position: absolute; z-index: 11;  text-align: center;  left: 20%;" /></a>';

      html +='        <ul class="fileBoxUl">';
      html +='          <li id="fileBox_WU_FILE_v" class="diyUploadHover" style="  width: 90%;height: 200px;"> ';
      html +='            <div class="viewThumb" id="vediopreview" style="  width: 100%;height: 200px;"> ';
      html +='                <video controls preload="auto" width="100%" height="200" id="previewv" poster="">';
      html +='              <source src="" type=\'video/mp4\' />';
      html +='              <p class="vjs-no-js">你的浏览器不支持该视频</a></p>';
      html +='            </video>';
      html +='            </div> ';
      html +='            <div id="videodel" class="videodel"><a href="javascript:;" ><div class="diyCancel" id="diyCancelv" width="100%;"></div></a></div>';
      html +='            <div class="diyFileName" id="diyFileNamev">preview.png</div> ';
      html +='            <div class="diyBar" id="diyBarv" style="display:none"> ';
      html +='                <div class="diyProgress" id="diyProgressv" style="top:0;height:200px;"></div> ';
      html +='                <div class="diyProgressText" id="diyProgressTextv" style="font-size: 25px;"></div> ';
      html +='            </div>';
      html +='          </li>';
      html +='        </ul> ';
      html +='    </div>';
      html +='    <div style="position: absolute; top: 0px; left: 0px; width: 126px; height: 36px; overflow: hidden; bottom: auto; right: auto;">';
      html +='      <input type="file" name="vedio" id="vedio" class="webuploader-element-invisible" style="display:none;" accept="video/*" capture="camcorder" onchange="changeaudios(\'vedio\', \'v\')" >';
      html +='    </div>';
        $("#vedioafter").parent().append( html );
    return false;

}
function musicafter(mid){
    var html ='<div class="parentFileBox allboxm" id="parentFileBoxm" style="width: 50%;"> ';
      html +='      <a href="javascript:;" class="fmwebuploader-pick" id="webuploaderm" onclick="javascript:$(\'#music\').click();" style="width: 90%;height: 200px;"><img src="{FM_STATIC_MOBILE}public/images/addimages.png?v=3.1" width="30" style="width: 60%;position: absolute; z-index: 11;  text-align: center;  left: 20%;" /></a>';

      html +='        <ul class="fileBoxUl">';
      html +='          <li id="fileBox_WU_FILE_m" class="diyUploadHover" style="  width: 90%;height: 200px;"> ';
      html +='            <div class="viewThumb" id="musicpreview" style="  width: 100%;height: 200px;"> ';
      html +='            </div> ';
      html +='            <div id="musicdel" class="musicdel"><a href="javascript:;" ><div class="diyCancel" id="diyCancelm" width="100%;"></div></a></div>';
      html +='            <div class="diyFileName" id="diyFileNamem">preview.png</div> ';
      html +='            <div class="diyBar" id="diyBarm" style="display:none"> ';
      html +='                <div class="diyProgress" id="diyProgressm" style="top:0;height:200px;"></div> ';
      html +='                <div class="diyProgressText" id="diyProgressTextm" style="font-size: 25px;"></div> ';
      html +='            </div>';
      html +='          </li>';
      html +='        </ul> ';
      html +='    </div>';
      html +='    <div style="position: absolute; top: 0px; left: 0px; width: 126px; height: 36px; overflow: hidden; bottom: auto; right: auto;">';
      html +='      <input type="file" name="music" id="music" class="webuploader-element-invisible" style="display:none;" accept="audio/*" capture="camcorder" onchange="changeaudios(\'music\', \'m\')" >';
      html +='    </div>';
        $("#musicafter").parent().append( html );
    return false;

}

 function uploadProgress(evt,mid) {
       if (evt.lengthComputable) {
        //iBytesUploaded = evt.loaded;
        //iBytesTotal = evt.total;
        var iPercentComplete = Math.round(evt.loaded * 100 / evt.total);
        //var iBytesTransfered = bytesToSize(iBytesUploaded);


        document.getElementById('diyProgressText' + mid).innerHTML = iPercentComplete.toString() + '%' ;
        document.getElementById('diyProgress' + mid).style.left = (iPercentComplete * 1).toString() + '%';
        document.getElementById('diyProgressText' + mid).style.left = (iPercentComplete * 1 - 15).toString() + '%';


        if (iPercentComplete == 100) {
          document.getElementById('diyProgressText' + mid).innerHTML = '上传云端中...' ;
          document.getElementById('diyProgressText' + mid).style.left = '30%';
        }
            }
            else {
        document.getElementById('diyProgressText' + mid).innerHTML = 'unable to compute';

            }
        }
    function bytesToSize(bytes) {
      var sizes = ['Bytes', 'KB', 'MB'];
      if (bytes == 0) return 'n/a';
      var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
      return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    };
    function uploadComplete(evt,mid) {
            /* This event is raised when the server send back a response */
            //alert(evt.target.responseText);

      var progressNumber = 'diyProgressText' + mid;

      document.getElementById(progressNumber).innerHTML = '上传完成';
      document.getElementById(progressNumber).style.left = '35%';
        }

        function uploadFailed(evt,mid) {
      var progressNumber = 'diyProgressText' + mid;
      document.getElementById(progressNumber).innerHTML = '上传时出错！';
      document.getElementById(progressNumber).style.left = '31%';
           // alert("There was an error attempting to upload the file.");
        }

        function uploadCanceled(evt,mid) {

      var progressNumber = 'diyProgressText' + mid;
           // alert("The upload has been canceled by the user or the bitemser dropped the connection.");
      document.getElementById(progressNumber).innerHTML = '取消上传';
      document.getElementById(progressNumber).style.left = '35%';
    }

  function html5Reader(file, mid){
     var file = file.files[0];
     var reader = new FileReader();
     reader.readAsDataURL(file);
     reader.onload = function(e){
      $('#uploadimg' + mid).hide();
      var pic = document.getElementById('preview' + mid);
      pic.src=this.result;
      pic.style.display = 'block';
     }
   }