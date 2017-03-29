<?php
// Switchboard to Book Store Functions
function book_store_switch()
{
	// Define the possible Book Store function URLs which the page can be accessed from
	$possible_function_url = array("getBook", "getSectionBooks", "createBook", "findOrCreatePublisher", "toggleBook",
		"orderBook", "findOrCreateAuthor");

	if (isset($_GET["function"]) && in_array($_GET["function"], $possible_function_url))
	{
		switch ($_GET["function"])
		{
			case "createBook":
				if (isset($_POST["publisher_name"])){
					$pid = findOrCreatePublisher($_POST["publisher_name"], $_POST["address"], $_POST["website"]);
					$aid = findOrCreateAuthor($_Post["f_name"], $_POST["l_name"]);
				}
				else{
					logError("createBook ~ Required parameters were not submited correctly.");
					return ("findOrCreatePublisher One or more parameters were not provided");
				}
				if (isset($_POST["isbn"]) &&
					isset($_POST["title"]) &&
					isset($_POST["price"]) &&
					isset($_POST["thumbnail_url"]) &&
					isset($_POST["available"]) &&
					isset($_POST["count"]) 
				)
					{	
					return createBook(
						$_POST["isbn"], 
						$_POST["title"],
						$pid,
						$_POST["price"],
						$_POST["thumbnail_url"],
						$_POST["available"],
						$_POST["count"]
						);
					}
				else{
					logError("createBook ~ Required parameters were not submited correctly.");
					return ("createBook One or more parameters were not provided");
				}
			case "findOrCreatePublisher":
				logError("log or create pub case");
				if (isset($_POST["publisher_name"])){
					$pid = findOrCreatePublisher($_POST["publisher_name"], $_POST["address"], $_POST["website"]);
					return $pid;
				}
			case "findOrCreateAuthor":
				logError("log or create author case");
				if (isset($_POST["first_name"]) && isset($_POST["last_name"])){
					$aid = findorcreateAuthor($_POST["first_name"], $_POST["last_name"]);
					return $aid;
				}
				else{
					logError("findOrCreateAuthor ~ Required parameters were not submitted correctly.");
					return ("findOrCreateAuthor One or more parameters were not provided");
				}
			case "updateBook":
				if (isset($_GET["isbn"]) &&
					isset($_GET["title"]) &&
					isset($_GET["publisher_id"]) &&
					isset($_GET["price"]) &&
					isset($_GET["thumbnail_url"]) &&
					isset($_GET["available"]) &&
					isset($_GET["count"]))
					{
					return updateBook(
						$_POST["isbn"],
						$_POST["title"], 
						$_POST["publisher_id"], 
						$_POST["price"],
						$_POST["thumbnail_url"], 
						$_POST["available"], 
						$_POST["count"]);
					} else {
						logError("updateBook ~ Required parameters were not submitted correctly.");
						return ("One or more book parameters for updating this book were not provided.");
					}
			case "getBook":
				//if has params
				if (isset($_GET["isbn"])){
					return getBook($_GET["isbn"]);
				} else {
					logError("getBook ~ Required isbn parameter was not submitted correctly.");
					return ("getBook book isbn parameter was not submitted correctly.");
				}
				// return "Missing " . $_GET["param-name"]
			case "getSectionBooks":
				//if has params
				if (isset($_GET["section_id"])){
					return getSectionBooks($_GET["section_id"]);
				} else {
					logError("getBook ~ Required isbn parameter was not submitted correctly.");
					return ("getBook book isbn parameter was not submitted correctly.");
				}
            case "toggleBook":
                if (isset($_GET["isbn"]) && isset($_GET["available"]))
                {
                    return toggleBook($_GET["isbn"], $_GET["available"]);
                }
                else{
                    logError("getBook ~ Required isbn and-or available parameter not submitted correctly.");
                    return ("toggleBook isbn and-or available parameter not submitted correctly.");
                }
			case "orderBook":
                if (isset($_GET["isbn"]) && isset($_GET["amount"]))
                {
                    return orderBook($_GET["isbn"], $_GET["amount"]);
                }
                else{
                    logError("orderBook ~ Required isbn and-or amount parameter not submitted correctly.");
                    return ("orderBook isbn and-or amount parameter not submitted correctly.");
                }
		}
	}
}

