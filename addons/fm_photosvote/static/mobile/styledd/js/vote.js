var Dialogure = {
            $Main: null,
            Css: null,
            ClickInit: function (obj,id,name,wid,pid,key) {
                if($("#video"+name).length > 0&&$("#colorbox").css("display")=="block") $("#cboxClose").trigger("click");
                $("#dialogJ_hid").val("");
                var $d = Dialogure.GetNewBackGround();
                $("body").append($d);
                $("#sfxsfx").html("正在拉取验证码");
                setload();
                $.get("/9code/Target.htm", { r: Math.random() }, function (data) {
                    $(".load").css("display","none");
                    Dialogure.$Main = $(data);
                    $("body").append(Dialogure.$Main);

                    var ww=$('.dialogJshadow').width();
                    var hh=$('.dialogJshadow').height();
                    var top = parseInt(($(window).height()-hh)/2);
                    //Dialogure.$Main.css({ left: ($(window).width() / 2 - 184) + "px", top: 110 + "px" });
                    Dialogure.$Main.css({ left: (($(window).width()-ww-10) / 2) + "px", top: top + "px",position:"fixed" });

                    $('.dialogJtxt').html($("#uname"+name).html());
                    Dialogure.Css = $(Dialogure.AttrCss());
                    $("head").append(Dialogure.Css);
                    Dialogure.ClickImg(obj,id,name,wid,pid,key);
                });
            },
            AttrCss: function (obj,id,name,wid,pid,key) {
                var CSS = "<style type='text/css'>.tbui_captcha_grid_input div, .tbui_captcha_grid_content .tbui_captcha_img_wrap, .tbui_captcha_grid_buttons div{background:url(/@system/ncode.html?r=" + Math.random() + "&wxid="+key+"&wid="+wid+") no-repeat scroll -500px -500px transparent}</style>"
                return CSS;
            },
            GetNewBackGround: function () {
                var Html = '<div class="dialogJmodal" style="z-index: 99999; width:' + $(window).width() + 'px; height: ' + $(document).height() + 'px;"></div>';
                var $d = $('' + Html + '');
                return $d;

            }, Close: function () {
                $(".dialogJmodal").remove();
                Dialogure.$Main.remove();
            }, Reload: function () {
                Dialogure.Css.remove();
                Dialogure.Css = $(Dialogure.AttrCss());
                $("head").append(Dialogure.Css);
                for (var i = 0; i < 4; i++) {
                    Dialogure.RemoveImg();
                }


            }, ClickImg: function (obj,id,name,wid,pid,key) {
                $("div [divindex]").click(function () {
                    var $tagrget = Dialogure.getEmptyTarget();
                    if ($tagrget != null) {
                        var position = $(this).attr("divindex");
                        $tagrget.css({ "background-position": position }).attr({ targetindex: position });
                        $("#dialogJ_hid").val($("#dialogJ_hid").val() + $(this).attr("re"));
                    }
                    //AJAX 提交
                    $tagrget = Dialogure.getEmptyTarget();
                    if ($tagrget == null) {
                        Dialogure.AjaxSubmit(obj,id,name,wid,pid,key);
                    }
                });
            }, RemoveImg: function () {
                for (var i = $("div [targetindex]").length - 1; i >= 0; i--) {
                    if ($("div [targetindex]").eq(i).attr("targetindex") + "" != "") {
                        $("div [targetindex]").eq(i).attr({ targetindex: "" }).removeAttr("style");
                        var s = $("#dialogJ_hid").val();
                        $("#dialogJ_hid").val(s.substring(0, s.length - 1));
                        break;
                    }
                }
            }, getEmptyTarget: function () {
                var $target = null;
                for (var i = 0; i < $("div [targetindex]").length; i++) {
                    if ($("div [targetindex]").eq(i).attr("targetindex") + "" == "") {
                        $target = $("div [targetindex]").eq(i);
                        break;
                    }
                }
                return $target;
            }, AjaxSubmit: function (obj,id,name,wid,pid,key) {
                var code=$("#dialogJ_hid").val();
                ajaxvote(id,name,wid,pid,key,code);
                Dialogure.Close();
                $("#dialogJ_hid").val("");

            }
        }