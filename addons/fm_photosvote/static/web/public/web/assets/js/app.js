$(function() {

        var $fullText = $('.admin-fullText');
        $('#admin-fullscreen').on('click', function() {
            $.AMUI.fullscreen.toggle();
        });

        $(document).on($.AMUI.fullscreen.raw.fullscreenchange, function() {
            $fullText.text($.AMUI.fullscreen.isFullscreen ? '退出全屏' : '开启全屏');
        });


        var dataType = $('#body').attr('data-type');
        for (key in pageData) {
            if (key == dataType) {
                pageData[key]();
            }
        }

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


// 页面数据
var pageData = {
    // ===============================================
    // 首页
    // ===============================================
    'home': function homeData() {
    		var IScroll = $.AMUI.iScroll;
        var myScrollhdysh = new IScroll('#wrapperhdysh', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });

        var myScrollhdwsh = new IScroll('#wrapperhdwsh', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
        var myScrollA = new IScroll('#wrapperA', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });

        var myScrollB = new IScroll('#wrapperB', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
        var myScrollC = new IScroll('#wrapperC', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
        var myScrollD = new IScroll('#wrapperD', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
        var myScrollcomment = new IScroll('#wrapper_comment', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
        var myScrollmsgs = new IScroll('#wrapper_msgs', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });

        $('.box-refresh').on('click', function(br) {
	        br.preventDefault();
	        $("<div class='refresh-block'><span class='refresh-loader'><i class='fa fa-spinner fa-spin'></i></span></div>").appendTo($(this).parents('.sblue-on'));

	        setTimeout(function() {
	            $('.refresh-block').remove();
	        }, 1000);

	    });

		var linkurl  = "./index.php?c=site&a=entry&do=Getdate&m=fm_photosvote";
		var linkurl_alldata = "./index.php?c=site&a=entry&do=GetAlldatab&m=fm_photosvote";
		var linkurl_shebei  = "./index.php?c=site&a=entry&do=GetShebei&m=fm_photosvote";
		var linkurl_local  = "./index.php?c=site&a=entry&do=GetLocaltion&m=fm_photosvote";

	    getDate(linkurl, 'hdysh');
	    getDate(linkurl, 'hdwsh');
	    getDate(linkurl, 'rank');
	    getDate(linkurl, 'charge');
	    getDate(linkurl, 'gift');
	    getDate(linkurl, 'jifen');
	    getDate(linkurl, 'vote');
	    getDate(linkurl, 'newreg');
	    getDate(linkurl, 'newwsh');
	    getDate(linkurl, 'newfans');
	    getDate(linkurl, 'comment');
	    getDate(linkurl, 'msgs');

	    getalldate(linkurl_alldata);
	    getshebei(linkurl_shebei);
	    getearth(linkurl_local);
		//$('#refresh_vote').click(function(){
		$('#refresh_alldata').on('click', function(re) {
			getalldate(linkurl_alldata);
	   	});
	   	$('#refresh_shebei').on('click', function(re) {
			getshebei(linkurl_shebei);
	   	});
	   	$('#refresh_earth').on('click', function(re) {
			getearth(linkurl_local);
	   	});
		$('#refresh_vote').on('click', function(re) {
			refresh(linkurl, 'vote', this, re);
	   	});
	   	$('#refresh_newreg').on('click', function(re) {
			refresh(linkurl, 'refresh_newreg', this, re);
	   	});

	   	$('#refresh_newwsh').on('click', function(re) {
			refresh(linkurl, 'refresh_newwsh', this, re);
	   	});

	   	$('#refresh_newfans').on('click', function(re) {
			refresh(linkurl, 'refresh_newfans', this, re);
	   	});



    /**
	$.post(linkurl_alldata,{"type":''},function(alldate){
        var echartsalldata = echarts.init(document.getElementById('tpl-alldata'));
		console.log(alldate[0]);
		option = {
		    backgroundColor: {
		          type: 'radial',
		          x: 0.5,
		          y: 0.4,
		          r: 0.3,
		          colorStops: [{
		              offset: 0,
		              color: '#895355' // 0% 处的颜色
		          }, {
		              offset: .2,
		              color: '#593640' // 100% 处的颜色
		          }, {
		              offset: 1,
		              color: '#39273d' // 100% 处的颜色
		          }],
		          globalCoord: false // 缺省为 false
		      },
		    tooltip: {
		        trigger: 'item',
		        backgroundColor : 'rgba(0,0,250,0.2)'
		    },
		    legend: {
		        show: true,
		        bottom: 10,
		        left: 16,
		        itemWidth: 14,
		        itemHeight: 10,
		        itemGap: 10,
		        width: '60%',
		        height: 40,
		        align: 'auto',
		        data: alldate[0].tname[0],
		        textStyle: {
		            color: '#fff',
		            fontSize: 14,
		        },
		        selectedMode: 'single',
		        //orient: 'horizontal',

		    },
		    textStyle: {
		        color: '#4ac7f5',
		        fontSize: 16,
		    },
		    visualMap: {
		        min: 0,
		        max: alldate[0].sm,
		        calculable: true,
		        itemWidth: 14,
		        itemHeight: 80,
		        align: 'left',
		        color: ['#3f4199','#5d54b5','#a0589e','#e76281','#fe846d','#feda5b'],
		        right: 0,
		        bottom: 0,
		        textStyle: {
		            color: '#4ac7f5',
		            fontSize: 14,
		        }
		    },
		    radar: {
		       //center: ['500', '350'],//中心（圆心）坐标
		       radius: 115,//半径
		       startAngle: 90,//坐标系起始角度，也就是第一个指示器轴的角度。
		       nameGap: 16,//指示器名称和指示器轴的距离。
		       splitNumber: 4,//指示器轴的分割段数
		       shape: 'polygon',//雷达图绘制类型，支持 'polygon' 和 'circle'
		       axisLine: { //坐标轴轴线
		           show: true,
		           lineStyle: {
		               color: '#564d8e',
		               width: 1,
		           },
		       },
		       splitLine: {//坐标轴在 grid 区域中的分隔线。
		           show: true,
		           lineStyle: {
		               color: '#4b476f',
		               width: 1,
		           },
		       },
		       splitArea: {//坐标轴在 grid 区域中的分隔区域，默认不显示。
		           show: true,
		           areaStyle: {
		               color: '#2c2949'
		           },
		       },
		       indicator : alldate[0].max
		    },
		    series : [{
		                name:'违规占比雷达图',
		                type: 'radar',
		                symbol: 'none',
		                areaStyle: {//区域填充样式
		                    emphasis: {
		                        opacity: 0.3,
		                    },
		                },
		                lineStyle: {
		                    normal: {
		                        width: 0.8,
        							opacity: 0.5
		                    },
		                },
		                data: alldate[0].value
		    },{
		                name:'违规占比雷达图',
		                type: 'radar',
		                symbol: 'none',
		                areaStyle: {//区域填充样式
		                    emphasis: {
		                        opacity: 0.3,
		                    },
		                },
		                lineStyle: {
		                    normal: {
		                        width: 0.8,
        							opacity: 0.5
		                    },
		                },
		                data: alldate[0].value
		    }]
		};

		echartsalldata.setOption(option);
	},"json");
	/**$.get(linkurl_alldata, function (xml) {
		var alldata = echarts.init(document.getElementById('tpl-alldata'));
	    console.log(xml);
	    alldata.showLoading();
	    alldata.hideLoading();

	    var graph = echarts.dataTool.gexf.parse(xml);
	    var categories = [{name:"报名"},{name:"充值"},{name:"礼物数"},{name:"投票数"},{name:"点赞数"},{name:"分享数"},{name:"粉丝数"}];
	    /**for (var i = 0; i < 3; i++) {
	        categories[i] = {
	            name: '类目' + i
	        };
	    }**
	    graph.nodes.forEach(function (node) {
	        node.itemStyle = null;
	        node.value = node.symbolSize;
        		node.symbolSize /= 200;
	        node.label = {
	            normal: {
	                show: node.symbolSize > 30
	            }
	        };
	        node.label.normal.show = node.symbolSize > 30;
	        node.category = node.attributes.modularity_class;
	    });
	    option = {
	        title: {
	            text: '数据分析',
	            subtext: 'Circular layout',
	            top: 'bottom',
	            left: 'right'
	        },
	        tooltip: {},
	        legend: [{
	            // selectedMode: 'single',
	            data: categories.map(function (a) {
	                return a.name;
	            })
	        }],
	        animationDurationUpdate: 1500,
	        animationEasingUpdate: 'quinticInOut',
	        series : [
	            {
	                name: '数据分析',
	                type: 'graph',
	                layout: 'circular',
	                circular: {
	                    rotateLabel: true
	                },
	                data: graph.nodes,
	                links: graph.links,
	                categories: categories,
	                roam: true,
                		focusNodeAdjacency: true,
	                		itemStyle: {
	                    normal: {
	                        borderColor: '#fff',
	                        borderWidth: 1,
	                        shadowBlur: 10,
	                        shadowColor: 'rgba(0, 0, 0, 0.3)'
	                    }
	                },
	                label: {
	                    normal: {
	                        position: 'right',
	                        formatter: '{b}'
	                    }
	                },
	                lineStyle: {
	                    normal: {
	                        color: 'source',
	                        curveness: 0.3
	                    }
	                },
	                emphasis: {
	                    lineStyle: {
	                        width: 10
	                    }
	                }
	            }
	        ]
	    };

	    alldata.setOption(option);
	}, 'xml');**/



   },
 	// ===============================================
    // 数据统计管理页
    // ===============================================
	'statistics': function statisticsData() {

       	$('html').css({'background' : '#32085a'});
       	$('body').css({'background' : '#32085a'});
		var IScroll = $.AMUI.iScroll;
		var myScroll_shebei = new IScroll('#wrapper_shebei', {
            scrollbars: true,
            mouseWheel: true,
            interactiveScrollbars: true,
            shrinkScrollbars: 'scale',
            preventDefault: false,
            fadeScrollbars: true
        });
		var linkurl = "./index.php?c=site&a=entry&do=Getcdate&m=fm_photosvote";
		var linkurl_calldata = "./index.php?c=site&a=entry&do=calldata&m=fm_photosvote";
		var linkurl_shebei  = "./index.php?c=site&a=entry&do=callshebei&m=fm_photosvote";
		var linkurl_reg  = "./index.php?c=site&a=entry&do=callreg&m=fm_photosvote";
		var linkurl_charge  = "./index.php?c=site&a=entry&do=callcharge&m=fm_photosvote";
		var linkurl_votes  = "./index.php?c=site&a=entry&do=callvotes&m=fm_photosvote";
		var linkurl_comment  = "./index.php?c=site&a=entry&do=callcomment&m=fm_photosvote";
		var linkurl_share  = "./index.php?c=site&a=entry&do=callshare&m=fm_photosvote";
		var linkurl_fans  = "./index.php?c=site&a=entry&do=callfans&m=fm_photosvote";

		calldata(linkurl_calldata);
	    callshebei(linkurl_shebei);
	    getDate(linkurl, 'shebei_list');
	    callreg(linkurl_reg);
	    getDate(linkurl, 'reg_list');
	    callcharge(linkurl_charge);
	    getDate(linkurl, 'charge_list');
	    callvotes(linkurl_votes);
	    callcomment(linkurl_comment);
	    callshare(linkurl_share);
	    callfans(linkurl_fans);

		$('#refresh').on('click', function(re) {
			calldata(linkurl_calldata, 'all');
			allrefresh(this, re);
        });
        $('#refresh_shebei').on('click', function(re) {
        		callshebei(linkurl_shebei, 'all');
			allrefresh(this, re);
        });
        $('#refresh_shebei_list').on('click', function(re) {
        		refresh(linkurl, 'shebei_list', this, re);
        });
        $('#refresh_reg').on('click', function(re) {
        		callreg(linkurl_reg, 'all');
			allrefresh(this, re);
			$('#choose_yesterday_reg').addClass('am-hide');
    			$('#choose_week_reg').addClass('am-hide');
    			$('#choose_month_reg').addClass('am-hide');
        });
        $('#refresh_reg_list').on('click', function(re) {
        		refresh(linkurl, 'reg_list', this, re);
        });
        $('#refresh_charge').on('click', function(re) {
        		callcharge(linkurl_charge, 'all');
			allrefresh(this, re);
			$('#choose_yesterday_charge').addClass('am-hide');
    			$('#choose_week_charge').addClass('am-hide');
    			$('#choose_month_charge').addClass('am-hide');
        });
        $('#refresh_charge_list').on('click', function(re) {
        		refresh(linkurl, 'charge_list', this, re);
        });
        $('#refresh_votes').on('click', function(re) {
        		callvotes(linkurl_votes, 'all');
			allrefresh(this, re);
        });
        $('#refresh_comment').on('click', function(re) {
        		callcomment(linkurl_comment, 'all');
			allrefresh(this, re);
			$('#choose_yesterday_comment').addClass('am-hide');
    			$('#choose_week_comment').addClass('am-hide');
    			$('#choose_month_comment').addClass('am-hide');
        });
        $('#refresh_share').on('click', function(re) {
        		callshare(linkurl_share, 'all');
			allrefresh(this, re);
			$('#choose_yesterday_share').addClass('am-hide');
    			$('#choose_week_share').addClass('am-hide');
    			$('#choose_month_share').addClass('am-hide');
        });
        $('#refresh_fans').on('click', function(re) {
        		callfans(linkurl_fans, 'all');
			allrefresh(this, re);
        });


		$('#choose_yesterday_reg').on('click', function(re) {
    			choose_time_reg(linkurl_reg, this,'yesterday', re);
        });
        $('#choose_week_reg').on('click', function(re) {
    			choose_time_reg(linkurl_reg, this,'week', re);
        });
        $('#choose_month_reg').on('click', function(re) {
    			choose_time_reg(linkurl_reg, this,'month', re);
        });

        $('#choose_yesterday_charge').on('click', function(re) {
    			choose_time_charge(linkurl_charge, this,'yesterday', re);
        });
        $('#choose_week_charge').on('click', function(re) {
    			choose_time_charge(linkurl_charge, this,'week', re);
        });
        $('#choose_month_charge').on('click', function(re) {
    			choose_time_charge(linkurl_charge, this,'month', re);
        });

        $('#choose_yesterday_votes').on('click', function(re) {
    			choose_time_votes(linkurl_votes, this,'yesterday', re);
        });
        $('#choose_week_votes').on('click', function(re) {
    			choose_time_votes(linkurl_votes, this,'week', re);
        });
        $('#choose_month_votes').on('click', function(re) {
    			choose_time_votes(linkurl_votes, this,'month', re);
        });

        $('#choose_yesterday_comment').on('click', function(re) {
    			choose_time_comment(linkurl_charge, this,'yesterday', re);
        });
        $('#choose_week_comment').on('click', function(re) {
    			choose_time_comment(linkurl_comment, this,'week', re);
        });
        $('#choose_month_comment').on('click', function(re) {
    			choose_time_comment(linkurl_comment, this,'month', re);
        });

        $('#choose_yesterday_share').on('click', function(re) {
    			choose_time_share(linkurl_share, this,'yesterday', re);
        });
        $('#choose_week_share').on('click', function(re) {
    			choose_time_share(linkurl_share, this,'week', re);
        });
        $('#choose_month_share').on('click', function(re) {
    			choose_time_share(linkurl_share, this,'month', re);
        });

        $('#choose_yesterday_fans').on('click', function(re) {
    			choose_time_fans(linkurl_fans, this,'yesterday', re);
        });
        $('#choose_week_fans').on('click', function(re) {
    			choose_time_fans(linkurl_fans, this,'week', re);
        });
        $('#choose_month_fans').on('click', function(re) {
    			choose_time_fans(linkurl_fans, this,'month', re);
        });




        $('#js-selected').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid =linkurl_calldata + '&rid=' + $(this).val();
        			calldata(linkurl_rid, 'rid');
        			allrefresh(this, re);
        		}
		});
        $('#selected_shebei').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_shebei + '&rid='+ $(this).val();
        			var linkurl_list_rid = linkurl + '&rid='+ $(this).val();
        			//console.log(linkurl_rid);
        			allrefresh(this, re);
        			callshebei(linkurl_rid, 'rid');
        			refresh(linkurl_list_rid, 'shebei_list', this, re);

        		}

		});
        $('#selected_reg').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_reg + '&rid='+ $(this).val();
        			var linkurl_list_rid = linkurl + '&rid='+ $(this).val();
        			//console.log(linkurl_rid);
        			allrefresh(this, re);
        			callreg(linkurl_rid, 'rid');
        			refresh(linkurl_list_rid, 'reg_list', this, re);

        			$('#choose_yesterday_reg').removeClass('am-hide');
        			$('#choose_week_reg').removeClass('am-hide');
        			$('#choose_month_reg').removeClass('am-hide');
        			$('input[name="reg_time"]').val($(this).val());

        		}

		});
        $('#selected_charge').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_charge + '&rid='+ $(this).val();
        			var linkurl_list_rid = linkurl + '&rid='+ $(this).val();
        			//console.log(linkurl_rid);
        			allrefresh(this, re);
        			callcharge(linkurl_rid, 'rid');
        			refresh(linkurl_list_rid, 'charge_list', this, re);
        			$('#choose_yesterday_charge').removeClass('am-hide');
        			$('#choose_week_charge').removeClass('am-hide');
        			$('#choose_month_charge').removeClass('am-hide');
        			$('input[name="charge_time"]').val($(this).val());
        		}

		});
        $('#selected_votes').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_votes + '&rid='+ $(this).val();
        			allrefresh(this, re);
        			callvotes(linkurl_rid, 'rid');
        			$('input[name="votes_time"]').val($(this).val());
        		}

		});
        $('#selected_comment').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_comment + '&rid='+ $(this).val();
        			//console.log(linkurl_rid);
        			allrefresh(this, re);
        			callcomment(linkurl_rid, 'rid');
        			$('#choose_yesterday_comment').removeClass('am-hide');
        			$('#choose_week_comment').removeClass('am-hide');
        			$('#choose_month_comment').removeClass('am-hide');
        			$('input[name="comment_time"]').val($(this).val());
        		}

		});
        $('#selected_share').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_share + '&rid='+ $(this).val();
        			//console.log(linkurl_rid);
        			allrefresh(this, re);
        			callshare(linkurl_rid, 'rid');
        			$('#choose_yesterday_share').removeClass('am-hide');
        			$('#choose_week_share').removeClass('am-hide');
        			$('#choose_month_share').removeClass('am-hide');
        			$('input[name="share_time"]').val($(this).val());
        		}

		});
        $('#selected_fans').on('change', function(re) {
        		if ($(this).val() != '' || $(this).val() !=null || $(this).val() != undefined) {
        			var linkurl_rid = linkurl_fans + '&rid='+ $(this).val();
        			allrefresh(this, re);
        			callfans(linkurl_rid, 'rid');
        			$('input[name="fans_time"]').val($(this).val());
        		}

		});
	},
    // ===============================================
    //
    // ===============================================
    'chart': function chartData() {
    }
}
function choose_time_reg(linkurl,th, type, re) {
	$('.c_reg').removeClass('blue');
	$('.c_reg').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="reg_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callreg(linkurl_rid, type);
}
function choose_time_charge(linkurl,th, type, re) {
	$('.c_charge').removeClass('blue');
	$('.c_charge').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="charge_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callcharge(linkurl_rid, type);
}
function choose_time_votes(linkurl,th, type, re) {
	$('.c_votes').removeClass('blue');
	$('.c_votes').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="vote_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callvotes(linkurl_rid, type);
}
function choose_time_comment(linkurl,th, type, re) {
	$('.c_comment').removeClass('blue');
	$('.c_comment').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="comment_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callcomment(linkurl_rid, type);
}
function choose_time_share(linkurl,th, type, re) {
	$('.c_share').removeClass('blue');
	$('.c_share').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="share_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callshare(linkurl_rid, type);
}
function choose_time_fans(linkurl,th, type, re) {
	$('.c_fans').removeClass('blue');
	$('.c_fans').removeClass('blue-on');
	$(th).addClass('blue-on');
	var rid = $('input[name="fans_time"]').val();
	var linkurl_rid = linkurl + '&rid='+ rid;
	allrefresh(th, re);
	callfans(linkurl_rid, type);
}


