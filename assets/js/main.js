$(document).ready(function () {
    var instance = {
        file: '',
        result: '',
        base_location: '',
    };

    var editor = CodeMirror.fromTextArea($('#eval')[0], {
        theme: 'material-darker',
        mode: 'text/x-php',
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        smartIndent: true,
        indentWithTabs: true,
        indentUnit: 4,
    });

    window.editor = editor;

    editor.refresh();

    editor.on('change', () => {
        setCodeHeight();
    });

    editor.on('keyup', function (cm, event) {
        if (event.code === 'Backspace') {
            setCodeHeight();
        }
    });

    // get last modified file
    $.post('ajax.php', {
        cmd: 'getFile',
    }).done((result) => {
        if (result) {
            editor.setValue(result);
        } else {
            editor.setValue('');
        }

        setCodeHeight();
    });

    editor.focus();
    editor.setCursor(editor.lineCount(), 0);

    var codeHeight = () => {
        let result = 0;
        var children = $('.CodeMirror-code').find('> div');

        for (var i = 0; i < children.length; i++) {
            result += $(children[i]).height();
        }

        return children.length > 1 ? result : 0;
    };

    var setCodeHeight = () => {
        var height = ((codeHeight) => {
            if (codeHeight == 0) return 0;

            return (
                codeHeight +
                ($('#resizable-columns').height() -
                    $($('.CodeMirror-code > div')[0]).height() * 2)
            );
        })(codeHeight());
        // add buffer space
        $('.CodeMirror-code').height(height);
        // make sure we're the same for the scrollbar
        $($('.CodeMirror-vscrollbar > div')[0]).height(height);
    };

    $(window).on('resize', function () {
        // add some extra space on the bottom
        setCodeHeight();
    });

    $('#html').on('click', function (e) {
        if ($(this).prop('checked')) {
            var tmp = instance.result.replace('<br />', '');
            $('.output-container').html(
                editor.getValue().includes('print_r')
                    ? `<pre>${tmp.substr(0, tmp.length - 2)}</pre>`
                    : tmp,
            );
        } else {
            $('.output-container').text(instance.result);
        }
    });

    var Dragging = false;

    $('.splitter').on('mousedown mouseup', function (e) {
        console.log(e);

        switch (e.type) {
            case 'mouseup':
                if (Dragging) {
                    $(document).unbind('mousemove');
                    Dragging = false;
                }
                break;
            case 'mousedown':
                e.preventDefault();
                Dragging = true;
                $(document).mousemove(function (e) {
                    var percentage = (e.pageX / window.innerWidth) * 100;
                    var percentage = { a: percentage, b: 100 - percentage };

                    $('#input-container').css('width', `${percentage.a}%`);
                    $('#output-container').css('width', `${percentage.b}%`);
                });
                break;
        }

        //
    });

    $(window).on('keydown', function (e) {
        if (e.ctrlKey && e.which == 13 && editor.getValue().length >= 1) {
            // ctrl + enter
            $.post('ajax.php', {
                cmd: 'eval',
                eval: editor
                    .getValue()
                    .replace('\u003C?php', '')
                    .replace('?>', '')
                    .replace(/@\//g, '../'),
                baseLoc: $('#base-location').val(),
            }).done((result) => {
                if ($('#html').prop('checked')) {
                    var tmp = result.replace('<br />', '');
                    $('.output-container').html(
                        editor.getValue().includes('print_r')
                            ? `<pre>${tmp.substr(0, tmp.length - 1)}</pre>`
                            : tmp,
                    );
                } else {
                    $('.output-container').text(result);
                }

                instance.result = result;
            });
        } else if (e.ctrlKey && e.keyCode == 83) {
            // ctrl + s
            /* save */
            e.preventDefault();

            var modify = instance.file || undefined;
            var FileName =
                modify ||
                prompt('Please enter in a file name', 'Dingitydong.php');
            if (FileName) {
                if (FileName.split('.').slice(-1)[0] != 'php')
                    FileName += '.php';
                console.log(modify);
                $.post('ajax.php', {
                    cmd: 'save',
                    file: FileName,
                    code: editor.getValue(),
                    overwrite: modify != undefined || undefined,
                }).done((result) => {
                    if (result['error']) {
                        if ((ovrw = confirm(result['message']))) {
                            $.post('ajax.php', {
                                cmd: 'save',
                                file: FileName,
                                code: editor.getValue(),
                                overwrite: ovrw,
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
        } else if (e.ctrlKey && e.keyCode == 82) {
            // ctrl + r
            /* last instance */
            e.preventDefault();
            $.post('ajax.php', {
                cmd: 'getFile',
                file: instance.file,
            }).done((result) => {
                if (result) {
                    editor.setValue(result);
                } else {
                    editor.setValue('');
                }
            });

            editor.focus();
            editor.setCursor(editor.lineCount(), 0);
        } else if (e.ctrlKey && e.keyCode == 66) {
            // ctrl + b
            /* fresh start */
            e.preventDefault();
            instance.file = undefined;
            editor.setValue('');
            editor.focus();
            editor.setCursor(editor.lineCount(), 0);
        }
    });
});
