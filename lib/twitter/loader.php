<?php

include_once('twitter/client.php');
include_once('system/file.php');

class TwitterLoader extends TwitterClient
{
    function loadTimeline($limit)
    {
        $twits = $this->loadCache();
        if (!$twits)
        {
            $twits = $this->realLoadTimeline($limit);
            $this->saveCache($twits);
        }
        return $twits;
    }
    
    function getCacheFileName()
    {
        return sprintf(
            '%s/temp/twit/%s/%s',
            APPLICATION_PATH,
            $this->user,
            'timeline.cache'
        );
    }
    
    function saveCache($twits)
    {
        $file = new SystemFile($this->getCacheFileName());
        $file->setContent(
            sprintf(
                'return %s;', 
                var_export(
                    array(
                        'twits'  => $twits,
                        'crdate' => date('Y-m-d H:i:s'),
                    ), 
                    true
                )
            )
        );
        $file->close();
    }
    
    function loadCache()
    {
        $data = file_get_contents($this->getCacheFileName());
        if ($data)
        {
            $info = eval($data);
        }

        if (isset($info['twits'], $info['crdate']) && $info['twits'] && $info['crdate'] > date('Y-m-d H:i:s', strtotime('-5 minute')))
        {
            return $info['twits'];
        }
        else
        {
            return array();
        }
    }
    
    function realLoadTimeline($limit, $sinceId = 0)
    {
        $months = array(
            '€нвар€', 'феврал€', 'марта', 'апрел€', 'ма€', 'июн€', 'июл€', 'июл€', 'августа', 'сент€бр€', 'окт€бр€', 'но€бр€', 'декабр€'
        );        
        
        $statuses = $this->getTimeline($limit, $sinceId);
        
        $twits = array();
        for ($i = 0, $imax = count($statuses); $i < $imax; $i++)
        {
            $status = $statuses[$i];
            
            $twits[] = array(
                'date'  => sprintf(
                    '%d %s в %s',
                    $status->getDate('j'),
                    $months[$status->getDate('n') - 1],
                    $status->getDate('H:i')
                ),
                'text'  => $this->decorate($status->getText()),
            );
        }
        
        return $twits;
    }
    
    function decorate($text)
    {
        $in=array(
            '`((?:https?|ftp)://(\S+[[:alnum:]]/?))`si',
            //'`((?<!//)(www\.\S+[[:alnum:]]/?))`si',
            '`((\S+[[:alnum:]])@(\S+[[:alnum:]]))`si',
            '`^@([a-z0-9_-]+)`si',
            '` @([a-z0-9_-]+)`si',
            '`^#([a-z0-9_-]+)`si',
            '` #([a-z0-9_-]+)`si',
        );
        $out=array(
            '<a href="$1">$1</a>',
            //'<a href="http://$1">$1</a>',
            '<a href="mailto:$1">$1</a>',
            '<a class="twit-user" href="http://twitter.com/$1">@$1</a>',
            ' <a class="twit-user" href="http://twitter.com/$1">@$1</a>',
            '<a class="twit-search" href="http://search.twitter.com/search?q=$1">#$1</a>',
            ' <a class="twit-search" href="http://search.twitter.com/search?q=$1">#$1</a>',
        );
        $text = preg_replace($in, $out, $text);
        
        if (preg_match_all('(<a href="(http://[a-z0-9-_./]+)">((http://)?[a-z0-9-_./]+)</a>)i', $text, $parts, PREG_SET_ORDER))
        {
            foreach ($parts as $i => $part)
            {
                $text = str_replace(
                    sprintf('<a href="%s">%s</a>', $part[1], $part[2]),
                    sprintf(
                        '<a href="%s">%s</a>', 
                        $part[1], 
                        $this->expandUrl($part[1])
                    ),
                    $text
                );
            }
        }
        return $text;
    }
    
    function expandUrl($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $code = trim(parse_url($url, PHP_URL_PATH), '/');
        
        if ('tr.im' == $host)
        {
            $expandTarget = sprintf('http://api.tr.im/api/trim_destination.xml?trimpath=%s', $code);
            $req = new HttpCurlRequest($expandTarget);
            $resp = $req->getResponse();
            
            if (preg_match('(<destination>(http://[a-z0-9_./#-]+)</destination>)i', $resp, $parts))
            {
                return $parts[1];
            }
        }
        return $url;
    }
}