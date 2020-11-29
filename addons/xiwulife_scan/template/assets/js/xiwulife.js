$(document).ready(function () {
    var xiwulife_content= $('.xiwulife_content').outerWidth();
    if($.session.get('xiwulife_right') ==300){
        $('.xiwulife_right').animate({width:'300px'});
        var a ="-webkit-calc(100% - 540px)";
        $('.xiwulife_content').css({'width':a})
    }
    $('.Message_btn').click(function (e) {
        var xiwulife_right = $('.xiwulife_right').outerWidth();
        var xiwulife_content= $('.xiwulife_content').outerWidth();
        if(xiwulife_right == 0){
            $('.xiwulife_right').animate({width:'300px'});
            $('.xiwulife_content').animate({width:xiwulife_content-300+'px'})
            $.session.set('xiwulife_right',300);
        }else{
            $('.xiwulife_right').animate({width:'0px'});
            $('.xiwulife_content').animate({width:xiwulife_content+300+'px'})
            $.session.set('xiwulife_right',0);
        }
    });
});