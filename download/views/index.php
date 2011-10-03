<?php
/*
Copyright (C) 2010 - 2011 EllisLab, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of EllisLab, Inc. shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from EllisLab, Inc.
*/
?>

<?php if (count($files) > 0): ?>
<?=form_open($action_url, '', $form_hidden)?>


<?php
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		lang('file_title'),
		lang('file_name'),
		lang('access'),
		form_checkbox('select_all', 'true', FALSE, 'class="toggle_all" id="select_all"'));

	foreach($files as $file)
	{
		$this->table->add_row(
				'<a href="'.$file['edit_link'].'">'.$file['file_name'].'</a>',
				$file['file_title'],
				$file['member_access'],
				form_checkbox($file['toggle'])
			);
	}

echo $this->table->generate();

?>

<div class="tableFooter">
	<div class="tableSubmit">
		<?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')).NBS.NBS.form_dropdown('action', $options)?>
	</div>

	<span class="js_hide"><?=$pagination?></span>	
	<span class="pagination" id="filter_pagination"></span>
</div>	

<?=form_close()?>

<?php else: ?>
<?=lang('no_matching_files')?>
<?php endif; ?>