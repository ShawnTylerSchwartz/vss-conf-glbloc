<?php
    $images = explode('|', $cue);
    $values = explode('|', $value);

    //FLIP COIN (CONF OR NO CONF)
    // NO CONF ~> value == 0
    // CONF ~> value == 1
    $flip = rand(0,1);

    ///// FOR GLOBAL LOCAL
    // 0 ~> local only; 1 ~> global only; 2 ~> both; 3 ~> none
    $glob_loc_status = 3;

    if (!isset($procedureRow)) {
        $procedureRow = '';
    }

    if ($procedureRow === '') {
        $gridNumber = $currentPos-1;
    } elseif ($procedureRow[0] === 'r') {
        $gridNumber = $currentPos - substr($procedureRow, 1);
    } else {
        $gridNumber = $procedureRow - 1;    // correct for headers, row 2 is trial 1
    }
    
    if (!isset($_SESSION['Trials'][$gridNumber]['Grid'])) {
        trigger_error("Error: grid $gridNumber not studied previously", E_USER_ERROR);
    } else {
        $gridPlacements = json_decode($_SESSION['Trials'][$gridNumber]['Grid']['grid'], true);
    }
    
    $imagePlacements = array();
    foreach ($gridPlacements as $y => $row) {
        foreach ($row as $x => $imageNumber) {
            $imagePlacements[$imageNumber] = "$x.$y";
        }
    }
    ksort($imagePlacements);
    
    $tone_feedback = '';
    
    if (isset($_SESSION['Trials'][$gridNumber]['Response']['Tone_Response_Summary'])) {
        $tone_summary = json_decode($_SESSION['Trials'][$gridNumber]['Response']['Tone_Response_Summary'], true);
        
        $tones_correct = $tone_summary['last_correct'];
        
        $tone_feedback = "<p>You made $tones_correct correct tone judgment" . ($tones_correct == 1 ? '' : 's') . ' out of 10 possible.</p>';
    }
    
?><style>
    body > form { width: initial !important; }
    
    .gridInstructions {
        font-size: 120%;
        width: 650px;
        margin: 0 auto 20px;
        text-align: center;
    }

    .warnings {
        font-size: 110%;
        width: 850px;
        margin: 0 auto 10px;
        text-align: center;
        font-weight: bolder;
        color: red;
        visibility: hidden;
        display: block;
    }

    .doubleclick {
        font-size: 110%;
        width: 850px;
        margin: 0 auto 10px;
        text-align: center;
        font-weight: bolder;
        color: blue;
        visibility: visible;
        display: block;
    }

    .gridInstructionsInner {
        display: inline-block;
        text-align: left;
    }
    .vdrGridTable {
        margin: auto;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: white;
    }
    .vdrGridTable td {
        text-align: center;
        vertical-align: middle;
        padding: 4px;
        border: 1px solid #444;
        width: 100px;
        height: 100px;
        position: relative;
    }
    .vdrGridTable td img {
        max-height: 100px;
        max-width: 100px;
    }
    .vdrGridTable td .cellValue {
        position: absolute;
        top: 0;
        left: 2px;
    }
    .vdrGridTable td .cellValue::before {
        content: "(";
    }
    .vdrGridTable td .cellValue::after {
        content: ")";
    }
    .imgOptions {
        width: 1100px;
        margin: 30px auto 0;
    }
    .current {
        box-shadow: 1px 1px 10px 4px #30B0E0;
    }
    .activelyRating {
        box-shadow: 1px 1px 10px 4px #30B0E0;
    }
    .gettingDragged * {
        cursor: move !important;
    }
    .gettingDragged img, .gettingDragged button {
        pointer-events: none;
    }
    
    .bonus {
        position: absolute;
        top: 4px;
        left: 4px;
        color: #00A500;
        border: 3px solid #00B000;
        border-radius: 100px;
        display: inline-block;
        width: 30px;
        font-weight: bold;
        text-align: center;
        padding: 2px;
        box-sizing: border-box;
        background-color: yellow;
    }
    
    .feedback {
        font-size: 120%;
        margin: 20px 0 0 ;
        text-align: center;
        display: none;
    }
     /* The Modal (i.e., confidence rating) (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        /*left: 0;*/
        left: 68%;
        top: 0;
        /*width: 100%;*/ /* Full width */
        /*width: 50%;*/
        width: 450px;
        /*height: 100%;*/ /* Full height */
        height: 300px;
        overflow: auto; /* Enable scroll if needed */
        /*background-color: rgb(0,0,0); *//* Fallback color */
        /*background-color: rgba(0,0,0,0.4);*/ /* Black w/ opacity */
        /*background-color: red;*/

    }
    
    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    /* The Close Button */
    .close {
        /*color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;*/
        /*color: #2c2c2c;*/
        /*color: #000000;*/
        text-align: center;
        /*font-size: 18px;*/
        font-weight: normal;
        /*margin: 15px 10px 0 0;*/
        /*padding: 10px 10px;*/
        display: table;
        margin: 0 auto;
        font-weight: bold;
        /*color: #fff;*/
        /*background-color: #428bca;*/ /*the actual blue color*/
        background-color: grey;
        border-color: #357ebd;

        /*display: inline-block;*/
        padding: 9px 12px;
        padding-top: 7px;
        margin-bottom: 0;
        font-size: 14px;
        line-height: 20px;
/*color: #5e5e5e;*/
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
/*background-color: #d1dade;*/
        -webkit-border-radius: 3px;
        -webkit-border-radius: 3px;
        -webkit-border-radius: 3px;
        background-image: none !important;
        border: none;
        text-shadow: none;
        box-shadow: none;
        color: #fff;
        margin-top: 15px;
    }

    .close:hover,
    .close:focus {
        /*color: #000;*/
        /*color: #999999;*/
        text-decoration: none;
        cursor: pointer;
        border-color: transparent;
        background-color: #3a6891;


        text-shadow: 0 -1px 0 rgba(0,0,0,0.2);
-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,0.15),0 1px 1px rgba(0,0,0,0.075);
box-shadow: inset 0 1px 0 rgba(255,255,255,0.15),0 1px 1px rgba(0,0,0,0.075);
    }
