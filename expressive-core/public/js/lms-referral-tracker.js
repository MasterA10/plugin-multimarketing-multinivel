/**
 * Referral Tracking Script - Expressive Core
 * Captures ?ref= parameter and stores it in a cookie.
 */
(function() {
    function getQueryParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
           return null;
        }
        return decodeURI(results[1]) || 0;
    }

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    var ref = getQueryParam('ref');
    if (ref) {
        // Save referral for 30 days
        setCookie('exp_ref', ref, 30);
        console.log('Expressive Core: Referral captured -> ' + ref);
    }
})();
