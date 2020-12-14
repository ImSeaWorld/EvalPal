<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP EvalPal</title>
    <link rel="stylesheet" href="./assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/vendors/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="./assets/vendors/codemirror/theme/material-darker.css">
    <link rel="stylesheet" href="./assets/css/style.min.css">
</head>

<body>
    <div class="container-fluid p-0" style="height: 50px;">
        <div class="form-wrapper">
            <label for="html">Display HTML</label>
            <input type="checkbox" name="html" id="html" checked>
        </div>
        <?php echo 'PHP Version: ' . phpversion(); ?>
    </div>
    <div class="container-fluid p-0" style="height: calc(100vh - 50px);">
        <div class="row m-0" id="resizable-columns">
            <div class="col-6 p-0" id="input-container">
                <textarea id="eval" cols="30" rows="10"></textarea>
            </div>
            <div class="col-6 p-0" id="output-container">
                <div class="splitter"></div>
                <div class="output-container"></div>
            </div>
        </div>
    </div>

    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/jquery-ui.min.js"></script>
    <script src="./assets/vendors/codemirror/lib/codemirror.js"></script>
    <script src="./assets/vendors/codemirror/addon/selection/active-line.js"></script>
    <script src="./assets/vendors/codemirror/addon/edit/matchbrackets.js"></script>
    <script src="./assets/vendors/codemirror/mode/clike/clike.js"></script>
    <script src="./assets/vendors/codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="./assets/vendors/codemirror/mode/css/css.js"></script>
    <script src="./assets/vendors/codemirror/mode/xml/xml.js"></script>
    <script src="./assets/vendors/codemirror/mode/php/php.js"></script>
    <script src="./assets/vendors/codemirror/mode/sql/sql.js"></script>

    <script src="./assets/vendors/codemirror/mode/markdown/markdown.js"></script>
    <script src="./assets/vendors/codemirror/mode/javascript/javascript.js"></script>

    <script src="./assets/js/main.js"></script>
</body>

</html>