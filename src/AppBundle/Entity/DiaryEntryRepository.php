<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * DiaryEntryRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class DiaryEntryRepository extends EntityRepository
{
    public function findAllDesc()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    /**
     * Get all entries
     * @param int $currentPage Current page number
     * @param int $limit Maximal number of records on one page
     * @return Paginator
     */
    public function getAllEntries($currentPage = 1, $limit)
    {
        // Create our query
        $query = $this->createQueryBuilder('diary_entry')
            ->orderBy('diary_entry.dateTime', 'ASC')
            ->getQuery();

        // No need to manually get get the result ($query->getResult())
        $paginator = $this->paginate($query, $currentPage, $limit);
        return $paginator;
    }

    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return Paginator
     */
    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

    public function getNextEntryByDateTime($actualId)
    {
        /** @var DiaryEntry $actualEntry */
        $actualEntry = $this->find($actualId);
        $dateTime = $actualEntry->getDateTime()->format("Y-m-d h:i:s");

        $query = $this->createQueryBuilder('de')
            ->select('de.id')
            ->where("de.dateTime > :dt")
                ->setParameter(":dt", $dateTime)
            ->orderBy('de.dateTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery();
        return $query->getResult();
    }

    public function getRowNum($id)
    {
        $query = $this->createQueryBuilder('diary_entry')
            ->select('diary_entry.id')
            ->orderBy('diary_entry.dateTime', 'ASC')
            ->getQuery();
        $idArr = $query->getResult();
        //TODO get row number problem
        return $idArr['id'];
        $rowNum = array_search($id, $idArr['id']);
        return $rowNum;
    }
}
