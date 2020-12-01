<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Sample_model extends Model {

	public function all_users() {
		return $this->db->table('user')->get();
	}

	public function update() {
		return $this->db->table('user')->update(array('username'=>'acid'))->where('id', 2)->exec();
	}
}
?>
