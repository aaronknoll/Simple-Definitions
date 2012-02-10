<?php
$head = array('bodyclass' => 'definitions primary', 
              'title' => 'Definitions');
head($head);
?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
jQuery(window).load(function () {
    jQuery('#defList').change(function() {
        jQuery.ajax({
            url: <?php echo js_escape(uri(array('action' => 'element-terms'))); ?>,
            data: {defList: jQuery('#defList').val()},
            success: function(data) {
                jQuery('#terms').val(data);
            }
        });
    });
    jQuery('#display-texts').click(function() {
        jQuery.ajax({
            url: <?php echo js_escape(uri(array('action' => 'element-texts'))); ?>,
            data: {element_id: jQuery('#element-id').val()},
            success: function(data) {
                jQuery('#texts').html(data);
            }
        });
    });
});
//]]>
</script>
<h1><?php echo $head['title']; ?></h1>
<div id="primary">
    <?php echo flash(); ?>
    <form method="post" action="<?php echo uri(array('module' => 'simple-definitions', 
                                                     'controller' => 'index', 
                                                     'action' => 'edit-element-terms')); ?>">
        <div class="field">
            <label for="defList">Word to Define</label>
            <div class="inputs">
               <?php echo $this->formSelect('defList', 
                       null, 
                       null, 
                        $this->DefSelectOptions) ?>

                <p class="explanation">Select an element to manage its custom 
                definitionsulary. Elements with a custom definitionsulary are marked with an 
                asterisk (*).</p>
            </div>
        </div>
        <div class="field">
            <label for="terms">definitionsulary Terms</label>
            <div class="inputs">
                <?php echo $this->formTextarea('terms', 
                                               null, 
                                               array('id' => 'terms', 
                                                     'rows' => '10', 
                                                     'cols' => '50')) ?>
                <p class="explanation">Enter the custom definitionsulary terms for 
                this element, one per line. To delete the definitionsulary, simply 
                remove the terms and sumbit this form.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('edit_definitions', 
                                     'Add/Edit definitionsulary', 
                                     array('class' => 'submit submit-large')); ?>
    </form>
    <p><a id="display-texts" href="#display-texts"><strong>Click here</strong></a> 
    to display a list of texts for the selected element that currently exist in 
    your archive. You may use this list as a reference to build a definitionsulary, 
    but be aware of some caveats:</p>
    <ul style="list-style: disc;margin-left: 1.5em;">
        <li>definitionsulary terms must not contain newlines (line breaks).</li>
        <li>definitionsulary terms are typically short and concise. If your existing 
        texts are otherwise, avoid using a controlled definitionsulary for this 
        element.</li>
        <li>definitionsulary terms must be identical to their corresponding texts.</li>
        <li>Existing texts that are not in the definitionsulary will be preserved — 
        however, they cannot be selected in the item edit page, and will be 
        deleted once you save the item.</li>
    </ul>
    <div id="texts"></div>
</div>
<?php foot(); ?>