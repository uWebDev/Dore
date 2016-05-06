<?php

namespace Dore\Core\Paginator;

class Paginator
{

    private $currentPage;
    private $recordsCount;
    private $perPageLimit = 10;
    private $maxPagesCount = 1;
    private $pagesCount;

    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }

    public function setRecordsCount($recordsCount)
    {
        $this->recordsCount = $recordsCount;
        return $this;
    }

    public function setPerPageLimit($perPageLimit)
    {
        $this->perPageLimit = $perPageLimit;
        return $this;
    }

    public function setMaxPageCount($maxPagesCount)
    {
        $this->maxPagesCount = $maxPagesCount;
        return $this;
    }

    private function getPageRange()
    {
        $this->pagesCount = (int) ceil($this->recordsCount / $this->perPageLimit);
        $firstPageInRange0 = $this->currentPage - (int) ($this->maxPagesCount / 2);
        $firstPageInRange1 = $this->pagesCount - $firstPageInRange0 < $this->maxPagesCount ?
                $this->pagesCount - $this->maxPagesCount + 1 : $firstPageInRange0;
        $firstPageInRange = $firstPageInRange1 < 1 ? 1 : $firstPageInRange1;
        //
        $lastPageInRange0 = $firstPageInRange + ($this->maxPagesCount - 1);
        $lastPageInRange = $lastPageInRange0 > $this->pagesCount ? $this->pagesCount : $lastPageInRange0;

        return range($firstPageInRange, $lastPageInRange);
    }

    public function getPages()
    {
        $pageRange = $this->getPageRange();

        $pages = [
            'current' => $this->currentPage,
            'pages' => $pageRange,
        ];

        $prevPage = $this->currentPage != 1 ? $this->currentPage - 1 : null;
        $nextPage = $this->currentPage < $this->pagesCount ? $this->currentPage + 1 : null;
        $lastPage = $nextPage ? $this->pagesCount : null;

        $pages['previous'] = !$prevPage ? null : $prevPage;
        $pages['next'] = !$nextPage ? null : $nextPage;
        !$lastPage ? : $pages['last'] = $lastPage;

        return $pages;
    }

}