function allrefresh(to, re) {
	re.preventDefault();
    $("<div class='refresh-block'><span class='refresh-loader'><i class='fa fa-spinner fa-spin'></i></span></div>").appendTo($(to).parents('.actions-btn'));
    setTimeout(function() {
        $('.refresh-block').remove();
    }, 1000);
}
function refresh(linkurl,  type, to, re) {
	re.preventDefault();
    $("<div class='refresh-block'><span class='refresh-loader'><i class='fa fa-spinner fa-spin'></i></span></div>").appendTo($(to).parents('.actions-btn'));
    getDate(linkurl, type);
    setTimeout(function() {
        $('.refresh-block').remove();
    }, 1000);
}
function getDate(linkurl, type) {
	$.post(linkurl,{"type":type},function(data){
		$('#html_'+type).html('');
		$('#html_'+type).prepend(data.html);
	 },"json");
}

function getalldate(linkurl) {
	    $.post(linkurl,{"type":''},function(alldate){
        var echartsalldata = echarts.init(document.getElementById('tpl-alldata'));
        //console.log(alldate[0]);
        option = {

            tooltip: {
                trigger: 'axis',
            },
            legend: {
                data: alldate[0].tname
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: [{
                type: 'category',
                boundaryGap: true,
                data: alldate[0].title
            }],

            yAxis: [{
                type: 'value'
            }],
            series: [
            		{
                    name: alldate[0].tname[0],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[0]['value'],
                    itemStyle: {
                        normal: {
                            color: '#32c5d2'
                        },
                        emphasis: {

                        }
                    }
                },

                {
                    name: alldate[0].tname[1],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[1]['value'],
                    itemStyle: {
                        normal: {
                            color: '#3598dc'
                        }
                    }
                },
                {
                    name: alldate[0].tname[2],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[2]['value'],
                    itemStyle: {
                        normal: {
                            color: '#e7505a'
                        }
                    }
                },
                {
                    name: alldate[0].tname[3],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[3]['value'],
                    itemStyle: {
                        normal: {
                            color: '#F37B1D'
                        }
                    }
                },
                {
                    name: alldate[0].tname[4],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[4]['value'],
                    itemStyle: {
                        normal: {
                            color: '#8E44AD'
                        }
                    }
                },
                {
                    name: alldate[0].tname[5],
                    type: 'line',
                    stack: '总量',
                    areaStyle: { normal: {} },
                    data: alldate[0].value[5]['value'],
                    itemStyle: {
                        normal: {
                            color: '#1989fa'
                        }
                    }
                }
            ]
        };
        echartsalldata.setOption(option);
    },"json");
}

function getshebei(linkurl) {
	$.post(linkurl,{"type":''},function(shebeidate){
        //var piePatternSrc = '';
		//var bgPatternSrc = '#ffffff';
		var shebei = shebeidate;
		//console.log(shebei);
		//var piePatternImg = new Image();
		//piePatternImg.src = piePatternSrc;
		//var bgPatternImg = new Image();
		//bgPatternImg.src = bgPatternSrc;

		var itemStyle = {
		    normal: {
		        opacity: 0.7,
		        color: '#235894',
		        borderWidth: 3,
		        borderColor: '#235894'
		    }
		};
		var echartsShebei = echarts.init(document.getElementById('tpl-shebei'));
		option = {
		    backgroundColor: 'rgba(255, 255, 255, 0)',
		    title: {
		        text: '',
		        textStyle: {
		            color: '#235894'
		        }
		    },
		    tooltip: {},
		    series: [{
		        name: '设备统计',
		        type: 'pie',
		        selectedMode: 'single',
		        selectedOffset: 30,
		        clockwise: true,
		        label: {
		            normal: {
		                textStyle: {
		                    fontSize: 18,
		                    color: '#235894'
		                }
		            }
		        },
		        labelLine: {
		            normal: {
		                lineStyle: {
		                    color: '#235894'
		                }
		            }
		        },
		        data:[
		            {value:shebei.Android, name:'Android'},
		            {value:shebei.iPhone, name:'iPhone'},
		            {value:shebei.other, name:'Other'}
		        ],
		        itemStyle: itemStyle
		    }]
		};
        echartsShebei.setOption(option);
    },"json");
}

function getearth(linkurl) {
	$.post(linkurl,{"type":''},function(earthdate){
        var echartsearth = echarts.init(document.getElementById('tpl-echarts-earth'));

		//console.log(earthdate);
		var name_title = maptitle
		var subname = ''
		var nameColor = " #ffffff"
		var name_fontFamily = '等线'
		var subname_fontSize = 15
		var name_fontSize = 18
		var mapName = 'china'
		//earth = eval("(" + data + ")");
		var data = earthdate[0].earth;
		var maps = eval("(" + earthdate[0].maps + ")");

		//var data = earth.parseJSON();
		//var data = [JSON.parse(earth)];

		//console.log(earthdate[0].earth);
		var geoCoordMap = maps
		var toolTipData = earthdate[0].tooltip;
		/*获取地图数据*
		echartsearth.showLoading();
		var mapFeatures = echarts.getMap(mapName).geoJson.features;
		echartsearth.hideLoading();
		mapFeatures.forEach(function(v) {
		    // 地区名称
		    var name = v.properties.name;
		    // 地区经纬度
		    geoCoordMap[name] = v.properties.cp;

		});*/

		// console.log("============geoCoordMap===================")
		// console.log(geoCoordMap)
		// console.log("================data======================")
		//console.log(data)
		//console.log(toolTipData)
		var max = 480,
		    min = 1; // todo
		var maxSize4Pin = 100,
		    minSize4Pin = 50;

		var convertData = function(data) {
		    var res = [];
		    for (var i = 0; i < data.length; i++) {
		        var geoCoord = geoCoordMap[data[i].name];
		        if (geoCoord) {
		            res.push({
		                name: data[i].name,
		                value: geoCoord.concat(data[i].value),
		            });
		        }
		    }
		    return res;
		};
		option = {
			backgroundColor: '#404a59',
		    title: {
		        text: name_title,
		        subtext: subname,
		        x: 'center',
		        textStyle: {
		            color: nameColor,
		            fontFamily: name_fontFamily,
		            fontSize: name_fontSize
		        },
		        subtextStyle:{
		            fontSize:subname_fontSize,
		            fontFamily:name_fontFamily
		        }
		    },
		    tooltip: {
		        trigger: 'item',
		        formatter: function(params) {
		            if (typeof(params.value)[2] == "undefined") {
		                var toolTiphtml = ''
		                for(var i = 0;i<toolTipData.length;i++){
		                    if(params.name==toolTipData[i].name){
		                        toolTiphtml += toolTipData[i].name+':<br>'
		                        for(var j = 0;j<toolTipData[i].value.length;j++){
		                            toolTiphtml+=toolTipData[i].value[j].name+':'+toolTipData[i].value[j].value+"<br>"
		                        }
		                    }
		                }
		                //console.log(toolTiphtml)
		                // console.log(convertData(data))
		                return toolTiphtml;
		            } else {
		                var toolTiphtml = ''
		                for(var i = 0;i<toolTipData.length;i++){
		                    if(params.name==toolTipData[i].name){
		                        toolTiphtml += toolTipData[i].name+':<br>'
		                        for(var j = 0;j<toolTipData[i].value.length;j++){
		                            toolTiphtml+=toolTipData[i].value[j].name+':'+toolTipData[i].value[j].value+"<br>"
		                        }
		                    }
		                }
		                //console.log(toolTiphtml)
		                // console.log(convertData(data))
		                return toolTiphtml;
		            }
		        }
		    },
		     legend: {
		         orient: 'vertical',
		         y: 'bottom',
		         x: 'right',
		         data: ['credit_pm2.5'],
		         textStyle: {
		             color: '#fff'
		         }
		     },
		    visualMap: {
		        show: true,
		        min: 0,
		        max: 100,
		        left: 'left',
		        top: 'bottom',
		        text: ['高', '低'], // 文本，默认为数值文本
		        textStyle: {
		             color: '#fff'
		        },
		        calculable: true,
		        seriesIndex: [1],
		        inRange: {
		            // color: ['#3B5077', '#031525'] // 蓝黑
		             //color: ['#ffc0cb', '#800080'] // 红紫
		            // color: ['#3C3B3F', '#605C3C'] // 黑绿
		            // color: ['#0f0c29', '#302b63', '#24243e'] // 黑紫黑
		            // color: ['#23074d', '#cc5333'] // 紫红
		            //color: ['#00467F', '#A5CC82'] // 蓝绿
		            // color: ['#1488CC', '#2B32B2'] // 浅蓝
		             color: ['#323d48', '#8E44AD'] // 红白

		        }
		    },
		    /*工具按钮组*/
		     toolbox: {
		         show: true,
		         orient: 'vertical',
		         right: 20,
		         top: 'center',
		         itemGap:20,
		         feature: {
		             dataView: {
		                readOnly: false
		             },
		             restore: {},
		             saveAsImage: {}
		         }
		     },
		    geo: {
		        show: true,
		        map: mapName,
		        label: {
		            normal: {
		                show: false
		            },
		            emphasis: {
		                show: false,
		            }
		        },
		        roam: true,
		        itemStyle: {
		            normal: {
		                areaColor: '#323d48',
		                borderColor: '#3B5077',
		            },
		            emphasis: {
		                areaColor: '#8E44AD',
		            }
		        }
		    },
		    series: [{
		            name: '坐标及数据',
		            type: 'scatter',
		            coordinateSystem: 'geo',
		            data: convertData(data),
		            symbolSize: function(val) {
		                return val[2] / 1000;
		            },
		            label: {
		                normal: {
		                    formatter: '{b}',
		                    position: 'right',
		                    show: true
		                },
		                emphasis: {
		                    show: true
		                }
		            },
		            itemStyle: {
		                normal: {
		                    color: '#05C3F9'
		                }
		            }
		        },
		        {
		        		name:'数据量',
		            type: 'map',
		            map: mapName,
		            geoIndex: 0,
		            aspectScale: 0.75, //长宽比
		            showLegendSymbol: false, // 存在legend时显示
		            label: {
		                normal: {
		                    show: true
		                },
		                emphasis: {
		                    show: false,
		                    textStyle: {
		                        color: '#fff'
		                    }
		                }
		            },
		            roam: true,
		            itemStyle: {
		                normal: {
		                    areaColor: '#031525',
		                    borderColor: '#3B5077',
		                },
		                emphasis: {
		                    areaColor: '#2B91B7'
		                }
		            },
		            animation: false,
		            data: data
		        },
		        {
		            name: '点',
		            type: 'scatter',
		            coordinateSystem: 'geo',
		            symbol: 'pin', //气泡
		            symbolSize: function(val) {
		                var a = (maxSize4Pin - minSize4Pin) / (max - min);
		                var b = minSize4Pin - a * min;
		                b = maxSize4Pin - a * max;
		                return a  + b;
		            },
		            label: {
		                normal: {
		                    show: true,
		                    	formatter: function(params) {
		                         return params.data.value[2]
		                    	},
		                    textStyle: {
		                        color: '#fff',
		                        fontSize: 12,
		                    }
		                }
		            },
		            itemStyle: {
		                normal: {
		                    color: '#F62157', //标志颜色
		                }
		            },
		            zlevel: 6,
		            data: convertData(data),
		        },
		        {
		            name: 'Top 5',
		            type: 'effectScatter',
		            coordinateSystem: 'geo',
		            data: convertData(data.sort(function(a, b) {
		                return b.value - a.value;
		            }).slice(0, 5)),
		            symbolSize: function(val) {
		            		//console.log(val);
		                return (val[2] / val[2])*20;
		            },
		            showEffectOn: 'render',
		            rippleEffect: {
		                brushType: 'stroke'
		            },
		            hoverAnimation: true,
		            label: {
		                normal: {
		                    formatter: '{b}',
		                    position: 'right',
		                    show: true
		                }
		            },
		            itemStyle: {
		                normal: {
		                    color: '#8E44AD',
		                    shadowBlur:15,
		                    shadowColor: '#8E44AD'
		                }
		            },
		            zlevel: 1
		        },

		    ]
		};
    		echartsearth.setOption(option);
	},"json");
}

function calldata(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(alldate, textStatus) {

				$('#notice_alldata').html('');
		        var echartsalldata = echarts.init(document.getElementById('tpl-alldata'));
		       	var title = eval("(" + alldate[0].title + ")");
		       	var tname = eval("(" + alldate[0].tname + ")");
		       	var values = eval("(" + alldate[0].value + ")");
		        //console.log(alldate);

		        var series=[];
		        var color = ["#32c5d2", "#1989fa","#3598dc","#e7505a","#F37B1D","#8E44AD", "#F37B1D","#32c5d2","#8E44AD","#1989fa"];
			    for(var i = 0;i < values.length;i++){
			        series.push({
			            name: tname[i],
	                    type: alldate[0].linetype,
	                    stack: '总量',
	                    areaStyle: { normal: {} },
	                    data: values[i].value,
	                    itemStyle: {
	                        normal: {
	                            color: color[i]
	                        }
	                    }
			        });
			    }
		        option = {

				    tooltip: {
				        trigger: "axis",
				        axisPointer: { // 坐标轴指示器，坐标轴触发有效
				            type: 'line', // 默认为直线，可选为：'line' | 'shadow'
				            lineStyle: {
				                color: '#57617B'
				            }
				        },
				        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
				        backgroundColor: 'rgba(0,0,0,0.7)', // 背景
				        padding: [8, 10], //内边距
				        extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
				    },
				    toolbox: {
				        feature: {
				            saveAsImage: {}
				        }
				    },
		            legend: {
		                data: tname
		            },
		            grid: {
		                left: '3%',
		                right: '4%',
		                bottom: '3%',
		                containLabel: true
		            },
		            xAxis: [{
		                type: 'category',
   						boundaryGap : false,
		                data: title
		            }],

		            yAxis: [{
		                type: 'value'
		            }],
				    "dataZoom": [{
				        "show": true,
				        "height": 30,
				        "xAxisIndex": [
				            0
				        ],
				        bottom: 30,
				        "start": 10,
				        "end": 80,
				        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
				        handleSize: '110%',
				        handleStyle:{
				            color:"#d3dee5",

				        },
				           textStyle:{
				            color:"#fff"},
				           borderColor:"#90979c"


				    }, {
				        "type": "inside",
				        "show": true,
				        "height": 15,
				        "start": 1,
				        "end": 35
				    }],
		            series: series
		        };
		        echartsalldata.setOption(option);
		},
		complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_alldata').html(htmldes);
		}
	});
}
function callshebei(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(shebei, textStatus) {
			var shebei = eval("(" + shebei + ")");
			//console.log(shebei);
			$('#shebeiaa').html('');
			//$('#tpl-shebei').html('');
			var itemStyle = {
			    normal: {
			        opacity: 0.7,
			        color: '#235894',
			        borderWidth: 3,
			        borderColor: '#235894'
			    }
			};
			var echartsShebei = echarts.init(document.getElementById('tpl-shebei'));
			option = {
			    backgroundColor: 'rgba(255, 255, 255, 0)',
			    title: {
			        text: '',
			        textStyle: {
			            color: '#235894'
			        }
			    },
			    tooltip: {},
			    toolbox: {
			        feature: {
			            saveAsImage: {}
			        }
			    },
			    series: [{
			        name: '设备统计',
			        type: 'pie',
			        selectedMode: 'single',
			        selectedOffset: 30,
			        clockwise: true,
			        label: {
			            normal: {
			                textStyle: {
			                    fontSize: 18,
			                    color: '#235894'
			                }
			            }
			        },
			        labelLine: {
			            normal: {
			                lineStyle: {
			                    color: '#235894'
			                }
			            }
			        },
			        data:shebei,
			        itemStyle: itemStyle,
                    animationType: 'scale',
		            animationEasing: 'elasticOut',
		            animationDelay: function (idx) {
		                return Math.random() * 200;
		            }
			    }]
			};
		        echartsShebei.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);
			//alert("暂无设备");
			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无设备</h2></div></div>';
			$('#shebeiaa').html(htmldes);
		}
	});
}

