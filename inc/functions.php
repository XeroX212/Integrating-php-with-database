<?php

function get_catalog_count($category = null) { //optional paramter if category pass to function
    $category = strtolower($category); // make sure if the category pass to the function is always in lower case as we have in our database.
    include("connection.php");

    try {
        $sql = "SELECT COUNT(media_id) FROM Media";
        if (!empty($category)) {
            $result = $db->prepare(
            $sql
            . " WHERE LOWER(category) = ?"
        );
            $result->bindParam(1,$category,PDO::PARAM_STR);
        } else {
            $result = $db->prepare($sql);
        }
        $result->execute();
    } catch (Exception $e) {
        echo "Bad Query";
    }
    $count = $result->fetchColumn(0); // we specify zero for the first column
    return $count;
}

// Include the functions here so we don't repeat our code in future.
function full_catalog_array($limit = null, $offset = 0) {
    include("connection.php");

    try {
        $sql = "SELECT media_id, title, category, img 
        FROM media
        ORDER BY REPLACE(
                 REPLACE(
                 REPLACE(title, 'The ', ''),
                 'An ', ''
                 ),
                 'A ', ''
                 )";
            if (is_integer($limit)){
                $results = $db->prepare($sql . " LIMIT ? OFFSET ?");
                $results->bindParam(1,$limit,PDO::PARAM_INT);
                $results->bindParam(2,$offset,PDO::PARAM_INT);
                $results->execute();
            } else {
                $results = $db->query($sql);
             } //Try to run the query to get the results
    } catch (Exception $e) {
        echo "Unable to Retrived the Data";
        exit;
    }
    $catalog = $results->fetchAll();
    return $catalog;
}

function category_catalog_array($category, $limit = null, $offset = 0) {
    include("connection.php");
    $category = strtolower($category);
    try {
        $sql = "SELECT media_id, title, category, img 
            FROM media
            WHERE LOWER(category) = ?
            ORDER BY REPLACE(
                     REPLACE(
                     REPLACE(title, 'The ', ''),
                     'An ', ''
                     ),
                     'A ', ''
                     )";
        if (is_integer($limit)){
            $results = $db->prepare($sql . " LIMIT ? OFFSET ?");
            $results->bindParam(1,$category,PDO::PARAM_STR);
            $results->bindParam(2,$limit,PDO::PARAM_INT);
            $results->bindParam(3,$offset,PDO::PARAM_INT);
        } else {
            $results = $db->prepare($sql);//Try to run the query to get the results
            $results->bindParam(1,$category,PDO::PARAM_STR);
        }
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to Retrived the Data";
        exit;
    }

    $catalog = $results->fetchAll();
    return $catalog;
}

function random_catalog_array() {
    include("connection.php");

    try {
        $results = $db->query(
            "SELECT media_id, title, category, img 
            FROM media
            ORDER BY RAND()
            LIMIT 4"); //Try to run the query to get the results
    } catch (Exception $e) {
        echo "Unable to Retrived the Data By Random Sir";
        exit;
    }
    $catalog = $results->fetchAll();
    return $catalog;
}


function single_item_array($id) {
    include("connection.php");

    try {
        $results = $db->prepare(
            "SELECT title, category, img, format, year, genre, publisher, isbn 
            FROM media JOIN genres ON media.genre_id = genres.genre_id
            LEFT OUTER JOIN books ON media.media_id = books.media_id 
            WHERE media.media_id = ?"
        ); //Try to run the query to get the result for single item using JOIN SQL syntax
        $results->bindParam(1,$id,PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to Retrived the Data";
        exit;
    }

    $item = $results->fetch();
    if (empty($item)) return $item;
    try {
        $results = $db->prepare(
            "SELECT fullname, role
            FROM Media_People 
            JOIN People ON Media_People.people_id = People.people_id
            WHERE Media_People.media_id = ?"
        ); //Try to run the query to get the result for single item using JOIN SQL syntax
        $results->bindParam(1,$id,PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to Retrived the Data";
        exit;
    }
    while($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $item[$row["role"]][] = $row["fullname"];
    } 
    return $item;
}


function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item["media_id"] . "'><img src='" 
        . $item["img"] . "' alt='" 
        . $item["title"] . "' />" 
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}

function array_category($catalog,$category) { //Sorting Out the Categories
    $output = array();
    
    foreach ($catalog as $id => $item) {
        if ($category == null OR strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort,"The ");
            $sort = ltrim($sort,"A ");
            $sort = ltrim($sort,"An ");
            $output[$id] = $sort;            
        }
    }
    
    asort($output);
    return array_keys($output);
}