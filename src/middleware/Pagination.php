<?php
namespace Src\Middleware;
use Src\Model\System_Model;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]
class Pagination extends System_Model
{
	// Array containing data to be paginated
	/**
	 * @var mixed
	 */
	public $dataset;

	// Element ID for select menu Jump To
	/**
	 * @var mixed
	 */
	public $elid;

	// Total number of rows returned
	/**
	 * @var mixed
	 */
	public $num_records;

	// Offset for pagination
	/**
	 * @var mixed
	 */
	public $offset;

	// Current page
	/**
	 * @var mixed
	 */
	public $page;

	// How many results to display per page
	/**
	 * @var mixed
	 */
	public $perpage;

	// The sql being executed
	/**
	 * @var mixed
	 */
	public $query;

	// Range between current page and surrounding links to show in pag menu
	/**
	 * @var int
	 */
	public $range = 5;

	// Total number of pages to paginate
	/**
	 * @var mixed
	 */
	public $total_pages;

	// The url where pagination is displayed
	/**
	 * @var mixed
	 */
	public $url;

	// This is used for parsing links; if $_GET is set in url,
	// then use '&' instead of '?'
	/**
	 * @var string
	 */
	private $getseperator = '?';

	/**
	 * @param $sql
	 * @param $url
	 * @param $per_page
	 */
	public function config( $sql, $url, $per_page = 20 ): void
	{
		$url = chop( $url );
		$url = chop( $url, "/" );
		$url = $url . '/';

		if ( isset( $_GET ) )
		{
			if ( count( $_GET ) === 1 )
			{
				foreach ( $_GET as $k => $v )
				{
					$params = "?{$k}={$v}";
				}
				$url .= $params;
			}

			if ( count( $_GET ) > 1 )
			{
				$firstIndex      = array_key_first( $_GET );
				$firstIndexValue = urlencode( current( $_GET ) );
				$params          = "?{$firstIndex}={$firstIndexValue}";

				array_shift( $_GET );

				foreach ( $_GET as $k => $v )
				{
					$params .= "&{$k}={$v}";
				}
				$url = $url . $params;
			}

			$this->getseperator = '&';
		}

		$this->page        = $_GET["page"] ?? 1;
		$this->offset      = ( $this->page * $this->perpage ) - 1;
		$this->total_pages = 0;
		$this->perpage     = $per_page;
		$this->url         = $this->config->setting( 'site_url' ) . $url;
		$this->dataset     = $sql;
		$this->num_records = count( $this->dataset );
		if ( $this->num_records != 0 && $this->num_records != '' )
		{
			$this->total_pages = ceil( $this->num_records / $this->perpage );
		}

		$this->elid = md5( time() . mt_rand( 1, 10000 ) );
	}

	/**
	 * @return mixed
	 */
	public function links(): string
	{
		$backOnePage = 1;
		if ( ( $this->page - $this->range - 1 ) > 1 )
		{
			$backOnePage = $this->page - $this->range - 1;
		}

		$links = '<nav aria-label="Page navigation">';
		$links .= '<ul class="pagination pagination-md">';

		// Show first page link if previous page link below doesnt take to first page
		if ( $backOnePage > 1 )
		{
			$links .= '<li class="page-item prev">
							<a class="page-link" href="' . $this->url . $this->getseperator . 'page=1">
								<i class="tf-icon bx bx-chevrons-left"></i> First
							</a>
						</li>';
		}
		// Previous
		if ( $this->page > 1 )
		{
			$links .= '<li class="page-item prev">
						<a class="page-link" href="' . $this->url . $this->getseperator . 'page=' . $backOnePage . '">
							<i class="tf-icon bx bx-chevron-left"></i>
						</a>
	  				</li>';
		}

		// Numbered buttons
		for ( $i = 1; $i <= $this->total_pages; $i++ )
		{
			if ( (int) $this->page === $i )
			{
				$active_page_css = 'active';
			}
			else
			{
				$active_page_css = '';
			}

			// Only display links within the specified range
			if ( ( $i >= $this->page - $this->range ) && ( $i <= $this->page + $this->range ) && $this->total_pages > 1 )
			{
				$links .= '<li class="page-item ' . $active_page_css . '">
							<a class="page-link" href="' . $this->url . $this->getseperator . 'page=' . $i . '">' . $i . '</a>
						</li>';
			}
		}
		// Show last page button
		if ( ( $this->total_pages > 9 ) )
		{
			$links .= '<li class="page-item">
						<a class="page-link" href="' . $this->url . $this->getseperator . 'page=' . $this->total_pages . '">
							<i class="tf-icon bx bx-chevrons-right"></i> Last
						</a>
					</li>';
			$links .= '<li class="page-item" style="margin-left: 40px;">' . self::showSelectMenu() . '</li>';
		}

		$links .= '</ul>';
		$links .= '</nav>';

		return $links;
	}

	/**
	 * @return mixed
	 */
	public function runQuery(): array | string
	{
		$content = [];

		$page    = $this->page;
		$numrecs = $this->num_records;
		$perpage = $this->perpage;

		$start = 0;

		if ( $page > 1 )
		{
			$start = ( $page * $perpage ) - $perpage;
		}

		$stop = ( $start + $perpage ) - 1;

		for ( $i = 0; $i <= $this->num_records; $i++ )
		{
			if ( ( $i >= $start ) && ( $i <= $stop ) )
			{
				$content[] = $this->dataset[$i];
			}
		}

		return $content;
	}

	/**
	 * @return mixed
	 */
	public function showSelectMenu(): string
	{
		$select = '<select name="pagination_' . $this->elid . '"
					class="form-select"
					id="' . $this->elid . '"
					onChange="window.document.location.href=this.options[this.selectedIndex].value;">';

		$select .= '<option>Jump To Page</option>';

		for ( $i = 1; $i <= $this->total_pages; $i++ )
		{
			$select .= '<option value="' . $this->url . $this->getseperator . 'page=' . $i . '">' . $i . '</option>';
		}

		$select .= '</select>';
		return $select;
	}
}