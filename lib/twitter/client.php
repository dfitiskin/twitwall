<?php

include_once('http/curl/request.php');
include_once('twitter/status.php');
include_once('twitter/message.php');
include_once('twitter/user.php');

class TwitterClient
{
	protected $user = null;
	private $pw = null;
	protected $clientEncoding = 'UTF-8';
	private $serverEncoding = 'UTF-8';
	
	public function __construct($user, $pw, $encoding = 'UTF-8')
	{
		$this->user = $user;
		$this->pw = $pw;
		$this->clientEncoding = $encoding;
	}
	
	private function encodeMessage($message)
	{
		$result = $message;
		if ($this->clientEncoding !== $this->serverEncoding)
		{
			$result = iconv($this->clientEncoding, $this->serverEncoding, $message);
		}
		return $result; 
	}
	
    private function decodeMessage($message)
    {
        $result = $message;
        if ($this->clientEncoding !== $this->serverEncoding)
        {
            $result = iconv($this->serverEncoding, $this->clientEncoding, $message);
        }
        return $result; 
    }
	
	public function update($message, $replyToMessageId = null)
	{
		$reqData = array();
		$reqData['status'] = $this->encodeMessage($message);
		
		if ($replyToMessageId)
		{
			$reqData['in_reply_to_status_id'] = $replyToMessageId;
		}
		
		$req = new HttpCurlRequest('http://twitter.com/statuses/update.xml');
		$req->setBasicAuth($this->user, $this->pw);
		$req->setPostData($reqData);
		
		$resp = $req->getResponse();
		$result = $this->extractResponseId($resp);
		return $result['id'];
	}
	
	public function directMessage($message, $to)
	{
	    $reqData = array();
        $reqData['text'] = $this->encodeMessage($message);
        $reqData['user'] = $to;
        
        $req = new HttpCurlRequest('http://twitter.com/direct_messages/new.xml');
        $req->setBasicAuth($this->user, $this->pw);
        $req->setPostData($reqData);
        
        $resp = $req->getResponse();
        $result = $this->extractResponseId($resp);
        return $result['id'];
	}
	
    public function destroyDirectMessage($id)
    {
        $reqData = array();
        $reqData['id'] = $id;
        
        $req = new HttpCurlRequest(sprintf('http://twitter.com/direct_messages/destroy/%d.xml', $id));
        $req->setBasicAuth($this->user, $this->pw);
        $req->setPostData($reqData);
        
        $resp = $req->getResponse();
        return $resp;
    }	
	
	public function destroy($id)
	{
		$reqData = array();
		$reqData['id'] = $id;
		
		$req = new HttpCurlRequest(sprintf('http://twitter.com/statuses/destroy/%d.xml', $id));
		$req->setBasicAuth($this->user, $this->pw);
		$req->setPostData($reqData);
		
		$resp = $req->getResponse();
		return $resp;
	}
	
	private function extractResponseId($resp)
	{
	    $result = array();
	    if (preg_match('(<id>(\d+)</id>)', $resp, $parts))
	    {
	        $result['id'] = $parts[1];
	    }
	    return $result;
	}
	
	public function getFriendsTimeline()
	{
	    $req = new HttpCurlRequest('http://twitter.com/statuses/friends_timeline.xml');
        $req->setBasicAuth($this->user, $this->pw);
        
        $resp = $req->getResponse();
        return $this->extractTimeline($resp);
	}
	
	public function getSearchResult($query, $count = 0, $sinceId = 0)
	{
	    $url = sprintf('http://search.twitter.com/search.atom?q=%s', urlencode($query));
	    
	    $params = array();
        if ($count)
        {
            $params[] = sprintf('count=%d', $count); 
        }
        
        if ($sinceId)
        {
            $params[] = 'since_id=' . $sinceId; 
        }
        
        if ($params)
        {
            $url = sprintf(
               '%s&%s',
               $url,
               implode('&', $params) 
            );
        }
        
        $req = new HttpCurlRequest($url);
        $req->setBasicAuth($this->user, $this->pw);
        
        $resp = $req->getResponse();

        $data = new SimpleXMLElement(
            $this->decodeMessage($resp)
        );
        
        $result = array();
        foreach ($data->entry as $i => $statusNode)
        {
            $idParts = split(':', $statusNode->id);
            
            $user = new TwitterUser(
                0,
                $statusNode->author->name
            );
            
            $user->setName(
                $this->decodeMessage($statusNode->author->name)
            );
            
            //$user->setPicUrl($node->profile_image_url);
            
            $status = new TwitterStatus(
                array_pop($idParts),
                $statusNode->published,
                $this->decodeMessage($statusNode->content),
                $user       
            );
            $status->checkIsMine($this->user);
            $result[] = $status;
            unset($status);
        }
        return $result;
        
	}
	