function callreg(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_reg').html('');
			var ysh = eval("(" + data[0].ysh + ")");
			var wsh = eval("(" + data[0].wsh + ")");
			var wcl = eval("(" + data[0].wcl + ")");
			var title = eval("(" + data[0].title + ")");
			//console.log(data);

			var echartsReg = echarts.init(document.getElementById('tpl-reg'));

			option = {
			   "title": {
			      "text": "报名数据统计",
			      "left": "center",
			      "y": "10",
			      "textStyle": {
			        "color": "#fff"
			      }
			    },
			    "backgroundColor": "#1c2e40",
			    "color": "#384757",
			    "tooltip": {
			      "trigger": "axis",
			      "axisPointer": {
			        "type": "cross",
			        "crossStyle": {
			          "color": "#384757"
			        }
			      }
			    },
			    "legend": {
			      "data": [
			        {
			          "name": "未审核",
			          "icon": "circle",
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        },
			        {
			          "name": "已审核",
			          "icon": "circle",
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        },
			        {
			          "name": "完成率",
			          "icon": "circle",
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        }
			      ],
			      "top": "10%",
			      "textStyle": {
			        "color": "#fff"
			      }
			    },
			    "tooltip": {
			        "trigger": "axis",
			        "axisPointer": { // 坐标轴指示器，坐标轴触发有效
			            "type": 'line', // 默认为直线，可选为：'line' | 'shadow'
			            "lineStyle": {
			                "color": '#57617B'
			            }
			        },
			        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
			        "backgroundColor": 'rgba(0,0,0,0.7)', // 背景
			        "padding": [8, 10], //内边距
			        "extraCssText": 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
			    },
			    "toolbox": {
			        "feature": {
			            "saveAsImage": {}
			        }
			    },
			    "xAxis": [
			      {
			        "type": "category",
			        "data":title,
			        "axisPointer": {
			          "type": "shadow"
			        },
			        "axisLabel": {
			          "show": true,
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        }
			      }
			    ],
			    "yAxis": [
			      {
			        "type": "value",
			        "name": "报名数",
			        "nameTextStyle": {
			          "color": "#7d838b"
			        },

			        "axisLabel": {
			          "show": true,
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        },
			        "axisLine": {
			          "show": true
			        },
			        "splitLine": {
			          "lineStyle": {
			            "color": "#7d838b"
			          }
			        }
			      },
			      {
			        "type": "value",
			        "name": "完成率",
			        "show": true,
			        "axisLabel": {
			          "show": true,
			          "textStyle": {
			            "color": "#7d838b"
			          }
			        }
			      }
			    ],
			    "grid": {
			      "top": "20%"
			    },
			    "dataZoom": [{
			        "show": true,
			        "height": 30,
			        "xAxisIndex": [
			            0
			        ],
			        bottom: 30,
			        "start": 10,
			        "end": 80,
			        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
			        handleSize: '110%',
			        handleStyle:{
			            color:"#d3dee5",

			        },
			           textStyle:{
			            color:"#fff"},
			           borderColor:"#90979c"


			    }, {
			        "type": "inside",
			        "show": true,
			        "height": 15,
			        "start": 1,
			        "end": 35
			    }],
			    "series": [
			      {
			        "name": "未审核",
			        "type": "bar",
			        "data": wsh,
			        "barWidth": "auto",
			        "itemStyle": {
			          "normal": {
			            "color": {
			              "type": "linear",
			              "x": 0,
			              "y": 0,
			              "x2": 0,
			              "y2": 1,
			              "colorStops": [
			                {
			                  "offset": 0,
			                  "color": "rgba(255,37,117,0.7)"
			                },
			                {
			                  "offset": 0.5,
			                  "color": "rgba(0,133,245,0.7)"
			                },
			                {
			                  "offset": 1,
			                  "color": "rgba(0,133,245,0.3)"
			                }
			              ],
			              "globalCoord": false
			            }
			          }
			        }
			      },
			      {
			        "name": "已审核",
			        "type": "bar",
			        "data": ysh,
			        "barWidth": "auto",
			        "itemStyle": {
			          "normal": {
			            "color": {
			              "type": "linear",
			              "x": 0,
			              "y": 0,
			              "x2": 0,
			              "y2": 1,
			              "colorStops": [
			                {
			                  "offset": 0,
			                  "color": "rgba(255,37,117,0.7)"
			                },
			                {
			                  "offset": 0.5,
			                  "color": "rgba(0,255,252,0.7)"
			                },
			                {
			                  "offset": 1,
			                  "color": "rgba(0,255,252,0.3)"
			                }
			              ],
			              "globalCoord": false
			            }
			          }
			        },
			        "barGap": "0"
			      },
			      {
			        "name": "完成率",
			        "type": "line",
			        "yAxisIndex": 1,
			        "data": wcl,
			        "itemStyle": {
			          "normal": {
			            "color": "#ffaa00"
			          }
			        },
			        "smooth": true
			      }
			    ]
			};

		    echartsReg.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);
			alert("错误");

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_reg').html(htmldes);
		}
	});
}

