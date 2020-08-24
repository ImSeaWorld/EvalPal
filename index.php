<?php
if (!isset($_SESSION))
    session_start();
/*
        TODO:
    Clean up PHP. 
    Put ajax into it's own file
    Add autocomplete for directories
*/
set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

if (isset($_POST['eval'])) {
    try {
        $output = eval($_POST['eval']);
    } catch (ParseError $err) {
        die(implode("", [
            '<b style="color: #cc3737;">Eval Error</b><br>',
            '<p>',
            '<b>Line:</b> ',
            $err->getLine(),
            '<br><b>Error:</b> ',
            $err->getMessage(),
            '<br><b>Trace:</b> ',
            $err->getTraceAsString(),
            '<br><b>Error Code:</b> ',
            $err->getCode(),
            '</p>'
        ]));
    } catch (Exception $err) {
        $file = explode(' : ', $err->getFile())[0];
        die(implode("", [
            '<b style="color: #FFCB6B;">Exception</b><br>',
            '<p>',
            '<b>Line:</b> ',
            $err->getLine(),
            '<br><b>File:</b> ',
            explode('\\', $file)[count(explode('\\', $file)) - 1],
            '<br><b>Error:</b> ',
            $err->getMessage(),
            '<br><b>Trace:</b> ',
            $err->getTraceAsString(),
            '<br><b>Error Code:</b> ',
            $err->getCode(),
            '</p>'
        ]));
    } catch (ErrorException $err) {
        $file = explode(' : ', $err->getFile())[0];
        die(implode("", [
            '<b style="color: #FFCB6B;">Warning</b><br>',
            '<p>',
            '<b>Line:</b> ',
            $err->getLine(),
            '<br><b>File:</b> ',
            explode('\\', $file)[count(explode('\\', $file)) - 1],
            '<br><b>Error:</b> ',
            $err->getMessage(),
            '<br><b>Trace:</b> ',
            $err->getTraceAsString(),
            '<br><b>Error Code:</b> ',
            $err->getCode(),
            '</p>'
        ]));
    }

    die($output);
}

function jdie($input)
{
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode($input));
    exit();
}

