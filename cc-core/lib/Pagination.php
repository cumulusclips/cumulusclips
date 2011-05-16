<?php

class Pagination {

    private $url;
    private $query_string;
    private $total;
    private $records_per_page;
    private $page_count;
    private $page_limit;
    private $base = null;
    private $end = null;
    public $page;

    public function  __construct ($url, $total, $records_per_page, $seo_friendly_url = true) {
        global $config;
        $this->url = is_array ($url) ? $url[0] : $url;
        $this->seo_friendly_url = $seo_friendly_url ? true : false;
        $this->query_string = is_array ($url) ? $url[1] : null;
        $this->total = $total;
        $this->records_per_page = $records_per_page;
        $this->page_limit = 9;
        $this->page_count = ceil ($this->total/$this->records_per_page);
        $this->page = $this->GetPage();
        Plugin::Trigger ('pagination.start');
    }



    /**
     * Output paginated links
     * @return mixed Returns the pagination block with links 
     */
    public function Paginate() {
        Plugin::Trigger ('pagination.paginate');
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
//        return ($this->page != 1) ? '<li><a href="' . $this->url . '/page/' . ($this->page-1) . '/' . $this->query_string . '">&laquo;' . Language::GetText('previous') . '</a></li>' : '';
    }

    // Retrieve Next link
    private function GetNext() {
        return ($this->page != $this->page_count) ? '<li><a href="' . $this->BuildURL($this->page+1) . '">' . Language::GetText('next') . '&raquo;</a></li>' : '';
//        return ($this->page != $this->page_count) ? '<li><a href="' . $this->url . '/page/' . ($this->page+1) . '/' . $this->query_string . '">' . Language::GetText('next') . '&raquo;</a></li>' : '';
    }

    // Retrieve First two series links
    private function GetFirst() {
        if (!$this->base) {
            $first = '<li><a href="' . $this->BuildURL(1) . '">1</a></li>';
            $first .= '<li><a href="' . $this->BuildURL(2) . '">2</a></li>';
//            $first = '<li><a href="' . $this->url . '/page/1/' . $this->query_string . '">1</a></li>';
//            $first .= '<li><a href="' . $this->url . '/page/2/' . $this->query_string . '">2</a></li>';
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
//            $last .= '<li><a href="' . $this->url . '/page/' . ($this->page_count-1) . '/' . $this->query_string . '">' . ($this->page_count-1) . '</a></li>';
//            $last .= '<li><a href="' . $this->url . '/page/' . $this->page_count . '/' . $this->query_string . '">' . $this->page_count . '</a></li>';
            return $last;
        } else {
            return '';
        }
    }

    // Retrieve Starting record
    public function GetStartRecord() {
        return ($this->page - 1) * $this->records_per_page;
    }



    public function BuildURL ($page = null) {
        $page = (isset ($page)) ? $page : $this->GetPage();
        if ($this->seo_friendly_url) {
            return $this->url . '/page/' . $page . '/' . $this->query_string;
        } else {
            return $this->url . '?page=' . $page . $this->query_string;
        }
    }

}

?>