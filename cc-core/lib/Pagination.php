<?php

class Pagination {

    private $url;
    private $total;
    private $records_per_page;
    private $page_count;
    private $page_limit;
    private $base = null;
    private $end = null;
    public $page;


    /**
     * Instantiate pagination object
     * @param string $url The URL for the current page which will be paginated
     * @param integer $total Total number of records
     * @param integer $records_per_page Number of records to display per page
     * @param boolean $seo_friendly_url [optional] Whether to make page parameter
     * SEO friendly. Default true produces this type of URL: http://cumulusclips.org/path/page/1/,
     * boolean false produces this type of URL: http://cumulusclips.org/path/?page=1
     * @return void Pagination object is created
     */
    public function  __construct ($url, $total, $records_per_page, $seo_friendly_url = true) {
        $this->url = parse_url ($url);
        $this->seo_friendly_url = $seo_friendly_url;
        $this->total = $total;
        $this->records_per_page = $records_per_page;
        $this->page_limit = 9;
        $this->page_count = ceil ($this->total/$this->records_per_page);
        $this->page = $this->GetPage();
    }




    /**
     * Output paginated links
     * @return mixed Returns the pagination block with links
     */
    public function Paginate() {
        if ($this->total <= $this->records_per_page) return '';
        $links = $this->GetLinks();
        $previous = $this->GetPrevious();
        $first = $this->GetFirst();
        $last = $this->GetLast();
        $next = $this->GetNext();
        return '<ul id="pagination">' . $previous . $first . $links . $last . $next . '</ul>';
    }




    /**
     * Retrieve the current page in the pagination
     * @return integer Returns the current pagination page
     */
    private function GetPage() {
        $page = isset ($_GET['page']) && is_numeric ($_GET['page']) && $_GET['page'] > 1  ? $_GET['page'] : 1;
        return ($page > $this->page_count) ? 1 : $page;
    }




    // Retrieve start and end pages for range
    private function GetRange() {
        if ($this->page < $this->page_limit) {
            return array (1, $this->page_limit);
        } else {
            $half = floor ($this->page_limit/2);
            return array ($this->page-$half, $this->page+$half);
        }
    }




    private function GetLinks() {

        // Determine how many pages to display
        if ($this->page_count > $this->page_limit) {

            if ($this->page <= $this->page_limit) {

                // Page is in beginning of list
                $this->base = true;
                $start_range = 1;
                $end_range = $this->page_limit;

            } else if ($this->page >= $this->page_count - $this->page_limit) {

                // Page is in beginning of list
                $this->end = true;
                $start_range = $this->page_count - $this->page_limit;
                $end_range = $this->page_count;

            } else {
                list ($start_range, $end_range) = $this->GetRange();
            }

        } else {
            $this->end = true;
            $this->base = true;
            $start_range = 1;
            $end_range = $this->page_count;
        }


        // Display pages
        $links = '';
        for ($x = $start_range; $x <= $end_range; $x++) {
            $links .= $this->page == $x ? '<li><strong>' . $x . '</strong></li>' : '<li><a href="' . $this->BuildURL($x) . '">' . $x . '</a></li>';
        }
        return $links;

    }




    // Retrieve Previous link
    private function GetPrevious() {
        return ($this->page != 1) ? '<li><a href="' . $this->BuildURL($this->page-1) . '">&laquo;' . Language::GetText('previous') . '</a></li>' : '';
    }




    // Retrieve Next link
    private function GetNext() {
        return ($this->page != $this->page_count) ? '<li><a href="' . $this->BuildURL($this->page+1) . '">' . Language::GetText('next') . '&raquo;</a></li>' : '';
    }




    // Retrieve First two series links
    private function GetFirst() {
        if (!$this->base) {
            $first = '<li><a href="' . $this->BuildURL(1) . '">1</a></li>';
            $first .= '<li><a href="' . $this->BuildURL(2) . '">2</a></li>';
            $first .= '<li>...</li>';
            return $first;
        } else {
            return '';
        }
    }




    // Retrieve Last two series links
    private function GetLast() {
        if (!$this->end) {
            $last = '<li>...</li>';
            $last .= '<li><a href="' . $this->BuildURL($this->page_count-1) . '">' . ($this->page_count-1) . '</a></li>';
            $last .= '<li><a href="' . $this->BuildURL($this->page_count) . '">' . $this->page_count . '</a></li>';
            return $last;
        } else {
            return '';
        }
    }




    // Retrieve Starting record
    public function GetStartRecord() {
        return ($this->page - 1) * $this->records_per_page;
    }




    /**
     * Return a paginated URL based on current URL with page and additional query parameters
     * @param integer $page [optional] Page to add to the URL
     * @param string $additional_query [optional] Additional query to add to the URL
     * @return string Returns paginated URL with page and query string
     */
    private function BuildURL ($page = null, $additional_query = null) {

        $url = $this->url;

        // Append page number if needed
        if (!empty ($page) && $page > 1) {
            if ($this->seo_friendly_url) {
                $url['path'] .= '/page/' . $page;
            } else {
                if (isset ($url['query'])) {
                    $url['query'] .= '&page=' . $page;
                } else {
                    $url['query'] = 'page=' . $page;
                }
            }
        }


        // Append additional query string if provided
        if ($additional_query) {
            if (isset ($url['query'])) {
                $url['query'] .= '&' . $additional_query;
            } else {
                $url['query'] = $additional_query;
            }
        }


        // Build url string from parts
        $string = $url['scheme'] . '://';
        $string .= $url['host'];
        $string .= isset($url['port']) ? ':' . $url['port'] : '';
        $string .= $url['path'] . ($this->seo_friendly_url ? '/' : '');
        $string .= isset ($url['query']) ?  '?' . $url['query'] : '';
        return $string;

    }




    /**
     * Retrieve the current paginated URL
     * @param string $addtional_query [optional] Append additional query string to URL
     * @return string Returns current paginated URL with query appended (if any)
     */
    public function GetURL ($additional_query = null) {
        return $this->BuildURL ($this->GetPage(), $additional_query);
    }

    /**
     * Determine whether pagination is to be displayed
     * @return boolean Returns true if pagination is to be displayed, false otherwise
     */
    public function displayPagination()
    {
        return ($this->total > 0 && $this->total > $this->records_per_page);
    }
}