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

class Download_upd {

	var $version = '1.0';
	
	function Download_upd()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	function tabs()
	{
		$tabs['download'] = array(
			'download_field_ids'		=> array(
								'visible'		=> 'true',
								'collapse'		=> 'false',
								'htmlbuttons'	=> 'true',
								'width'			=> '100%'
								)
				);	
				
		return $tabs;	
	}




	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	function install()
	{
		$this->EE->load->dbforge();

		$data = array(
			'module_name' => 'Download' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'y'
		);

		$this->EE->db->insert('modules', $data);


		$data = array(
			'class'		=> 'Download' ,
			'method'	=> 'force_download'
		);

		$this->EE->db->insert('actions', $data);


		$fields = array(
						'file_id'			=> array('type' 		 => 'int',
													'constraint'	 => '10',
													'unsigned'		 => TRUE,
													'auto_increment' => TRUE),
						'dir_id'			=> array('type'			=> 'int',
													'constraint'	=> '4'),
						'file_name'			=> array('type' => 'varchar', 'constraint' => '250'),
						'file_title'		=> array('type' => 'varchar', 'constraint' => '250', 'null' => TRUE, 'default' => NULL),
						'member_access'		=> array('type' => 'varchar', 'constraint' => '250', 'default' => 'all')
						);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('file_id', TRUE);

		$this->EE->dbforge->create_table('download_files');
		
		unset($fields);
		
		$fields = array(
						'file_id'		  	=> array(	'type' 			 => 'int',
														'constraint'	 => '10',
														'unsigned'		 => TRUE),
						'entry_id'		  	=> array(	'type' 			 => 'int',
														'constraint'	 => '10',
														'unsigned'		 => TRUE)
						);



		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('file_id', TRUE);
		$this->EE->dbforge->add_key('entry_id', TRUE);

		$this->EE->dbforge->create_table('download_posts');		

		$this->EE->load->library('layout');
		$this->EE->layout->add_layout_tabs($this->tabs(), 'download');

		return TRUE;
/*

		$sql[] = "CREATE TABLE IF NOT EXISTS exp_download_files (
				file_id int(10) unsigned NOT NULL auto_increment,
				dir_id int(4) unsigned NOT NULL,
				file_name VARCHAR(250) NOT NULL,
				file_title VARCHAR(250) NULL DEFAULT NULL,
				member_access varchar(255) NULL DEFAULT 'all',
				PRIMARY KEY `file_id` (`file_id`)
				)";

		$sql[] = "CREATE TABLE IF NOT EXISTS exp_download_posts (
				file_id int(10) unsigned NOT NULL,
				entry_id int(10) unsigned NOT NULL,
				PRIMARY KEY `entry_id_cat_id` (`entry_id`, `file_id`)
				)";
*/
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Download'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Download');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'Download');
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('download_files');
		$this->EE->dbforge->drop_table('download_posts');		

		$this->EE->load->library('layout');
		$this->EE->layout->delete_layout_tabs($this->tabs(), 'download');

		return TRUE;
	}



	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */	
	
	function update($current='')
	{
		return TRUE;
	}
	
}
/* END Class */

/* End of file upd.download.php */
/* Location: ./system/expressionengine/third_party/modules/download/upd.download.php */