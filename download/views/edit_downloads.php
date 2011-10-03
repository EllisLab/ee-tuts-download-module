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

		<div class="pageContents">

			<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=update_downloads')?>

							<?php
							
								// without the div above, the slide effect breaks the table widths

								$this->table->set_template($cp_table_template);
								$this->table->set_heading(
														lang('file_name'),
														lang('file_title'),
														lang('access')
														);

								// no results?  Give the "no files" message
								
								if (count($file_info) == 0)
								{
									$this->table->add_row(array('data' => lang('no_uploaded_files'), 'colspan' => 3, 'class' => 'no_files_warning'));
								}
								else
								{
									// Create a row for each file
									foreach ($file_info as $file)
									{	
								
									$this->table->add_row(
									$file['file_name'].
									form_hidden('file_dir['.$file['id'].']', $file['file_dir']).
									form_hidden('file_name['.$file['id'].']', $file['file_name']).
									form_hidden('file_id['.$file['id'].']', $file['file_id']),
									form_input('file_title['.$file['id'].']', set_value('file_title', $file['file_title']), 'id="file_title"'),
									form_multiselect('member_access['.$file['id'].'][]', $member_groups_dropdown, set_value('member_access['.$file['file_id'].']', $file['member_access']))									
																		
									);
									}
								}
								echo $this->table->generate();
								$this->table->clear(); // needed to reset the table
							?>
							</div>

					<input type="submit" class="submit" value="<?=lang('create_download_files')?>" />
					<?=form_close()?>


		</div>
	</div><!-- contents -->
