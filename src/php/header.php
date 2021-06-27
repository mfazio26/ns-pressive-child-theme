<?php 

/**
 * header.php
 * Derived from themes/pressive/header.php
 *
 * This file serves a very special purpose. Thrive Themes has "defined hooks" for `tha_header_top`/etc, 
 * unfortunately, they aren't calling them anywhere! To insert additional content/components into the 
 * header we have to opt for a less-than-ideal solution.
 *
 * Basically, what we are doing here is loading Thrive Themes' `header.php` into the output buffer so 
 * we can use it as a variable. We are then creating a `DOMDocument` from it so that we can parse it as 
 * an XML structure, create additional "nodes", and add those "nodes" where we want them.
 *
 * Please note, we have to be VERY careful when doing this because a `DOMDocument` is a literal complete 
 * HTML document, and our `header.php` is only a partial (and invalid) HTML document. Our `header.php` 
 * file opens the `<html>` and `<body>` tag, but it doesn't close them. The `DOMDocument` will automatically 
 * close those tags...in possibly unexpected places. So, we have to close the tags ahead of time to create 
 * a valid document, and then strip them from the output to return out `header.php` file to its state of 
 * representing a "partial" file.
 *
 * The four elements we are adding are...
 * - The "social follow" component at the very top of the header.
 * - The "notsalmon tagline" at the bottom of the header.
 * - A mobile-only search bar.
 * - And adding a "title" attribute to the logo <img>s.
 * 
 */

// capture Thrive's header to a variable via output buffer
ob_start();
include get_template_directory() . "/header.php";
$content = ob_get_contents();
ob_end_clean();
	
// disable HTML5 and mismatched parser errors
libxml_use_internal_errors(true);

// create an html document from Thrive's header template. Please note, we are adding the closing `body` and `html` tags so 
// that the document is fully valid. we are going to be removing these later because they are actually closed in the footer 
// template, and we don't want to double these up!
$doc = new DOMDocument();
$doc->validateOnParse = false;
$doc->loadHTML(mb_convert_encoding($content . "</body></html>", 'HTML-ENTITIES', 'UTF-8'));

// re-enabled HTML and mismatched parser errors!
libxml_use_internal_errors(false);

// capture the logo wrapper element where we will be adding our extra elements
$wrapper = $doc->getElementById("logo")->parentNode;


// find all the <img> tags that are decendants of the id="logo" node and add a custom title attribute to each.
foreach ($doc->getElementById("logo")->childNodes as $node){
	if (($node->nodeType == 1) && ($node->tagName == "a")) {
		foreach ($node->childNodes as $child){
			if (($child->nodeType == 1) && ($child->tagName == "img")) {
				$child->setAttribute("title", esc_attr("My name is pronounced, Karen Saal•maan•son."));
			}
		}
	}
}

// capture header and floating menu nodes
$header = $doc->getElementsByTagName("header")->item(0);
$floating_menu = $doc->getElementById("floating_menu");

// create and append a tagline element
$tagline = $doc->createElement("span", get_bloginfo("description"));
$tagline->setAttribute("class", "ns-header-tagline");
$wrapper->appendChild($tagline);

// social follow string (matches the footer widget)
$follow_str =  "<p class=\"ns-social-follow__heading\">Follow notsalmon.com</p>" . 
		   	   "<p class=\"ns-social-follow__subheading\">Over 1.5 Million Fans</p>" . 
		   	   do_shortcode("[et_social_follow icon_style=\"simple\" icon_shape=\"rectangle\" icons_location=\"top\" col_number=\"6\" spacing=\"true\" custom_colors=\"true\" bg_color=\"\" bg_color_hover=\"\" icon_color=\"#393e41\" icon_color_hover=\"#ab0372\" outer_color=\"dark\"]");

// create an element from the `$follow_str`
$follow = $doc->createElement("aside");
$follow->setAttribute("class", "ns-social-follow ns-social-follow--header");

// make a new html document with only the $follow_str
$follow_doc = new DOMDocument();
$follow_doc->loadHTML($follow_str);

// loop through all the child nodes in the `$follow_doc` body...
foreach ($follow_doc->getElementsByTagName("body")->item(0)->childNodes as $node){
	// import child node into the `$doc`
	$node = $follow->ownerDocument->importNode($node, true);
	// append the node into the `$follow` element
	$follow->appendChild($node);
}



// mobile search string
$mobisearch_str =	"<form class=\"ns-header-mobisearch-form\" action=\"/\" method=\"get\">" .
						"<input class=\"ns-header-mobisearch-form__input\" type=\"text\" name=\"s\" placeholder=\"Search...\" />" . 
						"<button class=\"ns-header-mobisearch-form__btn search-button\" type=\"submit\">" . 
							"<span class=\"sr-only\">Search NotSalmon.com</span>" .
						"</button>" .
					"</form>";

// create an element from the `$mobisearch_str`
$mobisearch = $doc->createElement("aside");
$mobisearch->setAttribute("class", "ns-header-mobisearch");

// make a new html document with only the `$mobisearch_str`
$mobisearch_doc = new DOMDocument();
$mobisearch_doc->loadHTML($mobisearch_str);

// loop through all the child nodes in the `$mobisearch_doc` body...
foreach ($mobisearch_doc->getElementsByTagName("body")->item(0)->childNodes as $node){
	// import child node into the `$doc`
	$node = $mobisearch->ownerDocument->importNode($node, true);
	// append the node into the `$mobisearch` element
	$mobisearch->appendChild($node);
}


// now we can insert `$follow` and `$mobisearch` elements into the `$wrapper` at the top!
$header->insertBefore($follow, $header->firstChild);
$floating_menu->insertBefore($mobisearch, $floating_menu->firstChild);


// finally we have to strip the last occurences of `</body>` and `</html>` because those are closed in other templates!
$output = preg_replace("/<\/body>/i", "", $doc->saveHTML());
$output = preg_replace("/<\/html>/i", "", $output);
echo $output;
