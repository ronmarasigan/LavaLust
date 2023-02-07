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
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @since Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

 /**
  * FileSessionHandler
  */
class FileSessionHandler extends Session implements SessionHandlerInterface {
    /**
     * Path to save session
     *
     * @var string
     */
    private $save_path;
    /**
     * Path to save session file
     *
     * @var string
     */
    private $file_path;
    /**
     * Session Data
     *
     * @var boolean
     */
    private $data;

    public function __construct()
    {
        if (!empty(config_item('sess_save_path'))) {
            $this->save_path = rtrim(config_item('sess_save_path'), '/\\');
            ini_set('session.save_path', $this->save_path);
        } else {
            $this->save_path = rtrim(ini_get('session.save_path'), '/\\');
        }

    }

    /**
     * Open
     *
     * @param string $save_path
     * @param string $session_name
     * @return bool
     */
    public function open($save_path, $session_name): bool {
        $this->save_path = $save_path;
        $this->file_path = $this->save_path.DIRECTORY_SEPARATOR.$session_name . '_';
        if ( !is_dir($this->save_path) ) {
            mkdir($this->save_path, 0700, TRUE);
        }
        return true;
    }

    /**
     * Close
     *
     * @return void
     */
    public function close(): bool {
        return true;
    }

    /**
     * Read
     *
     * @param string $session_id
     * @return void
     */
    public function read($session_id): string {
        $this->data = false;
        $filename = $this->file_path.$session_id;
        if ( file_exists($filename) ) $this->data = @file_get_contents($filename);
        if ( $this->data === false ) $this->data = '';

        return $this->data;
    }

    /**
     * Write
     *
     * @param string $session_id
     * @param string $session_data
     * @return void
     */
    public function write($session_id, $session_data): bool {
        $filename = $this->file_path.$session_id;

        if ( $session_data !== $this->data ) {
            return @file_put_contents($filename, $session_data, LOCK_EX) === false ? false : true;
        }
        else return @touch($filename);
    }

    /**
     * Destroy
     * 
     * @param  string $session_id
     * @return bool
     */
    public function destroy($session_id): bool {
        $filename = $this->file_path . $session_id;
        if ( file_exists($filename) ) @unlink($filename);

        return true;
    }

    /**
     * Session Lifetime
     * 
     * @param  int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime): int {
        foreach ( glob("$this->file_path*") as $filename ) {
            if ( filemtime($filename) + $maxlifetime < time() && file_exists($filename) ) {
                @unlink($filename);
            }
        }

        return true;
    }
}
?>