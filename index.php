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
    <link rel="stylesheet" href="./codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="./codemirror/theme/material-darker.css">
    <style>
        body {
            margin: 0;
            font-family: arial;
        }

        .container {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            top: 50%;
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
            color: #fff;
            display: inline-block;
        }

        .con {
            margin-left: 20px;
            top: 51%;
            z-index: 2;
            position: absolute;
        }
    </style>
</head>

<body>
    <textarea name="" id="eval" cols="30" rows="10"></textarea>
    <div class="con">
        <label for="html">Display HTML</label>
        <input type="checkbox" name="html" id="html" checked>
    </div>
    <div class="container"></div>

    <script src="./js/jquery.min.js"></script>
    <script src="./codemirror/lib/codemirror.js"></script>
    <script src="./codemirror/addon/selection/active-line.js"></script>
    <script src="./codemirror/addon/edit/matchbrackets.js"></script>
    <script src="./codemirror/mode/clike/clike.js"></script>
    <script src="./codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="./codemirror/mode/css/css.js"></script>
    <script src="./codemirror/mode/xml/xml.js"></script>
    <script src="./codemirror/mode/php/php.js"></script>
    <script>
        var editor = CodeMirror.fromTextArea($('#eval')[0], {
            mode: 'php',
            theme: 'material-darker',
            lineNumbers: true,
            matchBrackets: true,
        });

        editor.setValue(readCookie('eval_input') || '\u003C?php\n    ');
        editor.focus();
        editor.setCursor(editor.lineCount(), 0);

        function createCookie(name, value, days) {
            try {
                var expires;

                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toGMTString();
                } else {
                    expires = "";
                }
                document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
            } catch (e) {
                console.error(e);
            }
        }

        function readCookie(name) {
            var nameEQ = encodeURIComponent(name) + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) {
                    var out = decodeURIComponent(c.substring(nameEQ.length, c.length));
                    console.log(out);
                    return out;
                }
            }
            return null;
        }

        function eraseCookie(name) {
            createCookie(name, "", -1);
        }

        $(document).ready(function() {
            let lastResult = '';

            $('#html').on('click', function(e) {
                if ($(this).prop("checked")) {
                    $('.container').html(lastResult);
                } else {
                    $('.container').text(lastResult);
                }
            });

            $(window).on('keydown', function(e) {
                if (e.ctrlKey && e.which == 13 && editor.getValue().length >= 1) { // ctrl + enter
                    $.post('index.php', {
                        eval: editor.getValue().replace('\u003C?php', '').replace('?>', '').replace('@', '../')
                    }).done((result) => {
                        lastResult = result;
                        if ($('#html').prop("checked")) {
                            $('.container').html(result);
                        } else {
                            $('.container').text(result);
                        }
                    });
                } else if (e.ctrlKey && e.keyCode == 83) { // ctrl + s
                    /* save to cookies */
                    e.preventDefault();
                    createCookie('eval_input', editor.getValue());
                    alert('Saved');
                    return false;
                } else if (e.ctrlKey && e.keyCode == 82) { // ctrl + r
                    /* last instance */
                    e.preventDefault();
                    editor.setValue(readCookie('eval_input') || '\u003C?php\n    ');
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