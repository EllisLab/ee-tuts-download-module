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

class Download {

	var $return_data	= '';
	var $p_limit = '';

	
	function Download()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

	}
	
	function entries()
	{
		if (($entry_id = $this->EE->TMPL->fetch_param('entry_id')) === FALSE) return;
		$limit	= ( ! isset($params['limit']) OR ! is_numeric($params['limit'])) ? 100 : $params['limit'];

		$this->EE->db->select('*');
		$this->EE->db->where('entry_id', $entry_id); 
		$this->EE->db->from('download_files');
		$this->EE->db->join('download_posts', 'download_files.file_id = download_posts.file_id', 'right');

		$query = $this->EE->db->get();


		if ($query->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();
		}

		//  Instantiate Typography class
		$this->EE->load->library('typography');
		$this->EE->typography->initialize(array(
				'parse_images'		=> TRUE,
				'allow_headings'	=> FALSE)
				);
		
		$edit_date			= array();

		// We do this here to avoid processing cycles in the foreach loop

		$date_vars = array('edit_date');
		
		$base_url = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->fetch_action_id('Download', 'force_download');

		foreach ($query->result_array() as $id => $row)
		{
			$variables[] = array(
				'file_title' => $row['file_title'],
				'file_link' => '{filedir_'.$row['dir_id'].'}',
				'file_download' => $base_url.AMP.'id='.$row['file_id']
				);					
			
		}

		$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables); 
		
		return $output;
	}
	
	function files()
	{
		$limit	= ( ! isset($params['limit']) OR ! is_numeric($params['limit'])) ? 100 : $params['limit'];

		$this->EE->db->select('*');
		$this->EE->db->from('download_files');

		$query = $this->EE->db->get();


		if ($query->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();
		}

		//  Instantiate Typography class
		$this->EE->load->library('typography');
		$this->EE->typography->initialize(array(
				'parse_images'		=> TRUE,
				'allow_headings'	=> FALSE)
				);
		
		$edit_date			= array();

		// We do this here to avoid processing cycles in the foreach loop

		$date_vars = array('edit_date');
		
		$base_url = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->functions->fetch_action_id('Download', 'force_download');

		foreach ($query->result_array() as $id => $row)
		{
			$variables[] = array(
				'file_name' => $row['file_name'],
				'file_link' => '{filedir_'.$row['dir_id'].'}',
				'file_download' => $base_url.AMP.'id='.$row['file_id']
				);					
			
		}
		
		$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables); 
		
		return $output;

	}
	
	function force_download()
	{
		$file_id = $this->EE->input->get('id');
		$this->EE->lang->loadfile('download');

		
		if ($file_id === FALSE)
		{
			return $this->EE->output->show_user_error('general', $this->EE->lang->line('invalid_download'));
		}
		
		$group_id = $this->EE->session->userdata['group_id'];
		
		$this->EE->load->helper('download');
		
		$this->EE->db->select('file_name, file_title, member_access, server_path, url');
		$this->EE->db->from('download_files');
		$this->EE->db->join('upload_prefs', 'upload_prefs.id = download_files.dir_id');
		$this->EE->db->where('file_id', $file_id); 

		$query = $this->EE->db->get();
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$allowed = explode('|', $row->member_access);
			
			if ( ! in_array('all', $allowed) && ! in_array($group_id, $allowed))
			{
				return $this->EE->output->show_user_error('general', $this->EE->lang->line('no_permission'));
			}
			
			$file_name = $row->file_name;
			$file_path = $row->server_path.$file_name;
	
			$data = file_get_contents($file_path); // Read the file's contents

			force_download($file_name, $data); 
		} 

	}
}

/* End of file mod.download.php */
/* Location: ./system/expressionengine/third_party/download/mod.download.php */