<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
    
    $title = 'Basic Information';
    require $_PATH->get('Header');
?>
<style>    
    #content {
        width:auto;
        min-width: 400px;
        /*Make the flexchild, form, fit the basic info content size*/
    }
</style>
<form id="content" class="basicInfo" name="Demographics"
      action="<?= $_PATH->get('Basic Info Record') ?>" method="post" autocomplete="off">
    
    <fieldset>
        <legend><h1>Basic Information</h1></legend>
        
        
        <section class="radioButtons">
            <h3>Gender</h3>
            <label><input name="Gender" type="radio" value="Male"   required/>Male</label>
            <label><input name="Gender" type="radio" value="Female" required/>Female</label>
            <label><input name="Gender" type="radio" value="Other"  required/>Other</label>
        </section>
        
        
        <section>
            <label>
                <h3>Age</h3>
                <input name="Age" class="wide collectorInput" type="text"
                pattern="[0-9][0-9]" value="" autocomplete="off" required/>
            </label>
        </section>
        
        
        <section>
            <label>
                <h3>Education</h3>
                <select name="Education" class="wide collectorInput" required>
                    <option value="" default selected>Select Level</option>
                    <option>Some High School</option>
                    <option>High School Graduate</option>
                    <option>Some College, no degree</option>
                    <option>Associates degree</option>
                    <option>Bachelors degree</option>
                    <option>Graduate degree (Masters, Doctorate, etc.)</option>
                </select>
            </label>
        </section>
        
        
        <section class="radioButtons">
            <h3>Are you Hispanic?</h3>
            <label><input name="Hispanic" type="radio" value="Yes"   required/>Yes</label>
            <label><input name="Hispanic" type="radio" value="No"    required/>No</label>
        </section>
        
        
        <section>
            <label>
                <h3>Ethnicity</h3>
                <select name="Race" required class="wide collectorInput">
                    <option value="" default selected>Select one</option>
                    <option>American Indian/Alaskan Native</option>
                    <option>Asian/Pacific Islander</option>
                    <option>Black</option>
                    <option>White</option>
                    <option>Other/unknown</option>
                </select>
            </label>
        </section>
        
        
        <section class="radioButtons">
            <h3>Do you speak english fluently?</h3>
            <label><input name="Fluent" type="radio" value="Yes"   required/>Yes</label>
            <label><input name="Fluent" type="radio" value="No"    required/>No</label>
        </section>
        
        
        <section>
            <label>
                <h3>At what age did you start learning English?</h3>
                <input name="AgeEnglish" type="text" value="" autocomplete="off" class="wide collectorInput"/>
                <div class="small shim">If English is your first language please enter 0.</div>
            </label>
        </section>
        
        
        <section>
            <label>
                <h3>What is your country of residence?</h3>
                <input name="Country" type="text" value="" autocomplete="off" class="wide collectorInput"/>
            </label>
        </section>
        
        
        <section class="consent">
            <legend><h3>Informed Consent</h3></legend>
            <textarea readonly>
University of California, Los Angeles

RESEARCH INFORMATION SHEET

Learning and Cognition

You are asked to participate in a research study conducted by Dr. Alan Castel and students, from the Department of Psychology at the University of California, Los Angeles. Your participation in this research study is voluntary.

PURPOSE OF THE STUDY
The purpose of the study is to learn more about learning and cognition, how we process different types of information, and how one can predict cognitive performance.

PROCEDURES
If you volunteer to participate in this study, we would ask you to learn and respond to various types of information. For example, you might be presented with different types of word pairs, some which may have some emotional content, and you will need to read them and make predictions about how well you will learn them. In other cases, you might be asked to write an essay about yourself. You may also be asked to answer some questions. We are interested in how you respond to such information, as well as how accurately you can predict how well you will learn this information. This research will be conducted on the 6th or 7th floor of Franz Hall, and the session usually lasts less than one hour. In some cases, you will be asked to participate in the second session as a follow-up, which will last less than 30 minutes.

POTENTIAL RISKS AND DISCOMFORTS
There are no risks or discomforts, except possibly mild boredom.

POTENTIAL BENEFITS TO SUBJECTS AND/OR TO SOCIETY
You will not directly benefit from your participation in the research. However, the results of the research may contribute to a better understanding of learning, attention and emotion.

PAYMENT FOR PARTICIPATION
You will receive no payment for your participation. If participating for course credit, you will receive one hour of course credit for your participation. If you are asked to participate in the second session, you will receive additional half hour course credit for your second participation.

ALTERNATIVES TO PARTICIPATION
An alternative to participating in this project and fulfilling the Psychology 10 research requirement is to participate in other research or to do an equivalent classroom project.

CONFIDENTIALITY
Any information that is obtained in connection with this study and that can be identified with you will remain confidential and will be disclosed only with your permission or as required by law. Protocol ID:IRB#12-000858 UCLA OHRPP Certified Date Certified: 6/13/2012 Through: 6/12/2017 Committee: Exempt Review Confidentiality will be maintained by means of coding your data as a number, keeping all data in protected files, and grouping individual data files.

PARTICIPATION AND WITHDRAWAL
You can choose whether to be in this study or not. If you volunteer to be in this study, you may withdraw at any time without consequences of any kind.

IDENTIFICATION OF INVESTIGATORS
If you have any questions or concerns about the research, please feel free to contact Professor Alan Castel (and students) email: castel@ucla.edu, Tel: 310 206-9262, Office: 7635 Franz Hall

RIGHTS OF RESEARCH SUBJECTS
You may withdraw your consent at any time and discontinue participation without penalty. You are not waiving any legal rights because of your participation in this research study. If you have questions regarding your rights as a research subject, contact the Office of the Human Research Protection Program at (310) 825-7122 or write to Office of the Human Research Protection Program, UCLA, 11000 Kinross Avenue, Suite 102, Box 951694, Los Angeles, CA 90095-1694. I understand the procedures described above. My questions have been answered to my satisfaction, and I agree to participate in this study. I have been given a copy of this form. Given the above information, do you want to participate in this research study?

Protocol ID:IRB#12-000858 UCLA OHRPP Certified Date Certified: 6/13/2012 Through: 6/12/2017 Committee: Exempt Review</textarea>
            <label>
                <span class="shim">Check this box if you have read, understand, 
                    and agree to the Informed Consent above.</span>
                <input type="checkbox" name="consent" required/>
            </label>
        </section>
        
        
        <section>
            <button class="collectorButton">Submit Basic Info</button>
        </section>
        
    </fieldset>
</form>

<?php
    require $_PATH->get('Footer');
