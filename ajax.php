<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function jdie($input)
{
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode($input));
    exit();
}

if (isset($_POST['cmd'])) {
    switch ($_POST['cmd']) {
        case 'eval': {
                set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
                    // error was suppressed with the @-operator
                    if (0 === error_reporting()) {
                        return false;
                    }

                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                });

                try {
                    die(eval($_POST['eval']));
                } catch (ParseError $err) {
                    die(implode("", [
                        '<b style="color: #cc3737;">Eval Error</b><br>',
                        '<p>',
                        '<b>Line:</b> ',
                        $err->getLine(),
                        '<br><b>Error:</b> ',
                        $err->getMessage(),
                        '<br><b>Error Code:</b> ',
                        $err->getCode(),
                        '</p>'
                    ]));
                } catch (Exception $err) {
                    $file = explode(' : ', $err->getFile())[0];
                    die(implode("", [
                        '<b style="color: #FFCB6B;">Exception</b><br>',
                        '<p>',
                        '<b>File:</b> ',
                        explode('\\', $file)[count(explode('\\', $file)) - 1],
                        '<br><b>Line:</b> ',
                        $err->getLine(),
                        '<br><b>Error:</b> ',
                        $err->getMessage(),
                        '<br><b>Error Code:</b> ',
                        $err->getCode(),
                        '</p>'
                    ]));
                } catch (ErrorException $err) {
                    $file = explode(' : ', $err->getFile())[0];
                    die(implode("", [
                        '<b style="color: #FFCB6B;">Warning</b><br>',
                        '<p>',
                        '<b>File:</b> ',
                        explode('\\', $file)[count(explode('\\', $file)) - 1],
                        '<br><b>Line:</b> ',
                        $err->getLine(),
                        '<br><b>Error:</b> ',
                        $err->getMessage(),
                        '<br><b>Error Code:</b> ',
                        $err->getCode(),
                        '</p>'
                    ]));
                }
            }
        case 'scanDir':
            $arr = [];
            $userInput = $_POST['loc'];

            foreach (scandir($userInput) as $key => $file) {
                if (is_dir(realpath($userInput) . '\\' . $file)) {
                    $arr[$key] = $userInput . $file;
                }
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
            if (!file_exists('./saved/')) {
                mkdir('./saved/', 0755);
            }

            if (!file_exists('./saved/' . $_POST['file']) || (isset($_POST['overwrite']) && $_POST['overwrite'])) {
                file_put_contents('./saved/' . $_POST['file'], $_POST['code']);
                jdie(true);
            } else {
                jdie([
                    'error' => 'DUPLICATE',
                    'message' => 'Would you like to overwrite ' . $_POST['file'] . '?'
                ]);
            }
        case 'getFile':
            if (!isset($_POST['file'])) {
                $saves = glob('saved/*');
                $modMap = array_map("filemtime", glob('saved/*'));
                arsort($modMap);
                jdie(file_get_contents($saves[key($modMap)]));
            } else {
                if (file_exists('./saved/' . $_POST['file'])) {
                    jdie(file_get_contents('./saved/' . $_POST['file']));
                }

                jdie(false);
            }
    }
    exit();
}
