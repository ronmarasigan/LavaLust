<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 * 
 * Copyright (c) 2020 Ronald M. Marasigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
 */

class Sessionhandlerdb extends Session {

    //Store the starting session ID so we can check against current id at close
    public $session_id      = NULL;
    //Table to look for session data in
    public $session_table   = NULL;
    // How long are sessions good?
    public $expiration      = NULL;

    /**
     * Record the current sesion_id for later
     * @return boolean
     */
    public function open() {
        //Store the current ID so if it is changed we will know!
        $this->session_id = session_id();
        return TRUE;
    }


    /**
     * Superfluous close function
     * @return boolean
     */
    public function close() {
        return TRUE;
    }


    /**
     * Attempt to read a session from the database.
     * @param   string  $id
     */
    public function read($id = NULL) {

        //Select the session
        $result = $this->db->select('data')->where('session_id', $id)->get($this->session_table);

        //Check to see if there is a result
        if($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
            return $row['data'];
        }

        return '';
    }


    /**
     * Attempt to create or update a session in the database.
     * The $data is already serialized by PHP.
     *
     * @param   string  $id
     * @param   string  $data
     */
    public function write($id = NULL, $data = '') {

        /*
         * Case 1: The session we are now being told to write does not match
         * the session we were given at the start. This means that the ID was
         * regenerated sometime durring the script and we need to update that
         * old session id to this new value. The other choice is to delete
         * the old session first - but that wastes resources.
         */

        //If the session was not empty at start && regenerated sometime durring the page
        if($this->session_id && $this->session_id != $id) {

            //Update the data and new session_id
            $data = array('data' => $data, 'session_id' => $id);

            //Then we need to update the row with the new session id (and data)
            $this->db->update($this->session_table, $data, array('session_id' => $this->session_id));

            return;
        }

        /*
         * Case 2: We check to see if the session already exists. If it does
         * then we need to update it. If not, then we create a new entry.
         */
        if($this->db->where('session_id', $id)->count($this->session_table)) {
            $this->db->update($this->session_table, array('data' => $data), array('session_id' => $id));

        } else {
            $this->db->insert($this->session_table, array('data' => $data));
        }

    }


    /**
     * Delete a session from the database
     * @param   string  $id
     * @return  boolean
     */
    public function destroy($id) {
        $this->db->delete($this->session_table, array('session_id' => $id));
        return TRUE;
    }


    /**
     * Garbage collector method to remove old sessions
     */
    public function gc() {
        //The max age of a session
        $time = date('Y-m-d H:i:s', time() - $this->expiration);
        //Remove all old sessions
        $this->db->delete($this->session_table, array('last_activity < ' => $time));
        return TRUE;
    }
}