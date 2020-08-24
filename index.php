<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP EvalPal</title>
    <link rel="stylesheet" href="./inc/css/jquery-ui.min.css">
    <link rel="stylesheet" href="./inc/vendors/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="./inc/vendors/codemirror/theme/material-darker.css">
    <link rel="stylesheet" href="./inc/css/style.css">
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
                file: '',
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

            var ccTimer;
            var baseDir = './';

            var clearClasses = () => {
                $('#base-location').removeClass('success');
                $('#base-location').removeClass('error');
            }

            function CheckLocation() {
                $.post('ajax.php', {
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
            // get last modified file
            $.post('ajax.php', {
                cmd: 'getFile',
            }).done((result) => {
                if (result) {
                    editor.setValue(result);
                } else {
                    editor.setValue('');
                }
            });

            editor.focus();
            editor.setCursor(editor.lineCount(), 0);

            /*try {
                $('#base-location').val(Session.get('EvalPal').baseLoc || baseDir);
                CheckLocation();
            } catch (e) {
                $('#base-location').val('./\.');
            }*/

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
                    $.post('ajax.php', {
                        cmd: 'eval',
                        eval: editor.getValue().replace('\u003C?php', '').replace('?>', '').replace(/@\//g, '../'),
                        baseLoc: $('#base-location').val()
                    }).done((result) => {
                        if ($('#html').prop("checked")) {
                            var tmp = result.replace('<br />', '');
                            $('.output-container').html(editor.getValue().includes('print_r') ? `<pre>${tmp.substr(0, tmp.length - 1)}</pre>` : tmp);
                        } else {
                            $('.output-container').text(result);
                        }

                        instance.result = result;
                    });
                } else if (e.ctrlKey && e.keyCode == 83) { // ctrl + s
                    /* save */
                    e.preventDefault();

                    var modify = instance.file || undefined;
                    var FileName = modify || prompt("Please enter in a file name", "Dingitydong.php");
                    if (FileName) {
                        if (FileName.split('.').slice(-1)[0] != 'php')
                            FileName += '.php';
                        console.log(modify);
                        $.post('ajax.php', {
                            cmd: 'save',
                            file: FileName,
                            code: editor.getValue(),
                            overwrite: (modify != undefined) || undefined
                        }).done((result) => {
                            if (result['error']) {
                                if (ovrw = confirm(result['message'])) {
                                    $.post('ajax.php', {
                                        cmd: 'save',
                                        file: FileName,
                                        code: editor.getValue(),
                                        overwrite: ovrw
                                    }).done((result) => {
                                        if (result) {
                                            instance.file = FileName;
                                            alert('Saved ' + FileName);
                                        }
                                    });
                                }
                            } else {
                                instance.file = FileName;
                                alert('Saved ' + FileName);
                            }
                        });
                    }
                    return false;
                } else if (e.ctrlKey && e.keyCode == 82) { // ctrl + r
                    /* last instance */
                    e.preventDefault();
                    $.post('ajax.php', {
                        cmd: 'getFile',
                        file: instance.file
                    }).done((result) => {
                        if (result) {
                            editor.setValue(result);
                        } else {
                            editor.setValue('');
                        }
                    });

                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                } else if (e.ctrlKey && e.keyCode == 66) { // ctrl + b
                    /* fresh start */
                    e.preventDefault();
                    instance.file = undefined;
                    editor.setValue('');
                    editor.focus();
                    editor.setCursor(editor.lineCount(), 0);
                }
            });
        });
    </script>
</body>

</html>