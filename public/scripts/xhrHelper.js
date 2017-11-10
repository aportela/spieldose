"use strict";

var jsonHttpRequest = function (method, url, jsonData, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var jsonResult = null;
            try {
                jsonResult = JSON.parse(xhr.responseText);
            } catch (e) {
                console.groupCollapsed("Error parsing JSON response");
                console.log(e);
                console.log(xhr.responseText);
                console.groupEnd();
            } finally {
                callback(xhr.status, jsonResult, jsonResult == null ? xhr.responseText : null, xhr.getAllResponseHeaders());
            }
        }
    }
    xhr.ontimeout = function (e) {
        callback(408, null);
    };
    xhr.send(JSON.stringify(jsonData));
}

var httpRequest = function (request, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(request.method, request.url, true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var response = {
                status: xhr.status,
                statusText: xhr.statusText,
                headers: xhr.getAllResponseHeaders(),
                text: xhr.responseText,
                json: {}
            };
            try {
                if (xhr.getResponseHeader("Content-Type").indexOf("json") != -1) {
                    response.json = JSON.parse(xhr.responseText);
                }
            } catch (e) {
                response.json = {};
            } finally {
                callback(response);
            }
        }
    }
    xhr.ontimeout = function (e) {
        var response = {
            status: 408,
            statusText: "Request Timeout",
            headers: [],
            text: xhr.responseText,
            json: {}
        };
        callback(response);
    };
    xhr.send(JSON.stringify(request.params));
}
