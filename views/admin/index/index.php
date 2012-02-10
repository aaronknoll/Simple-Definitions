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

                <p class="explanation">Select a controlled vocabulary field
                	to define a term. Terms with existing definitions
                	are **starred**. </p>
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
                <p class="explanation">Enter new text, edit existing text
                	or delete all content (and save a blank box) to 
                	delete an item.</p>
            </div>
        </div>
        <?php echo $this->formSubmit('edit_definitions', 
                                     'Add/Edit a Definition', 
                                     array('class' => 'submit submit-large')); ?>
    </form>
</div>
<?php foot(); ?>