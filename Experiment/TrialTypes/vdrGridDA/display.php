<?php
    $images = explode('|', $cue);
    $values = explode('|', $value);
    
    $mask = dirname($trialFiles['display']) . '/mask.png';
    
    //if (!isset($_SESSION['Trials'][$currentPos]['Grid'])) {
    if (1) {
        $imageList = array_keys($images);
        shuffle($imageList);
    
        if (count($imageList) < 10) trigger_error("Error: insufficient images provided", E_USER_WARNING);
        
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
                $gridPlacements[$y][$x1] = $imageList[$y*2];
                $gridPlacements[$y][$x2] = $imageList[$y*2+1];
                ++$columnsUsed[$x1];
                ++$columnsUsed[$x2];
                if ($columnsUsed[$x1] >= 2) unset($columns[$x1]);
                if ($columnsUsed[$x2] >= 2) unset($columns[$x2]);
            }
            break;
        }
        
        $_SESSION['Trials'][$currentPos]['Grid']['grid']   = json_encode($gridPlacements);
    } else {
        $gridPlacements = json_decode($_SESSION['Trials'][$currentPos]['Grid']['grid'], true);
    }
    
    require __DIR__ . '/settings.php';
    
    $settings = parse_settings($settings);
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
    
    #ToneResponseRequestContainer {
        text-align: center;
    }
    
    #ToneResponseRequest {
        text-align: left;
        width: 400px;
        margin: 0 auto 20px;
        border: 2px solid #666;
        background-color: #BBB;
        padding: 5px 8px;
        font-size: 130%;
    }
    
    #ToneResponseRequest::before {
        content: "Tone Response: ";
    }
</style><?php
    
    echo "<div class='gridInstructions'><div class='gridInstructionsInner'>$text</div></div>";
    
    echo '<div id="ToneResponseRequestContainer"></div>';
    
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
	<input type="hidden" name="Study_Protocol" value="<?= $settings['study protocol'] ?>">
</div>

<script>
var debug_mode = false;

var Settings = <?= json_encode($settings) ?>;

function simultaneous_start() {
    var study_time = Settings['study time per item'];
    $(".vdrGridTable td > *").css("visibility", "visible");
    
    for (var i=0; i<10; ++i) {
        COLLECTOR.timer(i*study_time, play_tone);
    }
    
    COLLECTOR.timer(10*study_time, end_trial);
}

var sequential = {
    current_index: -1,
    
    study_next: function() {
        var study_time = Settings['study time per item'];
        var currImg = ".item" + this.current_index;
        ++this.current_index;
        var nextImg = ".item" + this.current_index;
        
        $(currImg).children().css("visibility", "hidden");
        $(nextImg).children().css("visibility", "visible");
		
        play_tone();
        
        if (this.current_index === 9) {
            COLLECTOR.timer(study_time, end_trial);
        } else {
            COLLECTOR.timer(study_time, function() { sequential.study_next(); });
        }
    }
}

function end_trial() {
    $("form > *").css("visibility", "hidden");
    $("table").hide();
    $(".imgHolder").show().css("visibility", "visible");
    
    COLLECTOR.timer(0.1, function() {
        tones.end_current_response_set();
        tones.add_inputs_to_form($("form"));
    });
    
    if (!debug_mode) {
        COLLECTOR.timer(0.5, function() {
            $("form").submit();
        });
    }
}

COLLECTOR.experiment.<?= $trialType ?> = function() {
    if (Settings['tones'] === 'on') {
        tones.prepare(Settings['study time per item'], $("#ToneResponseRequestContainer"));
    }
    
    play_tone();
    if (Settings['study protocol'] === 'simultaneous') {
        var start_func = simultaneous_start;
    } else {
        var start_func = function() {
            sequential.study_next();
        }
    }
    
    var study_time = Settings['study time per item'];
    
    COLLECTOR.timer(study_time, start_func);
};

