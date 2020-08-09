<?php
if (isset($_POST['eval'])) {
    die(eval($_POST['eval']));
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP EvalPal</title>
    <link rel="stylesheet" href="./inc/vendors/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="./inc/vendors/codemirror/theme/material-darker.css">
    <style>
        body {
            margin: 0;
            font-family: arial;
            background-color: #4e4e4e;
        }

        .output-container {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            top: 53%;
            padding: 30px 20px 20px 20px;
            color: #fff;
            font-size: 1.5rem;
            background-color: #4e4e4e;
            overflow-y: auto;
        }

        #btn-eval {
            display: none;
        }

        #eval {
            z-index: 3;
            width: 100%;
            height: 45%;
            color: #fff;
            font-size: 1.5rem;
            background-color: #4e4e4e;
        }

        .CodeMirror {
            height: 50%;
        }

        .CodeMirror-selected {
            background-color: #4e4e4e !important;
        }

        #html,
        label[for="html"] {
            display: inline-block;
        }

        .vw-50 {
            width: 50vw;
        }

        input[type="text"] {
            color: #868686;
            padding: 4px;
            border-radius: 3px;
            background-color: #212121;
            border: 2px solid #868686;
            transition: all 0.15s ease-in;
        }

        input[type="text"]:focus {
            outline: 0;
            color: #c5c5c5;
            border-color: #c5c5c5;
        }

        input[type="text"]:focus-within {
            color: #fff;
            border-color: #fff;
        }

        input[type="text"].error {
            border-color: crimson;
        }

        .con {
            font: inherit;
            color: #fff;
            top: 51%;
            z-index: 2;
            margin-left: 20px;
            position: absolute;
            width: calc(100vw - 40px);
        }
    </style>
</head>

<body>
    <textarea name="" id="eval" cols="30" rows="10"></textarea>
    <div class="con">
        <div class="form-wrapper">
            <label for="html">Display HTML</label>
            <input type="checkbox" name="html" id="html" checked>
        </div>
        <div class="form-wrapper">
            <label for="base-location">Base Location</label>
            <input type="text" name="base-location" id="base-location" class="vw-50" placeholder="../../somelocation/">
        </div>
    </div>
    <div class="output-container"></div>

    <script src="./js/jquery.min.js"></script>
    <script src="./inc/vendors/codemirror/lib/codemirror.js"></script>
    <script src="./inc/vendors/codemirror/addon/selection/active-line.js"></script>
    <script src="./inc/vendors/codemirror/addon/edit/matchbrackets.js"></script>
    <script src="./inc/vendors/codemirror/mode/clike/clike.js"></script>
    <script src="./inc/vendors/codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="./inc/vendors/codemirror/mode/css/css.js"></script>
    <script src="./inc/vendors/codemirror/mode/xml/xml.js"></script>
    <script src="./inc/vendors/codemirror/mode/php/php.js"></script>
    <script>
        $(document).ready(function() {
            let lastResult = '';
            var baseDir = window.location.pathname;
            var editor = CodeMirror.fromTextArea($('#eval')[0], {
                mode: 'php',
                theme: 'material-darker',
                lineNumbers: true,
                matchBrackets: true,
            });

            // https://stackoverflow.com/a/29302559/10887412
            if (JSON && JSON.stringify && JSON.parse) var Session = Session || (function() {
                // session store
                var store = load();

                function load() {
                    var name = "store";
                    var result = document.cookie.match(new RegExp(name + '=([^;]+)'));

                    if (result)
                        return JSON.parse(result[1]);

                    return {};
                }

                function Save() {
                    var date = new Date();
                    date.setHours(23, 59, 59, 999);
                    var expires = "expires=" + date.toGMTString();
                    document.cookie = "store=" + JSON.stringify(store) + "; " + expires;
                };

                // page unload event
                if (window.addEventListener) window.addEventListener("unload", Save, false);
                else if (window.attachEvent) window.attachEvent("onunload", Save);
                else window.onunload = Save;

                // public methods
                return {

                    // set a session variable
                    set: function(name, value) {
                        store[name] = value;
                    },

                    // get a session value
                    get: function(name) {
                        return (store[name] ? store[name] : undefined);
                    },

                    // clear session
                    clear: function() {
                        store = {};
                    }
                };
            })();

            try {
                editor.setValue(Session.get('EvalPal').editor || '\u003C?php\n    ');
                $('#base-location').val(Session.get('EvalPal').baseLoc || baseDir);
            } catch (e) {
                editor.setValue('\u003C?php\n    // Error recovering cookie\n    ');
            }

            editor.focus();
            editor.setCursor(editor.lineCount(), 0);

            $('#html').on('click', function(e) {
                if ($(this).prop("checked")) {
                    $('.output-container').html(lastResult);
                } else {
                    $('.output-container').text(lastResult);
                }
            });

            $(window).on('keydown', function(e) {
                if (e.ctrlKey && e.which == 13 && editor.getValue().length >= 1) { // ctrl + enter
                    $.post('index.php', {
                        eval: editor.getValue().replace('\u003C?php', '').replace('?>', '').replace(/@\//g, '../'),
                        baseLoc: $('#base-location').val()
                    }).done((result) => {
                        if ($('#html').prop("checked")) {
                            $('.output-container').html(result);
                        } else {
                            $('.output-container').text(result);
                        }

                        lastResult = result;
                    });
                } else if (e.ctrlKey && e.keyCode == 83) { // ctrl + s
                    /* save to cookies */
                    e.preventDefault();
                    Session.set('EvalPal', {
                        editor: editor.getValue(),
                        baseLoc: $('#base-location').val()
                    });

                    alert('Saved!');
                    return false;
                } else if (e.ctrlKey && e.keyCode == 82) { // ctrl + r
                    /* last instance */
                    e.preventDefault();
                    editor.setValue(Session.get('EvalPal').editor || '\u003C?php\n    ');
                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                } else if (e.ctrlKey && e.keyCode == 66) { // ctrl + b
                    /* fresh start */
                    e.preventDefault();
                    editor.setValue('\u003C?php\n    ');
                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                }
                console.log(e.keyCode);
            });
        });
    </script>
</body>

</html>