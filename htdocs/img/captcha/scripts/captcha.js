$(function () {
    $("#" + captcha_id + "-image").click(function() {
        $.get(
            callback,
            function (data) {
                if (data == null) {
                    return;
                }
                $("#" + captcha_id + "-image").attr("src", data.image);
                $("#" + captcha_id + "-id").attr("value", data.id);
                return true;
            },
            "json"
        );
        return false;    
    });
});