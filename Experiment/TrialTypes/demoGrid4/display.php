<?php
    $src = fileExists(dirname($trialFiles['display']) . '/seqDemo.gif');
    $texts = explode('|', $text);
?>
<style>
    .allArea {
        width: 600px;
    }
    .allArea > div {
        margin: 20px auto;
    }
    img {
        width: 100%;
    }
    .imgHolder {
        width: 75%;
    }
</style>

<div class="allArea">
    <div><?php echo $texts[0]; ?></div>
    
    <div class="imgHolder"><img src="<?= $src ?>"></div>
    
    <div><?= isset($texts[1]) ? $texts[1] : '' ?></div>

    <div class="textcenter">
        <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
    </div>
</div>