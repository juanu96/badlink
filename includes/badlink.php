<?php

function bad_link_tracking()
{
    add_menu_page(
        'Bad Links Trackign',
        'Bad Links Trackign',
        'edit_pages',
        'bad-link',
        'contentdisplay',
        'dashicons-admin-links',
        1  // create before Dashboard menu item
    );
}

function contentdisplay()
{
    echo '<div class="wrap">';
    echo '<h2>Table Example</h2>';
    new Bad_Links_List_Table();
    echo '</div>';
}

function badlinkstracking()
{
    $data = array();
    //get all post for search bad links       
    $posts = get_posts();
    foreach ($posts as $key => $value) {
        $datameta = array();
        //We check if you have broken links
        $listbadlinks = get_post_meta($value->ID, 'BAD_LINKS', true);
        if ($listbadlinks) {
            foreach ($listbadlinks as $value) {
                array_push($data, $value);
            }
        } else {
            // we get all links in post_content
            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $value->post_content, $result);
            $datahref = $result['href'];
            //we get post_title
            $name = $value->post_title;
            //we get the link from this post
            $link = get_permalink($value->ID);
            $status = "";
            //we show the post where the broken link was found
            $origin = "<a href='" . $link . "'>" . $name . "</a>";
            $brokenurl = "";
            foreach ($datahref as $url) {
                //we parse the url to get its components
                $dataurl = parse_url($url);
                $item['url'] = $link;
                if (!isset($dataurl['scheme'])) {
                    //if the url has no scheme like http://
                    $status = "<p style='color:orange'>Protocolo no especificado</p>";
                    $brokenurl = $url;
                } else if (isset($dataurl['scheme']) && $dataurl['scheme'] === 'http') {
                    //if the url has http://
                    $status = "<p style='color:orange'>Enlace inseguro</p>";
                    $brokenurl = $url;
                } else if (isset($dataurl['scheme']) && $dataurl['scheme'] === 'https') {
                    //if the url has any problem from the pathname or response code
                    $status = get_headers($url, 1)[0];
                    preg_match('/\s(\d+)\s/', $status, $matches);
                    //we validate that the response code is not 20x
                    in_array($matches[0], range(200, 299)) ? $brokenurl = "" : $brokenurl = $url;
                }

                if ($brokenurl !== "") {
                    //if the url has any problem from the pathname or response code
                    $item = array('ID' => $key, 'url' => $brokenurl, 'estado' => $status, 'origen' => $origin);
                    array_push($datameta, $item);
                    array_push($data, $item);
                }
            }
            //we save the broken links in the post meta
            add_post_meta($value->ID, 'BAD_LINKS', $datameta);
        }
    }
    return $data;
}

// Extending class
class Bad_Links_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'bad link',
            'plural'   => 'bad links',
            'ajax'     => true
        ));
        // Prepare table
        $this->prepare_items();
        // Display table
        $this->display();
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
            'url' => 'URL',
            'estado' => 'Estado',
            'origen' => 'Origen'
        );
        return $columns;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'url':
            case 'estado':
            case 'origen':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'url' => array('url', false),
            'estado' => array('estado', false),
            'origen'  => array('origen', false)
        );
        return $sortable_columns;
    }

    // Bind table with columns, data and all
    function prepare_items()
    {
        $data = badlinkstracking();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
}
