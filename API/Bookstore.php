<?php

require ("BookStoreSwitch.php");

function searchBooks($attribute, $search_key)
{
	logError("searchBooks ");
	try
		{
			$sqlite = new SQLite3($GLOBALS["databaseFile"]);
			$sqlite->enableExceptions(true);

			//prepare query to protect from sql injection for appropriate query
			if(($attribute == "isbn") || ($attribute == "available") || ($attribute == "title") || ($attribute == "thumbnail_url")) {
				$search_query = $sqlite->prepare("Select * from book where $attribute LIKE :search_key_placeholder;");
				$search_key = "%" . $search_key . "%" ;
				$search_query->bindParam(':search_key_placeholder', $search_key);
			} else {
				echo "INVALID ARGUMENTS FOR SEARCHING BOOKS";
			}

			$result = $search_query->execute();
			$matchingBooks = array();
			// get all the rows until none are left to fetch
			while ( $row = $result->fetchArray() )
			{
				// Add sql row to our final result
				array_push($matchingBooks, $row);
			}
			return $matchingBooks;
	}
	catch (Exception $exception) { handleException($exception); }
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

			header("HTTP/1.1 201 Book Created");
			return $result;
	}
	catch (Exception $exception) { handleException($exception); }
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
	catch (Exception $exception) { handleException($exception); }

	return $pub_id["ID"];
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
			header("HTTP/1.1 201 Author Created");
			return $auth_id["ID"];
		}
		else
		{
			header("HTTP/1.1 201 Author Found");
			return $auth_id["ID"];
		}
	}
	catch (Exception $exception) { handleException($exception); }
}

function updateBook($isbn, $title, $publisher_id, $price, $thumbnail_url, $available, $count)
{
	logError("updateBook ");
	try
		{
			$sqlite = new SQLite3($GLOBALS["databaseFile"]);
			$sqlite->enableExceptions(true);

			$update_book_query = $sqlite->prepare("UPDATE Book SET title=:title, publisher_id=:publisher_id, price=:price, thumbnail_url=:thumbnail_url, available=:available, count=:count WHERE isbn=:isbn");

			$update_book_query->bindParam(':isbn', $isbn);
			$update_book_query->bindParam(':title', $title);
			$update_book_query->bindParam(':publisher_id', $publisher_id);
			$update_book_query->bindParam(':thumbnail_url', $thumbnail_url);
			$update_book_query->bindParam(':price', $price);
			$update_book_query->bindParam(':available', $available);
			$update_book_query->bindParam(':count', $count);

			$result = $update_book_query->execute();
			//$book_result = $result->fetchArray();

			return $result; //keep in mind that this wont actually return any data since its an update query -Daniel Roberts
		}
	catch (Exception $exception) { handleException($exception); }
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

		return $book_result;
	}
	catch (Exception $exception) { handleException($exception); }
}

