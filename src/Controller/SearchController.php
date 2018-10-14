<?php

namespace App\Controller;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Suggest\Completion;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Elastica\Client;

class SearchController extends Controller
{

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request)
    {
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


        $aggregation = $searcher->search($this->getFilterAggregations($query))->getAggregations();
        $pagerfanta = $finder->findPaginated($query);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        return $this->render('search/index.html.twig', ['pagerfanta' => $pagerfanta, 'data' => $pagerfanta->getCurrentPageResults(), 'aggregation' => array_shift($aggregation)['category_aggregation']]);
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


    public function getFilterAggregations($query)
    {
        $aggregation = (new \Elastica\Aggregation\Nested('category_aggregation', 'category'));
        $aggregation2 = (new \Elastica\Aggregation\Terms('category_aggregation'))->setField('category.name')->setSize(10);
        $aggregation2->setOrder('_term', 'asc');
        $aggregation2->setParam('min_doc_count', 0);
        $aggregation->addAggregation($aggregation2);
        $query->addAggregation($aggregation);

        return $query;
    }
//
//
//    public function getFilterAggregations($query) {
//
//        $aggregation = new \Elastica\Aggregation\Terms('category_aggregation');
//        $aggregation->setParam('min_doc_count', 0);
//        $aggregation->setField('name');
//        $aggregation->setOrder('_term', 'asc');
//        $query->addAggregation($aggregation);
//
//        return $query;
//    }
//

//
//{
//"query": {
//"match_all": {}
//},
//"aggs": {
//    "category_aggregation": {
//        "nested": {
//            "path": "category"
//      },
//      "aggs": {
//            "category_terms": {
//                "terms": {
//                    "field": "category.name"
//          }
//        }
//      }
//    }
//  }
//}
}
