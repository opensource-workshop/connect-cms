/* ページ上部へ戻る */
$(function(){
    $('#ccFooterArea').prepend('<p id="page-top"><a href="#"><i class="fas fa-arrow-up"></i></a></p>');
    var topBtn = $('#page-top');
    topBtn.hide();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            topBtn.fadeIn();
        } else {
            topBtn.fadeOut();
        }
    });
    topBtn.click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 500);
        return false;
    });
});
