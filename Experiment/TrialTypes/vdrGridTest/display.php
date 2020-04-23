<?php
    $images = explode('|', $cue);
    $values = explode('|', $value);
    
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
        $imageOrder     = explode('|', $_SESSION['Trials'][$gridNumber]['Grid']['images']);
        $gridPlacements = json_decode($_SESSION['Trials'][$gridNumber]['Grid']['grid'], true);
    }
    
    $imagePlacements = array();
    foreach ($gridPlacements as $y => $row) {
        foreach ($row as $x => $imageNumber) {
            $imagePlacements[$imageNumber] = "$x.$y";
        }
    }
    ksort($imagePlacements);
    
?><style>
    body > form { width: initial !important; }
    
    .gridInstructions {
        font-size: 120%;
        width: 650px;
        margin: 0 auto 20px;
        text-align: center;
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
</style><?php
    
    echo "<div class='gridInstructions'><div class='gridInstructionsInner'>$text</div></div>";
    
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
    }
    shuffle($imageList);
?>

<table class="vdrGridTable imgOptions">
    <tr>
    <?php
        foreach ($imageList as $img) {
            echo "<td>$img</td>";
        }
    ?>
    </tr>
</table>

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
    
    function swapImgs(td1, td2) {
        var img1 = td1.find("img").detach();
        var img2 = td2.find("img").detach();
        td1.append(img2);
        td2.append(img1);
        
        if (img2.length > 0) highlightSwap(td1);
        if (img1.length > 0) highlightSwap(td2);
        
        if ($(".imgOptions").find("img").length === 0) {
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
        return false;
    });
    
    $("body").on("mousemove", function(e) {
        if ($("body").hasClass("finished")) return;
        mouseX = e.pageX;
        mouseY = e.pageY;
        if (!dragging) return;
        dragTable.offset({top: e.pageY - offY, left: e.pageX - offX});
    });
    
    $("body").on("mouseup", function() {
        if ($("body").hasClass("finished")) return;
        if (!dragging) return;
        dragging = false;
        $("body").removeClass("gettingDragged");
        var img = dragTable.find("img").detach();
        var orig = $(".imgOrigLoc");
        orig.append(img);
        var target = $("td:hover:not(.imgOrigLoc)");
        if (target.length === 1) {
            swapImgs(orig, target);
        } else {
            orig.addClass("current");
        }
        orig.removeClass("imgOrigLoc");
        dragTable.detach();
    });
    
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
    });
</script>
