<?php

// Switchboard to Book Store Functions
function book_store_switch($getFunctions)
{
	// Define the possible Book Store function URLs which the page can be accessed from
	$possible_function_url = array("getBook", "getSectionBooks", "createBook", "findOrCreatePublisher", "toggleBook",
		"orderBook", "findOrCreateAuthor", "viewBookReviews", "updateBook", "searchBooks", "createReview", 
		"viewPurchaseHistory", "purchaseBook", "getAllBooks");

	if ($getFunctions)
	{
		return $possible_function_url;
	}
		
	if (isset($_GET["function"]) && in_array($_GET["function"], $possible_function_url))
	{
		switch ($_GET["function"])
		{
			case "createBook":
				if (isset($_POST["publisher_name"])){
					$pid = findOrCreatePublisher($_POST["publisher_name"], $_POST["address"], $_POST["website"]);
					$aid = findOrCreateAuthor($_POST["f_name"], $_POST["l_name"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("findOrCreatePublisher ~ Required parameters were not submited correctly.");
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
/* 					if(getBook($_POST["isbn"])){
						return ("The book with the isbn already exists.");
					} else { */
						//Book with the isbn does not yet exist so go
						//ahead and create it.
						return createBook(
							$_POST["isbn"],
							$_POST["title"],
							$pid,
							$_POST["price"],
							$_POST["thumbnail_url"],
							$_POST["available"],
							$_POST["count"]
							);
					//	}
					}
				else{
					header("HTTP/1.1 400");
					logError("createBook ~ Required parameters were not submited correctly.");
					return ("createBook One or more parameters were not provided");
				}
				
			case "findOrCreatePublisher":
				logError("log or create pub case");
				if (isset($_POST["publisher_name"])){
					$pid = findOrCreatePublisher($_POST["publisher_name"], $_POST["address"], $_POST["website"]);
					return $pid;
				}
				else{
					// TODO: handle this error
					header("HTTP/1.1 400");
				}
				
			case "findOrCreateAuthor":
				logError("log or create author case");
				if (isset($_POST["first_name"]) && isset($_POST["last_name"])){
					$aid = findorcreateAuthor($_POST["first_name"], $_POST["last_name"]);
					return $aid;
				}
				else{
					header("HTTP/1.1 400");
					logError("findOrCreateAuthor ~ Required parameters were not submitted correctly.");
					return ("findOrCreateAuthor One or more parameters were not provided");
				}
				
			case "updateBook":
				if (isset($_POST["publisher_name"])){
					$pid = findOrCreatePublisher($_POST["publisher_name"], $_POST["address"], $_POST["website"]);
					$aid = findOrCreateAuthor($_POST["f_name"], $_POST["l_name"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("updateBook ~ Required parameters were not submited correctly.");
					return ("updateBook ~ One or more parameters were not provided");
				}
				if (isset($_POST["isbn"]) &&
					isset($_POST["title"]) &&
					isset($_POST["price"]) &&
					isset($_POST["thumbnail_url"]) &&
					isset($_POST["available"]) &&
					isset($_POST["count"])
				)
					{
					return updateBook(
						$_POST["isbn"],
						$_POST["title"],
						$pid,
						$_POST["price"],
						$_POST["thumbnail_url"],
						$_POST["available"],
						$_POST["count"]
						);
					}
				else {
					header("HTTP/1.1 400");
					logError("updateBook ~ Required parameters were not submited correctly.");
					return ("updateBook One or more parameters were not provided");
				}
				
			case "getBook":
				//if has params
				if (isset($_GET["isbn"]) && $_GET["isbn"] >= 0){
					return getBook($_GET["isbn"]);
				} else {
					header("HTTP/1.1 400");
					logError("getBook ~ Required isbn parameter was not submitted correctly.");
					return ("getBook book isbn parameter was not submitted correctly.");
				}
				
			case "getSectionBooks":
				//if has params
				if (isset($_GET["section_id"])){
					return getSectionBooks($_GET["section_id"]);
				} else {
					header("HTTP/1.1 400");
					logError("getBook ~ Required isbn parameter was not submitted correctly.");
					return ("getBook book isbn parameter was not submitted correctly.");
				}
				
			case "toggleBook":
				if (isset($_GET["isbn"]) && isset($_GET["available"]))
				{
					return toggleBook($_GET["isbn"], $_GET["available"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("getBook ~ Required isbn and-or available parameter not submitted correctly.");
					return ("toggleBook isbn and-or available parameter not submitted correctly.");
				}
				
			case "createReview":
				if (isset($_POST["id"]) &&
					isset($_POST["review"]) &&
					isset($_POST["rating"]) &&
					isset($_POST["book_isbn"]) &&
					isset($_POST["user_id"]))
					{
					return createReview(
						$_POST["id"],
						$_POST["review"],
						$_POST["rating"],
						$_POST["book_isbn"],
						$_POST["user_id"]);
				} else {
					header("HTTP/1.1 400");
					logError("createReview ~ Required parameters not submitted correctly.");
					return ("createReview parameters not submitted correctly.");
				}
				
			case "orderBook":
                if (isset($_GET["isbn"]) && isset($_GET["amount"]))
                {
                    return orderBook($_GET["isbn"], $_GET["amount"]);
                }
                else{
					header("HTTP/1.1 400");
                    logError("orderBook ~ Required isbn and-or amount parameter not submitted correctly.");
                    return ("orderBook isbn and-or amount parameter not submitted correctly.");
                }
				
			case "viewBookReviews":
				if (isset($_GET["isbn"])){
					return viewBookReviews($_GET["isbn"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("viewBookReviews ~ Required isbn parameter not submitted correctly.");
                    return ("viewBookReviews isbn parameter not submitted correctly.");
				}

			case "searchBooks":
				if(isset($_GET["search_attribute"])){
					return searchBooks($_GET["search_attribute"], $_GET["search_string"]);
				} else {
					header("HTTP/1.1 400");
					logError("searchBooks ~ Required search_attribute parameter not submitted correctly.");
                    return ("searchBooks search_attribute parameter not submitted correctly.");
				}
				
			case "viewPurchaseHistory":
				if (isset($_GET["user_id"])){
					return viewPurchaseHistory($_GET["user_id"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("viewPurchaseHistory ~ Required user_id parameter not submitted correctly.");
                    return ("viewPurchaseHistory user_id parameter not submitted correctly.");
				}
			
			case "purchaseBook":
				if (isset($_GET["isbn"]) && ($_GET["user_id"]) && ($_GET["price"])){
					return purchaseBook($_GET["isbn"], $_GET["user_id"], $_GET["price"]);
				}
				else{
					header("HTTP/1.1 400");
					logError("purchaseBook ~ Required isbn and/or user_id parameter not submitted correctly.");
					return ("purchaseBook isbn or user_id parameter not submitted correctly.");
				}
				
			case "getAllBooks":
				return getAllBooks();
			
			default:
				header("HTTP/1.1 404");
				logError("Your query of function: " + $_GET["function"] + " is incorrect.  It may be structured wrong or the function
					does not exits");
				return "Your query of function: " + $_GET["function"] + " is incorrect.  It may be structured wrong or the function
					does not exits";
		}
	}
}

?>