<?php
    $images = explode('|', $cue);
    $values = explode('|', $value);
    
    $mask = dirname($trialFiles['display']) . '/mask.png';
    
    if (!isset($_SESSION['Trials'][$currentPos]['Grid'])) {
        $imageOrder = array_keys($images);
        shuffle($imageOrder);
    
        if (count($imageOrder) < 10) trigger_error("Error: insufficient images provided", E_USER_WARNING);
        
        while (true) {
            $gridPlacements   = array();
            $columns          = array(0, 1, 2, 3, 4);
            $columnsUsed      = array(0, 0, 0, 0, 0);
            
            for ($y=0; $y<5; ++$y) {
                if (count($columns) < 2) continue 2; // bad arrangement, used up 4 columns in 4 rows, cant make 2 items in 5th row
                $columnsAvailable = $columns;
                shuffle($columnsAvailable);
                $x1 = $columnsAvailable[0];
                $x2 = $columnsAvailable[1];
                $gridPlacements[$y][$x1] = $imageOrder[$y*2];
                $gridPlacements[$y][$x2] = $imageOrder[$y*2+1];
                ++$columnsUsed[$x1];
                ++$columnsUsed[$x2];
                if ($columnsUsed[$x1] >= 2) unset($columns[$x1]);
                if ($columnsUsed[$x2] >= 2) unset($columns[$x2]);
            }
            break;
        }
        
        $_SESSION['Trials'][$currentPos]['Grid']['images'] = implode('|', $imageOrder);
        $_SESSION['Trials'][$currentPos]['Grid']['grid']   = json_encode($gridPlacements);
    } else {
        $imageOrder     = explode('|', $_SESSION['Trials'][$currentPos]['Grid']['images']);
        $gridPlacements = json_decode($_SESSION['Trials'][$currentPos]['Grid']['grid'], true);
    }
    
?><style>
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
    .vdrGridTable td > * {
        visibility: hidden;
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
    .imgHolder {
        display: none;
        text-align: center;
    }
    .imgHolder img {
        width: 546px;
        height: 546px;
    }
</style><?php
    
    echo "<div class='gridInstructions'><div class='gridInstructionsInner'>$text</div></div>";
    
    echo '<table class="vdrGridTable">';
    for ($y=0; $y<5; ++$y) {
        echo '<tr>';
        for ($x=0; $x<5; ++$x) {
            if (isset($gridPlacements[$y][$x])) {
                $itemNumber = $gridPlacements[$y][$x];
                echo "<td class='item$itemNumber'>";
                echo show($images[$itemNumber]);
                echo "<span class='cellValue'>{$values[$itemNumber]}</span>";
            } else {
                echo '<td>';
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<div class="imgHolder"><img src="' . $mask . '"></div>';
?>
<div><?php echo $text; ?></div>

<div class="hidden">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
    var itemTime = 3;
    var curImg = 0;
    function showNext() {
        var nextImg = ".item" + curImg;
        $(nextImg).children().css("visibility", "visible");
        ++curImg;
        COLLECTOR.timer(itemTime, function() {
            $(nextImg).children().css("visibility", "hidden");
            if (curImg < 10) {
                COLLECTOR.timer(0.25, showNext);
            } else {
                $("#FormSubmitButton").click();
            }
        });
    }
    COLLECTOR.experiment.<?= $trialType ?> = function() {
        COLLECTOR.timer(1, showNext);
    }
    
    var masked = false;
    $("form").submit(function(e) {
        if (!masked) {
            $("table").hide();
            $(".imgHolder").show();
            setTimeout(function() {
                $("form").removeClass("invisible").data('submitted', false);
            }, 10);
            masked = true;
            COLLECTOR.timer(0.5, function() {
                $("form").submit();
            });
            e.preventDefault();
            return;
        }
    });
</script>
