Node.prototype.insertAfter = function(newNode, refNode)
{
    if (refNode)
    {
		if (refNode.nextSibling)
	    {
	        return this.insertBefore(newNode, refNode.nextSibling);
	    }
    }
    return this.appendChild(newNode);
}
get = function (id)
{
	return document.getElementById(id) || false;
}

var Twi = function (storage)
{
	this.tweetStorage = storage;
	this.url = '/twitwall/terminal.php';
	this.tweets = new Array();

	this.updateTimeout = false;
	this.updateTimeoutValue = 10000;

	this.moveTimeout = false;
	this.moveTimeoutValue = 5000;
	
	this.birnTimeout = false;
	this.birnTimeoutValue = 80;

	this.lastTweet = false;
	this.lastId = 0;
	this.idPrefix = 'tweet-';
	
	this.idToKill = 0;
	
	this.tweetLimit = 20;
	this.i = 0;

	this.createXmlHttp = function ()
	{
	    if (typeof XMLHttpRequest != "undefined")
	    {
			return new XMLHttpRequest();
	    }
	    else if (window.ActiveXObject)
	    {
	        var aVersions = ["MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP", "Microsoft.XMLHTTP"];
	        for(var i = 0; i < aVersions.length; i++)
	        {
	            try
	            {
					var oXmlHttp = new ActiveXObject(aVersions[i]);
	                return oXmlHttp;
	            }
	            catch (oError)
	            {
	                // Не удалось подключить
	            }
	        }
	    }
	    throw new Error("Невозможно создать объект XMLHttp.");
	}

	this.getNew = function ()
	{
		clearTimeout(this.updateTimeout);
		var oXmlHttp = this.createXmlHttp();
		var url = this.url + '?lastId=' + this.lastId;
		oXmlHttp.open("GET", url, true);
		oXmlHttp.onreadystatechange = function ()
		{
			switch(oXmlHttp.readyState)
			{
				case 4 :
					if (oXmlHttp.status == 200)
					{
						if (!get('tmp'))
						{
							var tmp = document.createElement('DIV');
							tmp.style.display = 'none';
							tmp.id = 'Twitmp';
							document.body.appendChild(tmp);
						}
						get('Twitmp').innerHTML = oXmlHttp.responseText;
						this.updateTimeout = setTimeout("window.twi.prepareNew()", 100);
					}
					else
					{
						// bad...
					}
				break;
			}
		}
		oXmlHttp.send(null);
	}

	this.prepareNew = function ()
	{
		clearTimeout(this.updateTimeout);
		var newTweets = get('Twitmp').getElementsByTagName('LI');
		
		if (newTweets.length)
		{
			var lastId = newTweets[0].id.replace(this.idPrefix, '');
			var lastTweet = newTweets[0];
		}
		
		var start = this.i;
		var finish = this.i + + newTweets.length;
		while (newTweets.length)
		{
			if (this.lastTweet)
			{
				this.i = start + newTweets.length;
				var node = this.tweetStorage.insertAfter(newTweets[0], this.lastTweet);
			}
			else
			{
    			++this.i;
	            var node = this.tweetStorage.insertAfter(newTweets[newTweets.length - 1], this.lastTweet);
			}
		    //node.innerHTML += '[' + this.i + ']';
		}
		this.i = finish;
		
		if (lastId && lastTweet)
		{
			this.lastId = lastId;
			this.lastTweet = lastTweet;
		}

		this.updateTimeout = setTimeout("window.twi.getNew()", this.updateTimeoutValue);
	}

	this.move = function ()
	{
		clearTimeout(this.moveTimeout);
		if (this.tweetStorage.firstChild)
		{
			if (!this.idToKill)
			{
				this.idToKill = this.tweetStorage.firstChild.id;
			}
			
			if (this.idToKill == this.tweetStorage.firstChild.id)
			{
				if (this.tweetStorage.childNodes.length <= this.tweetLimit)
				{
					this.tweetStorage.appendChild(this.tweetStorage.firstChild.cloneNode(true));
				}
				else
				{
					this.idToKill = false;
				}
			}
			else
			{
				this.tweetStorage.appendChild(this.tweetStorage.firstChild.cloneNode(true));
			}
			this.birn(this.tweetStorage.firstChild);
		}
		else
		{
			window.twi.moveTimeout = setTimeout("window.twi.move()", window.twi.moveTimeoutValue);
		}
	}

	this.birn = function (node)
	{
		if (!node.style.height) node.style.height = node.offsetHeight + 'px';
		
		clearTimeout(window.twi.birnTimeout);
		if (parseFloat(node.style.height) <= 1)
		{
			node.parentNode.removeChild(node);
			window.twi.moveTimeout = setTimeout("window.twi.move()", window.twi.moveTimeoutValue);
		}
		else
		{
			node.style.height = parseInt(node.style.height) - (parseInt(node.style.height) / 2) + 'px';
			window.twi.birnTimeout = setTimeout("window.twi.birn(window.twi.tweetStorage.firstChild)", window.twi.birnTimeoutValue);
		}
	}
	

	this.init = function ()
	{
		this.getNew();
		this.moveTimeout = setTimeout("window.twi.move()", this.moveTimeoutValue);
	}
}


window.twi = new Twi(get('tweets'));
window.twi.init();