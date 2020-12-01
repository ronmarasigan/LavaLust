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

class DBSessionHandler extends Session {

    /**
    * Open the session
    * @return bool
    */
    public function _open() {
        if($this->db)
            return true;
        return false;
    }

    /**
    * Close the session
    * @return bool
    */
    public function _close() {
        $this->db = null;
    }


    /**
    * Read the session
    * @param int session id
    * @return string string of the sessoin
    */
    public function _read($id) {
        $res = $this->db->table('session')->select('data')->where('id', $id)->get();
        if($res)
            return $res['data'];
        return '';
    }

    /**
    * Write the session
    * @param int session id
    * @param string data of the session
    */
    public function _write($id, $data) {
        $res = $this->db->raw('REPLACE INTO sessions (id, access, data) VALUES (?, ?, ?)', array($id, time(), $data));
        if($res)
            return true;
        return false;
    }

    /**
    * Destroy the session
    * @param int session id
    * @return bool
    */
    public function _destroy($id) {
        $res = $this->db->table('sessions')->delete()->where('id', $id)->exec();
        if($res)
            return true;
        return false;
    }

    /**
    * Garbage Collector
    * @param int life time (sec.)
    * @return bool
    * @see session.gc_divisor      100
    * @see session.gc_maxlifetime 1440
    * @see session.gc_probability    1
    * @usage execution rate 1/100
    *        (session.gc_probability/session.gc_divisor)
    */
    public function _gc($max) {
        $old = time() - $max;
        $res = $this->db->table('sessions')->delete()->where('access', '<', $old)->exec();
        if($res)
            return true;
        return false;
    }
}