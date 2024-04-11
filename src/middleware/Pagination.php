<?php
namespace Src\Middleware;
use Src\Model\System_Model;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]
class Pagination extends System_Model
{
    public $results_per_page;
    public $range;
    public $current_page;
    public $total_pages;
    public $url;
    public $query;
    public $offset;
    public $dataset;
    // Dropdown select how many results per page to show
    public $item_select;

    public function __construct( $app )
    {
        parent::__construct( $app );

        //set default values
        $this->results_per_page = 20;
        $this->range            = 3;
        $this->current_page     = $_GET['page'] ?? 1;		
        $this->total		    = 0;
        $this->item_select      = [5,10,20,50,100,'All'];
        $this->url = $this->config->setting( 'site_url' );
    }

    public function config( $sql, $results_per_page = 20, $url = '', $range = 5)
    {
        $this->current_page = $_GET['page'] ?? 1;
        $this->results_per_page = $results_per_page;
        $this->offset = ($this->current_page - 1) * $results_per_page;
        $this->query = $sql;
        $this->url = $this->config->setting( 'site_url' ) . DS . $url;
        $this->range = $range;
        $this->dataset = self::runQuery( $sql );
        $this->total_pages = self::countResults();

        return $this;
    }

    

    /**
     * paginate main function
     * 
     * @author              The-Di-Lab <thedilab@gmail.com>
     * @access              public
     * @return              type
     */
    public function paginate()
    {
        //get current page
        if(isset($_GET['current'])){
            $this->currentPage  = $_GET['current'];		
        }			
        //get item per page
        if(isset($_GET['item'])){
            $this->itemsPerPage = $_GET['item'];
        }			
        //get page numbers
        $this->_pageNumHtml = $this->_getPageNumbers();			
        //get item per page select box
        $this->_itemHtml	= $this->_getItemSelect();	
    }

    /**
     * return pagination numbers in a format of UL list
     * 
     * @author              The-Di-Lab <thedilab@gmail.com>
     * @access              public
     * @param               type $parameter
     * @return              string
     */
    public function pageNumbers()
    {
        if(empty($this->_pageNumHtml)){
            exit('Please call function paginate() first.');
        }
        return $this->_pageNumHtml;
    }

    /**
     * return jump menu in a format of select box
     *
     * @author              The-Di-Lab <thedilab@gmail.com>
     * @access              public
     * @return              string
     */
    public function itemsPerPage()
    {          
        if(empty($this->_itemHtml)){
            exit('Please call function paginate() first.');
        }
        return $this->_itemHtml;	
    }

    /**
     * return page numbers html formats
     *
     * @author              The-Di-Lab <thedilab@gmail.com>
     * @access              public
     * @return              string
     */
    private function  _getPageNumbers()
    {
        $html  = '<nav aria-label="Page navigation"><ul class="pagination">'; 
        //previous link button
        if($this->textNav&&($this->currentPage>1)){
            $html.='<li class="page-item prev"><a class="link" href="'.$this->_link .'?current='.($this->currentPage-1).'"';
            $html.='>'.$this->_navigation['pre'].'</a></li>';
        }        	
        //do ranged pagination only when total pages is greater than the range
        if($this->total > $this->range){				
            $start = ($this->currentPage <= $this->range)?1:($this->currentPage - $this->range);
            $end   = ($this->total - $this->currentPage >= $this->range)?($this->currentPage+$this->range): $this->total;
        }else{
            $start = 1;
            $end   = $this->total;
        }    
        //loop through page numbers
        for($i = $start; $i <= $end; $i++){
                $html.='<li class="page-item"><a class="page-link" href="'.$this->_link .'?current='.$i.'"';
                if($i==$this->currentPage) $html.="class='current'";
                $html.='>'.$i.'</a></li>';
        }
        //next link button
        if($this->textNav&&($this->currentPage<$this->total)){
            $html.='<li class="next"><a href="'.$this->_link .'?current='.($this->currentPage+1).'"';
            $html.='><i class="tf-icon bx bx-chevron-right"></i></a></li>';
        }
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * return item select box
     *
     * @author              The-Di-Lab <thedilab@gmail.com>
     * @access              public
     * @return              string
     */
    private function  _getItemSelect()
    {
        $items = '';
        $ippArray = $this->itemSelect;   			
        foreach($ippArray as $ippOpt){   
            $items .= ($ippOpt == $this->itemsPerPage) ? "<option selected value=\"$ippOpt\">$ippOpt</option>\n":"<option value=\"$ippOpt\">$ippOpt</option>\n";
        }   			
        return "<span class=\"paginate\">".$this->_navigation['ipp']."</span>
        <select class=\"paginate\" onchange=\"window.location='$this->_link?current=1&item='+this[this.selectedIndex].value;return false\">$items</select>\n";   	
    }

    

    public function countResults($sql)
    {
        //$this->fancyDebug( TRUE );
        $pdo = $this->getPDO();
        $writer = $this->getWriter();
        // $table = $writer->esc( $sql['table'] );
        $query = $sql;
        // $parameters =  $sql['parameters'];
        // exit(var_dump( "{$sql} LIMIT " . ($this->currentPage - 1) * $this->itemsPerPage . ", ". $this->itemsPerPage));
        $q = $this->getAssocRow( 
            $query
        );
        return count($q);
    }

    public function runQuery($sql)
    {
        // $this->fancyDebug( TRUE );
        $pdo = $this->getPDO();
        $writer = $this->getWriter();
        // $table = $writer->esc( $sql['table'] );
        $query = $sql;
        // $parameters =  $sql['parameters'];
        // exit(var_dump( "{$sql} LIMIT " . ($this->currentPage - 1) * $this->itemsPerPage . ", ". $this->itemsPerPage));
        $q = $this->getAssocRow( 
            "{$query} LIMIT " . ($this->currentPage - 1) * $this->itemsPerPage . ", ". $this->itemsPerPage
        );

        return $q;
    }

    public function display()
    {
        $navbar = '<nav aria-label="Page navigation">';
        $navbar.= '<ul class="pagination">';

        // Show back buttons
        if( $this->total_pages > 1 && $this->current_page != 1 )
        {
            $navbar.= '<li class="page-item first"><a class="page-link" href="'.$this->url.'"><i class="tf-icon bx bx-chevrons-left"></i></a></li>';
            $navbar.= '<li class="page-item prev"><a class="page-link" href="'.$this->url.'"><i class="tf-icon bx bx-chevron-left"></i></a></li>';
        }

        $navbar.= '<li class="page-item"><a class="page-link" href="'.$this->url.'">1</a></li>';
    }
     
}