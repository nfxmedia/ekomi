$(function () {
    
    var cls = "ekomi-filter-action";
    
    $(".ekomi--comments-vote-area-button").click(function () {
        var answer = $(this).attr("data-answer");
        var form = $(this).closest("form");
        var ekomiCont = form.closest(".ekomi--comments-vote-area-cont");
        var thanksTxt = form.find(".thanks-text").html();
        var posting = $.post(window.location.href, form.serialize() + "&answer=" + answer);
        posting.done(function (data) {
            ekomiCont.find(".thanks-text").show();
            form.hide();
        });
        return false;
    });

    $(".ekomi-filter").each(function(){
        var filterVote = $(this).val();
        var reviewEntry = $(this).next();
        var ekomiVoteCont = reviewEntry.next();
        reviewEntry.addClass(filterVote);
        reviewEntry.addClass(cls);
        ekomiVoteCont.addClass(filterVote);
        ekomiVoteCont.addClass(cls);
        if(reviewEntry.hasClass("has--answer")){
            var answerEntry = ekomiVoteCont.next();
            answerEntry.addClass(filterVote);
            answerEntry.addClass(cls);
        }
    });
    
    $(".f_filter").click(function(){
       $(".ekomi-rating-filter").show();
       var filterVal = $(this).attr("data-vote-filter");
       $(".ekomi-rating-filter-result").text($(this).attr("data-vote-num"));
       $("."+cls).hide();
       $("."+filterVal).show();
    });
    
    $("#f_cancelFilter").click(function(){
        $(".ekomi-rating-filter").hide();
        $(".ekomi-filter-action").show();
    });

});