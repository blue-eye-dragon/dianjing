/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

var util_dialog = (function() {

    var __prompt = function(options, fn_ok, fn_cancel) {
        options['onEscape'] = true;
        options['callback'] = function(v) {
            // null means dialog is cancelled
            if (v === null) {
                if (fn_cancel) {
                    fn_cancel();
                }
            } else {
                fn_ok(v);
            }
        };
        return bootbox.prompt(options);
    }

    // show a user text input dialog
    var prompt = function(message, fn_ok, fn_cancel) {
        return bootbox.prompt(message, function(value) {
            // null means dialog is cancelled
            if (value === null) {
                if (fn_cancel) {
                    fn_cancel();
                }
            } else {
                fn_ok(value);
            }
        });
    }

    // will not accept empty input value
    var prompt2 = function(message, fn_ok, fn_cancel) {
        return prompt(message, function(value) {
            if (value === '') {
                alert(find_i18n('e_empty_input', "Error: Input is empty"));
                return;
            }
            fn_ok(value);
        }, fn_cancel);
    }

    var empty_func = function() {}

    // show an alert message dialog
    var alert = function(message) {
        return bootbox.alert(message, empty_func);
    }

    var alert_sql_error = function(errno, error) {
        var message = error;
        if (errno === 1062) {
            var value = error.split("'");
            message = find_i18n('e_duplicate_value', "Error: Cannot use duplicate value");
            if (value.length > 1) {
                value = "&ensp;" + value[1];
                message += value;
            }
        }
        util_dialog.alert(message);
    }

    return {
        alert: alert,
        alert_sql_error: alert_sql_error,
        prompt: prompt,
        prompt2: prompt2
    };

})();