function callcharge(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_charge').html('');
			var datas = eval("(" + data[0].data + ")");
			//var wsh = eval("(" + data[0].wsh + ")");
			//var wcl = eval("(" + data[0].wcl + ")");
			var title = eval("(" + data[0].title + ")");
			//console.log(data);

			var echartsCharge = echarts.init(document.getElementById('tpl-charge'));

			var color = ['#e7505a', '#F37B1D'];
			var name = ['充值(元)', '礼物数'];
			var data = datas;

			var series = [];
			for (var i = 0; i < 2; i++) {
			    series.push({
			        name: name[i],
			        type: "line",
			        symbolSize: 10,//标记的大小，可以设置成诸如 10 这样单一的数字，也可以用数组分开表示宽和高，例如 [20, 10] 表示标记宽为20，高为10[ default: 4 ]
			        symbol: 'circle',//标记的图形。ECharts 提供的标记类型包括 'circle', 'rect', 'roundRect', 'triangle', 'diamond', 'pin', 'arrow'
			        smooth: true, //是否平滑曲线显示
			        showSymbol: false, //是否显示 symbol, 如果 false 则只有在 tooltip hover 的时候显示
			        areaStyle: {
			            normal: {
			                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
			                    offset: 0,
			                    color: color[i]
			                }, {
			                    offset: 0.8,
			                    color: 'rgba(255,255,255,0)'
			                }], false),
			                // shadowColor: 'rgba(255,255,255, 0.1)',
			                shadowBlur: 10,
			                opacity:0.3,
			            }
			        },
			        itemStyle: {
			            normal: {
			                color: color[i],
			                lineStyle: {
			                    width: 1,
			                    type: 'solid' //'dotted'虚线 'solid'实线
			                },
			                borderColor: color[i], //图形的描边颜色。支持的格式同 color
			                borderWidth: 8 ,//描边线宽。为 0 时无描边。[ default: 0 ]
			                barBorderRadius: 0,
			                label: {
			                    show: false,
			                },
			                opacity:0.5,
			            }
			        },
			        data: data[i],

			    })
			}
			option = {
			    backgroundColor: "#141f56",
			    legend: {
			        top: 20,
			            itemGap:5,
			            itemWidth:5,
			            textStyle: {
			                color: '#fff',
			                fontSize: '10'
			            },
			            data: name
			    },
			    title: {
			        text: "充值及礼物趋势",
			        textStyle: {
			            color: '#fff',
			            fontSize: '22',
			            fontWeight: 'normal',
			        },
			        subtextStyle: {
			            color: '#90979c',
			            fontSize: '16',

			        },
			    },
			    toolbox: {
			        feature: {
			            saveAsImage: {}
			        }
			    },
			    tooltip: {
			        trigger: "axis",
			        axisPointer: { // 坐标轴指示器，坐标轴触发有效
			            type: 'line', // 默认为直线，可选为：'line' | 'shadow'
			            lineStyle: {
			                color: '#57617B'
			            }
			        },
			        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
			        backgroundColor: 'rgba(0,0,0,0.7)', // 背景
			        padding: [8, 10], //内边距
			        extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
			    },
			    grid: {
			        borderWidth: 0,
			        top: 110,
			        bottom: 95,
			        textStyle: {
			            color: "#fff"
			        }
			    },
			    xAxis: [{
			        type: "category",
			        axisLine: {
			            lineStyle: {
			                color: '#32346c'
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        boundaryGap: false, //坐标轴两边留白策略，类目轴和非类目轴的设置和表现不一样
			        axisTick: {
			            show: false
			        },
			        splitArea: {
			            show: false
			        },
			        axisLabel: {
			            inside: false,
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			        },
			        data: title,
			    }],
			    yAxis: {
			        type: 'value',
			        axisTick: {
			            show: false
			        },
			        axisLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c',
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        axisLabel: {
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			            formatter: '{value}',
			        },
			    },
			    "dataZoom": [{
			        "show": true,
			        "height": 30,
			        "xAxisIndex": [
			            0
			        ],
			        bottom: 30,
			        "start": 10,
			        "end": 80,
			        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
			        handleSize: '110%',
			        handleStyle:{
			            color:"#d3dee5",

			        },
			           textStyle:{
			            color:"#fff"},
			           borderColor:"#90979c"


			    }, {
			        "type": "inside",
			        "show": true,
			        "height": 15,
			        "start": 1,
			        "end": 35
			    }],
			    series: series,
			}
		    echartsCharge.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			console.log(e);

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_charge').html(htmldes);
		}
	});
}

