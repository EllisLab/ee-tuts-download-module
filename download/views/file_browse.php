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

		<div id="file_manager">

			<div id="file_manager_holder">
				<!--<div class="main_tab solo" id="file_manager_list"> -->

				<?php if (count($file_list) == 0):?>

						<p class="notice"><?=lang('no_upload_dirs')?></p>

				<?php else:?>

					<?=form_open($action_url, array('id'=>'files_form'))?>

					<?php 
						$this->table->set_template($cp_table_template);

						foreach ($file_list as $directory_info):
					?>

							<h3><?=$directory_info['name']?></h3>

							<div id="dir_id_<?=$directory_info['id']?>" style="display:<?=$directory_info['display']?>; margin-bottom:10px">
							<?php
								// without the div above, the slide effect breaks the table widths

								$this->table->set_heading(
											lang('file_name'),
											lang('file_size'),
											lang('kind'),
											lang('date'),
											form_checkbox('select_all', 'true', FALSE, 'class="toggle_all"')
														);

								// no results?  Give the "no files" message
								if (count($directory_info['files']) == 0)
								{
									$this->table->add_row(array('data' => lang('no_uploaded_files'), 'colspan' => 5, 'class' => 'no_files_warning'));
								}
								else
								{
									// Create a row for each file
									foreach ($directory_info['files'] as $file)
									{
										$this->table->add_row($file);
									}
								}
								echo $this->table->generate();
								$this->table->clear(); // needed to reset the table
							?>
							</div>

					<?php endforeach;?>
					<input type="submit" class="submit" value="<?=lang('create_download_files')?>" />
					<?=form_close()?>

				<?php endif;?>

</div>
</div>