if (isset($_POST['cmd'])) {
    switch ($_POST['cmd']) {
        case 'scanDir':
            $arr = [];
            $userInput = $_POST['loc'];

            //jdie($_SESSION['EP_DIRECTORIES']);

            if (isset($_SESSION['EP_DIRECTORIES'])) {
                //$arr = $_SESSION['EP_DIRECTORIES'];
                //jdie($arr);
            }

            //if (!isset($_SESSION['EP_LASTSEARCH']) || substr($_SESSION['EP_LASTSEARCH'], -1) == '/') {
            foreach (scandir($userInput) as $key => $file) {
                if (is_dir(realpath($userInput) . '\\' . $file)) {
                    $arr[$key] = $userInput . $file;
                }
            }
            //}

            if (is_array($arr) && count($arr) > 0) {
                $_SESSION['EP_DIRECTORIES'] = $arr;
                $_SESSION['EP_LASTSEARCH'] = $userInput;
            }

            if (!is_array($arr)) {
                jdie(['fuck' => 'you', $arr]);
            }

            jdie(["grep" => preg_grep('~' . preg_quote($userInput, '~') . '~', $arr)]);
        case 'baseLoc':
            jdie(__DIR__);
        case 'checkLoc':
            jdie([
                'realPath' => realpath($_POST['loc']),
                'exists' => file_exists($_POST['loc']),
                'isDirectory' => is_dir($_POST['loc'])
            ]);
        case 'save':
            if (!file_exists('./saved/' . $_POST['file']) || isset($_POST['overwrite'])) {
                file_put_contents('./saved/' . $_POST['file'], $_POST['code']);
                jdie(true);
            } else {
                jdie([
                    'error' => 'DUPLICATE',
                    'message' => 'Would you like to overwrite ' . $_POST['file'] . '?'
                ]);
            }
        case 'getFile':
            if (file_exists('./saved/' . $_POST['file'])) {
                jdie(file_get_contents('./saved/' . $_POST['file']));
            }

            jdie(false);
    }
    exit();
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP EvalPal</title>
    <link rel="stylesheet" href="./inc/css/jquery-ui.min.css">
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
            top: 60%;
            padding: 20px;
            margin: 5px;
            color: #fff;
            font-size: 1.5rem;
            background-color: #212121;
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
            border: 1px solid #868686;
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

        input[type="text"].success {
            border-color: green;
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

        .auto-complete {
            display: inline-block;
            position: relative
        }

        ul.auto-complete-list {
            position: absolute;
            list-style: none;
            margin: 0;
            padding: 0;
            left: 0;
            right: 0;
        }

        ul.auto-complete-list>li {
            padding: 5px;
            font-size: 0.8rem;
            background-color: #212121;
            border-top: 1px solid #797979;
            border-left: 1px solid #797979;
            border-right: 1px solid #797979;
        }

        ul.auto-complete-list>li:first-child {
            border-top: 0;
        }

        ul.auto-complete-list>li:last-child {
            border-bottom: 1px solid #797979;
        }

        ul.auto-complete-list>li.selected {
            color: #000;
            background-color: #d4d4d4;
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
            <div class="auto-complete">
                <input type="text" name="base-location" id="base-location" class="vw-50" placeholder="../../somelocation/">
                <ul class="auto-complete-list" style="display:none;">
                    <li class="selected">../Balls</li>
                    <li>../Chit</li>
                    <li>../Bitty</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="output-container"></div>

    <script src="./inc/js/jquery.min.js"></script>
    <script src="./inc/js/jquery-ui.min.js"></script>
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
            var instance = {
                result: '',
                base_location: '',
            }
            let lastResult = '';
            var editor = CodeMirror.fromTextArea($('#eval')[0], {
                theme: 'material-darker',
                lineNumbers: true,
                lineWrapping: true,
                matchBrackets: true,
                smartIndent: true,
                indentWithTabs: true,
                indentUnit: 4,
                mode: 'text/x-php',
            });
            editor.refresh();

            // https://stackoverflow.com/a/29302559/10887412
            if (JSON && JSON.stringify && JSON.parse) var Session = Session || (function() {
                // session store
                var store = load();

                function load() {
                    var name = "store";
                    var result = document.cookie.match(new RegExp(name + '=([^;]+)'));

                    try {
                        if (result)
                            return JSON.parse(atob(result[1]));
                    } catch (exception) {
                        console.error(exception);
                    }

                    return {};
                }

                function Save() {
                    var date = new Date();
                    date.setHours(23, 59, 59, 999);
                    var expires = "expires=" + date.toGMTString();
                    // store as base64
                    document.cookie = "store=" + btoa(JSON.stringify(store)) + "; " + expires;
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

            var ccTimer;
            var baseDir = './';

            try {
                baseDir = Session.get('EvalPal').baseLoc;
            } catch (Exception) {}

            var clearClasses = () => {
                $('#base-location').removeClass('success');
                $('#base-location').removeClass('error');
            }

            function CheckLocation() {
                $.post('index.php', {
                    cmd: 'checkLoc',
                    loc: $('#base-location').val()
                }).done((result) => {
                    if (!result.isDirectory) {
                        $('#base-location').removeClass('success');
                        $('#base-location').addClass('error');
                    } else {
                        $('#base-location').removeClass('error');
                        $('#base-location').addClass('success');
                        $('#base-location').attr('title', 'Full Path: ' + result.realPath);
                        instance.base_location = $('#base-location').val();
                    }
                    clearInterval(ccTimer);
                    ccTimer = setTimeout(clearClasses, 300);
                });
            }

            try {
                $.post('index.php', {
                    cmd: 'getFile',
                    file: Session.get('EvalPal').FileOpen || ''
                }).done((result) => {
                    if (result) {
                        editor.setValue(result);
                    } else {
                        editor.setValue('');
                    }
                });
            } catch (e) {
                editor.setValue('// Error recovering cookie\n');
            }

            editor.focus();
            editor.setCursor(editor.lineCount(), 0);

            try {
                $('#base-location').val(Session.get('EvalPal').baseLoc || baseDir);
                CheckLocation();
            } catch (e) {
                $('#base-location').val('./\.');
            }

            $('#html').on('click', function(e) {
                if ($(this).prop("checked")) {
                    var tmp = instance.result.replace('<br />', '');
                    $('.output-container').html(editor.getValue().includes('print_r') ? `<pre>${tmp.substr(0, tmp.length - 1)}</pre>` : tmp);
                } else {
                    $('.output-container').text(instance.result);
                }
            });

            $('#base-location').on('keyup', function(e) {
                if (e.which === 13) {
                    CheckLocation();
                }
            });

            $(window).on('keydown', function(e) {
                if (e.ctrlKey && e.which == 13 && editor.getValue().length >= 1) { // ctrl + enter
                    $.post('index.php', {
                        eval: editor.getValue().replace('\u003C?php', '').replace('?>', '').replace(/@\//g, '../'),
                        baseLoc: $('#base-location').val()
                    }).done((result) => {
                        if ($('#html').prop("checked")) {
                            var tmp = result.replace('<br />', '');
                            console.log("tmp.includes('print_r')", tmp.includes('print_r'));
                            $('.output-container').html(editor.getValue().includes('print_r') ? `<pre>${tmp.substr(0, tmp.length - 1)}</pre>` : tmp);
                        } else {
                            $('.output-container').text(result);
                        }

                        instance.result = result;
                    });
                } else if (e.ctrlKey && e.keyCode == 83) { // ctrl + s
                    /* save */
                    e.preventDefault();

                    var modify = undefined;
                    try {
                        modify = Session.get('EvalPal').FileOpen;
                    } catch ($err) {}

                    var FileName = modify || prompt("Please enter in a file name", "Dingitydong.php");
                    if (FileName) {
                        if (FileName.split('.').slice(-1)[0] != 'php')
                            FileName += '.php';

                        $.post('index.php', {
                            cmd: 'save',
                            file: FileName,
                            code: editor.getValue(),
                            overwrite: modify
                        }).done((result) => {
                            if (result['error']) {
                                if (confirm(result['message'])) {
                                    $.post('index.php', {
                                        cmd: 'save',
                                        file: FileName,
                                        code: editor.getValue(),
                                        overwrite: modify ? true : undefined
                                    }).done((result) => {
                                        alert(result);
                                    });
                                }
                            } else {
                                Session.set('EvalPal', {
                                    FileOpen: FileName
                                });

                                alert('Saved ' + FileName);
                            }
                        });
                    }
                    return false;
                } else if (e.ctrlKey && e.keyCode == 82) { // ctrl + r
                    /* last instance */
                    e.preventDefault();
                    $.post('index.php', {
                        cmd: 'getFile',
                        file: Session.get('EvalPal').FileOpen || ''
                    }).done((result) => {
                        if (result) {
                            editor.setValue(result);
                        } else {
                            editor.setValue('');
                        }
                    });

                    //editor.setValue(Session.get('EvalPal').editor || '');
                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                } else if (e.ctrlKey && e.keyCode == 66) { // ctrl + b
                    /* fresh start */
                    e.preventDefault();
                    editor.setValue('');
                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                }
            });
        });
    </script>
</body>

</html>