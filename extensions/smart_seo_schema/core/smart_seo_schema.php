<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionSmartSeoSchema extends Extension
{
    protected $registry;

    public function onControllerPagesProductProduct_UpdateData()
    {
        /** @var ControllerPagesProductProduct $that */
        $that = $this->baseObject;
        $product_snippet = [
            "@context" => "http://schema.org/",
            "@type"    => "Product",
        ];
        $product_snippet["name"] = $that->data['product_info']['name'];
        if ($that->config->get('smart_seo_schema_description') == 'auto') {
            if ($that->data['product_info']['blurb']) {
                $product_snippet["description"] = strip_tags(
                    html_entity_decode($that->data['product_info']['blurb'])
                );
            } else {
                $product_snippet["description"] = strip_tags(
                    html_entity_decode($that->data['product_info']['description'])
                );
            }
        } else {
            if ($that->config->get('smart_seo_schema_description') == 'blurb') {
                $product_snippet["description"] = strip_tags(
                    html_entity_decode($that->data['product_info']['blurb'])
                );
            } else {
                if ($that->config->get('smart_seo_schema_description') == 'description') {
                    $product_snippet["description"] = strip_tags(
                        html_entity_decode($that->data['product_info']['description'])
                    );
                }
            }
        }

        if ($that->config->get('smart_seo_schema_show_image')) {
            $product_snippet["image"] = $that->data['image_main']['thumb_url'];
        }

        if ($that->data['product_info']['model']) {
            $product_snippet["mpn"] = $that->data['product_info']['model'];
        }

        if ($that->data['product_info']['manufacturer']) {
            $product_snippet["brand"] = $that->data['product_info']['manufacturer'];
        }

        if ($that->config->get('smart_seo_schema_show_sku') && $that->data['product_info']['sku']) {
            $product_snippet["sku"] = $that->data['product_info']['sku'];
        }

        $rating = $that->data['average'];
        if ($that->data['product_info']['rating']) {
            $rating = $that->data['product_info']['rating'];
        }
        if ($that->config->get('smart_seo_schema_show_review') && $rating) {
            $total_reviews = $that->model_catalog_review->getTotalReviewsByProductId(
                $that->data['product_info']['product_id']
            );

            $product_snippet["aggregateRating"] = [
                "@type"       => "AggregateRating",
                "ratingValue" => $rating,
                "reviewCount" => $total_reviews,
            ];
        }

        $price = $that->data['product_info']['price'];
        if ($that->data['product_info']['final_price'] > 0.00) {
            $price = $that->data['product_info']['final_price'];
        }

        if ($that->config->get('smart_seo_schema_show_offer') && $price > 0.00) {
            $stockStatuses = [
                'Discontinued'        => 'Discontinued',
                'InStock'             => 'InStock',
                'InStoreOnly'         => 'InStoreOnly',
                'LimitedAvailability' => 'LimitedAvailability',
                'OnlineOnly'          => 'OnlineOnly',
                'OutOfStock'          => 'OutOfStock',
                'Pre-Order'           => 'PreOrder',
                'Pre-Sale'            => 'PreSale',
                'SoldOut'             => 'SoldOut',
            ];

            $stockStatus = $stockStatuses['InStock'];
            if (preg_match("/".$that->language->get('text_instock')."/i", $that->data['stock'])) {
                $stockStatus = $stockStatuses['InStock'];
            } else {
                if (preg_match("/".$that->language->get('text_out_of_stock')."/i", $that->data['stock'])) {
                    $stockStatus = $stockStatuses['OutOfStock'];
                } else {
                    if (preg_match("/discontinued/i", $that->data['stock'])) {
                        $stockStatus = $stockStatuses['Discontinued'];
                    } else {
                        if (preg_match("/limited/i", $that->data['stock'])) {
                            $stockStatus = $stockStatuses['LimitedAvailability'];
                        } else {
                            if (preg_match("/Pre[\s-]*Order/i", $that->data['stock'])) {
                                $stockStatus = $stockStatuses['Pre-Order'];
                            } else {
                                if (preg_match("/Pre[\s-]*Sale/i", $that->data['stock'])) {
                                    $stockStatus = $stockStatuses['Pre-Sale'];
                                }
                            }
                        }
                    }
                }
            }

            $priceValidUntil = new DateTime();

            $product_snippet["offers"] = [
                "@type"           => "Offer",
                "price"           => $price,
                "priceCurrency"   => $that->currency->getCode(),
                "priceValidUntil" => $priceValidUntil->format('c'),
                "url"             => $that->data['product_review_url'],
            ];
            if ($that->config->get('smart_seo_schema_show_availability')) {
                $product_snippet["offers"]["availability"] = $stockStatus;
            }
        }
        $that->load->library('json');
        $that->view->assign('product_snippet', AJson::encode($product_snippet));
    }
}