var tones = {
    context:  null,
    tones:    null,
    sequence: null,
    sequence_position: null,
    tone_interval: null,
    strict_time_limit: null,
    responses: [],
	response_summary: {},
    response_keys: null,
    current_response_set: null,
    max_gain: 1,
    response_request_notice: null,
    
    prepare: function(tone_interval, response_request_notice_container) {
        this.context = new AudioContext;
        this.tones = [
            this.create_tone(400),
            this.create_tone(900)
        ];
        this.sequence = this.get_sequence();
        this.sequence_position = 0;
        this.tone_interval = tone_interval;
        this.strict_time_limit = 2000;
        
        this.response_keys = {
            '.': 'Same',
            '[': 'Different'
        };
        this.set_event_handlers();
        
        this.add_response_request_notice(response_request_notice_container);
		
		this.response_summary = {};
		
		var scoring_categories = [
			'all_correct', 'all_correct_strict', 'first_correct', 'first_correct_strict',
			'last_correct', 'last_correct_strict', 'correct_response_count', 'correct_response_count_strict',
			'incorrect_response_count', 'incorrect_response_count_strict'
		];
		
		for (var i=0; i<scoring_categories.length; ++i) {
			this.response_summary[scoring_categories[i]] = 0;
		}
    },
    
    create_tone: function(freq) {
        var oscillator = this.context.createOscillator();
        oscillator.type = 'sine';
        oscillator.frequency.value = freq;
        oscillator.start();
        
        var gain = this.context.createGain();
        gain.gain.value = 0;
        
        oscillator.connect(gain);
        gain.connect(this.context.destination);
        
        return {
            oscillator: oscillator,
            gain: gain,
        };
    },
    
    get_sequence: function() {
        do {
            var sequence = this.get_random_sequence();
        } while (!this.is_valid_sequence(sequence));
        
        return sequence;
    },
    
    get_random_sequence: function() {
        var sequence = [];
        
        for (var i=0; i<11; ++i) {
            sequence.push(Math.floor(Math.random()*2));
        }
        
        return sequence;
    },
    
    is_valid_sequence: function(sequence) {
        /* check if tones 1 and 2 each have at least 4 occurrences
           check if the tone stays the same at least 4 times
           check if the tone changes at least 4 times 
           check if the tone changes at least once every 3 tones */
        
        var pitch_counts = [0, 0];
        var pitch_change_counts = [0, 0];
        
        for (var i=0; i<sequence.length; ++i) {
            ++pitch_counts[sequence[i]];
        }
        
        for (i=1; i<sequence.length; ++i) {
            if (sequence[i] === sequence[i-1]) {
                ++pitch_change_counts[0];
            } else {
                ++pitch_change_counts[1];
            }
        }
        
        var last_tone = sequence[0];
        var last_tone_count = 1;
        var longest_same_tone_run = 1;
        
        for (i=1; i<sequence.length; ++i) {
            if (sequence[i] === last_tone) {
                ++last_tone_count;
                longest_same_tone_run = Math.max(longest_same_tone_run, last_tone_count);
            } else {
                last_tone = sequence[i];
                last_tone_count = 1;
            }
        }
        
        return pitch_counts[0] > 3
            && pitch_counts[1] > 3
            && pitch_change_counts[0] > 3
            && pitch_change_counts[1] > 3
            && longest_same_tone_run < 4;
    },
    
    play_next: function() {
        var tone_index = this.sequence[this.sequence_position];
        var tone = this.tones[tone_index];
        
        tone.gain.gain.value = this.max_gain;
        
        COLLECTOR.timer(1, silence_tones);
        
        if (this.sequence_position > 0) {
            var self = this;
            
            COLLECTOR.timer(.1, function() {
                self.prepare_new_response_set();
                self.set_response_request_notice_message('awaiting response...');
            });
        }
        
        ++this.sequence_position;
    },
    
    set_event_handlers: function() {
        var self = this;
        
        $(document).on("keypress", function(e) {
            self.process_key_event(String.fromCharCode(e.which));
        });
    },
    
    prepare_new_response_set: function() {
        if (this.current_response_set !== null) {
            this.end_current_response_set();
        }
        
        var answer = (    this.sequence[this.sequence_position - 1]
                      === this.sequence[this.sequence_position - 2])
                   ? 'Same' : 'Different';
        
        this.current_response_set = {
            status: "no response",
            answer: answer,
            timestamp: Date.now()-100-COLLECTOR.startTime, // response is requested after 100ms, to prevent presumptive responses
            responses: [],
            response_times: [],
            reaction_time: null,
            all_correct:          null,
            all_correct_strict:   null,
            first_correct:        null,
            first_correct_strict: null,
            last_correct:         null,
            last_correct_strict:  null,
            correct_response_count:          0,
            correct_response_count_strict:   0,
            incorrect_response_count:        0,
            incorrect_response_count_strict: 0,
        };
    },
    
    end_current_response_set: function() {
        var scoring = [
			'all_correct', 'all_correct_strict', 'first_correct',
			'first_correct_strict', 'last_correct', 'last_correct_strict'
		];
        
        for (var i=0; i<scoring.length; ++i) {
            if (this.current_response_set[scoring[i]] === null)
                this.current_response_set[scoring[i]] = 0;
			
			if (this.current_response_set[scoring[i]] === 1)
				++this.response_summary[scoring[i]];
        }
		
		var response_counts = [
			'correct_response_count', 'correct_response_count_strict',
			'incorrect_response_count', 'incorrect_response_count_strict'
		];
		
		for (var i=0; i<response_counts.length; ++i) {
			this.response_summary[response_counts[i]] += this.current_response_set[response_counts[i]];
		}
        
        this.responses.push(this.current_response_set);
        this.current_response_set = null;
        this.set_response_request_notice_message("");
    },
    
    process_key_event: function(key) {
        if (this.current_response_set === null) return;
        if (typeof this.response_keys[key] === "undefined") return;
        
        var resp_set = this.current_response_set;
        
        var reaction_time = Date.now() - resp_set.timestamp - COLLECTOR.startTime;
        var response = this.response_keys[key];
        var is_correct = resp_set.answer === response;
        var is_late = reaction_time > 2000;
        
        resp_set.responses     .push(response);
        resp_set.response_times.push(reaction_time);
        
        this.set_response_request_notice_message(response);
        
        // additional scoring
        if (resp_set.reaction_time === null) {
            resp_set.reaction_time = reaction_time;
            
            if (is_late) {
                resp_set.status = "late";
            } else {
                resp_set.status = "on time";
            }
        }
            
        if (resp_set.first_correct === null) resp_set.first_correct = is_correct ? 1 : 0;
        if (resp_set.first_correct_strict === null && !is_late)
            resp_set.first_correct_strict = is_correct ? 1 : 0;
        
        if (is_correct) {
            ++resp_set.correct_response_count;
            if (!is_late) ++resp_set.correct_response_count_strict;
            if (resp_set.all_correct === null) resp_set.all_correct = 1;
            if (!is_late && resp_set.all_correct_strict === null) resp_set.all_correct_strict = 1;
            resp_set.last_correct = 1;
            if (!is_late) resp_set.last_correct_strict = 1;
        } else {
            ++resp_set.incorrect_response_count;
            if (!is_late) ++resp_set.incorrect_response_count_strict;
            resp_set.all_correct = 0;
            if (!is_late) resp_set.all_correct_strict = 0;
            resp_set.last_correct = 0;
            if (!is_late) resp_set.last_correct_strict = 0;
        }
    },
    
    add_inputs_to_form: function($form) {
        var tone_responses = JSON.stringify(this.responses);
        var inp = "<input type='hidden' name='Tone_Responses' value='" + tone_responses + "'>";
		
		var tone_summary = JSON.stringify(this.response_summary);
		inp += "<input type='hidden' name='Tone_Response_Summary' value='" + tone_summary + "'>";
		
        var tones = this.sequence.join(',');
        inp += "<input type='hidden' name='Tones' value='" + tones + "'>";
		
        $form.append(inp);
    },
    
    add_response_request_notice: function(container) {
        var notice = $("<div id='ToneResponseRequest'>");
        this.response_request_notice = notice;
        notice.appendTo(container);
    },
    
    set_response_request_notice_message(message) {
        this.response_request_notice.html(message);
    }
}

function play_tone() {
    if (tones.context !== null) tones.play_next();
}

function silence_tones() {
    if (tones.context !== null) {
        for (var i=0; i<tones.tones.length; ++i) {
            tones.tones[i].gain.gain.value = 0;
        }
    }
}

function stop_tones() {
    silence_tones();
    tones.context = null;
}

function start_debug_mode() {
    var debug_notice = $("<div>Debug Mode</div>");
    debug_notice.css({
        position: "fixed",
        top: "0",
        left: "0",
        right: "0",
        padding: "5px",
        textAlign: "center",
        opacity: "0.5",
        backgroundColor: "#AAA"
    });
    debug_notice.appendTo("body");
    tones.max_gain = 0.1; // fricking tones
    $("form").append("<input type='hidden' name='DEBUG_MODE' value='YES'>");
    Settings['study protocol'] = 'simultaneous';
	$("input[name='Study_Protocol']").val("simultaneous");
}

if (debug_mode) start_debug_mode();
</script>
