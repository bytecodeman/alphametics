<?php
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_time_limit(0);

spl_autoload_register(function ($class_name) {
    require_once $class_name . '.php';
});

require "../library/php/dbconnect.php";
require "../library/php/library.php";
require "secret/recaptcha.php";

function performAnalysis($expr, $tmpmin, $tmpmax, $tmpunique, $tmpleadingZero, &$succMsg, &$errMsg)
{
    $solution = "";
    try {
        logAccess($expr, $tmpleadingZero, $tmpunique, $tmpmin, $tmpmax);
        $results = AnalyzeAlphaMetics::getAllAlphameticSolutions($expr, $tmpmin, $tmpmax, $tmpunique, $tmpleadingZero);
        if (count($results) < 1) {
            $errMsg = 'No Solutions Found';
        } else {
            $solution = AnalyzeAlphaMetics::outputSolutions($expr, $results);
            $succMsg = 'Solutions Found!!!';
        }
    } catch (Exception $ex) {
        $errMsg = "Error in Expression: " . $ex->getMessage();
    }
    return $solution;
}

function logAccess($expr, $leadingzeros, $unique, $min, $max)
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();
        $sql = 'INSERT INTO alphametics(expr, leadingzeros, uniquevalues, min, max, ipaddr) ' .
            'VALUES(:expr, :leadingzeros, :uniquevalues, :min, :max, :ipaddr)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'expr' => $expr, 'leadingzeros' => $leadingzeros, 'uniquevalues' => $unique,
            'min' => $min, 'max' => $max, 'ipaddr' => getUserIP()
        ]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Connection failed: ' . $e->getMessage();
    }
}

$title = "The Ultimate Alphametics Solver!";
$current = "alphametics";
$expr = "";
$min = 0;
$max = 9;
$unique = "unique";
$leadingZero = "";
$solution = "";
$errMsg = '';
$succMsg = '';
$showCaptCha = !isLocalHost();
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
    $expr = trim($_POST["expr"]);
    $min = $_POST["min"];
    $max = $_POST["max"];
    $unique = $_POST["unique"];
    $leadingZero = !isset($_POST["leadingZero"]) ? "" : $_POST["leadingZero"];

    $tmpexpr = test_input($expr);
    $tmpmin = (int)test_input($min);
    $tmpmax = (int)test_input($max);
    $tmpunique = test_input($unique) === "unique";
    $tmpleadingZero = test_input($leadingZero) === "leadingZero" ? 1 : 0;
    $showCaptCha = !isset($_POST["showCaptCha"]);

    if ($expr !== $tmpexpr) :
        $errMsg = "Hacking Attempt Detected";
    elseif (isLocalHost() || !$showCaptCha) :
        $solution = performAnalysis($expr, $tmpmin, $tmpmax, $tmpunique, $tmpleadingZero, $succMsg, $errMsg);
    elseif (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) :
        //your site secret key
        $secret = SECRET_KEY;
        //get verify response data
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
        $responseData = json_decode($verifyResponse);
        if ($responseData->success) :
            $showCaptCha = false;
            $solution = performAnalysis($expr, $tmpmin, $tmpmax, $tmpunique, $tmpleadingZero, $succMsg, $errMsg);
        else :
            $errMsg = 'Robot verification failed, please try again.';
        endif;
    elseif ($showCaptCha) :
        $errMsg = 'Please click on the reCAPTCHA box.';
    endif;
endif;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Antonio C. Silvestri">
    <meta name="description" content="The Ultimate Alphametic Puzzle Solver! Accepts any expression with standard operator precedence.">
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@bytecodeman">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="Enter a complete alphametic expression such as TWO + TWO = FOUR and the system will output all possible solutions for it.">
    <meta name="twitter:image" content="https://cs.stcc.edu/specialapps/alphametics/img/alphametics1.jpg">

    <meta property="og:url" content="https://cs.stcc.edu/specialapps/alphametics/" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:description" content="Enter a complete alphametic expression such as TWO + TWO = FOUR and the system will output all possible solutions for it." />
    <meta property="og:image" content="https://cs.stcc.edu/specialapps/alphametics/img/alphametics1.jpg" />

    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-9626577709396562",
            enable_page_level_ads: true
        });
    </script>
</head>

