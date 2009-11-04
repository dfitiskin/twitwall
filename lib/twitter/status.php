<?php

class TwitterStatus
{
    private $id = null;
    private $crdate = null;
    private $realDate = null;
    private $text = null;
    /**
     * @var TwitterUser
     */
    private $from = null;
    private $isMineFlag = false;
    
    public function __construct($id, $crdate, $text, $from)
    {
        $this->id = (string)$id;
        $this->crdate = date('Y-m-d H:i:s', strtotime($crdate));
        $this->realDate = $crdate;
        $this->text = $text;
        $this->from = $from;
    }
    
    public function checkIsMine($accaunt)
    {
        $this->isMineFlag = (bool) ($accaunt == $this->from->getScreenName());
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function getFrom()
    {
        return $this->from;
    }
    
    public function getUrl()
    {
        return sprintf(
            '%s/status/%s/',
            $this->getAuthorUrl(),
            $this->getId()
        );
    }
    
    public function getDate($format = 'd.m.Y H:i')
    {
        return date($format, strtotime($this->crdate));
    }
    
    public function getRealDate()
    {
        return $this->realDate;
    }
    
    public function getAuthorName()
    {
        return $this->from->getName();
    }
    
    public function getAuthorUrl()
    {
        return sprintf('http://twitter.com/%s', $this->from->getScreenName());
    }
    
    public function getAuthorUserPicUrl()
    {
        return $this->from->getPicUrl();
    }
    
    public function isMine()
    {
        return $this->isMineFlag;
    }
}