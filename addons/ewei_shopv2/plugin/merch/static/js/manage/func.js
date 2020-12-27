var ls = window.localStorage;
var GoodsStorage = getGoodsStorage();
if(!jQuery.isEmptyObject(GoodsStorage)){
	var goodsArr=JSON.parse(GoodsStorage);
	$("#goods_table tbody tr").each(function(ind,val){
		var thisId=$(this).children().eq(1).text();
		var thisval=$(this).find('input.lskdo');
		var thischeck=$(this).find('[data-type="checkbox"]');
		goodsArr.map(function(item) {
			if(thisId == item.id){
				thisval.val(item.num)
				thischeck.prop("checked",true);
			}
		})
	})
}

/*
 * 设置选中商品缓存，勾选或者修改商品数量时候调用
 * params - {id: 1, num: 1}
 */  
function setGoodsStroage(params) {
	var goodStorage = ls.getItem('goods');
	var goodsArr = [];
	if (!goodStorage) {
		$('[data-type="checkbox"][value='+params.id+']').prop("checked", true);
		goodsArr.push(params);
	} else {
		var currTime = new Date().getTime();
		var expiredStorage = ls.getItem('goodsExpired');
		if (currTime - expiredStorage > 12*60*60*1000) {			// 超过12小时过期清除
			removeGoodsStorage();
			goodsArr.push(params);
		} else {
			var isInStorage = false;
			goodsArr = JSON.parse(goodStorage);
			goodsArr.map(function(item , val) {
				if (item.id == params.id) {
					if (params.num==0){
						$('[data-type="checkbox"][value='+item.id+']').prop("checked", false);
						goodsArr.splice(val,1);
					}else {
						$('[data-type="checkbox"][value='+item.id+']').prop("checked", true);
						item.num = params.num;
					}
					isInStorage = true;
				}
			})
			if (!isInStorage) {
				$('[data-type="checkbox"][value='+params.id+']').prop("checked", true);
				goodsArr.push(params);
			}
		}
	}
	var expired = new Date().getTime();
	ls.setItem('goodsExpired', expired);
	return ls.setItem('goods', JSON.stringify(goodsArr));
}


// 获取商品缓存
function getGoodsStorage() {
	var goodsData = ls.getItem('goods');
	if (!goodsData) {
		return [];
	}

	return goodsData;
}

//清除单个商品
function removeGoodsOneStorage(){
	var getGoodsStorage = getGoodsStorage();
	var goodsArr = JSON.parse(getGoodsStorage);
}

// 清除商品缓存
function removeGoodsStorage() {
	ls.removeItem('goodsExpired');
	return ls.removeItem('goods');
}

function model_shop() {
	var  goodsData = getGoodsStorage();
	if (jQuery.isEmptyObject(goodsData)){
		tip.msgbox.err('没有选择商品喔！');
		return
	}
	var url=$('#shopurl').val();
	$.ajax({
		url: url,
		type: 'post',
		data: {goods: goodsData},
		dataType: 'json',
		async: false,
		success:function(data){
			if (data.status){
				var item=data.result;
				var options = {"containerName":"material-Modal"};
				var title="确认支付";
				var footer = '<button class="btn btn-danger close2">确认支付</button><button class="btn btn-danger close2">关闭</button>';
				var content = "<div class=\"table-responsive\"></div><table class=\"table\"><caption>商品订单信息</caption>";
					content+="<thead><tr>"+"<td>id</td><td>商品</td><td>数量</td><td>价格</td>"+"</tr></thead>";
					content+="<tbody>"
				var order=item.goodsarr
					order.map(function(index) {
						content+="<tr><td>"+index.id+"</td><td><img src='"+index.img+"' width='75px' height='75px' alt='图片'>"+index.title+"</td><td>x"+index.num+"</td><td>¥"+index.price+"</td></tr>"
					})
					content+="</tbody>"
					content+='</table>';
				var model=util.dialog(title, content, footer,options);
				model.find(".modal-body").css({height:"50%","overflow-y":"auto"});
				model.modal({keyboard:!1});
				model.modal('show');
				model.removeClass("fade");
				model.find(".close2").click(function () {
					model.modal("hide");
				})
			}
		}
	})
	return false;
}
$("body").on("click",".jian",function(){
	var $thisParents=$(this).parents("tr")
	var $lskoe=$thisParents.find(".lskdo")
	var lskoe=$($lskoe).val()*1;
	//当前id
	var thisId=$thisParents.find('td:eq(1)').text();

	if(lskoe-1<0){
		$thisParents.children(".lskdo").val(0)
	} else{
		$($lskoe).val(lskoe-1)
	}
	//创建json数据
	var date={"id":thisId, "num":$lskoe.val()}
	setGoodsStroage(date);
})
$("body").on("click",".jia",function(iiii){
	// 获取商品信息行
	var $thisParents=$(this).parents("tr")
	// 获取输入框
	var lsoek=$thisParents.find(".lskdo");
	// 获取商品数量
	var lskoe=parseInt(lsoek.val());
	//当前id
	var thisId=$thisParents.find('td:eq(1)').text();

	// 设置商品数量
	lsoek.val(lskoe+1);
	//创建json数据
	var date={"id":thisId, "num":lsoek.val()}
	setGoodsStroage(date);
})
var input_time=null
$("body").on('input propertychange','.lskdo',function(){
	clearTimeout(input_time)
	var deox=$(this).val();
	var that=this
	var $thisParents=$(this).parents("tr")
	var thisId=$thisParents.find('td:eq(1)').text()

	if(isNaN(deox)){
		alert("您好,请输入您想购买的数量!");
		$(this).val(1);
		deox=1
	}
	input_time=setTimeout(function(){
		if(!jQuery.isEmptyObject(GoodsStorage)){
			var goodsArr=JSON.parse(GoodsStorage);
			if( JSON.stringify(GoodsStorage ).indexOf(thisId) > -1 ) {
				goodsArr.map(function(item) {
					if (item.id==thisId){
						var date={"id":thisId, "num":deox}
						setGoodsStroage(date);
					}
				})
			} else{
				var date={"id":thisId, "num":deox}
				setGoodsStroage(date);
			}
		} else{
			var date={"id":thisId, "num":deox}
			setGoodsStroage(date);
		}
	},1000)
})
$('body').on('click','[data-type="checkbox"]',function(){
	var  txtalso = $(this).parents("tr").find("input.lskdo").val();
	if($(this).prop("checked")) {
		if(txtalso <= 0) {
			// 设置商品数量
			txtalso = 1;
		} else {
			return;
		}
	} else {
		txtalso = 0;
	}
	var date={"id":$(this).val(), "num":txtalso}
	setGoodsStroage(date);
	$(this).parents("tr").find("input.lskdo").val(txtalso);
});