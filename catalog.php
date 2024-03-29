<?php 
include("inc/functions.php");

$pageTitle = "Full Catalog";
$section = null;
$search = null;
$items_per_page = 8; //[1]. Number of items per page is something we need to define first

if (isset($_GET["cat"])) {
    if ($_GET["cat"] == "books") {
        $pageTitle = "Books";
        $section = "books";
    } else if ($_GET["cat"] == "movies") {
        $pageTitle = "Movies";
        $section = "movies";
    } else if ($_GET["cat"] == "music") {
        $pageTitle = "Music";
        $section = "music";
    }
}

if (isset($_GET["s"])) { //Getting the search data using get method from form in header.php file
    $search = filter_input(INPUT_GET, "s",FILTER_SANITIZE_STRING);
}

if (isset($_GET["pg"])) { //[2]. Pass page number using get variable
    $current_page = filter_input(INPUT_GET, "pg",FILTER_SANITIZE_NUMBER_INT); //[3]. Filter the int input of variable current_page set after getting the get value which is number. 
}

if (empty($current_page)) {

    $current_page = 1; //[4]. if current page is not set we always want to set the current_page variable to number 1 value.
    //last thing we need to know up front is that how many items are in total, create funtion for that in functions.php file.
}

//[5]. Last once we create our count function we need to set the variable total_items.
$total_items = get_catalog_count($section, $search); // if section vraible is not set it will pass null as well, So it's safe to pass section variable no matter what.
$total_pages = 1; //set default if total_items result in less then 1
$offset = 0; // set default
if ($total_items > 0 ) {
    //[6]. Once basic variables are setup need to do couple calculations for pagination. First we want to total number of pages by dividing total_items variable with items_per_page variable.
    $total_pages = ceil($total_items / $items_per_page); //we want this to return next highest int value, rounding up value if necessary, there is built in function to do this which is ceil.

    //limit results in redirect, if we are on category page
    $limit_results = ""; //By default we don't want to limit our results.
    if (!empty($search)) {
        $limit_results = "s=".urlencode(htmlspecialchars($search))."&";
    } else if (!empty($section)) {
        $limit_results = "cat=" . $section . "&"; //& sign helps to add the page number as well.
    }

    //redirect too-large page numbers to the last page
    if ($current_page > $total_pages) {
        header("location:catalog.php?" 
        . $limit_results
        . "pg="
        . $total_pages
        );
    }
    //redirect too-small page numbers to the first page
    if ($current_page < 1) {
        header("location:catalog.php?"
        .$limit_results
        ."pg=1");
    }

    //determine the offset (number of items to skip) for the current page
    // for example: on page 3 with 8 items per page, the offset would be 16
    $offset = ($current_page - 1) * $items_per_page;

    $pagination = "<div class=\"pagination\">";
    $pagination .= "Pages: ";     
    for ($i = 1;$i <= $total_pages;$i++) {
        if ($i == $current_page) {
        $pagination .= " <span>$i</span>";
        }  else {
        $pagination .= " <a href='catalog.php?";
        if (!empty($search)) {
            $pagination .= "s=".htmlspecialchars($search)."&";
        } else if (!empty($section)) {
            $pagination .= "cat=".$section."&";
        }
            $pagination .= "pg=$i'>$i</a>";
        }            
    }
    $pagination .= "</div>";

} // if out total items are less then 1 will will skip that entier block of code

//On a first page we don't want to skip any items, if we have less items left it will show only the remaining items.
// For example: if we have 15 items and we show 10 per page, on the second page we would only see 5 items.


if (!empty($search)) { // Use Search function from functions.php if search is not empty
    $catalog = search_catalog_array($search,$item_per_page,$offset);
} else if (empty($section)) {
    $catalog = full_catalog_array($items_per_page, $offset);
} else {
    $catalog = category_catalog_array($section, $items_per_page, $offset);
}

include("inc/header.php"); ?>

<div class="section catalog page">
    
    <div class="wrapper">
        
        <h1><?php 
        if ($search != null) { // Change h1 Title if there is search detected
            echo "Search Results for \"".htmlspecialchars($search)."\"";
        } else {
            if ($section != null) {
                echo "<a href='catalog.php'>Full Catalog</a> &gt; ";
            }
        echo $pageTitle;
        }?></h1>
        <?php 
        if ($total_items < 1) { // for search results to show message if there is nothing in the search
            echo "<p>No items were found matching that search term.</p>";
            echo "<p>Search Again or "
              . "<a href=\"catalog.php\">Browse the Full Catalog</a></p>";
        } else { 
        echo $pagination; ?>
        <ul class="items">
            <?php
            foreach ($catalog as $item) {
                echo get_item_html($item);
            }
            ?>
        </ul>
        <?php echo $pagination; 
        } ?>
    </div>
</div>

<?php include("inc/footer.php"); ?>