<?php
/**
 * 分页类
 *
 * @author: 洪涛
 * @date: 2017/8/30
 */

namespace core\ext\pagination;

class Pagination
{
    private $_currentPage;
    private $_totalRow;
    private $_pageRow;
    private $_totalPage;
    private $_showPage = 8;
    private $_redirect;

    public function __construct($totalRow, $pageRow, $redirect,$currentPage)
    {
        $this->_totalRow = $totalRow;
        $this->_pageRow = $pageRow;
        $this->_redirect = $redirect;
        $this->_currentPage = (int)abs($currentPage);
        $this->_currentPage = $this->_currentPage === 0 ? 1 : $this->_currentPage;
        $this->_totalPage = ceil($this->_totalRow / $this->_pageRow);
    }

    public function show()
    {
        $previousPage = $this->_currentPage - 1;
        $nextPage = $this->_currentPage + 1;

        $paginationHtml = "<nav aria-label='Page navigation'>
                                <ul class='pagination'>
                                       <li><a href='$this->_redirect/p/{$previousPage}'><span>&laquo;</span></a></li>";


        if(($this->_currentPage - 1) % ($this->_showPage - 1) == 0) // 当前页是本层的第一位或最后一位
        {

            $startPage = $this->_currentPage; // 本层开始页码
        }
        else // 当前页是本层的中间位
        {
            $startPage = (ceil($this->_currentPage / ($this->_showPage-1)) - 1) * ($this->_showPage - 1) + 1; // 本层开始页码
        }

        // 根据开始页码计算结束页码
        if(($startPage + ($this->_showPage - 1)) > $this->_totalPage) // 对应层结束页码大于总页码
        {
            $endPage = $this->_totalPage; // 则结束页码为总页码
        }
        else
        {
            $endPage = $startPage + ($this->_showPage - 1);
        }

        for($i = $startPage;$i <= $endPage;$i++)
        {
            $active = '';
            if($i == $this->_currentPage)
            {
                $active = 'active';
            }
            $paginationHtml .= "<li class='{$active}'><a href='{$this->_redirect}/p/{$i}'>{$i}</a></li>";
        }

        $paginationHtml .= "
                                        <li><a href='$this->_redirect/p/{$nextPage}'><span>&raquo;</span></a></li>
                                </ul>
                           </nav>";

        return $paginationHtml;
    }
}