function getSectionBooks($section_id)
{
	try
	{
		$sqlite = new SQLite3($GLOBALS["databaseFile"]);

		$sqlite->enableExceptions(true);
		$section_query = $sqlite->prepare("Select * from Sectionbook where section_id=
			:section_id;");
		$section_query->bindParam(':section_id', $section_id);
		$result = $section_query->execute();

		$all_section_books = array();

		while ($row = $result->fetchArray())
		   {
			   array_push($all_section_books, $row);
		   }

		   return $all_section_books;
   }
   catch (Exception $exception) { handleException($exception); }

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
    catch (Exception $exception) { handleException($exception); }
}
function createReview($review, $rating, $book_isbn, $user_id)
{
	logError("createReview");
	try{
		$sqlite = new SQLite3($GLOBALS["databaseFile"]);
		$sqlite->enableExceptions(true);
		
		//prepare query to protect from sql injection
		$query = $sqlite->prepare("INSERT INTO BookReview (review, rating,
					book_isbn, user_id) VALUES (:review, :rating,
					:book_isbn, :user_id)");
		$query->bindParam(':review', $review);
		$query->bindParam(':rating', $rating);
		$query->bindParam(':book_isbn', $book_isbn);
		$query->bindParam(':user_id', $user_id);
		
		$result = $query->execute();
		
		header("HTTP/1.1 201 Book Review Created");
		
		
		return $result;
	}
	catch (Exception $exception) { 
		handleException($exception); 
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
		$total = $count["COUNT"] + $amount;
        $query = $sqlite->prepare("UPDATE Book SET count=:total WHERE isbn=:isbn;");
		$query->bindParam(':isbn', $isbn);
		$query->bindParam(':total', $total);  //update statement not working for some reason
        $result = $query->execute();

        return $total;
    }
    catch (Exception $exception) { handleException($exception); }

}

function viewBookReviews($isbn){
		logError("viewBookReviews ");
	try
    {
        $sqlite = new SQLite3($GLOBALS ["databaseFile"]);
        $sqlite->enableExceptions(true);

        //prepare query to protect from sql injection
		$query = $sqlite->prepare("Select * from BookReview where book_isbn=:isbn;");
		$query->bindParam(':isbn', $isbn);
		$result = $query->execute();
		
		$reviews = array();

		// get all the rows until none are left to fetch
		while ( $row = $result->fetchArray() )
		{
			// Add sql row to our final result
			array_push($reviews, $row);
		}
		
		header("HTTP/1.1 200");
        return $reviews;
    }
    catch (Exception $exception) { handleException($exception); }

}

function viewPurchaseHistory($user_id)
{
	try
    {
        $sqlite = new SQLite3($GLOBALS ["databaseFile"]);
        $sqlite->enableExceptions(true);

        //prepare query to protect from sql injection
		// !!TODO: join to get the author
		$query = $sqlite->prepare("SELECT BookOrder.subtotal, b.title, b.price
			FROM BookOrder
			JOIN OrderItem as o on BookOrder.id = o.order_id
			JOIN Book as b on o.book_isbn=b.isbn
			WHERE BookOrder.user_id=:user_id;");
		$query->bindParam(':user_id', $user_id);
		$result = $query->execute();

		$bookOrders = array();

		// get all the rows until none are left to fetch
		while ( $row = $result->fetchArray() )
		{
			// Add sql row to our final result
			array_push($bookOrders, $row);
		}
		return $bookOrders;
    }
    catch (Exception $exception) { handleException($exception); }
}


function purchaseBook($isbn, $user_id, $price){
	try
	{
		$sqlite = new SQLite3($GLOBALS ["databaseFile"]);
		$sqlite->enableExceptions(true);

        //prepare query to protect from sql injection
		$count_query = $sqlite->prepare("Select count from Book where isbn=:isbn;");
		$count_query->bindParam(':isbn', $isbn);
		$count_result = $count_query->execute();
		$count = $count_result->fetchArray();
		$total = $count["COUNT"] - 1;

        $query = $sqlite->prepare("UPDATE Book SET count=:total WHERE isbn=:isbn;");
		$query->bindParam(':isbn', $isbn);
		$query->bindParam(':total', $total);  //update statement not working for some r
        $result = $query->execute();

	   $hist_query = $sqlite->prepare("Insert into BookOrder (subtotal, user_id)
			VALUES (:price, :user_id);");
		$hist_query->bindParam(':price', $price);
		$hist_query->bindParam(':user_id', $user_id);
        $result = $hist_query->execute();

		$item_query = $sqlite->prepare("Insert into OrderItem (order_id, Book_isbn)
			VALUES (:order_id, :isbn);");
	   $order_id = 1;
		$item_query->bindParam(':order_id', $order_id);
		$item_query->bindParam(':isbn', $isbn);
        $result = $item_query->execute();

        return $result;
    }
    catch (Exception $exception) { handleException($exception); }

}

function getAllBooks(){
	try
	{
		$sqlite = new SQLite3($GLOBALS ["databaseFile"]);
		$sqlite->enableExceptions(true);

		$query = $sqlite->prepare("SELECT * FROM Book;");
		$result = $query->execute();

		$allBooks = array();
		// get all the rows until none are left to fetch
		while ( $row = $result->fetchArray() )
		{
			// Add sql row to our final result
			array_push($allBooks, $row);
		}
		header("HTTP/1.1 200 Books Found");
		return $allBooks;
    }
    catch (Exception $exception) { handleException($exception); }

}

function handleException(Exception $exception){
	 if ($GLOBALS ["sqliteDebug"])
        {
        
          header("HTTP/1.1 400 Exception Occured");  
 	  return $exception->getMessage();
        }
        logError($exception);
}

?>