//Define Functions Here
function createBook($isbn, $title, $publisher_id, $price, $thumbnail_url, $available, $count)
{
	logError("createBook ");

	try 
		{
			//$sqlite = new SQLite3($GLOBALS ["databaseFile"]);

			$sqlite = new SQLite3($GLOBALS["databaseFile"]); 
		
			$sqlite->enableExceptions(true);
			
			//prepare query to protect from sql injection
			$query = $sqlite->prepare("INSERT INTO Book (isbn, title, publisher_id, 
						price, thumbnail_url, available, count) VALUES (:isbn, :title, :publisher_id,
							:price, :thumbnail_url, :available, :count)");
							
			$query->bindParam(':isbn', $isbn);
			$query->bindParam(':title', $title);
			$query->bindParam(':publisher_id', $publisher_id);
			$query->bindParam(':thumbnail_url', $thumbnail_url);
			$query->bindParam(':price', $price);
			$query->bindParam(':available', $available);
			$query->bindParam(':count', $count);
			$result = $query->execute();
			return $result;
	}
	catch (Exception $exception)
	{
		if ($GLOBALS ["sqliteDebug"]) 
		{
			return $exception->getMessage();
		}
		logError($exception);
	}
}

function findOrCreatePublisher($name, $address, $website){
	logError("findorcreate ");
	try{
		$sqlite = new SQLite3($GLOBALS["databaseFile"]); 
		
		$sqlite->enableExceptions(true);
		$pub_query = $sqlite->prepare("Select id from publisher where name=:name");
		$pub_query->bindParam(":name", $name);	//possible duplicate
		$publisher_id = $pub_query->execute();
		logError('outside of if statemet');
		$pub_id = $publisher_id->fetchArray();
		logError($pub_id[0]);
		if (empty($pub_id)){
			logError("inside if statement");
			$pub_query = $sqlite->prepare("INSERT INTO Publisher (name, address, website) 
				VALUES (:name, :address, :website)");
			$pub_query->bindParam(':name', $name);
			$pub_query->bindParam(':address', $address);
			$pub_query->bindParam(':website', $website);
			$pub_query->execute();
			$pub_query = $sqlite->prepare("Select id from publisher where name=:name");
			$publisher_id = $pub_query->execute();
			$pub_id = $publisher_id->fetchArray();			
		}
		
	}
	catch (Exception $exception)
	{
		if ($GLOBALS ["sqliteDebug"]) 
		{
			return $exception->getMessage();
		}
		logError($exception);
	}
	return $pub_id[0];
}

function findOrCreateAuthor($f_name, $l_name){
	logError("findorcreateAuthor ");
	try{
		$sqlite = new SQLite3($GLOBALS["databaseFile"]); 
		
		$sqlite->enableExceptions(true);
		$author_query = $sqlite->prepare("Select id from author where first_name=:f_name and last_name=:l_name;");
		$author_query->bindParam(":f_name", $f_name);	//possible duplicate
		$author_query->bindParam(":l_name", $l_name);
		$author_id = $author_query->execute();
		$auth_id = $author_id->fetchArray();
		//logError($auth_id[0]);
		if (empty($auth_id)){
			$auth_query = $sqlite->prepare("INSERT INTO Author (first_name, last_name) 
				VALUES (:f_name, :l_name);");
			$auth_query->bindParam(':f_name', $f_name);
			$auth_query->bindParam(':l_name', $l_name);
			$auth_query->execute();
			$auth_query = $sqlite->prepare("Select id from author where first_name=:f_name and last_name=:l_name;");
			$author_id = $auth_query->execute();
			$auth_id = $author_id->fetchArray();			
		}
		
	}
	catch (Exception $exception)
	{
		if ($GLOBALS ["sqliteDebug"]) 
		{
			return $exception->getMessage();
		}
		logError($exception);
	}
	return $auth_id["ID"];
}

function updateBook($isbn, $title, $publisher_id, $price, $thumbnail_url, $available, $count)
{
	try 
		{
			$sqlite = new SQLite3($GLOBALS ["databaseFile"]);
			$sqlite->enableExceptions(true);
			
			//prepare query to protect from sql injection
			$query = $sqlite->prepare("INSERT INTO Book (isbn, title, published_by, 
						price, thumbnail_url, available, count) VALUES (:isbn, :title, :published_by,
							:price, :thumbnail_url, :available, :count)");
							
			$query->bindParam(':isbn', $isbn);
			$query->bindParam(':title', $title);
			$query->bindParam(':publisher_id', $publisher_id);
			$query->bindParam(':thumbnail_url', $thumbnail_url);
			$query->bindParam(':price', $price);
			$query->bindParam(':available', $available);
			$query->bindParam(':count', $count);
			$result = $query->execute();
		}
		catch (Exception $exception)
		{
			if ($GLOBALS ["sqliteDebug"]) 
			{
				return $exception->getMessage();
			}
			logError($exception);
	}
	return "TODO";
}

function getBook($isbn)
{
	logError("findorcreate ");
	try
	{
		$sqlite = new SQLite3($GLOBALS["databaseFile"]); 
		
		$sqlite->enableExceptions(true);
		$book_query = $sqlite->prepare("Select * from book where isbn=:isbn;");
		$book_query->bindParam(':isbn', $isbn);	
		//need to get everything out of the dict
		$result = $book_query->execute();
		$book_result = $result->fetchArray();
	}
	catch (Exception $exception)
	{
		if ($GLOBALS ["sqliteDebug"]) 
		{
			return $exception->getMessage();
		}
		logError($exception);
	}
	return $book_result;
}

function getSectionBooks($section_id)
{
	return "TODO";
}

/* Change a book from available to customers to unavailable and vice versa */
function toggleBook($isbn, $isAvailable)
{
    // assert current state is 1 or 0
    $newState = ($isAvailable == 1 ? 0 : 1);
    try
    {
        $sqlite = new SQLite3($GLOBALS ["databaseFile"]);
        $sqlite->enableExceptions(true);

        //prepare query to protect from sql injection
        $query = $sqlite->prepare("UPDATE Book SET available = :newState 
                  WHERE isbn= :isbn");

        $query->bindParam(':isbn', $isbn);
        $query->bindParam(':newState', $newState);
        $result = $query->execute();

        return $result;
    }
    catch (Exception $exception)
    {
        if ($GLOBALS ["sqliteDebug"])
        {
            return $exception->getMessage();
        }
        logError($exception);
    }
}

function orderBook($isbn, $amount)
{
	logError("orderBook ");
	try
    {
        $sqlite = new SQLite3($GLOBALS ["databaseFile"]);
        $sqlite->enableExceptions(true);

        //prepare query to protect from sql injection
		$count_query = $sqlite->prepare("Select count from Book where isbn=:isbn;");
		$count_query->bindParam(':isbn', $isbn);
		$count_result = $count_query->execute();
		$count = $count_result->fetchArray();
		$total = $count[0] + $amount;		
        $query = $sqlite->prepare("UPDATE Book SET count=:total WHERE isbn=:isbn;");
		$query->bindParam(':total', $total);  //update statement not working for some reason
        $result = $query->execute();

        return $total;
    }
    catch (Exception $exception)
    {
        if ($GLOBALS ["sqliteDebug"])
        {
            return $exception->getMessage();
        }
        logError($exception);
    }
	
}


?>