<body>
    <?php include "../library/php/navbar.php"; ?>
    <div class="container">
        <div class="jumbotron">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="font-weight-bold"><?php echo $title; ?></h1>
                    <div class="clearfix">
                        <img src="img/bigcatlion.jpg" alt="" class="rounded mb-2 mr-4 float-left d-block img-fluid">
                        <p>An alphametic puzzle is a puzzle where words and numbers are put together into an arithmetic formula such that digits can be
                            substituted for the letters to make the formula true.</p>
                        <p class="d-print-none">Enter a complete alphametic expression such as <code>TWO + TWO = FOUR</code>
                            and the system will output all possible solutions for it. Operands can consist of both letters and numbers. The standard
                            precedence expressions can include <code>+, -, *, /, %, ()</code> operators.
                        </p>
                    </div>
                    <p class="d-print-none"><b>Warning!</b> With many variables, there are <b>VERY</b> many possible solutions that must be examined. It may take a long time to find solutions. <b>Patience Please!!!</b> (BIG + CAT = LION, a 9 variable expression, will take 8 minutes to analyze. A 10 variable expression will take over 80 minutes!!!)</p>
                    <p class="d-print-none"><a href="#" data-toggle="modal" data-target="#myModal">About <?php echo $title; ?></a></p>
                    <p class="d-print-none"><a href="https://github.com/bytecodeman/alphametics" target="_blank" rel="noopener noreferrer">Source Code</a></p>
                </div>
                <div class="col-lg-4 d-print-none">
                    <!-- <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> -->
                    <!-- Mobile Ads -->
                    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-9626577709396562" data-ad-slot="7064413444" data-ad-format="auto"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <form id="alphameticsform" method="post" action="<?php echo htmlspecialchars(extractPath($_SERVER["PHP_SELF"])); ?>">
                    <?php if (!empty($errMsg)) : ?>
                        <div id="errMsg" class="form-group font-weight-bold h3 text-danger">
                            <?php echo $errMsg; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($succMsg)) : ?>
                        <div id="succMsg" class="form-group font-weight-bold h3 text-success"><?php echo $succMsg; ?>
                            <div id="copyToClipboard">
                                <a tabindex="0" id="copytoclip" data-trigger="focus" data-clipboard-target="#resultsPanel" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Copied!">
                                    <img src="img/clippy.svg" alt="Copy to Clipboard" title="Copy to Clipboard">
                                </a>
                            </div>
                        </div>
                        <pre id="resultsPanel" class="form-group"><?php echo $solution; ?></pre>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="expr">Alphametic Expression</label>
                        <input type="text" id="expr" name="expr" class="form-control form-control-lg" placeholder="Enter Alphametic Expression" value="<?php echo $expr; ?>" required>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" value="leadingZero" name="leadingZero" <?php if ($leadingZero === "leadingZero") {
                                                                                                                        echo "checked";
                                                                                                                    } ?>>
                            Allow leading zeroes on operands?</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="unique" class="form-check-input" value="unique" required <?php if ($unique == "unique") {
                                                                                                                    echo "checked";
                                                                                                                } ?> />
                            Generate unique values for each letter?
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" name="unique" class="form-check-input" value="repeat" required <?php if ($unique == "repeat") {
                                                                                                                    echo "checked";
                                                                                                                } ?>>
                            Allow reuse of values for each letter?
                        </label>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="min">Minimum Value</label>
                            <input type="number" min="0" max="9" id="min" name="min" class="form-control" value="<?php echo $min; ?>" placeholder="Enter Minimum Value" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="max">Maximum Value</label>
                            <input type="number" min="0" max="9" id="max" name="max" class="form-control" value="<?php echo $max; ?>" placeholder="Enter Maximum Value" required>
                        </div>
                    </div>
                    <?php if ($showCaptCha) : ?>
                        <div class="g-recaptcha form-group d-print-none" data-sitekey="<?php echo SITE_KEY; ?>"></div>
                    <?php else : ?>
                        <input type="hidden" name="showCaptCha" value="false">
                    <?php endif; ?>
                    <button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg d-print-none">Submit</button>

                </form>
            </div>
        </div>
    </div>

    <?php
    require "../library/php/about.php";
    ?>

    <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <?php if ($showCaptCha) : ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a576c39d176f4a6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
    <script>
        $(function() {
            $('[data-toggle="popover"]').popover();
            new Clipboard("#copytoclip");

            $("#alphameticsform").submit(function() {
                function isCaptchaChecked() {
                    return window.grecaptcha && window.grecaptcha.getResponse().length !== 0;
                }

                $("#errMsg, #succMsg, #resultsPanel").remove();
                const $errMsg = $('<div id="errMsg" class="form-group font-weight-bold h3 text-danger"></div>');

                if (!this.checkValidity()) {
                    $(this).prepend($errMsg);
                    $("#errMsg").text("Invalid Inputs in Form");
                    return false;
                } else if (window.grecaptcha && !isCaptchaChecked()) {
                    $(this).prepend($errMsg);
                    $("#errMsg").text("Please click on the reCAPTCHA box.");
                    return false;
                }

                $(".g-recaptcha").attr("style", "display: none !important");
                $("#submit").html('Please Wait <i class="fas fa-spinner fa-spin fa-lg ml-3"></i>').attr("disabled", "disabled");
                return true;
            });

        });
    </script>
</body>

</html>