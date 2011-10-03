<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class Download_tab {

	
	function Download_tab()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	function publish_tabs($channel_id, $entry_id = '')
	{

		$settings = array();
		$selected = array();
		$existing_files = array();

		$query = $this->EE->db->get('download_files');

		foreach ($query->result() as $row)
		{
			$existing_files[$row->file_id] = $row->file_name;
		}

		if ($entry_id != '')
		{
			$query = $this->EE->db->get_where('download_posts', array('entry_id' => $entry_id));

			foreach ($query->result() as $row)
			{
				$selected[] = $row->file_id;
			}
		}

		// Load the module lang file for the field label
		$this->EE->lang->loadfile('download');
		$id_instructions = lang('id_field_instructions');

		$settings[] = array(
				'field_id'				=> 'download_field_ids',
				'field_label'			=> $this->EE->lang->line('download_files'),
				'field_required' 		=> 'n',
				'field_data'			=> $selected,
				'field_list_items'		=> $existing_files,
				'field_fmt'				=> '',
				'field_instructions' 	=> $id_instructions,
				'field_show_fmt'		=> 'n',
				'field_fmt_options'		=> array(),
				'field_pre_populate'	=> 'n',
				'field_text_direction'	=> 'ltr',
				'field_type' 			=> 'multi_select'
			);

		return $settings;
	}

	function validate_publish($params)
	{
		return FALSE;
	}
	
	function publish_data_db($params)
	{
		// Remove existing

		$this->EE->db->where('entry_id', $params['entry_id']);
		$this->EE->db->delete('download_posts'); 
		
		if (isset($params['mod_data']['download_field_ids']) && is_array($params['mod_data']['download_field_ids']) && count($params['mod_data']['download_field_ids']) > 0)
		{		
			foreach ($params['mod_data']['download_field_ids'] as $val)
			{
				$data = array(
               		'entry_id' => $params['entry_id'],
               		'file_id' => $val
            		);
			
				$this->EE->db->insert('download_posts', $data); 
			}
		}
	}

	function publish_data_delete_db($params)
	{
		// Remove existing
		$this->EE->db->where_in('entry_id', $params['entry_ids']);
		$this->EE->db->delete('download_posts'); 
	}

}
/* END Class */

/* End of file tab.download.php */
/* Location: ./system/expressionengine/third_party/modules/download/tab.download.php */