function callvotes(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_votes').html('');
			var mapdata = eval("(" + data[0].mapdata + ")");
			//var wsh = eval("(" + data[0].wsh + ")");
			//var wcl = eval("(" + data[0].wcl + ")");
			//var title = eval("(" + data[0].title + ")");
			var maps = eval("(" + data[0].maps + ")");
			//console.log(mapdata);
			var mapName = 'china'

			var echartsVotes = echarts.init(document.getElementById('tpl-votes'));
			var geoCoordMap = maps;
				//console.log(geoCoordMap);

				var BJData = mapdata;
				var convertData = function(data) {
				    var res = [];
				    for (var i = 0; i < data.length; i++) {
				        var dataItem = data[i];
				        var fromCoord = geoCoordMap[dataItem[0].name];
				        var toCoord = geoCoordMap[dataItem[1].name];
				        if (fromCoord && toCoord) {
				            res.push([{
				                fromName: dataItem[0].name,
				                toName: dataItem[1].name,
				                coord: fromCoord,
				                value: dataItem[0].value
				            }, {
				                coord: toCoord,
				            }]);
				        }
				    }
				    return res;
				};

				var color = ['#a6c84c', '#ffa022', '#46bee9'];
				var series = [];
				[
				    [data[0].mainname, BJData]
				].forEach(function(item, i) {
				    series.push(

				        {//投票路线
				            type: 'lines',
				            zlevel: 2,
				            effect: {
				                show: true,
				                period: 4,
				                trailLength: 0.02,
				                symbol: 'arrow',
				                symbolSize: 5,
				            },
				            lineStyle: {
				                normal: {
				                    width: 1,
				                    opacity: 0.6,
				                    curveness: 0.2
				                }
				            },

				            data: convertData(item[1])
				        },
//				        {
//				        		name:'数据量',
//				            type: 'map',
//				            map: '',
//				            geoIndex: 0,
//				            aspectScale: 0.75, //长宽比
//				            showLegendSymbol: false, // 存在legend时显示
//				            label: {
//				                normal: {
//				                    show: false
//				                },
//				                emphasis: {
//				                    show: false,
//				                    textStyle: {
//				                        color: '#fff'
//				                    }
//				                }
//				            },
//				            roam: false,
//				            itemStyle: {
//				                normal: {
//				                    areaColor: '#fff',
//				                    borderColor: '#fff',
//				                },
//				                emphasis: {
//				                    areaColor: '#fff'
//				                }
//				            },
//				            animation: false,
//				            data: item[1].map(function(dataItem) {
//				                return {
//				                    name: dataItem[0].name,
//				                    value: geoCoordMap[dataItem[0].name].concat([dataItem[0].value])
//				                };
//				            }),
//				        },

				        {//投票点
				            type: 'effectScatter',
				            coordinateSystem: 'geo',
				            zlevel: 2,
				            rippleEffect: {
				                period: 4,
				                brushType: 'stroke',
				                scale: 4
				            },
				            label: {
				                normal: {
				                    show: true,
				                    position: 'right',
				                    offset: [5, 0],
				                    	formatter: function(params) {
				                         return params.data.name + " : " + params.data.value[2]
				                    	},
				                },
				                emphasis: {
				                    show: true
				                }
				            },
				            symbol: 'circle',
				            symbolSize: function(val) {
				                return 4 + val[2] / val[2];
				            },
				            itemStyle: {
				                normal: {
				                    show: false,
				                    color: '#f00'
				                }
				            },
				            data: item[1].map(function(dataItem) {
				                return {
				                    name: dataItem[0].name,
				                    value: geoCoordMap[dataItem[0].name].concat([dataItem[0].value])
				                };
				            }),
				        },
				        //投票终点
				        {
				            type: 'scatter',
				            coordinateSystem: 'geo',
				            zlevel: 2,
				            rippleEffect: {
				                period: 4,
				                brushType: 'stroke',
				                scale: 4
				            },
				            label: {
				                normal: {
				                    show: true,
				                    position: 'right',
				                    //			                offset:[5, 0],

				                    color: '#00ffff',
				                    formatter: function(params) {
				                         return params.data.name + " : " + params.data.value[2]
				                    	},
				                    textStyle: {
				                        color: "#00ffff"
				                    }
				                },
				                emphasis: {
				                    show: true
				                }
				            },
				            symbol: 'pin',
				            symbolSize: 30,
				            itemStyle: {
				                normal: {
				                    show: true,
				                    color: '#f5f5f5'
				                }
				            },
				            data: [{
				                name: item[0],
				                value: geoCoordMap[item[0]].concat([100]),
				            }],
				        }
				    );
				});

				option = {
				    backgroundColor: '#404a59',

				    visualMap: {
				        min: 0,
				        max: 100,
				        left: 'left',
				        top: 'bottom',
				        text: ['高', '低'], // 文本，默认为数值文本
				        calculable: true,
				        color: ['#ff3333', 'orange', 'yellow', 'lime', 'aqua'],
				        textStyle: {
				            color: '#fff'
				        }
				    },
				      tooltip: {
				          trigger: 'item',
				           formatter:function(params, ticket, callback){
				            //console.log(params);
				            if(params.seriesType=="effectScatter") {
				                return "投票："+"<br />"+params.data.name+" : "+params.data.value[2] + "票";
				            }else if(params.seriesType=="lines"){
				                return params.data.fromName+">"+params.data.toName+"<br />"+params.data.value + "票";
				            }else if(params.seriesType=="map"){
				            		console.log(params);
				            		return "投票："+"<br />"+params.data.name+" : "+params.data.value[2] + "票";
				            }else{
				                //console.log(params);
				                return params.data.name;
				            }
				        }
				      },
				    toolbox: {
				        feature: {
				            saveAsImage: {}
				        }
				    },
				    geo: {
				        map: 'china',
				        label: {
				            emphasis: {
				                show: false
				            }
				        },
				        roam: true,
				        layoutCenter: ['50%', '53%'],
				        layoutSize: "100%",
				          itemStyle: {
				              normal: {
				                  areaColor: '#323c48',
				                  borderColor: '#404a59'
				              },
				              emphasis: {
				                  areaColor: '#2a333d'
				              }
				          }
				    },

				    series: series
				};

		    echartsVotes.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);
			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_votes').html(htmldes);
		}
	});
}

