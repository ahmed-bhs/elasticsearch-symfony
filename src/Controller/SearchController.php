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
        $query = $...->getSearchQuery();

        $aggregation = $...->getFilterAggregations($query);

        /** @var Pagerfanta $pagerfanta */
        $pagerfanta = $finder->findPaginated($query);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        return $this->render('search/index.html.twig', ['pagerfanta' => $pagerfanta, 'data' => $pagerfanta->getCurrentPageResults(), 'aggregation' => array_shift($aggregation)['category_aggregation']]);
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
