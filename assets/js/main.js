jQuery(function () {
    var instance = {
        file: '',
        result: '',
        base_location: '',
        loading: true,
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

    function handleLoading(loading = true) {
        instance.loading = loading;
        $('.loading')[loading ? 'addClass' : 'removeClass']('visible');
    }

    window.editor = editor;

    editor.refresh();

    $.ajaxSetup({
        beforeSend: () => handleLoading(true),
        complete: () => handleLoading(false),
    });

    // get last modified file
    $.post('ajax.php', {
        cmd: 'getFile',
    }).done((result) => {
        editor.setValue(result ? result : '');
    });

    editor.focus();
    editor.setCursor(editor.lineCount(), 0);

    $('#html').on('click', function (e) {
        if ($(this).prop('checked')) {
            var tmp = instance.result.replace('<br />', '');
            $('.output-container').html(
                editor.getValue().includes('print_r')
                    ? `<pre>${tmp.substring(0, tmp.length - 2)}</pre>`
                    : tmp,
            );
        } else {
            $('.output-container').text(instance.result);
        }
    });

    var Dragging = false;

    $('.splitter').on('mousedown mouseup', function (e) {
        switch (e.type) {
            case 'mouseup':
                if (Dragging) {
                    $(document).off('mousemove');
                    Dragging = false;
                }
                break;
            case 'mousedown':
                e.preventDefault();
                Dragging = true;
                $(document)
                    .on('mousemove', function (e) {
                        if (!Dragging) return;

                        var percentage = (e.pageX / window.innerWidth) * 100;
                        var percentage = { a: percentage, b: 100 - percentage };

                        $('#input-container').css('width', `${percentage.a}%`);
                        $('#output-container').css('width', `${percentage.b}%`);
                    })
                    .on('mouseup', function (e) {
                        $(document).off('mousemove');
                        Dragging = false;
                    });
                break;
        }
    });

    $(window).on('keydown', function (e) {
        // Text wrapping
        if (editor.getSelection().length > 0) {
            const wrapArray = '[]{}()<>\'\'""``'.split('');
            const wrapIndex = wrapArray.findIndex((v) => v === e.key);
            if (wrapIndex >= 0) {
                e.preventDefault();
                editor.replaceSelection(
                    wrapArray[wrapIndex] +
                        editor.getSelection() +
                        wrapArray[wrapIndex + 1],
                );
            }
        }

        if (e.ctrlKey && e.which == 13 && editor.getValue().length >= 1) {
            // ctrl + enter

            if (instance.loading) return;

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
                            ? `<pre>${tmp.substr(0, tmp.length)}</pre>`
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
            if (instance.loading) return;

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
            if (instance.loading) return;

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