function callcomment(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_comment').html('');
			var datas = eval("(" + data[0].data + ")");
			var title = eval("(" + data[0].title + ")");
			//console.log(data);

			var echartsComment = echarts.init(document.getElementById('tpl-comment'));

			var color = ['#5eb95e', '#ff3433', '#1989fa'];
			var name = ['总数', '未审', '点赞'];
			var data = datas;

			var series = [];
			for (var i = 0; i < 3; i++) {
			    series.push({
			        name: name[i],
			        type: "line",
			        symbolSize: 10,//标记的大小，可以设置成诸如 10 这样单一的数字，也可以用数组分开表示宽和高，例如 [20, 10] 表示标记宽为20，高为10[ default: 4 ]
			        symbol: 'circle',//标记的图形。ECharts 提供的标记类型包括 'circle', 'rect', 'roundRect', 'triangle', 'diamond', 'pin', 'arrow'
			        smooth: true, //是否平滑曲线显示
			        showSymbol: false, //是否显示 symbol, 如果 false 则只有在 tooltip hover 的时候显示
			        areaStyle: {
			            normal: {
			                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
			                    offset: 0,
			                    color: color[i]
			                }, {
			                    offset: 0.8,
			                    color: 'rgba(255,255,255,0)'
			                }], false),
			                // shadowColor: 'rgba(255,255,255, 0.1)',
			                shadowBlur: 10,
			                opacity:0.3,
			            }
			        },
			        itemStyle: {
			            normal: {
			                color: color[i],
			                lineStyle: {
			                    width: 1,
			                    type: 'solid' //'dotted'虚线 'solid'实线
			                },
			                borderColor: color[i], //图形的描边颜色。支持的格式同 color
			                borderWidth: 8 ,//描边线宽。为 0 时无描边。[ default: 0 ]
			                barBorderRadius: 0,
			                label: {
			                    show: false,
			                },
			                opacity:0.5,
			            }
			        },
			        data: data[i],

			    })
			}
			option = {
			    backgroundColor: "#141f56",
			    legend: {
			        top: 20,
			            itemGap:5,
			            itemWidth:5,
			            textStyle: {
			                color: '#fff',
			                fontSize: '10'
			            },
			            data: name
			    },
			    title: {
			        text: "",
			        textStyle: {
			            color: '#fff',
			            fontSize: '22',
			            fontWeight: 'normal',
			        },
			        subtextStyle: {
			            color: '#90979c',
			            fontSize: '16',

			        },
			    },
			    toolbox: {
			        feature: {
			            saveAsImage: {}
			        }
			    },
			    tooltip: {
			        trigger: "axis",
			        axisPointer: { // 坐标轴指示器，坐标轴触发有效
			            type: 'line', // 默认为直线，可选为：'line' | 'shadow'
			            lineStyle: {
			                color: '#57617B'
			            }
			        },
			        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
			        backgroundColor: 'rgba(0,0,0,0.7)', // 背景
			        padding: [8, 10], //内边距
			        extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
			    },
			    grid: {
			        borderWidth: 0,
			        top: 110,
			        bottom: 95,
			        textStyle: {
			            color: "#fff"
			        }
			    },
			    xAxis: [{
			        type: "category",
			        axisLine: {
			            lineStyle: {
			                color: '#32346c'
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        boundaryGap: false, //坐标轴两边留白策略，类目轴和非类目轴的设置和表现不一样
			        axisTick: {
			            show: false
			        },
			        splitArea: {
			            show: false
			        },
			        axisLabel: {
			            inside: false,
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			        },
			        data: title,
			    }],
			    yAxis: {
			        type: 'value',
			        axisTick: {
			            show: false
			        },
			        axisLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c',
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        axisLabel: {
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			            formatter: '{value}',
			        },
			    },
			    "dataZoom": [{
			        "show": true,
			        "height": 30,
			        "xAxisIndex": [
			            0
			        ],
			        bottom: 30,
			        "start": 10,
			        "end": 80,
			        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
			        handleSize: '110%',
			        handleStyle:{
			            color:"#d3dee5",

			        },
			           textStyle:{
			            color:"#fff"},
			           borderColor:"#90979c"


			    }, {
			        "type": "inside",
			        "show": true,
			        "height": 15,
			        "start": 1,
			        "end": 35
			    }],
			    series: series,
			}
		    echartsComment.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_comment').html(htmldes);
		}
	});
}

