<?php


namespace Doowebdev;


class DooPagination {

    var $max, $total, $parameter, $start = 0;
    var $i = 0;

    /**
     * Class Constructor, Accepts 3 parameters. The minimum that any pagination requires.
     * @param int $max maximum amount of results per page
     * @param int $total total number of results in data source
     * @param int $max_items If set to 7 then a maximum of 7 page numbers are shown. The previous 3 pages, the current page and the next 3 pages shown. (Set to false for all page numbers)
     * @param int $parameter Default $_GET[param], where parameter will be replaced
     */
    public function __construct($max, $total, $max_items = 10, $parameter = 'p')
    {
        $this->max = $max;
        $this->total = $total;
        $this->parameter = $parameter;
        $this->max_items = $max_items;

        # check if the get parameter value is not empty or not higher than the total number of pages.
        $this->get = (!empty($_GET[$this->parameter]) && ($_GET[$this->parameter] <= $this->pages())) ? $_GET[$this->parameter] : 1;
    }

    /**
     * A pre-formatted string of html to display general pagination links. Use this if you don't care too much about
     * The html includes links to first, last, next, previous and numbered pages.
     * @param int $url the url to be used in the links
     * @return string HTML
     *
     * to add - $div_class="pagination", $prev_class ='', $prev_link_class ='', $next_class='', $next_link_class =''
     */
    public function get_links( $url )
    {
        $links = '<style>.n{ padding: 0 8px 0 8px;}</style><div class="pagination">';
        $links .='<ul>';
        // $links .= $this->first('<a href="' . $url . '" alt="First">First</a> ', 'First ');
        $links .= '<li class="previous">'.$this->previous(' <a class="fui-arrow-left" href="' . $url . '" alt=""></a> ', ' ').'</li>';
        $links .= $this->numbers('<li><a href="' . $url . '"> {nr} </a></li> ', ' <li class="n">{nr}</li> ');
        $links .= '<li class="next">'.$this->next(' <a class="fui-arrow-right" href="' . $url . '" alt=""></a> ', ' ').'</li>';
        // $links .= $this->last('<a href="' . $url . '">Last</a>', 'Last ');
        $links .='</ul>';
      //  $links .= '</div>';
        return $links;
    }

    /**
     * This calculates the start of our result set, based on our current page
     * @return int Final Calculation of where our result set should begin
     */
    public function start()
    {
        # Computers Calculate From 0 thus, page1 must start results from 0
        $start = $this->get - 1;

        # Calculate Our Starting Point (0x6=0(start from 0, page1), 1x6=6(start from 6, page2), etc)
        $calc = $start * $this->max;

        # return our final outcome
        return $calc;
    }

    /**
     * This calculates the end of our result set, based on our current page
     * @return int Final Calculation of where our result set should end
     */
    public function end()
    {
        # Calculate the Beginning + The maximum amount of results
        $calc = $this->start() + $this->max;

        # Only return this if it is not above the total otherwise return our maximum
        # example, 24 + 6 = 30, but with only 26 reselts it will display the total results istead (26)
        $r = ($calc > $this->total) ? $this->total : $calc;

        # return the result
        return $r;
    }

    /**
     * This calculates the total pages in our result set
     * @return int return Rounds Up the total results / maximum per page
     */
    public function pages()
    {
        return ceil($this->total / $this->max);
    }

    /**
     * Based on which page you are this returns informations like, start result, end result, total results, current page, total pages
     * @param string $html The HTML you wish to use to display the link
     * @return mixed return information we may need to display
     */
    public function info($html)
    {
        $tags = array('{total}', '{start}', '{end}', '{page}', '{pages}');
        $code = array($this->total, $this->start() + 1, $this->end(), $this->get, $this->pages());

        return str_replace($tags, $code, $html);
    }

    /**
     * This shows the 'first' link with custom html
     * @param string $html The HTML you wish to use to display the link
     * @return string The Same HTML replaced the tags with the proper number
     */
    public function first($html, $html2 = '')
    {
        # Only show if you are not on page 1, otherwise show HTML2
        $r = ($this->get != 1) ? str_replace('{nr}', 1, $html) : str_replace('{nr}', 1, $html2);

        return $r;
    }

    /**
     * This shows the 'previous' link with custom html
     * @param string $html The HTML you wish to use to display the link
     * @return string The Same HTML replaced the tags with the proper number
     */
    public function previous($html, $html2 = '')
    {
        # Only show if you are not on page 1, otherwise show HTML2
        $r = ($this->get != 1) ? str_replace('{nr}', $this->get - 1, $html) : str_replace('{nr}', $this->get - 1, $html2);

        return $r;
    }

    /**
     * This shows the 'next' link with custom html
     * @param string $html The HTML you wish to use to display the link
     * @return string The Same HTML replaced the tags with the proper number
     */
    public function next($html, $html2 = '')
    {
        # Only show if you are not on the last page
        $r = ($this->get < $this->pages()) ? str_replace('{nr}', $this->get + 1, $html) : str_replace('{nr}', $this->get + 1, $html2);

        return $r;
    }

    /**
     * This shows the 'last' link with custom html
     * @param string $html The HTML you wish to use to display the link
     * @return string The Same HTML replaced the tags with the proper number
     */
    public function last($html, $html2 = '')
    {
        # Only show if you are not on the last page
        $r = ($this->get < $this->pages()) ? str_replace('{nr}', $this->pages(), $html) : str_replace('{nr}', $this->pages(), $html2);

        return $r;
    }

    /**
     * This shows an loop of 'numbers' with their appropriate link in custom html
     * @param string $link The HTML to display a number with a link
     * @param string $current The HTML to display a the current page number without a link
     * @param string $reversed Optional Parameter, set to true if you want the numbers reversed (align to right for designs)
     * @return string The Same HTML replaced the tags with the proper numbers and links
     */
    public function numbers($link, $current, $reversed = false)
    {
        $r = '';
        $range = floor(($this->max_items - 1) / 2);
        if (!$this->max_items) {
            $page_nums = range(1, $this->pages());
        } else {
            $lower_limit = max($this->get - $range, 1);
            $upper_limit = min($this->pages(), $this->get + $range);
            $page_nums = range($lower_limit, $upper_limit);
        }

        if ($reversed) {
            $page_nums = array_reverse($page_nums);
        }

        foreach ($page_nums as $i) {
            if ($this->get == $i) {
                $r .= str_replace('{nr}', $i, $current);
            } else {
                $r .= str_replace('{nr}', $i, $link);
            }
        }
        return $r;
    }

    /**
     * This function allows you to limit the loop without using a limit inside another query. Or if you are using arrays / xml.
     */
    public function paginator()
    {
        $this->i = $this->i + 1;
        if ($this->i > $this->start() && $this->i <= $this->end()) {
            return true;
        }
    }



} 