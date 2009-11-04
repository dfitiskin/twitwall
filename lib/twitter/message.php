<?php

class TwitterMessage
{
    private $id = null;
    private $crdate = null;
    private $text = null;
    /**
     * @var TwitterUser
     */
    private $from = null;
    private $isMineFlag = false;
    
    public function __construct($id, $crdate, $text, $from, $to)
    {
        $this->id = $id;
        $this->crdate = date('Y-m-d H:i:s', strtotime($crdate));
        $this->text = $text;
        $this->from = $from;
        $this->to = $to;
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
    
    public function getUrl()
    {
        return sprintf(
            '%s/status/%s/',
            $this->getAuthorUrl(),
            $this->getId()
        );
    }
    
    public function getFrom()
    {
        return $this->from;
    }
    
    public function getDate($format = 'd.m.Y H:i')
    {
        return date($format, strtotime($this->crdate));
    }
    
    public function getCrDate()
    {
        return $this->crdate;
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
