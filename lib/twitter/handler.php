<?php

include_once('client.php');

class TwitterHandler extends TwitterClient
{
    private $users = array();
    private $isPublic = false;
    private $appendAlias = true;

    public function __construct($user, $pw, $encoding = 'UTF-8', $isPublic = false, $appendAlias = true)
    {
        parent::__construct($user, $pw, $encoding);
        $this->isPublic = $isPublic;
        $this->appendAlias = $appendAlias;
    }
    
    public function addUser($uin, $name)
    {
        $this->users[$uin] = $name;
    }
    
    public function handleMessage($message)
    {
        $result = false;
        if (isset($this->users[$message['from']]))
        {
            if ($this->appendAlias)
            {
                $id = $this->update(
                    sprintf(
                        '%s: %s',
                        $this->users[$message['from']],
                        $message['message']
                    )
                );
            }
            else
            {
                $id = $this->update($message['message']);
            }
            $result = true;
        }
        elseif ($this->isPublic)
        {
            $id = $this->update($message['message']);
            $result = true;
        }
        
        if ($result)
        {
            $message['client']->sendMessage(
                $message['from'],
                sprintf(
                	'Message sended, available at http://twitter.com/%s/status/%s', 
                    $this->user,
                    $id
                )
            );
        }
        else
        {
            $message['client']->sendMessage(
                $message['from'],
                'Your accaunt not in white list'
            );
        }
        return $result;
    }
}