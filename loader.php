<?php

include_once('bootstrap.php');
include_once('config.php');
include_once('twitter/client.php');
include_once('database/mysql/connection.php');


$twitter = new TwitterClient(
    $twitLogin, 
    $twitPass, 
    $twitEncoding
);

$db = new DatabaseMysqlConnection(
    $twitDb['host'], 
    $twitDb['user'], 
    $twitDb['pw'], 
    $twitDb['db']
);
$dbHelper = $db->getHelper();

$ids = $dbHelper->createDictionary(
    $twitTable, 
    'twitId'
);

$twits = $twitter->getSearchResult('umarafon', 20);
$count = 0;

foreach ($twits as $i => $twit)
{
    $text = strip_tags($twit->getText());
    
    if (!isset($ids[$twit->getId()]))
    {
        $ban = array(
            'herovina',
            '404ok',
            '404fest',
        );

        $isOk = true;
        $userName = $twit->getFrom()->getName();
        foreach ($ban as $i => $banName)
        {
            if (false === stripos($userName, $banName))
            {
            }
            else
            {
                $isOk = false;
                break;
            }
        }
        
        
        
        if ($isOk)
        {
            if (preg_match('(^(.+?) \((.+?)\)$)', $userName, $userNameParts))
            {
                $messagePrepend = sprintf(
                    '<a href="http://twitter.com/%s">%s</a>: ',
                    $userNameParts[1],
                    $userNameParts[2]
                );
            }
            else
            {
                $messagePrepend = null;
            }
            
            
            $item = array(
                'crdate'    => $twit->getDate('Y-m-d H:i:s'), 
                'name'      => substr($text, 0, 50) . (strlen($text) > 50 ? '...' : ''), 
                'annot'     => $messagePrepend . $twit->getText(), 
                'descr'     => null, 
                'isHidden'  => $text{0} == '@' ? true : false, 
                'twitId'    => $twit->getId(), 
                'twitUrl'   => $twit->getUrl() 
            );
            $dbHelper->insertItem(
                $twitTable,
                $item
            );
            ++$count;
        }
        
    }
}
printf('Импортировано твитов: %d<br/>', $count);
