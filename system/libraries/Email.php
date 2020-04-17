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

/*
 * ------------------------------------------------------
 *  Class Mail / For sending email
 * ------------------------------------------------------
 */

class Email {

	public $sender;

	public $recipients = array();

	public $reply_to;

	public $subject;

	public $attach_files = array();

	public $emailContent;


	public function isEmailValid($email)
	{
		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public function setSender($sender)
	{
		if( ! empty($sender) && $this->isEmailValid($sender) )
		{
			$this->sender = $sender;
			return $this->sender;
		} else {
			throw new Exception("Invalid email address");	
		}
	}

	public function setRecipient($recipient)
	{
		if( ! empty($recipient) && $this->isEmailValid($recipient) )
		{
			if( ! in_array($recipient, $this->recipients) )
			{
				$this->recipients[] = $recipient;
			}
		} else {
			throw new Exception("Invalid email address");	
		}
	}

	public function setReply_to($reply_to)
	{
		if($this->isEmailValid($reply_to))
		{
			$this->reply_to = $reply_to;
			return $this->reply_to;
		} else {
			throw new Exception("Invalid email address");	
		}
	}

	public function setSubject($subject)
	{
		if( ! empty($subject) )
		{
			$this->subject = $subject;
			return $this->subject;
		} else {
			throw new Exception("Email subject is empty");	
		}
	}

	public function setEmailContent($emailContent)
	{
		$emailContent = wordwrap($emailContent, 70, "\n");
        $this->emailContent = $emailContent;
	}


	public function setAttachment($attach_file)
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

	public function recreateAttachment($attachment)
    {
        if(file_exists($attachment) === true)
        {
			$fileType = get_mime_type(pathinfo($attachment, PATHINFO_EXTENSION));
			$file_size = filesize($attachment);
			$handle = fopen($attachment, "r");
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

	public function send()
	{
		if(( ! is_array($this->recipients) ) || (count($this->recipients) < 1)) {
            return false;
        }

        $bm = md5(uniqid(time()).'msg');
        $bc = md5(uniqid(time()).'cont');

        $out = "\r\n";
        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        $headers[] = 'Content-Type: multipart/related;boundary='.$bm;
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'From: '.$this->sender;

        if(trim($this->reply_to) !== '') {
            $headers[] = 'Reply-To: '.$this->reply_to;
        } else {
            $headers[] = 'Reply-To: '.$this->sender;
        }

      	$contents = $out.'--'.$bm.$out;
        $contents .= 'Content-Type: multipart/alternative; boundary='.$bc.$out;

        if(trim($this->emailContent) !== '') {
            $contents .= $out.'--'.$bc.$out;
            $contents .= 'Content-Type: text/plain; charset=ISO-8859-1'.$out;
            $contents .= $out.$this->emailContent.$out;
        }

        $contents .= $out.'--'.$bc.'--'.$out;

        //echo ($this->recreateAttachment($this->attach_files[0])); die;
        foreach($this->attach_files as $attach_file) {
            $attachmentContent = $this->recreateAttachment($attach_file);

            if($attachmentContent !== false) {
                $contents .= $out.'--'.$bm.$out;
                $contents .= $attachmentContent;
            }
        }

        $contents .= $out.'--'.$bm.'--'.$out;

		$recipients = implode(',', $this->recipients);

        return mail($recipients, $this->subject, $contents, implode($out, $headers));
	}

}

?>