function callshare(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_share').html('');
			var datas = eval("(" + data[0].data + ")");
			var title = eval("(" + data[0].title + ")");
			//console.log(data);

			var echartsShare = echarts.init(document.getElementById('tpl-share'));

			var color = ['#5eb95e'];
			var name = ['分享数'];
			var data = datas;

			var series = [];
			for (var i = 0; i < 1; i++) {
			    series.push({
			        name: name[i],
			        type: "line",
			        symbolSize: 10,//标记的大小，可以设置成诸如 10 这样单一的数字，也可以用数组分开表示宽和高，例如 [20, 10] 表示标记宽为20，高为10[ default: 4 ]
			        symbol: 'circle',//标记的图形。ECharts 提供的标记类型包括 'circle', 'rect', 'roundRect', 'triangle', 'diamond', 'pin', 'arrow'
			        smooth: true, //是否平滑曲线显示
			        showSymbol: false, //是否显示 symbol, 如果 false 则只有在 tooltip hover 的时候显示
			        areaStyle: {
			            normal: {
			                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
			                    offset: 0,
			                    color: color[i]
			                }, {
			                    offset: 0.8,
			                    color: 'rgba(255,255,255,0)'
			                }], false),
			                // shadowColor: 'rgba(255,255,255, 0.1)',
			                shadowBlur: 10,
			                opacity:0.3,
			            }
			        },
			        itemStyle: {
			            normal: {
			                color: color[i],
			                lineStyle: {
			                    width: 1,
			                    type: 'solid' //'dotted'虚线 'solid'实线
			                },
			                borderColor: color[i], //图形的描边颜色。支持的格式同 color
			                borderWidth: 8 ,//描边线宽。为 0 时无描边。[ default: 0 ]
			                barBorderRadius: 0,
			                label: {
			                    show: false,
			                },
			                opacity:0.5,
			            }
			        },
			        data: data[i],

			    })
			}
			option = {
			    backgroundColor: "#141f56",
			    legend: {
			        top: 20,
			            itemGap:5,
			            itemWidth:5,
			            textStyle: {
			                color: '#fff',
			                fontSize: '10'
			            },
			            data: name
			    },
			    title: {
			        text: "",
			        textStyle: {
			            color: '#fff',
			            fontSize: '22',
			            fontWeight: 'normal',
			        },
			        subtextStyle: {
			            color: '#90979c',
			            fontSize: '16',

			        },
			    },
			    toolbox: {
			        feature: {
			            saveAsImage: {}
			        }
			    },
			    tooltip: {
			        trigger: "axis",
			        axisPointer: { // 坐标轴指示器，坐标轴触发有效
			            type: 'line', // 默认为直线，可选为：'line' | 'shadow'
			            lineStyle: {
			                color: '#57617B'
			            }
			        },
			        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
			        backgroundColor: 'rgba(0,0,0,0.7)', // 背景
			        padding: [8, 10], //内边距
			        extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
			    },
			    grid: {
			        borderWidth: 0,
			        top: 110,
			        bottom: 95,
			        textStyle: {
			            color: "#fff"
			        }
			    },
			    xAxis: [{
			        type: "category",
			        axisLine: {
			            lineStyle: {
			                color: '#32346c'
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        boundaryGap: false, //坐标轴两边留白策略，类目轴和非类目轴的设置和表现不一样
			        axisTick: {
			            show: false
			        },
			        splitArea: {
			            show: false
			        },
			        axisLabel: {
			            inside: false,
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			        },
			        data: title,
			    }],
			    yAxis: {
			        type: 'value',
			        axisTick: {
			            show: false
			        },
			        axisLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c',
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        axisLabel: {
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			            formatter: '{value}',
			        },
			    },
			    "dataZoom": [{
			        "show": true,
			        "height": 30,
			        "xAxisIndex": [
			            0
			        ],
			        bottom: 30,
			        "start": 10,
			        "end": 80,
			        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
			        handleSize: '110%',
			        handleStyle:{
			            color:"#d3dee5",

			        },
			           textStyle:{
			            color:"#fff"},
			           borderColor:"#90979c"


			    }, {
			        "type": "inside",
			        "show": true,
			        "height": 15,
			        "start": 1,
			        "end": 35
			    }],
			    series: series,
			}
		    echartsShare.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_share').html(htmldes);
		}
	});
}

