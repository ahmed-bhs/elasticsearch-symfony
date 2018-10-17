<?php

namespace App\Repository;

class SearchRepository
{

    public function getSearchQuery() {

        /** @var \Elastica\Type $searcher */
        $searcher = $this->container->get('fos_elastica.index.post_index.post');

        /** @var \FOS\ElasticaBundle\Finder\TransformedFinder $finder */
        $finder = $this->container->get('fos_elastica.finder.post_index.post');


        $query = new \Elastica\Query();
        $query->setQuery(new \Elastica\Query\MatchAll());

        if (!empty($cat = $request->query->get('cat'))) {
            $searchQuery = new \Elastica\Query\Match();
            $searchQuery->setFieldQuery('category.name', $cat);

            $original = new \Elastica\Query;
            $query = $original->setQuery((new \Elastica\Query\Nested())->setPath('category')->setQuery($searchQuery));
        }

        if (!empty($q = $request->query->get('q'))) {
            $searchQuery = new \Elastica\Query\Match();
            $searchQuery->setField('title', $q);


            $match = new \Elastica\Query\Match();
            $match->setFieldQuery('category.name', $q);
            $match->setFieldFuzziness('category.name', 2);

            $nestedQuery = new \Elastica\Query\Nested();
            $nestedQuery->setPath('category');
            $nestedQuery->setQuery($match);

            $boolQuery = new \Elastica\Query\BoolQuery();
            $boolQuery->addShould($searchQuery);
            $boolQuery->addShould($nestedQuery);
            $query = new \Elastica\Query($boolQuery);
        }

        return $query;
    }

    /**
     * @var $resultSet ResultSet
     */
    public function getSuggestsArray(ResultSet $resultSet)
    {
        foreach ($resultSet->getSuggests() as $suggests) {
            foreach ($suggests as $suggest) {
                if (!empty($suggest['options'])) {
                    foreach ($suggest['options'] as $option) {
                        $suggestions[] = $option['_source'];
                    }
                } else {
                    $suggestions = [];
                    break;
                }
            }
        }

        return $suggestions;
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function getFilterAggregations($query)
    {
        /** @var \Elastica\Type $searcher */
        $searcher = $this->container->get('fos_elastica.index.post_index.post');

        $aggregation = (new \Elastica\Aggregation\Nested('category_aggregation', 'category'));
        $aggregation2 = (new \Elastica\Aggregation\Terms('category_aggregation'))->setField('category.name')->setSize(10);
        $aggregation2->setOrder('_term', 'asc');
        $aggregation2->setParam('min_doc_count', 0);
        $aggregation->addAggregation($aggregation2);
        $query->addAggregation($aggregation);

        return $searcher->search($this->getFilterAggregations($query))->getAggregations();;
    }
}