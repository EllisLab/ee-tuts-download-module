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

class Download_mcp {

	var $pipe_length = 1;
	var $perpage = 5;
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Download_mcp()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

		$this->EE->cp->set_right_nav(array(
				'add_download'		=> BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=file_browse'

			));

	}

	// --------------------------------------------------------------------

	/**
	 * Main Page
	 *
	 * @access	public
	 */
	function index()
	{
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('download_module_name'));

		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=edit_downloads';
		$vars['form_hidden'] = NULL;
		$vars['files'] = array();
		
		$vars['options'] = array(
				'edit'  => lang('edit_selected'),
				'delete'    => lang('delete_selected')
				);

		// Add javascript

		$this->EE->cp->add_js_script(array('plugin' => 'dataTables'));
			
		$this->EE->javascript->output($this->ajax_filters('edit_items_ajax_filter', 4));


		$this->EE->javascript->output(array(
				'$(".toggle_all").toggle(
					function(){
						$("input.toggle").each(function() {
							this.checked = true;
						});
					}, function (){
						var checked_status = this.checked;
						$("input.toggle").each(function() {
							this.checked = false;
						});
					}
				);'
			)
		);
			
		$this->EE->javascript->compile();

		// get all member groups for the dropdown list
		$member_groups = $this->EE->member_model->get_member_groups();
		
		foreach($member_groups->result() as $group)
		{
			$member_group[$group->group_id] = $group->group_title;
		}

		//  Check for pagination
		$total = $this->EE->db->count_all('download_files');
		
			
		if ( ! $rownum = $this->EE->input->get_post('rownum'))
		{		
			$rownum = 0;
		}

		$this->EE->db->order_by("file_id", "desc"); 
		$query = $this->EE->db->get('download_files', $this->perpage, $rownum);	

		foreach($query->result_array() as $row)
		{
			$vars['files'][$row['file_id']]['entry_title'] = $row['file_title'];
			$vars['files'][$row['file_id']]['edit_link'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=edit_downloads'.AMP.'file_id='.$row['file_id'];

			$vars['files'][$row['file_id']]['dir_id'] = $row['dir_id'];
			$vars['files'][$row['file_id']]['file_name'] = $row['file_name'];
			$vars['files'][$row['file_id']]['file_title'] = $row['file_title'];
				
			$access = '';
			$member_access = explode('|', $row['member_access']);
				
			foreach ($member_access as $group_id)
			{
				$access .= (isset($member_group[$group_id])) ? $member_group[$group_id] : $group_id;
				$access .= ', ';
			}

			$vars['files'][$row['file_id']]['member_access'] = rtrim($access, ', ');

			// Toggle checkbox
			$vars['files'][$row['file_id']]['toggle'] = array(
															'name'		=> 'toggle[]',
															'id'		=> 'edit_box_'.$row['file_id'],
															'value'		=> $row['file_id'],
															'class'		=>'toggle'
															);
		}
			
		// Pass the relevant data to the paginate class so it can display the "next page" links
		$this->EE->load->library('pagination');
		$p_config = $this->pagination_config('index', $total);

		$this->EE->pagination->initialize($p_config);

		$vars['pagination'] = $this->EE->pagination->create_links();

		return $this->EE->load->view('index', $vars, TRUE);
	}
	

	function pagination_config($method, $total_rows)
	{
		// Pass the relevant data to the paginate class
		$config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method='.$method;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $this->perpage;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'rownum';
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		return $config;
	}
	
	function file_browse()
	{
		$vars = array();

		$this->EE->load->helper(array('form', 'string', 'url', 'file'));
		$this->EE->load->library('table');
		$this->EE->load->library('encrypt');
		$this->EE->load->model('tools_model');

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('add_files'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download', $this->EE->lang->line('download_module_name'));
		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=add_downloads';		
		
		
		$this->EE->cp->add_js_script(array(
		            'plugin'    => array('tablesorter')
		    )
		);
		
		
		$this->EE->jquery->tablesorter('.mainTable', '{
			headers: {4: {sorter: false}, 5: {sorter: false}, 6: {sorter: false}},
			widgets: ["zebra"],
			sortList: [[0,0]] 
		}');
		
		$this->EE->cp->add_js_script(array(
		            'plugin'    => array('fancybox', 'tablesorter', 'ee_upload')
		    )
		);
		
		$this->EE->cp->add_to_head('<link type="text/css" rel="stylesheet" href="'.BASE.AMP.'C=css'.AMP.'M=fancybox" />');


		$this->EE->javascript->output('		
			function setup_events(el) {

				$("td.fancybox a").unbind("click").
					fancybox();

					// Set the row as "selected"
					$(".toggle").unbind("click").click(function(e){
						$(this).parent().parent().toggleClass("selected");
					});

					$(".mainTable td").unbind("click").click(function(e){
						// if the control or command key was pressed, select the file
						if (e.ctrlKey || e.metaKey)
						{
							$(this).parent().toggleClass("selected"); // Set row as selected

							if ( ! $(this).parent().find(".file_select :checkbox").attr("checked"))
							{
								$(this).parent().find(".file_select :checkbox").attr("checked", "true");
							}
							else
							{
								$(this).parent().find(".file_select :checkbox").attr("checked", "");
							}
						}
					});
			}
			setup_events();
		');



		$vars = array_merge($vars, $this->_map());

		$this->EE->javascript->compile();

		return $this->EE->load->view('file_browse', $vars, TRUE);
	}

	function add_downloads()
	{
		if (! $this->EE->cp->allowed_group('can_access_content')  OR ! $this->EE->cp->allowed_group('can_access_files'))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$this->EE->load->model('member_model');
		$this->EE->load->helper(array('form', 'date'));
		$this->EE->load->library('table');
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('add_files'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download', $this->EE->lang->line('download_module_name'));
		

		$files = $this->EE->input->post('file');

		if ( ! is_array($files) OR count($files) == 0)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('invalid_entries'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP
				.'M=show_module_cp'.AMP.'module=download');	
		}

		if (count($files == 1))
		{
			$vars['del_notice'] =  'confirm_del_file';
		}
		else
		{
			$vars['del_notice'] = 'confirm_del_files';
		}
			
		$i= 0;
		foreach ($files as $file)
		{
			$file_name = ltrim(strstr($file, '_'), '_');
			$cut_size = strlen($file_name) + 1;
			$file_dir = substr($file, 0, -$cut_size);

			$vars['file_info'][$i]['file_dir'] = $file_dir;
			$vars['file_info'][$i]['file_name'] = $file_name;
			$vars['file_info'][$i]['file_title'] = '';
			$vars['file_info'][$i]['file_id'] = '';
			$vars['file_info'][$i]['id'] = $i;
			$vars['file_info'][$i]['member_access'] = '';

			$i++;
		}
		
		// get all member groups for the dropdown list
		$member_groups = $this->EE->member_model->get_member_groups();
		
		// first dropdown item is "all"
		$vars['member_groups_dropdown'] = array(0 => $this->EE->lang->line('all'));
		
		foreach($member_groups->result() as $group)
		{
			$vars['member_groups_dropdown'][$group->group_id] = $group->group_title;
		}

		return $this->EE->load->view('edit_downloads', $vars, TRUE);		
	}
	
	function edit_downloads()
	{
		if (! $this->EE->cp->allowed_group('can_access_content')  OR ! $this->EE->cp->allowed_group('can_access_files'))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$this->EE->load->model('member_model');
		$this->EE->load->helper(array('form', 'date'));
		$this->EE->load->library('table');
		
		if ($this->EE->input->get_post('toggle'))
		{
			$files = $this->EE->input->get_post('toggle');
		}
		else
		{
			$files = $this->EE->input->get_post('file_id');
		}

		if ($files === FALSE)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('invalid_entries'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP
				.'M=show_module_cp'.AMP.'module=download');	
		}

		if ( ! is_array($files))
		{
			$files = array($files);
		}
					
		$this->EE->db->where_in('file_id', $files);
		$query = $this->EE->db->get('download_files');
			
		if ($query->num_rows() == 0)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('invalid_entries'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download');				
		}

		// No files in post- check get
		if ($this->EE->input->post('action') == 'delete')
		{
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('delete_files'));
			$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download', $this->EE->lang->line('download_module_name'));


			foreach ($_POST['toggle'] as $key => $val)
			{
				$vars['damned'][] = $val;
			}
			
			$vars['form_action'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=delete_downloads';

			return $this->EE->load->view('delete_confirm', $vars, TRUE);
			
		}
		else
		{
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('edit_files'));
			$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download', $this->EE->lang->line('download_module_name'));

   			foreach ($query->result() as $row)
			{
				$vars['file_info'][$row->file_id]['file_dir'] = $row->dir_id;
				$vars['file_info'][$row->file_id]['file_name'] = $row->file_name;
				$vars['file_info'][$row->file_id]['file_title'] = $row->file_title;
				$vars['file_info'][$row->file_id]['file_id'] = $row->file_id;
				$vars['file_info'][$row->file_id]['id'] = $row->file_id;
				$vars['file_info'][$row->file_id]['member_access'] = $row->member_access;
			}						

		}
		
		// get all member groups for the dropdown list
		$member_groups = $this->EE->member_model->get_member_groups();
		
		// first dropdown item is "all"
		$vars['member_groups_dropdown'] = array(0 => $this->EE->lang->line('all'));
		
		foreach($member_groups->result() as $group)
		{
			$vars['member_groups_dropdown'][$group->group_id] = $group->group_title;
		}
		

		return $this->EE->load->view('edit_downloads', $vars, TRUE);

	}

	function update_downloads()
	{

		if (! $this->EE->cp->allowed_group('can_access_content')  OR ! $this->EE->cp->allowed_group('can_access_files'))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$new = TRUE;
		$this->EE->load->library('encrypt');

		$file_names = $_POST['file_name'];

		if ($file_names == '')
		{
			// nothing for you here
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('choose_file'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=file_browse');
		}

	
		foreach($file_names as $id => $file)
		{
			if (isset($_POST['file_id'][$id]) && $_POST['file_id'][$id] != '')
			{
				$data['file_id'] = $_POST['file_id'][$id]; 
				$new = FALSE;
			}
			
			if (isset($_POST['member_access'][$id]) && is_array($_POST['member_access'][$id]))
			{
				$member_access = implode('|', $_POST['member_access'][$id]);
			}
			else
			{
				$member_access = '1';
			}
			
			$title = (isset($_POST['file_title'][$id]) && $_POST['file_title'][$id] != '') ? $_POST['file_title'][$id] : $_POST['file_name'][$id];
			
			$data = array(
							//'file_id'			=> '', //$_POST['entry_id'][$id],
							'dir_id'			=> $_POST['file_dir'][$id],
							'file_name'			=> $_POST['file_name'][$id],
							'file_title'		=> $title,
							'member_access'		=> $member_access
							);



					
			/** ---------------------------------
			/**  Do our insert or update
			/** ---------------------------------*/
							
			if ($new)
			{
				$this->EE->db->query($this->EE->db->insert_string('exp_download_files', $data));
				$cp_message = $this->EE->lang->line('item_added');
			}
			else
			{
				$this->EE->db->query($this->EE->db->update_string('exp_download_files', $data, "file_id = '$id'"));
				$cp_message = $this->EE->lang->line('updated');
			}
		}
		
		$this->EE->session->set_flashdata('message_success', $cp_message);

		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=index');
	}
	
	function delete_downloads()
	{
		if ( ! $this->EE->input->post('delete'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download');
		}

		foreach ($_POST['delete'] as $key => $val)
		{
			$this->EE->db->or_where('file_id', $val);
		}

		$this->EE->db->delete('download_posts');
		
		foreach ($_POST['delete'] as $key => $val)
		{
			$this->EE->db->or_where('file_id', $val);
		}

		$this->EE->db->delete('download_files');		
	
		$message = (count($_POST['delete']) == 1) ? $this->EE->lang->line('download_deleted') : $this->EE->lang->line('downloads_deleted');

		$this->EE->session->set_flashdata('message_success', $message);
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download');
		
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get a directory map
	 *
	 * Creates an array of directories and their content
	 * 
	 * @access	public
	 * @param	int		optional directory id (defaults to all)
	 * @return	mixed
	 */
	function _map($dir_id = FALSE)
	{
		$upload_directories = $this->EE->tools_model->get_upload_preferences($this->EE->session->userdata('member_group'));
		$existing_files = FALSE;

		// if a user has no directories available to them, then they have no right to be here
		if ($this->EE->session->userdata['group_id'] != 1 && $upload_directories->num_rows() == 0)
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$vars['file_list'] = array(); // will hold the list of directories and files
		$vars['upload_directories'] = array();


		if ($dir_id)
		{
			$this->EE->db->where('dir_id', $dir_id); 
		}

		$query = $this->EE->db->get('download_files');
		
		if ($query->num_rows() > 0)
		{		
			foreach ($query->result() as $row)
			{
				$existing_files[$row->dir_id.'_'.$row->file_name] = '';
			}
		}

		$this->EE->load->helper('directory');

		foreach ($upload_directories->result() as $dir)
		{
			if ($dir_id && $dir->id != $dir_id)
			{
				continue;
			}
			
			// we need to know the dirs for the purposes of uploads, so grab them here
			$vars['upload_directories'][$dir->id] = $dir->name;

			$vars['file_list'][$dir->id]['id'] = $dir->id;
			$vars['file_list'][$dir->id]['name'] = $dir->name;
			$vars['file_list'][$dir->id]['url'] = $dir->url;
			$vars['file_list'][$dir->id]['display'] = ($this->EE->input->cookie('hide_upload_dir_id_'.$dir->id) == 'true') ? 'none' : 'block';
			$files = $this->EE->tools_model->get_files($dir->server_path, $dir->allowed_types);

			$file_count = 0;
			$vars['file_list'][$dir->id]['files'] = array(); // initialize so empty dirs don't throw errors

			// construct table row arrays
			foreach($files as $file)
			{
				if ($file['name'] == '_thumbs' OR $file['name'] == 'folder')
				{
					continue;
				}

				if (isset($existing_files[$dir->id.'_'.$file['name']]))
				{
					continue;
				}

				if (strncmp($file['mime'], 'image', 5) == 0)
				{
					$vars['file_list'][$dir->id]['files'][$file_count] = array(
						array(
							'class'=>'fancybox', 
							'data' => '<a class="fancybox" id="img_'.str_replace(".", '', $file['name']).'" href="'.$dir->url.$file['name'].'" title="'.$file['name'].NBS.'" rel="'.$file['encrypted_path'].'">'.$file['name'].'</a>',
						),
						array(
							'class'=>'fancybox align_right', 
							'data' => number_format($file['size']/1000, 1).NBS.lang('file_size_unit'),
						),
						array(
							'class'=>'fancybox', 
							'data' => $file['mime'],
						),
						array(
							'class'=>'fancybox', 
							'data' => date('M d Y - H:ia', $file['date'])
						),
						array(
							'class' => 'file_select', 
							'data' => form_checkbox('file[]', $dir->id.'_'.$file['name'], FALSE, 'class="toggle"')
						)
					);
				}
				else
				{
					$vars['file_list'][$dir->id]['files'][$file_count] = array(
						$file['name'],
						array(
							'class'=>'align_right', 
							'data' => number_format($file['size']/1000, 1).NBS.lang('file_size_unit'),
						),
						$file['mime'],
						date('M d Y - H:ia', $file['date']),
						array(
							'class' => 'file_select', 
							'data' => form_checkbox('file[]', $dir->id.'_'.$file['name'], FALSE, 'class="toggle"')
						)
					);
				}

				$file_count++;
			}

		}

		return $vars;
	}

	

	// --------------------------------------------------------------------

	function edit_items_ajax_filter()
	{
		$this->EE->output->enable_profiler(FALSE);
		$this->EE->load->helper('text');
		
		// get all member groups for the dropdown list
		$member_groups = $this->EE->member_model->get_member_groups();
		
		foreach($member_groups->result() as $group)
		{
			$member_group[$group->group_id] = $group->group_title;
		}

				
		$col_map = array('file_name', 'file_title', 'member_access');

		$id = ($this->EE->input->get_post('id')) ? $this->EE->input->get_post('id') : '';		


		// Note- we pipeline the js, so pull more data than are displayed on the page		
		$perpage = $this->EE->input->get_post('iDisplayLength');
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0; // Display start point
		$sEcho = $this->EE->input->get_post('sEcho');

		/* Ordering */
		$order = array();
		
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}

		$total = $this->EE->db->count_all('download_files');

		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		$j_response['iTotalDisplayRecords'] = $total;
					
		$tdata = array();
		$i = 0;
		

		if (count($order) > 0)
		{
			foreach ($order as $key => $val)
			{
				$this->EE->db->order_by($key, $val);
			}
		}
		else
		{
			$this->EE->db->order_by('file_id');
		}

		$query = $this->EE->db->get('download_files', $perpage, $offset);
		
		// Note- empty string added because otherwise it will throw a js error
		foreach ($query->result_array() as $file)
		{
				
			$access = '';
			$member_access = explode('|', $file['member_access']);
				
			foreach ($member_access as $group_id)
			{
				$access .= (isset($member_group[$group_id])) ? $member_group[$group_id] : $group_id;
				$access .= ', ';
			}

			$display_access = rtrim($access, ', ');			
			
			$m[] = '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=download'.AMP.'method=edit_downloads'.AMP.'file_id='.$file['file_id'].'">'.$file['file_title'].'</a>';
			$m[] = $file['file_name'];
			$m[] = $display_access;
			$m[] = '<input class="toggle" id="edit_box_'.$file['file_id'].'" type="checkbox" name="toggle[]" value="'.$file['file_id'].'" />';		

			$tdata[$i] = $m;
			$i++;
			unset($m);
		}		

		$j_response['aaData'] = $tdata;	
		$sOutput = $this->EE->javascript->generate_json($j_response, TRUE);
	
		die($sOutput);
	}


	function ajax_filters($ajax_method = '', $cols = '')
	{
		if ($ajax_method == '')
		{
			return;
		}
		
		$col_defs = '';
		if ($cols != '')
		{
			$col_defs .= '"aoColumns": [ ';
			$i = 1;
			
			while ($i < $cols)
			{
				$col_defs .= 'null, ';
				$i++;
			}
			
			$col_defs .= '{ "bSortable" : false } ],';
		}
		
		$js = '
var oCache = {
	iCacheLower: -1
};

function fnSetKey( aoData, sKey, mValue )
{
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
	{
		if ( aoData[i].name == sKey )
		{
			aoData[i].value = mValue;
		}
	}
}

function fnGetKey( aoData, sKey )
{
	for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
	{
		if ( aoData[i].name == sKey )
		{
			return aoData[i].value;
		}
	}
	return null;
}

function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
	var iPipe = '.$this->pipe_length.';  /* Ajust the pipe size */
	
	var bNeedServer = false;
	var sEcho = fnGetKey(aoData, "sEcho");
	var iRequestStart = fnGetKey(aoData, "iDisplayStart");
	var iRequestLength = fnGetKey(aoData, "iDisplayLength");
	var iRequestEnd = iRequestStart + iRequestLength;
	oCache.iDisplayStart = iRequestStart;
	
	/* outside pipeline? */
	if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
	{
		bNeedServer = true;
	}
	
	/* sorting etc changed? */
	if ( oCache.lastRequest && !bNeedServer )
	{
		for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
		{
			if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
			{
				if ( aoData[i].value != oCache.lastRequest[i].value )
				{
					bNeedServer = true;
					break;
				}
			}
		}
	}
	
	/* Store the request for checking next time around */
	oCache.lastRequest = aoData.slice();
	
	if ( bNeedServer )
	{
		if ( iRequestStart < oCache.iCacheLower )
		{
			iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
			if ( iRequestStart < 0 )
			{
				iRequestStart = 0;
			}
		}
		
		oCache.iCacheLower = iRequestStart;
		oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
		oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
		fnSetKey( aoData, "iDisplayStart", iRequestStart );
		fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
		
		$.getJSON( sSource, aoData, function (json) { 
			/* Callback processing */
			oCache.lastJson = jQuery.extend(true, {}, json);
			
			if ( oCache.iCacheLower != oCache.iDisplayStart )
			{
				json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
			}
			json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
			
			fnCallback(json)
		} );
	}
	else
	{
		json = jQuery.extend(true, {}, oCache.lastJson);
		json.sEcho = sEcho; /* Update the echo for each response */
		json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
		json.aaData.splice( iRequestLength, json.aaData.length );
		fnCallback(json);
		return;
	}
}

	oTable = $(".mainTable").dataTable( {	
			"sPaginationType": "full_numbers",
			"bLengthChange": false,
			"bFilter": false,
			"sWrapper": false,
			"sInfo": false,
			"bAutoWidth": false,
			"iDisplayLength": '.$this->perpage.', 
			
			'.$col_defs.'
					
		"oLanguage": {
			"sZeroRecords": "'.$this->EE->lang->line('invalid_entries').'",
			
			"oPaginate": {
				"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
				"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
				"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
				"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
			}
		},
		
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=download&method='.$ajax_method.'",
			"fnServerData": fnDataTablesPipeline

	} );';

		return $js;
		
	}

}
// END CLASS

/* End of file mcp.download.php */
/* Location: ./system/expressionengine/third_party/modules/download/mcp.download.php */