function callfans(linkurl, type) {
	$.ajax({
		type: "post",
		dataType: "json",
		traditional: true,
		data: {
			type: type
		},
		url: linkurl,
		async: false, //表示同步执行
		success: function(data, textStatus) {
			$('#notice_fans').html('');
			var datas = eval("(" + data[0].data + ")");
			var title = eval("(" + data[0].title + ")");
			//console.log(data);

			var echartsFans = echarts.init(document.getElementById('tpl-fans'));

			var color = ['#3598dc'];
			var name = ['粉丝数'];
			var data = datas;

			var series = [];
			for (var i = 0; i < 1; i++) {
			    series.push({
			        name: name[i],
			        type: "line",
			        symbolSize: 10,//标记的大小，可以设置成诸如 10 这样单一的数字，也可以用数组分开表示宽和高，例如 [20, 10] 表示标记宽为20，高为10[ default: 4 ]
			        symbol: 'circle',//标记的图形。ECharts 提供的标记类型包括 'circle', 'rect', 'roundRect', 'triangle', 'diamond', 'pin', 'arrow'
			        smooth: true, //是否平滑曲线显示
			        showSymbol: false, //是否显示 symbol, 如果 false 则只有在 tooltip hover 的时候显示
			        areaStyle: {
			            normal: {
			                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
			                    offset: 0,
			                    color: color[i]
			                }, {
			                    offset: 0.8,
			                    color: 'rgba(255,255,255,0)'
			                }], false),
			                // shadowColor: 'rgba(255,255,255, 0.1)',
			                shadowBlur: 10,
			                opacity:0.3,
			            }
			        },
			        itemStyle: {
			            normal: {
			                color: color[i],
			                lineStyle: {
			                    width: 1,
			                    type: 'solid' //'dotted'虚线 'solid'实线
			                },
			                borderColor: color[i], //图形的描边颜色。支持的格式同 color
			                borderWidth: 8 ,//描边线宽。为 0 时无描边。[ default: 0 ]
			                barBorderRadius: 0,
			                label: {
			                    show: false,
			                },
			                opacity:0.5,
			            }
			        },
			        data: data[i],

			    })
			}
			option = {
			    backgroundColor: "#141f56",
			    legend: {
			        top: 20,
			            itemGap:5,
			            itemWidth:5,
			            textStyle: {
			                color: '#fff',
			                fontSize: '10'
			            },
			            data: name
			    },
			    title: {
			        text: "",
			        textStyle: {
			            color: '#fff',
			            fontSize: '22',
			            fontWeight: 'normal',
			        },
			        subtextStyle: {
			            color: '#90979c',
			            fontSize: '16',

			        },
			    },
			    toolbox: {
			        feature: {
			            saveAsImage: {}
			        }
			    },
			    tooltip: {
			        trigger: "axis",
			        axisPointer: { // 坐标轴指示器，坐标轴触发有效
			            type: 'line', // 默认为直线，可选为：'line' | 'shadow'
			            lineStyle: {
			                color: '#57617B'
			            }
			        },
			        //formatter: '{b}<br />{a0}: {c0}<br />{a1}: {c1}',
			        backgroundColor: 'rgba(0,0,0,0.7)', // 背景
			        padding: [8, 10], //内边距
			        extraCssText: 'box-shadow: 0 0 3px rgba(255, 255, 255, 0.4);', //添加阴影
			    },
			    grid: {
			        borderWidth: 0,
			        top: 110,
			        bottom: 95,
			        textStyle: {
			            color: "#fff"
			        }
			    },
			    xAxis: [{
			        type: "category",
			        axisLine: {
			            lineStyle: {
			                color: '#32346c'
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        boundaryGap: false, //坐标轴两边留白策略，类目轴和非类目轴的设置和表现不一样
			        axisTick: {
			            show: false
			        },
			        splitArea: {
			            show: false
			        },
			        axisLabel: {
			            inside: false,
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			        },
			        data: title,
			    }],
			    yAxis: {
			        type: 'value',
			        axisTick: {
			            show: false
			        },
			        axisLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c',
			            }
			        },
			        splitLine: {
			            show: true,
			            lineStyle: {
			                color: '#32346c ',
			            }
			        },
			        axisLabel: {
			            textStyle: {
			                color: '#bac0c0',
			                fontWeight: 'normal',
			                fontSize: '12',
			            },
			            formatter: '{value}',
			        },
			    },
			    "dataZoom": [{
			        "show": true,
			        "height": 30,
			        "xAxisIndex": [
			            0
			        ],
			        bottom: 30,
			        "start": 10,
			        "end": 80,
			        handleIcon: 'path://M306.1,413c0,2.2-1.8,4-4,4h-59.8c-2.2,0-4-1.8-4-4V200.8c0-2.2,1.8-4,4-4h59.8c2.2,0,4,1.8,4,4V413z',
			        handleSize: '110%',
			        handleStyle:{
			            color:"#d3dee5",

			        },
			           textStyle:{
			            color:"#fff"},
			           borderColor:"#90979c"


			    }, {
			        "type": "inside",
			        "show": true,
			        "height": 15,
			        "start": 1,
			        "end": 35
			    }],
			    series: series,
			}
		    echartsFans.setOption(option);


	    },
	    complete: function(XMLHttpRequest, textStatus) {},
		error: function(e) {
			//console.log(e);

			var htmldes = '<div class="am-form-group am-vertical-align-middle" style="    width: 100%;"><div class="am-u-sm-12 am-zwsb color_F89135"><h2><span class="am-icon-exclamation-triangle"></span>暂无数据</h2></div></div>';
			$('#notice_fans').html(htmldes);
		}
	});
}