	public function getTimeline($count = 0, $sinceId = 0)
	{
	    $url = 'http://twitter.com/statuses/user_timeline.xml';
	    
	    $params = array();
	    if ($count)
	    {
	        $params[] = sprintf('count=%d', $count); 
	    }
	    
	    if ($sinceId)
	    {
	        $params[] = 'since_id=' . $sinceId; 
	    }
	    
	    if ($params)
	    {
	        $url = sprintf(
	           '%s?%s',
	           $url,
	           implode('&', $params) 
	        );
	        
	    }
        return $this->getMessages($url);
	}
	
    public function getUserTimeline($userId)
    {
        return $this->getMessages(sprintf('http://twitter.com/statuses/user_timeline/%s.xml', $userId));
    }
    
    public function getReplies()
    {
        return $this->getMessages('http://twitter.com/statuses/replies.xml');
    }
    
    public function getDirectMessages()
    {
        return $this->getMessagesX('http://twitter.com/direct_messages.xml');
    }
    
    public function getSentDirectMessages()
    {
        return $this->getMessagesX('http://twitter.com/direct_messages/sent.xml');
    }    
    
    private function getMessages($url)
    {
        $req = new HttpCurlRequest($url);
        $req->setBasicAuth($this->user, $this->pw);
        
        $resp = $req->getResponse();
        return $this->extractTimeline($resp);
    }
    
    private function getMessagesX($url)
    {
        $req = new HttpCurlRequest($url);
        $req->setBasicAuth($this->user, $this->pw);
        
        $resp = $req->getResponse();
        return $this->extractMessages($resp);
    }    
	
	private function extractTimeline($resp)
	{
	    $data = new SimpleXMLElement(
            $this->decodeMessage($resp)
        );
        
        $result = array();
        foreach ($data->status as $i => $statusNode)
        {
            $status = $this->createStatusFromXmlNode($statusNode);
            $status->checkIsMine($this->user);
            $result[] = $status;
            unset($status);
        }
        return $result;
	}
	
	private function extractMessages($resp)
	{
	    $data = new SimpleXMLElement(
            $this->decodeMessage($resp)
        );
        
        $result = array();
        foreach ($data->direct_message as $i => $statusNode)
        {
            $status = $this->createMessageFromXmlNode($statusNode);
            $status->checkIsMine($this->user);
            $result[] = $status;
            unset($status);
        }
        return $result;
	}
	
	private function createStatusFromXmlNode($node)
	{
	    $status = new TwitterStatus(
            $node->id,
            $node->created_at,
            $this->decodeMessage($node->text),
            $this->createUserFromXmlNode($node->user)        
	    );
	    return $status;
	}
	
    private function createMessageFromXmlNode($node)
	{
	    $status = new TwitterMessage(
            $node->id,
            $node->created_at,
            $this->decodeMessage($node->text),
            $this->createUserFromXmlNode($node->sender),
            $this->createUserFromXmlNode($node->recipient)                            
	    );
	    return $status;
	}	
	
	private function createUserFromXmlNode($node)
	{
	    $user = new TwitterUser(
            $node->id,
            $node->screen_name
	    );
	    
	    $user->setName(
            $this->decodeMessage($node->name)
	    );
	    
	    $user->setPicUrl($node->profile_image_url);
	    
	    return $user;
	}
	
	public function followUser($userId, $notify = true)
	{
	    $reqData = array();
        $reqData['id'] = $userId;
        
        $req = new HttpCurlRequest(sprint('http://twitter.com/friendships/create/%s.xml', $userId));
        $req->setBasicAuth($this->user, $this->pw);
        $req->setPostData($reqData);
        
        $resp = $req->getResponse();
        $result = $this->extractResponseId($resp);
        return $result['id'];
	}
	
    public function unfollowUser($userId)
    {
        $reqData = array();
        $reqData['id'] = $userId;
        
        $req = new HttpCurlRequest(sprint('http://twitter.com/friendships/destroy/%s.xml', $userId));
        $req->setBasicAuth($this->user, $this->pw);
        $req->setPostData($reqData);
        
        $resp = $req->getResponse();
        $result = $this->extractResponseId($resp);
        return $result['id'];
    }	
}
