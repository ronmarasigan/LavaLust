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
class Email
{
    private $additionalParameter = '';
    
    private $attachments = array();

    private $bcc = array();

    private $cc = array();

    private $contentHTML = '';

    private $contentPlain = '';

    private $receivers = array();

    private $replyTo = '';

    private $sender = '';

    private $subject = '';

    public function addAttachment($attachment)
    {
        if(file_exists($attachment) === true) {

            if(in_array($attachment, $this->attachments) === false) {
                $this->attachments[] = $attachment;
            }
        } else {
            throw new Exception('Attachment not found: '.$attachment);
        }
    }

    public function addBCC($bcc)
    {
        if($this->isValidAddress($bcc) === true) {

            if(in_array($bcc, $this->bcc) === false) {
                $this->bcc[] = $bcc;
            }
        } else {
            throw new Exception('Invalid bcc mail-address: '.$bcc);
        }
    }

    public function addCC($cc)
    {
        if($this->isValidAddress($cc) === true) {

            //Check if the CC mail-address is already in the collection.
            if(in_array($cc, $this->cc) === false) {
                $this->cc[] = $cc;
            }
        } else {
            throw new Exception('Invalid cc mail-address: '.$cc);
        }
    }

    public function addReceiver($receiver)
    {
        if($this->isValidAddress($receiver) === true) {

            if(in_array($receiver, $this->receivers) === false) {
                $this->receivers[] = $receiver;
            }
        } else {
            throw new Exception('Invalid receiver mail-address: '.$receiver);
        }
    }

    public function isValidAddress($address)
    {
        if(filter_var($address, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        if(filter_var(idn_to_ascii(utf8_encode($address)), FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        return false;
    }

    public function prepareAttachment($attachment)
    {
        if(file_exists($attachment) === true) {

            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $fileInfo->file($attachment);

            $file = fopen($attachment, "r");
            $fileContent = fread($file, filesize($attachment));
            $fileContent = chunk_split(base64_encode($fileContent));
            fclose($file);

            $msgContent = 'Content-Type: '.$fileType.'; name='.basename($attachment)."\r\n";
            $msgContent .= 'Content-Transfer-Encoding: base64'."\r\n";
            $msgContent .= 'Content-ID: <'.basename($attachment).'>'."\r\n";
            $msgContent .= "\r\n".$fileContent."\r\n\r\n";
            return $msgContent;
        }

        return false;
    }

    public function reset()
    {
        $this->cc = array();
        $this->bcc = array();
        $this->receivers = array();
        $this->attachments = array();
        $this->contentHTML = '';
        $this->contentPlain = '';
    }

    public function send()
    {
        if(trim($this->sender) === '') {
            return false;
        }
        
        if((is_array($this->receivers) === false) || (count($this->receivers) < 1)) {
            return false;
        }

        $boundaryMessage = md5(rand().'message');
        $boundaryContent = md5(rand().'content');

        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: PHP/'.phpversion();
        $headers[] = 'Date: '.date('r', $_SERVER['REQUEST_TIME']);
        $headers[] = 'X-Originating-IP: '.$_SERVER['SERVER_ADDR'];
        $headers[] = 'Content-Type: multipart/related;boundary='.$boundaryMessage;
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $headers[] = 'From: '.$this->sender;
        $headers[] = 'Return-Path: '.$this->sender;

        if(trim($this->replyTo) !== '') {
            $headers[] = 'Reply-To: '.$this->replyTo;
        } else {
            $headers[] = 'Reply-To: '.$this->sender;
        }

        if((is_array($this->bcc) === true) && (count($this->bcc) > 0)) {
            $headers[] = 'Bcc: '.implode(', ', $this->bcc);
        }

        if((is_array($this->cc) === true) && (count($this->cc) > 0)) {
            $headers[] = 'Cc: '.implode(', ', $this->cc);
        }

        $msgContent = "\r\n".'--'.$boundaryMessage."\r\n";
        $msgContent .= 'Content-Type: multipart/alternative; boundary='.$boundaryContent."\r\n";

        if(trim($this->contentPlain) !== '') {
            $msgContent .= "\r\n".'--'.$boundaryContent."\r\n";
            $msgContent .= 'Content-Type: text/plain; charset=ISO-8859-1'."\r\n";
            $msgContent .= "\r\n".$this->contentPlain."\r\n";
        }

        if(trim($this->contentHTML) !== '') {
            $msgContent .= "\r\n".'--'.$boundaryContent."\r\n";
            $msgContent .= 'Content-Type: text/html; charset=ISO-8859-1'."\r\n";
            $msgContent .= "\r\n".$this->contentHTML."\r\n";
        }

        $msgContent .= "\r\n".'--'.$boundaryContent.'--'."\r\n";

        foreach($this->attachments as $attachment) {
            $attachmentContent = $this->prepareAttachment($attachment);

            if($attachmentContent !== false) {
                $msgContent .= "\r\n".'--'.$boundaryMessage."\r\n";
                $msgContent .= $attachmentContent;
            }
        }

        $msgContent .= "\r\n".'--'.$boundaryMessage.'--'."\r\n";

        $receivers = implode(',', $this->receivers);

        return mail($receivers, $this->subject, $msgContent, implode("\r\n", $headers), $this->additionalParameter);
    }

    public function setAdditionalParameter($parameter)
    {
        $this->additionalParameter = $parameter;
    }
    
    public function setContentHTML($content)
    {
        $content = wordwrap($content, 70, "\n");
        $this->contentHTML = $content;
    }

    public function setContentPlain($content)
    {
        $content = wordwrap($content, 70, "\n");
        $this->contentPlain = $content;
    }

    public function setReplyTo($reply_to)
    {
        //Check if the Reply-To mail-address is valid.
        if($this->isValidAddress($reply_to) === true) {
            $this->replyTo = $reply_to;
        } else {
            throw new Exception('Invalid reply-to mail-address: '.$reply_to);
        }
    }

    public function setSender($sender)
    {
        //Check if the sender mail-address is valid.
        if($this->isValidAddress($sender) === true) {
            $this->sender = $sender;
        } else {
            throw new Exception('Invalid sender mail-address: '.$sender);
        }
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}

?>