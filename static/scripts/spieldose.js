"use strict";

function clearFieldsWarnings() {
    $("p.control").removeClass("has-icons-right");
    $("p.control input").removeClass("is-danger");
    $("p.control p").remove();
}

function addFieldWarning(containerElement, message) {
    clearFieldsWarnings();
    $(containerElement).addClass("has-icons-right");
    $(containerElement).find("input").addClass("is-danger");
    $(containerElement).append('<span class="icon is-small is-right"><i class="fa fa-warning"></i></span>');
    $(containerElement).append('<p class="help is-danger">' + message + '</p>');
}

$("form#f_signin").submit(function (e) {
    e.preventDefault();
    var xhr = new XMLHttpRequest();
    xhr.open($(this).attr("method"), $(this).attr("action"), true);
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var result = null;
            try {
                result = JSON.parse(xhr.responseText);
            } catch (e) {
                console.groupCollapsed("Error parsing JSON response");
                console.log(e);
                console.log(xhr.responseText);
                console.groupEnd();
            } finally {
                if (result === null) {
                    alert("JSON decode error");
                } else {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        switch(xhr.status) {
                            case 200:
                                addFieldWarning($("p#password-container"), "Invalid password");
                            break;
                            case 404:
                                addFieldWarning($("p#login-container"), "Username not found");
                            break;
                            default:
                                alert("General error");
                            break;
                        }
                    }
                }
            }
        }
    }
    xhr.ontimeout = function (e) {
        alert("Timeout error");
    };
    xhr.send(new FormData($(this)[0]), null, 2);
});

/*
$("a#menu_link_signout").click(function(e) {
    e.preventDefault();
    var xhr = new XMLHttpRequest();
    xhr.open("GET", $(this).attr("href"), true);
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var result = null;
            try {
                result = JSON.parse(xhr.responseText);
            } catch (e) {
                console.groupCollapsed("Error parsing JSON response");
                console.log(e);
                console.log(xhr.responseText);
                console.groupEnd();
            } finally {
                if (result === null) {
                    alert("JSON decode error");
                } else {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert("General error");
                    }
                }
            }
        }
    }
    xhr.ontimeout = function (e) {
        alert("Timeout error");
    };
    xhr.send(null);
});
*/