</style>

<script type="text/javascript">

// var global_flip_list = "<?php echo $flipList ?>"; 
global_flip_list = <?php echo json_encode($flipListLock); ?>;
console.log("Global flip list: ", global_flip_list);

var current_experiment_page_num = ((<?php echo $gridNumber ?> - 6) - 1)/2; //offset grid number
console.log("Current position in experiment list: ", current_experiment_page_num);
var is_conf = global_flip_list[current_experiment_page_num];
console.log(is_conf);
</script>

<?php
    
    $modGridNumber = (($gridNumber - 6) - 1)/2;
    echo "<div class='gridInstructions'><div class='gridInstructionsInner'>$text</div></div>";

    if($glob_loc_status == 0 || $glob_loc_status == 2) {
        // echo "<div class='warnings'>YOU MUST INPUT &amp; SAVE YOUR CONFIDENCE RATING BEFORE PLACING THE NEXT ITEM.<br /><em>If you decide to move an item to a new location after previously providing your confidence rating, you must delete and update your rating.</em></div>";
        echo "<div class='warnings'>YOU MUST INPUT &amp; SAVE YOUR CONFIDENCE RATING BEFORE PLACING THE NEXT ITEM.<br /><em>If you decide to move an item to a new location after previously providing your confidence rating, <u>click 'Save Rating' twice to dismiss the box</u>.</em></div>";
    }

    echo "<div class='doubleclick'><u>DRAG &amp; DROP</u> THE ITEM INTO THE CELL YOU WOULD LIKE TO PLACE IT.</div>";
    
    echo '<table class="vdrGridTable">';
    for ($y=0; $y<5; ++$y) {
        echo '<tr>';
        for ($x=0; $x<5; ++$x) {
            echo "<td data-coord='$x.$y'>";
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    
    $imageList = array();
    foreach ($images as $i => $img) {
        $imageList[] = "<img src='../Experiment/$img' data-index='$i'>";
        $miniImageList[] = "<img src='../Experiment/$img' width='20%' height='20%' data-index='$i'>";
    }

    /* TODO START: FIX \\ Something with this is causing the SI and user-coord to not _POST */

    /* Check if it is an artifact of the popup menu actually popping up by just doing enough items and then manually skipping to next slide to see if popup itself is causing the SI-block or if it is just the interaction with the popup (i.e., by clicking the span x to close it (because, if so, then removing the span click interaction prevent the data-structure for coord from being removed from memory, which seems to be blocking the rest of the coord structures from being defined, and consequentially, preventing the Si index from being computed due to the undefined storage of the data coordinates (affecting the "data-index='$i').*/

    if($glob_loc_status == 0 || $glob_loc_status == 2) {
    foreach ($miniImageList as $i => $img) {
                echo "<div id='itemModal$i' class='modal'>";
                    echo "<div class='modal-content'>";
                        //echo "<span class='close'>&times;</span>";
                            echo "<p>How confident are you that this item was presented in this location?</p>";
                            echo "<p><em>(1 = Not at all confident, 10 = extremely confident)</em></p>";
                            echo "<span class='confRatingButtonsRow'>";
                                // echo "$img";
                                echo "<input name='JOLItem$i' type='text' value='' id='confRating$i' placeholder='Confidence Rating' maxlength='2' autocomplete='off' class='forceNumeric textcenter collectorInput' style='margin: 0 auto; display: block;' onkeyup='CheckNo(this,$i)' onblur='releaseSubmitIfGood(this,$i)'>";
                                echo "<span class='close'>Save Rating</span>";
                            echo "</span>";
                    echo "</div>";
                echo "</div>";
            }
    /* TODO END: FIX \\ Something with this is causing the SI and user-coord to not _POST */
    }
    shuffle($imageList);
?>

<!-- Restrict input value for confidence ratings -->
<script type="text/javascript">

function releaseSubmitIfGood(sender, numIter){
    var testingItemSubmit = document.getElementsByClassName("close")[numIter];
    if(!isNaN(sender.value)) {
        if((sender.value >= 1) && (sender.value <=10) && (sender.value != '')) {
            testingItemSubmit.style.pointerEvents = "all";
            testingItemSubmit.style.backgroundColor = "#428bca";
        }
    }
}

function CheckNo(sender, numIter){
    var testingItemSubmit = document.getElementsByClassName("close")[numIter];
    if(!isNaN(sender.value)) {
        if(sender.value > 10) {
            alert("You must input a valid confidence rating less than 10 to continue!");
            sender.value = '';
            sender.focus();
            console.log(numIter);
            console.log(testingItemSubmit);
            testingItemSubmit.style.pointerEvents = "none";
            testingItemSubmit.style.backgroundColor = "grey";
            return false;
        } if(sender.value < 1) {
            alert("You must input a valid confidence rating greater than 1 to continue!");
            sender.value = '';
            sender.focus();
            testingItemSubmit.style.pointerEvents = "none";
            testingItemSubmit.style.backgroundColor = "grey";
            return false;
        }  else if(sender.value === '') {
            alert("You must input a valid confidence rating between 1 and 10 to continue!");
            sender.focus(); 
            testingItemSubmit.style.pointerEvents = "none";
            testingItemSubmit.style.backgroundColor = "grey";
            return false;
        } else {
            testingItemSubmit.style.pointerEvents = "all";
            testingItemSubmit.style.backgroundColor = "#428bca";
        }
    } else {
          sender.value = '';
          sender.focus();
    }
}

function CheckNoBlur(sender) {
    if(sender.value === '') {
        alert("You must input a valid confidence rating between 1 and 10 to continue!");
        sender.focus(); 
        return false;
    }
}

function IsEmpty(str) { 
    return !str.replace(/\s+/, '').length;
}

function CheckCorVal(sender) {
    if(!isNaN(sender.value)) {
        if(sender.value > 10) {
            sender.value = '';
        } else if (sender.value < 0) {
            sender.value = '';
        }
    }
}
</script>

<table class="vdrGridTable imgOptions">
    <tr>
    <?php
        foreach ($imageList as $img) {
            echo "<td>$img</td>";
        }
    ?>
    </tr>
</table>

<?php 
if($glob_loc_status == 1 || $glob_loc_status == 2) {
echo "<div class='textcenter'>";
    echo "<hr />";
    echo "<p>Of the 10 items that you placed, how many do you think you correctly placed?";
    echo "<br />";
    echo "<em>(Range 0 to 10)</em></p>";
    echo "<input name='UserRatedCorrectlyPlacedItems' type='text' value='' id='correctlyPlacedItems' placeholder='How Many?' maxlength='2' autocomplete='off' class='forceNumeric textcenter collectorInput corValidate' onblur='CheckCorVal(this)' onkeyup='checkUpdatedIptValue(this)' style='margin: 0 auto; display: block;'>";
echo "</div>";
}
?>

<div class="feedback"></div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance finBtn" type="button">Submit</button>
    <button class="collectorButton collectorAdvance hidden" id="FormSubmitButton">Submit</button>
</div>

<div class="inputs hidden">

</div>

<?php
    $trialData = array (
        'coords' => $imagePlacements,
        'values' => $values
    );
?>
<script>
    var respOrder = [];
    var tone_feedback = <?= json_encode($tone_feedback) ?>;
    
    function swapImgs(td1, td2) {
        var img1 = td1.find("img").detach();
        var img2 = td2.find("img").detach();
        td1.append(img2);
        td2.append(img1);
        
        if (img2.length > 0) highlightSwap(td1);
        if (img1.length > 0) highlightSwap(td2);

        //console.log((document.getElementById('HowManyCorPlaced').value >= 0) && (document.getElementById('HowManyCorPlaced').value <= 10) && (document.getElementById('HowManyCorPlaced').value != ''));
        <?php 
            if($glob_loc_status == 0 || $glob_loc_status == 3) {
                echo "if ($('.imgOptions').find('img').length === 0) {";
            // } elseif ($glob_loc_status == 0) {
                // echo "if (($('.imgOptions').find('img').length === 0) || ((document.getElementByID('confRating0').value != '') && (document.getElementByID('confRating1').value > 0) && (document.getElementByID('confRating2').value > 0) && (document.getElementByID('confRating3').value > 0) && (document.getElementByID('confRating4').value > 0) && (document.getElementByID('confRating5').value > 0) && (document.getElementByID('confRating6').value > 0) && (document.getElementByID('confRating7').value > 0) && (document.getElementByID('confRating8').value > 0) && (document.getElementByID('confRating9').value > 0))) {";
            } elseif ($glob_loc_status == 1 || $glob_loc_status == 2) {
                echo "if (($('.imgOptions').find('img').length === 0) && (document.getElementById('correctlyPlacedItems').value >= 0) && (document.getElementById('correctlyPlacedItems').value <= 10) && (document.getElementById('correctlyPlacedItems').value != '')) {";
            }
        ?>
            $("button").prop("disabled", false);
        } else {
            $("button").prop("disabled", true);
        }
    }

    function checkUpdatedIptValue(sender) {
        if (($(".imgOptions").find("img").length === 0) && (sender.value >= 0) && (sender.value <= 10) && (sender.value != '')) {
            $("button").prop("disabled", false);
        } else {
            $("button").prop("disabled", true);
        }
    }
    
    function highlightSwap(td) {
        var imgIndex = td.find("img").data("index");
        var i = respOrder.indexOf(imgIndex);
        if (i !== -1) {
            respOrder.splice(i, 1);
        }
        respOrder.push(imgIndex);
        
        var tds, offset;
        var flash = $("<div>");

        flash.css("position", "absolute");
        flash.css("visibility", "visible");
        flash.css("padding", "0");
        flash.css("margin", "0");
        flash.css("background-color", "rgba(120, 255, 40, 0.1)");
        flash.css("box-shadow", "1px 1px 20px 9px #2FE22F");
        
        flash.css("position", "absolute");
        flash.css("pointer-events", "none");
        
        offset = $(td).offset();
        flash.width($(td).outerWidth());
        flash.height($(td).outerHeight());
        flash.css("top", offset.top);
        flash.css("left", offset.left);
        
        $("body").append(flash);
        flash.fadeOut(800, function() { $(this).remove(); });
    }
    
    var trialData = <?= json_encode($trialData) ?>;
    
    var dragging = false;
    var dragTable = $("<table>");
    dragTable.css("position", "absolute");
    dragTable.append("<tr><td></td></tr>");
    dragTable.find("td").css({
        boxShadow: "1px 1px 10px 4px #30B0E0",
        cursor: "move"
    });
    dragTable.css({
        pointerEvents: "none"
    });
    dragTable.addClass("vdrGridTable");
    
    var mouseX, mouseY;
    var offX, offY;
    
    $("td").on("mousedown", function() {
        if ($("body").hasClass("finished")) return;
        var _this = $(this);
        var _curr = $(".current");
        if (_curr.length > 0) {
            swapImgs(_this, _curr);
            _curr.removeClass("current");
            return false;
        }
        if (_this.find("img").length < 1) return false;
        var thisImg = _this.find("img").detach();
        var thisOffset = _this.offset();
        var mouseOffset = {top: mouseY, left: mouseX};
        _this.addClass("imgOrigLoc");
        offX = mouseOffset.left - thisOffset.left;
        offY = mouseOffset.top  - thisOffset.top;
        dragTable.find("td").append(thisImg);
        $("body").append(dragTable);
        dragTable.css({top: thisOffset.top, left: thisOffset.left});
        dragging = true;
        $("body").addClass("gettingDragged");
        var doubleclickStatus = document.getElementsByClassName("doubleclick")[0];
        doubleclickStatus.style.visibility = "visible";
        return false;
    });
    
    $("body").on("mousemove", function(e) {
        if ($("body").hasClass("finished")) return;
        mouseX = e.pageX;
        mouseY = e.pageY;
        if (!dragging) return;
        dragTable.offset({top: e.pageY - offY, left: e.pageX - offX});
    });
    </script>
    
    <?php
    if($glob_loc_status == 0 || $glob_loc_status == 2) {
    echo "<script>";
    echo "
    $('body').on('mouseup', function() {
        if ($('body').hasClass('finished')) return;
        if (!dragging) return;
        dragging = false;
        $('body').removeClass('gettingDragged');
        var img = dragTable.find('img').detach();
        var orig = $('.imgOrigLoc');
        orig.append(img);
        var target = $('td:hover:not(.imgOrigLoc)');
        if (target.length === 1) {
            swapImgs(orig, target);
        } else {
            orig.addClass('current');
        }
        orig.removeClass('imgOrigLoc');
        dragTable.detach();
        
        var doubleclickStatus = document.getElementsByClassName('doubleclick')[0];
        doubleclickStatus.style.visibility = 'visible';";

        echo "if(($('body').hasClass('imgOrigLoc')==false) && ($('body').hasClass('finished')==false)) {";

            echo "
            if(img.attr('data-index') == 0) {
                console.log('This item is indexed at 0');
                target.addClass('activelyRating');
                var modalItemZero = document.getElementById('itemModal0');
                var confZero = document.getElementById('confRating0');
                modalItemZero.style.display = 'block';
                confZero.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                // warningStatus.style.display = 'block';
                var spanZero = document.getElementsByClassName('close')[0];
                spanZero.style.pointerEvents = 'none';
                spanZero.style.backgroundColor = 'grey';
                spanZero.onclick = function() {
                    modalItemZero.style.display = 'none';
                    target.removeClass('activelyRating');
                    // warningStatus.style.display = 'none';
                    warningStatus.style.visibility = 'hidden';
                    spanZero.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                    // if (modalItemZero.style.display == 'none') {
                    //     console.log('It was successful');
                    // }
                }

            }";

            echo "
            if (img.attr('data-index') == 1) {
                console.log('This item is indexed at 1');
                target.addClass('activelyRating');
                var modalItemOne = document.getElementById('itemModal1');
                var confOne = document.getElementById('confRating1');
                modalItemOne.style.display = 'block';
                confOne.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanOne = document.getElementsByClassName('close')[1];
                spanOne.style.pointerEvents = 'none';
                spanOne.style.backgroundColor = 'grey';
                spanOne.onclick = function() {
                    modalItemOne.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanOne.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";

                
            echo "
            if (img.attr('data-index') == 2) {
                console.log('This item is indexed at 2');
                target.addClass('activelyRating');
                var modalItemTwo = document.getElementById('itemModal2');
                var confTwo = document.getElementById('confRating2');
                modalItemTwo.style.display = 'block';
                confTwo.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanTwo = document.getElementsByClassName('close')[2];
                spanTwo.style.pointerEvents = 'none';
                spanTwo.style.backgroundColor = 'grey';
                spanTwo.onclick = function() {
                    modalItemTwo.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanTwo.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 3) {
                console.log('This item is indexed at 3');
                target.addClass('activelyRating');
                var modalItemThree = document.getElementById('itemModal3');
                var confThree = document.getElementById('confRating3');
                modalItemThree.style.display = 'block';
                confThree.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanThree = document.getElementsByClassName('close')[3];
                spanThree.style.pointerEvents = 'none';
                spanThree.style.backgroundColor = 'grey';
                spanThree.onclick = function() {
                    modalItemThree.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanThree.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 4) {
                console.log('This item is indexed at 4');
                target.addClass('activelyRating');
                var modalItemFour = document.getElementById('itemModal4');
                var confFour = document.getElementById('confRating4');
                modalItemFour.style.display = 'block';
                confFour.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanFour = document.getElementsByClassName('close')[4];
                spanFour.style.pointerEvents = 'none';
                spanFour.style.backgroundColor = 'grey';
                spanFour.onclick = function() {
                    modalItemFour.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanFour.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 5) {
                console.log('This item is indexed at 5');
                target.addClass('activelyRating');
                var modalItemFive = document.getElementById('itemModal5');
                var confFive = document.getElementById('confRating5');
                modalItemFive.style.display = 'block';
                confFive.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanFive = document.getElementsByClassName('close')[5];
                spanFive.style.pointerEvents = 'none';
                spanFive.style.backgroundColor = 'grey';
                spanFive.onclick = function() {
                    modalItemFive.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanFive.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 6) {
                console.log('This item is indexed at 6');
                target.addClass('activelyRating');
                var modalItemSix = document.getElementById('itemModal6');
                var confSix = document.getElementById('confRating6');
                modalItemSix.style.display = 'block';
                confSix.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanSix = document.getElementsByClassName('close')[6];
                spanSix.style.pointerEvents = 'none';
                spanSix.style.backgroundColor = 'grey';
                spanSix.onclick = function() {
                    modalItemSix.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanSix.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 7) {
                console.log('This item is indexed at 7');
                target.addClass('activelyRating');
                var modalItemSeven = document.getElementById('itemModal7');
                var confSeven = document.getElementById('confRating7');
                modalItemSeven.style.display = 'block';
                confSeven.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanSeven = document.getElementsByClassName('close')[7];
                spanSeven.style.pointerEvents = 'none';
                spanSeven.style.backgroundColor = 'grey';
                spanSeven.onclick = function() {
                    modalItemSeven.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanSeven.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 8) {
                console.log('This item is indexed at 8');
                target.addClass('activelyRating');
                var modalItemEight = document.getElementById('itemModal8');
                var confEight = document.getElementById('confRating8');
                modalItemEight.style.display = 'block';
                confEight.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanEight = document.getElementsByClassName('close')[8];
                spanEight.style.pointerEvents = 'none';
                spanEight.style.backgroundColor = 'grey';
                spanEight.onclick = function() {
                    modalItemEight.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanEight.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }";
            echo "
            if (img.attr('data-index') == 9) {
                console.log('This item is indexed at 9');
                target.addClass('activelyRating');
                var modalItemNine = document.getElementById('itemModal9');
                var confNine = document.getElementById('confRating9');
                modalItemNine.style.display = 'block';
                confNine.focus();
                $('body').addClass('finished');
                var warningStatus = document.getElementsByClassName('warnings')[0];
                warningStatus.style.visibility = 'visible';
                var spanNine = document.getElementsByClassName('close')[9];
                spanNine.style.pointerEvents = 'none';
                spanNine.style.backgroundColor = 'grey';
                spanNine.onclick = function() {
                    modalItemNine.style.display = 'none';
                    target.removeClass('activelyRating');
                    warningStatus.style.visibility = 'hidden';
                    spanNine.style.pointerEvents = 'all';
                    $('body').removeClass('finished');
                }
            }

        }

    });";

    echo "</script>";
    }
    ?>
    <?php 
    if($glob_loc_status == 0 || $glob_loc_status == 1 || $glob_loc_status == 2 || $glob_loc_status == 3) {
        echo "<script>";
        echo "
        $('body').on('mouseup', function() {
            if ($('body').hasClass('finished')) return;
            if (!dragging) return;
            dragging = false;
            $('body').removeClass('gettingDragged');
            var img = dragTable.find('img').detach();
            var orig = $('.imgOrigLoc');
            orig.append(img);
            var target = $('td:hover:not(.imgOrigLoc)');
            if (target.length === 1) {
                swapImgs(orig, target);
            } else {
                orig.addClass('current');
            }
            orig.removeClass('imgOrigLoc');
            dragTable.detach();
        });";
        echo "</script>";
    }
  
    ?>

    
    <script>
    $("body").on("mousedown", function() {
        $(".current").removeClass("current");
    });
    
    $("button").prop("disabled", true);
    
    function addBonus(td, bonus, delay) {
        var bonusShow = $("<span>");
        bonusShow.html(bonus);
        bonusShow.addClass("bonus");
        
        td.append(bonusShow);
        bonusShow.hide().delay(delay).show(300);
    }
    
    $(".finBtn").on("click", function() {
        $(this).hide();
        $("#FormSubmitButton").removeClass("hidden").prop("disabled", true);
        $("body").addClass("finished");
        
        var correct = 0;
        var bonusEarned = 0;

        var converted_flipped_coin = <?php echo $flip ?>;
        
        $("form img").each(function() {
            var _this = $(this);
            var imgIndex = _this.data("index");
            var coord = _this.closest("td").data("coord");
            var i = parseInt(imgIndex)+1;
            var val = trialData.values[imgIndex];
            var ans = trialData.coords[imgIndex];
            $(".inputs").append(
                "<input name='Coord" + i + "' value='" + coord + "'>"
              + "<input name='Value" + i + "' value='" + val   + "'>"
              + "<input name='CorrectCoord" + i + "' value='" + ans + "'>"
              + "<input name='IsCONF' value='" + is_conf + "'>"
            );
            if (ans == coord) {
                addBonus(_this.closest("td"), val, correct*100);
                ++correct;
                bonusEarned += parseInt(val);
                $(".inputs").append(
                    "<input name='Correct" + i + "' value='1'>"
                );
            } else {
                $(".inputs").append(
                    "<input name='Correct" + i + "' value='0'>"
                );
            }
        });
        
        var totalPossible = 0;
        
        for (i=0, len=trialData.values.length; i<len; ++i) {
            totalPossible += parseInt(trialData.values[i]);
        }
        
        var percentage = Math.round(bonusEarned / totalPossible * 100);
        
        $(".feedback").html(
            "You got " + bonusEarned + " points out of " + totalPossible + ". "
          + "This means you got " + percentage + "% of the points available."
          + tone_feedback
        );
        
        var delayTime = correct*100 + 300 + 500;
        $(".feedback").delay(delayTime).show(400);
        $(".gridInstructions").delay(delayTime).hide(400);
        
        COLLECTOR.timer((delayTime+500)/1000, function() {
            $("#FormSubmitButton").prop("disabled", false);
        });
        
        var imgIndex = 1;
        while (respOrder.length > 0) {
            var respO = parseInt(respOrder.shift(respOrder)) + 1;
            $(".inputs").append(
                "<input name='RespOrder" + respO + "' value='" + imgIndex + "'>"
            );
            ++imgIndex;
        }
        $("#correctlyPlacedItems").prop("readonly", true);
    });
</script>
