<?php
/**
 * Created by Mr. Jason A. Mullings.
 * File Name: index.php
 * User: jlmconsulting
 * Date: 18/02/2017
 * Time: 5:26 PM
 */
$dataPoints = array(
    array("y" => 100, "legendText" => "Employers", "label" => "Employers")
);
$dataPoints2 = array(
    array("y" => 100, "legendText" => "Need", "label" => "Need")
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>iHR IT Recruitment: What employers REALLY seek in employees!</title>

    <!-- stylesheets -->
    <link href="assets/bootstrap.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">

    <!-- scripts -->
    <script src="assets/js/jquery-3.1.0.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/phpUnserialize.js"></script>
    <script src="assets/js/canvasjs.min.js"></script>
</head>
<header>
    <h1><img src="http://www.ihr.co.nz/sites/default/files/ihr-logo.png" style="height: 90px; margin-left: 42%"></h1>
    <br/>
</header>
<body>
<!-- /header -->

<!-- page-content-wrapper -->
<div class="container-fluid">

    <div class="row">
        <div id="content" class="col-md-4" style="float: left">

            <div id="chartContainer"></div>

        </div> <!-- /content-->
        <div id="content" class="col-md-4  " style="float: right; ">
            <div id="chartContainer2"></div>
        </div> <!-- /content-->
        <form id="form1" method="post" action="#">
            <div class="address">
                <label for="data[address][0]">Enter Advert Address:</label>
                <input class="emailD" type="text" name="data[address][0]" id="data[address][0]"
                       placeholder="E.g. www.jobsite.com"/>
            </div>
            <br/>
            <button class="btn" id="add-address">Add Site</button>
            <br/>
            <br/>
            <input class="submit btn" type="submit" value="Query"/>
        </form>
        <br/>
        <div class="iconz" style="margin-left: 38.5%;margin-right: 30%">
            <br/>
            <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>

            <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>

            <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>

            <i class="fa fa-cog fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>

            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#add-address").click(function (e) {
                    e.preventDefault();

                    // console.log($(".address").length);
                    if ($(".address").length > 4) {
                        alert('Only five addresses allowed!!');
                        return false;
                    }
                    var numberOfAddresses = $("#form1").find("input[name^='data[address]']").length;
                    var label = '<label for="data[address][' + numberOfAddresses + ']">Address ' + (numberOfAddresses + 1) + '</label> ';
                    var input = '<input type="text" class="emailD" name="data[address][' + numberOfAddresses + ']" id="data[address][' + numberOfAddresses + ']" />';
                    var removeButton = '<button class="remove-address btn">Remove</button>';
                    var html = "<div class='address'>" + label + input + removeButton + "</div>";
                    $("#form1").find("#add-address").before(html);
                });
            });

            $(document).on("click", ".remove-address", function (e) {
                e.preventDefault();
                $(this).parents(".address").remove();
                //update labels
                $("#form1").find("label[for^='data[address]']").each(function () {
                    $(this).html("Address " + ($(this).parents('.address').index() + 1));
                });
            });
            $(document).ajaxSend(function (event, request, settings) {
                $('.iconz').show();
            });

            $(document).ajaxComplete(function (event, request, settings) {
                $('.iconz').hide();
            });
            $(document).on("click", ".submit", function (e) {
                e.preventDefault();

                $("input[class='emailD']").each(function () {

                    if (is_valid_url($(this).val())==false) {
                        alert('Please enter valid URLs!');
                        return null;
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "file_parse.php",
                    data: $("input[class='emailD']").serializeArray(),
                    success: function (result) {
                        var aa = phpUnserialize(result);
                        CanvasLeft(aa[0]);
                        CanvasRight(aa[1]);
                       // alert(result);

                    }
                });

                return false;
            });
            function is_valid_url(url)
            {
                return /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(url);
            }

            function CanvasLeft(result) {
                var chart = new CanvasJS.Chart("chartContainer", {
                    title: {
                        text: "Your current statistics:"
                    },
                    animationEnabled: true,
                    legend: {
                        verticalAlign: "center",
                        horizontalAlign: "left",
                        fontSize: 20,
                        fontFamily: "Helvetica"
                    },
                    theme: "theme2",
                    data: [
                        {
                            type: "pie",
                            indexLabelFontFamily: "Garamond",
                            indexLabelFontSize: 20,
                            indexLabel: "{label} {y}%",
                            startAngle: -20,
                            showInLegend: true,
                            toolTipContent: "{legendText} {y}%",
                            dataPoints: result
                        }
                    ]
                });
                chart.render();
            }

            function CanvasRight(result) {
                var chart = new CanvasJS.Chart("chartContainer2", {
                    title: {
                        text: "Total statistics:"
                    },
                    animationEnabled: true,
                    legend: {
                        verticalAlign: "center",
                        horizontalAlign: "left",
                        fontSize: 20,
                        fontFamily: "Helvetica"
                    },
                    theme: "theme2",
                    data: [
                        {
                            type: "pie",
                            indexLabelFontFamily: "Garamond",
                            indexLabelFontSize: 20,
                            indexLabel: "{label} {y}%",
                            startAngle: -20,
                            showInLegend: true,
                            toolTipContent: "{legendText} {y}%",
                            dataPoints: result
                        }
                    ]
                });
                chart.render();
            }
            CanvasLeft(<?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>);
            CanvasRight(<?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>);
        </script>
    </div> <!-- /content-->
</div> <!-- /row -->
<!-- footer -->
<div id="footer"></div>
<!-- /footer -->
<!-- /page-content-wrapper -->

</body>
</html>