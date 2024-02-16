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
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
* ------------------------------------------------------
*  Class Email
* ------------------------------------------------------
 */
class Email {
	/**
	 * Sender Email
	 *
	 * @var string
	 */
	private $sender;

	/**
	 * Sender Name if needed
	 *
	 * @var string
	 */
	public $sender_name = '';

	/**
	 * Receipient Emails
	 *
	 * @var array
	 */
	private $recipients = array();

	/**
	 * Specified Email to receive replies
	 *
	 * @var string
	 */
	private $reply_to;

	/**
	 * Email Subject
	 *
	 * @var string
	 */
	private $subject;

	/**
	 * Email Attachement
	 *
	 * @var array
	 */
	private $attach_files = array();

	/**
	 * Email Content
	 *
	 * @var string
	 */
	private $emailContent;

	/**
	 * Email type
	 * Default: plain
	 * Other value: html
	 *
	 * @var string
	 */
	private $emailType;

	/**
	 * Class constructor
	 */
	public function __construct() {

	}
	/**
	 * Check if email is in correct format
	 * 
	 * @param  string  $email Email to check
	 * @return boolean
	 */
	public function valid_email($email)
	{
		$email = filter_var($email,FILTER_SANITIZE_EMAIL);
		if(filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			return true;
		} else {
			throw new Exception('Invalid email address');
		}
	}

	public function filter_string($string)
	{
		return filter_var($string, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH);
	}

	/**
	 * Check Sender Email is Valid
	 * 
	 * @param string $sender Email of the sender
	 * @return string Validated email
	 */
	public function sender($sender_email, $display_name = '')
	{
		if( ! empty($sender_email) && $this->valid_email($sender_email) )
		{
			$this->sender = $sender_email;
			
			if(! is_null($display_name))
			{
				$this->sender_name = $this->filter_string($display_name);
			}
			return $this->sender;
		}
	}

	/**
	 * Set recepient Email Addresses
	 * 
	 * @param string $recipient Email of the recipient
	 * @return array Email Addresses
	 */
	public function recipient($recipient)
	{
		if( ! empty($recipient) && $this->valid_email($recipient) )
		{
			if( ! in_array($recipient, $this->recipients) )
			{
				$this->recipients[] = $recipient;
			}
		}
	}

	/**
	 * Set Reply to Email Address
	 * 
	 * @param string $recipient Email of the recipient
	 * @return string Email Address
	 */
	public function reply_to($reply_to)
	{
		if($this->valid_email($reply_to))
		{
			$this->reply_to = $reply_to;
			return $this->reply_to;
		}
	}

	/**
	 * Set Email Subject
	 * 
	 * @param string $subject Email Subject
	 * @return string Email Subject
	 */
	public function subject($subject)
	{
		if( ! empty($subject) )
		{
			$this->subject = $this->filter_string($subject);
			return $this->subject;
		} else {
			throw new Exception("Email subject is empty");	
		}
	}

	/**
	 * Email Content
	 * 
	 * @param string $emailContent Email Content
	 */
	public function email_content($emailContent, $type = 'plain')
	{
		$emailContent = wordwrap($emailContent, 70, "\n");
        $this->emailContent = $emailContent;
        $this->emailType = $type;
	}

	/**
	 * Email Attachment
	 * 
	 * @param string $attach_file Email Attachment
	 * @return array Email Attachments
	 */
	public function attachment($attach_file)
	{
		if( ! empty($attach_file) )
		{
			if( ! in_array($attach_file, $this->attach_files) )
			{
				$this->attach_files[] = $attach_file;
			}

		} else {
			throw new Exception("No file attachment was specified");	
		}
	}

	/**
	 * Recreate Attachment
	 * 
	 * @param  Stream File $attachment Attachment File
	 * @return File
	 */
	public function recreate_attachment($attachment)
    {
        if(file_exists($attachment) === true)
        {
			$fileType = mime_content_type($attachment);
			$file_size = filesize($attachment);
			$handle = fopen($attachment, 'rb');
			$content = fread($handle, $file_size);
			$content = chunk_split(base64_encode($content));
			fclose($handle);
            $out = "\r\n";
            $contents = 'Content-Type: '.$fileType.'; name='.basename($attachment).$out;
            $contents .= 'Content-Transfer-Encoding: base64'.$out;
            $contents .= 'Content-ID: <'.basename($attachment).'>'.$out;
            $contents .= $out.$content.$out.$out;
            return $contents;
        }

        return false;
    }

    /**
     * Send Email
     * 
     * @return function Email Sending
     */
	public function send()
	{
		if(( ! is_array($this->recipients) ) || (count($this->recipients) < 1)) {
            return false;
        }

        if($this->emailType == 'plain')
        	$contype = 'Content-Type: text/plain; charset=ISO-8859-1';
        else
        	$contype = 'Content-Type: text/html; charset='.config_item('charset');

        $bm = md5(uniqid(time()).'msg');
        $bc = md5(uniqid(time()).'cont');

        $out = "\r\n";
        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        $headers[] = 'Content-Type: multipart/related;boundary='.$bm;
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'From: '.$this->sender_name.' <'.$this->sender.'>';
		$headers[] = 'Return-Path: '.$this->sender_name.' <'.$this->sender.'>';
      	$headers[] = 'X-Priority: 3';

        if($this->reply_to !== '') {
            $headers[] = 'Reply-To: '.$this->reply_to;
        } else {
            $headers[] = 'Reply-To: '.$this->sender;
        }

      	$contents = $out.'--'.$bm.$out;
        $contents .= 'Content-Type: multipart/alternative; boundary='.$bc.$out;

        if($this->emailContent !== '') {
            $contents .= $out.'--'.$bc.$out;
            $contents .= $contype.$out;
            $contents .= $out.$this->emailContent.$out;
        }

        $contents .= $out.'--'.$bc.'--'.$out;

        foreach($this->attach_files as $attach_file) {
            $attachmentContent = $this->recreate_attachment($attach_file);

            if($attachmentContent !== false) {
                $contents .= $out.'--'.$bm.$out;
                $contents .= $attachmentContent;
            }
        }

        $contents .= $out.'--'.$bm.'--'.$out;

		$recipients = implode(',', $this->recipients);

        return mail($recipients, $this->subject, $contents, implode($out, $headers), '-f'.$this->sender);
	}

}

?>